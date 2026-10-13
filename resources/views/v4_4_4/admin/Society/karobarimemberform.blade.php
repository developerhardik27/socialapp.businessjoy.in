@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Manage Karobari Members
@endsection
@section('title')
    Manage Karobari Members
@endsection

@section('style')
    <style>
        .kmember-row {
            border: 1px solid #d7dce5;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 8px;
            background: #fafbff;
        }
    </style>
@endsection

@section('form-content')
    <form id="karobarimemberform">
        @csrf
        <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
        <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
        <input type="hidden" name="token" value="{{ session('api_token') }}" />

        <p class="text-muted mb-2">Select members to add as Karobari Members. Unchecked members will be
            removed from Karobari.</p>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="selectall">
                <label class="custom-control-label" for="selectall">Select All</label>
            </div>
            <input type="text" id="membersearch" class="form-control w-50" placeholder="Search member..." />
        </div>

        <div id="membersContainer"></div>

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
            // redirect on karobari member page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societykarobarimember') }}";
            });

            // load members with is_karobari flag
            function loadmembers() {
                loadershow();
                $.ajax({
                    type: 'GET',
                    url: "{{ route('karobarimember.loadmemberforkarobari') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.members) {
                            $('#membersContainer').empty();
                            $.each(response.members, function(key, member) {
                                var checked = member.is_karobari == 1 ? 'checked' : '';
                                $('#membersContainer').append(`
                                    <div class="kmember-row member-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input member-check" id="member_${member.id}" name="family_person_ids[]" value="${member.id}" ${checked}>
                                            <label class="custom-control-label" for="member_${member.id}">
                                                <b>${member.full_name || (member.first_name + ' ' + (member.surname || ''))}</b>
                                                <small class="text-muted ml-2">${member.email || ''} ${member.mobile ? ' | ' + member.mobile : ''}</small>
                                                ${member.lifetime_member_no ? ' | Member No: ' + member.lifetime_member_no : ''}
                                            </label>
                                        </div>
                                    </div>
                                `);
                            });
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message || 'No members found'
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
            loadmembers();

            // select all
            $('#selectall').on('change', function() {
                $('.member-check').prop('checked', $(this).is(':checked'));
            });

            // search filter
            $('#membersearch').on('keyup', function() {
                var search = $(this).val().toLowerCase();
                $('.member-item').each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(search) !== -1);
                });
            });

            //submit form
            $('#karobarimemberform').submit(function(event) {
                event.preventDefault();
                loadershow();
                ajaxRequest('POST', "{{ route('karobarimember.store') }}", $(this).serialize())
                    .done(function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Karobari members updated successfully!'
                            });
                            window.location = "{{ route('admin.societykarobarimember') }}";
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
