<!-- resources/views/admin/psychology-tests/js/psychology-test_js.blade.php -->
<script>
    let currentPage = 1;
    let searchTimeout = null;
    let modal = null;
    let simsData = [];
    let groupSimsData = [];
    let isEditMode = false;
    let currentPhotoUrl = null;

    $(document).ready(function() {
        // Initialize modal
        modal = new bootstrap.Modal($('#formModal'));

        // Load dropdown data first
        loadDropdownData();

        // Load data on page load
        loadData();

        // Button create
        $('#btnCreate').on('click', function() {
            createData();
        });

        // Search functionality
        $('#searchInput').on('keyup', function() {
            clearTimeout(searchTimeout);
            const search = $(this).val();
            searchTimeout = setTimeout(function() {
                loadData(1, search);
            }, 500);
        });

        // Form submit
        $('#dataForm').on('submit', function(e) {
            e.preventDefault();
            submitForm();
        });

        // Reset form when modal is hidden
        $('#formModal').on('hidden.bs.modal', function() {
            $('#dataForm')[0].reset();
            disableForm(false);
            clearValidation();
            resetPhotoPreview();
            isEditMode = false;
            currentPhotoUrl = null;
        });

        // Calculate age when date of birth changes
        $('#date_of_birth').on('change', function() {
            calculateAge();
        });

        // Photo preview
        $('#photo').on('change', function(e) {
            previewPhoto(e.target.files[0]);
        });

        // Remove photo button
        $('#removePhotoBtn').on('click', function() {
            resetPhotoPreview();
        });

        // TAMBAH: Validasi input NIK hanya angka
        $('#nik').on('input', function() {
            // Hapus karakter selain angka
            this.value = this.value.replace(/[^0-9]/g, '');

            // Batasi maksimal 16 digit
            if (this.value.length > 16) {
                this.value = this.value.slice(0, 16);
            }
        });

        // TAMBAH: Validasi paste di NIK
        $('#nik').on('paste', function(e) {
            setTimeout(() => {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16);
            }, 0);
        });
    });

    // Load dropdown data
    function loadDropdownData() {
        $.ajax({
            url: '{{ route('psychology-tests.dropdown-data') }}',
            type: 'GET',
            success: function(result) {
                if (result.meta.status === 'success') {
                    simsData = result.data.sims;
                    groupSimsData = result.data.group_sims;
                    populateDropdowns();
                }
            },
            error: function(xhr) {
                console.error('Error loading dropdown data:', xhr);
            }
        });
    }

    // Populate dropdowns
    function populateDropdowns() {
        // Populate SIM dropdown
        let simOptions = '<option value="">Pilih Jenis SIM</option>';
        $.each(simsData, function(index, sim) {
            simOptions += `<option value="${sim.id}">${sim.name}</option>`;
        });
        $('#sim_id').html(simOptions);

        // Populate Group SIM dropdown
        let groupSimOptions = '<option value="">Pilih Golongan SIM</option>';
        $.each(groupSimsData, function(index, groupSim) {
            groupSimOptions += `<option value="${groupSim.id}">${groupSim.name}</option>`;
        });
        $('#group_sim_id').html(groupSimOptions);
    }

    // Load data
    function loadData(page = 1, search = '') {
        currentPage = page;

        $('#tableBody').html(`
            <tr>
                <td colspan="9" class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `);

        $.ajax({
            url: '{{ route('psychology-tests.data') }}',
            type: 'GET',
            data: {
                page: page,
                search: search
            },
            success: function(result) {
                if (result.meta.status === 'success') {
                    renderTable(result.data);
                    renderPagination(result.data);
                } else {
                    showAlert('danger', result.meta.message);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                showAlert('danger', 'Terjadi kesalahan saat memuat data');
            }
        });
    }

    // Render table
    function renderTable(data) {
        if (data.data.length === 0) {
            $('#tableBody').html(`
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data</td>
                </tr>
            `);
            return;
        }

        let html = '';
        $.each(data.data, function(index, item) {
            const no = (data.current_page - 1) * data.per_page + index + 1;
            const gender = item.gender === 'male' ? 'Laki-laki' : 'Perempuan';
            const birthInfo = `${item.place_of_birth || '-'}, ${item.date_of_birth || '-'}`;
            const simName = item.sim ? item.sim.name : '-';
            const groupSimName = item.group_sim ? item.group_sim.name : '-';

            html += `
                <tr>
                    <td>${no}</td>
                    <td>${item.name}</td>
                    <td>${item.nik || '-'}</td>
                    <td>${gender}</td>
                    <td>${birthInfo}</td>
                    <td>${item.age || '-'}</td>
                    <td>${simName}</td>
                    <td>${groupSimName}</td>
                    <td>${item.domicile || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewData(${item.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editData(${item.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-success" onclick="printPDF(${item.id})" title="Cetak PDF">
                            <i class="fas fa-print"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteData(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#tableBody').html(html);
    }

    // Render pagination
    function renderPagination(data) {
        let html = '<nav><ul class="pagination justify-content-center">';

        if (data.prev_page_url) {
            html += `<li class="page-item">
                <a class="page-link" href="#" data-page="${data.current_page - 1}">Previous</a>
            </li>`;
        }

        for (let i = 1; i <= data.last_page; i++) {
            const active = i === data.current_page ? 'active' : '';
            html += `<li class="page-item ${active}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }

        if (data.next_page_url) {
            html += `<li class="page-item">
                <a class="page-link" href="#" data-page="${data.current_page + 1}">Next</a>
            </li>`;
        }

        html += '</ul></nav>';
        $('#pagination').html(html);

        // Pagination click event
        $('#pagination a.page-link').on('click', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            const search = $('#searchInput').val();
            loadData(page, search);
        });
    }

    // Create new data
    function createData() {
        $('#formModalLabel').text('Tambah Data Tes Psikologi');
        $('#dataForm')[0].reset();
        $('#dataId').val('');
        clearValidation();
        populateDropdowns();
        resetPhotoPreview();
        isEditMode = false;
        currentPhotoUrl = null;

        // Photo required untuk create
        $('#photo').prop('required', true);
        $('#photoRequired').show();

        modal.show();
    }

    // View data
    function viewData(id) {
        $.ajax({
            url: `{{ url('psychology-tests') }}/${id}`,
            type: 'GET',
            success: function(result) {
                if (result.meta.status === 'success') {
                    $('#formModalLabel').text('Detail Data Tes Psikologi');
                    populateDropdowns();
                    fillForm(result.data);
                    disableForm(true);

                    // Show existing photo if available
                    if (result.data.photo_url) {
                        showExistingPhoto(result.data.photo_url);
                    }

                    modal.show();
                }
            },
            error: function(xhr) {
                showAlert('danger', 'Data tidak ditemukan');
            }
        });
    }

    // Edit data
    function editData(id) {
        $.ajax({
            url: `{{ url('psychology-tests') }}/${id}`,
            type: 'GET',
            success: function(result) {
                if (result.meta.status === 'success') {
                    $('#formModalLabel').text('Edit Data Tes Psikologi');
                    populateDropdowns();
                    fillForm(result.data);
                    disableForm(false);
                    isEditMode = true;
                    currentPhotoUrl = result.data.photo_url;

                    // Photo optional untuk edit
                    $('#photo').prop('required', false);
                    $('#photoRequired').hide();

                    // Show existing photo if available
                    if (result.data.photo_url) {
                        showExistingPhoto(result.data.photo_url);
                    }

                    modal.show();
                }
            },
            error: function(xhr) {
                showAlert('danger', 'Data tidak ditemukan');
            }
        });
    }

    // Print PDF
    function printPDF(id) {
        window.open(`{{ url('psychology-tests') }}/${id}/pdf`);
    }

    // Fill form
    function fillForm(data) {
        $('#dataId').val(data.id);
        $('#name').val(data.name);
        $('#nik').val(data.nik || '')
        $('#gender').val(data.gender);
        $('#place_of_birth').val(data.place_of_birth || '');
        $('#date_of_birth').val(data.date_of_birth || '');
        $('#age').val(data.age || '');
        $('#sim_id').val(data.sim_id || '');
        $('#group_sim_id').val(data.group_sim_id || '');
        $('#domicile').val(data.domicile || '');
    }

    // Disable form
    function disableForm(disabled) {
        $('#dataForm input, #dataForm select').prop('disabled', disabled);

        // Age field should always be readonly (not disabled for view mode)
        if (!disabled) {
            $('#age').prop('readonly', true);
        }

        if (disabled) {
            $('#submitBtn').hide();
            $('#removePhotoBtn').hide();
        } else {
            $('#submitBtn').show();
        }
    }

    // Delete data
    function deleteData(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            $.ajax({
                url: `{{ url('psychology-tests') }}/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(result) {
                    if (result.meta.status === 'success') {
                        showAlert('success', result.meta.message);
                        loadData(currentPage, $('#searchInput').val());
                    } else {
                        showAlert('danger', result.meta.message);
                    }
                },
                error: function(xhr) {
                    showAlert('danger', 'Terjadi kesalahan saat menghapus data');
                }
            });
        }
    }

    // Submit form
    function submitForm() {
        clearValidation();

        const $submitBtn = $('#submitBtn');
        const $spinner = $submitBtn.find('.spinner-border');

        $spinner.removeClass('d-none');
        $submitBtn.prop('disabled', true);

        const formData = new FormData($('#dataForm')[0]);
        const id = $('#dataId').val();

        // Tambahkan _method untuk PUT jika edit
        if (id) {
            formData.append('_method', 'PUT');
        }

        const url = id ? `{{ url('psychology-tests') }}/${id}` : '{{ route('psychology-tests.store') }}';

        $.ajax({
            url: url,
            type: 'POST',
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(result) {
                $spinner.addClass('d-none');
                $submitBtn.prop('disabled', false);

                if (result.meta.status === 'success') {
                    modal.hide();
                    showAlert('success', result.meta.message);
                    loadData(currentPage, $('#searchInput').val());
                } else {
                    if (result.meta.code === 422) {
                        showValidationErrors(result.data);
                    } else {
                        showAlert('danger', result.meta.message);
                    }
                }
            },
            error: function(xhr) {
                $spinner.addClass('d-none');
                $submitBtn.prop('disabled', false);

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.data;
                    showValidationErrors(errors);
                } else {
                    showAlert('danger', 'Terjadi kesalahan saat menyimpan data');
                }
            }
        });
    }

    // Show validation errors
    function showValidationErrors(errors) {
        $.each(errors, function(key, messages) {
            const $input = $(`#${key}`);
            if ($input.length) {
                $input.addClass('is-invalid');
                const $feedback = $input.parent().find('.invalid-feedback');
                if ($feedback.length) {
                    $feedback.text(messages[0]);
                }
            }
        });
    }

    // Clear validation
    function clearValidation() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    // Show alert
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        $('.container-fluid').prepend(alertHtml);

        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }

    // Calculate age from date of birth
    // function calculateAge() {
    //     const dateOfBirth = $('#date_of_birth').val();

    //     if (!dateOfBirth) {
    //         $('#age').val('');
    //         return;
    //     }

    //     const birthDate = new Date(dateOfBirth);
    //     const today = new Date();

    //     let age = today
    //     const monthDiff = today.getMonth() - birthDate.getMonth();

    //     if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    //         age--;
    //     }

    //     $('#age').val(age >= 0 ? age : '');
    // }

    // Calculate age from date of birth
    function calculateAge() {
        const dateOfBirth = $('#date_of_birth').val();

        if (!dateOfBirth) {
            $('#age').val('');
            return;
        }

        const birthDate = new Date(dateOfBirth);
        const today = new Date();

        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        // Adjust age if birthday hasn't occurred this year
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        // Set age value
        $('#age').val(age >= 0 ? age : '');
    }

    // Preview photo
    function previewPhoto(file) {
        if (file) {
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                showAlert('danger', 'Format file tidak valid. Gunakan JPEG, JPG, PNG, atau WebP');
                $('#photo').val('');
                return;
            }

            // Validate file size (2MB)
            if (file.size > 2048 * 1024) {
                showAlert('danger', 'Ukuran file maksimal 2MB');
                $('#photo').val('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                $('#photoPreviewImg').attr('src', e.target.result);
                $('#photoPreview').show();
                $('#removePhotoBtn').show();
            };
            reader.readAsDataURL(file);
        }
    }

    // Show existing photo
    function showExistingPhoto(url) {
        $('#photoPreviewImg').attr('src', url);
        $('#photoPreview').show();
    }

    // Reset photo preview
    function resetPhotoPreview() {
        $('#photo').val('');
        $('#photoPreviewImg').attr('src', '');
        $('#photoPreview').hide();
        $('#removePhotoBtn').hide();
    }
</script>
