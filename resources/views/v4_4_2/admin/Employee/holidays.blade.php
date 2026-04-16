@php
    $folder = session('folder_name');
    $user_id = session('user_id');
@endphp
@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Holidays
@endsection
@section('table_title')
    Holidays List
@endsection

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* ── Toolbar buttons ── */
        .fc .fc-toolbar .fc-button {
            background: transparent !important;
            color: #3c4043 !important;
            border: 1px solid #dadce0 !important;
            border-radius: 4px !important;
            font-family: 'Google Sans', sans-serif !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            padding: 6px 12px !important;
            box-shadow: none !important;
            text-transform: capitalize;
            transition: background .15s;
        }

        .fc .fc-toolbar .fc-button.fc-button-active,
        .fc .fc-toolbar .fc-button:active,
        .fc .fc-toolbar .fc-button:focus {
            background: #e8f0fe !important;
            color: #1a73e8 !important;
            border-color: #1a73e8 !important;
            box-shadow: none !important;
        }

        .fc .fc-toolbar .fc-button:hover {
            background: #f1f3f4 !important;
            color: #3c4043 !important;
        }

        /* Today button pill */
        .fc .fc-today-button {
            border-radius: 20px !important;
            padding: 6px 16px !important;
        }

        /* Prev/Next arrows round */
        .fc .fc-prev-button,
        .fc .fc-next-button {
            border: none !important;
            border-radius: 50% !important;
            width: 36px !important;
            height: 36px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Toolbar title */
        .fc .fc-toolbar-title {
            font-family: 'Google Sans', sans-serif !important;
            font-size: 22px !important;
            font-weight: 400 !important;
            color: #3c4043 !important;
        }

        .fc-header-toolbar {
            flex-wrap: wrap !important;
            gap: 0.5rem;
            padding: 14px 16px 10px !important;
        }

        .fc-toolbar-chunk {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 4px;
        }

        /* ── Day header row ── */
        .fc .fc-col-header-cell {
            font-size: 11px !important;
            font-weight: 500 !important;
            color: #70757a !important;
            text-transform: uppercase;
            letter-spacing: .6px;
            border: none !important;
            padding: 8px 0 6px !important;
        }

        .fc .fc-col-header-cell-cushion {
            color: inherit !important;
            text-decoration: none !important;
        }

        /* ── Day number ── */
        .fc .fc-daygrid-day-number {
            font-size: 13px !important;
            color: #70757a !important;
            padding: 4px 8px !important;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
            transition: background .15s;
        }

        .fc .fc-daygrid-day-number:hover {
            background: #f1f3f4;
            color: #3c4043 !important;
        }

        .fc .fc-day-today .fc-daygrid-day-number {
            background: #1a73e8 !important;
            color: #fff !important;
            font-weight: 500 !important;
        }

        .fc .fc-day-today {
            background: transparent !important;
        }

        /* ── Grid borders lighter ── */
        .fc .fc-scrollgrid {
            border: none !important;
        }

        .fc td,
        .fc th {
            border-color: #f1f3f4 !important;
        }

        /* ── Event pills ── */
        .fc-daygrid-event {
            border-radius: 4px !important;
            padding: 2px 6px !important;
            font-size: 12px !important;
            font-weight: 500 !important;
            border: none !important;
            border-left-width: 3px !important;
            border-left-style: solid !important;
            margin-bottom: 2px !important;
            transition: filter .15s, transform .1s;
        }

        .fc-daygrid-event:hover {
            filter: brightness(.93);
            transform: translateY(-1px);
        }

        .fc-event .fc-event-title {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        .fc-event-time {
            display: none !important;
        }

        /* ── More link ── */
        .fc .fc-daygrid-more-link {
            font-size: 11px !important;
            color: #1a73e8 !important;
        }

        /* ── List view ── */
        .fc-list-event:hover td {
            background: #f1f3f4 !important;
        }

        .fc-list-day-cushion {
            background: #f8f9fa !important;
            font-size: 13px;
            color: #70757a !important;
        }


        /* ── Legend chips (replacing plain ul/li) ── */
        .gc-legend-list {
            list-style: none;
            padding-left: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .gc-legend-list li {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 5px;
            font-weight: 500;
            cursor: default;
            user-select: none;
            transition: transform .15s;
        }

        .gc-legend-list li:hover {
            transform: scale(1.04);
        }

        .gc-legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        #modalBottomBar {
            height: 5px;
        }

        .fc .fc-button {
            margin-left: 5px !important;
        }

        #holidayModal .modal-dialog {
            max-width: 500px;
            /* optional width control */
        }

        #holidayModal .modal-content {
            max-height: 85vh;
            /* maximum height */
        }

        #holidayModal .modal-body {
            overflow-y: auto;
        }

        .fc-event-title {
            white-space: pre-line !important;
        }
    </style>
