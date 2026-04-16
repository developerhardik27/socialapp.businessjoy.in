<div>
    <!-- Because you are alive, everything is possible. - Thich Nhat Hanh -->
</div>

@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')

@section('page_title')
    {{ config('app.name') }} - Update Package
@endsection
@section('title')
    Update Package
@endsection


@section('form-content')
    <form id="packageupdateform">
        @csrf
        @method('PUT')
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
                    <input type="text" id="name" class="form-control" name='name'
                        placeholder="Package Name" required>
                    <span class="error-msg" id="error-name" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="type">Package Type</label><span style="color:red;">*</span>
                    <select id="type" class="form-control" name='type' required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="monthly">Monthly(30 Days)</option>
                        <option value="quarterly">Quarterly(90 Days)</option>
                        <option value="yearly">Yearly(365 Days)</option>
                    </select>
                    <span class="error-msg" id="error-type" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="price">Price</label><span style="color:red;">*</span>
                    <input type="number" step="0.01" id="price" class="form-control" name='price'
                        placeholder="Price" required>
                    <span class="error-msg" id="error-price" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="currency">Currency</label>
                    <select id="currency" class="form-control select2" name='currency'>
                        <option value="">Select Currency</option>
                    </select>
                    <span class="error-msg" id="error-currency" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="trial_days">Trial Days</label>
                    <input type="number" id="trial_days" class="form-control" name='trial_days'
                        placeholder="Trial Days" min="0">
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
                    <button type="reset" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Reset Package Details"
                        class="btn iq-bg-danger float-right mr-2">Reset</button>
                    <button type="submit" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Update Package Details"
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

            // get selected package data and show it into fields
            var edit_id = @json($edit_id);
            let packageSearchUrl = "{{ route('package.search', '__editId__') }}".replace('__editId__', edit_id);
            $.ajax({
                type: 'GET',
                url: packageSearchUrl,
                data: {
                    token: "{{ session()->get('api_token') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    user_id: "{{ session()->get('user_id') }}"
                },
                success: function(response) {
                    if (response.status == 200) {
                        data = response.package;

                        // You can update your HTML with the data here if needed
                        $('#name').val(data.name);
                        $('#type').val(data.type);
                        $('#price').val(data.price);
                        $('#trial_days').val(data.trial_days);
                        $('#description').val(data.description);
                        currency = data.currency_id;
                        
                        loadCurrencies(currency);

                    } else if (response.status == 500) {
                        Toast.fire({
                            icon: "error",
                            title: response.message
                        });
                    }
                    loaderhide();
                },
                error: function(error) {
                    loaderhide();
                    console.error('Error:', error);
                }
            });

            // show currency data in dropdown
            function loadCurrencies(selectedCurrency) {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('country.index') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.country != '') {
                            let currencySelect = $('#currency');
                            currencySelect.html('<option value="">Select Currency</option>');
                            // You can update your HTML with the data here if needed
                            $.each(response.country, function(key, value) {
                                let selected = value.id == selectedCurrency ? 'selected' : '';
                                currencySelect.append(
                                    `<option data-symbol='${value.currency_symbol}' data-currency='${value.currency}' value='${value.id}' ${selected}>${value.country_name} - ${value.currency_name} - ${value.currency} - ${value.currency_symbol}</option>`
                                )
                            });
                            // Refresh Select2 after adding options
                            currencySelect.trigger('change');
                        } else {
                            $('#currency').append(`<option disabled> No Data Found</option>`);
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            }

            // redirect on package list page on click cancel btn
            $('#cancelbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.package') }}";
            });

            // submit form
            $('#packageupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                const formdata = $(this).serialize();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('package.update', $edit_id) }}",
                    data: formdata,
                    success: function(response) {
                        // Handle the response from the server
                        if (response.status == 200) {
                            // You can perform additional actions, such as showing a success message or redirecting the user
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });
                            window.location.href =
                                "{{ route('admin.package') }}";

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
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr.responseText); // Log the full error response for debugging
                        handleAjaxError(xhr);
                    }
                });
            })
        });
    </script>
@endpush
