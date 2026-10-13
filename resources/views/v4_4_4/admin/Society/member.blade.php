@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Member
@endsection
@section('table_title')
    Member
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
    </style>
@endsection
@section('table-content')
    <table id="data" class="table table-bordered display w-100 table-striped">
        <thead>
            <tr>
                <th>Id</th>
                <th>Full Name</th>
                <th>Lifetime Member No</th>
                <th>Receipt Number</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Status</th>
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
            loaderhide();
            var global_response = '';
            let table = '';
            // load member data in table 
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
                        url: "{{ route('member.index') }}",
                        data: function(d) {
                            d.user_id = "{{ session()->get('user_id') }}";
                            d.company_id = "{{ session()->get('company_id') }}";
                            d.token = "{{ session()->get('api_token') }}";
                        },
                        dataSrc: function(json) {
                            if (json.status == 200 && json.data && json.data.length) {
                                global_response = json;
                                return json.data;
                            }

                            global_response = '';
                            // show api message (no data / unauthorized) inside the table instead of error toast
                            if (table && json.message) {
                                table.settings()[0].oLanguage.sEmptyTable = json.message;
                            }
                            return [];
                        },
                        complete: function() {
                            loaderhide();
                        },
                        error: function(xhr) {
                            global_response = '';
                            if (table) {
                                table.settings()[0].oLanguage.sEmptyTable =
                                    'Error loading data';
                            }
                        }
                    },
                    order: [
                        [0, 'desc']
                    ],
                    columns: [{
                            data: 'id',
                            name: 'id',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'full_name',
                            name: 'full_name',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'lifetime_member_no',
                            name: 'lifetime_member_no',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'receipt_number',
                            name: 'receipt_number',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'email',
                            name: 'email',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'mobile',
                            name: 'mobile',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'is_active',
                            name: 'is_active',
                            orderable: false,
                            searchable: false,
                            defaultContent: '-',
                            render: function(data, type, row) {
                                if (data == 1 || data == 'active') {
                                    return `<span class="badge badge-success">Active</span>`;
                                }
                                return `<span class="badge badge-secondary">Inactive</span>`;
                            }
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                var actions = '-';
                                @if (session('user_permissions.societymodule.member.view') == '1')
                                    actions = ` 
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="View Details">
                                            <button type="button"  data-view = '${row.id}' data-toggle="modal" data-target="#exampleModalScrollable" class="view-btn btn btn-info btn-rounded btn-sm my-0">
                                                <i class="ri-indent-decrease"></i>
                                            </button>
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
                            render: function(data, type, row) {
                                let actionBtns = '';
                                @if (session('user_permissions.societymodule.member.edit') == '1')
                                    actionBtns += `
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="Edit">
                                            <button type="button" data-id='${row.family_person_id}' class="edit-btn btn btn-success btn-rounded btn-sm my-0">
                                                <i class="ri-edit-fill"></i>
                                            </button>
                                        </span>
                                    `;
                                @endif

                                return actionBtns || '-';
                            }
                        }
                    ],

                    pagingType: "full_numbers",
                    drawCallback: function(settings) {
                        $('[data-toggle="tooltip"]').tooltip({
                            boundary: 'window',
                            offset: '0, 10'
                        });
                    }
                });

            }

            //call function for loaddata
            loaddata();

            // redirect to edit page
            $(document).on("click", ".edit-btn", function() {
                loadershow();
                var editid = $(this).data('id');
                let editUrl = "{{ route('admin.editsocietymember', '__editId__') }}".replace('__editId__',
                    editid);
                window.location.href = editUrl;
            });

            // view member data in pop-up
            $(document).on("click", ".view-btn", function() {
                $('#details').html('');
                var data = $(this).data('view');
                $.each(global_response.data, function(key, memberdetail) {
                    if (memberdetail.id == data) {
                        var fields = {
                            'Full Name': memberdetail.full_name || '-',
                            'Email': memberdetail.email || '-',
                            'Mobile': memberdetail.mobile || '-',
                            'Gender': memberdetail.gender || '-',
                            'DOB': memberdetail.dob || '-',
                            'Marital Status': memberdetail.marital_status || '-',
                            'Lifetime Member No': memberdetail.lifetime_member_no || '-',
                            'Receipt Number': memberdetail.receipt_number || '-',
                            'Address': [memberdetail.address_house_no_building_name,
                                memberdetail.address_landmark, memberdetail.address_area,
                                memberdetail.address_city_name, memberdetail
                                .address_state_name, memberdetail.address_country_name,
                                memberdetail.address_pincode
                            ].filter(Boolean).join(', ') || '-',
                            'Job Role': memberdetail.job_role || '-',
                            'Company Name': memberdetail.company_name || '-',
                            'Company Address': [memberdetail
                                .company_house_no_building_name, memberdetail
                                .company_landmark, memberdetail.company_area,
                                memberdetail.company_city_name, memberdetail
                                .company_state_name, memberdetail
                                .company_country_name, memberdetail.company_pincode
                            ].filter(Boolean).join(', ') || '-',
                            'Business Category': memberdetail.business_category_name || '-',
                            'Business Subcategory': memberdetail.business_subcategory_name ||
                                '-',
                            'Services': memberdetail.services || '-',
                            'Shakh': memberdetail.shakh || '-',
                            'Created On': (memberdetail.created_at || '-').split('T')[0]
                        };
                        $.each(fields, function(label, value) {
                            $('#details').append(
                                `<tr><th>${label}</th><td>${value}</td></tr>`);
                        });
                    }
                });
            });
        });
    </script>
@endpush
