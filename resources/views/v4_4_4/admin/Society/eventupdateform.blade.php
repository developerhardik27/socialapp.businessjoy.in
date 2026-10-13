@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Update Event
@endsection
@section('title')
    Update Event
@endsection

@section('form-content')
    <form id="eventupdateform" enctype="multipart/form-data">
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
                    <label class="form-label" for="images">Add Images:</label>
                    <input type="file" class="form-control" name="images[]" id="images"
                        accept="image/jpeg,image/jpg,image/png" multiple />
                    <small class="text-muted">jpg, jpeg, png</small>
                    <div id="existingImages" class="mt-2"></div>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="videos">Add Videos:</label>
                    <input type="file" class="form-control" name="videos[]" id="videos"
                        accept="video/mp4,video/mov,video/avi" multiple />
                    <small class="text-muted">mp4, mov, avi</small>
                    <div id="existingVideos" class="mt-2"></div>
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

            // normalize media field (string / json string / array / double-encoded) into array of paths
            function toMediaList(value) {
                var list = [];
                function addItem(item, depth) {
                    if (!item || depth > 4) {
                        return;
                    }
                    if (typeof item === 'string') {
                        var t = $.trim(item);
                        t = t.replace(/&quot;/g, '"').replace(/&#34;|&#x22;/gi, '"')
                            .replace(/&#39;|&#x27;/gi, "'").replace(/&amp;/g, '&');
                        if (t.charAt(0) === '[' || t.charAt(0) === '"') {
                            try {
                                addItem(JSON.parse(t), depth + 1);
                                return;
                            } catch (e) {}
                        }
                        list.push(t.replace(/\\\//g, '/'));
                    } else if ($.isArray(item)) {
                        $.each(item, function(i, p) {
                            addItem(p, depth + 1);
                        });
                    } else {
                        list.push(String(item));
                    }
                }
                addItem(value, 0);
                return list;
            }

            // build usable url from stored path (collapse duplicate slashes)
            function mediaUrl(path) {
                path = String(path);
                if (/^https?:\/\//i.test(path)) {
                    return path;
                }
                return "{{ asset('') }}" + path.replace(/\/+/g, '/').replace(/^\//, '');
            }

            // redirect on event page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societyevent') }}";
            });

            // load persons
            function loadpersons() {
                return $.ajax({
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
            }

            // load event types
            function loadEventTypes() {
                return $.ajax({
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
                                    `<option value="${etype.name}">${etype.name}</option>`
                                );
                            });
                        }
                    }
                });
            }

            // load event statuses
            function loadEventStatuses() {
                return $.ajax({
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
                                    `<option value="${estatus.name}">${estatus.name}</option>`
                                );
                            });
                        }
                    }
                });
            }

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

            function loadStates(countryId, selectedState, selectedCity) {
                $('#state_id').empty().append(
                    `<option value="" disabled selected>Select State</option>`);
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
                            if (selectedState) {
                                $('#state_id').val(selectedState);
                                loadCities(selectedState, selectedCity);
                            }
                        }
                    }
                });
            }

            function loadCities(stateId, selectedCity) {
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
                            if (selectedCity) {
                                $('#city_id').val(selectedCity);
                            }
                        }
                    }
                });
            }

            // country -> state -> city change handlers
            $('#country_id').on('change', function() {
                loadStates($(this).val(), null, null);
            });

            $('#state_id').on('change', function() {
                loadCities($(this).val(), null);
            });

            // load old data
            function loaddata() {
                loadershow();
                let eventEditUrl = "{{ route('event.edit', '__editId__') }}".replace('__editId__',
                    edit_id);
                $.ajax({
                    type: 'GET',
                    url: eventEditUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.data) {
                            var data = response.data;
                            $('#title').val(data.title);
                            $('#description').val(data.description);
                            $('#familyPersonId').val(data.familyPersonId);
                            $('#event_type').val(data.event_type);
                            $('#event_status').val(data.event_status);
                            $('#event_date_from').val(data.event_date_from ? String(data
                                .event_date_from).split('T')[0] : '');
                            $('#event_date_to').val(data.event_date_to ? String(data.event_date_to)
                                .split('T')[0] : '');
                            $('#event_time_from').val(data.event_time_from ? String(data
                                .event_time_from).substring(0, 5) : '');
                            $('#event_time_to').val(data.event_time_to ? String(data.event_time_to)
                                .substring(0, 5) : '');
                            $('#building_name').val(data.building_name);
                            $('#landmark').val(data.landmark);
                            $('#area').val(data.area);
                            $('#pincode').val(data.pincode);
                            if (data.country_id) {
                                $('#country_id').val(data.country_id);
                                loadStates(data.country_id, data.state_id, data.city_id);
                            }
                            var images = toMediaList(data.image || data.images);
                            if (images.length) {
                                $('#existingImages').html('<b>Existing:</b> ' + images.map(
                                    function(img, i) {
                                        return `<a href="${mediaUrl(img)}" target="_blank">Image ${i+1}</a>`;
                                    }).join(', '));
                            }
                            var videos = toMediaList(data.video || data.videos);
                            if (videos.length) {
                                $('#existingVideos').html('<b>Existing:</b> ' + videos.map(
                                    function(vid, i) {
                                        return `<a href="${mediaUrl(vid)}" target="_blank">Video ${i+1}</a>`;
                                    }).join(', '));
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
            $.when(loadpersons(), loadEventTypes(), loadEventStatuses(), loadCountries()).always(
                function() {
                    loaddata();
                });

            //submit form
            $('#eventupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');

                var formData = new FormData(this);
                formData.append('token', "{{ session()->get('api_token') }}");
                formData.append('company_id', "{{ session()->get('company_id') }}");
                formData.append('user_id', "{{ session()->get('user_id') }}");
                formData.append('_method', 'PUT');

                let eventUpdateUrl = "{{ route('event.update', '__editId__') }}".replace('__editId__',
                    edit_id);

                $.ajax({
                    type: 'POST',
                    url: eventUpdateUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        loaderhide();
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Event updated successfully!'
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
