@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Meeting Attendance
@endsection
@section('table_title')
    Meeting Attendance
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
@if (session('user_permissions.societymodule.meetingattendance.add') == '1')
    @section('addnew')
        {{ route('admin.addsocietymeetingattendance') }}
    @endsection
    @section('addnewbutton')
        <button type="button" data-toggle="tooltip" data-placement="bottom"
            data-original-title="Take Attendance" class="btn btn-sm btn-primary">
            <span class="">+ Take Attendance</span>
        </button>
    @endsection
@endif
@section('table-content')
    <table id="data" class="table table-bordered display w-100 table-striped">
        <thead>
            <tr>
                <th>Id</th>
                <th>Meeting Name</th>
                <th>Meeting Date</th>
                <th>Time</th>
                <th>Members</th>
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
            // load attendance data in table 
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
                        url: "{{ route('karobarimeeting.attendance.index') }}",
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
                            data: 'meeting_name',
                            name: 'meeting_name',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'meeting_date',
                            name: 'meeting_date',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            defaultContent: '-',
                            render: function(data, type, row) {
                                var from = row.meeting_time_from || '';
                                var to = row.meeting_time_to || '';
                                return (from || to) ? (from + ' - ' + to) : '-';
                            }
                        },
                        {
                            data: 'attendance_details',
                            name: 'attendance_details',
                            orderable: false,
                            searchable: false,
                            defaultContent: '-',
                            render: function(data, type, row) {
                                if (Array.isArray(data)) {
                                    var present = data.filter(function(m) {
                                        return m.status == 'present';
                                    }).length;
                                    return present + ' / ' + data.length;
                                }
                                return '-';
                            }
                        },
                        {
                            data: 'status',
                            name: 'status',
                            orderable: false,
                            searchable: false,
                            defaultContent: '-',
                            render: function(data, type, row) {
                                if (data) {
                                    return `<span class="badge badge-success">${data}</span>`;
                                }
                                return '-';
                            }
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                var actions = '-';
                                @if (session('user_permissions.societymodule.meetingattendance.view') == '1')
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
                                @if (session('user_permissions.societymodule.meetingattendance.edit') == '1')
                                    actionBtns += `
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="Edit">
                                            <button type="button" data-id='${row.karobari_meeting_id}' class="edit-btn btn btn-success btn-rounded btn-sm my-0">
                                                <i class="ri-edit-fill"></i>
                                            </button>
                                        </span>
                                    `;
                                @endif
                                @if (session('user_permissions.societymodule.meetingattendance.delete') == '1')
                                    actionBtns += `
                                        <span>
                                            <button data-toggle="tooltip" data-placement="bottom" data-original-title="Delete" type="button" data-id= '${row.karobari_meeting_id}' class=" del-btn btn btn-danger btn-rounded btn-sm my-0">
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
                let editUrl = "{{ route('admin.editsocietymeetingattendance', '__editId__') }}".replace(
                    '__editId__', editid);
                window.location.href = editUrl;
            });

            // delete attendance             
            $(document).on("click", ".del-btn", function() {
                var deleteid = $(this).data('id');
                let attendanceDeleteUrl = "{{ route('karobarimeeting.attendance.delete', '__deleteId__') }}"
                    .replace('__deleteId__', deleteid);
                var row = this;
                showConfirmationDialog(
                    'Are you sure?',
                    'to delete this record ?',
                    'Yes, delete',
                    'No, cancel',
                    'question',
                    () => {
                        loadershow();
                        ajaxRequest('PUT', attendanceDeleteUrl, {
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

            // view attendance data in pop-up
            $(document).on("click", ".view-btn", function() {
                $('#details').html('');
                var data = $(this).data('view');
                $.each(global_response.data, function(key, attendance) {
                    if (attendance.id == data) {
                        $('#details').append(`
                            <tr><th>Meeting Name</th><td>${attendance.meeting_name || '-'}</td></tr>
                            <tr><th>Meeting Date</th><td>${attendance.meeting_date || '-'}</td></tr>
                            <tr><th>Time</th><td>${(attendance.meeting_time_from || '-')} - ${(attendance.meeting_time_to || '-')}</td></tr>
                            <tr><th>Status</th><td>${attendance.status || '-'}</td></tr>
                            <tr><th>Present Members</th><td>${attendance.family_person_names || '-'}</td></tr>
                        `);
                        if (Array.isArray(attendance.attendance_details)) {
                            $('#details').append(
                                `<tr><th colspan="2" style="background:#f5f7ff;">Attendance Detail</th></tr>`
                            );
                            $.each(attendance.attendance_details, function(k, m) {
                                $('#details').append(
                                    `<tr><th>${m.name || '-'}</th><td>${m.status || '-'}</td></tr>`
                                );
                            });
                        }
                    }
                });
            });
        });
    </script>
@endpush
