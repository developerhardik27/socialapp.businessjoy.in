@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')
@section('page_title')
    {{ config('app.name') }} - Incomes
@endsection
@section('table_title')
    Incomes
@endsection
@section('style')
    <style>
        .ui-widget-header {
            background: #1518b117 !important;
            border: 1px solid #e1ded9 !important;
        }

        .btn-info {
            background-color: #253566 !important;
            border-color: #253566 !important;
            color: white;
        }

        .btn-info:hover {
            background-color: #39519b !important;
            color: rgb(255, 255, 255);
        }

        .btn-success {
            background-color: #67d5a5d9 !important;
            border-color: var(--iq-success) !important;
            color: black !important;
        }

        .btn-success:hover {
            background-color: #16d07ffa !important;
            border-color: var(--iq-success) !important;
            color: rgb(250, 250, 250) !important;
        }
    </style>
@endsection

@if (session('user_permissions.accountmodule.income.add') == '1')
    @section('addnew')
        {{ route('admin.addincome') }}
    @endsection
    @section('addnewbutton')
        <button class="btn btn-sm btn-primary">
            <span data-toggle="tooltip" data-placement="bottom" data-original-title="Add New Income" class="">+ Add
                New</span>
        </button>
    @endsection
@endif
@section('advancefilter')
    <div class="col-sm-12 text-right">
        <button class="btn btn-sm btn-primary m-0 mr-3" data-toggle="tooltip" data-placement="bottom"
            data-original-title="Filters" onclick="showOffCannvas()">
            <i class="ri-filter-line"></i>
        </button>
    </div>
@endsection

@section('sidebar-filters')
    <div class="col-12 p-0">
        <div class="card">
            <div class="card-header">
                <h6>Paid By </h6>
            </div>
            <div class="card-body">
                <select class="filter select2 form-control w-100" name="filter_paid_by" id="filter_paid_by">

                </select>

            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h6>Category</h6>
            </div>
            <div class="card-body">
                <select class="filter select2 form-control w-100" name="filter_category" id="filter_category">

                </select>

            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h6>Sub Category</h6>
            </div>
            <div class="card-body">
                <select class="filter select2 form-control w-100" name="filter_subcategory" id="filter_subcategory">

                </select>

            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h6>Created Date</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-1">
                        <label for="filter_from_date" class="form-label">From:</label>
                        <input type="date" id="filter_from_date" class="filter form-input form-control">
                    </div>
                    <div class="col-md-6 mb-1">
                        <label for="filter_to_date" class="form-label">To:</label>
                        <input type="date" id="filter_to_date" class="filter form-input form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('table-content')
    <table id="data" class="dataTable display table table-bordered table-striped w-100">
        <thead>
            <tr>
                <th>Receipt No</th>
                <th>Voucher No</th>
                <th>Reference no</th>
                <th>Category</th>
                <th>Sub Category</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Description</th>
                <th>View</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="tabledata">

        </tbody>
    </table>
@endsection