@endsection

@if (session('user_permissions.hrmodule.companiesholidays.add') == '1' || $user_id == 1)

    @section('advancefilter')
        <div class="col-sm-12 text-right">
            <button type="button" class="btn btn-sm btn-primary mt-1 mr-3" data-toggle="modal" data-target="#holidayModal"
                data-placement="bottom" data-original-title="Add New Holiday">
                <span>+ Add New Holidays</span>
            </button>
        </div>
        <div class="col-sm-12 text-right">
            <button class="btn btn-sm btn-primary m-0 mr-3" data-toggle="tooltip" data-placement="bottom"
                data-original-title="Filters" onclick="showOffCannvas()">
                <i class="ri-filter-line"></i>
            </button>
        </div>
    @endsection

    @section('sidebar-filters')
        {{-- <div class="col-12 p-0">
            <div class="card">
                <div class="card-header">
                    <h6>Event Type</h6>
                </div>
                <div class="card-body">
                    <select class="form-control filter" name="filter_event" id="filter_event">
                        <option value="">Select Type</option>
                        <option value="holiday">Holiday</option>
                        <option value="increment">Increment</option>
                        <option value="interview">Interview</option>
                        <option value="leave">Leave</option>
                        <option value="joining">Joining</option>
                        <option value="meeting">Meeting</option>
                        <option value="tours">Tours</option>
                        <option value="seminar">Seminar</option>
                    </select>
                </div>
            </div>
        </div> --}}
        <div class="col-12 p-0">
            <div class="card">
                <div class="card-header">
                    <h6>Holidays Date</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-1">
                            <label for="filter_date_from">From</label>
                            <input type="date" class="form-control filter " name="filter_date_from" id="filter_date_from"
                                placeholder="From Date">
                        </div>
                        <div class="col-6 mb-1">
                            <label for="filter_date_to">To</label>
                            <input type="date" class="form-control filter" name="filter_date_to" id="filter_date_to"
                                placeholder="To Date">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
@endif

