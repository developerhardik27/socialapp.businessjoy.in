@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')
@section('page_title')
    {{ config('app.name') }} - Subscriptions
@endsection
@section('table_title')
    Subscriptions
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

         .vertical-timeline {
            width: 100%;
            position: relative;
            padding: 1.5rem 0 1rem;
        }

        .vertical-timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 4px;
            background: #e9ecef;
            border-radius: .25rem;
        }

        .vertical-timeline-element {
            position: relative;
            margin: 0 0 1rem;
        }

        .vertical-timeline--animate .vertical-timeline-element-icon.bounce-in {
            visibility: visible;
            animation: cd-bounce-1 .8s;
        }

        .vertical-timeline-element-icon {
            position: absolute;
            top: 0;
            left: -7px;
        }

        .vertical-timeline-element-icon .badge-dot-xl {
            box-shadow: 0 0 0 5px #fff;
        }

        .badge-dot-xl {
            width: 18px;
            height: 18px;
            position: relative;
        }

        .badge:empty {
            display: block !important;
        }

        .badge-dot-xl::before {
            content: '';
            width: 10px;
            height: 10px;
            border-radius: .25rem;
            position: absolute;
            left: 50%;
            top: 50%;
            margin: -5px 0 0 -5px;
            background: #fff;
        }

        .vertical-timeline-element-content {
            position: relative;
            margin-left: 25px;
            font-size: .8rem; 
        }

        .vertical-timeline-element-content .btn {
            text-align: left !important;
        }

        .vertical-timeline-element-content .timeline-title {
            font-size: .8rem;
            margin: 0 0 .5rem;
            padding: 2px 0 0;
        }

        .vertical-timeline-element-content .vertical-timeline-element-date {
            display: block;
            position: absolute; 
            top: 0;
            padding-right: 10px;
            text-align: right;
            color: #adb5bd;
            font-size: .7619rem;
            white-space: nowrap;
        }

        .vertical-timeline-element-content:after {
            content: "";
            display: table;
            clear: both;
        }

        /* Media query for screens with max width of 768px */
        @media (min-width: 769px) {
            .vertical-timeline-element-content .vertical-timeline-element-date {
                right: 0px !important; /* Remove absolute positioning */ 
            }
        }
        /* Media query for screens with max width of 768px */
        @media (max-width: 768px) {
            .vertical-timeline-element-content .vertical-timeline-element-date {
                left: 0px !important; /* Remove absolute positioning */
                top: -20px !important; /* Adjust vertical positioning */
            }
        }
    </style>
@endsection
@if (session('user_permissions.adminmodule.subscription.add') == '1')
    @section('addnew')
        {{ route('admin.addsubscription') }}
    @endsection
    @section('addnewbutton')
        <button class="btn btn-sm btn-primary">
            <span data-toggle="tooltip" data-placement="bottom" data-original-title="Add New Subscription" class="">+ Add
                New</span>
        </button>
    @endsection
@endif

@section('advancefilter')
    <div class="col-sm-12 text-right px-4">
        <button class="btn btn-sm btn-primary m-0" data-toggle="tooltip" data-placement="bottom" data-original-title="Filters"
            onclick="showOffCannvas()">
            <i class="ri-filter-line"></i>
        </button>
    </div>
@endsection

