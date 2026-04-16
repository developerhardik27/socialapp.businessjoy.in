@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')

@section('page_title')
    {{ config('app.name') }} - Add New Employee
@endsection
@section('title')
    New Employee
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
    </style>
@endsection


@section('form-content')
    <form id="employeeform" name="employeeform" enctype="multipart/form-data">
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

                                <label>First Name</label><span style="color:red;">*</span>
                                <input type="text" name="first_name" id="first_name" maxlength="255" class="form-control"
                                    placeholder="First Name" />
                                <span class="error-msg" id="error-first_name" style="color:red"></span>

                            </div>

                            <div class="col-sm-6 mb-2">
                                <label>Middle Name</label><span style="color:red;">*</span>
                                <input type="text" name="middle_name" id="middle_name" maxlength="255"
                                    class="form-control" placeholder="Middle Name" />
                                <span class="error-msg" id="error-middle_name" style="color:red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label>Surname</label><span style="color:red;">*</span>
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
                                <textarea class="form-control  input" name='house_no_building_name' id="house_no_building_name" rows="2"
                                    placeholder="e.g. 2nd floor/ 04 ABC Appartment"></textarea>
                                <span class="error-msg" id="error-house_no_building_name" style="color: red"></span>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label for="road_name_area_colony">Road Name/Area/Colony</label>
                                <textarea class="form-control  input" name='road_name_area_colony' id="road_name_area_colony" rows="2"
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
                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-sm-6 mb-2">
                                <label for="cv_resume">CV / Resume</label>
                                <div
                                    class="input-group mt-1 border rounded bg-white mb-2 px-3 py-2 d-flex align-items-center">
                                    <input type="file" name="cv_resume" id="cv_resume" accept=".pdf,.doc,.docx" />
                                    <button type="button" class="btn btn-danger remove-cv-file" style="display:none">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    <span class="error-msg" id="error-cv_resume" style="color:red"></span>
                                </div>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <button type="button" class="btn btn-primary btn-sm m-0 mr-2 rounded addinput"
                                    id="add-id_proofs" data-toggle="tooltip" data-placement="bottom"
                                    data-input-type="id_proofs" data-original-title="Add Id Proofs"><i
                                        class="ri-add-line"></i></button>
                                <label>ID Proofs</label><br>
                                <div id="id_proofs-wrapper">
                                    <div
                                        class="input-group mt-1 mb-2 border rounded bg-white px-3 py-2 d-flex align-items-center">
                                        <select name="id_proofs_type[]" class="form-control mr-2">
                                            <option value="">Select Proof</option>
                                        </select>

                                        <input type="file" name="id_proofs[]" />
                                        <span class="error-msg" id="error-id_proofs" style="color:red"></span>
                                    </div>

                                </div>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <button type="button"
                                    id="add-address_proofs"class="btn btn-primary btn-sm m-0 mr-2 rounded addinput"
                                    data-input-type="address_proofs" data-toggle="tooltip" data-placement="bottom"
                                    data-original-title="Address Proofs"><i class="ri-add-line"></i></button>
                                <label>Address Proofs</label><br>
                                <div id="address_proofs-wrapper">
                                    <div
                                        class="input-group mt-1 mb-2 border rounded bg-white px-3 py-2 d-flex align-items-center">
                                        <select name="address_proofs_type[]" class="form-control mr-2">
                                            <option value="">Select Proof</option>
                                        </select>

                                        <input type="file" name="address_proofs[]" />
                                        <span class="error-msg" id="error-address_proofs" style="color:red"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 mb-2">
                                <button type="button"
                                    id="add-other_attachments"class="btn btn-primary btn-sm m-0 mr-2 rounded addinput"
                                    data-input-type="other_attachments" data-toggle="tooltip" data-placement="bottom"
                                    data-original-title="Add Other Attachments"><i class="ri-add-line"></i></button>
                                <label>Other Attachments</label><br>
                                <div id="other_attachments-wrapper">
                                    <div
                                        class="input-group mt-1 mb-2 d-flex border rounded bg-white mb-2 px-3 py-2 d-flex align-items-center ">
                                        <input type="file" id="other_attachments" name="other_attachments[]" /><br>
                                        <span class="error-msg" id="error-other_attachments" style="color:red"></span>
                                    </div>
                                </div>
                            </div>
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
                        data-original-title="Save Employee" class="btn btn-primary float-right my-0">
                        Save
                    </button>
                </div>
            </div>
        </div>

    </form>