@section('table-content')
    {{-- Legend: same data, just pill style via gc-legend-list
    <ul class="gc-legend-list mt-2 px-1">
        <li style="background:#DBEAFE; color:#1E3A8A;">
            <span class="gc-legend-dot" style="background:#2563EB;"></span> Holiday
        </li>
        <li style="background:#DCFCE7; color:#14532D;">
            <span class="gc-legend-dot" style="background:#16A34A;"></span> Joining
        </li>
        <li style="background:#E9D5FF; color:#581C87;">
            <span class="gc-legend-dot" style="background:#9333EA;"></span> Interview
        </li>
        <li style="background:#FEE2E2; color:#7F1D1D;">
            <span class="gc-legend-dot" style="background:#DC2626;"></span> Leave
        </li>
        <li style="background:#FEF9C3; color:#854D0E;">
            <span class="gc-legend-dot" style="background:#EAB308;"></span> Seminar
        </li>
        <li style="background:#E0F2FE; color:#075985;">
            <span class="gc-legend-dot" style="background:#0284C7;"></span> Meeting
        </li>
        <li style="background:#DDD6FE; color:#4C1D95;">
            <span class="gc-legend-dot" style="background:#7C3AED;"></span> Increment
        </li>
        <li style="background:#F1F5F9; color:#334155;">
            <span class="gc-legend-dot" style="background:#64748B;"></span> Tours
        </li>
    </ul> --}}

    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="iq-card iq-card-block iq-card-stretch iq-card-height overflow-hidden">
                <div class="iq-card-body">
                    <div id="holiday-calendar"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail modal — exact same IDs, only inline style values refined --}}
    <div class="modal fade" id="holidayDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content"
                style="border:none; box-shadow:0 8px 30px rgba(60,64,67,.25); overflow:hidden; border-radius:12px;">

                <div class="d-flex justify-content-between align-items-center"
                    style="padding:14px 20px; background:#fff; border-bottom:1px solid #f1f3f4;">
                    <div>
                        <h6 id="modalTitleHeading" style="font-size:15px; font-weight:500; margin:0; color:#3c4043;"></h6>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div id="holidayDetailActions" class="d-flex gap-2"></div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            style="position:absolute; right:16px; top:50%; transform:translateY(-50%); z-index:2;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>

                <div class="modal-body p-0">
                    <div class="d-flex">
                        <div
                            style="background:#f8fafc; padding:28px 18px; text-align:center; border-right:1px solid #f1f3f4; min-width:96px;">
                            <div id="holidayMonth"
                                style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.8px;">
                            </div>
                            <div id="holidayDay"
                                style="font-size:36px; font-weight:300; color:#3c4043; line-height:1; margin:4px 0;"></div>
                            <div id="holidayYear" style="font-size:12px; font-weight:500; color:#70757a;"></div>
                            <div id="holidaytime" style="font-size:12px; margin-top:6px;"></div>
                        </div>

                        <div
                            style="padding:22px 24px; flex-grow:1; display:flex; flex-direction:column; justify-content:center; gap:14px;">

                            <div id="holidayNamediv">
                                <label id="holidaytitlelabel"
                                    style="display:block; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">
                                    Event Title
                                </label>
                                <div id="holidayName"
                                    style="font-size:17px; font-weight:500; color:#3c4043; line-height:1.3;"></div>
                            </div>

                            <div id="line" style="width:36px; height:2px; background:#f1f3f4; border-radius:2px;">
                            </div>

                            <div id="holidayDescriptiondiv">
                                <label id="holidaydescritptionlabel"
                                    style="display:block; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">
                                    Description
                                </label>
                                <div id="holidayDescription"
                                    style="font-size:14px; color:#3c4043; font-weight:400; line-height:1.5; max-height:140px; overflow-y:auto;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="modalBottomBar" style="height:5px;"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="holidayModal" tabindex="-1" role="dialog" aria-labelledby="holidayModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="holidayModalLabel">Add Holidays</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="holidayForm">
                        <input type="hidden" name="token" value="{{ session('api_token') }}">
                        <input type="hidden" name="user_id" value="{{ session('user_id') }}">
                        <input type="hidden" name="company_id" value="{{ session('company_id') }}">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="form-group">
                            <input type="hidden" name="event_type" value="holiday">
                            <input type="hidden" class="form-control" value="holiday" readonly>
                            <span class="error-msg" id="error-event_type" style="color: red"></span>
                        </div>

                        <div class="form-group" id="event_title_field">
                            <label id="event_title_label">Event Title</label><span style="color: red">*</span>
                            <input type="text" class="form-control" name="event_title" id="event_title"
                                placeholder="Enter Event Title">
                            <span class="error-msg" id="error-event_title" style="color: red"></span>
                        </div>

                        <div class="form-group" id="date_field">
                            <label id="date_label">Event Date</label><span style="color: red">*</span>
                            <input type="date" class="form-control" name="event_date" id="event_date">
                            <span class="error-msg" id="error-event_date" style="color: red"></span>
                        </div>

                        <div class="form-group" id="description_field">
                            <label>Description</label>
                            <textarea class="form-control" name="description" id="description" placeholder="Enter Event description"></textarea>
                            <span class="error-msg" id="error-description" style="color: red"></span>
                        </div>

                        <div class="form-group" id="employee_field">
                            <label>Employee Name</label>
                            <input type="text" class="form-control" name="employee_name" id="employee_name"
                                placeholder="Enter Employee Name">
                            <span class="error-msg" id="error-employee_name" style="color: red"></span>
                        </div>

                        <div class="form-group" id="candidate_field">
                            <label>Candidate Name</label>
                            <input type="text" class="form-control" name="candidate_name" id="candidate_name"
                                placeholder="Enter Candidate Name">
                            <span class="error-msg" id="error-candidate_name" style="color: red"></span>
                        </div>

                        <div class="form-group" id="place_field">
                            <label>Place Name</label>
                            <input type="text" class="form-control" name="place_name" id="place_name"
                                placeholder="Enter Place Name">
                            <span class="error-msg" id="error-place_name" style="color: red"></span>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="submit" form="holidayForm" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('ajax')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/list/index.global.min.js'></script>
    <script>
        $(document).ready(function() {


            function setModalTheme(type) {
                let theme = {};
                switch (type) {
                    case 'holiday':
                        theme = {
                            primary: '#2563EB',
                            light: '#DBEAFE'
                        };
                        break;
                    case 'joining':
                        theme = {
                            primary: '#16A34A',
                            light: '#DCFCE7'
                        };
                        break;
                    case 'interview':
                        theme = {
                            primary: '#9333EA',
                            light: '#E9D5FF'
                        };
                        break;
                    case 'leave':
                        theme = {
                            primary: '#DC2626',
                            light: '#FEE2E2'
                        };
                        break;
                    case 'seminar':
                        theme = {
                            primary: '#EAB308',
                            light: '#FEF9C3'
                        };
                        break;
                    case 'meeting':
                        theme = {
                            primary: '#0284C7',
                            light: '#E0F2FE'
                        };
                        break;
                    case 'increment':
                        theme = {
                            primary: '#7C3AED',
                            light: '#DDD6FE'
                        };
                        break;
                    default:
                        theme = {
                            primary: '#6366F1',
                            light: '#EEF2FF'
                        };
                }
                document.getElementById('modalTitleHeading').style.color = theme.primary;
                document.getElementById('holidayMonth').style.color = theme.primary;
                document.getElementById('holidaydescritptionlabel').style.color = theme.primary;
                document.getElementById('holidaytitlelabel').style.color = theme.primary;
                document.getElementById('modalBottomBar').style.background =
                    `linear-gradient(90deg, ${theme.primary} 0%, white 100%)`;
            }

            /* ── params — unchanged ── */
            function getParams() {
                return new URLSearchParams({
                    user_id: "{{ session()->get('user_id') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    token: "{{ session()->get('api_token') }}",
                    // filter_event: $('#filter_event').val(),
                    filter_date_from: $('#filter_date_from').val(),
                    filter_date_to: $('#filter_date_to').val(),
                });
            }

            /* ── hideAll — unchanged ── */
            function hideAll() {
                $('#employee_field').hide();
                $('#candidate_field').hide();
                $('#place_field').hide();
                $('#event_title_field').show();
                $('#date_field').show();
            }

            hideAll();

            $('#event_type').change(function() {

                hideAll();

                var type = $(this).val();

                if (!type) return;

                $('#date_field').show();


                if (type == 'meeting' || type == 'seminar' || type == 'interview') {
                    $('#event_date').attr('type', 'datetime-local');
                    $('#date_label').text('Event Date & Time');

                } else {
                    $('#event_date').attr('type', 'date');
                    $('#date_label').text('Event Date');
                }

                if (type == 'increment' || type == 'leave' || type == 'meeting') {
                    $('#employee_field').show();
                }

                if (type == 'holiday' || type == 'meeting' || type == 'seminar') {
                    $('#event_title_field').show();
                }

                if (type == 'joining' || type == 'interview') {
                    $('#candidate_field').show();
                }

                if (type == 'tours') {
                    $('#place_field').show();
                }

            });

            let user_id = "{{ session()->get('user_id') }}";
            const user_edit = "{{ session()->get('user_permissions.hrmodule.companiesholidays.edit') }}";
            const user_delete = "{{ session()->get('user_permissions.hrmodule.companiesholidays.delete') }}";

            /* ── holidayCalendar — added destroy fix ── */
            function holidayCalendar() {
                loadershow();
                if (window._calInstance) {
                    window._calInstance.refetchEvents();
                    return;
                }

                var calendarEl = document.getElementById('holiday-calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'listYear',
                    headerToolbar: {
                        left: 'today',
                        center: 'prev title next',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek,listYear'
                    },
                    views: {
                        listWeek: {
                            buttonText: 'List'
                        },
                        listYear: {
                            buttonText: 'Year',
                            duration: {
                                year: 1
                            }
                        }
                    },
                    dayMaxEvents: 3,
                    events: function(fetchInfo, successCallback, failureCallback) {
                        fetch("{{ route('holiday.index') }}?" + getParams().toString())
                            .then(response => response.json())
                            .then(data => {
                                if (data.status !== 200) {
                                    Toast.fire({
                                        icon: "error",
                                        title: data.message || 'Something went wrong!'
                                    });
                                    loaderhide();
                                    return;
                                }
                                const events = data.holiday.map(item => {
                                    let title = (item.event_type ?
                                        item.event_type.charAt(0).toUpperCase() + item
                                        .event_type.slice(1) :
                                        'Event');

                                    if (item.event_title) {
                                        title = title + "\n" + item.event_title;
                                    }
                                    if (item.event_type == "increment" || item.event_type ==
                                        "joining" ||
                                        item.event_type == "tours" || item.event_type ==
                                        "leave" ||
                                        item.event_type == "interview") {
                                        title = item.event_type;
                                    }
                                    if (item.event_date && ['meeting', 'seminar',
                                            'interview'
                                        ].includes(item.event_type)) {
                                        const dt = new Date(item.event_date);
                                        let hours = dt.getHours();
                                        const minutes = dt.getMinutes().toString().padStart(
                                            2, '0');
                                        const ampm = hours >= 12 ? 'PM' : 'AM';
                                        hours = hours % 12 || 12;
                                        title = `${hours}:${minutes} ${ampm} - ${title}`;
                                    }
                                    return {
                                        id: item.id,
                                        title: title,
                                        start: item.event_date,
                                        allDay: true,
                                        description: item.description,
                                        extendedProps: {
                                            title: item.event_title,
                                            created_by: item.created_by,
                                            event_type: item.event_type,
                                            employee_name: item.employee_name,
                                            candidate_name: item.candidate_name,
                                            place_name: item.place_name
                                        }
                                    };
                                });
                                successCallback(events);
                                loaderhide();
                            })
                            .catch(error => {
                                loaderhide();
                                failureCallback(error);
                                Toast.fire({
                                    icon: "error",
                                    title: error.message || 'Something went wrong!'
                                });
                            });
                    },
                    eventContent: function(arg) {

                        let type = arg.event.extendedProps.event_type || '';
                        let title = arg.event.extendedProps.title || '';

                        let typeText = type.charAt(0).toUpperCase() + type.slice(1);

                        return {
                            html: `
                            <div style="line-height:1.3; color:#000; font-family:inherit;">
                                <div style="font-weight:600; font-size:12px;">
                                    ${typeText}
                                </div>

                                <hr style="margin:2px 0; border:none; border-top:1px solid rgba(0,0,0,0.2);">

                                <div style="font-size:11px; font-weight:500;">
                                    ${title}
                                </div>
                            </div>
                        `
                        };
                    },
                    /* ── eventClick — unchanged ── */
                    eventClick: function(info) {
                        const event = info.event;
                        const props = event.extendedProps;
                        const typeTitles = {
                            holiday: 'Holiday Details',
                            meeting: 'Meeting Details',
                            seminar: 'Seminar Details',
                            increment: 'Increment Details',
                            leave: 'Leave Details',
                            joining: 'Joining Details',
                            interview: 'Interview Details',
                            tours: 'Tour Details'
                        };
                        const eventDate = new Date(event.start);
                        const month = eventDate.toLocaleDateString('en-US', {
                            month: 'short'
                        });
                        const dt = new Date(eventDate);
                        const day = dt.getDate();
                        const year = dt.getFullYear();
                        let hours = dt.getHours();
                        const minutes = dt.getMinutes().toString().padStart(2, '0');
                        const ampm = hours >= 12 ? 'PM' : 'AM';
                        hours = hours % 12 || 12;
                        const timeString = `<b>${hours}:${minutes} ${ampm}</b>`;
                        const modalTitle = typeTitles[props.event_type] || 'Event Details';
                        $('#holidayDetailModal h6').html(
                            `<i class="ri-information-line" style="margin-right:5px;"></i> ${modalTitle}`
                        );
                        $('#holidayMonth').text(month);
                        $('#holidayDay').text(day);
                        $('#holidayYear').text(year);
                        if (['meeting', 'seminar', 'interview'].includes(props.event_type)) {
                            $('#holidaytime').html(timeString);
                        } else {
                            $('#holidaytime').text('');
                        }
                        if (props.title && props.title.trim() !== '') {
                            $('#holidayName').text(props.title);
                            $('#holidayName').parent().show();
                            $('#line').show();
                        } else {
                            $('#holidayName').text('');
                            $('#holidayName').parent().hide();
                            $('#line').hide();
                        }
                        let descriptionHTML = props.description ?
                            `<p>${props.description}</p>` :
                            '<span style="color:#70757a; font-style:italic;">No additional details provided for this event.</span>';
                        if (['meeting', 'seminar', 'increment', 'leave'].includes(props.event_type)) {
                            if (props.employee_name) descriptionHTML +=
                                `<p><strong>Employee:</strong> ${props.employee_name}</p>`;
                            if (props.employee_id) descriptionHTML +=
                                `<p><strong>Employee ID:</strong> ${props.employee_id}</p>`;
                        }
                        if (['joining', 'interview'].includes(props.event_type)) {
                            if (props.candidate_name) descriptionHTML +=
                                `<p><strong>Candidate:</strong> ${props.candidate_name}</p>`;
                        }
                        if (props.event_type === 'tours') {
                            if (props.place_name) descriptionHTML +=
                                `<p><strong>Place:</strong> ${props.place_name}</p>`;
                        }
                        $('#holidayDescription').html(descriptionHTML);
                        let actionsHTML = '';
                        if (user_edit == 1 || user_id == 1) {
                            actionsHTML += `<button type="button" class="btn edit-holiday" data-id="${event.id}"
                                style="border:none; background:#e8f0fe; margin-right:4px; color:#1a73e8;
                                       border-radius:20%; width:32px; height:32px; display:flex;
                                       align-items:center; justify-content:center; transition:0.2s;">
                                <i class="ri-edit-2-line" style="font-size:15px;"></i>
                            </button>`;
                        }
                        if (user_delete == 1 || user_id == 1) {
                            actionsHTML += `<button type="button" class="btn delete-holiday" data-id="${event.id}"
                                style="border:none; background:#fce8e6; margin-right:4px; color:#d93025;
                                       border-radius:20%; width:32px; height:32px; display:flex;
                                       align-items:center; justify-content:center; transition:0.2s;">
                                <i class="ri-delete-bin-6-line" style="font-size:15px;"></i>
                            </button>`;
                        }
                        $('#holidayDetailActions').html(actionsHTML);
                        setModalTheme(info.event.extendedProps.event_type);
                        $('#holidayDetailModal').modal('show');
                    },

                    /* ── eventDidMount — unchanged ── */
                    eventDidMount: function(info) {
                        let bg = '',
                            border = '',
                            text = '';
                        switch (info.event.extendedProps.event_type) {
                            case 'holiday':
                                bg = '#DBEAFE';
                                border = '#2563EB';
                                text = '#1E3A8A';
                                break;
                            case 'joining':
                                bg = '#DCFCE7';
                                border = '#16A34A';
                                text = '#14532D';
                                break;
                            case 'interview':
                                bg = '#E9D5FF';
                                border = '#9333EA';
                                text = '#581C87';
                                break;
                            case 'leave':
                                bg = '#FEE2E2';
                                border = '#DC2626';
                                text = '#7F1D1D';
                                break;
                            case 'seminar':
                                bg = '#FEF9C3';
                                border = '#EAB308';
                                text = '#854D0E';
                                break;
                            case 'meeting':
                                bg = '#E0F2FE';
                                border = '#0284C7';
                                text = '#075985';
                                break;
                            case 'increment':
                                bg = '#DDD6FE';
                                border = '#7C3AED';
                                text = '#4C1D95';
                                break;
                            default:
                                bg = '#F1F5F9';
                                border = '#64748B';
                                text = '#1E293B';
                        }
                        if (info.view.type.includes('list')) {
                            info.el.style.backgroundColor = bg;
                            info.el.style.borderLeft = `4px solid ${border}`;
                            info.el.style.color = '#000';
                            const dot = info.el.querySelector('.fc-list-event-dot');
                            if (dot) dot.style.borderColor = border;
                        } else {
                            info.el.style.backgroundColor = bg;
                            info.el.style.borderColor = border;
                            info.el.style.borderLeft = `3px solid ${border}`;
                            info.el.style.color = '#000';
                            info.el.style.borderRadius = '4px';
                            info.el.style.padding = '2px 6px';
                        }
                        info.el.style.fontWeight = '500';
                        info.el.style.fontSize = '12px';
                    }
                });

                window._calInstance = calendar;
                calendar.render();
            }

            holidayCalendar();

            /* ── reset modal — unchanged ── */
            $('#holidayModal').on('hidden.bs.modal', function(event) {
                $("#holidayModalLabel").text('Add New Event');
                $("#holidayForm")[0].reset();
                $("#holidayForm .error-msg").text("");
            });

            /* ── edit — unchanged ── */
            $(document).on("click", ".edit-holiday", function() {
                const id = $(this).data("id");
                const url = "{{ route('holiday.edit', ['id' => '__id__']) }}".replace('__id__', id);
                $.ajax({
                    url,
                    type: "GET",
                    data: {
                        user_id: "{{ session()->get('user_id') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        token: "{{ session()->get('api_token') }}"
                    },
                    success: function(response) {
                        loaderhide();
                        if (response.status !== 200) {
                            Toast.fire({
                                icon: "error",
                                title: response.message ||
                                    "Failed to fetch holiday data"
                            });
                            return;
                        }
                        const holiday = response.data;
                        $("#holidayModalLabel").text("Edit Event");
                        $("#edit_id").val(holiday.id);
                        $("#event_type").val(holiday.event_type).trigger("change");
                        $("#event_title").val(holiday.event_title || "");
                        $("#employee_name").val(holiday.employee_name || "");
                        $("#candidate_name").val(holiday.candidate_name || "");
                        $("#place_name").val(holiday.place_name || "");
                        $("#description").val(holiday.description || "");
                        const input = $("#event_date");
                        if (["meeting", "seminar", "interview"].includes(holiday.event_type)) {
                            input.attr("type", "datetime-local");
                            $("#date_label").text("Event Date & Time");
                        } else {
                            input.attr("type", "date");
                            $("#date_label").text("Event Date");
                        }
                        if (holiday.event_date) {
                            const dt = new Date(holiday.event_date);
                            const yyyy = dt.getFullYear();
                            const mm = (dt.getMonth() + 1).toString().padStart(2, "0");
                            const dd = dt.getDate().toString().padStart(2, "0");
                            const hh = dt.getHours().toString().padStart(2, "0");
                            const min = dt.getMinutes().toString().padStart(2, "0");
                            input.val(input.attr("type") === "datetime-local" ?
                                `${yyyy}-${mm}-${dd}T${hh}:${min}` :
                                `${yyyy}-${mm}-${dd}`);
                        } else {
                            input.val("");
                        }
                        $("#employee_field, #candidate_field, #event_title_field, #place_field")
                            .hide();
                        if (["increment", "leave", "meeting"].includes(holiday.event_type)) $(
                            "#employee_field").show();
                        if (["holiday", "meeting", "seminar"].includes(holiday.event_type)) $(
                            "#event_title_field").show();
                        if (["joining", "interview"].includes(holiday.event_type)) $(
                            "#candidate_field").show();
                        if (holiday.event_type === "tours") $("#place_field").show();
                        $("#holidayModal").modal("show");
                        $("#holidayDetailModal").modal("hide");
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        Toast.fire({
                            icon: "error",
                            title: "Failed to fetch holiday data"
                        });
                    }
                });
            });

            /* ── delete — unchanged ── */
            $(document).on("click", ".delete-holiday", function() {
                $("#holidayDetailModal").modal("hide");
                const id = $(this).data("id");
                const url = "{{ route('holiday.delete', ['id' => '__id__']) }}".replace('__id__', id);
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently delete the holiday!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d93025',
                    cancelButtonColor: '#1a73e8',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url,
                            type: "put",
                            data: {
                                user_id: "{{ session()->get('user_id') }}",
                                company_id: "{{ session()->get('company_id') }}",
                                token: "{{ session()->get('api_token') }}"
                            },
                            success: function(response) {
                                if (response.status == 200) {
                                    Toast.fire({
                                        icon: "success",
                                        title: response.message
                                    });
                                    holidayCalendar();
                                } else {
                                    Toast.fire({
                                        icon: "error",
                                        title: response.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                console.log(xhr.responseText);
                                Toast.fire({
                                    icon: "error",
                                    title: "Failed to delete holiday"
                                });
                            }
                        });
                        Swal.close();
                    }
                });
            });

            /* ── form submit — unchanged ── */
            $("#holidayForm").on("submit", function(e) {
                e.preventDefault();
                loadershow();
                $('.error-msg').text('');
                let url = "{{ route('holiday.store') }}";
                let type = 'POST';
                const holidayId = $("#holidayForm #edit_id").val();
                if (holidayId) {
                    url = "{{ route('holiday.update', ['id' => '__id__']) }}".replace('__id__', holidayId);
                    type = 'PUT';
                }
                $.ajax({
                    url,
                    type,
                    data: $('#holidayForm').serialize(),
                    success: function(response) {
                        loaderhide();
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });
                            $("#holidayModal").modal("hide");
                            holidayCalendar();
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        loaderhide();
                        console.log(xhr.responseText);
                        handleAjaxError(xhr);
                    }
                });
            });
            $('#applyfilters').on('click', function() {

                console.log(getParams().toString()); // debug

                if (window._calInstance) {
                    window._calInstance.refetchEvents();
                }

                hideOffCanvass();
            });


            //remove filters
            $('#removefilters').on('click', function() {

                // $('#filter_event').val('').trigger('change');
                $('#filter_date_to').val('');
                $('#filter_date_from').val('');

                if (window._calInstance) {
                    window._calInstance.refetchEvents();
                }

                hideOffCanvass();
            });

        });
    </script>
@endpush
