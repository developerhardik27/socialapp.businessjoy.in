@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Take Meeting Attendance
@endsection
@section('title')
    Take Meeting Attendance
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
    <form id="attendanceform">
        @csrf
        <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
        <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
        <input type="hidden" name="token" value="{{ session('api_token') }}" />

        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-6 mb-3">
                    <label class="form-label" for="karobari_meeting_id">Select Meeting:</label><span
                        style="color:red;"> *</span>
                    <select name="karobari_meeting_id" class="form-control" id="karobari_meeting_id" required>
                        <option value="" disabled selected>Select Meeting</option>
                    </select>
                    <span class="error-msg" id="error-karobari_meeting_id" style="color: red"></span>
                </div>
            </div>
        </div>

        <div id="membersContainer">
            <p class="text-muted">Select a meeting to load karobari members.</p>
        </div>

        <div class="form-row mt-3">
            <div class="col-sm-12">
                <button type="reset" id="resetbtn" data-toggle="tooltip" data-placement="bottom"
                    data-original-title="Cancel" class="btn iq-bg-danger float-right">Cancel</button>
                <button type="submit" data-toggle="tooltip" data-placement="bottom" data-original-title="Save"
                    class="btn btn-primary float-right my-0">Save</button>
            </div>
        </div>
    </form>
@endsection

@push('ajax')
    <script>
        $('document').ready(function() {
            loaderhide();
            // redirect on attendance page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societymeetingattendance') }}";
            });

            // load meetings dropdown
            function loadmeetings() {
                loadershow();
                $.ajax({
                    type: 'GET',
                    url: "{{ route('karobarimeeting.loadmettings') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.meetings) {
                            $.each(response.meetings, function(key, meeting) {
                                $('#karobari_meeting_id').append(
                                    `<option value="${meeting.id}">${meeting.meeting_name} (${meeting.meeting_date})</option>`
                                );
                            });
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message || 'No meetings found'
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
            loadmeetings();

            // load karobari members when meeting selected
            $('#karobari_meeting_id').on('change', function() {
                var meetingId = $(this).val();
                if (!meetingId) {
                    return;
                }
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
                                $('#membersContainer').append(`
                                    <div class="attendance-row d-flex justify-content-between align-items-center">
                                        <div>
                                            <b>${member.full_name || '-'}</b>
                                            <small class="text-muted ml-2">${member.email || ''}${member.lifetime_member_no ? ' | Member No: ' + member.lifetime_member_no : ''}</small>
                                        </div>
                                        <div>
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" class="custom-control-input" id="present_${member.id}" name="karobari_member_ids[${member.id}]" value="1" checked>
                                                <label class="custom-control-label" for="present_${member.id}">Present</label>
                                            </div>
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" class="custom-control-input" id="absent_${member.id}" name="karobari_member_ids[${member.id}]" value="0">
                                                <label class="custom-control-label" for="absent_${member.id}">Absent</label>
                                            </div>
                                        </div>
                                    </div>
                                `);
                            });
                        } else {
                            $('#membersContainer').html(
                                '<p class="text-muted">No karobari members found.</p>');
                            Toast.fire({
                                icon: "error",
                                title: response.message || 'No karobari members found'
                            });
                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    }
                });
            });

            //submit form
            $('#attendanceform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                ajaxRequest('POST', "{{ route('karobarimeeting.attendance.store') }}", $(this).serialize())
                    .done(function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Attendance recorded successfully!'
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