@endsection


@push('ajax')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>

    <script>
        $('document').ready(function() {
            loaderhide();
            // companyId and userId both are   in every ajax request for all action *************
            // response status == 200 that means response succesfully recieved
            // response status == 500 that means database not found
            // response status == 422 that means api has not got valid or   data

            let proofOptions = '<option value="">Select Proof</option>';

            // fetch proofs name
            $.ajax({
                type: 'GET',
                url: "{{ route('proofsname') }}",
                data: {
                    token: "{{ session()->get('api_token') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    user_id: "{{ session()->get('user_id') }}"
                },
                success: function(response) {
                    if (response.status == 200 && response.proof != '') {
                        // You can update your HTML with the data here if needed
                        $.each(response.proof, function(key, value) {
                            proofOptions +=
                                `<option value="${value.proof_name}">${value.proof_name}</option>`;
                        });
                        $('select[name="id_proofs_type[]"], select[name="address_proofs_type[]"]')
                            .html(proofOptions);
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: response.message
                        });
                    }
                    loaderhide();
                },
                error: function(xhr, status, error) { // if calling api request error 
                    loaderhide();
                    console.log(xhr.responseText); // Log the full error response for debugging
                    handleAjaxError(xhr);
                }
            });

            // fetch country data and show in dropdown and set default value accroding logged in user
            $.ajax({
                type: 'GET',
                url: "{{ route('country.index') }}",
                data: {
                    token: "{{ session()->get('api_token') }}"
                },
                success: function(response) {
                    if (response.status == 200 && response.country != '') {
                        // You can update your HTML with the data here if needed
                        $.each(response.country, function(key, value) {
                            $('#country').append(
                                `<option value='${value.id}'> ${value.country_name}</option>`
                            )
                        });
                        country_id = "{{ session('user')['country_id'] }}";
                        $('#country').val(country_id);
                        loadstate();
                    } else {
                        $('#country').append(`<option> No Data Found</option>`);
                    }
                    loaderhide();
                },
                error: function(xhr, status, error) { // if calling api request error 
                    loaderhide();
                    console.log(xhr.responseText); // Log the full error response for debugging
                    handleAjaxError(xhr);
                }
            });

            // load state in dropdown when country change
            $('#country').on('change', function() {
                loadershow();
                $('#city').html(`<option selected="" disabled="">Select your city</option>`);
                var country_id = $(this).val();
                loadstate(country_id);
            });

            // load state in dropdown and set default value accroding logged in user if user not select manual
            function loadstate(id = 0) {
                $('#state').html(`<option selected="" disabled="">Select your State</option>`);
                var url = "{{ route('state.search', '__id__') }}".replace('__id__', id);
                if (id == 0) {
                    url = "{{ route('state.search', session('user')['country_id']) }}";
                }
                $.ajax({
                    type: 'GET',
                    url: url,
                    data: {
                        token: "{{ session()->get('api_token') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.state != '') {
                            // You can update your HTML with the data here if needed
                            $.each(response.state, function(key, value) {
                                $('#state').append(
                                    `<option value='${value.id}'> ${value.state_name}</option>`
                                )
                            });
                            if (id == 0) {
                                state_id = "{{ session('user')['state_id'] }}";
                                $('#state').val(state_id);
                                loadcity();
                            }
                        } else {
                            $('#state').append(`<option> No Data Found</option>`);
                        }
                        loaderhide();
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr
                            .responseText); // Log the full error response for debugging
                        handleAjaxError(xhr);
                    }
                });
            }

            // load city in dropdown when state select/change
            $('#state').on('change', function() {
                loadershow();
                var state_id = $(this).val();
                loadcity(state_id);
            });

            // load city in dropdown and set default value accroding logged in user if user not select manual
            function loadcity(id = 0) {
                $('#city').html(`<option selected="" disabled="">Select your City</option>`);
                url = "{{ route('city.search', '__id__') }}".replace('__id__', id);;
                if (id == 0) {
                    url = "{{ route('city.search', session('user')['state_id']) }}";
                }
                $.ajax({
                    type: 'GET',
                    url: url,
                    data: {
                        token: "{{ session()->get('api_token') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.city != '') {
                            // You can update your HTML with the data here if needed
                            $.each(response.city, function(key, value) {
                                $('#city').append(
                                    `<option value='${value.id}'> ${value.city_name}</option>`
                                )
                            });
                            if (id == 0) {
                                $('#city').val("{{ session('user')['city_id'] }}");
                            }
                        } else {
                            $('#city').append(`<option> No Data Found</option>`);
                        }
                        loaderhide();
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr
                            .responseText); // Log the full error response for debugging
                        handleAjaxError(xhr);
                    }
                });
            }

            function addFileInput(wrapperId, inputName) {

                let proofSelect = '';
                if (inputName === 'id_proofs' || inputName === 'address_proofs') {
                    proofSelect = `
                        <select name="${inputName}_type[]" class="form-control mr-2">
                            ${proofOptions}
                        </select>
                    `;
                }

                var fileInput = `
                    <div class="border rounded bg-white mb-2 px-3 py-2 d-flex align-items-center"
                        data-type="new">

                        ${proofSelect}

                        <input type="file" name="${inputName}[]" class="form-control-file mr-3"/>

                        <button type="button" class="btn btn-danger remove-file">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                `;

                $(wrapperId).append(fileInput);
            }


            // redirect on employee list page on click cancel btn
            $('#cancelbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.employee') }}";
            });

            // Handle add buttons
            $('.addinput').click(function() {
                fileInput = $(this).data('input-type');
                wrapper = `${fileInput}-wrapper`;
                addFileInput(`#${wrapper}`, fileInput);
            });

            // Handle remove buttons (works for all types)
            $(document).on('click', '.remove-file', function() {
                var element = $(this);

                showConfirmationDialog(
                    'Are you sure?',
                    'Do you want to remove this file?',
                    'Yes, remove it!',
                    'No, Cancel!',
                    'warning',
                    () => {
                        element.closest('[data-type="new"]').remove();
                    }
                );
            });
            // When a file is selected
            $(document).on('change', 'input[type="file"]', function() {
                const $input = $(this);
                const $removeBtn = $input.siblings('.remove-cv-file');

                if ($input[0].files.length > 0) {
                    $removeBtn.show();
                } else {
                    $removeBtn.hide();
                }
            });

            // When remove button is clicked
            $(document).on('click', '.remove-cv-file', function() {
                const $btn = $(this);
                const $input = $btn.siblings('input[type="file"]');

                showConfirmationDialog(
                    'Are you sure?',
                    'Do you want to remove this file?',
                    'Yes, remove it!',
                    'No, Cancel!',
                    'warning',
                    () => {
                        $input.val(''); // Clear file input
                        $btn.hide(); // Hide remove button
                    }
                );
            });

            $("#employeeform").on('submit', function(e) {
                e.preventDefault();
                loadershow()
                $('.error-msg').text(''); // Clear previous error messages
                var formData = new FormData(this);

                $.ajax({
                    type: 'POST',
                    url: "{{ route('employee.store') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
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
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide()
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                if (key.startsWith('id_proofs')) {
                                    $('#error-id_proofs').html(value.join('<br>'));
                                } else if (key.startsWith('other_attachments')) {
                                    $('#error-other_attachments').html(value.join(
                                        '<br>'));
                                } else if (key.startsWith('address_proofs')) {
                                    $('#error-address_proofs').html(value.join(
                                        '<br>'));
                                } else {
                                    $('#error-' + key.replace(/\./g, '_')).html(value[
                                        0]);
                                }
                            });

                        } else {
                            Toast.fire({
                                icon: "error",
                                title: "An error occurred. Please try again."
                            });

                        }
                    }
                });
            });
        });
    </script>
@endpush
