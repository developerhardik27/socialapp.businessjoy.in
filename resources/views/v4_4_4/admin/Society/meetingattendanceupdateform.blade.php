@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Update Meeting Attendance
@endsection
@section('title')
    Update Meeting Attendance
@endsection

@section('style')
    <style>
        .attendance-row {
            border: 1px solid #d7dce5;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 8px;
            background: #fafbff;
        }
    </style>
@endsection

@section('form-content')
    <form id="attendanceupdateform">
        @csrf
        <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
        <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
        <input type="hidden" name="token" value="{{ session('api_token') }}" />
        <input type="hidden" name="karobari_meeting_id" value="{{ $edit_id }}" />

        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-6 mb-3">
                    <label class="form-label">Meeting:</label>
                    <input type="text" class="form-control" id="meeting_name_display" readonly />
                </div>
            </div>
        </div>

        <div id="membersContainer">
            <p class="text-muted">Loading karobari members...</p>
        </div>

        <div class="form-row mt-3">
            <div class="col-sm-12">
                <button type="reset" id="resetbtn" data-toggle="tooltip" data-placement="bottom"
                    data-original-title="Cancel" class="btn iq-bg-danger float-right">Cancel</button>
                <button type="submit" data-toggle="tooltip" data-placement="bottom"
                    data-original-title="Update" class="btn btn-primary float-right my-0">Save</button>
            </div>
        </div>
    </form>
@endsection

@push('ajax')
    <script>
        $('document').ready(function() {
            loaderhide();
            var edit_id = @json($edit_id);
            var savedStatus = {};

            // redirect on attendance page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societymeetingattendance') }}";
            });

            // load existing attendance for this meeting
            function loadexisting() {
                return $.ajax({
                    type: 'GET',
                    url: "{{ route('karobarimeeting.attendance.edit', '__editId__') }}".replace('__editId__',
                        edit_id),
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.attendance && response.attendance
                            .length) {
                            var record = response.attendance[0];
                            if (record.meeting_name) {
                                $('#meeting_name_display').val(record.meeting_name);
                            }
                            if (Array.isArray(record.karobari_member_id)) {
                                $.each(record.karobari_member_id, function(k, m) {
                                    savedStatus[m.id] = m.status;
                                });
                            }
                        }
                    }
                });
            }

            // load meeting name
            function loadMeetingName() {
                return $.ajax({
                    type: 'GET',
                    url: "{{ route('karobarimeeting.edit', '__editId__') }}".replace('__editId__',
                        edit_id),
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.meeting) {
                            $('#meeting_name_display').val(response.meeting.meeting_name + ' (' +
                                (response.meeting.meeting_date || '') + ')');
                        }
                    }
                });
            }

            // load karobari members and render rows
            function loadmembers() {
                loadershow();
                $.ajax({
                    type: 'GET',
                    url: "{{ route('karobarimeeting.loadkarobarimember') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        $('#membersContainer').empty();
                        if (response.status == 200 && response.karobari_members) {
                            $.each(response.karobari_members, function(key, member) {
                                var saved = savedStatus[member.id] !== undefined ? savedStatus[
                                    member.id] : 1;
                                $('#membersContainer').append(`
                                    <div class="attendance-row d-flex justify-content-between align-items-center">
                                        <div>
                                            <b>${member.full_name || '-'}</b>
                                            <small class="text-muted ml-2">${member.email || ''}${member.lifetime_member_no ? ' | Member No: ' + member.lifetime_member_no : ''}</small>
                                        </div>
                                        <div>
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" class="custom-control-input" id="present_${member.id}" name="karobari_member_ids[${member.id}]" value="1" ${saved == 1 ? 'checked' : ''}>
                                                <label class="custom-control-label" for="present_${member.id}">Present</label>
                                            </div>
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" class="custom-control-input" id="absent_${member.id}" name="karobari_member_ids[${member.id}]" value="0" ${saved == 0 ? 'checked' : ''}>
                                                <label class="custom-control-label" for="absent_${member.id}">Absent</label>
                                            </div>
                                        </div>
                                    </div>
                                `);
                            });
                        } else {
                            $('#membersContainer').html(
                                '<p class="text-muted">No karobari members found.</p>');
                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    }
                });
            }

            loadershow();
            $.when(loadexisting(), loadMeetingName()).always(function() {
                loadmembers();
            });

            //submit form
            $('#attendanceupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                let attendanceUpdateUrl = "{{ route('karobarimeeting.attendance.update', '__editId__') }}"
                    .replace('__editId__', edit_id);
                ajaxRequest('PUT', attendanceUpdateUrl, $(this).serialize())
                    .done(function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Attendance updated successfully!'
                            });
                            window.location = "{{ route('admin.societymeetingattendance') }}";
                        } else {
                            loaderhide();
                            Toast.fire({
                                icon: "error",
                                title: response.message || 'Something went wrong!'
                            });
                        }
                    })
                    .fail(function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    });
            });
        });
    </script>
@endpush
