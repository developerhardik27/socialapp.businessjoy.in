@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Add Event
@endsection
@section('title')
    Add Event
@endsection

@section('form-content')
    <form id="eventaddform" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="title">Title:</label><span style="color:red;"> *</span>
                    <input type="text" class="form-control" name="title" id="title"
                        placeholder="Event Title" required />
                    <span class="error-msg" id="error-title" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="familyPersonId">Family Person:</label>
                    <select name="familyPersonId" class="form-control" id="familyPersonId">
                        <option value="" selected>Select Family Person (Optional)</option>
                    </select>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="event_type">Event Type:</label>
                    <select name="event_type" class="form-control" id="event_type">
                        <option value="" selected>Select Event Type</option>
                    </select>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="event_status">Event Status:</label>
                    <select name="event_status" class="form-control" id="event_status">
                        <option value="" selected>Select Event Status</option>
                    </select>
                </div>
                <div class="col-sm-3 mb-2">
                    <label class="form-label" for="event_date_from">Date From:</label>
                    <input type="date" class="form-control" name="event_date_from" id="event_date_from" />
                </div>
                <div class="col-sm-3 mb-2">
                    <label class="form-label" for="event_date_to">Date To:</label>
                    <input type="date" class="form-control" name="event_date_to" id="event_date_to" />
                </div>
                <div class="col-sm-3 mb-2">
                    <label class="form-label" for="event_time_from">Time From:</label>
                    <input type="time" class="form-control" name="event_time_from" id="event_time_from" />
                </div>
                <div class="col-sm-3 mb-2">
                    <label class="form-label" for="event_time_to">Time To:</label>
                    <input type="time" class="form-control" name="event_time_to" id="event_time_to" />
                </div>
                <div class="col-sm-12 mb-2">
                    <label class="form-label" for="description">Description:</label>
                    <textarea class="form-control" name="description" id="description" rows="3"
                        placeholder="Event Description"></textarea>
                </div>
                <div class="col-sm-12 mb-1"><b>Venue Details</b></div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="building_name">Building Name:</label>
                    <input type="text" class="form-control" name="building_name" id="building_name"
                        placeholder="Building Name" />
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="landmark">Landmark:</label>
                    <input type="text" class="form-control" name="landmark" id="landmark"
                        placeholder="Landmark" />
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="area">Area:</label>
                    <input type="text" class="form-control" name="area" id="area" placeholder="Area" />
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="pincode">Pincode:</label>
                    <input type="text" class="form-control" name="pincode" id="pincode" maxlength="10"
                        onkeyup="this.value=this.value.replace(/[^\d]/g,'')" placeholder="Pincode" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="country_id">Country:</label>
                    <select name="country_id" class="form-control" id="country_id">
                        <option value="" disabled selected>Select Country</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="state_id">State:</label>
                    <select name="state_id" class="form-control" id="state_id">
                        <option value="" disabled selected>Select State</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="city_id">City:</label>
                    <select name="city_id" class="form-control" id="city_id">
                        <option value="" disabled selected>Select City</option>
                    </select>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="images">Images:</label>
                    <input type="file" class="form-control" name="images[]" id="images"
                        accept="image/jpeg,image/jpg,image/png" multiple />
                    <small class="text-muted">jpg, jpeg, png</small>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="videos">Videos:</label>
                    <input type="file" class="form-control" name="videos[]" id="videos"
                        accept="video/mp4,video/mov,video/avi" multiple />
                    <small class="text-muted">mp4, mov, avi</small>
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
            // redirect on event page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societyevent') }}";
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

            // load event types (type?page=event)
            $.ajax({
                type: 'GET',
                url: "{{ route('type.index') }}",
                data: {
                    token: "{{ session()->get('api_token') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    user_id: "{{ session()->get('user_id') }}",
                    page: 'event'
                },
                success: function(response) {
                    if (response.status == 200 && response.types) {
                        $.each(response.types, function(key, etype) {
                            $('#event_type').append(
                                `<option value="${etype.name}">${etype.name}</option>`);
                        });
                    }
                }
            });

            // load event statuses (status?page=event)
            $.ajax({
                type: 'GET',
                url: "{{ route('status.index') }}",
                data: {
                    token: "{{ session()->get('api_token') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    user_id: "{{ session()->get('user_id') }}",
                    page: 'event'
                },
                success: function(response) {
                    if (response.status == 200 && response.status) {
                        $.each(response.status, function(key, estatus) {
                            $('#event_status').append(
                                `<option value="${estatus.name}">${estatus.name}</option>`);
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
                            $('#country_id').append(
                                `<option value="${country.id}">${country.country_name}</option>`
                            );
                        });
                    }
                }
            });

            // country -> state -> city
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
                                    `<option value="${city.id}">${city.city_name}</option>`
                                );
                            });
                        }
                    }
                });
            });

            //submit form
            $('#eventaddform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');

                var formData = new FormData(this);
                formData.append('token', "{{ session()->get('api_token') }}");
                formData.append('company_id', "{{ session()->get('company_id') }}");
                formData.append('user_id', "{{ session()->get('user_id') }}");

                $.ajax({
                    type: 'POST',
                    url: "{{ route('event.store') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        loaderhide();
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Event added successfully!'
                            });
                            window.location = "{{ route('admin.societyevent') }}";
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
