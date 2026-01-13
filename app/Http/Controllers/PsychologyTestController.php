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
    /**
     * Generate PDF Certificate
     */
    public function generatePDFOld($id)
    {
        try {
            $data = PsychologyTest::with(['sim', 'groupSim'])->findOrFail($id);
            // dd($data);

            // Generate certificate number
            $certificateNumber = date('Y') . str_pad($data->id, 10, '0', STR_PAD_LEFT);
            // dd($data->id .''. $certificateNumber);
            // Format tanggal lahir
            $birthDate = $data->date_of_birth ? \Carbon\Carbon::parse($data->date_of_birth)->isoFormat('D MMMM YYYY') : '-';
            $birthPlace = $data->place_of_birth ?: '-';

            // Generate QR Code URL (bisa diganti dengan URL verifikasi real)
            $qrCodeUrl = url('/verify/' . $certificateNumber);

            $pdf = PDF::loadView('admin.psychology-tests.certificate', [
                'data' => $data,
                'certificateNumber' => $certificateNumber,
                'birthDate' => $birthDate,
                'birthPlace' => $birthPlace,
                'qrCodeUrl' => $qrCodeUrl,
                'printDate' => \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY'),
            ]);

            $pdf->setPaper('A4', 'portrait');
            return $pdf->stream('Sertifikat-' . $data->name . '-' . $certificateNumber . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }
    public function generatePDFOld2($id)
    {
        try {
            $data = PsychologyTest::with(['sim', 'groupSim'])->findOrFail($id);

            // Generate certificate number
            $certificateNumber = date('Y') . str_pad($data->id, 10, '0', STR_PAD_LEFT);

            // Format tanggal lahir
            $birthDate = $data->date_of_birth ? \Carbon\Carbon::parse($data->date_of_birth)->isoFormat('D MMMM YYYY') : '-';
            $birthPlace = $data->place_of_birth ?: '-';

            // Generate QR Code URL (bisa diganti dengan URL verifikasi real)
            $qrCodeUrl = url('/verify/' . $certificateNumber);
            $pdf = PDF::loadView('admin.psychology-tests.certificate', [
                'data' => $data,
                'certificateNumber' => $certificateNumber,
                'birthDate' => $birthDate,
                'birthPlace' => $birthPlace,
                'qrCodeUrl' => $qrCodeUrl,
                'printDate' => \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY'),
            ]);

            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream('Sertifikat-' . $data->name . '-' . $certificateNumber . '.pdf');
        } catch (\Exception $e) {
            // Return error sebagai response JSON atau text untuk debugging
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
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
            $qrCodeApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrCodeUrl);

            // Download QR Code dan convert ke base64
            $qrCodeImage = file_get_contents($qrCodeApiUrl);
            $qrCode = base64_encode($qrCodeImage);

            $pdf = PDF::loadView('admin.psychology-tests.certificate', [
                'data' => $data,
                'certificateNumber' => $certificateNumber,
                'birthDate' => $birthDate,
                'birthPlace' => $birthPlace,
                'qrCodeUrl' => $qrCodeUrl,
                'qrCode' => $qrCode,
                'printDate' => \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY'),
            ]);

            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream('Sertifikat-' . $data->name . '-' . $certificateNumber . '.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
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