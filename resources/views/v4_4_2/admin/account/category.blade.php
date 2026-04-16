@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')
@section('page_title')
    {{ config('app.name') }} - Category
@endsection
@section('table_title')
    Category
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
@if (session('user_permissions.accountmodule.expense.add') == '1')
    @section('addnew')
        {{ route('admin.addcategory') }}
    @endsection
    @section('addnewbutton')
        <button class="btn btn-sm btn-primary">
            <span data-toggle="tooltip" data-placement="bottom" data-original-title="Add New Category" class="">+ Add
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
                <h6>Category Type</h6>
            </div>
            <div class="card-body">
                <select class="form-control filter" name="filter_type" id="filter_type">
                    <option value="">Select Type</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>
        </div>
    </div>
@endsection
@section('table-content')
    <table id="data" class="dataTable display table table-bordered table-striped w-100">
        <thead>
            <tr>
                <th style="width: 5%;">Id</th>
                <th style="width: 20%;">Name</th>
                <th style="width: 10%;">Type</th>
                <th style="width: 25%;">Subcategories</th>
                <th style="width: 10%;">View</th>
                <th style="width: 15%; text-align: center;">Add Subcategory</th>
                <th style="width: 15%;">Action</th>
            </tr>
        </thead>
        <tbody id="tabledata">

        </tbody>
    </table>
    <div class="modal fade" id="addsubcategoryModel" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="addSubcategoryForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Add Sub Category — <span id="modal_category_name" class="text-primary"></span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <small class="text-muted">Existing Subcategory</small>
                        <div id="subcategoryName" class="mt-2"></div>
                        <input type="hidden" id="modal_category_id" name="category_id">
                        <div class="form-group">
                            <label for="sub_name">Sub Category Name</label><span style="color:red;">*</span>
                            <input type="text" id="sub_name" name="sub_name" class="form-control"
                                placeholder="Enter Sub Category Name">
                            <span id="error-sub_name" style="color:red;"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="sub_savebtn" class="btn btn-primary">Save</button>
                        <button type="button" id="sub_cancelbtn" class="btn btn-secondary">Cancel</button>
                    </div>
                </div>
            </form>
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

            var global_response = '';

            let table = '';

            $('#filter_type').select2({
                placeholder: 'Select category',
                allowClear: true,
                searchable:true,
            });

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
                        url: "{{ route('category.index') }}",
                        data: function(d) {
                            d.user_id = "{{ session()->get('user_id') }}";
                            d.company_id = "{{ session()->get('company_id') }}";
                            d.token = "{{ session()->get('api_token') }}";
                            d.filter_type = $('#filter_type').val();
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
                            data: 'category_id',
                            name: 'category_id',
                            orderable: true,
                            searchable: false,
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
                            data: 'category_type',
                            name: 'category_type',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'subcategories',
                            name: 'subcategories',
                            orderable: true,
                            searchable: true,
                            render: function(data, type, row) {
                                if (!data) return '-';

                                const maxLength = 50;
                                let displayText = data;

                                if (data.length > maxLength) {
                                    displayText = data.substr(0, maxLength) + '...';
                                }

                                return `<span data-toggle="tooltip" title="${data}">${displayText}</span>`;
                            },
                            defaultContent: '-'
                        },
                        {
                            data: 'category_id',
                            name: 'category_id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                actionBtns = '';
                                @if (session('user_permissions.accountmodule.category.view') == '1')
                                    actionBtns += ` 
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="View category Details">
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
                            data: 'category_id',
                            orderable: false,
                            searchable: false,
                            render: function(data) {
                                let btn = '';
                                @if (session('user_permissions.accountmodule.category.add') == '1')
                                    btn = `
                                    <span data-toggle="tooltip" data-placement="bottom" data-original-title="Add Sub Category">
                                        <button type="button" data-view="${data}" data-toggle="modal"
                                            data-target="#addsubcategoryModel"
                                            class="add-subcategory-btn btn btn-warning btn-rounded btn-sm my-0">
                                            <i class="ri-list-check-2"></i>
                                        </button>
                                    </span>`;
                                @endif
                                return btn;
                            }
                        },
                        {
                            data: 'category_id',
                            name: 'category_id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                let actionBtns = '';

                                @if (session('user_permissions.accountmodule.category.edit') == '1')
                                    let editexpenseUrl =
                                        `{{ route('admin.editcategory', '__id__') }}`.replace(
                                            '__id__', data);
                                    actionBtns += `
                                        <span class="" data-toggle="tooltip" data-placement="bottom" data-original-title="Edit category Details">
                                            <a href=${editexpenseUrl}>
                                                <button type="button" class="btn btn-success btn-rounded btn-sm my-0 mb-1">
                                                    <i class="ri-edit-fill"></i>
                                                </button>
                                            </a>
                                        </span>
                                        `;
                                @endif

                                @if (session('user_permissions.accountmodule.category.delete') == '1')

                                    actionBtns += `
                                        <span class="" data-toggle="tooltip" data-placement="bottom" data-original-title="Delete Category Details">
                                                <button type="button" data-id= '${data}' class=" del-btn btn btn-danger btn-rounded btn-sm my-0 mb-1">
                                                    <i class="ri-delete-bin-fill"></i>
                                                </button>
                                            </span>
                                        `;
                                @endif

                                return actionBtns;
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

            loaddata();
            $(document).on('click', '.add-subcategory-btn', function() {
                var categoryId = $(this).data('view');

                var categoryName = $.grep(global_response.data, function(item) {
                    return item.category_id == categoryId;
                })[0]?.category_name ?? '';
                var subcategoryName = $.grep(global_response.data, function(item) {
                    return item.category_id == categoryId;
                })[0]?.subcategories ?? '';
                let items = subcategoryName.split(',');

                let html = '<div class="mb-3">';
                items.forEach(function(item) {
                    html += `<span class="badge badge-primary mr-1 mb-1 p-1">${item.trim()}</span>`;
                });
                html += '</div>';

                $('#subcategoryName').html(html);
                $('#modal_category_id').val(categoryId);
                $('#modal_category_name').text(categoryName);
                $('#sub_name').val('');
                $('#error-sub_name').text('');
                $('#addsubcategoryModel').modal('show').on('shown.bs.modal', function() {
                    $('#sub_name').focus();
                });

            });

            $('#sub_cancelbtn').on('click', function() {
                $('#addSubcategoryForm')[0].reset();
                $('#error-sub_name').text('');
                $('#addsubcategoryModel').modal('hide');
            });

            $('#addSubcategoryForm').on('submit', function(e) {
                e.preventDefault();
                $('#error-sub_name').text('');

                var name = $('#sub_name').val().trim();
                var categoryId = $('#modal_category_id').val();

                if (name == '') {
                    $('#error-sub_name').text('Sub Category Name is required.');
                    return;
                }

                loadershow();

                $.ajax({
                    type: 'POST',
                    url: "{{ route('subcategory.store') }}",
                    data: {
                        token: "{{ session('api_token') }}",
                        company_id: "{{ session('company_id') }}",
                        user_id: "{{ session('user_id') }}",
                        category_id: categoryId,
                        name: name,
                    },
                    success: function(response) {
                        if (response.status == 200) {
                            $('#sub_name').val('');
                            $('#addsubcategoryModel').modal('hide');
                            table.draw();
                            Toast.fire({
                                icon: "success",
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
                    error: function(xhr) {
                        loaderhide();
                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                $('#error-sub_name').text(value[0]);
                            });
                        } else {
                            handleAjaxError(xhr);
                        }
                    }
                });
            });
            // record delete 
            $(document).on("click", ".del-btn", function() {
                var deleteid = $(this).data('id');
                var row = this;
                showConfirmationDialog(
                    'Delete Category?',
                    'This will also delete all subcategories under this category. Are you sure?',
                    'Yes, Delete',
                    'No, Cancel',
                    'warning',
                    () => {

                        loadershow();
                        let expenseDeleteUrl = "{{ route('category.delete', '__deleteId__') }}"
                            .replace(
                                '__deleteId__', deleteid);
                        $.ajax({
                            type: 'PUT',
                            url: expenseDeleteUrl,
                            data: {
                                token: "{{ session()->get('api_token') }}",
                                company_id: "{{ session()->get('company_id') }}",
                                user_id: "{{ session()->get('user_id') }}",
                            },
                            success: function(response) {
                                loaderhide();
                                if (response.status == 200) {
                                    Toast.fire({
                                        icon: "success",
                                        title: response.message
                                    });
                                    table.draw();
                                } else if (response.status == 500) {
                                    Toast.fire({
                                        icon: "error",
                                        title: response.message
                                    });
                                }
                            },
                            error: function(xhr, status,
                                error) {
                                loaderhide();
                                console.log(xhr
                                    .responseText
                                );
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

            // view record
            $(document).on("click", ".view-btn", function() {
                $('#details').html('');

                var viewId = $(this).data('view');

                var data = $.grep(global_response.data, function(item) {
                    return item.category_id == viewId;
                })[0];

                if (!data) return;

                var badgeClass = data.category_type == 'income' ? 'badge-success' : 'badge-danger';

                var subcategoryHtml = '<span class="text-muted">No Sub Categories</span>';
                if (data.subcategories && data.subcategories.trim() != '') {
                    subcategoryHtml = data.subcategories.split(',').map(function(sub) {
                        return `<span class="badge ${badgeClass} mr-1 mb-1">${sub.trim()}</span>`;
                    }).join('');
                }

                $('#details').append(`
                    <tr>
                        <th>Category ID</th>
                        <td>${data.category_id || '-'}</td>
                    </tr>
                    <tr>
                        <th>Category Name</th>
                        <td>${data.category_name || '-'}</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>
                            <span class="badge ${data.category_type == 'income' ? 'badge-success' : 'badge-danger'}">
                                ${data.category_type || '-'}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Sub Categories</th>
                        <td>${subcategoryHtml}</td>
                    </tr>
                `);
            });
            $('.applyfilters').on('click', function() {
                table.draw();
                hideOffCanvass(); // close OffCanvass
            });

            //remove filtres
            $('.removefilters').on('click', function() {
                $('#filter_type').val(null).trigger('change');
                table.draw();
                hideOffCanvass(); // close OffCanvass
            });


        });
    </script>
@endpush
