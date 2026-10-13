@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Update Karobari Meeting
@endsection
@section('title')
    Update Karobari Meeting
@endsection

@section('form-content')
    <form id="karobarimeetingupdateform">
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
                        data-original-title="Update" class="btn btn-primary float-right my-0">Save</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('ajax')
    <script>
        $('document').ready(function() {
            loaderhide();
            var edit_id = @json($edit_id);
            var meetingData = null;

            // redirect on karobari meeting page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societykarobarimeeting') }}";
            });

            function loadStates(countryId, stateSelect, selectedState, citySelect, selectedCity) {
                stateSelect.empty().append(`<option value="" disabled selected>Select State</option>`);
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
                                stateSelect.append(
                                    `<option value="${state.id}">${state.state_name}</option>`);
                            });
                            if (selectedState) {
                                stateSelect.val(selectedState);
                                loadCities(selectedState, citySelect, selectedCity);
                            }
                        }
                    }
                });
            }

            function loadCities(stateId, citySelect, selectedCity) {
                citySelect.empty().append(`<option value="" disabled selected>Select City</option>`);
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
                                citySelect.append(
                                    `<option value="${city.id}">${city.city_name}</option>`);
                            });
                            if (selectedCity) {
                                citySelect.val(selectedCity);
                            }
                        }
                    }
                });
            }

            $('#country_id').on('change', function() {
                loadStates($(this).val(), $('#state_id'), '', $('#city_id'), '');
            });
            $('#state_id').on('change', function() {
                loadCities($(this).val(), $('#city_id'), '');
            });

            // load countries
            function loadCountries() {
                return $.ajax({
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
                    }
                });
            }

            // load old data
            function loaddata() {
                loadershow();
                let meetingEditUrl = "{{ route('karobarimeeting.edit', '__editId__') }}".replace(
                    '__editId__', edit_id);
                $.ajax({
                    type: 'GET',
                    url: meetingEditUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.meeting) {
                            meetingData = response.meeting;
                            $('#meeting_name').val(meetingData.meeting_name);
                            $('#meeting_date').val(meetingData.meeting_date ? String(meetingData
                                .meeting_date).split('T')[0] : '');
                            $('#meeting_time_from').val(meetingData.meeting_time_from);
                            $('#meeting_time_to').val(meetingData.meeting_time_to);
                            $('#building_name').val(meetingData.building_name);
                            $('#landmark').val(meetingData.landmark);
                            $('#area').val(meetingData.area);
                            $('#country_id').val(meetingData.country_id);
                            $('#pincode').val(meetingData.pincode);

                            if (meetingData.country_id) {
                                loadStates(meetingData.country_id, $('#state_id'), meetingData
                                    .state_id, $('#city_id'), meetingData.city_id);
                            }
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message || 'Something went wrong!'
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

            loadershow();
            $.when(loadCountries()).always(function() {
                loaddata();
            });

            //submit form
            $('#karobarimeetingupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                let meetingUpdateUrl = "{{ route('karobarimeeting.update', '__editId__') }}".replace(
                    '__editId__', edit_id);
                ajaxRequest('PUT', meetingUpdateUrl, $(this).serialize())
                    .done(function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Meeting updated successfully!'
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
