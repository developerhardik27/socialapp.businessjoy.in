@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')
@section('page_title')
    {{ config('app.name') }} - Expenses
@endsection
@section('table_title')
    Expenses
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
        {{ route('admin.addexpense') }}
    @endsection
    @section('addnewbutton')
        <button class="btn btn-sm btn-primary">
            <span data-toggle="tooltip" data-placement="bottom" data-original-title="Add New Expense" class="">+ Add
                New</span>
        </button>
    @endsection
@endif

@section('table-content')
    <table id="data" class="dataTable display table table-bordered table-striped w-100">
        <thead>
            <tr>
                <th>Id</th>
                <th>Voucher No</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Paid to</th>
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

            var global_response = '';

            let table = '';


            // fetch & show coach data in table
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
                        url: "{{ route('expense.datatable') }}",
                        data: function(d) {
                            d.user_id = "{{ session()->get('user_id') }}";
                            d.company_id = "{{ session()->get('company_id') }}";
                            d.token = "{{ session()->get('api_token') }}";
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
                            data: 'id',
                            name: 'id',
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
                            data: 'paid_to',
                            name: 'paid_to',
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
                                @if (session('user_permissions.accountmodule.expense.view') == '1')
                                    actionBtns += ` 
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="View expense Details">
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
  
                                @if (session('user_permissions.accountmodule.expense.edit') == '1')
                                    if (row.entry_type == 'm') {
                                        let editexpenseUrl =
                                            `{{ route('admin.editexpense', '__id__') }}`.replace(
                                                '__id__', data);
                                        actionBtns += `
                                            <span class="" data-toggle="tooltip" data-placement="bottom" data-original-title="Edit Expense Details">
                                                <a href=${editexpenseUrl}>
                                                    <button type="button" class="btn btn-success btn-rounded btn-sm my-0 mb-1">
                                                        <i class="ri-edit-fill"></i>
                                                    </button>
                                                </a>
                                            </span>
                                        `;
                                    }
                                @endif

                                @if (session('user_permissions.accountmodule.expense.delete') == '1')
                                    if (row.entry_type == 'm') {
                                        actionBtns += `
                                            <span class="" data-toggle="tooltip" data-placement="bottom" data-original-title="Delete Expense Details">
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

            //call function for load coach in table
            loaddata();


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
                        let expenseDeleteUrl = "{{ route('expense.delete', '__deleteId__') }}".replace(
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

            // view record
            $(document).on("click", ".view-btn", function() {
                $('#details').html('');
                var data = $(this).data('view'); // get coachid

                $.each(global_response.data, function(key, expense) {
                    if (expense.id == data) {
                        $('#details').append(`
                            <tr>
                                <th>Voucher No</th>                         
                                <td>${expense.voucher_no || '-'}</td>
                            </tr>
                            <tr>
                                <th>Date</th>                         
                                <td>${expense.date_formatted || '-'}</td>
                            </tr>
                            <tr>
                                <th>Amount</th>                         
                                <td>${expense.amount || '-'}</td>
                            </tr>
                            <tr>
                                <th>Paid To</th>                         
                                <td>${expense.paid_to || '-'}</td>
                            </tr>
                            <tr>
                                <th>Payment Type</th>                         
                                <td>${expense.payment_type || '-'}</td>
                            </tr>
                            <tr>
                                <th>Description</th>                         
                                <td><div style="white-space: pre-line;">${(expense.description || '-').replace(/(&lt;br\s*\/?&gt;|<br\s*\/?>)/gi, '\n')}</div></td>
                            </tr> 
                        `);
                    }
                });
            });
 
        });
    </script>
@endpush
