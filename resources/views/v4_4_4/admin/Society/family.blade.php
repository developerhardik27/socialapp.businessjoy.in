@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Family Members
@endsection
@section('table_title')
    Family Members
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

        #details .person-block td {
            background: #f5f7ff;
            font-weight: 600;
        }
    </style>
@endsection
@if (session('user_permissions.societymodule.familymembers.add') == '1')
    @section('addnew')
        {{ route('admin.addsocietyfamily') }}
    @endsection
    @section('addnewbutton')
        <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Add New Family"
            class="btn btn-sm btn-primary">
            <span class="">+ Add New</span>
        </button>
    @endsection
@endif
@section('table-content')
    <table id="data" class="table table-bordered display w-100 table-striped">
        <thead>
            <tr>
                <th>Id</th>
                <th>Main Family Member</th>
                <th>Total Persons</th>
                <th>Created At</th>
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
            // load family data in table 
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
                        url: "{{ route('family.index') }}",
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
                            data: 'mainFamilyMemberFullName',
                            name: 'mainFamilyMemberFullName',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'family_persons',
                            name: 'family_persons',
                            orderable: false,
                            searchable: false,
                            defaultContent: '-',
                            render: function(data, type, row) {
                                return Array.isArray(data) ? data.length : '-';
                            }
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            orderable: true,
                            searchable: false,
                            defaultContent: '-',
                            render: function(data, type, row) {
                                return data ? data.split('T')[0] : '-';
                            }
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                var actions = '-';
                                @if (session('user_permissions.societymodule.familymembers.view') == '1')
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
                                @if (session('user_permissions.societymodule.familymembers.edit') == '1')
                                    actionBtns += `
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="Edit">
                                            <button type="button" data-id='${row.id}' class="edit-btn btn btn-success btn-rounded btn-sm my-0">
                                                <i class="ri-edit-fill"></i>
                                            </button>
                                        </span>
                                    `;
                                @endif
                                @if (session('user_permissions.societymodule.familymembers.delete') == '1')
                                    actionBtns += `
                                        <span>
                                            <button data-toggle="tooltip" data-placement="bottom" data-original-title="Delete" type="button" data-id= '${row.id}' class=" del-btn btn btn-danger btn-rounded btn-sm my-0">
                                                <i  class="ri-delete-bin-fill"></i>
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
                let editUrl = "{{ route('admin.editsocietyfamily', '__editId__') }}".replace('__editId__',
                    editid);
                window.location.href = editUrl;
            });

            // delete family             
            $(document).on("click", ".del-btn", function() {
                var deleteid = $(this).data('id');
                let familyDeleteUrl = "{{ route('family.destroy', '__deleteId__') }}".replace(
                    '__deleteId__', deleteid);
                var row = this;
                showConfirmationDialog(
                    'Are you sure?',
                    'to delete this record ?',
                    'Yes, delete',
                    'No, cancel',
                    'question',
                    () => {
                        loadershow();
                        ajaxRequest('PUT', familyDeleteUrl, {
                            token: "{{ session()->get('api_token') }}",
                            company_id: "{{ session()->get('company_id') }}",
                            user_id: "{{ session()->get('user_id') }}",
                        }).done(function(response) {
                            if (response.status == 200) {
                                Toast.fire({
                                    icon: "success",
                                    title: "succesfully deleted"
                                });
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
                        }).fail(function(xhr) {
                            loaderhide();
                            handleAjaxError(xhr);
                        });
                    }
                );
            });

            // view family data in pop-up
            $(document).on("click", ".view-btn", function() {
                $('#details').html('');
                var data = $(this).data('view');
                $.each(global_response.data, function(key, familydetail) {
                    if (familydetail.id == data) {
                        $('#details').append(`
                            <tr>
                                <th>Main Family Member</th>
                                <td>${familydetail.mainFamilyMemberFullName || '-'}</td>
                            </tr>
                            <tr>
                                <th>Created On</th>
                                <td>${(familydetail.created_at || '-').split('T')[0]}</td>
                            </tr>
                        `);

                        if (Array.isArray(familydetail.family_persons)) {
                            $.each(familydetail.family_persons, function(pkey, person) {
                                $('#details').append(`
                                    <tr class="person-block">
                                        <td colspan="2">Person ${pkey + 1}${person.main_family_member == 1 ? ' (Main Member)' : ''}</td>
                                    </tr>
                                    <tr>
                                        <th>Full Name</th>
                                        <td>${person.full_name || ((person.first_name || '') + ' ' + (person.last_name || '') + ' ' + (person.surname || '')).trim() || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>${person.email || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th>Mobile</th>
                                        <td>${person.mobile || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th>Gender</th>
                                        <td>${person.gender || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th>DOB</th>
                                        <td>${person.dob || '-'}</td>
                                    </tr>
                                `);
                            });
                        }
                    }
                });
            });
        });
    </script>
@endpush
