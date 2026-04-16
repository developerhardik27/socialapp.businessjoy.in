@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterpage')

@section('page_title')
    {{ config('app.name') }} - Account Other Settings
@endsection
@section('title')
    Other settings
@endsection

@section('page-content')
    <div id="content-page" class="content-page">
        <div class="container-fluid">
            <div class="row">
                {{-- @php
                    dd(session('user_permissions.accountmodule.accountformsetting.edit'));
                @endphp --}}
                @if (session('user_permissions.accountmodule.accountformsetting.edit') == 1)
                    <div class="col-md-6">
                        <button data-toggle="tooltip" data-placement="bottom" data-original-title="Form Settings" type="button"
                            id="editcustomerformsettingsBtn"
                            class="float-right m-4 btn btn-outline-success btn-rounded btn-sm my-0">
                            <i class="ri-edit-fill"></i>
                        </button>
                        <div class="iq-card">
                            <div class="iq-card-header d-flex justify-content-between">
                                <div class="iq-header-title">
                                    <h4 class="card-title">Form Settings</h4>
                                </div>
                            </div>
                            <div class="iq-card-body">
                                <form id="customerformsettingform" class="d-none">
                                    @csrf
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12 mb-2">
                                                <input type="hidden" name="edit_id" class="form-control" id="edit_id"
                                                    required />
                                                <input type="hidden" name="token" class="form-control"
                                                    value="{{ session('api_token') }}" required />
                                                <input type="hidden" value="{{ $user_id }}" name="user_id"
                                                    class="form-control">
                                                <input type="hidden" value="{{ $company_id }}" name="company_id"
                                                    class="form-control">

                                                <label for="income_customer_dropdown"><b>Income (Paid By) Customer
                                                        Drop Down</b><span style="color: red">*</span></label>
                                                <div class="checkbox-group">
                                                    <label><input type="checkbox" name="income_customer_dropdown[]"
                                                            value="invoice"> Invoice</label>
                                                    <label><input type="checkbox" name="income_customer_dropdown[]"
                                                            value="account"> Account</label>
                                                    <label><input type="checkbox" name="income_customer_dropdown[]"
                                                            value="quotation"> Quotation</label>
                                                    <label><input type="checkbox" name="income_customer_dropdown[]"
                                                            value="consignor"> Consignors</label>
                                                    <label><input type="checkbox" name="income_customer_dropdown[]"
                                                            value="consignee"> Consignees</label>
                                                </div>
                                                <span class="error-msg" id="error-income_customer_dropdown"
                                                    style="color: red"></span>
                                                <label for="expense_customer_dropdown"><b>Expense (Paid To)Customer Drop
                                                        Down</b><span style="color: red">*</span></label>
                                                <div class="checkbox-group">
                                                    <label><input type="checkbox" name="expense_customer_dropdown[]"
                                                            value="invoice"> Invoice</label>
                                                    <label><input type="checkbox" name="expense_customer_dropdown[]"
                                                            value="account"> Account</label>
                                                    <label><input type="checkbox" name="expense_customer_dropdown[]"
                                                            value="quotation"> Quotation</label>
                                                    <label><input type="checkbox" name="expense_customer_dropdown[]"
                                                            value="consignor"> Consignors</label>
                                                    <label><input type="checkbox" name="expense_customer_dropdown[]"
                                                            value="consignee"> Consignees</label>
                                                </div>
                                                <span class="error-msg" id="error-expense_customer_dropdown"
                                                    style="color: red"></span>
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div class="col-sm-12 mt-2">
                                                <button type="btn" id="cancelcustomerformsettingBtn"
                                                    class="btn btn-secondary float-right">Cancel</button>
                                                <button type="reset"
                                                    class="btn iq-bg-danger float-right mr-2">Reset</button>
                                                <button type="submit"
                                                    class="btn btn-primary float-right my-0">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                Income (Paid By) Customer Drop Down : <span id="incomecustomerdropdown"></span> <br>
                                Expense (Paid To) Customer Drop Down : <span id="expensecustomerdropdown"></span> <br>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@push('ajax')
    <script>
        $('document').ready(function() {

            loaderhide();

            // ── Fetch & populate form settings data ──────────────────────────────
            function getoverduedays() {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('accountothersettings.index') }}",
                    data: {
                        user_id: "{{ session()->get('user_id') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        token: "{{ session()->get('api_token') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.settings != '') {
                            var data = response.settings[0];

                            var income_customer_dropdown = data['income_customer_dropdown'];
                            var expense_customer_dropdown = data['expense_customer_dropdown'];


                            let income_customer_dropdown_list = JSON.parse(income_customer_dropdown);
                            let expense_customer_dropdown_list = JSON.parse(expense_customer_dropdown);


                            // Tick saved checkboxes – Customer Drop Down
                            document.querySelectorAll('input[name="income_customer_dropdown[]"]').forEach((
                                checkbox) => {
                                $.each(income_customer_dropdown_list, function(index, value) {
                                    if (value === checkbox.value) {
                                        checkbox.checked = true;
                                    }
                                });
                            });
                            // Display current values
                            $('#incomecustomerdropdown').html(`<b>${income_customer_dropdown_list.join(', ')}</b>`);

                             document.querySelectorAll('input[name="expense_customer_dropdown[]"]').forEach((
                                checkbox) => {
                                $.each(expense_customer_dropdown_list, function(index, value) {
                                    if (value === checkbox.value) {
                                        checkbox.checked = true;
                                    }
                                });
                            });
                            // Display current values
                            $('#expensecustomerdropdown').html(`<b>${expense_customer_dropdown_list.join(', ')}</b>`);

                        } else if (response.status == 500) {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        } else {
                            $('#incomecustomerdropdown').html(`<b>Not set</b>`);
                            $('#expensecustomerdropdown').html(`<b>Not set</b>`);

                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        console.log(xhr.responseText);
                        handleAjaxError(xhr);
                    }
                });
            }

            getoverduedays();

            // ── Show form ─────────────────────────────────────────────────────────
            $('#editcustomerformsettingsBtn').on('click', function(e) {
                e.preventDefault();
                getoverduedays();
                $('#customerformsettingform').removeClass('d-none');
                $('#editcustomerformsettingsBtn').addClass('d-none');
            });

            // ── Hide form ─────────────────────────────────────────────────────────
            $('#cancelcustomerformsettingBtn').on('click', function(e) {
                e.preventDefault();
                $('#customerformsettingform').addClass('d-none');
                $('#editcustomerformsettingsBtn').removeClass('d-none');
            });

            // ── Submit form ───────────────────────────────────────────────────────
            $('#customerformsettingform').on('submit', function(e) {
                e.preventDefault();
                loadershow();
                const formdata = $(this).serialize();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('accountcustomerdropdown.store') }}",
                    data: formdata,
                    success: function(response) {
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });
                            $('#customerformsettingform')[0].reset();
                            $('#customerformsettingform').addClass('d-none');
                            $('#editcustomerformsettingsBtn').removeClass('d-none');
                            getoverduedays();
                        } else if (response.status == 500) {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: "Something went wrong!"
                            });
                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        console.log(xhr.responseText);
                        handleAjaxError(xhr);
                    }
                });
            });

        });
    </script>
@endpush
