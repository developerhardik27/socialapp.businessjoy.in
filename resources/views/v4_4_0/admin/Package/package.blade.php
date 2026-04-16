@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')
@section('page_title')
    {{ config('app.name') }} - Packages
@endsection
@section('table_title')
    Packages
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
@if (session('user_permissions.adminmodule.package.add') == '1')
    @section('addnew')
        {{ route('admin.addpackage') }}
    @endsection
    @section('addnewbutton')
        <button class="btn btn-sm btn-primary">
            <span data-toggle="tooltip" data-placement="bottom" data-original-title="Add New Package" class="">+ Add
                New</span>
        </button>
    @endsection
@endif

@section('table-content')
    <table id="data" class="dataTable display table table-bordered table-striped w-100">
        <thead>
            <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Type</th>
                <th>Price</th>
                <th>Currency</th>
                <th>Trial Days</th>
                <th>Subscribed</th>
                <th>Status</th>
                <th>Created On</th>
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
            var global_response = '';
            let table = '';

            // fetch & show package data in table
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
                        url: "{{ route('package.index') }}",
                        data: function(d) {
                            d.user_id = "{{ session()->get('user_id') }}";
                            d.company_id = "{{ session()->get('company_id') }}";
                            d.token = "{{ session()->get('api_token') }}";
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
                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                            orderable: true,
                            searchable: false,
                            defaultContent: '-'
                        },
                        {
                            data: 'name',
                            name: 'name',
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
                            data: 'price',
                            name: 'price',
                            orderable: true,
                            searchable: false,
                            render: function(data, type, row) {
                                return data != null ? data : '-';
                            }
                        },
                        {
                            data: 'currency_symbol',
                            name: 'currency_symbol',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return data != null ? data : '-';
                            }
                        },
                        {
                            data: 'trial_days',
                            name: 'trial_days',
                            orderable: true,
                            searchable: false,
                            render: function(data, type, row) {
                                return data != null ? data : '-';
                            }
                        },
                        {
                            data: 'subscribed_count',
                            name: 'subscribed_count',
                            orderable: true,
                            searchable: false,
                            render: function(data, type, row) {
                                return data != null ? `<span class="badge badge-info">${data}</span>` : '<span class="badge badge-secondary">0</span>';
                            }
                        },
                        {
                            data: 'is_active',
                            name: 'is_active',
                            orderable: true,
                            searchable: false,
                            render: function(data, type, row) {
                                let actionBtns = ``;
                                @if (session('user_permissions.adminmodule.package.edit') == '1')
                                    if (data == 1) {
                                        actionBtns += `<div id="status_${row.id}" data-toggle="tooltip" data-placement="bottom" data-original-title="Deactivate"> 
                                            <button data-status="${row.id}" class="status-active btn btn-outline-success btn-rounded btn-sm my-0" >Active</button>
                                        </div>`;
                                    } else {
                                        actionBtns += `<div id="status_${row.id}" data-toggle="tooltip" data-placement="bottom" data-original-title="Activate">
                                                <button data-status="${row.id}" class="status-deactive btn btn-outline-dark btn-rounded btn-sm my-0" >Inactive</button>
                                            </div>`;
                                    }
                                @endif
                                return actionBtns;
                            }
                        },
                        {
                            data: 'created_at_formatted',
                            name: 'created_at_formatted',
                            orderable: true,
                            searchable: false,
                            defaultContent: '-'
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                actionBtns = '';
                                @if (session('user_permissions.adminmodule.package.view') == '1')
                                    actionBtns += ` 
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="View Package Details">
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
                                actionBtns = '';
                                @if (session('user_permissions.adminmodule.package.edit') == '1')
                                    var editPackageUrl = "{{ route('admin.editpackage', '__packageid__') }}"
                                        .replace('__packageid__', data);
                                    actionBtns += ` 
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="Edit">
                                            <a href="${editPackageUrl}">
                                                <button type="button" class="btn btn-success btn-rounded btn-sm my-0 mb-2">
                                                    <i class="ri-edit-fill"></i>
                                                </button>
                                            </a>
                                        </span>
                                    `;
                                @endif
                                @if (session('user_permissions.adminmodule.package.delete') == '1')
                                    actionBtns += ` 
                                        <span class="" data-toggle="tooltip" data-placement="bottom" data-original-title="Delete">
                                            <button type="button" data-id= '${data}' class=" del-btn btn btn-danger btn-rounded btn-sm my-0 mb-2">
                                                <i class="ri-delete-bin-fill"></i>
                                            </button>
                                        </span>
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

            //  package status update active to deactive              
            $(document).on("click", ".status-active", function() {
                element = $(this);
                showConfirmationDialog(
                    'Are you sure?',
                    'to change status to inactive?',
                    'Yes, change',
                    'No, cancel',
                    'question',
                    () => {
                        loadershow();
                        var statusid = element.data('status');
                        changepackagestatus(statusid, 'inactive');
                    }
                );
            });

            //  package status update deactive to  active            
            $(document).on("click", ".status-deactive", function() {
                element = $(this);
                showConfirmationDialog(
                    'Are you sure?',
                    'to change status to active?',
                    'Yes, change',
                    'No, cancel',
                    'question',
                    () => {
                        loadershow();
                        var statusid = element.data('status');
                        changepackagestatus(statusid, 'active');
                    }
                );
            });

            function changepackagestatus(packageid, statusvalue) {
                let packageStatusUpdateUrl = "{{ route('package.statusupdate', '__packageId__') }}".replace('__packageId__',
                    packageid);
                $.ajax({
                    type: 'PUT',
                    url: packageStatusUpdateUrl,
                    data: {
                        status: statusvalue,
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });
                            if (statusvalue == 'active') {
                                $('#status_' + packageid).html('<button data-status= ' +
                                    packageid +
                                    ' class="status-active btn btn-outline-success btn-rounded btn-sm my-0" >Active</button>'
                                );
                            } else {
                                $('#status_' + packageid).html('<button data-status= ' +
                                    packageid +
                                    ' class="status-deactive btn btn-outline-dark btn-rounded btn-sm my-0" >Inactive</button>'
                                );
                            }
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
                    error: function(xhr, status, error) {
                        loaderhide();
                        console.log(xhr.responseText);
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
                });
            }

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
                        let packageDeleteUrl = "{{ route('package.delete', '__deleteId__') }}".replace(
                            '__deleteId__',
                            deleteid);
                        $.ajax({
                            type: 'PUT',
                            url: packageDeleteUrl,
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
                            error: function(xhr, status,
                                error) {
                                loaderhide();
                                console.log(xhr.responseText);
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
                var data = $(this).data('view');
                $.each(global_response.data, function(key, pkg) {
                    if (pkg.id == data) {
                        $('#details').append(`
                            <tr>
                                <th>Name</th>                         
                                <td>${pkg.name != null ? pkg.name : '-'}</td>
                            </tr>
                            <tr>
                                <th>Type</th>                         
                                <td>${pkg.type != null ? pkg.type : '-'}</td>
                            </tr>
                            <tr>
                                <th>Price</th>                         
                                <td>${pkg.currency_symbol != null ? pkg.currency_symbol : ''} ${pkg.price != null ? pkg.price : '-'}</td>
                            </tr>
                            <tr>
                                <th>Trial Days</th>                         
                                <td>${pkg.trial_days != null ? pkg.trial_days : '-'}</td>
                            </tr>
                            <tr>
                                <th>Subscribed Count</th>                         
                                <td>${pkg.subscribed_count != null ? pkg.subscribed_count : '0'}</td>
                            </tr>
                            <tr>
                                <th>Description</th>                         
                                <td>${pkg.description != null ? pkg.description : '-'}</td>
                            </tr>
                            <tr>
                                <th>Status</th>                         
                                <td>${pkg.is_active == 1 ? 'Active' : 'Inactive'}</td>
                            </tr>
                            <tr>
                                <th>Created On</th>                         
                                <td>${pkg.created_at_formatted != null ? pkg.created_at_formatted : '-'}</td>
                            </tr>
                        `)
                    }
                });
            });
        });
    </script>
@endpush