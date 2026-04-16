@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')

@section('page_title')
    {{ config('app.name') }} - Add New Package
@endsection
@section('title')
    New Package
@endsection


@section('form-content')
    <form id="packageform" name="packageform">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-6 mb-2">
                    <input type="hidden" name="user_id" class="form-control" value="{{ session('user_id') }}"
                        placeholder="user_id" required />
                    <input type="hidden" name="token" class="form-control" value="{{ session('api_token') }}"
                        placeholder="token" required />
                    <input type="hidden" name="company_id" class="form-control" value="{{ session('company_id') }}"
                        placeholder="company_id" required />
                    <label for="name">Package Name</label><span style="color:red;">*</span>
                    <input id="name" type="text" name="name" class="form-control" placeholder="Package Name"
                        required />
                    <span class="error-msg" id="error-name" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="type">Package Type</label><span style="color:red;">*</span>
                    <select name="type" class="form-control" id="type" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="monthly">Monthly(30 Days)</option>
                        <option value="quarterly">Quarterly(90 Days)</option>
                        <option value="yearly">Yearly(365 Days)</option>
                    </select>
                    <span class="error-msg" id="error-type" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="price">Price</label><span style="color:red;">*</span>
                    <input type="number" step="0.01" name="price" class="form-control" id="price" value=""
                        placeholder="Price" required />
                    <span class="error-msg" id="error-price" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="currency">Currency</label>
                    <select name="currency" class="form-control select2" id="currency">
                        <option value="">Select Currency</option>
                    </select>
                    <span class="error-msg" id="error-currency" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="trial_days">Trial Days</label>
                    <input type="number" id="trial_days" name="trial_days" class="form-control" placeholder="Trial Days"
                        value="0" min="0"/>
                    <span class="error-msg" id="error-trial_days" style="color: red"></span>
                </div>
                <div class="col-sm-12 mb-2">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" placeholder="Description"></textarea>
                    <span class="error-msg" id="error-description" style="color: red"></span>
                </div>
                <div class="col-sm-12">
                    <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Cancel"
                        id="cancelbtn" class="btn btn-secondary float-right">Cancel</button>
                    <button type="reset" data-toggle="tooltip" data-placement="bottom" data-original-title="Reset Details"
                        class="btn iq-bg-danger float-right mr-2">Reset</button>
                    <button type="submit" data-toggle="tooltip" data-placement="bottom" data-original-title="Save Package"
                        class="btn btn-primary float-right my-0">Save</button>
                </div>
            </div>
        </div>
    </form>
@endsection


@push('ajax')
    <script>
        $('document').ready(function() {

            // companyId and userId both are required in every ajax request for all action *************
            // response status == 200 that means response succesfully recieved
            // response status == 500 that means database not found
            // response status == 422 that means api has not got valid or required data
            loaderhide();

            // Initialize Select2
            $('#currency').select2({
                placeholder: 'Search and select currency',
                allowClear: true,
                width: '100%'
            });

            // Load currencies
            loadCurrencies();

            function loadCurrencies() {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('country.index') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200) {
                            let currencySelect = $('#currency');
                            currencySelect.html('<option value="">Select Currency</option>');
                            $.each(response.country, function(key, value) {
                                currencySelect.append(`
                                    <option data-symbol='${value.currency_symbol}' data-currency='${value.currency}' value='${value.id}'>${value.country_name} - ${value.currency_name} - ${value.currency} - ${value.currency_symbol}</option>
                                `);
                            });
                            // Refresh Select2 after adding options
                            currencySelect.trigger('change');
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            }

            // redirect on package list page onclick cancel btn
            $('#cancelbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.package') }}";
            });

            // submit package form data
            $('#packageform').submit(function(event) {
                event.preventDefault();
                $('.error-msg').text('');
                loadershow();
                const formdata = $(this).serialize();

                ajaxRequest('POST', "{{ route('package.store') }}", formdata).done(function(response) {
                    // Handle the response from the server
                    if (response.status == 200) {
                        Toast.fire({
                            icon: "success",
                            title: response.message
                        });
                        window.location =
                            "{{ route('admin.package') }}"; // after succesfully data submit redirect on list page
                    } else if (response.status == 500) {
                        Toast.fire({
                            icon: "error",
                            title: response.message
                        });
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: response.message
                        });
                    }
                    loaderhide();
                }).fail(function(xhr) {
                    loaderhide();
                    handleAjaxError(xhr);
                });
            });
        });
    </script>
@endpush
