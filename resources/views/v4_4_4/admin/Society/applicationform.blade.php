@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Add Application
@endsection
@section('title')
    Add Application
@endsection

@section('form-content')
    <form id="applicationaddform" enctype="multipart/form-data">
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
                    <small class="text-muted">pdf, doc, docx, jpg, jpeg, png</small>
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
            // redirect on application page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societyapplication') }}";
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

            // load application types (type?page=application)
            $.ajax({
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
                                `<option value="${atype.name}">${atype.name}</option>`);
                        });
                    }
                }
            });

            //submit form
            $('#applicationaddform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');

                var formData = new FormData(this);
                formData.append('token', "{{ session()->get('api_token') }}");
                formData.append('company_id', "{{ session()->get('company_id') }}");
                formData.append('user_id', "{{ session()->get('user_id') }}");

                $.ajax({
                    type: 'POST',
                    url: "{{ route('application.store') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        loaderhide();
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message ||
                                    'Application added successfully!'
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