@push('ajax')
    <script>
        $('document').ready(function() {
            // companyId and userId both are required in every ajax request for all action *************
            // response status == 200 that means response succesfully recieved
            // response status == 500 that means database not found
            // response status == 422 that means api has not got valid or required data



            function getPadiByData() {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('customer.accountincomecustomer') }}",
                        data: {
                            user_id: "{{ session()->get('user_id') }}",
                            company_id: "{{ session()->get('company_id') }}",
                            token: "{{ session()->get('api_token') }}",
                            customer_id: "all"
                        },
                        success: function(response) {
                            resolve(response);
                        },
                        error: function(xhr, status, error) { // if calling api request error 
                            loaderhide();
                            console.log(xhr
                                .responseText); // Log the full error response for debugging
                            handleAjaxError(xhr);
                            reject(xhr);
                        }
                    });
                });
            }

            function getCategoryData() {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('income.category') }}",
                        data: {
                            user_id: "{{ session()->get('user_id') }}",
                            company_id: "{{ session()->get('company_id') }}",
                            token: "{{ session()->get('api_token') }}",
                            customer_id: "all"
                        },
                        success: function(response) {
                            resolve(response);
                        },
                        error: function(xhr, status, error) { // if calling api request error 
                            loaderhide();
                            console.log(xhr
                                .responseText); // Log the full error response for debugging
                            handleAjaxError(xhr);
                            reject(xhr);
                        }
                    });
                });
            }

            function loadFilters() {
                return new Promise((resolve, reject) => {
                    var filterData = JSON.parse(sessionStorage.getItem('filterData'));
                    if (filterData) {
                        $.each(filterData, function(key, value) {
                            if (value != ' ') {
                                $('#' + key).val(value); // Removed `, true`
                            }
                        });

                        // Trigger change event to ensure multiselect UI updates
                        $('#filter_paid_by', '#filter_category', '#filter_subcategory')
                            .trigger('change');

                        loaddata();


                        sessionStorage.removeItem('filterData');
                        loaderhide();
                        resolve(); // Resolve the promise here after all actions
                    } else {
                        // If no filter data, resolve immediately
                        resolve();
                        loaddata();
                    }
                });
            }


            async function initialize() {
                try {
                    // Perform AJAX calls concurrently
                    const [padiByDataResponse, categoryDataResponse] = await Promise
                        .all([
                            getPadiByData(),
                            getCategoryData()
                        ]);


                    // Check if user data is successfully fetched
                    if (padiByDataResponse.status == 200 && padiByDataResponse.customer != '') {
                        // You can update your HTML with the data here if needed     
                        $.each(padiByDataResponse.customer, function(key, value) {
                            var optionValue = value.firstname + (value.lastname ? ' ' + value.lastname :
                                '');
                            var id = value.id;
                            $('#filter_paid_by').append(
                                `<option value="${id}">${optionValue}</option>`);
                        });

                        $('#filter_paid_by').val('');
                        $('#filter_paid_by').select2({
                            search: true,
                            placeholder: 'Select Paid By',
                            allowClear: true // Optional: adds "clear" (x) button
                        });
                    } else if (padiByDataResponse.status == 500) {
                        Toast.fire({
                            icon: "error",
                            title: padiByDataResponse.message
                        });
                    } else {
                        $('#filter_paid_by').val('');
                        $('#filter_paid_by').select2({
                            search: true,
                            placeholder: 'No user found',
                            allowClear: true // Optional: adds "clear" (x) button
                        });
                    }
                    if (categoryDataResponse.status == 200 && categoryDataResponse.category != '') {
                        // You can update your HTML with the data here if needed     
                        $.each(categoryDataResponse.category, function(key, value) {
                            var optionValue = value.name;
                            var id = value.id;
                            $('#filter_category').append(
                                `<option value="${id}">${optionValue}</option>`);
                        });

                        $('#filter_category').val('');
                        $('#filter_category').select2({
                            search: true,
                            placeholder: 'Select Category',
                            allowClear: true // Optional: adds "clear" (x) button
                        });
                    } else if (categoryDataResponse.status == 500) {
                        Toast.fire({
                            icon: "error",
                            title: categoryDataResponse.message
                        });
                    } else {
                        $('#filter_category').val('');
                        $('#filter_category').select2({
                            search: true,
                            placeholder: 'No category found',
                            allowClear: true // Optional: adds "clear" (x) button
                        });
                    }
                    loaderhide();
                    // Load filters
                    await loadFilters();

                    // Further code execution after successful AJAX calls and HTML appending
                    // Your existing logic here

                } catch (error) {
                    console.error('Error:', error);
                    Toast.fire({
                        icon: "error",
                        title: "An error occurred while initializing"
                    });
                    loaderhide();
                }
            }

            initialize();
            $('#filter_subcategory').select2({
                search: true,
                placeholder: 'Select Category First',
                allowClear: true
            });
            // Load subcategory when category changes
            $(document).on('change', '#filter_category', function() {
                var categoryId = $(this).val();

                // Reset subcategory
                $('#filter_subcategory').empty().val('');
                $('#filter_subcategory').select2({
                    search: true,
                    placeholder: categoryId ? 'Loading...' : 'Select Category First',
                    allowClear: true
                });

                if (!categoryId) return;

                $.ajax({
                    type: 'GET',
                    url: `/api/subcategorylist/${categoryId}`,
                    data: {
                        user_id: "{{ session()->get('user_id') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        token: "{{ session()->get('api_token') }}",
                        category_id: categoryId // ← pass category id
                    },
                    success: function(response) {
                        $('#filter_subcategory').empty().val('');
                        $('#filter_subcategory').append(
                            '<option value="">Select Sub Category</option>');
                        if (response.status == 200 && response.subcategory != '') {
                            $.each(response.subcategory, function(key, value) {
                                $('#filter_subcategory').append(
                                    `<option value="${value.id}">${value.name}</option>`
                                );
                            });
                            $('#filter_subcategory').select2({
                                search: true,
                                placeholder: 'Select Sub Category',
                                allowClear: true
                            });
                        } else {
                            $('#filter_subcategory').select2({
                                search: true,
                                placeholder: 'No sub category found',
                                allowClear: true
                            });
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        $('#filter_subcategory').select2({
                            search: true,
                            placeholder: 'Error loading sub categories',
                            allowClear: true
                        });
                    }
                });
            });
            var global_response = '';

            let table = '';

            // fetch & show income data in table
            function loaddata() {
                loadershow();

                table = $('#data').DataTable({
                    language: {
                        lengthMenu: '_MENU_ &nbsp;Entries per page'
                    },
                    destroy: true, // allows re-initialization
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        type: "GET",
                        url: "{{ route('income.datatable') }}",
                        data: function(d) {
                            d.user_id = "{{ session()->get('user_id') }}";
                            d.company_id = "{{ session()->get('company_id') }}";
                            d.token = "{{ session()->get('api_token') }}";
                            d.filter_paid_by = $('#filter_paid_by').val();
                            d.filter_category = $('#filter_category').val();
                            d.filter_subcategory = $('#filter_subcategory').val();
                            d.filter_from_date = $('#filter_from_date').val();
                            d.filter_to_date = $('#filter_to_date').val();
                        },
                        dataSrc: function(json) {
                            if (json.message) {
                                Toast.fire({
                                    icon: "error",
                                    title: json.message || 'Somethint went wrong!'
                                })
                            }

                            global_response = json;

                            return json.data;
                        },
                        complete: function() {
                            loaderhide();
                        },
                        error: function(xhr) {
                            global_response = '';
                            console.log(xhr.responseText);
                            Toast.fire({
                                icon: "error",
                                title: "Error loading data"
                            });
                        }
                    },
                    order: [
                        [0, 'desc']
                    ],
                    columns: [{
                            data: 'receipt_no',
                            name: 'receipt_no',
                            orderable: true,
                            searchable: false,
                            defaultContent: '-'
                        },
                        {
                            data: 'voucher_no',
                            name: 'voucher_no',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'reference_no',
                            name: 'reference_no',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'category_name',
                            name: 'category_name',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'subcategory_name',
                            name: 'subcategory_name',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'date_formatted',
                            name: 'date_formatted',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'amount',
                            name: 'amount',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'type',
                            name: 'type',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'description',
                            name: 'description',
                            orderable: false,
                            searchable: true,
                            defaultContent: '-',
                            render: function(data, type, row) {
                                return `<div style="white-space: pre-line;">${(data || '-').replace(/(&lt;br\s*\/?&gt;|<br\s*\/?>)/gi, '\n')}</div>`;
                            }
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                actionBtns = '';
                                @if (session('user_permissions.accountmodule.income.view') == '1')
                                    actionBtns += ` 
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="View Income Details">
                                            <button type="button" data-view = '${data}' data-toggle="modal" data-target="#exampleModalScrollable" class="view-btn btn btn-info btn-rounded btn-sm my-0">
                                                <i class="ri-indent-decrease"></i>
                                            </button>
                                        </span>  
                                    `;
                                @endif

                                return actionBtns;
                            }
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                let actionBtns = '';

                                @if (session('user_permissions.accountmodule.income.edit') == '1')
                                    if (row.entry_type == 'm') {
                                        let editincomeUrl =
                                            `{{ route('admin.editincome', '__id__') }}`.replace(
                                                '__id__', data);
                                        actionBtns += `
                                            <span class="" data-toggle="tooltip" data-placement="bottom" data-original-title="Edit Income Details">
                                                <a href=${editincomeUrl}>
                                                    <button type="button" class="btn btn-success btn-rounded btn-sm my-0 mb-1">
                                                        <i class="ri-edit-fill"></i>
                                                    </button>
                                                </a>
                                            </span>
                                        `;
                                    }
                                @endif

                                @if (session('user_permissions.accountmodule.income.delete') == '1')
                                    if (row.entry_type == 'm') {
                                        actionBtns += `
                                                 <span class="" data-toggle="tooltip" data-placement="bottom" data-original-title="Delete Income Details">
                                                        <button type="button" data-id= '${data}' class=" del-btn btn btn-danger btn-rounded btn-sm my-0 mb-1">
                                                            <i class="ri-delete-bin-fill"></i>
                                                        </button>
                                                    </span>
                                            `;
                                    }
                                @endif

                                return actionBtns || 'No Action available';
                            }
                        }

                    ],

                    pagingType: "full_numbers",
                    drawCallback: function(settings) {
                        $('[data-toggle="tooltip"]').tooltip();

                        // 👇 Jump to Page input injection
                        if ($('#jumpToPageWrapper').length === 0) {
                            let jumpHtml = `
                                    <div id="jumpToPageWrapper" class="d-flex align-items-center ml-3" style="gap: 5px;">
                                        <label for="jumpToPage" class="mb-0">Jump to page:</label>
                                        <input type="number" id="jumpToPage" min="1" class="dt-input" style="width: 80px;" />
                                        <button id="jumpToPageBtn" class="btn btn-sm btn-primary">Go</button>
                                    </div>
                                `;
                            $(".dt-paging").after(jumpHtml);
                        }


                        $(document).off('click', '#jumpToPageBtn').on('click', '#jumpToPageBtn',
                            function() {
                                let table = $('#data').DataTable();
                                // Check if table is initialized
                                if ($.fn.DataTable.isDataTable('#data')) {
                                    let page = parseInt($('#jumpToPage').val());
                                    let totalPages = table.page.info().pages;

                                    if (!isNaN(page) && page > 0 && page <= totalPages) {
                                        table.page(page - 1).draw('page');
                                    } else {
                                        Toast.fire({
                                            icon: "error",
                                            title: `Please enter a page number between 1 and ${totalPages}`
                                        });
                                    }
                                } else {

                                    Toast.fire({
                                        icon: "error",
                                        title: `DataTable not yet initialized.`
                                    });
                                }
                            }
                        );
                    }
                });
            }
  
            // record delete 
            $(document).on("click", ".del-btn", function() {
                var deleteid = $(this).data('id');
                var row = this;
                showConfirmationDialog(
                    'Are you sure?', // Title
                    'to delete this record?', // Text
                    'Yes, delete', // Confirm button text
                    'No, cancel', // Cancel button text
                    'question', // Icon type (question icon)
                    () => {
                        // Success callback
                        loadershow();
                        let incomeDeleteUrl = "{{ route('income.delete', '__deleteId__') }}".replace(
                            '__deleteId__', deleteid);
                        $.ajax({
                            type: 'PUT',
                            url: incomeDeleteUrl,
                            data: {
                                token: "{{ session()->get('api_token') }}",
                                company_id: "{{ session()->get('company_id') }}",
                                user_id: "{{ session()->get('user_id') }}",
                            },
                            success: function(response) {
                                loaderhide();
                                if (response.status == 200) {
                                    table.draw();
                                } else if (response.status == 500) {
                                    Toast.fire({
                                        icon: "error",
                                        title: response.message
                                    });
                                }
                            },
                            error: function(xhr, status,
                                error) { // if calling api request error 
                                loaderhide();
                                console.log(xhr
                                    .responseText
                                ); // Log the full error response for debugging
                                var errorMessage = "";
                                try {
                                    var responseJSON = JSON.parse(xhr.responseText);
                                    errorMessage = responseJSON.message ||
                                        "An error occurred";
                                } catch (e) {
                                    errorMessage = "An error occurred";
                                }
                                Toast.fire({
                                    icon: "error",
                                    title: errorMessage
                                });
                            }
                        });
                    }
                );
            });
            $('.applyfilters').on('click', function() {
                table.draw();
                hideOffCanvass(); // close OffCanvass
            });

            //remove filtres
            $('.removefilters').on('click', function() {
                $('#filter_paid_by').val(null).trigger('change');
                $('#filter_category').val(null).trigger('change');
                $('#filter_subcategory').val(null).trigger('change');
                $('#filter_from_date').val('');
                $('#filter_to_date').val('');
                table.draw();
                hideOffCanvass(); // close OffCanvass
            });
            // view record
            $(document).on("click", ".view-btn", function() {
                $('#details').html('');
                var data = $(this).data('view'); // get coachid

                $.each(global_response.data, function(key, income) {
                    if (income.id == data) {
                        $('#details').append(`
                            <tr>
                                <th>Receipt No</th>                         
                                <td>${income.receipt_no || '-'}</td>
                            </tr>
                            <tr>
                                <th>Voucher No</th>                         
                                <td>${income.voucher_no || '-'}</td>
                            </tr>
                            <tr>
                                <th>Category</th>                         
                                <td>${income.category_name || '-'}</td>
                            </tr>
                            <tr>
                                <th>Sub Category</th>                         
                                <td>${income.subcategory_name || '-'}</td>
                            </tr>
                            <tr>
                                <th>Date</th>                         
                                <td>${income.date_formatted || '-'}</td>
                            </tr>
                            <tr>
                                <th>Amount</th>                         
                                <td>${income.amount || '-'}</td>
                            </tr>
                            <tr>
                                <th>Type</th>                         
                                <td>${income.type || '-'}</td>
                            </tr>
                            <tr>
                                <th>Paid By</th>                         
                                <td>${income.paid_by || '-'}</td>
                            </tr>
                            <tr>
                                <th>Payment Type</th>                         
                                <td>${income.payment_type || '-'}</td>
                            </tr>
                            <tr>
                                <th>Description</th>                         
                                <td><div style="white-space: pre-line;">${(income.description || '-').replace(/(&lt;br\s*\/?&gt;|<br\s*\/?>)/gi, '\n')}</div></td>
                            </tr> 
                        `);
                    }
                });
            });

        });
    </script>
@endpush
