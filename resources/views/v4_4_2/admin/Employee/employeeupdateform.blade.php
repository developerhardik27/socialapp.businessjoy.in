@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')

@section('page_title')
    {{ config('app.name') }} - Update Employee
@endsection
@section('title')
    Update Employee
@endsection

@section('style')
    <style>
        select+.btn-group {
            border: 1px solid #ced4da;
            width: 100%;
            border-radius: 5px;
        }

        .dropdown-menu {
            width: 100%;
        }

        .file-preview div {
            margin-bottom: 5px;
        }
    </style>
@endsection

@section('form-content')
    <form id="employeeupdateform" enctype="multipart/form-data">
        @csrf
        <div class="col-12 p-0">
            <div class="card">
                <div class="card-header bg-light">
                    <h6>Basic Details</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-sm-6 mb-2">
                                <input type="hidden" name="token" class="form-control"
                                    value="{{ session('api_token') }}" />
                                <input type="hidden" name="user_id" class="form-control"
                                    value="{{ session('user_id') }}" />
                                <input type="hidden" name="company_id" class="form-control"
                                    value="{{ session('company_id') }}" />
                                <input type="hidden" name="emp_id" id="emp_id" value="{{ $id }}" />

                                <label>First Name</label><span style="color:red;">*</span>
                                <input type="text" name="first_name" id="first_name" maxlength="255" class="form-control"
                                    placeholder="First Name" />
                                <span class="error-msg" id="error-first_name" style="color:red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label>Middle Name</label>
                                <input type="text" name="middle_name" id="middle_name" maxlength="255"
                                    class="form-control" placeholder="Middle Name" />
                                <span class="error-msg" id="error-middle_name" style="color:red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label>Surname</label>
                                <input type="text" name="surname" id="surname" maxlength="255" class="form-control"
                                    placeholder="Surname" />
                                <span class="error-msg" id="error-surname" style="color:red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label>Email</label>
                                <input type="email" name="email" id="email" maxlength="255" class="form-control"
                                    placeholder="Email" />
                                <span class="error-msg" id="error-email" style="color:red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label>Mobile</label>
                                <input type="number" name="mobile" id="mobile" maxlength="20" class="form-control"
                                    placeholder="Mobile Number" />
                                <span class="error-msg" id="error-mobile" style="color:red"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header bg-light">
                    <h6>Address</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-sm-6 mb-2">
                                <label for="country">Select Country</label>
                                <select class="form-control" name="country" id="country">
                                    <option selected="" disabled="">Select your Country</option>
                                </select>
                                <span class="error-msg" id="error-country" style="color: red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label for="state">Select State</label>
                                <select class="form-control" name="state" id="state">
                                    <option selected disabled="">Select your State</option>
                                </select>
                                <span class="error-msg" id="error-state" style="color: red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label for="city">Select City</label>
                                <select class="form-control" name="city" id="city">
                                    <option selected disabled="">Select your City</option>
                                </select>
                                <span class="error-msg" id="error-city" style="color: red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label for="pincode">Pincode</label>
                                <input type="text" name="pincode" id='pincode' class="form-control"
                                    placeholder="Pin Code" />
                                <span class="error-msg" id="error-pincode" style="color: red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label for="house_no_building_name">House no./ Building Name</label>
                                <textarea class="form-control input" name='house_no_building_name' id="house_no_building_name" rows="2"
                                    placeholder="e.g. 2nd floor/ 04 ABC Appartment"></textarea>
                                <span class="error-msg" id="error-house_no_building_name" style="color: red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label for="road_name_area_colony">Road Name/Area/Colony</label>
                                <textarea class="form-control input" name='road_name_area_colony' id="road_name_area_colony" rows="2"
                                    placeholder="e.g. sardar patel road, jagatpur"></textarea>
                                <span class="error-msg" id="error-road_name_area_colony" style="color: red"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header bg-light">
                    <h6>Bank Details</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-sm-6 mb-2">
                                <label for="holder_name">Holder Name</label>
                                <input id="holder_name" type="text" name="holder_name" class="form-control"
                                    placeholder="Holder Name" />
                                <span class="error-msg" id="error-holder_name" style="color: red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label for="account_number">Account Number</label>
                                <input type="text" name="account_number" class="form-control" id="account_number"
                                    value="" placeholder="Account Number" />
                                <span class="error-msg" id="error-account_number" style="color: red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label for="swift_code">Swift Code</label>
                                <input type="text" name="swift_code" class="form-control" id="swift_code"
                                    value="" placeholder="Swift Code" />
                                <span class="error-msg" id="error-swift_code" style="color: red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label for="ifsc_code">IFSC Code</label>
                                <input type="text" id="ifsc_code" name="ifsc_code" class="form-control"
                                    placeholder="IFSC Code" />
                                <span class="error-msg" id="error-ifsc_code" style="color: red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label for="bank_name">Bank Name</label>
                                <input type="text" id="bank_name" name="bank_name" class="form-control"
                                    placeholder="Bank Name" />
                                <span class="error-msg" id="error-bank_name" style="color: red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label for="branch_name">Branch Name</label>
                                <input type="text" id="branch_name" name="branch_name" class="form-control"
                                    placeholder="Branch Name" />
                                <span class="error-msg" id="error-branch_name" style="color: red"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header bg-light">
                    <h6>Documents</h6>
                </div>
                <div class="card-body">
                    <div class="form-row">

                        {{-- CV --}}
                        <div class="col-sm-6 mb-2">
                            <label for="cv_resume">CV / Resume</label>

                            <div class="form-control d-flex align-items-center p-0 overflow-hidden mt-1"
                                style="height:38px;">

                                <label for="cv_resume"
                                    class="bg-light border-right px-3 h-100 d-flex align-items-center text-muted flex-shrink-0 mb-0"
                                    style="font-size:13px; white-space:nowrap; cursor:pointer;">
                                    Choose file
                                </label>

                                <span id="cvFileName" class="px-2 text-muted text-truncate flex-grow-1"
                                    style="font-size:13px;">
                                    No file chosen
                                </span>

                                <a id="cvViewBtn" href="#" target="_blank"
                                    class="btn btn-sm btn-info text-white mr-1" style="display:none;">
                                    <i class="ri-eye-fill"></i>
                                </a>

                                <button type="button" class="btn btn-sm btn-danger mr-1" id="cvDeleteBtn"
                                    style="display:none;">
                                    <i class="ri-delete-bin-line"></i>
                                </button>

                                <input type="file" name="cv_resume" id="cv_resume" accept=".pdf,.doc,.docx"
                                    style="display:none;">
                                <input type="hidden" name="existing_cv_resume" id="existing_cv_resume" value="">
                            </div>
                            <span class="error-msg" id="error-cv_resume" style="color:red"></span>
                        </div>

                        {{-- ID Proofs --}}
                        <div class="col-sm-6 mb-2">
                            <button type="button" class="btn btn-primary btn-sm m-0 mr-2 addinput"
                                data-input-type="id_proofs">
                                <i class="ri-add-line"></i>
                            </button>
                            <label>ID Proofs</label>
                            <div id="id_proofs_preview" class="file-preview"></div>
                            <div id="id_proofs-wrapper"></div>
                        </div>

                        {{-- Address Proofs --}}
                        <div class="col-sm-6 mb-2">
                            <button type="button" class="btn btn-primary btn-sm m-0 mr-2 addinput"
                                data-input-type="address_proofs">
                                <i class="ri-add-line"></i>
                            </button>
                            <label>Address Proofs</label>
                            <div id="address_proofs_preview" class="file-preview"></div>
                            <div id="address_proofs-wrapper"></div>
                        </div>

                        {{-- Other Attachments --}}
                        <div class="col-sm-6 mb-2">
                            <button type="button" class="btn btn-primary btn-sm m-0 mr-2 addinput"
                                data-input-type="other_attachments">
                                <i class="ri-add-line"></i>
                            </button>
                            <label>Other Attachments</label>
                            <div id="other_attachments_preview" class="file-preview"></div>
                            <div id="other_attachments-wrapper"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-12">
                    <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Cancel"
                        id="cancelbtn" class="btn btn-secondary float-right">Cancel</button>
                    <button type="reset" id="resetBtn" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Reset" class="btn iq-bg-danger float-right mr-2">
                        Reset
                    </button>
                    <button type="submit" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Update Employee" class="btn btn-primary float-right my-0">
                        Update
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('ajax')
    <script>
        $(document).ready(function() {
            const emp_id = $('#emp_id').val();

            // Track whether a new file is selected or existing is loaded
            let cvState = 'empty'; // 'empty' | 'existing' | 'new'

            let proofOptions = '<option value="">Select Proof</option>';

            $.ajax({
                type: 'GET',
                url: "{{ route('proofsname') }}",
                data: {
                    token: "{{ session()->get('api_token') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    user_id: "{{ session()->get('user_id') }}"
                },
                success: function(response) {
                    if (response.status == 200 && response.proof.length > 0) {
                        $.each(response.proof, function(i, v) {
                            proofOptions +=
                                `<option value="${v.proof_name}">${v.proof_name}</option>`;
                        });
                    }
                }
            });

            // Show country data in dropdown
            $.ajax({
                type: 'GET',
                url: "{{ route('country.index') }}",
                data: {
                    token: "{{ session()->get('api_token') }}",
                },
                success: function(response) {
                    if (response.status == 200 && response.country != '') {
                        $.each(response.country, function(key, value) {
                            $('#country').append(
                                `<option value='${value.id}'> ${value.country_name}</option>`
                            )
                        });
                    } else {
                        $('#country').append(`<option disabled> No Data Found</option>`);
                    }
                },
                error: function(error) {
                    console.error('Error:', error);
                }
            });

            // ── CV Helper Functions ──────────────────────────────────────

            // Reset bar to empty state
            function cvReset() {
                cvState = 'empty';
                $('#cvFileName').text('No file chosen').addClass('text-muted');
                $('#cvViewBtn').hide().attr('href', '#');
                $('#cvDeleteBtn').hide();
                $('#existing_cv_resume').val('');
            }

            // Show existing saved CV
            function cvShowExisting(filePath) {
                cvState = 'existing';
                const fileName = filePath.split('/').pop();
                $('#cvFileName').text(fileName).removeClass('text-muted');
                $('#cvViewBtn').attr('href', '/' + filePath).show();
                $('#cvDeleteBtn').show();
                $('#existing_cv_resume').val(filePath);
            }

            // Show newly selected file
            function cvShowNew(fileName) {
                cvState = 'new';
                $('#cvFileName').text(fileName).removeClass('text-muted');
                $('#cvViewBtn').hide(); // no view for unsaved file
                $('#cvDeleteBtn').show();
            }

            // ── CV Event Handlers ────────────────────────────────────────

            // User selects a new file
            $(document).on('change', '#cv_resume', function() {
                const file = this.files[0];
                if (file) {
                    cvShowNew(file.name);
                } else {
                    // If no file picked, restore previous state
                    const existingPath = $('#existing_cv_resume').val();
                    if (existingPath) {
                        cvShowExisting(existingPath);
                    } else {
                        cvReset();
                    }
                }
            });

            // Delete button — handles both existing and new
            $(document).on('click', '#cvDeleteBtn', function() {

                showConfirmationDialog(
                    'Are you sure?',
                    'Do you want to remove this CV?',
                    'Yes, remove it!',
                    'No, Cancel!',
                    'warning',
                    () => {

                        const filePath = $('#existing_cv_resume').val();

                        // ✅ ALWAYS check hidden field (not cvState)
                        if (filePath) {
                            $('<input>', {
                                type: 'hidden',
                                name: 'removed_files[]',
                                value: filePath
                            }).appendTo('#employeeupdateform');
                        }

                        // clear file input
                        $('#cv_resume').val('');

                        // reset UI
                        cvReset();
                    }
                );
            });

            // ── Load Employee Data ───────────────────────────────────────

            function loadData() {
                $.get("{{ route('employee.edit', '__id__') }}".replace('__id__', emp_id), {
                    token: "{{ session('api_token') }}",
                    company_id: "{{ session('company_id') }}",
                    user_id: "{{ session('user_id') }}"
                }, function(res) {
                    if (res.status == 200) {
                        loaderhide();
                        const data = res.data;
                        $('#first_name').val(data.first_name);
                        $('#middle_name').val(data.middle_name);
                        $('#surname').val(data.surname);
                        $('#email').val(data.email);
                        $('#mobile').val(data.mobile);
                        $('#pincode').val(data.pincode);
                        $('#house_no_building_name').val(data.house_no_building_name);
                        $('#road_name_area_colony').val(data.road_name_area_colony);
                        $('#holder_name').val(data.holder_name);
                        $('#account_number').val(data.account_no);
                        $('#swift_code').val(data.swift_code);
                        $('#ifsc_code').val(data.ifsc_code);
                        $('#branch_name').val(data.branch_name);
                        $('#bank_name').val(data.bank_name);

                        country = data.country_id;
                        state = data.state_id;
                        city = data.city_id;
                        if (country != null) {
                            $('#country').val(country);
                        }
                        loadstate(country, state);
                        loadcity(state, city);

                        // CV
                        if (data.cv_resume) {
                            cvShowExisting(data.cv_resume);
                        } else {
                            cvReset();
                        }

                        renderFilePreview('#id_proofs_preview', data.id_proofs, 'id_proofs');
                        renderFilePreview('#address_proofs_preview', data.address_proofs, 'address_proofs');
                        renderFilePreview('#other_attachments_preview', data.other_attachments,
                            'other_attachments');
                    }
                });
            }

            loadData();

            // ── Country / State / City ───────────────────────────────────

            function loadstate(country, state) {
                let stateSearchUrl = "{{ route('state.search', '__countryId__') }}".replace('__countryId__',
                    country);
                $.ajax({
                    type: 'GET',
                    url: stateSearchUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.state != '') {
                            $.each(response.state, function(key, value) {
                                $('#state').append(
                                    `<option value='${value.id}'> ${value.state_name}</option>`
                                    )
                            });
                            $('#state').val(state);
                        } else {
                            $('#state').append(`<option disabled> No Data Found</option>`);
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            }

            function loadcity(state, city) {
                let citySearchUrl = "{{ route('city.search', '__stateId__') }}".replace('__stateId__', state);
                $.ajax({
                    type: 'GET',
                    url: citySearchUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.city != '') {
                            $.each(response.city, function(key, value) {
                                $('#city').append(
                                    `<option value='${value.id}'> ${value.city_name}</option>`
                                    )
                            });
                            $('#city').val(city);
                        } else {
                            $('#city').append(`<option disabled> No Data Found</option>`);
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            }

            $('#country').on('change', function() {
                loadershow();
                var country = $(this).val();
                $('#state').html(`<option selected="" disabled="">Select your State</option>`);
                stateSearchUrl = "{{ route('state.search', '__countryId__') }}".replace('__countryId__',
                    country);
                $.ajax({
                    type: 'GET',
                    url: stateSearchUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.state != '') {
                            $.each(response.state, function(key, value) {
                                $('#state').append(
                                    `<option value='${value.id}'> ${value.state_name}</option>`
                                    )
                            })
                        } else {
                            $('#state').append(`<option disabled> No Data Found</option>`);
                        }
                        loaderhide();
                    },
                    error: function(error) {
                        loaderhide();
                        console.error('Error:', error);
                    }
                });
            });

            $('#state').on('change', function() {
                loadershow();
                $('#city').html(`<option selected="" disabled="">Select your City</option>`);
                var state = $(this).val();
                citySearchUrl = "{{ route('city.search', '__stateId__') }}".replace('__stateId__', state);
                $.ajax({
                    type: 'GET',
                    url: citySearchUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.city != '') {
                            $.each(response.city, function(key, value) {
                                $('#city').append(
                                    `<option value='${value.id}'> ${value.city_name}</option>`
                                    )
                            });
                        } else {
                            $('#city').append(`<option disabled> No Data Found</option>`);
                        }
                        loaderhide();
                    },
                    error: function(error) {
                        loaderhide();
                        console.error('Error:', error);
                    }
                });
            });

            // ── File Preview (ID / Address / Other) ─────────────────────

            function getArray(value) {
                if (!value) return [];
                if (Array.isArray(value)) return value;
                try {
                    return JSON.parse(value);
                } catch {
                    return [];
                }
            }

            function renderFilePreview(container, files, inputName) {
                const c = $(container);
                c.empty();
                const arr = getArray(files);
                arr.forEach(item => {
                    const filePath = item.file_path;
                    const proofType = item.proof_type ?? '';
                    const fileName = filePath.split('/').pop();

                    let proofSelect = '';
                    if (inputName !== 'other_attachments') {
                        proofSelect = `
                            <select name="${inputName}_type_existing[]" class="form-control mr-2">
                                ${proofOptions}
                            </select>
                        `;
                    }

                    const el = $(`
                        <div class="border rounded bg-white mb-2 px-2 py-2 d-flex align-items-center"
                            data-type="existing">
                            ${proofSelect}
                            <div class="flex-grow-1 text-truncate mr-2">
                                <b>${fileName}</b>
                            </div>
                            <a href="/${filePath}" target="_blank"
                                class="btn btn-sm btn-info text-white mr-1">
                                <i class="ri-eye-fill"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger remove-file">
                                <i class="ri-delete-bin-fill"></i>
                            </button>
                            <input type="hidden" name="${inputName}_existing_path[]" value="${filePath}">
                        </div>
                    `);

                    el.find('select').val(proofType);
                    c.append(el);
                });
            }

            function addFileInput(wrapperId, inputName) {
                let proofSelect = '';
                if (inputName == 'id_proofs' || inputName == 'address_proofs') {
                    proofSelect = `
                        <select name="${inputName}_type[]" class="form-control mr-2">
                            ${proofOptions}
                        </select>
                    `;
                }
                const html = `
                    <div class="border rounded bg-white mb-2 px-3 py-2 d-flex align-items-center"
                        data-type="new">
                        ${proofSelect}
                        <input type="file" name="${inputName}[]" class="form-control-file mr-3"/>
                        <button type="button" class="btn btn-danger remove-file">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                `;
                $(wrapperId).append(html);
            }

            // ── Other Actions ────────────────────────────────────────────

            $('#cancelbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.employee') }}";
            });

            $(document).on('click', '.remove-file', function() {
                const parent = $(this).closest('[data-type]');
                const type = parent.data('type');
                showConfirmationDialog(
                    'Are you sure?',
                    'Do you want to remove this file?',
                    'Yes, remove it!',
                    'No, Cancel!',
                    'warning',
                    () => {
                        if (type == 'existing') {
                            $('<input>', {
                                type: 'hidden',
                                name: 'removed_files[]',
                                value: parent.find('input[type=hidden]').val()
                            }).appendTo('#employeeupdateform');
                        }
                        parent.remove();
                    }
                );
            });

            $('.addinput').click(function() {
                fileInput = $(this).data('input-type');
                wrapper = `${fileInput}-wrapper`;
                addFileInput(`#${wrapper}`, fileInput);
            });

            $('#employeeupdateform').submit(function(e) {
                e.preventDefault();
                $('.error-msg').text('');
                let formData = new FormData(this);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('employee.update', '__id__') }}".replace('__id__', emp_id),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        loaderhide();
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });
                            window.location = "{{ route('admin.employee') }}";
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseJSON);
                    }
                });
            });
        });
    </script>
@endpush
