@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Update Business Subcategory
@endsection
@section('title')
    Update Business Subcategory
@endsection

@section('form-content')
    <form id="businesssubcategoryupdateform">
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
                    <label class="form-label" for="category_id">Category:</label><span style="color:red;"> *</span>
                    <select name="category_id" class="form-control" id="category_id" required>
                        <option value="" disabled selected>Select Category</option>
                    </select>
                    <span class="error-msg" id="error-category_id" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="name">Subcategory Name:</label><span style="color:red;">
                        *</span>
                    <input type="text" class="form-control" name="name" id="name"
                        placeholder="Enter Subcategory Name" required />
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
            // redirect on business subcategory page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societybusinesssubcategory') }}";
            });

            var edit_id = @json($edit_id);
            var selected_category = '';

            // load business categories in dropdown then load old data
            function loadcategories() {
                loadershow();
                $.ajax({
                    type: 'GET',
                    url: "{{ route('businesscategory.index') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.data) {
                            $.each(response.data, function(key, category) {
                                $('#category_id').append(
                                    `<option value="${category.id}">${category.name}</option>`);
                            });
                            if (selected_category) {
                                $('#category_id').val(selected_category);
                            }
                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    }
                });
            }

            // load old data
            function loaddata() {
                loadershow();
                let subcategoryEditUrl = "{{ route('businesssubcategory.edit', '__editId__') }}".replace(
                    '__editId__', edit_id);
                $.ajax({
                    type: 'GET',
                    url: subcategoryEditUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200) {
                            $('#name').val(response.data.name);
                            selected_category = response.data.category_id;
                            $('#category_id').val(response.data.category_id);
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
            loadcategories();

            //submit form
            $('#businesssubcategoryupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                let subcategoryUpdateUrl = "{{ route('businesssubcategory.update', '__editId__') }}".replace(
                    '__editId__', edit_id);
                ajaxRequest('PUT', subcategoryUpdateUrl, $(this).serialize())
                    .done(function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Business subcategory updated successfully!'
                            });
                            window.location = "{{ route('admin.societybusinesssubcategory') }}";
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
