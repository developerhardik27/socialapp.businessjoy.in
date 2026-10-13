@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Add Donation Type
@endsection
@section('title')
    Add Donation Type
@endsection

@section('form-content')
    <form id="donationtypeaddform">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
                <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
                <input type="hidden" name="token" value="{{ session('api_token') }}" />
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="name">Donation Type Name:</label><span
                        style="color:red;"> *</span>
                    <input type="text" class="form-control" name="name" id="name"
                        placeholder="Enter Donation Type Name" required />
                    <span class="error-msg" id="error-name" style="color: red"></span>
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
            // redirect on donation type page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societydonationtype') }}";
            });

            //submit form
            $('#donationtypeaddform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                ajaxRequest('POST', "{{ route('donestiontype.store') }}", $(this).serialize())
                    .done(function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Donation type added successfully!'
                            });
                            window.location = "{{ route('admin.societydonationtype') }}";
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
