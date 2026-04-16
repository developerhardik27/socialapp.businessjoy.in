@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')

@section('page_title')
    {{ config('app.name') }} - Edit Category
@endsection
@section('title')
    Edit Category
@endsection

@section('form-content')
    <form id="categoryform" name="categoryform" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-6 mb-2">
                    <input type="hidden" name="company_id" class="form-control" value="{{ session('company_id') }}" required />
                    <input type="hidden" name="token" class="form-control" value="{{ session('api_token') }}" required />
                    <input type="hidden" name="user_id" class="form-control" value="{{ session('user_id') }}" required>
                    <label for="name">Category Name</label><span style="color:red;">*</span>
                    <input type="text" name="name" class="form-control" id="name" placeholder="Add Category Name" />
                    <span class="error-msg" id="error-name" style="color: red"></span>
                </div>

                <div class="col-sm-6 mb-2">
                    <label for="type">Type</label><span style="color:red;">*</span>
                    <select name="type" id="type" class="form-control">
                        <option value="" disabled selected>Select Type</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                    <span class="error-msg" id="error-type" style="color: red"></span>
                </div>

                <div class="col-sm-12 mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="mb-0">Sub Categories</label>
                        <button type="button" id="add_subcategory_btn" class="btn btn-sm iq-bg-success">
                            <i class="ri-add-fill"></i> Add Sub Category
                        </button>
                    </div>

                    <div id="subcategory_list"></div>

                    <span class="error-msg" id="error-subcategories" style="color: red"></span>
                </div>

                <div class="col-sm-12">
                    <button type="button" id="cancelbtn" class="btn btn-secondary float-right">Cancel</button>
                    <button type="reset" class="btn iq-bg-danger float-right mr-2">Reset</button>
                    <button type="submit" class="btn btn-primary float-right my-0">Update</button>
                </div>
            </div>
        </div>
    </form>
@endsection


@push('ajax')
<script>
    $('document').ready(function() {
        loaderhide();

        const API_TOKEN = "{{ session()->get('api_token') }}";
        const COMPANY_ID = "{{ session()->get('company_id') }}";
        const USER_ID = "{{ session()->get('user_id') }}";
        const EDIT_ID = @json($edit_id);

        let subcategoryCount = 0;

        function loaddata() {
            loadershow();
            let url = "{{ route('category.edit', '__id__') }}".replace('__id__', EDIT_ID);

            $.ajax({
                type: 'GET',
                url: url,
                data: {
                    token: API_TOKEN,
                    company_id: COMPANY_ID,
                    user_id: USER_ID
                },
                success: function(response) {
                    if (response.status == 200) {
                        let data = response.category;

                        $('#name').val(data.name);
                        $('#type').val(data.type);

                        if (data.subcategories && data.subcategories.length > 0) {
                            $.each(data.subcategories, function(key, sub) {
                                subcategoryCount++;
                                $('#subcategory_list').append(`
                                    <div class="input-group mb-2" id="subcategory_row_${subcategoryCount}">
                                        <input type="text" name="subcategories[]" class="form-control" placeholder="Sub Category Name" id="subcategory_${subcategoryCount}" value="${sub.name}">
                                        <input type="hidden" name="subcategory_ids[]" value="${sub.id}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn iq-bg-danger remove_subcategory_btn" data-id="${subcategoryCount}">
                                                <i class="ri-delete-bin-2-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                `);
                            });
                        }

                    } else {
                        Toast.fire({ icon: "error", title: response.message });
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

        $('#add_subcategory_btn').on('click', function() {
            subcategoryCount++;
            $('#subcategory_list').append(`
                <div class="input-group mb-2" id="subcategory_row_${subcategoryCount}">
                    <input type="text" name="subcategories[]" class="form-control" placeholder="Sub Category Name" id="subcategory_${subcategoryCount}">
                    <input type="hidden"name="subcategory_ids[]"value="">  
                    <div class="input-group-append">
                        <button type="button" class="btn iq-bg-danger remove_subcategory_btn" data-id="${subcategoryCount}">
                            <i class="ri-delete-bin-2-line"></i>
                        </button>
                    </div>
                </div>
            `);
        });

        $(document).on('click', '.remove_subcategory_btn', function() {
            let id = $(this).data('id');
            $('#subcategory_row_' + id).remove();
        });

        $('button[type="reset"]').on('click', function() {
            $('#subcategory_list').html('');
            subcategoryCount = 0;
            setTimeout(function() { loaddata(); }, 100);
        });

        $('#cancelbtn').on('click', function() {
            loadershow();
            window.location.href = "{{ route('admin.category') }}";
        });

        $('#categoryform').submit(function(event) {
            event.preventDefault();
            loadershow();
            $('.error-msg').text('');

            var formdata = new FormData($(this)[0]);

            $.ajax({
                type: 'POST',
                url: "{{ route('category.update', $edit_id) }}",
                data: formdata,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status == 200) {
                        Toast.fire({ icon: "success", title: response.message });
                        window.location = "{{ route('admin.category') }}";
                    } else {
                        Toast.fire({ icon: "error", title: response.message });
                        loaderhide();
                    }
                },
                error: function(xhr) {
                    loaderhide();
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        let firstErrorElement = null;
                        $.each(errors, function(key, value) {
                            let errorKey = key.replace('subcategories.', 'subcategory_');
                            let errorElement = $('#error-' + errorKey);
                            if (errorElement.length) {
                                errorElement.text(value[0]);
                            } else {
                                $('#error-subcategories').text(value[0]);
                            }
                            if (!firstErrorElement) firstErrorElement = $('#error-' + errorKey);
                        });
                        if (firstErrorElement && firstErrorElement.length) {
                            $('html, body').animate({
                                scrollTop: firstErrorElement.offset().top - 100
                            }, 800);
                        }
                    } else {
                        var errorMessage = "";
                        try {
                            errorMessage = JSON.parse(xhr.responseText).message || "An error occurred";
                        } catch(e) {
                            errorMessage = "An error occurred";
                        }
                        Toast.fire({ icon: "error", title: errorMessage });
                    }
                }
            });
        });
    });
</script>
@endpush