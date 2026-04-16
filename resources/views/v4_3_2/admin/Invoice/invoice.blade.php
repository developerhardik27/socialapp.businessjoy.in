@php
    $folder = session('folder_name');
@endphp

@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Invoicelist
@endsection

@section('table_title')
    Invoice
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

        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection

@if (session('user_permissions.invoicemodule.invoice.add') == '1')
    @section('addnew')
        {{ route('admin.addinvoice') }}
    @endsection
    @section('addnewbutton')
        <button class="btn btn-sm btn-primary">
            <span class="" data-toggle="tooltip" data-placement="bottom" data-original-title="Create New Invoice">+ Create
                New</span>
        </button>
    @endsection
@endif

@section('table-content')
    <table id="data" class="table display table-bordered w-100 table-striped">
        <thead>
            <tr>
                <th>Invoice ID</th>
                <th>Invoice Date</th>
                <th>Customer/Company Name</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Invoice</th>
                <th>Payment</th>
                @if (session('user_permissions.invoicemodule.invoicecommission.show') == '1' && $commission == 1)
                    <th>Commission</th>
                @endif
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="tabledata">

        </tbody>
    </table>

    <div class="modal fade" id="paymentmodal" tabindex="-1" role="dialog" aria-labelledby="viewpaymentmodalTitle"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewpaymentmodalTitle"><b>Payment</b></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="paymentform">
                    <div class="modal-body">
                        @csrf
                        <div class="payment_details">
                            <input type="hidden" name="user_id" class="form-control" value="{{ session('user_id') }}"
                                required />
                            <input type="hidden" name="company_id" class="form-control" value="{{ session('company_id') }}"
                                required />
                            <input type="hidden" name="token" class="form-control" value="{{ session('api_token') }}"
                                placeholder="token" required />
                            <input type="hidden" name="inv_id" id="inv_id">
                            <label for="transid">Transaction ID</label>
                            <input type="text" name="transid" class="form-control" id="transid"
                                placeholder="Transaction id" />
                            <p class="modal_error-msg mb-1" id="modal_error-transid" style="color: red"></p>
                            <label for="payment_date">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" id="payment_date" required />
                            <p class="modal_error-msg mb-1" id="modal_error-payment_date" style="color: red"></p>
                            Total Amount :-&nbsp;<span class="mb-1 text-info" id="info-total_amount">0</span>,
                            &nbsp;Received Amount :-&nbsp;<span class="mb-1 text-info"
                                id="info-total_received_amount">0</span><br>
                            <label for="paidamount">New Amount</label>
                            <input type="number" name="paidamount" class="form-control" id="paidamount"
                                placeholder="New Amount" required />
                            <p class="modal_error-msg mb-1" id="modal_error-paidamount" style="color: red"></p>
                            Pending Amount :-&nbsp;<span class="mb-1 text-info info-pending_amount">0</span><br>
                            <label for="paid_by">Paid By</label>
                            <input type="text" name="paid_by" class="form-control" id="paid_by"
                                placeholder="Who Paid Amount" />
                            <p class="modal_error-msg mb-1" id="modal_error-paid_by" style="color: red"></p>
                            <label for="payment_type">How They Paid</label>
                            <select class="form-control" name="payment_type" id="payment_type">
                                <option selected="" disabled="">Select Payment Type</option>
                                <option value="Online Payment">Online Payment</option>
                                <option value="Cash">Cash</option>
                                <option value="Check">Check</option>
                            </select>
                            <p class="modal_error-msg mb-1" id="modal_error-payment_type" style="color: red"></p>
                        </div>
                        <div class="tds_details">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="tds_applicable"
                                    id="tds_applicable">
                                <label class="form-check-label" for="tds_applicable">
                                    TDS Applicable
                                </label>
                                <p class="modal_error-msg mb-1" id="modal_error-tds_applicable" style="color: red"></p>
                            </div>
                            <div class="tds_inputs" style="display: none">
                                <hr>
                                <label for="tds_amount">TDS Amount</label>
                                <input type="number" name="tds_amount" class="form-control" id="tds_amount"
                                    placeholder="TDS Amount" />
                                <p class="modal_error-msg mb-1" id="modal_error-tds_amount" style="color: red"></p>
                                Pending Amount :-&nbsp;<span class="mb-1 text-info info-pending_amount">0</span><br>
                                <label for="challan_no">Challan No</label>
                                <input type="text" name="challan_no" class="form-control" id="challan_no"
                                    placeholder="Challan No" />
                                <p class="modal_error-msg mb-1" id="modal_error-challan_no" style="color: red"></p>
                                <label for="status">Status</label>
                                <select class="form-control" name="status" id="status">
                                    <option selected="" disabled="">Select Status</option>
                                    <option value="Recorded">Recorded</option>
                                    <option value="Mapped to Challan">Mapped to Challan</option>
                                    <option value="Filed in Return">Filed in Return</option>
                                    <option value="Reconciled (matches 26AS)"> Reconciled (matches 26AS)</option>
                                </select>
                                <p class="modal_error-msg mb-1" id="modal_error-status" style="color: red"></p>
                            </div>
                        </div>
                        <div class="payment-desciption">
                            <label for="description">Description</label>
                            <textarea type="text" name="description" class="form-control" id="description"
                                placeholder="Enter description..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="" class="btn btn-primary">Submit</button>
                        <button type="reset" class="btn iq-bg-danger">Reset</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="commissionmodal" tabindex="-1" role="dialog" aria-labelledby="commissionmodalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commissionmodalTitle"><b>Add Commission</b></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="custom-modal-content">

                </div>
            </div>
        </div>
    </div>
@endsection

