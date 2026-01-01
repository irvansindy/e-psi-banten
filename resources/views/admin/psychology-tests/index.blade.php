@extends('layouts.app', ['title' => 'Data Tes Psikologi - e-Psi Banten'])
@section('content')
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Data Tes Psikologi</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Data Tes Psikologi</li>
            </ol>

            <div class="card mb-4">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <i class="fas fa-table me-1"></i>
                            Daftar Tes Psikologi
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary btn-sm" id="btnCreate">
                                <i class="fas fa-plus"></i> Tambah Data
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <input type="text" id="searchInput" class="form-control"
                            placeholder="Cari nama, tempat lahir, atau gender...">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama</th>
                                    <th>Gender</th>
                                    <th>Tempat/Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>SIM ID</th>
                                    <th>Group SIM ID</th>
                                    <th>Domisili</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="spinner-border" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="pagination" class="mt-3"></div>
                </div>
            </div>
        </div>
    </main>
    @include('admin.psychology-tests.modal_psychology_test')
    @push('scripts')
        @include('admin.psychology-tests.js.psychology-test_js')
    @endpush
@endsection
