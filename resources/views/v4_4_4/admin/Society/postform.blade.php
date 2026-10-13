@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Add Post
@endsection
@section('title')
    Add Post
@endsection

@section('form-content')
    <form id="postaddform" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="title">Title:</label><span style="color:red;"> *</span>
                    <input type="text" class="form-control" name="title" id="title" placeholder="Post Title"
                        required />
                    <span class="error-msg" id="error-title" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="familyPersonId">Family Person:</label>
                    <select name="familyPersonId" class="form-control" id="familyPersonId">
                        <option value="" selected>Select Family Person (Optional)</option>
                    </select>
                    <span class="error-msg" id="error-familyPersonId" style="color: red"></span>
                </div>
                <div class="col-sm-12 mb-2">
                    <label class="form-label" for="text">Text:</label>
                    <textarea class="form-control" name="text" id="text" rows="4" placeholder="Post Text"></textarea>
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
            // redirect on post page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societypost') }}";
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

            //submit form
            $('#postaddform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');

                var formData = new FormData(this);
                formData.append('token', "{{ session()->get('api_token') }}");
                formData.append('company_id', "{{ session()->get('company_id') }}");
                formData.append('user_id', "{{ session()->get('user_id') }}");

                $.ajax({
                    type: 'POST',
                    url: "{{ route('post.store') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        loaderhide();
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Post added successfully!'
                            });
                            window.location = "{{ route('admin.societypost') }}";
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
