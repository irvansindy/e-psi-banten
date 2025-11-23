<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PsychologyTest;
use App\Models\Sim;
use App\Models\GroupSim;
use App\Helpers\FormatResponseJson;
use Illuminate\Support\Facades\Validator;

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
        ]);

        if ($validator->fails()) {
            return FormatResponseJson::error($validator->errors(), 'Validasi gagal', 422);
        }

        try {
            $data = PsychologyTest::create($request->all());
            return FormatResponseJson::success($data, 'Data berhasil ditambahkan');
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
            'gender' => 'required|in:male,female',
            'place_of_birth' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'age' => 'nullable|integer|min:0|max:127',
            'sim_id' => 'nullable|integer',
            'group_sim_id' => 'nullable|integer',
            'domicile' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return FormatResponseJson::error($validator->errors(), 'Validasi gagal', 422);
        }

        try {
            $data = PsychologyTest::findOrFail($id);
            $data->update($request->all());
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
}