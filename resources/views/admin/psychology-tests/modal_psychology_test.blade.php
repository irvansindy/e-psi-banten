<!-- resources/views/admin/psychology-tests/modal_psychology_test.blade.php -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formModalLabel">Form Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="dataForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="dataId" name="id">

                    <!-- Photo Upload Section -->
                    <div class="mb-3">
                        <label for="photo" class="form-label">Foto <span class="text-danger" id="photoRequired">*</span></label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/jpeg,image/jpg,image/png,image/webp">
                        <small class="text-muted">Format: JPEG, JPG, PNG, WebP (Max: 2MB). Akan dikonversi ke WebP.</small>
                        <div class="invalid-feedback"></div>

                        <!-- Photo Preview -->
                        <div id="photoPreview" class="mt-2" style="display: none;">
                            <img id="photoPreviewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                            <button type="button" class="btn btn-sm btn-danger mt-2" id="removePhotoBtn">
                                <i class="fas fa-times"></i> Hapus Foto
                            </button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Pilih Gender</option>
                                <option value="male">Laki-laki</option>
                                <option value="female">Perempuan</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="place_of_birth" class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control" id="place_of_birth" name="place_of_birth">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="age" class="form-label">Umur</label>
                            <input type="number" class="form-control" id="age" name="age" min="0"
                                max="127" readonly>
                            <small class="text-muted">Otomatis terisi dari tanggal lahir</small>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label for="sim_id" class="form-label">Jenis SIM</label>
                            <select class="form-select" id="sim_id" name="sim_id">
                                <option value="">Pilih Jenis SIM</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label for="group_sim_id" class="form-label">Golongan SIM</label>
                            <select class="form-select" id="group_sim_id" name="group_sim_id">
                                <option value="">Pilih Golongan SIM</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="domicile" class="form-label">Domisili</label>
                        <input type="text" class="form-control" id="domicile" name="domicile">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"
                            aria-hidden="true"></span>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>