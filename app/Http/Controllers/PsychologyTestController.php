<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PsychologyTest;
use App\Models\Sim;
use App\Models\GroupSim;
use App\Helpers\FormatResponseJson;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;
class PsychologyTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.psychology-tests.index');
    }

    /**
     * Get dropdown data for SIM and Group SIM.
     */
    public function getDropdownData()
    {
        try {
            $data = [
                'sims' => Sim::orderBy('name')->get(),
                'group_sims' => GroupSim::orderBy('name')->get(),
            ];

            return FormatResponseJson::success($data, 'Data dropdown berhasil diambil');
        } catch (\Exception $e) {
            return FormatResponseJson::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Get all data via API.
     */
    public function getData(Request $request)
    {
        try {
            $query = PsychologyTest::with(['sim', 'groupSim']);

            // Search functionality
            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('place_of_birth', 'like', "%{$search}%")
                      ->orWhere('gender', 'like', "%{$search}%");
                });
            }

            $data = $query->orderBy('created_at', 'desc')->paginate(10);

            return FormatResponseJson::success($data, 'Data berhasil diambil');
        } catch (\Exception $e) {
            return FormatResponseJson::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nik' => 'required|string|size:16|regex:/^[0-9]+$/',
            'gender' => 'required|in:male,female',
            'place_of_birth' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'age' => 'nullable|integer|min:0|max:127',
            'sim_id' => 'nullable|integer',
            'group_sim_id' => 'nullable|integer',
            'domicile' => 'nullable|string',
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048', // TAMBAH
        ]);

        if ($validator->fails()) {
            return FormatResponseJson::error($validator->errors(), 'Validasi gagal', 422);
        }

        try {
            $data = $request->except('photo'); // UBAH dari $request->all()

            // TAMBAH: Handle photo upload
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->handlePhotoUpload($request->file('photo'));
            }

            $psychologyTest = PsychologyTest::create($data);
            return FormatResponseJson::success($psychologyTest, 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return FormatResponseJson::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $data = PsychologyTest::findOrFail($id);
            return FormatResponseJson::success($data, 'Data berhasil diambil');
        } catch (\Exception $e) {
            return FormatResponseJson::error(null, 'Data tidak ditemukan', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nik' => 'required|string|size:16|regex:/^[0-9]+$/',
            'gender' => 'required|in:male,female',
            'place_of_birth' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'age' => 'nullable|integer|min:0|max:127',
            'sim_id' => 'nullable|integer',
            'group_sim_id' => 'nullable|integer',
            'domicile' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048', // TAMBAH
        ]);

        if ($validator->fails()) {
            return FormatResponseJson::error($validator->errors(), 'Validasi gagal', 422);
        }

        try {
            $data = PsychologyTest::findOrFail($id);
            $updateData = $request->except('photo'); // UBAH dari $request->all()

            // TAMBAH: Handle photo upload
            if ($request->hasFile('photo')) {
                if ($data->photo && Storage::disk('public')->exists($data->photo)) {
                    Storage::disk('public')->delete($data->photo);
                }
                $updateData['photo'] = $this->handlePhotoUpload($request->file('photo'));
            }

            $data->update($updateData);
            return FormatResponseJson::success($data, 'Data berhasil diupdate');
        } catch (\Exception $e) {
            return FormatResponseJson::error(null, 'Data tidak ditemukan atau gagal diupdate', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $data = PsychologyTest::findOrFail($id);
            $data->delete();
            return FormatResponseJson::success(null, 'Data berhasil dihapus');
        } catch (\Exception $e) {
            return FormatResponseJson::error(null, 'Data tidak ditemukan atau gagal dihapus', 500);
        }
    }

    public function generatePDFOld($id)
    {
        try {
            $data = PsychologyTest::with(['sim', 'groupSim'])->findOrFail($id);

            // Generate certificate number
            $certificateNumber = date('Y') . str_pad($data->id, 10, '0', STR_PAD_LEFT);

            // Format tanggal lahir
            $birthDate = $data->date_of_birth ? \Carbon\Carbon::parse($data->date_of_birth)->isoFormat('D MMMM YYYY') : '-';
            $birthPlace = $data->place_of_birth ?: '-';

            // Generate QR Code URL
            $qrCodeUrl = route('psychology-tests.pdf', $id);

            // Generate QR Code menggunakan API external
            $qrCodeApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrCodeUrl);
            $qrCodeImage = @file_get_contents($qrCodeApiUrl);

            if ($qrCodeImage === false) {
                throw new \Exception('Gagal generate QR Code');
            }

            $qrCode = base64_encode($qrCodeImage);

            // Load template PDF - path yang benar
            $templatePath = public_path('assets/template/template_e-psi.pdf');

            // Cek apakah template ada
            if (!file_exists($templatePath)) {
                throw new \Exception('Template PDF tidak ditemukan di: ' . $templatePath);
            }

            // Menggunakan FPDI untuk edit PDF
            $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false, 0);

            // Import page 1 dari template
            $pageCount = $pdf->setSourceFile($templatePath);
            $templateId = $pdf->importPage(1);
            $pdf->AddPage('P', 'A4');
            $pdf->useTemplate($templateId, 0, 0, 210, 297, true);

            // Foto peserta
            if ($data->photo && Storage::disk('public')->exists($data->photo)) {
                $photoPath = Storage::disk('public')->path($data->photo);
                $pdf->Image($photoPath, 50, 85, 32, 32, '', '', '', true, 300, '', false, false, 0);
            }

            // Set font untuk data
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(0, 0, 0);

            // Nomor Sertifikat (di header)
            $pdf->SetFont('helvetica', 'B', 18);
            $pdf->SetXY(80, 45);
            $pdf->Cell(70, 5, 'SERTIFIKAT', 0, 0, 'L');

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetXY(70, 55);
            $pdf->Cell(70, 5, 'HASIL TES PSIKOLOGI SIM', 0, 0, 'L');

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetXY(70, 65);
            $pdf->Cell(70, 5, 'NO SERTIFIKAT : '.$certificateNumber, 0, 0, 'L');

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetXY(40, 75);
            $pdf->Cell(70, 5, 'Sertifikat ini diberikan kepada:', 0, 0, 'L');

            // Reset font untuk data
            $pdf->SetFont('helvetica', '', 10);

            // Nama Lengkap
            $pdf->SetXY(115, 84);
            $pdf->Cell(80, 5, strtoupper($data->name), 0, 0, 'L');

            // NIK
            $pdf->SetXY(115, 92);
            $pdf->Cell(80, 5, $data->nik, 0, 0, 'L');

            // Jenis Kelamin
            $pdf->SetXY(115, 100);
            $genderText = $data->gender == 'male' ? 'LAKI-LAKI' : 'PEREMPUAN';
            $pdf->Cell(80, 5, $genderText, 0, 0, 'L');

            // Tempat, Tanggal Lahir
            $pdf->SetXY(115, 108);
            $birthInfo = strtoupper($birthPlace) . ', ' . $birthDate;
            $pdf->Cell(80, 5, $birthInfo, 0, 0, 'L');

            // Usia
            $pdf->SetXY(115, 116);
            $ageText = ($data->age ?: '-') . ' TAHUN';
            $pdf->Cell(80, 5, $ageText, 0, 0, 'L');

            // Jenis SIM
            $pdf->SetXY(115, 124);
            $simType = $data->sim ? strtoupper($data->sim->name) : 'PERPANJANGAN';
            $pdf->Cell(80, 5, $simType, 0, 0, 'L');

            // Golongan SIM
            $pdf->SetXY(115, 132);
            $simGroup = $data->groupSim ? strtoupper($data->groupSim->name) : 'C';
            $pdf->Cell(80, 5, $simGroup, 0, 0, 'L');

            // Domisili
            $pdf->SetXY(115, 140);
            $domicile = $data->domicile ? strtoupper($data->domicile) : '-';
            $pdf->Cell(80, 5, $domicile, 0, 0, 'L');

            // QR Code di tengah bawah
            $qrImageData = base64_decode($qrCode);
            $qrTempPath = storage_path('app/temp_qr_' . $id . '.png');
            file_put_contents($qrTempPath, $qrImageData);

            $pdf->Image($qrTempPath, 82, 160, 28, 28, 'PNG');

            @unlink($qrTempPath);

            // Tanggal cetak (kanan bawah)
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetXY(120, 215);
            $printDate = \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY');
            $pdf->Cell(70, 5, $printDate, 0, 0, 'R');

            // Nama Psikolog
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetXY(120, 253);
            $pdf->Cell(70, 5, '( Pamila Maysari M.Psi, Psikolog )', 0, 0, 'R');

            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetXY(120, 258);
            $pdf->Cell(70, 5, 'Psikolog', 0, 0, 'R');

            // Output PDF
            $filename = 'Sertifikat-' . $data->name . '-' . $certificateNumber . '.pdf';
            return $pdf->Output($filename, 'I');

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function generatePDF($id)
    {
        try {
            $data = PsychologyTest::with(['sim', 'groupSim'])->findOrFail($id);

            // Generate certificate number
            $certificateNumber = date('Y') . str_pad($data->id, 10, '0', STR_PAD_LEFT);

            // Format tanggal lahir
            $birthDate = $data->date_of_birth ? \Carbon\Carbon::parse($data->date_of_birth)->isoFormat('D MMMM YYYY') : '-';
            $birthPlace = $data->place_of_birth ?: '-';

            // Generate QR Code URL
            $qrCodeUrl = route('psychology-tests.pdf', $id);

            // Generate QR Code menggunakan API external
            $qrCodeApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrCodeUrl);
            $qrCodeImage = @file_get_contents($qrCodeApiUrl);

            if ($qrCodeImage === false) {
                throw new \Exception('Gagal generate QR Code');
            }

            $qrCode = base64_encode($qrCodeImage);

            // Load template PDF
            $templatePath = public_path('assets/template/template_e-psi.pdf');

            if (!file_exists($templatePath)) {
                throw new \Exception('Template PDF tidak ditemukan di: ' . $templatePath);
            }

            // Menggunakan FPDI untuk edit PDF
            $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false, 0);

            // Import dan proses semua halaman dari template
            $pageCount = $pdf->setSourceFile($templatePath);

            // ============== PAGE 1 ==============
            $templateId = $pdf->importPage(1);
            $pdf->AddPage('P', 'A4');
            $pdf->useTemplate($templateId, 0, 0, 210, 297, true);

            // Set font
            $pdf->SetTextColor(0, 0, 0);

            // Nomor Sertifikat (di header)
            $pdf->SetFont('helvetica', 'B', 18);
            $pdf->SetXY(80, 45);
            $pdf->Cell(70, 5, 'SERTIFIKAT', 0, 0, 'L');

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetXY(70, 55);
            $pdf->Cell(70, 5, 'HASIL TES PSIKOLOGI SIM', 0, 0, 'L');

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetXY(70, 65);
            $pdf->Cell(70, 5, 'NO SERTIFIKAT : '.$certificateNumber, 0, 0, 'L');

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetXY(40, 75);
            $pdf->Cell(70, 5, 'Sertifikat ini diberikan kepada:', 0, 0, 'L');

            // Foto peserta
            if ($data->photo && Storage::disk('public')->exists($data->photo)) {
                $photoPath = Storage::disk('public')->path($data->photo);
                $pdf->Image($photoPath, 35, 85, 32, 32, '', '', '', true, 300);
            }

            // Data peserta
            $pdf->SetFont('helvetica', '', 9.5);

            $labelX = 82;
            $valueX = 145;
            $y = 82;

            // Nama Lengkap
            $pdf->SetXY($labelX, $y);
            $pdf->Cell(50, 0, 'Nama Lengkap', 0, 0, 'L');
            $pdf->SetXY($valueX, $y);
            $pdf->Cell(0, 0, ': ' . strtoupper($data->name), 0, 0, 'L');

            // NIK
            $y += 8;
            $pdf->SetXY($labelX, $y);
            $pdf->Cell(50, 0, 'NIK', 0, 0, 'L');
            $pdf->SetXY($valueX, $y);
            $pdf->Cell(0, 0, ': ' . $data->nik, 0, 0, 'L');

            // Jenis kelamin
            $y += 8;
            $pdf->SetXY($labelX, $y);
            $pdf->Cell(50, 0, 'Jenis kelamin', 0, 0, 'L');
            $pdf->SetXY($valueX, $y);
            $genderText = $data->gender == 'male' ? 'LAKI-LAKI' : 'PEREMPUAN';
            $pdf->Cell(0, 0, ': ' . $genderText, 0, 0, 'L');

            // Tempat, Tanggal Lahir
            $y += 8;
            $pdf->SetXY($labelX, $y);
            $pdf->Cell(50, 0, 'Tempat, Tanggal Lahir', 0, 0, 'L');
            $pdf->SetXY($valueX, $y);
            $birthInfo = strtoupper($birthPlace) . ', ' . $birthDate;
            $pdf->Cell(0, 0, ': ' . $birthInfo, 0, 0, 'L');

            // Usia
            $y += 8;
            $pdf->SetXY($labelX, $y);
            $pdf->Cell(50, 0, 'Usia', 0, 0, 'L');
            $pdf->SetXY($valueX, $y);
            $ageText = ($data->age ?: '-') . ' TAHUN';
            $pdf->Cell(0, 0, ': ' . $ageText, 0, 0, 'L');

            // Jenis SIM
            $y += 8;
            $pdf->SetXY($labelX, $y);
            $pdf->Cell(50, 0, 'Jenis SIM', 0, 0, 'L');
            $pdf->SetXY($valueX, $y);
            $simType = $data->sim ? strtoupper($data->sim->name) : 'PERPANJANGAN';
            $pdf->Cell(0, 0, ': ' . $simType, 0, 0, 'L');

            // Golongan SIM
            $y += 8;
            $pdf->SetXY($labelX, $y);
            $pdf->Cell(50, 0, 'Golongan SIM', 0, 0, 'L');
            $pdf->SetXY($valueX, $y);
            $simGroup = $data->groupSim ? strtoupper($data->groupSim->name) : 'C';
            $pdf->Cell(0, 0, ': ' . $simGroup, 0, 0, 'L');

            // Domisili
            $y += 8;
            $pdf->SetXY($labelX, $y);
            $pdf->Cell(50, 0, 'Domisili', 0, 0, 'L');
            $pdf->SetXY($valueX, $y);
            $domicile = $data->domicile ? strtoupper($data->domicile) : '-';
            $pdf->Cell(0, 0, ': ' . $domicile, 0, 0, 'L');

            // ================== KETERANGAN KELULUSAN ==================
            $y += 14;

            $textBold   = 'MEMENUHI SYARAT';
            $textNormal = ' dalam mengajukan permohonan SIM.';

            // Hitung lebar teks
            $pdf->SetFont('helvetica', 'B', 11);
            $boldWidth = $pdf->GetStringWidth($textBold);

            $pdf->SetFont('helvetica', '', 11);
            $normalWidth = $pdf->GetStringWidth($textNormal);

            $totalWidth = $boldWidth + $normalWidth;

            // Posisi X agar tetap center
            $startX = (210 - $totalWidth) / 2;

            // Teks bold
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetXY($startX, $y);
            $pdf->Cell($boldWidth, 6, $textBold, 0, 0, 'L');

            // Teks normal
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Cell($normalWidth, 6, $textNormal, 0, 1, 'L');

            // Masa berlaku
            $pdf->SetFont('helvetica', 'b', 10);

            // contoh: berlaku 1 tahun dari sekarang
            $validUntil = $data->created_at
    ? \Carbon\Carbon::parse($data->created_at)->addMonths(6)->isoFormat('D MMMM YYYY')
    : '-';


            $pdf->SetXY(20, $y + 7);
            $pdf->Cell(170, 6, 'Sertifikat ini berlaku sampai dengan ' . $validUntil, 0, 1, 'C');


            // QR Code tengah
            $qrImageData = base64_decode($qrCode);
            $qrTempPath = storage_path('app/temp_qr_' . $id . '.png');
            file_put_contents($qrTempPath, $qrImageData);

            $pdf->Image($qrTempPath, 90, 170, 30, 30, 'PNG');

            // ================== INFORMASI PLATFORM (KIRI + LOGO) ==================

            // Logo
            $logoPath = public_path('assets/logo/caina-logo.png');
            if (file_exists($logoPath)) {
                // X, Y, Width
                $pdf->Image($logoPath, 20, 208, 14, 14, 'PNG');
            }

            // Posisi teks setelah logo
            $textX = 36; // 20 + 14 + jarak
            $startY = 210;

            // EPPSI
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetXY($textX, $startY);
            $pdf->Cell(80, 5, 'EPPSI', 0, 1, 'L');

            // Platform
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetXY($textX, $startY + 5);
            $pdf->Cell(80, 5, 'Platform Tes Psikologi SIM', 0, 1, 'L');

            // Company
            $pdf->SetXY($textX, $startY + 10);
            $pdf->Cell(80, 5, 'dibuat oleh PT. Central Asesmen Indonesia', 0, 1, 'L');

            // Catatan
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetXY(20, $startY + 18);
            $pdf->Cell(80, 5, 'Catatan:', 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetXY(20, $startY + 23);
            $pdf->MultiCell(
                80,
                5,
                "Sertifikat ini bisa digunakan untuk pembuatan SIM A dan SIM C",
                0,
                'L'
            );


            // Tanggal
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetXY(106, 200);
            $printDate = \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY');
            $pdf->Cell(65, 0, $printDate, 0, 0, 'R');

            // QR Code TTD
            $pdf->Image($qrTempPath, 138, 205, 25, 25, 'PNG');

            @unlink($qrTempPath);

            // Nama Psikolog
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetXY(110, 232);
            $pdf->Cell(65, 0, '( Pamila Maysari M.Psi, Psikolog )', 0, 0, 'R');

            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetXY(100, 237);
            $pdf->Cell(55, 0, 'Psikolog', 0, 0, 'R');

            // Load semua page
            for ($i = 2; $i <= $pageCount; $i++) {
                $templateIdN = $pdf->importPage($i);
                $pdf->AddPage('P', 'A4');
                $pdf->useTemplate($templateIdN, 0, 0, 210, 297, true);
            }

            // Output PDF
            $filename = 'Sertifikat-' . $data->name . '-' . $certificateNumber . '.pdf';
            return $pdf->Output($filename, 'I');

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    private function handlePhotoUpload($file)
    {
        try {
            // Generate unique filename
            $filename = 'psychology_test_' . time() . '_' . uniqid() . '.webp';
            $path = 'psychology-tests/' . $filename;

            // Create ImageManager with GD driver
            $manager = new ImageManager(new Driver());

            // Read and process image
            $image = $manager->read($file);

            // Resize jika terlalu besar (max width 1200px)
            if ($image->width() > 1200) {
                $image->scale(width: 1200);
            }

            // Convert to WebP dan encode
            $encoded = $image->toWebp(quality: 90);

            // Simpan ke storage
            Storage::disk('public')->put($path, $encoded);

            return $path;
        } catch (\Exception $e) {
            throw new \Exception('Gagal memproses foto: ' . $e->getMessage());
        }
    }
}