@section('sidebar-filters')
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6>Subscription Status</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-1">
                        <select class="filter select2 form-control w-100" id="filter_status" multiple>
                            <option value="active">Active</option>
                            <option value="trial">Trial</option>
                            <option value="expired">Expired</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6>Company</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-1">
                        <select class="filter select2 form-control w-100" id="filter_company" multiple>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6>Package</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-1">
                        <select class="filter select2 form-control w-100" id="filter_package" multiple>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6>Billing Cycle</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-1">
                        <select class="filter select2 form-control w-100" id="filter_billing_cycle" multiple>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6>Payment Period</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-1">
                        <label for="filter_payment_start_from_date" class="form-label">Start From:</label>
                        <input type="date" id="filter_payment_start_from_date" class="filter form-input form-control">
                    </div>
                    <div class="col-md-6 mb-1">
                        <label for="filter_payment_start_to_date" class="form-label">Start To:</label>
                        <input type="date" id="filter_payment_start_to_date" class="filter form-input form-control">
                    </div>
                    <div class="col-md-6 mb-1">
                        <label for="filter_payment_end_from_date" class="form-label">End From:</label>
                        <input type="date" id="filter_payment_end_from_date" class="filter form-input form-control">
                    </div>
                    <div class="col-md-6 mb-1">
                        <label for="filter_payment_end_to_date" class="form-label">End To:</label>
                        <input type="date" id="filter_payment_end_to_date" class="filter form-input form-control">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6>Next Billing</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-1">
                        <label for="filter_next_billing_from_date" class="form-label">From:</label>
                        <input type="date" id="filter_next_billing_from_date" class="filter form-input form-control">
                    </div>
                    <div class="col-md-6 mb-1">
                        <label for="filter_next_billing_to_date" class="form-label">To:</label>
                        <input type="date" id="filter_next_billing_to_date" class="filter form-input form-control">
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
                <th>Id</th>
                <th>Company</th>
                <th>Package</th>
                <th>Status</th>
                <th>Billing Cycle</th>
                <th>Payment Start</th>
                <th>Payment End</th>
                <th>Next Billing</th>
                <th>EMI Cost</th>
                <th>History</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="tabledata">

        </tbody>
    </table>

    <!-- Subscription Details Modal -->
    <div class="modal fade" id="subscriptionDetailsModal" tabindex="-1" role="dialog"
        aria-labelledby="subscriptionDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subscriptionDetailsModalLabel">
                        <i class="ri-file-list-3-line mr-2"></i>Subscription Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="subscriptionDetailsContent">
                    <!-- Content will be loaded via AJAX -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('ajax')
    <script>
        $('document').ready(function() {
            var global_response = '';
            let table = '';

            // fetch & show subscription data in table
            function loaddata() {
                loadershow();

                table = $('#data').DataTable({
                    language: {
                        lengthMenu: '_MENU_ &nbsp;Entries per page'
                    },
                    destroy: true,
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        type: "GET",
                        url: "{{ route('subscription.index') }}",
                        data: function(d) {
                            d.user_id = "{{ session()->get('user_id') }}";
                            d.company_id = "{{ session()->get('company_id') }}";
                            d.token = "{{ session()->get('api_token') }}";
                            // Subscription Status Filter
                            d.status = $('#filter_status').val();
                            // Company Filter
                            d.company = $('#filter_company').val();
                            // Package Filter
                            d.package = $('#filter_package').val();
                            // Billing Cycle Filter
                            d.billing_cycle = $('#filter_billing_cycle').val();
                            // Start Date Range Filter
                            d.payment_start_from_date = $('#filter_payment_start_from_date').val();
                            d.payment_start_to_date = $('#filter_payment_start_to_date').val();
                            // End Date Range Filter
                            d.payment_end_from_date = $('#filter_payment_end_from_date').val();
                            d.payment_end_to_date = $('#filter_payment_end_to_date').val();
                            // Next Billing Date Range Filter
                            d.next_billing_from_date = $('#filter_next_billing_from_date').val();
                            d.next_billing_to_date = $('#filter_next_billing_to_date').val();
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
                    columns: [{
                            data: 'id',
                            name: 'id',
                            orderable: true,
                            searchable: false,
                            defaultContent: '-'
                        },
                        {
                            data: 'company_name',
                            name: 'company_name',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'package_name',
                            name: 'package_name',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'status',
                            orderable: true,
                            searchable: false,
                            render: function(data, type, row) {
                                @if (session('user_permissions.adminmodule.subscription.edit') == '1')
                                    return `
                                        <select class="status-dropdown form-control form-control-sm" data-id="${row.id}" data-original="${data}">
                                            <option value="active" ${data == 'active' ? 'selected' : ''}>Active</option>
                                            <option value="trial" ${data == 'trial' ? 'selected' : ''}>Trial</option>
                                            <option value="expired" ${data == 'expired' ? 'selected' : ''}>Expired</option>
                                            <option value="suspended" ${data == 'suspended' ? 'selected' : ''}>Suspended</option>
                                        </select>
                                    `;
                                @else
                                    let statusClass = '';
                                    switch (data) {
                                        case 'active':
                                            statusClass = 'badge-success';
                                            break;
                                        case 'trial':
                                            statusClass = 'badge-warning';
                                            break;
                                        case 'expired':
                                            statusClass = 'badge-danger';
                                            break;
                                        case 'suspended':
                                            statusClass = 'badge-secondary';
                                            break;
                                        default:
                                            statusClass = 'badge-secondary';
                                    }
                                    return data ?
                                        `<span class="badge ${statusClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>` :
                                        '-';
                                @endif
                            }
                        },
                        {
                            data: 'billing_cycle',
                            name: 'billing_cycle',
                            orderable: true,
                            searchable: true,
                            render: function(data, type, row) {
                                return data ? data.charAt(0).toUpperCase() + data.slice(1) : '-';
                            }
                        },
                        {
                            data: 'payment_cycle_start_date',
                            name: 'payment_cycle_start_date',
                            orderable: true,
                            searchable: false,
                            render: function(data, type, row) {
                                return data ? new Date(data).toLocaleDateString() : '-';
                            }
                        },
                        {
                            data: 'payment_cycle_end_date',
                            name: 'payment_cycle_end_date',
                            orderable: true,
                            searchable: false,
                            render: function(data, type, row) {
                                return data ? new Date(data).toLocaleDateString() : '-';
                            }
                        },
                        {
                            data: 'next_billing_date',
                            name: 'next_billing_date',
                            orderable: true,
                            searchable: false,
                            render: function(data, type, row) {
                                return data ? new Date(data).toLocaleDateString() : '-';
                            }
                        },
                        {
                            data: 'emi_cost',
                            name: 'emi_cost',
                            orderable: true,
                            searchable: false,
                            render: function(data, type, row) {
                                return data != null ? parseFloat(data).toFixed(2) : '-';
                            }
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                let actionBtns = '';
                                actionBtns += `
                                    <button type="button" data-id="${data}" class="view-btn btn btn-primary btn-sm mr-1" data-toggle="tooltip" data-placement="bottom" data-original-title="View Details">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    <button type="button" data-id="${data}" class="history-btn btn btn-info btn-sm mr-1" data-toggle="tooltip" data-placement="bottom" data-original-title="View History">
                                        <i class="ri-time-line"></i>
                                    </button>
                                `;
                                return actionBtns;
                            }
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                actionBtns = '';
                                @if (session('user_permissions.adminmodule.subscription.edit') == '1')
                                    var editUrl = "{{ route('admin.editsubscription', '__id__') }}"
                                        .replace('__id__', data);
                                    actionBtns +=
                                        `<a href="${editUrl}" class="btn btn-success btn-sm"><i class="ri-edit-fill"></i></a>`;
                                @endif
                                @if (session('user_permissions.adminmodule.subscription.delete') == '1')
                                    actionBtns += ` 
                                        <button type="button" data-id='${data}' class="del-btn btn btn-danger btn-sm ml-1" data-toggle="tooltip"
                                            data-placement="bottom" data-original-title="delete">
                                            <i class="ri-delete-bin-fill"></i>
                                        </button>
                                    `;
                                @endif
                                return actionBtns;
                            }
                        },

                    ], 
                    pagingType: "full_numbers",
                    drawCallback: function(settings) {
                        $('[data-toggle="tooltip"]').tooltip({
                            boundary: 'window',
                            offset: '0, 10'
                        });

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

            // Load companies and packages for filters
            loadCompanies();
            loadPackages();

            // Initialize Select2 for all filter dropdowns after data is loaded
            setTimeout(function() {
                $('#filter_billing_cycle, #filter_status').select2({
                    searche: true,
                    width: '100%',
                    placeholder: 'Select options...',
                    allowClear: true
                });
            }, 500);

            function loadCompanies() {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('company.companylist') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200) {
                            let companySelect = $('#filter_company');
                            companySelect.html('');
                            $.each(response.company, function(key, company) {
                                var companydetails = [
                                    company.name,
                                    company.contact_no,
                                    company.email,
                                ].filter(Boolean).join(' - ');
                                companySelect.append(
                                    `<option value='${company.id}'>${companydetails}</option>`
                                );
                            });
                            // Reinitialize Select2 for this element
                            companySelect.select2({
                                search: true,
                                width: '100%',
                                placeholder: 'Select companies...',
                                allowClear: true
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            }

            function loadPackages() {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('package.index') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200) {
                            let packageSelect = $('#filter_package');
                            packageSelect.html('');
                            $.each(response.data, function(key, package) {
                                packageSelect.append(
                                    `<option value='${package.id}'>${package.name}</option>`
                                );
                            });
                            // Reinitialize Select2 for this element
                            packageSelect.select2({
                                search: true,
                                width: '100%',
                                placeholder: 'Select packages...',
                                allowClear: true
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            }

            // Apply filters on change
            $('.applyfilters').on('click', function() {
                table.draw();
                hideOffCanvass();
            });

            //remove filtres
            $('.removefilters').on('click', function() {
                $('.filter').val('');

                $('#filter_status').val(null).trigger('change');
                $('#filter_company').val(null).trigger('change');
                $('#filter_package').val(null).trigger('change');
                $('#filter_billing_cycle').val(null).trigger('change');

                table.draw();
                hideOffCanvass(); // close OffCanvass
            });

            // history button
            $(document).on('click', '.history-btn', function() {
                var subId = $(this).data('id');
                loadershow();
                let url = "{{ route('subscription.history', '__id__') }}".replace('__id__', subId);
                $.ajax({
                    type: 'GET',
                    url: url,
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(resp) {
                        loaderhide();
                        if (resp.status == 200) {
                            let rows = resp.history || resp.data || [];
                            let html = '<div class="vertical-timeline vertical-timeline--animate vertical-timeline--one-column" id="timelinerow">';
                            $.each(rows, function(i, h) {
                                let actionDate = h.action_date || h.created_at;
                                let formattedDate = new Date(actionDate);
                                let dateClass = formattedDate.toLocaleDateString().replace(/\//g, '-');
                                
                                html += `
                                    <div class="vertical-timeline-item vertical-timeline-element ${dateClass}">
                                        <div>
                                            <span class="vertical-timeline-element-icon bounce-in">
                                                <i class="badge badge-dot-xl badge-cobalt-blue"></i>
                                            </span>
                                            <div class="vertical-timeline-element-content bounce-in">
                                                <div class="timeline-body">
                                                    <p>${h.notes}</p>
                                                    <p><small class="text-muted">${actionDate}</small></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            html += '</div>';
                            $('#details').html(html);
                            $('#exampleModalScrollable').modal('show');
                        } else {
                            Toast.fire({ icon: 'error', title: resp.message || 'No history' });
                        }
                    },
                    error: function(xhr) {
                        loaderhide();
                        Toast.fire({
                            icon: 'error',
                            title: 'Error fetching history'
                        });
                    }
                });
            });
  
            // record delete 
            $(document).on("click", ".del-btn", function() {
                var deleteid = $(this).data('id');
                var row = this;
                showConfirmationDialog(
                    'Are you sure?',
                    'to delete this record?',
                    'Yes, delete',
                    'No, cancel',
                    'question',
                    () => {
                        loadershow();
                        let subscriptionDeleteUrl =
                            "{{ route('subscription.delete', '__deleteId__') }}".replace(
                                '__deleteId__',
                                deleteid);
                        $.ajax({
                            type: 'PUT',
                            url: subscriptionDeleteUrl,
                            data: {
                                token: "{{ session()->get('api_token') }}",
                                company_id: "{{ session()->get('company_id') }}",
                                user_id: "{{ session()->get('user_id') }}"
                            },
                            success: function(response) {
                                if (response.status == 200) {
                                    table.draw();
                                } else if (response.status == 500) {
                                    Toast.fire({
                                        icon: "error",
                                        title: response.message
                                    });
                                } else {
                                    Toast.fire({
                                        icon: "error",
                                        title: "something went wrong!"
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
                );
            });
 

            // Handle view button click
            $(document).on('click', '.view-btn', function() {
                var subscriptionId = $(this).data('id');
                loadershow();

                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscription.search', '__id__') }}".replace('__id__',
                        subscriptionId),
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        loaderhide();
                        if (response.status == 200 && response.subscription) {
                            const sub = response.subscription;
                            let detailsHtml = `
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr><td><strong>Status:</strong></td><td><span class="badge ${getStatusClass(sub.status)}">${sub.status ? sub.status.charAt(0).toUpperCase() + sub.status.slice(1) : '-'}</span></td></tr>
                                            <tr><td><strong>Trial Days:</strong></td><td>${sub.trial_days || '-'}</td></tr>
                                            <tr><td><strong>Billing Cycle:</strong></td><td>${sub.billing_cycle ? sub.billing_cycle.charAt(0).toUpperCase() + sub.billing_cycle.slice(1) : '-'}</td></tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr><td><strong>Start Date:</strong></td><td>${sub.subscription_start_date ? new Date(sub.subscription_start_date).toLocaleDateString() : '-'}</td></tr>
                                            <tr><td><strong>End Date:</strong></td><td>${sub.subscription_end_date ? new Date(sub.subscription_end_date).toLocaleDateString() : '-'}</td></tr>
                                            <tr><td><strong>Next Billing:</strong></td><td>${sub.next_billing_date ? new Date(sub.next_billing_date).toLocaleDateString() : '-'}</td></tr>
                                            <tr><td><strong>EMI Cost:</strong></td><td>${sub.emi_cost != null ? parseFloat(sub.emi_cost).toFixed(2) : '-'}</td></tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <table class="table table-sm">
                                            <tr><td><strong>Trial Start Date:</strong></td><td>${sub.trial_start_date ? new Date(sub.trial_start_date).toLocaleDateString() : '-'}</td></tr>
                                            <tr><td><strong>Trial End Date:</strong></td><td>${sub.trial_end_date ? new Date(sub.trial_end_date).toLocaleDateString() : '-'}</td></tr>
                                            <tr><td><strong>Payment Cycle Start:</strong></td><td>${sub.payment_cycle_start_date ? new Date(sub.payment_cycle_start_date).toLocaleDateString() : '-'}</td></tr>
                                            <tr><td><strong>Payment Cycle End:</strong></td><td>${sub.payment_cycle_end_date ? new Date(sub.payment_cycle_end_date).toLocaleDateString() : '-'}</td></tr>
                                            <tr><td><strong>Auto Generate Invoice:</strong></td><td>${sub.auto_generate_invoice == 1 ? 'Yes' : 'No'}</td></tr>
                                            <tr><td><strong>Renew Count:</strong></td><td>${sub.renew_count}</td></tr>
                                            <tr><td><strong>Created At:</strong></td><td>${sub.created_at ? new Date(sub.created_at).toLocaleDateString() : '-'}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            `;

                            $('#subscriptionDetailsContent').html(detailsHtml);
                            $('#subscriptionDetailsModal').modal('show');
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: "Error loading subscription details"
                            });
                        }
                    },
                    error: function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    }
                });
            });

            // Handle status dropdown change
            $(document).on('change', '.status-dropdown', function() {
                var subscriptionId = $(this).data('id');
                var newStatus = $(this).val();
                var originalStatus = $(this).data('original');

                if (newStatus !== originalStatus) {
                    if (confirm('Are you sure you want to change status to ' + newStatus + '?')) {
                        loadershow();

                        $.ajax({
                            type: 'PUT',
                            url: "{{ route('subscription.statusupdate', '__id__') }}".replace(
                                '__id__', subscriptionId),
                            data: {
                                token: "{{ session()->get('api_token') }}",
                                company_id: "{{ session()->get('company_id') }}",
                                user_id: "{{ session()->get('user_id') }}",
                                status: newStatus
                            },
                            success: function(response) {
                                loaderhide();
                                if (response.status == 200) {
                                    Toast.fire({
                                        icon: "success",
                                        title: response.message
                                    });
                                    // Update the dropdown original value for future changes
                                    $(this).data('original', newStatus);
                                    // Reload table to show updated status
                                    table.draw();
                                } else {
                                    Toast.fire({
                                        icon: "error",
                                        title: response.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                loaderhide();
                                handleAjaxError(xhr);
                            }
                        });
                    } else {
                        // Reset to original value if cancelled
                        $(this).val(originalStatus);
                    }
                }
            });

            // Helper function to get status badge class
            function getStatusClass(status) {
                switch (status) {
                    case 'active':
                        return 'badge-success';
                    case 'trial':
                        return 'badge-warning';
                    case 'expired':
                        return 'badge-danger';
                    case 'suspended':
                        return 'badge-secondary';
                    default:
                        return 'badge-secondary';
                }
            }
        });
    </script>
@endpush
