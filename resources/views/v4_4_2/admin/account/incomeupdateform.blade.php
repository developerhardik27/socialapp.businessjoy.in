@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Update Income Details
@endsection
@section('title')
    Update Income Details
@endsection

@section('form-content')
    <form id="incomeupdateform" name="incomeupdateform" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-6 mb-2">
                    <input type="hidden" name="company_id" class="form-control" value="{{ session('company_id') }}" required />
                    <input type="hidden" name="token" class="form-control" value="{{ session('api_token') }}" required />
                    <input type="hidden" name="user_id" class="form-control" value="{{ session('user_id') }}" required>
                    <label for="date">Date</label><span style="color:red;">*</span>
                    <input type="date" name="date" class="form-control" id="date"
                        value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" autocomplete="off" required />
                    <span class="error-msg" id="error-date" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="amount">Amount</label><span style="color:red;">*</span>
                    <input type="amount" name="amount" class="form-control" id="amount" placeholder="Enter amount"
                        autocomplete="off" required />
                    <span class="error-msg" id="error-amount" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="type">Type</label>
                    <input type="text" name="type" class="form-control" id="type"
                        placeholder="e.x., etc payment" />
                    <span class="error-msg" id="error-type" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="paid_by">Paid by</label>
                    <select id="paid_by" class="form-control">
                        <option value="">Select Paid By</option>
                    </select>
                    <span class="error-msg" id="error-paid_by" style="color: red"></span>
                </div>
                <input type="hidden" name="paid_by" id="paid_by_name" value="">
                <input type="hidden" name="customer_id" id="customer_id" value="">
                <div class="col-sm-6 mb-2">
                    <label for="income_category">Category</label>
                    <select id="income_category" name="income_category" class="form-control">
                        <option value="">Select Category</option>
                    </select>
                    <span class="error-msg" id="error-category" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="income_subcategory">Sub Category</label>
                    <select id="income_subcategory" name="income_subcategory"class="form-control">
                        <option value="">Select Sub Category</option>
                    </select>
                    <span class="error-msg" id="error-category" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="payment_type">Payment Type</label>
                    <select name="payment_type" class="form-control" id="payment_type">
                        <option value="" disabled>Select Type</option>
                        <option value="Cash">Cash</option>
                        <option value="Online">Online</option>
                    </select>
                    <span class="error-msg" id="error-payment_type" style="color: red"></span>
                </div>
                <div class="col-sm-12 mb-2">
                    <label for="description">Description</label>
                    <textarea id="description" type="text" name="description" class="form-control" placeholder="description"></textarea>
                    <span class="error-msg" id="error-description" style="color: red"></span>
                </div>
                <div class="col-sm-12">
                    <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Cancel"
                        id="cancelbtn" class="btn btn-secondary float-right">Cancel</button>
                    <button type="reset" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Reset Income Details"
                        class="btn iq-bg-danger float-right mr-2">Reset</button>
                    <button type="submit" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Update Income Details" class="btn btn-primary float-right my-0">Save</button>
                </div>
            </div>
        </div>
    </form>
    {{-- paid by add new (customer) modal --}}
    <div class="modal fade" id="exampleModalScrollable" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalScrollableTitle">Add New Paid By</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="details" width='100%'
                        class="table table-bordered table-responsive-sm table-responsive-md table-responsive-lg table-responsive-xl table-striped">
                        <form id="customerform">
                            @csrf
                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-sm-6 mb-2">
                                        <input type="hidden" name="company_id" class="form-control"
                                            value="{{ session('company_id') }}" required />
                                        <input type="hidden" name="token" class="form-control"
                                            value="{{ session('api_token') }}" required />
                                        <input type="hidden" name="user_id" class="form-control"
                                            value="{{ session('user_id') }}" required>
                                        <input type="hidden" name="customer_type" value="account" class="form-control"
                                            required />
                                        <label for="firstname">FirstName</label><span class="withoutgstspan"
                                            style="color:red;">*</span>
                                        <input type="text" class="form-control withoutgstinput" id="firstname"
                                            name='firstname' placeholder="First name" required>
                                        <span class="modal-error-msg" id="modal-error-firstname"
                                            style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="lastname">LastName</label>
                                        {{-- <span style="color:red;">*</span> --}}
                                        <input type="text" class="form-control" id="lastname" name='lastname'
                                            placeholder="Last name">
                                        <span class="modal-error-msg" id="modal-error-lastname"
                                            style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="company_name">Company Name</label>
                                        {{-- <span class="withgstspan" style="color:red;">*</span> --}}
                                        <input type="text" class="form-control withgstiput" id="company_name"
                                            name='company_name' id="" placeholder="Company name">
                                        <span class="modal-error-msg" id="modal-error-company_name"
                                            style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="gst_number">GST Number</label>
                                        {{-- <span class="withgstspan" style="color:red;">*</span> --}}
                                        <input type="text" class="form-control" name='gst_number' id="gst_number"
                                            placeholder="GST Number">
                                        <span class="modal-error-msg" id="modal-error-gst_number"
                                            style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="modal_email">Email</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <input type="email" class="form-control requiredinput" name="email"
                                            id="modal_email" placeholder="Enter Email" />
                                        <span class="modal-error-msg" id="modal-error-email" style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="modal_exampleInputphone">Contact Number</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <input type="tel" class="form-control requiredinput" name='contact_number'
                                            id="modal_exampleInputphone" placeholder="0123456789">
                                        <span class="modal-error-msg" id="modal-error-contact_number"
                                            style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="modal_country">Select Country</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <select class="form-control requiredinput" name='country' id="modal_country">
                                            <option selected="" disabled="">Select your Country</option>
                                        </select>
                                        <span class="modal-error-msg" id="modal-error-country" style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="modal_state">Select State</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <select class="form-control requiredinput" name='state' id="modal_state">
                                            <option selected="" disabled="">Select your State</option>
                                        </select>
                                        <span class="modal-error-msg" id="modal-error-state" style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="modal_city">Select City</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <select class="form-control requiredinput" name='city' id="modal_city">
                                            <option selected="" disabled="">Select your City</option>
                                        </select>
                                        <span class="modal-error-msg" id="modal-error-city" style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="modal_pincode">Pincode</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <input type="text" id="modal_pincode" name='pincode'
                                            class="form-control requiredinput" placeholder="Pin Code">
                                        <span class="modal-error-msg" id="modal-error-pincode" style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="house_no_building_name">House no./ Building Name</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <textarea class="form-control requiredinput" name='house_no_building_name' id="house_no_building_name"
                                            rows="2" placeholder="e.g. 2nd floor/ 04 ABC Appartment"></textarea>
                                        <span class="modal-error-msg" id="modal-error-house_no_building_name"
                                            style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="road_name_area_colony">Road Name/Area/Colony</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <textarea class="form-control requiredinput" name='road_name_area_colony' id="road_name_area_colony" rows="2"
                                            placeholder="e.g. sardar patel road, jagatpur"></textarea>
                                        <span class="modal-error-msg" id="modal-error-road_name_area_colony"
                                            style="color: red"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary" id="modal_submitBtn">Save</button>
                                <button id="modal_resetbtn" type="reset" class="btn iq-bg-danger mr-2">Reset</button>
                                <button id="modal_cancelBtn" type="btn" class="btn btn-secondary">Cancel</button>
                            </div>
                        </form>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{-- add category modal --}}
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalTitle">Add New Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="categoryform">
                        @csrf
                        <input type="hidden" name="token" value="{{ session('api_token') }}" />
                        <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
                        <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
                        <input type="hidden" name="type" value="income">
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-sm-12 mb-2">
                                    <label for="name">Category Name</label>
                                    <span style="color:red;">*</span>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Enter category name" required>
                                    <span class="category-error-msg" id="category-error-name" style="color: red;"></span>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="category_submitBtn">Save</button>
                            <button type="reset" class="btn iq-bg-danger mr-2" id="category_resetBtn">Reset</button>
                            <button type="button" class="btn btn-secondary" id="category_cancelBtn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- add Sub category modal --}}
    <div class="modal fade" id="addsubCategoryModal" tabindex="-1" role="dialog"
        aria-labelledby="addsubCategoryModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addsubCategoryModalTitle">Add New Sub Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="subcategoryform">
                        @csrf
                        <input type="hidden" name="token" value="{{ session('api_token') }}" />
                        <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
                        <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
                        <input type="hidden" name="type" value="expense">
                        <input type="hidden" name="category_id" id="sub_category_parent_id" value="">
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-sm-12 mb-2">
                                    <label for="sub_name">Sub Category Name</label>
                                    <span style="color:red;">*</span>
                                    <input type="text" class="form-control" id="sub_name" name="name"
                                        placeholder="Enter sub category name" required>
                                    <span class="subcategory-error-msg" id="subcategory-error-name"
                                        style="color: red;"></span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="subcategory_submitBtn">Save</button>
                            <button type="reset" class="btn iq-bg-danger mr-2" id="subcategory_resetBtn">Reset</button>
                            <button type="button" class="btn btn-secondary" id="subcategory_cancelBtn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('ajax')
    <script>
        $('document').ready(function() {
            // companyId and userId both are required in every ajax request for all action *************
            // response status == 200 that means response succesfully recieved
            // response status == 500 that means database not found
            // response status == 422 that means api has not got valid or required data
            // get selected consignee data and show it into fields


            const API_TOKEN = "{{ session()->get('api_token') }}";
            const COMPANY_ID = "{{ session()->get('company_id') }}";
            const USER_ID = " {{ session()->get('user_id') }} ";
            let category_id;
            let subcategory_id;
            $('#payment_type').select2({
                placeholder: 'Select type',
                allowClear: true
            });
            // get & set coach old data in the form input
            var edit_id = @json($edit_id);
            let incomeSearchUrl = "{{ route('income.edit', '__editId__') }}".replace('__editId__', edit_id);
            $.ajax({
                type: 'GET',
                url: incomeSearchUrl,
                data: {
                    token: "{{ session()->get('api_token') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    user_id: " {{ session()->get('user_id') }} "
                },
                success: function(response) {
                    if (response.status == 200) {
                        data = response.income;

                        // You can update your HTML with the data here if needed
                        $('#date').val(data.date);
                        $('#amount').val(data.amount);
                        $('#type').val(data.type);
                        // $('#paid_by').val(data.paid_by);
                        $('#payment_type').val(data.payment_type);
                        $('#description').val(data.description);
                        if (data.customer_id) {
                            loadPaidby(data.customer_id);
                        } else {
                            loadPaidby();
                            $('#paid_by_name').text(data.paid_by);
                        }
                        category_id = data.category_id;
                        subcategory_id = data.subcategory_id;
                        if (data.category_id && data.subcategory_id) {
                            loadCategory(data.category_id, data.subcategory_id);
                        } else if (data.category_id) {
                            loadCategory(data.category_id);
                        }
                    } else if (response.status == 500) {
                        Toast.fire({
                            icon: "error",
                            title: response.message
                        });
                    }
                    loaderhide();
                },
                error: function(error) {
                    loaderhide();
                    console.error('Error:', error);
                }
            });
            // load category data 
            function loadCategory(selectedId = '', selectedSubId = '') {
                const $select = $('#income_category');
                $select.html(
                    `<option value="">Select category</option><option value="add_new">Add New category</option>`
                );

                $.ajax({
                    type: 'GET',
                    url: `{{ route('income.category') }}`,
                    data: {
                        token: API_TOKEN,
                        company_id: COMPANY_ID,
                        user_id: USER_ID
                    },
                    success: function(res) {
                        if (res.status === 200 && Array.isArray(res.category) && res.category.length >
                            0) {
                            res.category.forEach(function(item) {
                                $select.append(
                                    `<option value="${item.id}">${item.name}</option>`);
                            });
                        } else {
                            $select.append(`<option disabled>No Category Found</option>`);
                        }

                        if ($select.hasClass('select2-hidden-accessible')) {
                            $select.select2('destroy');
                        }
                        $select.val(selectedId).select2({
                            placeholder: 'Select category',
                            allowClear: true
                        });
                        if (selectedId) {
                            loadsubCategory(selectedId, selectedSubId); // ← forward selectedSubId
                        } else {
                            loaderhide();
                            $('#income_subcategory')
                                .html(`<option value="">Select category</option>`)
                                .select2({
                                    placeholder: 'Select category',
                                    allowClear: true
                                });
                        }
                    },
                    error: function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    }
                });
            }
            // Load subcategory based on category ID
            function loadsubCategory(categoryId, selectedSubId = '') {
                const $select = $('#income_subcategory');
                $select.html(
                    `<option value="">Select category</option><option value="add_new">Add New Sub category</option>`
                );

                $.ajax({
                    type: 'GET',
                    url: `/api/subcategorylist/${categoryId}`,
                    data: {
                        token: API_TOKEN,
                        company_id: COMPANY_ID,
                        user_id: USER_ID
                    },
                    success: function(res) {
                        if (res.status === 200 && Array.isArray(res.subcategory) && res.subcategory
                            .length > 0) {
                            res.subcategory.forEach(function(item) {
                                $select.append(
                                    `<option value="${item.id}">${item.name}</option>`);
                            });
                        } else {
                            $select.append(`<option disabled>No Subcategory Found</option>`);
                        }

                        if ($select.hasClass('select2-hidden-accessible')) {
                            $select.select2('destroy');
                        }

                        $select.val(selectedSubId).select2({
                            placeholder: 'Select category',
                            allowClear: true
                        });

                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    }
                });
            }

            // When category changes, load subcategory
            $('#income_category').on('change', function() {
                const categoryId = $(this).val();
                if (categoryId == 'add_new') {
                    $('#addCategoryModal').modal('show');
                }
                if (categoryId) {
                    loadsubCategory(categoryId);
                }
            });
            // When subcategory changes
            $('#income_subcategory').on('change', function() {
                const val = $(this).val();
                if (val == 'add_new') {
                    const parentCategoryId = $('#income_category').val();
                    if (!parentCategoryId || parentCategoryId == 'add_new') {
                        Toast.fire({
                            icon: 'warning',
                            title: 'Please select a category first'
                        });
                        $(this).val('').trigger('change');
                        return;
                    }
                    $('#sub_category_parent_id').val(parentCategoryId);
                    $('#addsubCategoryModal').modal('show');
                }
            });
            // ── category Cancel button ────────────────────────────────────────────────────────────
            $('#category_cancelBtn').on('click', function() {
                $('#categoryform')[0].reset();
                loadCategory(category_id, subcategory_id);
                $('.category-error-msg').text('');
                $('#addCategoryModal').modal('hide');
            });
            // ── Sub category Cancel button ────────────────────────────────────────────────────────────
            $('#subcategory_cancelBtn').on('click', function() {
                $('#subcategoryform')[0].reset();
                $('#income_subcategory').val(subcategory_id).trigger(
                    'change'); // ✅ correct ID + select2 reset
                $('.subcategory-error-msg').text('');
                $('#addsubCategoryModal').modal('hide');
            });

            // ── Submit new category ──────────────────────────────────────────────────────
            $('#categoryform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.category-error-msg').text('');

                const formdata = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: "{{ route('category.store') }}",
                    data: formdata,
                    success: function(response) {
                        if (response.status == 200) {
                            $('#categoryform')[0].reset();
                            $('#addCategoryModal').modal('hide');
                            loadCategory(response.category_id);
                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: response.message
                            });
                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#category-error-' + key).text(value[0]);
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: 'An error occurred'
                            });
                        }
                    }
                });
            });
            // ── Submit new Sub category ──────────────────────────────────────────────────────
            $('#subcategoryform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.subcategory-error-msg').text('');

                $('#sub_category_parent_id').val($('#income_category').val());

                const formdata = $(this).serialize();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('subcategory.store') }}",
                    data: formdata,
                    success: function(response) {
                        if (response.status == 200) {
                            $('#subcategoryform')[0].reset();
                            $('#addsubCategoryModal').modal('hide');
                            category_id = response.data.category_id;
                            subcategory_id = response.data.subcategory_id;
                            loadsubCategory(category_id, subcategory_id);
                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: response.message
                            });
                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#subcategory-error-' + key).text(value[
                                    0]);
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: 'An error occurred'
                            });
                        }
                    }
                });
            });
            // load paidby (customer)
            function loadPaidby(selectedId = '') {
                console.log(selectedId);
                let customer_id = selectedId;
                loadershow();
                $('#paid_by').html(
                    `<option value="">Select Paid By</option><option value="add_new">+ Add New Paid By</option>`
                );

                ajaxRequest('GET', "{{ route('customer.accountincomecustomer') }}", {
                    token: API_TOKEN,
                    company_id: COMPANY_ID,
                    user_id: USER_ID,
                    customer_id: customer_id
                }).done(function(response) {
                    if (response.status == 200 && response.data != '') {
                        $.each(response.customer, function(key, value) {
                            const customerLabel = [
                                value.firstname,
                                value.lastname,
                                value.company_name,
                                value.contact_no
                            ].filter(Boolean).join(' - ');
                            const name = [
                                value.firstname,
                                value.lastname
                            ].filter(Boolean).join(' ');
                            $('#paid_by').append(
                                `<option value="${value.id}" data-name="${name}">${customerLabel}</option>`
                            );
                        });
                    } else {
                        $('#paid_by').append(`<option disabled>No Paid By Found</option>`);
                    }
                    if ($('#paid_by').data('select2')) {
                        $('#paid_by').select2('destroy');
                    }
                    $('#paid_by').val(selectedId).select2({
                        placeholder: 'Select Paid By',
                        allowClear: true,
                    });
                    if (selectedId) {
                        const selectedOption = $('#paid_by option[value="' + selectedId + '"]');
                        $('#customer_id').val(selectedId);
                        $('input[name="paid_by"]').val(selectedOption.data('name') || '');
                    }
                    loaderhide();
                }).fail(function(xhr) {
                    loaderhide();
                    handleAjaxError(xhr);
                });
            }
            //whene paid by chagne 
            $('#paid_by').on('change', function() {

                var payid = $(this).val();
                var selectedOption = $(this).find('option:selected');
                if (payid == 'add_new') {
                    $('#exampleModalScrollable').modal('show'); // 👈 opens its own modal
                    $('#customer_id').val('');
                    $('#paid_by_name').val('');
                } else {
                    $('#customer_id').val(payid);
                    $('#paid_by_name').val(selectedOption.data('name') || '');
                }
                loaderhide();
            });
            // add new paid by (customer) modal cancel btn
            $('#modal_cancelBtn').on('click', function() {
                $('#customerform')[0].reset();
                $('#exampleModalScrollable').modal('hide');
                $('#paid_by option:first').prop('selected', true);
            })
            // ── Submit new paid by (customer) ──────────────────────────────────────────────────────
            $('#customerform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.modal-error-msg').text('');
                const formdata = $(this).serialize();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('customer.store') }}",
                    data: formdata,
                    success: function(response) {
                        // Handle the response from the server
                        if (response.status == 200) {
                            $('#customerform')[0].reset();
                            $('#exampleModalScrollable').modal('hide');
                            // You can perform additional actions, such as showing a success message or redirecting the user
                            loadPaidby(response.customer_id);
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });

                        } else if (response.status == 500) {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        }
                        loaderhide();
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr
                            .responseText); // Log the full error response for debugging
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorcontainer;
                            $.each(errors, function(key, value) {
                                $('#modal-error-' + key).text(value[0]);
                                errorcontainer = '#modal-error-' + key;
                            });
                            $('.modal-body').animate({
                                scrollTop: $(errorcontainer).position().top
                            }, 1000);
                        } else {
                            var errorMessage = "";
                            try {
                                var responseJSON = JSON.parse(xhr.responseText);
                                errorMessage = responseJSON.message || "An error occurred";
                            } catch (e) {
                                errorMessage = "An error occurred";
                            }
                            Toast.fire({
                                icon: "error",
                                title: errorMessage
                            });
                        }
                    }
                });
            });
            // redirect on consignee list page on click cancel btn
            $('#cancelbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.income') }}";
            });

            // subimt form
            $('#incomeupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                var formdata = new FormData($(this)[0]);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('income.update', $edit_id) }}",
                    data: formdata,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Handle the response from the server
                        if (response.status == 200) {
                            // You can perform additional actions, such as showing a success message or redirecting the user
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });

                            window.location.href = "{{ route('admin.income') }}";

                        } else if (response.status == 500) {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        }
                        loaderhide();
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr
                            .responseText); // Log the full error response for debugging
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            let firstErrorElement = null;

                            $.each(errors, function(key, value) {
                                let errorElement = $('#error-' + key);
                                errorElement.text(value[0]);

                                // Capture the first error element
                                if (!firstErrorElement) {
                                    firstErrorElement = errorElement;
                                }
                            });

                            if (firstErrorElement) {
                                $('html, body').animate({
                                    scrollTop: firstErrorElement.offset().top -
                                        100 // adjust for spacing
                                }, 800);
                            }
                        } else {
                            var errorMessage = "";
                            try {
                                var responseJSON = JSON.parse(xhr.responseText);
                                errorMessage = responseJSON.message || "An error occurred";
                            } catch (e) {
                                errorMessage = "An error occurred";
                            }
                            Toast.fire({
                                icon: "error",
                                title: errorMessage
                            });
                        }
                    }
                });
            })
        });
    </script>
@endpush
