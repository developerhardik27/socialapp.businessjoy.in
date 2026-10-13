@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Update Application
@endsection
@section('title')
    Update Application
@endsection

@section('form-content')
    <form id="applicationupdateform" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="familyPersonId">Family Person:</label>
                    <select name="familyPersonId" class="form-control" id="familyPersonId">
                        <option value="" selected>Select Family Person (Optional)</option>
                    </select>
                    <span class="error-msg" id="error-familyPersonId" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="type">Type:</label><span style="color:red;"> *</span>
                    <select name="type" class="form-control" id="type" required>
                        <option value="" disabled selected>Select Type</option>
                    </select>
                    <span class="error-msg" id="error-type" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="status">Status:</label>
                    <select name="status" class="form-control" id="status">
                        <option value="" selected>Select Status</option>
                    </select>
                    <span class="error-msg" id="error-status" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="details">Details:</label><span style="color:red;">
                        *</span>
                    <input type="text" class="form-control" name="details" id="details"
                        placeholder="Application Details" required />
                    <span class="error-msg" id="error-details" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="amount">Amount:</label><span style="color:red;">
                        *</span>
                    <input type="number" class="form-control" name="amount" id="amount" min="0"
                        step="0.01" placeholder="Amount" required />
                    <span class="error-msg" id="error-amount" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="documents">Documents:</label>
                    <input type="file" class="form-control" name="documents[]" id="documents"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple />
                    <small class="text-muted">pdf, doc, docx, jpg, jpeg, png — uploading replaces old
                        documents</small>
                    <div id="existingDocs" class="mt-2"></div>
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

            // redirect on application page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societyapplication') }}";
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

            // load application types
            function loadTypes() {
                return $.ajax({
                    type: 'GET',
                    url: "{{ route('type.index') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}",
                        page: 'application'
                    },
                    success: function(response) {
                        if (response.status == 200 && response.types) {
                            $.each(response.types, function(key, atype) {
                                $('#type').append(
                                    `<option value="${atype.name}">${atype.name}</option>`
                                );
                            });
                        }
                    }
                });
            }

            // load application statuses
            function loadStatuses() {
                return $.ajax({
                    type: 'GET',
                    url: "{{ route('status.index') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}",
                        page: 'application'
                    },
                    success: function(response) {
                        if (response.status == 200 && response.status) {
                            $.each(response.status, function(key, astatus) {
                                $('#status').append(
                                    `<option value="${astatus.name}">${astatus.name}</option>`
                                );
                            });
                        }
                    }
                });
            }

            // load old data
            function loaddata() {
                loadershow();
                let applicationEditUrl = "{{ route('application.edit', '__editId__') }}".replace(
                    '__editId__', edit_id);
                $.ajax({
                    type: 'GET',
                    url: applicationEditUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.data) {
                            var data = response.data;
                            $('#familyPersonId').val(data.familyPersonId);
                            $('#type').val(data.type);
                            $('#status').val(data.status);
                            $('#details').val(data.details);
                            $('#amount').val(data.amount);
                            var docs = toMediaList(data.documents);
                            if (docs.length) {
                                $('#existingDocs').html('<b>Existing:</b> ' + docs.map(
                                    function(doc, i) {
                                        return `<a href="${mediaUrl(doc)}" target="_blank">Document ${i+1}</a>`;
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
            $.when(loadpersons(), loadTypes(), loadStatuses()).always(function() {
                loaddata();
            });

            //submit form
            $('#applicationupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');

                var formData = new FormData(this);
                formData.append('token', "{{ session()->get('api_token') }}");
                formData.append('company_id', "{{ session()->get('company_id') }}");
                formData.append('user_id', "{{ session()->get('user_id') }}");
                formData.append('_method', 'PUT');

                let applicationUpdateUrl = "{{ route('application.update', '__editId__') }}".replace(
                    '__editId__', edit_id);

                $.ajax({
                    type: 'POST',
                    url: applicationUpdateUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        loaderhide();
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message ||
                                    'Application updated successfully!'
                            });
                            window.location = "{{ route('admin.societyapplication') }}";
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
