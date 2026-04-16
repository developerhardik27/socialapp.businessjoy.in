@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')

@section('page_title')
    {{ config('app.name') }} - Add New letter variable setting
@endsection
@section('title')
    New letter variable setting
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
    <form id="lettersettingForm" enctype="multipart/form-data" class="border rounded p-4 mb-4">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-6 mb-2">
                    <input type="hidden" name="token" value="{{ session('api_token') }}">
                    <input type="hidden" name="user_id" value="{{ session('user_id') }}">
                    <input type="hidden" name="company_id" value="{{ session('company_id') }}">
                    <label for="variable_name" class="form-label">Variable Name</label>
                    <span style="color:red;">*</span>
                    <input type="text" name="variable_name" id="variable_name" class="form-control"
                        placeholder="Variable Name" pattern="[A-Za-z_][A-Za-z0-9_]*"
                        title="Only letters, numbers, underscore. No spaces.">
                    <span class="error-msg" id="error-variable_name" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="variable_formate_type" class="form-label">Variable Format</label>
                    <span style="color:red;">*</span>
                    <select id="variable_formate_type" name="variable_formate_type" class="form-select form-control">
                        <option value="" selected disabled>Select Component Type</option>
                        <option value="static_text">Text</option>
                        <option value="variable">Variable</option>
                    </select>
                    <span class="error-msg " id="error-variables" style="color: red"></span>
                </div>
                <div class="col-sm-12 mb-2">
                    <div id="variable_formate_components" class="d-flex flex-wrap gap-2">
                        
                    </div>
                </div>
                <div class="col-sm-12 mb-2">
                      <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class=" form-control"></textarea>
                </div>
                <div class="col-sm-12">
                    <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Cancel"
                        id="cancelbtn" class="btn btn-secondary float-right">Cancel</button>
                    <button type="reset" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Reset variable Details"
                        class="btn iq-bg-danger float-right mr-2">Reset</button>
                    <button type="submit" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Save letter variable setting Details"
                        class="btn btn-primary float-right my-0">Save</button>
                </div>
            </div>
        </div>
    </form>

    <div class="container ">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white ">
                        <h5 class="mb-0 text-dark font-weight-bold">
                            letter Variable Guide
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 50%">Variable Tag</th>
                                        <th>What it Represents</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code class="text-black-50 font-weight-bold"
                                                style="font-size: 1.1rem;">$fname</code></td>
                                        <td>Employee's <strong>First Name</strong></td>
                                    </tr>
                                    <tr>
                                        <td><code class="text-black-50 font-weight-bold"
                                                style="font-size: 1.1rem;">$mname</code></td>
                                        <td>Employee's <strong>Middle Name</strong></td>
                                    </tr>
                                    <tr>
                                        <td><code class="text-black-50 font-weight-bold"
                                                style="font-size: 1.1rem;">$sname</code></td>
                                        <td>Employee's <strong>Surname / Last Name</strong></td>
                                    </tr>

                                    <tr>
                                        <td><code class="text-black-50 font-weight-bold"
                                                style="font-size: 1.1rem;">$email</code></td>
                                        <td>Registered <strong>Email Address</strong></td>
                                    </tr>
                                    <tr>
                                        <td><code class="text-black-50 font-weight-bold"
                                                style="font-size: 1.1rem;">$mobile</code></td>
                                        <td>Primary <strong>Mobile Number</strong></td>
                                    </tr>

                                    <tr>
                                        <td><code class="text-black-50 font-weight-bold"
                                                style="font-size: 1.1rem;">$house_no</code></td>
                                        <td><strong>House Number</strong> or Building Name</td>
                                    </tr>
                                    <tr>
                                        <td><code class=" text-black-50 font-weight-bold"
                                                style="font-size: 1.1rem;">$road_area_name</code></td>
                                        <td><strong>Road, Area, or Colony</strong> name</td>
                                    </tr>
                                    <tr>
                                        <td><code class="text-black-50 font-weight-bold"
                                                style="font-size: 1.1rem;">$city_name</code></td>
                                        <td>Current <strong>City</strong> of residence</td>
                                    </tr>
                                    <tr>
                                        <td><code class="text-black-50 font-weight-bold"
                                                style="font-size: 1.1rem;">$state_name</code></td>
                                        <td>Registered <strong>State</strong></td>
                                    </tr>
                                    <tr>
                                        <td><code class="text-black-50 font-weight-bold"
                                                style="font-size: 1.1rem;">$pincode</code></td>
                                        <td>6-Digit <strong>Postal / ZIP Code</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-center py-2">
                        <small class="text-muted">Tip: Ensure the <strong>$</strong> symbol is included
                            when using these tags.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
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


            $('#variable_formate_type').on('change', function() {

                var type = $(this).val();

                if (type === 'static_text') {
                    $('#variable_formate_components').append(`
                        <div class="col-sm-3 variable_formate_component mb-2">
                             <label class="form-label">text</label>
                            <input type="text"
                                class="form-control invoice-component-input"
                                data-type="static_text"
                                placeholder="Enter text">
                            <span class="text-center"><i data-type="text" class="ri-delete-bin-line btn btn-primary iq-bg-danger remove_invoice_component"></i></span>
                        </div>
                     `);
                }

                if (type === 'variable') {
                    $('#variable_formate_components').append(`
                        <div class="col-sm-3 variable_formate_component mb-2">
                           <label class="form-label">variable</label>
                            <select class="form-control variable_formate_component"data-type="variable">
                                <option value="">Select Variable </option>
                                <option value="first_name">$fname</option>
                                <option value="middle_name">$mname</option>
                                <option value="surname">$sname</option>
                                <option value="email">$email</option>
                                <option value="mobile">$mobile</option>
                                <option value="house_no_building_name">$house_no</option>
                                <option value="road_name_area_colony">$road_area_name</option>
                                <option value="city_id">$city_name</option>
                                <option value="state_id">$state_name</option>
                                <option value="pincode">$pincode</option>
                            </select>
                            <span class="text-center"><i data-type="text" class="ri-delete-bin-line btn btn-primary iq-bg-danger remove_invoice_component"></i></span>
                        </div>
                    `);
                }
                $(this).prop('selectedIndex', 0);
            });


            $(document).on('click', '.remove_invoice_component', function() {
                $(this).closest('.variable_formate_component').remove();
            });

            $('#cancelbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.letter_variable_setting') }}";
            });
            $("#lettersettingForm").on('submit', function(e) {
                e.preventDefault();
                loadershow();
                $('.error-msg').text('');

                var compArr = [];
                var hasError = false; // flag to track if any field is invalid

                $('#variable_formate_components').children('.variable_formate_component').each(function() {
                    var $input = $(this).find('input[data-type="static_text"]');
                    var $select = $(this).find('select[data-type="variable"]');
                    console.log($input.length);
                    console.log($select.length);
                    if ($input.length) {
                        var val = $input.val().trim();
                        if (!val) {
                            hasError = true;
                            $(this).append(
                                '<div class="error-msg" style="color:red">This field is required</div>'
                            );
                        } else {
                            compArr.push(val);
                        }
                    }

                    if ($select.length) {
                        var val = $select.val();
                        if (!val) {
                            hasError = true;
                            $(this).append(
                                '<div class="error-msg" style="color:red">Please select a variable</div>'
                            );
                        } else {
                            compArr.push(val);
                        }
                    }
                });

                if (hasError) {
                    loaderhide();
                    return false; // stop submission until all fields are valid
                }

                // Build FormData
                var formData = new FormData(this);
                if (compArr.length) {
                    for (var i = 0; i < compArr.length; i++) {
                        formData.append('variables[0][' + i + ']', compArr[i]);
                    }
                } else {
                    formData.append('variables[0]', '');
                }
                // console.log(formData);
                //    loaderhide();
                // return;
                $.ajax({
                    type: 'POST',
                    url: "{{ route('lettervariablesetting.store') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });
                            window.location = "{{ route('admin.letter_variable_setting') }}";
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
                        console.log(xhr
                            .responseText); // Log the full error response for debugging
                        handleAjaxError(xhr);
                    }
                });
            });


        });
    </script>
@endpush
