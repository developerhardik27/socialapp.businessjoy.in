@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Add Biodata
@endsection
@section('title')
    Add Biodata
@endsection

@section('form-content')
    <form id="biodataaddform" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
                <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
                <input type="hidden" name="token" value="{{ session('api_token') }}" />
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="familyPersonId">Family Person:</label><span
                        style="color:red;"> *</span>
                    <select name="familyPersonId" class="form-control" id="familyPersonId" required>
                        <option value="" disabled selected>Select Family Person</option>
                    </select>
                    <span class="error-msg" id="error-familyPersonId" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="pdfPath">Biodata PDF:</label><span style="color:red;">
                        *</span>
                    <input type="file" class="form-control" name="pdfPath" id="pdfPath" accept=".pdf"
                        required />
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
            // redirect on biodata page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societybiodata') }}";
            });

            // load all family persons and exclude ones that already have biodata
            function loadpersons() {
                loadershow();
                var usedIds = {};
                $.ajax({
                    type: 'GET',
                    url: "{{ route('biodata.index') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.data) {
                            $.each(response.data, function(i, row) {
                                if (row.familyPersonId) {
                                    usedIds[row.familyPersonId] = true;
                                }
                            });
                        }
                    },
                    complete: function() {
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
                                        if (!usedIds[person.id]) {
                                            $('#familyPersonId').append(
                                                `<option value="${person.id}">${person.full_name || person.first_name || ''} (${person.id})</option>`
                                            );
                                        }
                                    });
                                }
                            },
                            error: function(xhr) {
                                handleAjaxError(xhr);
                            },
                            complete: function() {
                                loaderhide();
                            }
                        });
                    }
                });
            }
            loadpersons();

            //submit form
            $('#biodataaddform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                var formData = new FormData(this);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('biodata.store') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Biodata added successfully!'
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
