@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Add Family Relation
@endsection
@section('title')
    Add Family Relation
@endsection

@section('form-content')
    <form id="familyrelationaddform">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <input type="hidden" name="user_id" class="form-control" value="{{ session('user_id') }}"
                    required />
                <input type="hidden" name="company_id" class="form-control" value="{{ session('company_id') }}"
                    required />
                <input type="hidden" name="token" class="form-control" value="{{ session('api_token') }}"
                    required />
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="relation">Relation:</label><span style="color:red;"> *</span>
                    <input type="text" class="form-control" name="relation" id="relation"
                        placeholder="Enter Relation Name (e.g. Father, Mother)" required />
                    <span class="error-msg" id="error-relation" style="color: red"></span>
                </div>
                <div class="col-sm-12">
                    <button type="reset" id="resetbtn" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Cancel" class="btn iq-bg-danger float-right">Cancel</button>
                    <button type="submit" data-toggle="tooltip" data-placement="bottom" data-original-title="Save"
                        class="btn btn-primary float-right my-0">Save</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('ajax')
    <script>
        $('document').ready(function() {
            loaderhide();
            // redirect on family relation page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societyfamilyrelation') }}";
            });

            //submit form
            $('#familyrelationaddform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                ajaxRequest('POST', "{{ route('familyrelation.store') }}", $(this).serialize())
                    .done(function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Family relation added successfully!'
                            });
                            window.location = "{{ route('admin.societyfamilyrelation') }}";
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
