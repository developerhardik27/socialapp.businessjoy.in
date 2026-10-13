@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Donor
@endsection
@section('table_title')
    Donor
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
@if (session('user_permissions.societymodule.donation.add') == '1')
    @section('addnew')
        {{ route('admin.addsocietydonor') }}
    @endsection
    @section('addnewbutton')
        <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Add New Donor"
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
                <th>Donor Name</th>
                <th>Family Person</th>
                <th>Donation Type</th>
                <th>Amount</th>
                <th>Donate Date</th>
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
            // load donor data in table 
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
                        url: "{{ route('donor.index') }}",
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
                            data: null,
                            name: 'first_name',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            render: function(data, type, row) {
                                return ((row.first_name || '') + ' ' + (row.last_name || '') + ' ' +
                                    (row.surname || '')).trim() || '-';
                            }
                        },
                        {
                            data: 'family_person_name',
                            name: 'family_person_name',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'donation_type_name',
                            name: 'donation_type_name',
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
                            data: 'donate_date',
                            name: 'donate_date',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                var actions = '-';
                                @if (session('user_permissions.societymodule.donation.view') == '1')
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
                                @if (session('user_permissions.societymodule.donation.edit') == '1')
                                    actionBtns += `
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="Edit">
                                            <button type="button" data-id='${row.id}' class="edit-btn btn btn-success btn-rounded btn-sm my-0">
                                                <i class="ri-edit-fill"></i>
                                            </button>
                                        </span>
                                    `;
                                @endif
                                @if (session('user_permissions.societymodule.donation.delete') == '1')
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
                let editUrl = "{{ route('admin.editsocietydonor', '__editId__') }}".replace('__editId__',
                    editid);
                window.location.href = editUrl;
            });

            // delete donor             
            $(document).on("click", ".del-btn", function() {
                var deleteid = $(this).data('id');
                let donorDeleteUrl = "{{ route('donor.destroy', '__deleteId__') }}".replace(
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
                        ajaxRequest('PUT', donorDeleteUrl, {
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

            // view donor data in pop-up
            $(document).on("click", ".view-btn", function() {
                $('#details').html('');
                var data = $(this).data('view');
                $.each(global_response.data, function(key, donor) {
                    if (donor.id == data) {
                        var fields = {
                            'Donor Name': ((donor.first_name || '') + ' ' + (donor.last_name || '') +
                                ' ' + (donor.surname || '')).trim() || '-',
                            'Family Person': donor.family_person_name || '-',
                            'Email': donor.email || '-',
                            'Mobile': donor.mobile || '-',
                            'Donation Type': donor.donation_type_name || '-',
                            'Amount': donor.amount || '-',
                            'Donate Date': donor.donate_date || '-',
                            'Created On': (donor.created_at || '-').split('T')[0]
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
