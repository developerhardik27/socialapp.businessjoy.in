@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Subscription Payments
@endsection
@section('table_title')
    Subscription Payments
@endsection

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
                <h6>Payment Status</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-1">
                        <select class="filter select2 form-control w-100" id="filter_payment_status" multiple>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
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
                <h6>Due Date</h6>
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
    </div>
@endsection

@section('table-content')
    <!-- Payments Table -->
    <div class="table-responsive">
        <table id="payments_data" class="dataTable display table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Company</th>
                    <th>Package</th>
                    <th>Payment Start</th>
                    <th>Payment End</th>
                    <th>Next Billing</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be loaded via AJAX -->
            </tbody>
        </table>
    </div>
@endsection

@push('ajax')
    <script>
        $('document').ready(function() {
            loaderhide();
            let table = '';
            var global_response = '';
            // Initialize DataTable
            table = $('#payments_data').DataTable({
                language: {
                    lengthMenu: '_MENU_ &nbsp;Entries per page'
                },
                destroy: true,
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('subscriptionpayment.index') }}",
                    type: 'GET',
                    data: function(d) {
                        d.token = "{{ session()->get('api_token') }}";
                        d.company_id = "{{ session()->get('company_id') }}";
                        d.user_id = "{{ session()->get('user_id') }}";
                        // Payment Status Filter
                        d.payment_status = $('#filter_payment_status').val();
                        // Company Filter
                        d.company = $('#filter_company').val();
                        // Package Filter
                        d.package = $('#filter_package').val();
                        // Next Billing Date Filter
                        d.next_billing_from_date = $('#filter_next_billing_from_date').val();
                        d.next_billing_to_date = $('#filter_next_billing_to_date').val();
                        // Payment Start Date Filter
                        d.payment_start_from_date = $('#filter_payment_start_from_date').val();
                        d.payment_start_to_date = $('#filter_payment_start_to_date').val();
                        // Payment End Date Filter
                        d.payment_end_from_date = $('#filter_payment_end_from_date').val();
                        d.payment_end_to_date = $('#filter_payment_end_to_date').val();
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
                        data: 'payment_start_date',
                        name: 'payment_start_date',
                        orderable: true,
                        searchable: false,
                        render: function(data, type, row) {
                            return data ? new Date(data).toLocaleDateString() : '-';
                        }
                    },
                    {
                        data: 'payment_end_date',
                        name: 'payment_end_date',
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
                        data: 'payment_status',
                        name: 'payment_status',
                        orderable: true,
                        searchable: false,
                        render: function(data, type, row) {
                            let statusClass = '';
                            switch (data) {
                                case 'paid':
                                    statusClass = 'badge-success';
                                    break;
                                case 'pending':
                                    statusClass = 'badge-warning';
                                    break;
                                default:
                                    statusClass = 'badge-secondary';
                            }
                            return data ?
                                `<span class="badge ${statusClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>` :
                                '-';
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            let actionBtns = '';
                            @if (session('user_permissions.adminmodule.subscription.edit') == '1')
                                if(row.payment_status == 'pending'){
                                    actionBtns += `
                                    <button type="button" data-id="${data}" data-status="${row.payment_status}" 
                                        class="status-btn btn btn-sm btn-success mr-1" 
                                        data-toggle="tooltip" title="Mark as Paid">
                                        <i class="ri-check-line"></i>
                                    </button>
                                `;
                                }else{
                                    actionBtns = 'No Action';
                                } 
                            @endif
                            return actionBtns;
                        }
                    }
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

            // Initialize Select2 for all filter dropdowns after data is loaded
            setTimeout(function() {
                $('#filter_payment_status').select2({
                    searche: true,
                    width: '100%',
                    placeholder: 'Select options...',
                    allowClear: true
                });
            }, 500);


            // Load companies and packages for filters
            loadCompanies();
            loadPackages();

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
                                    company.app_version ? company.app_version.replace(/_/g,
                                        '.') : null
                                ].filter(Boolean).join(' - ');
                                companySelect.append(
                                    `<option value='${company.id}'>${companydetails}</option>`
                                );
                            });
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

                $('#filter_payment_status').val(null).trigger('change');
                $('#filter_company').val(null).trigger('change');
                $('#filter_package').val(null).trigger('change');

                table.draw();
                hideOffCanvass(); // close OffCanvass
            });

            // Handle status change
            $(document).on('click', '.status-btn', function() {
                var paymentId = $(this).data('id');
                var currentStatus = $(this).data('status');
                var newStatus = currentStatus == 'pending' ? 'paid' : 'pending';

                showConfirmationDialog(
                    'Are you sure?',
                    'You want to change payment status to ' + newStatus + '?',
                    'Yes, change',
                    'No, cancel',
                    'question',
                    () => {
                        loadershow();

                        $.ajax({
                            type: 'PUT',
                            url: "{{ route('subscriptionpayment.updatestatus', '__id__') }}"
                                .replace(
                                    '__id__', paymentId),
                            data: {
                                token: "{{ session()->get('api_token') }}",
                                company_id: "{{ session()->get('company_id') }}",
                                user_id: "{{ session()->get('user_id') }}",
                                payment_status: newStatus
                            },
                            success: function(response) {
                                loaderhide();
                                if (response.status == 200) {
                                    Toast.fire({
                                        icon: "success",
                                        title: response.message
                                    });
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
                    }
                );
            });
        });
    </script>
@endpush
