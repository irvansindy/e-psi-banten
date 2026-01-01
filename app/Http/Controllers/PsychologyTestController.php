<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PsychologyTest;
use App\Models\Sim;
use App\Models\GroupSim;
use App\Helpers\FormatResponseJson;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
// Atau gunakan Imagick driver jika tersedia:
// use Intervention\Image\Drivers\Imagick\Driver;

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
            'gender' => 'required|in:male,female',
            'place_of_birth' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'age' => 'nullable|integer|min:0|max:127',
            'sim_id' => 'nullable|integer',
            'group_sim_id' => 'nullable|integer',
            'domicile' => 'nullable|string',
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return FormatResponseJson::error($validator->errors(), 'Validasi gagal', 422);
        }

        try {
            $data = $request->except('photo');

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->handlePhotoUpload($request->file('photo'));
            }

            $psychologyTest = PsychologyTest::create($data);
            return FormatResponseJson::success($psychologyTest->load(['sim', 'groupSim']), 'Data berhasil ditambahkan');
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
            $data = PsychologyTest::with(['sim', 'groupSim'])->findOrFail($id);
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
            'gender' => 'required|in:male,female',
            'place_of_birth' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'age' => 'nullable|integer|min:0|max:127',
            'sim_id' => 'nullable|integer',
            'group_sim_id' => 'nullable|integer',
            'domicile' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return FormatResponseJson::error($validator->errors(), 'Validasi gagal', 422);
        }

        try {
            $psychologyTest = PsychologyTest::findOrFail($id);
            $data = $request->except('photo');

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Hapus foto lama jika ada
                if ($psychologyTest->photo && Storage::disk('public')->exists($psychologyTest->photo)) {
                    Storage::disk('public')->delete($psychologyTest->photo);
                }

                $data['photo'] = $this->handlePhotoUpload($request->file('photo'));
            }

            $psychologyTest->update($data);
            return FormatResponseJson::success($psychologyTest->load(['sim', 'groupSim']), 'Data berhasil diupdate');
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
     * Handle photo upload and convert to WebP
     */
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