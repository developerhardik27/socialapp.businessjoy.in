@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Update Shakh
@endsection
@section('title')
    Update Shakh
@endsection

@section('form-content')
    <form id="shakhupdateform">
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
                    <label class="form-label" for="name">Shakh Name:</label><span style="color:red;"> *</span>
                    <input type="text" class="form-control" name="name" id="name"
                        placeholder="Enter Shakh Name" required />
                    <span class="error-msg" id="error-name" style="color: red"></span>
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

            // redirect on shakh page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societyshakh') }}";
            });

            // load old data from the shakh list
            function loaddata() {
                loadershow();
                $.ajax({
                    type: 'GET',
                    url: "{{ route('shakh.index') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.data) {
                            $.each(response.data, function(key, shakh) {
                                if (shakh.id == edit_id) {
                                    $('#name').val(shakh.name);
                                    return false;
                                }
                            });
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
            loaddata();

            //submit form
            $('#shakhupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                let shakhUpdateUrl = "{{ route('shakh.update', '__editId__') }}".replace('__editId__',
                    edit_id);
                ajaxRequest('PUT', shakhUpdateUrl, $(this).serialize())
                    .done(function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Shakh updated successfully!'
                            });
                            window.location = "{{ route('admin.societyshakh') }}";
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
