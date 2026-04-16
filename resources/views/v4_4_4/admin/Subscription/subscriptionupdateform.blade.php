@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')

@section('page_title')
    {{ config('app.name') }} - Edit Subscription
@endsection
@section('title')
    Edit Subscription
@endsection

@section('form-content')
    <form id="subscriptionupdateform">
        @csrf
        @method('PUT')
        <div class="form-group">
            <div class="form-row">
                <!-- Hidden Fields -->
                <input type="hidden" name="user_id" class="form-control" value="{{ session('user_id') }}" placeholder="user_id"
                    required />
                <input type="hidden" name="token" class="form-control" value="{{ session('api_token') }}"
                    placeholder="token" required />
                <input type="hidden" name="company_id" class="form-control" value="{{ session('company_id') }}"
                    placeholder="company_id" required />
                <input type="hidden" name="subscription_id" class="form-control" value="{{ $edit_id ?? '' }}" />

                <!-- Company Selection -->
                <div class="col-sm-6 mb-2">
                    <label for="company">Select Company</label><span style="color:red;">*</span>
                    <select name="company" class="form-control select2" id="company" required>
                        <option value="">Select Company</option>
                    </select>
                    <span class="error-msg" id="error-company" style="color: red"></span>
                </div>

                <!-- Package Selection -->
                <div class="col-sm-6 mb-2">
                    <label for="package">Select Package</label><span style="color:red;">*</span>
                    <select name="package" class="form-control select2" id="package" required>
                        <option value="">Select Package</option>
                    </select>
                    <span class="error-msg" id="error-package" style="color: red"></span>
                </div>

                <!-- TRIAL SECTION -->
                <div class="col-sm-12">
                    <hr>
                    <h5><i class="ri-time-line"></i> Trial Period</h5>
                </div>

                <!-- Trial Start Date -->
                <div class="col-sm-6 mb-2">
                    <label for="trial_start_date">Trial Start Date</label>
                    <input type="date" name="trial_start_date" class="form-control" id="trial_start_date" />
                    <span class="error-msg" id="error-trial_start_date" style="color: red"></span>
                </div>

                <!-- Trial Days -->
                <div class="col-sm-6 mb-2">
                    <label for="trial_days">Trial Days</label>
                    <input type="number" name="trial_days" class="form-control" id="trial_days" min="0" />
                    <span class="error-msg" id="error-trial_days" style="color: red"></span>
                </div>

                <!-- Trial End Date (Auto-calculated) -->
                <div class="col-sm-6 mb-2">
                    <label for="trial_end_date">Trial End Date</label>
                    <input type="date" name="trial_end_date" class="form-control" id="trial_end_date" readonly />
                    <span class="error-msg" id="error-trial_end_date" style="color: red"></span>
                </div>

                <!-- SUBSCRIPTION SECTION -->
                <div class="col-sm-12">
                    <hr>
                    <h5><i class="ri-checkbox-circle-line"></i> Subscription Period</h5>
                </div>

                <!-- Subscription Start Date -->
                <div class="col-sm-6 mb-2">
                    <label for="subscription_start_date">Subscription Start Date</label><span style="color:red;">*</span>
                    <input type="date" name="subscription_start_date" class="form-control" id="subscription_start_date"
                        required />
                    <span class="error-msg" id="error-subscription_start_date" style="color: red"></span>
                </div>

                <!-- Subscription End Date -->
                <div class="col-sm-6 mb-2">
                    <label for="subscription_end_date">Subscription End Date</label>
                    <div class="input-group">
                        <input type="date" name="subscription_end_date" class="form-control" id="subscription_end_date"
                            placeholder="Leave empty for always active" />
                    </div>
                    <span class="error-msg" id="error-subscription_end_date" style="color: red"></span>
                </div>

                <!-- Quick Action Buttons for Subscription -->
                <div class="col-sm-12 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary mr-2 quick-action" data-action="always">
                        <i class="ri-infinity-line"></i> Always
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary mr-2 quick-action" data-action="1month">
                        <i class="ri-calendar-line"></i> 1 Month
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary mr-2 quick-action" data-action="6month">
                        <i class="ri-calendar-line"></i> 6 Months
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary mr-2 quick-action" data-action="1year">
                        <i class="ri-calendar-line"></i> 1 Year
                    </button>
                </div>

                <!-- Subscription Status -->
                <div class="col-sm-6 mb-2">
                    <label for="status">Subscription Status</label>
                    <select name="status" class="form-control select2" id="status" required>
                        <option value="">Select Status</option>
                        <option value="trial">Trial</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="suspended">Suspended</option>
                    </select>
                    <span class="error-msg" id="error-status" style="color: red"></span>
                </div>

                <!-- PAYMENT CYCLE SECTION -->
                <div class="col-sm-12">
                    <hr>
                    <h5><i class="ri-bank-card-line"></i> Payment Cycle</h5>
                </div>

                <!-- Cycle Duration Type (Dropdown - Monthly, Quarterly, Yearly) -->
                <div class="col-sm-6 mb-2">
                    <label for="billing_cycle">Cycle Duration Type</label><span style="color:red;">*</span>
                    <select name="billing_cycle" class="form-control select2" id="billing_cycle" required>
                        <option value="">Select Cycle Type</option>
                        <option value="monthly">Monthly (30 Days)</option>
                        <option value="quarterly">Quarterly (90 Days)</option>
                        <option value="yearly">Yearly (365 Days)</option>
                    </select>
                    <span class="error-msg" id="error-billing_cycle" style="color: red"></span>
                </div>

                <!-- Cycle Duration (Days) Display -->
                <div class="col-sm-6 mb-2">
                    <label for="cycle_duration">Cycle Duration (Days)</label>
                    <input type="text" name="cycle_duration" class="form-control" id="cycle_duration" readonly />
                    <span class="error-msg" id="error-cycle_duration" style="color: red"></span>
                </div>

                <!-- Payment Cycle Start Date -->
                <div class="col-sm-6 mb-2">
                    <label for="payment_cycle_start_date">Payment Cycle Start Date</label><span
                        style="color:red;">*</span>
                    <input type="date" name="payment_cycle_start_date" class="form-control"
                        id="payment_cycle_start_date" required />
                    <span class="error-msg" id="error-payment_cycle_start_date" style="color: red"></span>
                </div>

                <!-- Payment Cycle End Date (Auto-calculated) -->
                <div class="col-sm-6 mb-2">
                    <label for="payment_cycle_end_date">Payment Cycle End Date</label>
                    <input type="date" name="payment_cycle_end_date" class="form-control" id="payment_cycle_end_date"
                        readonly />
                    <span class="error-msg" id="error-payment_cycle_end_date" style="color: red"></span>
                </div>

                <!-- Next Billing Date (Auto-calculated) -->
                <div class="col-sm-6 mb-2">
                    <label for="next_billing_date">Next Billing Date</label>
                    <input type="date" name="next_billing_date" class="form-control" id="next_billing_date"
                        readonly />
                    <span class="error-msg" id="error-next_billing_date" style="color: red"></span>
                </div>

                <!-- PRICING SECTION -->
                <div class="col-sm-12">
                    <hr>
                    <h5><i class="ri-money-dollar-circle-line"></i> Pricing</h5>
                </div>

                <!-- Package Type Display -->
                <div class="col-sm-6 mb-2">
                    <label for="package_type">Package Type</label>
                    <input type="text" name="package_type" class="form-control" id="package_type" readonly />
                    <span class="error-msg" id="error-package_type" style="color: red"></span>
                </div>

                <!-- Package Price Display -->
                <div class="col-sm-6 mb-2">
                    <label for="package_price">Package Price</label>
                    <input type="text" name="package_price" class="form-control" id="package_price" readonly />
                    <span class="error-msg" id="error-package_price" style="color: red"></span>
                </div>

                <!-- EMI Calculation Info -->
                <div class="col-sm-12 mb-2">
                    <label for="emi_calculation">EMI Calculation</label>
                    <input type="text" name="emi_calculation" class="form-control" id="emi_calculation" readonly />
                    <span class="error-msg" id="error-emi_calculation" style="color: red"></span>
                </div>

                <!-- EMI Cost Display -->
                <div class="col-sm-6 mb-2">
                    <label for="emi_cost">EMI Cost (Per Cycle)</label>
                    <input type="text" name="emi_cost" class="form-control" id="emi_cost" readonly />
                    <span class="error-msg" id="error-emi_cost" style="color: red"></span>
                </div>

                <!-- Auto Generate Invoice -->
                <div class="col-sm-6 mb-2 d-flex align-items-center" style="gap:10px;">
                    <input type="hidden" name="auto_generate_invoice" value="0" />
                    <input type="checkbox" name="auto_generate_invoice" id="auto_generate_invoice" value="1" />
                    <label for="auto_generate_invoice" class="mb-0">Auto generate invoice</label>
                </div>

                <!-- Submit Buttons -->
                <div class="col-sm-12 mt-4">
                    <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Cancel"
                        id="cancelbtn" class="btn btn-secondary float-right">Cancel</button>
                    <button type="reset" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Reset Subscription Details"
                        class="btn iq-bg-danger float-right mr-2">Reset</button>
                    <button type="submit" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Update Subscription Details"
                        class="btn btn-primary float-right m-0 mr-2">Update</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('ajax')
    <script>
        $('document').ready(function() {
            loaderhide();

            // Initialize Select2
            $('#company').select2({
                placeholder: 'Search and select company',
                allowClear: true,
                width: '100%'
            });

            $('#package').select2({
                placeholder: 'Search and select package',
                allowClear: true,
                width: '100%'
            });

            $('#billing_cycle').select2({
                placeholder: 'Search and select cycle type',
                allowClear: true,
                width: '100%'
            });

            $('#status').select2({
                placeholder: 'Select status',
                allowClear: true,
                width: '100%'
            });

            // Load companies and packages
            loadCompanies();
            loadPackages();

            var edit_id = @json($edit_id);
            // Load subscription data for editing
            loadSubscriptionData(edit_id);

            function loadCompanies() {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('company.companylist') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200) {
                            let companySelect = $('#company');
                            companySelect.html('<option value="">Select Company</option>');
                            $.each(response.company, function(key, company) {
                                var companydetails = [
                                    company.name,
                                    company.contact_no,
                                    company.email,
                                    company.app_version ? company.app_version.replace(/_/g,
                                        '.') : null
                                ].filter(Boolean).join(' - ');
                                if (company.subscription_count > 0) {
                                    companydetails +=
                                        ` - ${company.subscription_count} Active Subscription(s)`;
                                }
                                companySelect.append(
                                    `<option value='${company.id}'>${companydetails}</option>`
                                );
                            });
                            companySelect.trigger('change');
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            }

            function loadPackages() {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('package.index') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200) {
                            let packageSelect = $('#package');
                            packageSelect.html('<option value="">Select Package</option>');
                            $.each(response.data, function(key, pkg) {
                                packageSelect.append(
                                    `<option value='${pkg.id}' data-price='${pkg.price}' data-type='${pkg.type}' data-trial='${pkg.trial_days}'>${pkg.name} - ${pkg.type}</option>`
                                );
                            });
                            packageSelect.trigger('change');
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            }

            function loadSubscriptionData(id) {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscription.search', '__id__') }}".replace('__id__', id),
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.subscription) {
                            const data = response.subscription;

                            // Populate form fields
                            $('#company').val(data.company_id).trigger('change');
                            $('#package').val(data.package_id).trigger('change');
                            $('#trial_start_date').val(data.trial_start_date || '');
                            $('#trial_days').val(data.trial_days || 0);
                            $('#trial_end_date').val(data.trial_end_date || '');
                            $('#subscription_start_date').val(data.subscription_start_date);
                            $('#subscription_end_date').val(data.subscription_end_date || '');
                            $('#status').val(data.status).trigger('change');
                            $('#billing_cycle').val(data.billing_cycle || '').trigger('change');
                            $('#payment_cycle_start_date').val(data.payment_cycle_start_date);
                            $('#payment_cycle_end_date').val(data.payment_cycle_end_date);
                            $('#next_billing_date').val(data.next_billing_date);
                            $('#package_type').val(data.package_type || '');
                            $('#package_price').val(data.package_price || '');
                            $('#emi_calculation').val(data.emi_calculation || '');
                            $('#emi_cost').val(data.emi_cost || '');
                            $('#auto_generate_invoice').prop('checked', data.auto_generate_invoice ==
                                1);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        Toast.fire({
                            icon: "error",
                            title: "Error loading subscription data"
                        });
                    }
                });
            }

            // Get cycle type days
            function getCycleDays(type) {
                const days = {
                    'monthly': 30,
                    'quarterly': 90,
                    'yearly': 365
                };
                return days[type] || 0;
            }

            // Get package type days
            function getPackageDays(type) {
                const days = {
                    'monthly': 30,
                    'quarterly': 90,
                    'yearly': 365
                };
                return days[type] || 0;
            }

            // Trial start date change handler
            $('#trial_start_date').on('change', function() {
                const startDate = new Date($(this).val());
                let trialDays = parseInt($('#trial_days').val()) || 0;

                if (startDate && !isNaN(startDate) && trialDays > 0) {
                    const endDate = new Date(startDate);
                    endDate.setDate(endDate.getDate() + trialDays);
                    $('#trial_end_date').val(endDate.toISOString().split('T')[0]);
                } else {
                    $('#trial_end_date').val('');
                }
            });

            // Set today as default and calculate trial end date
            $('#trial_start_date').on('change', function() {
                const startDate = new Date($(this).val());
                const packageOption = $('#package').find('option:selected');
                let trialDays = parseInt($('#trial_days').val());
                if (isNaN(trialDays) || trialDays < 0) {
                    trialDays = parseInt(packageOption.data('trial')) || 0;
                }

                if (startDate && !isNaN(startDate) && trialDays > 0) {
                    const endDate = new Date(startDate);
                    endDate.setDate(endDate.getDate() + trialDays);

                    $('#trial_end_date').val(endDate.toISOString().split('T')[0]);

                    // Auto-set subscription start date to next day after trial end date
                    const subscriptionStartDate = new Date(endDate);
                    subscriptionStartDate.setDate(subscriptionStartDate.getDate() + 1);
                    $('#subscription_start_date').val(subscriptionStartDate.toISOString().split('T')[0]);

                    // Auto-set payment cycle start date to subscription start date
                    $('#payment_cycle_start_date').val(subscriptionStartDate.toISOString().split('T')[0]);

                    // Recalculate payment cycle dates with current cycle type
                    if ($('#billing_cycle').val()) {
                        $('#payment_cycle_start_date').trigger('change');
                    }
                } else if (startDate && !isNaN(startDate) && trialDays === 0) {
                    // If trialDays is zero, clear trial end and set subscription start as trial start
                    $('#trial_end_date').val('');
                    $('#subscription_start_date').val(startDate.toISOString().split('T')[0]);
                    $('#payment_cycle_start_date').val(startDate.toISOString().split('T')[0]);
                    if ($('#billing_cycle').val()) {
                        $('#payment_cycle_start_date').trigger('change');
                    }
                }
            });

            // When admin edits trial days directly, recalc end/subscription/payment dates
            $('#trial_days').on('input change', function() {
                if ($('#trial_start_date').val()) {
                    $('#trial_start_date').trigger('change');
                }
            });

            // Update when subscription start date changes
            $('#subscription_start_date').on('change', function() {
                // Auto-set payment cycle start date to subscription start date
                $('#payment_cycle_start_date').val($(this).val());

                // Recalculate payment cycle end date and next billing date
                if ($('#billing_cycle').val()) {
                    $('#payment_cycle_start_date').trigger('change');
                }
            });

            // Update when package changes
            $('#package').on('change', function() {
                const packageOption = $(this).find('option:selected');
                const price = packageOption.data('price') || 0;
                const trialDays = parseInt(packageOption.data('trial')) || 0;
                const packageType = packageOption.data('type');

                $('#package_price').val(price);
                $('#package_type').val(packageType);
                $('#trial_days').val(trialDays);

                // Set cycle type to package type (default)
                $('#billing_cycle').val(packageType).trigger('change');

                // Recalculate trial end date if trial start date is set
                if ($('#trial_start_date').val()) {
                    $('#trial_start_date').trigger('change');
                }

                // Recalculate EMI
                calculateEMI();
            });

            // Cycle type change - updates cycle duration and recalculates dates
            $('#billing_cycle').on('change', function() {
                const cycleType = $(this).val();
                const cycleDays = getCycleDays(cycleType);

                $('#cycle_duration').val(cycleDays + ' days');

                // Recalculate payment cycle end date and next billing date with new cycle duration
                if ($('#payment_cycle_start_date').val()) {
                    calculatePaymentCycleDates();
                }

                // Recalculate EMI
                calculateEMI();
            });

            // Calculate Payment Cycle End Date and Next Billing Date
            function calculatePaymentCycleDates() {
                const startDate = new Date($('#payment_cycle_start_date').val());
                const cycleType = $('#billing_cycle').val();
                const cycleDays = getCycleDays(cycleType);

                if (startDate && !isNaN(startDate) && cycleDays > 0) {
                    // Calculate end date (cycle days - 1 to get last day of cycle)
                    const endDate = new Date(startDate);
                    endDate.setDate(endDate.getDate() + cycleDays - 1);
                    $('#payment_cycle_end_date').val(endDate.toISOString().split('T')[0]);

                    // Calculate next billing date: 15 days before cycle end
                    let nextBillingDate = new Date(endDate);
                    nextBillingDate.setDate(nextBillingDate.getDate() - 15);

                    // Safety: do not set next billing earlier than cycle start
                    if (nextBillingDate < startDate) {
                        nextBillingDate = new Date(startDate);
                    }

                    $('#next_billing_date').val(nextBillingDate.toISOString().split('T')[0]);

                    // Recalculate EMI
                    calculateEMI();
                }
            }

            // Payment cycle start date change
            $('#payment_cycle_start_date').on('change', function() {
                calculatePaymentCycleDates();
            });

            // Calculate EMI Cost dynamically - All scenarios covered
            function calculateEMI() {
                const packageOption = $('#package').find('option:selected');
                const packagePrice = parseFloat(packageOption.data('price')) || 0;
                const packageType = packageOption.data('type');
                const cycleType = $('#billing_cycle').val();

                const packageDays = getPackageDays(packageType);
                const cycleDays = getCycleDays(cycleType);

                let emiCost = 0;
                let calculation = '';

                if (cycleDays > 0 && packageDays > 0 && packagePrice > 0) {
                    // Package: Monthly (30), Cycle: Monthly (30)
                    if (packageType === 'monthly' && cycleType === 'monthly') {
                        emiCost = packagePrice;
                        calculation = `${packagePrice} × 1`;
                    }
                    // Package: Monthly (30), Cycle: Quarterly (90)
                    else if (packageType === 'monthly' && cycleType === 'quarterly') {
                        emiCost = packagePrice * 3;
                        calculation = `${packagePrice} × 3`;
                    }
                    // Package: Monthly (30), Cycle: Yearly (365)
                    else if (packageType === 'monthly' && cycleType === 'yearly') {
                        emiCost = packagePrice * 12;
                        calculation = `${packagePrice} × 12`;
                    }
                    // Package: Quarterly (90), Cycle: Monthly (30)
                    else if (packageType === 'quarterly' && cycleType === 'monthly') {
                        emiCost = packagePrice / 3;
                        calculation = `${packagePrice} ÷ 3`;
                    }
                    // Package: Quarterly (90), Cycle: Quarterly (90)
                    else if (packageType === 'quarterly' && cycleType === 'quarterly') {
                        emiCost = packagePrice;
                        calculation = `${packagePrice} × 1`;
                    }
                    // Package: Quarterly (90), Cycle: Yearly (365)
                    else if (packageType === 'quarterly' && cycleType === 'yearly') {
                        emiCost = packagePrice * 4;
                        calculation = `${packagePrice} × 4`;
                    }
                    // Package: Yearly (365), Cycle: Monthly (30)
                    else if (packageType === 'yearly' && cycleType === 'monthly') {
                        emiCost = packagePrice / 12;
                        calculation = `${packagePrice} ÷ 12`;
                    }
                    // Package: Yearly (365), Cycle: Quarterly (90)
                    else if (packageType === 'yearly' && cycleType === 'quarterly') {
                        emiCost = packagePrice / 4;
                        calculation = `${packagePrice} ÷ 4`;
                    }
                    // Package: Yearly (365), Cycle: Yearly (365)
                    else if (packageType === 'yearly' && cycleType === 'yearly') {
                        emiCost = packagePrice;
                        calculation = `${packagePrice} × 1`;
                    }
                }

                $('#emi_cost').val(emiCost.toFixed(2));
                $('#emi_calculation').val(calculation);
            }

            // Quick action buttons for subscription dates
            $('.quick-action').on('click', function(e) {
                e.preventDefault();
                const action = $(this).data('action');
                const subscriptionStartDate = new Date($('#subscription_start_date').val());

                if (!subscriptionStartDate || isNaN(subscriptionStartDate)) {
                    Toast.fire({
                        icon: "warning",
                        title: "Please set Subscription Start Date first"
                    });
                    return;
                }

                if (action === 'always') {
                    // Clear end date for "Always" option
                    $('#subscription_end_date').val('');
                } else {
                    let endDate = new Date(subscriptionStartDate);

                    if (action === '1month') {
                        // Add 1 month and subtract 1 day
                        endDate.setMonth(endDate.getMonth() + 1);
                        endDate.setDate(endDate.getDate() - 1);
                    } else if (action === '6month') {
                        // Add 6 months and subtract 1 day
                        endDate.setMonth(endDate.getMonth() + 6);
                        endDate.setDate(endDate.getDate() - 1);
                    } else if (action === '1year') {
                        // Add 1 year and subtract 1 day
                        endDate.setFullYear(endDate.getFullYear() + 1);
                        endDate.setDate(endDate.getDate() - 1);
                    }

                    $('#subscription_end_date').val(endDate.toISOString().split('T')[0]);
                }
            });

            // Cancel button
            $('#cancelbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.subscription') }}";
            });

            // Submit form
            $('#subscriptionupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                const formdata = $(this).serialize();

                const url = "{{ route('subscription.update', '__id__') }}".replace('__id__', edit_id);

                const method = 'POST';

                ajaxRequest(method, url, formdata).done(function(response) {
                    if (response.status == 200) {
                        Toast.fire({
                            icon: "success",
                            title: response.message
                        });
                        window.location = "{{ route('admin.subscription') }}";
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
