@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')
@section('page_title')
    {{ config('app.name') }} - Ledger
@endsection
@section('table_title')
    Ledger
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

@section('pdfbtns')
    <div class="col-sm-12 text-right px-4">
        <button id="download-ledger" class="btn btn-sm btn-primary float-left" data-toggle="tooltip"
            data-placement="bottom" data-original-title="Download Ledger">
            <i class="ri-download-2-line"></i>
        </button>
    </div>
@endsection

@section('table-content')
    <table id="data" class="dataTable display table table-bordered table-striped w-100">
        <thead>
            <tr>
                <th>Id</th>
                <th>Date</th>
                <th>Description</th>
                <th>Credited</th>
                <th>Debited</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody id="tabledata">

        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total(All Record)</th>
                <th id="totalCredit" class="text-success fw-bold"></th>
                <th id="totalDebit" class="text-danger fw-bold"></th>
                <th id="totalBalance" class="fw-bold"></th>
            </tr>
        </tfoot>
    </table>

    {{-- modal for Receipts Zip  --}}
    <div class="modal fade" id="ledgermodal" tabindex="-1" role="dialog" aria-labelledby="ledgermodalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ledgermodalTitle"><b>Payment Receipts Zip</b></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="ledgermodalform">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <input type="hidden" name="company_id" id="company_id" value="{{ session('company_id') }}">
                                <input type="hidden" name="user_id" id="user_id" value="{{ session('user_id') }}">
                                <input type="hidden" name="token" id="token" value="{{ session('api_token') }}">
                                <div class="col-12 mb-2">
                                    <label for="from_date">From Date</label><span style="color:red;">*</span>
                                    <input type="date" name="from_date" class="form-control" id="from_date"
                                        value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" autocomplete="off" required />
                                    <span class="error-msg" id="error-from_date" style="color: red"></span>
                                </div>
                                <div class="col-12 mb-2">
                                    <label for="to_date">To Date</label><span style="color:red;">*</span>
                                    <input type="date" name="to_date" class="form-control" id="to_date"
                                        value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" autocomplete="off" required />
                                    <span class="error-msg" id="error-to_date" style="color: red"></span>
                                </div>
                                <div class="col-12 mb-3 text-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm quick-date" data-month="1">
                                        Last Month
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm quick-date" data-month="3">
                                        Last 3 Months
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm quick-date" data-month="6">
                                        Last 6 Months
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" value="Downalod" class="btn btn-sm btn-primary">
                            <button type="button" class="btn btn-danger resetledgermodalform"
                                data-dismiss="modal">Close</button>
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
                        url: "{{ route('ledger.datatable') }}",
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
                                $('#download-bulk-voucher').hide();
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
                    order: [],
                    columns: [{
                            data: 'id',
                            name: 'id',
                            orderable: true,
                            searchable: false,
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
                            data: 'description',
                            name: 'description',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            render: function(data, type, row) {
                                return `<div style="white-space: pre-line;">${(data || '-').replace(/(&lt;br\s*\/?&gt;|<br\s*\/?>)/gi, '\n')}</div>`;
                            }
                        },
                        {
                            data: 'credited',
                            name: 'credited',
                            className: 'text-success fw-bold',
                            orderable: false,
                            searchable: false,
                            defaultContent: '-',
                            render: function (data) {
                                return data > 0 ? parseFloat(data).toFixed(2) : '0';
                            }
                        },
                        {
                            data: 'debited',
                            name: 'debited',
                            className: 'text-danger fw-bold',
                            orderable: false,
                            searchable: false,
                            defaultContent: 0,
                            render: function (data) {
                                return data > 0 ? parseFloat(data).toFixed(2) : '0';
                            }
                        },
                        {
                            data: 'balance',
                            name: 'balance',
                            orderable: false,
                            searchable: true,
                            defaultContent: 0,
                            render: function (data) {
                                let color = data >= 0 ? 'text-success' : 'text-danger';
                                return `<span class="${color} fw-bold">${parseFloat(data).toFixed(2)}</span>`;
                            }
                        }
                    ],

                    pagingType: "full_numbers",
                    drawCallback: function(settings) {
                        let json = settings.json;

                        $('#totalCredit').html(json.totalCredited ?? '0');
                        $('#totalDebit').html(json.totalDebited ?? '0')
                        let rawBalance = json.totalBalance ?? '0';
                        let totalBalance = parseFloat(rawBalance.replace(/,/g, ''));
                        let balColor = totalBalance >= 0 ? 'text-success' : 'text-danger';
                       
                        $('#totalBalance').html(
                            `<span class="${balColor}">${rawBalance}</span>`
                        );

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

            $(document).on('click', '.quick-date', function () {
                let months = $(this).data('month');

                let today = new Date();
                let toDate = today.toISOString().split('T')[0];

                let fromDate = new Date();
                fromDate.setMonth(fromDate.getMonth() - months);
                fromDate = fromDate.toISOString().split('T')[0];

                $('#from_date').val(fromDate);
                $('#to_date').val(toDate);
            });

            $('#ledgermodal').on('hidden.bs.modal', function (e) {
                 // reset form
                $('#ledgermodalform')[0].reset();

                // set today date
                let today = new Date().toISOString().split('T')[0];
                $('#from_date').val(today);
                $('#to_date').val(today);
            })
 
            //  download ledger form modal
            $(document).on('click', '#download-ledger', function() {
                $('#ledgermodal').modal('show');
            });

            $('#ledgermodalform').on('submit', function(e) {
                e.preventDefault(); // Prevent the default form submission

                loadershow(); // Show the loader

                // Serialize form data
                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('ledger.generatepdf') }}", // Your API endpoint
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.status === 'success') {

                            $('#ledgermodal').modal('hide');
                            // Redirect to the URL to start the file download
                            setTimeout(() => {
                                window.location.href = response.pdfFileUrl;
                            }, 100);

                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: "error",
                            title: 'An error occurred while generating the ZIP file.'
                        });
                    },
                    complete: function() {
                        loaderhide(); // Hide the loader
                    }
                });
            });

        });
    </script>
@endpush
