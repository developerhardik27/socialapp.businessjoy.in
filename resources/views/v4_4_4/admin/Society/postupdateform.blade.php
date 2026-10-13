@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Update Post
@endsection
@section('title')
    Update Post
@endsection

@section('form-content')
    <form id="postupdateform" enctype="multipart/form-data">
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

            // redirect on post page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societypost') }}";
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

            // load old data
            function loaddata() {
                loadershow();
                let postEditUrl = "{{ route('post.edit', '__editId__') }}".replace('__editId__',
                    edit_id);
                $.ajax({
                    type: 'GET',
                    url: postEditUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.data) {
                            var data = response.data;
                            $('#title').val(data.title);
                            $('#text').val(data.text);
                            $('#familyPersonId').val(data.familyPersonId);
                            var images = toMediaList(data.images);
                            if (images.length) {
                                $('#existingImages').html('<b>Existing:</b> ' + images.map(
                                    function(img, i) {
                                        return `<a href="${mediaUrl(img)}" target="_blank">Image ${i+1}</a>`;
                                    }).join(', '));
                            }
                            var videos = toMediaList(data.videos);
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

            loadpersons().always(function() {
                loaddata();
            });

            //submit form
            $('#postupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');

                var formData = new FormData(this);
                formData.append('token', "{{ session()->get('api_token') }}");
                formData.append('company_id', "{{ session()->get('company_id') }}");
                formData.append('user_id', "{{ session()->get('user_id') }}");
                formData.append('_method', 'PUT');

                let postUpdateUrl = "{{ route('post.update', '__editId__') }}".replace('__editId__',
                    edit_id);

                $.ajax({
                    type: 'POST',
                    url: postUpdateUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        loaderhide();
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Post updated successfully!'
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
