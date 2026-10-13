@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Add Policy
@endsection
@section('title')
    Add Policy
@endsection

@section('form-content')
    <form id="policyaddform" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="policy_number">Policy Number:</label><span
                        style="color:red;"> *</span>
                    <input type="text" class="form-control" name="policy_number" id="policy_number"
                        placeholder="Policy Number" required />
                    <span class="error-msg" id="error-policy_number" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="date">Date:</label><span style="color:red;"> *</span>
                    <input type="date" class="form-control" name="date" id="date" required />
                    <span class="error-msg" id="error-date" style="color: red"></span>
                </div>
                <div class="col-sm-12 mb-2">
                    <label class="form-label" for="text">Text:</label><span style="color:red;"> *</span>
                    <textarea class="form-control" name="text" id="text" rows="4" placeholder="Policy Text"
                        required></textarea>
                    <span class="error-msg" id="error-text" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="pdf_file">PDF File:</label>
                    <input type="file" class="form-control" name="pdf_file" id="pdf_file"
                        accept="application/pdf" />
                    <small class="text-muted">pdf only</small>
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
            // redirect on policy page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societypolicy') }}";
            });

            //submit form
            $('#policyaddform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');

                var formData = new FormData(this);
                formData.append('token', "{{ session()->get('api_token') }}");
                formData.append('company_id', "{{ session()->get('company_id') }}");
                formData.append('user_id', "{{ session()->get('user_id') }}");

                $.ajax({
                    type: 'POST',
                    url: "{{ route('policy.store') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        loaderhide();
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Policy added successfully!'
                            });
                            window.location = "{{ route('admin.societypolicy') }}";
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
