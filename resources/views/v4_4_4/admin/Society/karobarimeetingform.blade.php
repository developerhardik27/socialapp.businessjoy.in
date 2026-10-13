@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Add Karobari Meeting
@endsection
@section('title')
    Add Karobari Meeting
@endsection

@section('form-content')
    <form id="karobarimeetingaddform">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
                <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
                <input type="hidden" name="token" value="{{ session('api_token') }}" />
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="meeting_name">Meeting Name:</label><span
                        style="color:red;"> *</span>
                    <input type="text" class="form-control" name="meeting_name" id="meeting_name"
                        placeholder="Meeting Name" required />
                    <span class="error-msg" id="error-meeting_name" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="meeting_date">Meeting Date:</label><span
                        style="color:red;"> *</span>
                    <input type="date" class="form-control" name="meeting_date" id="meeting_date" required />
                    <span class="error-msg" id="error-meeting_date" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="meeting_time_from">Time From:</label><span
                        style="color:red;"> *</span>
                    <input type="time" class="form-control" name="meeting_time_from" id="meeting_time_from"
                        required />
                    <span class="error-msg" id="error-meeting_time_from" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="meeting_time_to">Time To:</label><span
                        style="color:red;"> *</span>
                    <input type="time" class="form-control" name="meeting_time_to" id="meeting_time_to"
                        required />
                    <span class="error-msg" id="error-meeting_time_to" style="color: red"></span>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="building_name">Building Name:</label>
                    <input type="text" class="form-control" name="building_name" id="building_name"
                        placeholder="Building Name" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="landmark">Landmark:</label>
                    <input type="text" class="form-control" name="landmark" id="landmark"
                        placeholder="Landmark" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="area">Area:</label>
                    <input type="text" class="form-control" name="area" id="area" placeholder="Area" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="country_id">Country:</label>
                    <select class="form-control" name="country_id" id="country_id">
                        <option value="" disabled selected>Select Country</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="state_id">State:</label>
                    <select class="form-control" name="state_id" id="state_id">
                        <option value="" disabled selected>Select State</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="city_id">City:</label>
                    <select class="form-control" name="city_id" id="city_id">
                        <option value="" disabled selected>Select City</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="pincode">Pincode:</label>
                    <input type="number" class="form-control" name="pincode" id="pincode"
                        placeholder="Pincode" />
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
            // redirect on karobari meeting page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societykarobarimeeting') }}";
            });

            // load countries
            function loadCountries() {
                loadershow();
                $.ajax({
                    type: 'GET',
                    url: "{{ route('country.index') }}",
                    success: function(response) {
                        if (response.status == 200 && response.country) {
                            $.each(response.country, function(key, country) {
                                $('#country_id').append(
                                    `<option value="${country.id}">${country.country_name}</option>`
                                );
                            });
                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    }
                });
            }
            loadCountries();

            $('#country_id').on('change', function() {
                var countryId = $(this).val();
                $('#state_id').empty().append(
                    `<option value="" disabled selected>Select State</option>`);
                $('#city_id').empty().append(
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
                                $('#state_id').append(
                                    `<option value="${state.id}">${state.state_name}</option>`
                                );
                            });
                        }
                    }
                });
            });

            $('#state_id').on('change', function() {
                var stateId = $(this).val();
                $('#city_id').empty().append(
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
                                $('#city_id').append(
                                    `<option value="${city.id}">${city.city_name}</option>`);
                            });
                        }
                    }
                });
            });

            //submit form
            $('#karobarimeetingaddform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                ajaxRequest('POST', "{{ route('karobarimeeting.store') }}", $(this).serialize())
                    .done(function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Meeting added successfully!'
                            });
                            window.location = "{{ route('admin.societykarobarimeeting') }}";
                        } else if (response.status == 422) {
                            loaderhide();
                            if (typeof response.message === 'object') {
                                $.each(response.message, function(field, messages) {
                                    $('#error-' + field).text(messages[0]);
                                });
                            }
                            Toast.fire({
                                icon: "error",
                                title: "Please check the form fields"
                            });
                        } else {
                            loaderhide();
                            Toast.fire({
                                icon: "error",
                                title: response.message || 'Something went wrong!'
                            });
                        }
                    })
                    .fail(function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    });
            });
        });
    </script>
@endpush