@push('ajax')
    <script>
        let isEventBound = false;
        $('document').ready(function() {
            // companyId and userId both are required in every ajax request for all action *************
            // response status == 200 that means response succesfully recieved
            // response status == 500 that means database not found
            // response status == 422 that means api has not got valid or required data

            @if (Session::has('message'))
                Toast.fire({
                    icon: "error",
                    title: 'You have not any column for download this invoice'
                });
            @endif

            let commission = @json($commission);
            let showCommissionCol = "{{ session('user_permissions.invoicemodule.invoicecommission.show') == '1' && $commission == 1 }}";
            let addCommission = "{{ session('user_permissions.invoicemodule.invoicecommission.add') == '1' }}";
            let viewCommission = "{{ session('user_permissions.invoicemodule.invoicecommission.view') == '1' }}";
            let editCommission = "{{ session('user_permissions.invoicemodule.invoicecommission.edit') == '1' }}";
            let deleteCommission = "{{ session('user_permissions.invoicemodule.invoicecommission.delete') == '1' }}";

            const API_TOKEN = "{{ session()->get('api_token') }}";
            const COMPANY_ID = "{{ session()->get('company_id') }}";
            const USER_ID = "{{ session()->get('user_id') }}";

            let commissionInvId = null;
            let editCommissionId = null;

            let table = '';

            var global_response = '';

            var search = {!! json_encode($search) !!}

            // function for  get invoice data and set it table
            function loaddata() {
                loadershow();

                let columns = [{
                        data: 'inv_no',
                        name: 'inv_no',
                        orderable: true,
                        searchable: true,
                        defaultContent: '-'
                    },
                    {
                        data: 'inv_date_formatted',
                        name: 'inv_date_formatted',
                        orderable: false,
                        searchable: true,
                        defaultContent: '-',
                    },
                    {
                        data: 'customer',
                        name: 'customer',
                        orderable: true,
                        searchable: true,
                        defaultContent: '-'
                    },
                    {
                        data: 'grand_total',
                        name: 'grand_total',
                        orderable: true,
                        searchable: true,
                        defaultContent: '-',
                        render: function(data, type, row) {
                            return `${row.currency_symbol} ${row.grand_total}`;
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: true,
                        searchable: true,
                        defaultContent: '-',
                        render: function(data, type, row) {
                            actions = '-';
                            @if (session('user_permissions.invoicemodule.invoice.edit') == '1')
                                options = '';
                                if (row.part_payment == 1 && row.pending_amount != 0) {
                                    options = ` 
                                        <option value='part_payment' ${row.status == "part_payment" ? 'selected' : ''}>Part Payment</option>
                                        <option value='paid' ${row.status == "paid" ? 'selected' : ''} disabled>Paid</option>
                                        <option value='pending' ${row.status == "pending" ? 'selected' : ''} disabled>Pending</option>
                                    `;
                                }
                                if (row.pending_amount == 0) {
                                    options = `
                                        <option value='part_payment' ${row.status == "part_payment" ? 'selected' : ''} disabled>Part Payment</option>
                                        <option value='paid' ${row.status == "paid" ? 'selected' : ''}> Paid</option>
                                        <option value='pending' ${row.status == "pending" ? 'selected' : ''} disabled>Pending</option>
                                    `;
                                }

                                if (row.part_payment != 1 && row.part_payment != 0) {
                                    options = `
                                        <option value='part_payment' ${row.status == "part_payment" ? 'selected' : ''} disabled>Part Payment</option>
                                        <option value='paid' ${row.status == "paid" ? 'selected' : ''} disabled> Paid</option>
                                        <option value='pending' ${row.status == "pending" ? 'selected' : ''}>Pending</option>
                                    `;
                                }
                                actions = `  
                                    <select data-status='${row.id}' data-original-value="${row.status}" class="status" id="status_${row.id}" name="" required >
                                        ${options}
                                        <option value='cancel' ${row.status == "cancel" ? 'selected' : ''}>Cancel</option>
                                        <option value='due' ${row.status == "due" ? 'selected' : ''}>Over Due</option>
                                    </select>
                                `;
                            @endif

                            return actions;

                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        defaultContent: '-',
                        render: function(data, type, row) {
                            actions = '-';
                            @if (session('user_permissions.invoicemodule.invoice.view') == '1')
                                let generateInvoicePdfUrl =
                                    "{{ route('invoice.generatepdf', '__invoiceId__') }}"
                                    .replace('__invoiceId__', row.id);
                                actions = `                                             
                                    <span data-toggle="tooltip" data-placement="left" data-original-title="Download Invoice Pdf">
                                        <a href=${generateInvoicePdfUrl} target='_blank' id='pdf'>
                                            <button type="button" class="download-btn btn btn-info btn-rounded btn-sm my-0" ><i class="ri-download-line"></i></button>
                                        </a>
                                    </span>
                                `;
                            @endif

                            return actions;

                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        defaultContent: '-',
                        render: function(data, type, row) {
                            let generateInvoiceReceiptAllUrl =
                                "{{ route('invoice.generaterecieptll', '__invoiceId__') }}"
                                .replace('__invoiceId__', row.id);
                            actions = '';
                            if (row.status != 'paid') {
                                actions += `                                             
                                    <span data-toggle="tooltip" data-placement="bottom" data-original-title="Pay">
                                        <button data-toggle="modal" data-target="#paymentmodal" data-amount="${row.grand_total}" data-id='${row.id}' class='btn btn-sm btn-primary m-0 paymentformmodal'>
                                            <i class='ri-paypal-fill'></i>
                                        </button>
                                    </span>
                                `;
                            }
                            if (row.part_payment == 1 && row.status == 'paid' && row
                                .pending_amount == 0) {
                                actions += `                                             
                                    <span> 
                                        <a href=${generateInvoiceReceiptAllUrl} target='_blank'>
                                            <button data-toggle="tooltip" data-placement="bottom" data-original-title="Download Combined Receipt"  class="reciept-btn btn btn-primary btn-rounded btn-sm m-0" >
                                                <i class="ri-download-line"></i>
                                            </button>
                                        </a>
                                    </span>
                                `;
                            }
                            if (row.part_payment == 1) {
                                actions += `                                             
                                    <span data-toggle="tooltip" data-placement="right" data-original-title="View All Reciept"> 
                                        <button  data-id='${row.id}' data-toggle='modal' data-target='#exampleModalScrollable' class='btn btn-sm btn-info my-0 viewpayment' >
                                            <i class='ri-eye-fill'></i> 
                                        </button> 
                                    </span>
                                `;
                            }
                            if (row.part_payment == 0 && row.status == 'paid') {
                                actions += `                                             
                                    <span> 
                                        <a href=${generateInvoiceReceiptAllUrl}  target='_blank' >
                                            <button  class="btn-info reciept-btn btn btn-outline-dark btn-rounded btn-sm my-0" data-toggle="tooltip" data-placement="bottom" data-original-title="Download Single Receipt">
                                                <i class="ri-download-line"></i>
                                            </button>
                                        </a>
                                    </span>
                                    <span data-toggle="tooltip" data-placement="right" data-original-title="Delete Payment Entry">
                                        <button data-id="${row.paymentid}" data-inv-id="${row.id}" class="btn btn-sm btn-outline-danger pay-del-btn">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </span>    
                                `;
                            }
                            return actions;
                        }
                    }
                ];

                if (showCommissionCol) {
                    columns.push({
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        defaultContent: '-',
                        render: function(data, type, row) {
                            actions = '';
                            if (addCommission == 1) {
                                actions += `                                             
                                    <span data-toggle="tooltip" data-placement="bottom" data-original-title="Add Commission">
                                        <button data-id='${row.id}' class='btn btn-sm btn-outline-primary m-0 commissionmodal'>
                                            <i class='ri-money-pound-circle-line'></i>
                                        </button>
                                    </span>
                                `;
                            }
                            if (viewCommission == 1) {
                                actions += `                                             
                                    <span data-toggle="tooltip" data-placement="bottom" data-original-title="View Commission">
                                        <button data-id='${row.id}' class='btn btn-sm btn-outline-primary m-0 viewcommissionmodal'>
                                            <i class='ri-eye-fill'></i>
                                        </button>
                                    </span>   
                                `;
                            }
                            return actions;
                        }
                    });
                }

                columns.push({
                    data: 'id',
                    name: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let actionBtns = '';
                        @if (session('user_permissions.invoicemodule.invoice.edit') == '1')
                            if (row.is_editable == 1) {
                                let invoiceEditUrl =
                                    "{{ route('admin.editinvoice', '__invoiceId__') }}"
                                    .replace('__invoiceId__', row.id);
                                actionBtns += `
                                        <span>  
                                            <a href=${invoiceEditUrl}>
                                                <button type="button" data-id='${row.id}' data-toggle="tooltip" data-placement="bottom" data-original-title="Edit Invoice" class="edit-btn btn btn-success btn-rounded btn-sm my-0">
                                                    <i class="ri-edit-fill"></i>
                                                </button>
                                            </a>
                                        </span>
                                    `;
                            }
                        @endif
                        if (row.company_details_id != global_response.company_details_id) {
                            actionBtns += `
                                <span>
                                    <button type="button" data-id='${row.id}' data-toggle="tooltip" data-placement="bottom" title="Update Company Details"
                                        class="update-company-details-btn btn btn-outline-primary btn-rounded btn-sm my-0">
                                        <i class="ri-file-edit-line"></i>
                                    </button>
                                </span>
                            `;
                        }

                        @if (session('user_permissions.invoicemodule.invoice.delete') == '1')
                            actionBtns += `
                                        <span>
                                        <button type="button" data-id='${row.id}' data-toggle="tooltip" data-placement="bottom" data-original-title="Delete Invoice" class="del-btn btn btn-danger btn-rounded btn-sm my-0">
                                            <i class="ri-delete-bin-fill"></i>
                                        </button>
                                    </span>
                                `;
                        @endif

                        return actionBtns;
                    }
                });

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
                        url: "{{ route('invoice.inv_list') }}",
                        data: function(d) {
                            d.user_id = USER_ID;
                            d.company_id = COMPANY_ID;
                            d.token = API_TOKEN;
                        },
                        dataSrc: function(json) {
                            if (json.message) {
                                Toast.fire({
                                    icon: "error",
                                    title: json.message || 'Something went wrong!'
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
                    search: {
                        search: search
                    },
                    columns: columns,

                    pagingType: "full_numbers",
                    drawCallback: function(settings) {
                        $('[data-toggle="tooltip"]').tooltip({
                            boundary: 'window',
                            offset: '0, 10' // Push tooltip slightly away from the button
                        });

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
            //call data function for load customer data
            loaddata();

            // record delete 
            $(document).on("click", ".del-btn", function() {
                var deleteid = $(this).data('id');
                var row = this;
                let invoiceDeleteUrl = "{{ route('invoice.delete', '__deleteId__') }}".replace(
                    '__deleteId__', deleteid);

                showConfirmationDialog(
                    'Are you sure?',
                    'to delete this invoice?',
                    'Yes, delete it',
                    'No, cancel',
                    'question',
                    () => {
                        loadershow();
                        $.ajax({
                            type: 'PUT',
                            url: invoiceDeleteUrl,
                            data: {
                                token: API_TOKEN,
                                company_id: " {{ session()->get('company_id') }} ",
                                user_id: " {{ session()->get('user_id') }} "
                            },
                            success: function(response) {
                                if (response.status == 200) {
                                    Toast.fire({
                                        icon: "success",
                                        title: response.message
                                    });
                                    loaddata();
                                } else if (response.status == 500) {
                                    Toast.fire({
                                        icon: "error",
                                        title: response.message
                                    });
                                } else {
                                    Toast.fire({
                                        icon: "error",
                                        title: "invoice not deleted."
                                    });
                                }
                                loaderhide();
                            },
                            error: function(xhr, status,
                                error) { // if calling api request error 
                                loaderhide();
                                console.log(xhr
                                    .responseText
                                ); // Log the full error response for debugging
                                handleAjaxError(xhr);
                            }
                        });
                    }
                );


            });


            //status change function
            function statuschange(id, value) {
                loadershow();
                let invoiceStatusUrl = "{{ route('invoice.status', '__id__') }}".replace('__id__', id);
                $.ajax({
                    type: 'PUT',
                    url: invoiceStatusUrl,
                    data: {
                        status: value,
                        token: API_TOKEN,
                        company_id: " {{ session()->get('company_id') }}",
                        user_id: " {{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });
                            loaddata();
                        } else if (response.status == 500) {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: "Status not updated."
                            });
                        }
                        loaderhide();
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr
                            .responseText); // Log the full error response for debugging
                        var errorMessage = "";
                        handleAjaxError(xhr);
                    }
                });
            }

            //call status change function
            $(document).on("change", ".status", function() {
                var element = $(this);
                var oldstatus = element.data('original-value'); //get original invoice status value
                var statusid = element.data('status'); // get invoice id
                var status = element.val(); //get current value
                showConfirmationDialog(
                    'Are you sure?',
                    'to change this record status ?',
                    'Yes, change it',
                    'No, cancel',
                    'question',
                    () => {
                        loadershow();
                        element.data('original-value', status); // set current value to original value
                        statuschange(statusid, status);
                        loaderhide(); // Success callback
                    },
                    () => {
                        $('#status_' + statusid).val(oldstatus);
                    }
                );
            });

            // form reset every time when on click make payment button
            $(document).on('click', '.paymentformmodal', function() {
                $('#paymentform')[0].reset();
                $('.tds_inputs').hide();
                var invoiceid = $(this).data('id');
                var amount = $(this).data('amount');
                var totalreceivedamount = 0;
                var pendingamount = 0;
                $('#inv_id').val(invoiceid);
                loadershow();
                let pendingPaymentDetailsUrl =
                    "{{ route('paymentdetails.pendingpayment', '__invoiceId__') }}".replace(
                        '__invoiceId__',
                        invoiceid);
                $.ajax({
                    type: 'GET',
                    url: pendingPaymentDetailsUrl,
                    data: {
                        token: API_TOKEN,
                        company_id: " {{ session()->get('company_id') }}",
                        user_id: " {{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        // Handle the response from the server
                        if (response.status == 200) {
                            totalreceivedamount = amount - response.payment[0].pending_amount;
                            $('#paidamount').val(response.payment[0].pending_amount);
                            $('#paidamount').attr('max', response.payment[0].pending_amount);
                            $('#info-total_amount').text(amount);
                            $('#info-total_received_amount').text(totalreceivedamount);
                            $('.info-pending_amount').text(pendingamount);
                        } else {
                            $('#paidamount').val(amount);
                            $('#paidamount').attr('max', amount);
                            $('#info-total_amount').text(amount);
                            $('#info-total_received_amount').text(totalreceivedamount);
                            $('.info-pending_amount').text(pendingamount);
                        }
                        loaderhide();
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr
                            .responseText); // Log the full error response for debugging
                        handleAjaxError(xhr);
                    }
                });

            })

            // payment details 
            $(document).on('click', '.viewpayment', function() {
                loadershow();
                var invoiceId = $(this).data('id');
                viewpayment(invoiceId);
            })

            function viewpayment(invoiceId) {
                $('#details').html('');
                let paymentDetailsSearchUrl = "{{ route('paymentdetails.search', '__invoiceId__') }}"
                    .replace('__invoiceId__', invoiceId);
                $.ajax({
                    type: 'GET',
                    url: paymentDetailsSearchUrl,
                    data: {
                        token: API_TOKEN,
                        company_id: " {{ session()->get('company_id') }}",
                        user_id: " {{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        // Handle the response from the server
                        if (response.status == 200) {
                            $.each(response.paymentdetail, function(key, value) {
                                let generateInvoiceReceiptUrl =
                                    "{{ route('invoice.generatereciept', '__invoiceId__') }}"
                                    .replace('__invoiceId__', value.id);

                                let TDSDetails = '';
                                if (value.tds_amount && value.tds_amount > 0) {
                                    TDSDetails = ` 
                                        <div><b>TDS Amount : </b> ${value.tds_amount}</div>
                                        <div><b>Challan No : </b> ${value.challan_no}</div>
                                        <div><b>TDS Status : </b> ${value.tds_status}</div>
                                    `;
                                }
                                $('#details').append(`
                                    <tr>
                                        <td>
                                            <div class="col-md-10 float-left">
                                                <div><b>Payment date : </b> ${value.datetime}</div>
                                                <div><b>Total Amount : </b> ${value.amount}</div>
                                                <div><b>Paid Amount : </b> ${value.paid_amount}</div>
                                                ${TDSDetails}
                                                <div><b>Pending Amount: </b> ${value.pending_amount}</div>
                                                <div><b>Paid By: </b>  ${value.paid_by != null ? value.paid_by : '-'}</div>
                                                <div><b>Description: </b>  ${value.description || '-'}</div>
                                            </div>    
                                            <div class="col-md-2 float-right p-0">
                                                <a href=${generateInvoiceReceiptUrl} target='_blank'>
                                                    <button data-toggle="tooltip" data-placement="bottom" data-original-title="Download Single Receipt"  class="reciept-btn btn btn-outline-dark btn-rounded btn-sm my-0" >
                                                        <i class='ri-download-cloud-fill'></i>
                                                    </button>
                                                </a>
                                                <button data-id="${value.id}" data-inv-id="${invoiceId}" class="btn btn-sm btn-danger pay-del-btn float-right">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>    
                                            
                                        </td>
                                    </tr>
                                `)

                            });
                        } else {
                            $('#details').html(`
                                <tr>
                                    <td>
                                        No data Found
                                    </td>
                                </tr>
                            `); 
                        }
                        loaderhide();
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr
                            .responseText); // Log the full error response for debugging
                        handleAjaxError(xhr);
                    }
                });
            }

            // show today date as default payment date in modal when modal will open
            $("#paymentmodal").on("shown.bs.modal", function() {
                const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD format
                $('#payment_date').val(today);
            });

            // reset payment details in modal when modal will close
            $("#exampleModalScrollable").on("hidden.bs.modal", function() {
                $('#details').html('');
                $('#addfooterbutton').html('');
            });

            $('#paidamount, #tds_amount').on('change keyup', function() {
                var paidamount = $('#paidamount').val() || 0;
                var tdsamount = $('#tds_amount').val() || 0;
                var totalamount = parseInt($('#info-total_amount').text()) || 0;
                var totalreceived = parseInt($('#info-total_received_amount').text()) || 0;
                var pendingamount = totalamount - totalreceived - paidamount - tdsamount;
                $('.info-pending_amount').text(pendingamount);
            });

            $('#tds_applicable').on('change', function() {
                let val = $(this).is(':checked');
                $('.tds_inputs').hide();
                if (val) {
                    $('.tds_inputs').show();
                }
            });

            // payment form submit 
            $('#paymentform').submit(function(event) {
                $('#modal_error-msg').text('');
                event.preventDefault();
                loadershow();
                const formdata = $(this).serialize();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('paymentdetails.store') }}",
                    data: formdata,
                    success: function(response) {
                        // Handle the response from the server
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });
                            loaddata();
                            $('#paymentform')[0].reset();
                            $('#paymentmodal').modal('hide');
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
                        handleAjaxError(xhr);
                    }
                });
            });

            $(document).on("click", ".update-company-details-btn", function() {
                var invoiceid = $(this).data('id');
                let companyDetailsUrl =
                    "{{ route('invoice.updatecompanydetails', '__invoiceid__') }}".replace(
                        '__invoiceid__', invoiceid);
                var row = this;

                showConfirmationDialog(
                    'Are you sure?', // Title
                    'to update company details?', // Text
                    'Yes, update', // Confirm button text
                    'No, cancel', // Cancel button text
                    'question', // Icon type (question icon)
                    () => {
                        // Success callback
                        loadershow();
                        $.ajax({
                            type: 'PUT',
                            url: companyDetailsUrl,
                            data: {
                                token: API_TOKEN,
                                company_id: COMPANY_ID,
                                user_id: USER_ID,
                                invoiceid: invoiceid
                            },
                            success: function(response) {
                                if (response.status == 200) {
                                    Toast.fire({
                                        icon: "success",
                                        title: "Company details successfully updated"
                                    });
                                    $(row).hide();
                                } else {
                                    Toast.fire({
                                        icon: "error",
                                        title: response.message ||
                                            "Something went wrong!"
                                    });
                                }
                                loaderhide();
                            },
                            error: function(xhr, status,
                                error) { // If calling API request error
                                loaderhide();
                                console.log(xhr
                                    .responseText
                                ); // Log the full error response for debugging
                                handleAjaxError(xhr);
                            }
                        });
                    }
                );
            });

            // delete invoice payment             
            $(document).on("click", ".pay-del-btn", function() {
                var deleteid = $(this).data('id');
                var invId = $(this).data('inv-id');
                let invPaymentDltUrl = "{{ route('paymentdetails.deletepayment', '__deleteId__') }}"
                    .replace(
                        '__deleteId__', deleteid);
                var row = this;
                showConfirmationDialog(
                    'Are you sure?', // Title
                    'to delete this payment record ?', // Text
                    'Yes, delete', // Confirm button text
                    'No, cancel', // Cancel button text
                    'question', // Icon type (question icon)
                    () => {
                        // Success callback
                        loadershow();
                        $.ajax({
                            type: 'PUT',
                            url: invPaymentDltUrl,
                            data: {
                                token: API_TOKEN,
                                company_id: COMPANY_ID,
                                user_id: USER_ID,
                            },
                            success: function(response) {
                                if (response.status == 200) {
                                    Toast.fire({
                                        icon: "success",
                                        title: response.message ||
                                            "succesfully deleted"
                                    });
                                    viewpayment(invId);
                                    table.draw();
                                } else {
                                    Toast.fire({
                                        icon: "error",
                                        title: response.message ||
                                            "something went wrong!"
                                    });
                                }
                                loaderhide();
                            },
                            error: function(xhr, status,
                                error) { // if calling api request error 
                                loaderhide();
                                console.log(xhr
                                    .responseText
                                ); // Log the full error response for debugging
                                handleAjaxError(xhr);
                            }
                        });
                    }
                );
            });

            let invoiceCommissionParty = '';

            // customer data fetch and set customer dropdown
            function commissionParty(callFormFunction = false, partyId = null, commissionId = null, invoiceId =
                null, commission = null) {
                loadershow();

                invoiceCommissionParty = `
                    <option value='' disabled=""> Select Commission Party</option>
                    <option value="add_commission_party" >Add New Commission Party</option>
                `;

                ajaxRequest('GET', "{{ route('invoicecommissionparty.index') }}", {
                    token: API_TOKEN,
                    company_id: COMPANY_ID,
                    user_id: USER_ID,
                }).done(function(response) {
                    if (response.status == 200 && response.invoicecommissionparty != '') {
                        // You can update your HTML with the data here if needed
                        $.each(response.invoicecommissionparty, function(key, value) {
                            const partyDetails = [value.firstname, value.lastname, value
                                .company_name, value.contact_no, value.email
                            ].filter(Boolean).join(' - ');
                            invoiceCommissionParty +=
                                `<option value='${value.id}'>${partyDetails}</option>`;
                        });

                        if (callFormFunction) {
                            if (commissionId && invoiceId) {
                                commissionForm('Edit', invoiceId, partyId, commissionId, commission);
                            } else {
                                commissionForm('Add', commissionInvId, partyId);
                            }  
                        }

                    } else if (response.status == 500) {
                        Toast.fire({
                            icon: "error",
                            title: response.message
                        });
                        invoiceCommissionParty = '';
                    } else {
                        invoiceCommissionParty += `<option disabled '>No Data found </option>`;
                    }
                    loaderhide();
                }).fail(function(xhr) {
                    loaderhide();
                    handleAjaxError(xhr);
                });
            };

            if (showCommissionCol == 1) {
                commissionParty();
            }

            function commissionForm(formtitle, invid = null, partyid = null, commissionid = null, commission = null) {
                $('#commissionmodalTitle').text(`${formtitle} Commission`);
                $('#custom-modal-content').html(`
                    <form id="commissionform">
                        <div class="modal-body">
                            @csrf
                            <div class="payment_details">
                                <input type="hidden" name="user_id" class="form-control" value="${USER_ID}"
                                    required />
                                <input type="hidden" name="company_id" class="form-control" value="${COMPANY_ID}"
                                    required />
                                <input type="hidden" name="token" class="form-control" value="${API_TOKEN}"
                                    placeholder="token" required />
                                <input type="hidden" name="inv_id" id="invoice_id" value="${invid}">
                                <input type="hidden" name="commission_id" id="commission_id" value="${commissionid}">

                                <label for="commission_party">Commission Party</label><span style="color:red;">*</span>
                                <select class="form-control select2" id="commission_party" name="commission_party" required>
                                    ${invoiceCommissionParty};
                                </select>    
                                <p class="modal_error-msg mb-1" id="modal_error-commission_party" style="color: red"></p>

                                <label for="amount">Amount</label><span style="color:red;">*</span>
                                <input type="number" name="amount" class="form-control" id="amount" required step="0.1"/>
                                <p class="modal_error-msg mb-1" id="modal_error-amount" style="color: red"></p>
                                
                                <label for="description">Description</label>
                                <textarea name="description" class="form-control" id="description"
                                    placeholder="Enter Description"/>
                                <p class="modal_error-msg mb-1" id="modal_error-description" style="color: red"></p>
                            </div> 
                        </div>
                        <div class="modal-footer">
                            <button type="submit" id="" class="btn btn-primary m-1">Submit</button>
                            <button type="reset" class="btn iq-bg-danger">Reset</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                `);
                if (commission) {
                    console.log('edit commission', commission);
                    // $('#commission_party').val(commission.commission_party_id);
                    $('#commissionform #amount').val(commission.commission);
                    $('#commissionform #description').val(commission.description);
                }
                $('#commissionform #commission_party').val(partyid);
                $('#commissionform #commission_party').select2();
                loaderhide();
            }

            function newCommissionPartyForm() {
                $('#commissionmodalTitle').text(`Add New Commission Party`);
                $('#custom-modal-content').html(`
                    <form id="commissionpartyform">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-sm-6 mb-2">
                                        <input type="hidden" name="token" class="form-control" value="${API_TOKEN}"
                                            placeholder="token" required />
                                        <input type="hidden" value="${USER_ID}" class="form-control" name="user_id">
                                        <input type="hidden" value="${COMPANY_ID}" class="form-control" name="company_id">
                                        <label for="firstname">FirstName</label><span class="withoutgstspan" style="color:red;">*</span>
                                        <input type="text" id="firstname" class="form-control withoutgstinput" name='firstname'
                                            placeholder="First Name" required>
                                        <span class="modal_error-msg" id="modal_error-firstname" style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="lastname">LastName</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <input type="text" id="lastname" class="form-control requiredinput" name='lastname'
                                            placeholder="Last Name">
                                        <span class="modal_error-msg" id="modal_error-lastname" style="color: red"></span>
                                    </div> 
                                    <div class="col-sm-6 mb-2">
                                        <label for="company_name">Company Name</label>
                                        <span class="withgstspan" style="color:red;">*</span>
                                        <input type="text" id="company_name" class="form-control withgstinput" name='company_name'
                                            id="" placeholder="Company Name">
                                        <span class="modal_error-msg" id="modal_error-company_name" style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="gst_number">GST Number</label>
                                        {{-- <span class="withgstspan" style="color:red;">*</span> --}}
                                        <input type="text" id="gst_number" class="form-control" name='gst_number' id=""
                                            placeholder="GST Number">
                                        <span class="modal_error-msg" id="modal_error-gst_number" style="color: red"></span>
                                    </div> 
                                    <div class="col-sm-6 mb-2">
                                        <label for="email">Email</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <input type="email" class="form-control requiredinput" name="email" id="email"
                                            placeholder="Enter Email">
                                        <span class="modal_error-msg" id="modal_error-email" style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="contact_number">Contact Number</label>
                                        {{-- <span class="requiredinputspan"  style="color:red;">*</span> --}}
                                        <input type="tel" class="form-control requiredinput" name='contact_number' id="contact_number"
                                            placeholder="0123456789">
                                        <span class="modal_error-msg" id="modal_error-contact_number" style="color: red"></span>
                                    </div> 
                                    <div class="col-sm-6 mb-2">
                                        <label for="country">Select Country</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <select class="form-control requiredinput" name='country' id="country">
                                            <option selected="" disabled="">Select your Country</option>
                                        </select>
                                        <span class="modal_error-msg" id="modal_error-country" style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="state">Select State</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <select class="form-control requiredinput" name='state' id="state">
                                            <option selected="" disabled="">Select your State</option>
                                        </select>
                                        <span class="modal_error-msg" id="modal_error-state" style="color: red"></span>
                                    </div> 
                                    <div class="col-sm-6 mb-2">
                                        <label for="city">Select City</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <select class="form-control requiredinput" name='city' id="city">
                                            <option selected="" disabled="">Select your City</option>
                                        </select>
                                        <span class="modal_error-msg" id="modal_error-city" style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="pincode">Pincode</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <input type="text" id="pincode" name='pincode' class="form-control requiredinput"
                                            placeholder="Pin Code">
                                        <span class="modal_error-msg" id="modal_error-pincode" style="color: red"></span>
                                    </div> 
                                    <div class="col-sm-6 mb-2">
                                        <label for="house_no_building_name">House no./ Building Name</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <textarea class="form-control requiredinput" name='house_no_building_name' id="house_no_building_name"
                                            rows="2" placeholder="e.g. 2nd floor/ 04 ABC Appartment"></textarea>
                                        <span class="modal_error-msg" id="modal_error-house_no_building_name" style="color: red"></span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label for="road_name_area_colony">Road Name/Area/Colony</label>
                                        {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                        <textarea class="form-control requiredinput" name='road_name_area_colony' id="road_name_area_colony" rows="2"
                                            placeholder="e.g. sardar patel road, jagatpur"></textarea>
                                        <span class="modal_error-msg" id="modal_error-road_name_area_colony" style="color: red"></span>
                                    </div>  
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-sm-12">
                                        <button type="submit" id="" class="btn btn-primary m-1">Submit</button>
                                        <button type="reset" class="btn iq-bg-danger">Reset</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>    
                    </form>
                `);
                loadcountry();
                loaderhide();
            }

            function showCommission(invId) {
                let invCommissionSerchUrl = "{{ route('invoice.searchcommission', '__invId__') }}".replace(
                    '__invId__', invId);
                $.ajax({
                    type: 'GET',
                    url: invCommissionSerchUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 & response.invoicecommission != '') {
                            commission = response.invoicecommission;
                            let totalCommision = 0;

                            let commissionHtml = '<div class="modal-body">';
                            let invoiceNo = '';
                            $.each(commission, function(key, value) {
                                totalCommision = totalCommision + value.commission;
                                const recordId = `record-${key}`;
                                invoiceNo = value.inv_no;
                                let partyDetails = [value.firstname, value.lastname, value
                                    .company_name
                                ].filter(Boolean).join(' - ');

                                let editCommissionBtn = '';
                                if (editCommission) {
                                    editCommissionBtn = `
                                        <button type="button" class="btn btn-rouned btn-sm btn-success edit-commission-btn" data-commissionid='${value.id}' data-invid='${value.invoice_id}'>
                                            <i class="m-0 ri-pencil-line" style="cursor:pointer; margin-right: 10px;" title="Edit"></i>
                                        </button>
                                    `;
                                }
                                let deleteCommissionBtn = '';
                                if (deleteCommission) {
                                    deleteCommissionBtn = `
                                        <button type="button" class="btn btn-danger btn-sm delete-commission-btn" data-commissionid='${value.id}' data-invid='${value.invoice_id}'>
                                            <i class="ri-delete-bin-line" style="cursor:pointer;" title="Delete"></i>
                                        </button>
                                    `;
                                }

                                commissionHtml += `
                                    <div class="col-12 commission-item" id="${recordId}" data-key="${key}">
                                        <div class="display-mode">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div> 
                                                    <b>Commission Party:</b> <span class="status-text">${partyDetails}</span>
                                                </div>
                                                <div>
                                                    ${editCommissionBtn}
                                                    ${deleteCommissionBtn}
                                                </div>
                                            </div>
                                            <b>Amount:</b> <span class="status-text">${value.commission}</span><br>
                                            <b>Description:</b> <div class="notes-text">${value.description || ''}</div>
 
                                            <small>${value.created_at_formatted}</small><br>
                                        </div>  
                                    </div>
                                    <hr>
                                `;
                            });

                            commissionHtml += `
                                </div>
                                <div class="modal-footer">
                                    <div class="col-sm-12">
                                        <b>Total Commission:</b> <span class="status-text">${totalCommision}</span>
                                        <button type="button" class="btn btn-secondary float-right" data-dismiss="modal">Close</button>
                                    </div> 
                                </div>
                            `;
                            $('#commissionmodalTitle').text(`${invoiceNo} - View Commissions`);
                            $('#custom-modal-content').html(commissionHtml);

                        } else if (response.status == 500) {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        } else {
                            $('#custom-modal-content').html(`
                                <div class="col-12">
                                   No commission Found 
                                </div>
                            `);
                        }
                        loaderhide();
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr.responseText); // Log the full error response for debugging
                        handleAjaxError(xhr);
                    }
                });
            }

            $(document).on('click', '.commissionmodal', function() {
                loadershow();
                $('#custom-modal-content').html('');
                $('#commissionmodal').modal('show');
                let invId = $(this).data('id'); 
                commissionForm('Add', invId);
            });

            $(document).on('click', '.viewcommissionmodal', function() {
                loadershow();
                $('#commissionmodal').modal('show');
                $('#commissionmodalTitle').text(`View Commissions`);
                $('#custom-modal-content').html('');
                let invId = $(this).data('id');
                showCommission(invId);
            });

            $(document).on('change', '#commission_party', function() {
                loadershow();
                var party = $(this).val();
                let commissionId = $('#commission_id').val();
                let invId = $('#invoice_id').val();
                if (party == 'add_commission_party') {
                    editCommissionId = commissionId;
                    commissionInvId = invId; 
                    $('#custom-modal-content').html('');
                    $('#commissionmodal').modal('show');
                    newCommissionPartyForm();
                }
                $('.withgstspan').hide();
                loaderhide();
            });

            $(document).on('change keyup', '#company_name', function() {
                var val = $(this).val();
                if (val != '') {
                    $('.withgstspan').show();
                    $('.withoutgstspan').hide();
                    $('.withgstinput').attr('required', true);
                    $('.withoutgstinput').removeAttr('required');
                } else {
                    $('.withgstspan').hide();
                    $('.withoutgstspan').show();
                    $('.withoutgstinput').attr('required', true);
                    $('.withgstinput').removeAttr('required');
                }
            });

            // show country data and set default value according logged in user
            function loadcountry() {
                ajaxRequest('GET', "{{ route('country.index') }}", {
                    token: API_TOKEN,
                }).done(function(response) {
                    if (response.status == 200 && response.country != '') {
                        // You can update your HTML with the data here if needed
                        $.each(response.country, function(key, value) {
                            $('#country').append(
                                `<option value='${value.id}'> ${value.country_name}</option>`
                            )
                        });
                        country_id = "{{ session('user')['country_id'] }}";
                        $('#country').val(country_id);
                        loadstate();
                    } else {
                        $('#country').append(`<option> No Data Found</option>`);
                    }
                    loaderhide();
                }).fail(function(xhr) {
                    loaderhide();
                    handleAjaxError(xhr);
                });
            }

            // load state in dropdown when country change
            $(document).on('change', '#country', function() {
                loadershow();
                $('#city').html(`<option selected="" disabled="">Select your city</option>`);
                var country_id = $(this).val();
                loadstate(country_id);
            });

            // load state in dropdown and select state according to user
            function loadstate(id = 0) {
                $('#state').html(`<option selected="" disabled="">Select your State</option>`);
                let stateSearchUrl = "{{ route('state.search', 'id') }}".replace('id', id);
                var url = stateSearchUrl;
                if (id == 0) {
                    url = "{{ route('state.search', session('user')['country_id']) }}";
                }
                ajaxRequest('GET', url, {
                    token: API_TOKEN,
                }).done(function(response) {
                    if (response.status == 200 && response.state != '') {
                        // You can update your HTML with the data here if needed
                        $.each(response.state, function(key, value) {
                            $('#state').append(
                                `<option value='${value.id}'> ${value.state_name}</option>`
                            )
                        });
                        if (id == 0) {
                            state_id = "{{ session('user')['state_id'] }}";
                            $('#state').val(state_id);
                            loadcity();
                        }
                    } else {
                        $('#state').append(`<option> No Data Found</option>`);
                    }
                    loaderhide();
                }).fail(function(xhr) {
                    loaderhide();
                    handleAjaxError(xhr);
                });
            }

            // load city in dropdown when state select/change
            $(document).on('change', '#state', function() {
                loadershow();
                var state_id = $(this).val();
                loadcity(state_id);
            });

            function loadcity(id = 0) {
                $('#city').html(`<option selected="" disabled="">Select your City</option>`);
                citySearchUrl = "{{ route('city.search', 'id') }}".replace('id', id);
                url = citySearchUrl;
                if (id == 0) {
                    url = "{{ route('city.search', session('user')['state_id']) }}";
                }

                ajaxRequest('GET', url, {
                    token: API_TOKEN,
                }).done(function(response) {
                    if (response.status == 200 && response.city != '') {
                        // You can update your HTML with the data here if needed
                        $.each(response.city, function(key, value) {
                            $('#city').append(
                                `<option value='${value.id}'> ${value.city_name}</option>`
                            )
                        });
                        if (id == 0) {
                            $('#city').val("{{ session('user')['city_id'] }}");
                        }
                    } else {
                        $('#city').append(`<option> No Data Found</option>`);
                    }
                    loaderhide();
                }).fail(function(xhr) {
                    loaderhide();
                    handleAjaxError(xhr);
                });
            }

            function getCommissionData(commissionId, invId, partyId = null) {
                loadershow();
                let invCommissionEditUrl = "{{ route('invoice.editcommission', '__commissionId__') }}".replace(
                    '__commissionId__', commissionId);
                $.ajax({
                    type: 'GET',
                    url: invCommissionEditUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 & response.invoicecommission != '') {
                            commission = response.invoicecommission; 
                            commissionParty(true, partyId || commission.commission_party_id, commissionId, invId, commission);
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message || 'Something went wrong!'
                            });
                        }
                        loaderhide();
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr.responseText); // Log the full error response for debugging
                        handleAjaxError(xhr);
                    }
                });
            }

            // Switch to edit mode
            $(document).on('click', '.edit-commission-btn', function() {
                const commissionId = $(this).data('commissionid');
                const invId = $(this).data('invid');
                showConfirmationDialog(
                    'Are you want?',
                    "to edit this record",
                    'Yes, Edit!',
                    'No, cancle it!',
                    'warning',
                    () => { 
                        getCommissionData(commissionId, invId);
                    }
                );

            });

            $(document).on('click', '.delete-commission-btn', function() {
                const commissionId = $(this).data('commissionid');
                const invId = $(this).data('invid');
                showConfirmationDialog(
                    'Are you sure?',
                    "This will permanently delete the record.",
                    'Yes, delete it!',
                    'No, cancle it!',
                    'warning',
                    () => {
                        loadershow();
                        let deleteCommissionUrl =
                            "{{ route('invoice.destroycommission', '__deleteId__') }}".replace(
                                '__deleteId__', commissionId);
                        $.ajax({
                            url: deleteCommissionUrl,
                            method: 'PUT',
                            data: {
                                token: "{{ session()->get('api_token') }}",
                                company_id: "{{ session()->get('company_id') }}",
                                user_id: "{{ session()->get('user_id') }}"
                            },
                            success: function(response) {
                                loadershow();
                                if (response.status === 200) {
                                    Toast.fire({
                                        icon: 'success',
                                        title: response.message
                                    });

                                    showCommission(invId); // Refresh commission list
                                } else {
                                    Toast.fire({
                                        icon: 'error',
                                        title: response.message ||
                                            'Failed to delete'
                                    });
                                }
                                loaderhide();
                            },
                            error: function(xhr) {
                                loaderhide();
                                Toast.fire({
                                    icon: 'error',
                                    title: 'Something went wrong while deleting.'
                                });
                            }
                        });
                    }
                );
            });

            // submit new commission party form
            $(document).on('submit', '#commissionpartyform', function(event) {
                event.preventDefault();
                loadershow();
                $('.modal_error-msg').text('');
                const formdata = $(this).serialize();
                
                $.ajax({
                    type: 'POST',
                    url: "{{ route('invoicecommissionparty.store') }}",
                    data: formdata,
                    success: function(response) {
                        // Handle the response from the server
                        if (response.status == 200) {  
                            if(editCommissionId && editCommissionId != 'null'){ 
                                getCommissionData(editCommissionId, commissionInvId, response.commission_party_id);
                                editCommissionId = null; 
                            } else {
                                commissionParty(true, response.commission_party_id);
                            }
                            
                            $('#commissionmodal').modal('show');
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
                        handleModalAjaxError(xhr);
                    }
                });
            });

            // submit new commission party form
            $(document).on('submit', '#commissionform', function(event) {
                event.preventDefault();
                loadershow();
                $('.modal_error-msg').text('');
                let url = "{{ route('invoice.storecommission') }}";
                let type = 'POST';
                let commissionId = $('#commission_id').val();
                if(commissionId && commissionId !== 'null'){
                    url = "{{ route('invoice.updatecommission', '__editId__') }}".replace('__editId__', commissionId);
                    type = 'PUT';
                }
                const formdata = $(this).serialize();
                $.ajax({
                    type: type,
                    url: url,
                    data: formdata,
                    success: function(response) {
                        // Handle the response from the server
                        if (response.status == 200) {
                            data = response.data;
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });
                            $('#commissionmodal').modal('show');
                            $('#commissionmodalTitle').text(`View Commissions`);
                            $('#custom-modal-content').html(''); 
                            showCommission(data.invoice_id);
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
                        handleModalAjaxError(xhr);
                    }
                });
            });

        });
    </script>
@endpush
