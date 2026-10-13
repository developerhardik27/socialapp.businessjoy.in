@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Update Donor
@endsection
@section('title')
    Update Donor
@endsection

@section('form-content')
    <form id="donorupdateform">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
                <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
                <input type="hidden" name="token" value="{{ session('api_token') }}" />
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="familyPersonId">Family Person:</label>
                    <select name="familyPersonId" class="form-control" id="familyPersonId">
                        <option value="" selected>Select Family Person (Optional)</option>
                    </select>
                    <span class="error-msg" id="error-familyPersonId" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="donation_type_id">Donation Type:</label><span
                        style="color:red;"> *</span>
                    <select name="donation_type_id" class="form-control" id="donation_type_id" required>
                        <option value="" disabled selected>Select Donation Type</option>
                    </select>
                    <span class="error-msg" id="error-donation_type_id" style="color: red"></span>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="first_name">First Name:</label><span style="color:red;">
                        *</span>
                    <input type="text" class="form-control" name="first_name" id="first_name"
                        placeholder="First Name" required />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="last_name">Last Name:</label><span style="color:red;">
                        *</span>
                    <input type="text" class="form-control" name="last_name" id="last_name"
                        placeholder="Last Name" required />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="surname">Surname:</label>
                    <input type="text" class="form-control" name="surname" id="surname"
                        placeholder="Surname" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="email">Email:</label><span style="color:red;"> *</span>
                    <input type="email" class="form-control" name="email" id="email" placeholder="Email"
                        required />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="mobile">Mobile:</label><span style="color:red;"> *</span>
                    <input type="text" class="form-control" name="mobile" id="mobile" maxlength="15"
                        onkeyup="this.value=this.value.replace(/[^+\d]/g,'')" placeholder="Mobile Number"
                        required />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="donate_date">Donate Date:</label><span
                        style="color:red;"> *</span>
                    <input type="date" class="form-control" name="donate_date" id="donate_date" required />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label" for="amount">Amount:</label><span style="color:red;"> *</span>
                    <input type="number" class="form-control" name="amount" id="amount" min="0"
                        step="0.01" placeholder="Amount" required />
                </div>
                <div class="col-sm-12">
                    <button type="reset" id="resetbtn" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Cancel" class="btn iq-bg-danger float-right">Cancel</button>
                    <button type="submit" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Update" class="btn btn-primary float-right my-0">Save</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('ajax')
    <script>
        $('document').ready(function() {
            loaderhide();
            var edit_id = @json($edit_id);

            // redirect on donor page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societydonor') }}";
            });

            // load persons + donation types
            function loadpersons() {
                return $.ajax({
                    type: 'GET',
                    url: "{{ route('persons') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.data) {
                            $.each(response.data, function(key, person) {
                                $('#familyPersonId').append(
                                    `<option value="${person.id}">${person.full_name || person.first_name || ''} (${person.id})</option>`
                                );
                            });
                        }
                    }
                });
            }

            function loadDonationTypes() {
                return $.ajax({
                    type: 'GET',
                    url: "{{ route('donationTypes') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.data) {
                            $.each(response.data, function(key, dtype) {
                                $('#donation_type_id').append(
                                    `<option value="${dtype.id}">${dtype.name}</option>`);
                            });
                        }
                    }
                });
            }

            // load old data
            function loaddata() {
                loadershow();
                let donorEditUrl = "{{ route('donor.edit', '__editId__') }}".replace('__editId__',
                    edit_id);
                $.ajax({
                    type: 'GET',
                    url: donorEditUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.data) {
                            var data = response.data;
                            $('#familyPersonId').val(data.familyPersonId);
                            $('#donation_type_id').val(data.donation_type_id);
                            $('#first_name').val(data.first_name);
                            $('#last_name').val(data.last_name);
                            $('#surname').val(data.surname);
                            $('#email').val(data.email);
                            $('#mobile').val(data.mobile);
                            $('#donate_date').val(data.donate_date ? String(data.donate_date).split(
                                'T')[0] : '');
                            $('#amount').val(data.amount);
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message || 'Something went wrong!'
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

            loadershow();
            $.when(loadpersons(), loadDonationTypes()).always(function() {
                loaddata();
            });

            //submit form
            $('#donorupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                let donorUpdateUrl = "{{ route('donor.update', '__editId__') }}".replace('__editId__',
                    edit_id);
                ajaxRequest('PUT', donorUpdateUrl, $(this).serialize())
                    .done(function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Donor updated successfully!'
                            });
                            window.location = "{{ route('admin.societydonor') }}";
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
