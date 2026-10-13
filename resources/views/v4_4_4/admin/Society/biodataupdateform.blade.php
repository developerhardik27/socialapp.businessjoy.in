@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Update Biodata
@endsection
@section('title')
    Update Biodata
@endsection

@section('form-content')
    <form id="biodataupdateform" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
                <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
                <input type="hidden" name="token" value="{{ session('api_token') }}" />
                <input type="hidden" name="_method" value="PUT" />
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="familyPersonId">Family Person:</label><span
                        style="color:red;"> *</span>
                    <select name="familyPersonId" class="form-control" id="familyPersonId" required>
                        <option value="" disabled selected>Select Family Person</option>
                    </select>
                    <span class="error-msg" id="error-familyPersonId" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="pdfPath">Biodata PDF:</label>
                    <input type="file" class="form-control" name="pdfPath" id="pdfPath" accept=".pdf" />
                    <a href="#" id="currentpdf" target="_blank" class="btn btn-danger btn-rounded btn-sm mt-1"
                        style="display:none;background-color:#dc3545;color:#fff;border-color:#dc3545;"><i
                            class="ri-file-pdf-line"></i> View Current PDF</a>
                    <span class="error-msg" id="error-pdfPath" style="color: red"></span>
                </div>
                <div class="col-sm-12 mb-2">
                    <label class="form-label" for="description">Description:</label>
                    <textarea class="form-control" name="description" id="description" rows="3"
                        placeholder="Description"></textarea>
                    <span class="error-msg" id="error-description" style="color: red"></span>
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
            var baseurl = "{{ asset('/') }}";

            // redirect on biodata page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societybiodata') }}";
            });

            // load all family persons
            function loadpersons(done) {
                loadershow();
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
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr);
                    },
                    complete: function() {
                        loaderhide();
                        if (typeof done === 'function') {
                            done();
                        }
                    }
                });
            }

            // load old data
            function loaddata() {
                loadershow();
                let biodataEditUrl = "{{ route('biodata.edit', '__editId__') }}".replace('__editId__',
                    edit_id);
                $.ajax({
                    type: 'GET',
                    url: biodataEditUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.biodata) {
                            var data = response.biodata;
                            $('#familyPersonId').val(data.familyPersonId);
                            $('#description').val(data.description);
                            if (data.pdfPath) {
                                var pdfurl = String(data.pdfPath).replace(/&quot;/g, '"')
                                    .replace(/&#34;|&#x22;/gi, '"').replace(/&amp;/g, '&');
                                try {
                                    if (pdfurl.charAt(0) === '[' || pdfurl.charAt(0) === '"') {
                                        pdfurl = JSON.parse(pdfurl);
                                        pdfurl = $.isArray(pdfurl) ? pdfurl[0] : pdfurl;
                                    }
                                } catch (e) {}
                                pdfurl = String(pdfurl).replace(/\\\//g, '/');
                                if (!/^https?:\/\//i.test(pdfurl)) {
                                    pdfurl = baseurl + String(pdfurl).replace(/\/+/g, '/')
                                        .replace(/^\//, '');
                                }
                                $('#currentpdf').attr('href', pdfurl).show();
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

            loadpersons(function() {
                loaddata();
            });

            //submit form
            $('#biodataupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                var formData = new FormData(this);
                let biodataUpdateUrl = "{{ route('biodata.update', '__editId__') }}".replace('__editId__',
                    edit_id);
                $.ajax({
                    type: 'POST',
                    url: biodataUpdateUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Biodata updated successfully!'
                            });
                            window.location = "{{ route('admin.societybiodata') }}";
                        } else {
                            loaderhide();
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
