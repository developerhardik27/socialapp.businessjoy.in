@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Add Job
@endsection
@section('title')
    Add Job
@endsection

@section('form-content')
    <form id="jobaddform" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="title">Title:</label><span style="color:red;"> *</span>
                    <input type="text" class="form-control" name="title" id="title" placeholder="Job Title"
                        required />
                    <span class="error-msg" id="error-title" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="type">Type:</label><span style="color:red;"> *</span>
                    <select name="type" class="form-control" id="type" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="hiring">Hiring</option>
                        <option value="looking">Looking</option>
                    </select>
                    <span class="error-msg" id="error-type" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="familyPersonId">Family Person:</label>
                    <select name="familyPersonId" class="form-control" id="familyPersonId">
                        <option value="" selected>Select Family Person (Optional)</option>
                    </select>
                    <span class="error-msg" id="error-familyPersonId" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="image">Image/PDF:</label>
                    <input type="file" class="form-control" name="image" id="image"
                        accept="image/jpeg,image/jpg,image/png,application/pdf" />
                    <small class="text-muted">jpg, jpeg, png, PDF</small>
                </div>
                <div class="col-sm-12 mb-2">
                    <label class="form-label" for="description">Description:</label>
                    <textarea class="form-control" name="description" id="description" rows="3"
                        placeholder="Job Description"></textarea>
                </div>
                <div class="col-sm-12 mb-1"><b>Company Details</b></div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="company_name">Company Name:</label>
                    <input type="text" class="form-control" name="company_name" id="company_name"
                        placeholder="Company Name" />
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="company_house_no_building_name">House No / Building
                        Name:</label>
                    <input type="text" class="form-control" name="company_house_no_building_name"
                        id="company_house_no_building_name" placeholder="House No / Building Name" />
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="company_landmark">Landmark:</label>
                    <input type="text" class="form-control" name="company_landmark" id="company_landmark"
                        placeholder="Landmark" />
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="company_area">Area:</label>
                    <input type="text" class="form-control" name="company_area" id="company_area"
                        placeholder="Area" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="company_country_id">Country:</label>
                    <select name="company_country_id" class="form-control" id="company_country_id">
                        <option value="" disabled selected>Select Country</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="company_state_id">State:</label>
                    <select name="company_state_id" class="form-control" id="company_state_id">
                        <option value="" disabled selected>Select State</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="company_city_id">City:</label>
                    <select name="company_city_id" class="form-control" id="company_city_id">
                        <option value="" disabled selected>Select City</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="company_pincode">Pincode:</label>
                    <input type="text" class="form-control" name="company_pincode" id="company_pincode"
                        maxlength="10" onkeyup="this.value=this.value.replace(/[^\d]/g,'')"
                        placeholder="Pincode" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="salary_from">Salary From:</label>
                    <input type="number" class="form-control" name="salary_from" id="salary_from"
                        min="0" placeholder="Salary From" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="salary_to">Salary To:</label>
                    <input type="number" class="form-control" name="salary_to" id="salary_to"
                        min="0" placeholder="Salary To" />
                </div>
                <div class="col-sm-12">
                    <button type="reset" id="resetbtn" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Cancel" class="btn iq-bg-danger float-right">Cancel</button>
                    <button type="submit" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Save" class="btn btn-primary float-right my-0">Save</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('ajax')
    <script>
        $('document').ready(function() {
            loaderhide();
            // redirect on job page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societyjob') }}";
            });

            // load persons
            $.ajax({
                type: 'GET',
                url: "{{ route('persons') }}",
                data: {
                    token: "{{ session()->get('api_token') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    user_id: "{{ session()->get('user_id') }}"
                },
                success: function(response) {
                    if (response.status == 200 && response.data) {
                        $.each(response.data, function(key, person) {
                            $('#familyPersonId').append(
                                `<option value="${person.id}">${person.full_name || person.first_name || ''} (${person.id})</option>`
                            );
                        });
                    }
                }
            });

            // load countries
            $.ajax({
                type: 'GET',
                url: "{{ route('country.index') }}",
                success: function(response) {
                    if (response.status == 200 && response.country) {
                        $.each(response.country, function(key, country) {
                            $('#company_country_id').append(
                                `<option value="${country.id}">${country.country_name}</option>`
                            );
                        });
                    }
                }
            });

            // country -> state -> city
            $('#company_country_id').on('change', function() {
                var countryId = $(this).val();
                $('#company_state_id').empty().append(
                    `<option value="" disabled selected>Select State</option>`);
                $('#company_city_id').empty().append(
                    `<option value="" disabled selected>Select City</option>`);
                if (!countryId) {
                    return;
                }
                let stateUrl = "{{ route('state.search', '__id__') }}".replace('__id__', countryId);
                $.ajax({
                    type: 'GET',
                    url: stateUrl,
                    success: function(response) {
                        if (response.status == 200 && response.state) {
                            $.each(response.state, function(key, state) {
                                $('#company_state_id').append(
                                    `<option value="${state.id}">${state.state_name}</option>`
                                );
                            });
                        }
                    }
                });
            });

            $('#company_state_id').on('change', function() {
                var stateId = $(this).val();
                $('#company_city_id').empty().append(
                    `<option value="" disabled selected>Select City</option>`);
                if (!stateId) {
                    return;
                }
                let cityUrl = "{{ route('city.search', '__id__') }}".replace('__id__', stateId);
                $.ajax({
                    type: 'GET',
                    url: cityUrl,
                    success: function(response) {
                        if (response.status == 200 && response.city) {
                            $.each(response.city, function(key, city) {
                                $('#company_city_id').append(
                                    `<option value="${city.id}">${city.city_name}</option>`
                                );
                            });
                        }
                    }
                });
            });

            //submit form
            $('#jobaddform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');

                var formData = new FormData(this);
                formData.append('token', "{{ session()->get('api_token') }}");
                formData.append('company_id', "{{ session()->get('company_id') }}");
                formData.append('user_id', "{{ session()->get('user_id') }}");

                $.ajax({
                    type: 'POST',
                    url: "{{ route('job.store') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        loaderhide();
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Job added successfully!'
                            });
                            window.location = "{{ route('admin.societyjob') }}";
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message || 'Something went wrong!'
                            });
                        }
                    },
                    error: function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    }
                });
            });
        });
    </script>
@endpush
