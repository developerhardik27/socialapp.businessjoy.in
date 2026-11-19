@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Add Family
@endsection
@section('title')
    Add Family
@endsection

@section('style')
    <style>
        .person-card {
            border: 1px solid #d7dce5;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            background: #fafbff;
        }

        .person-card .person-title {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .person-section-title {
            font-size: 13px;
            font-weight: 600;
            color: #253566;
            margin: 10px 0 5px;
        }
    </style>
@endsection

@section('form-content')
    <form id="familyaddform">
        @csrf
        <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
        <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
        <input type="hidden" name="token" value="{{ session('api_token') }}" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Family Persons</h6>
            <button type="button" id="addpersonbtn" class="btn btn-sm btn-outline-primary">
                <i class="las la-plus-circle"></i> Add Person
            </button>
        </div>

        <div id="personsContainer"></div>

        <div class="form-row">
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
            var personIndex = 0;
            var countries = [];
            var relations = [];
            var businessCategories = [];

            // redirect on family page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societyfamily') }}";
            });

            // ------------- dropdown loaders -------------
            function loadCountries() {
                return $.ajax({
                    type: 'GET',
                    url: "{{ route('country.index') }}",
                    success: function(response) {
                        if (response.status == 200 && response.country) {
                            countries = response.country;
                        }
                    }
                });
            }

            function loadRelations() {
                return $.ajax({
                    type: 'GET',
                    url: "{{ route('loadrelation.show') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.relations) {
                            relations = response.relations;
                        }
                    }
                });
            }

            function loadBusinessCategories() {
                return $.ajax({
                    type: 'GET',
                    url: "{{ route('loadbusinesscategory.show') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.businesscategories) {
                            businessCategories = response.businesscategories;
                        }
                    }
                });
            }

            function fillSelect(selectEl, items, valueKey, textKey, placeholder) {
                selectEl.empty().append(`<option value="" disabled selected>${placeholder}</option>`);
                $.each(items, function(key, item) {
                    selectEl.append(`<option value="${item[valueKey]}">${item[textKey]}</option>`);
                });
            }

            function loadStates(countryId, stateSelect, selectedState, citySelect, selectedCity) {
                stateSelect.empty().append(`<option value="" disabled selected>Select State</option>`);
                if (!countryId) {
                    return;
                }
                let stateUrl = "{{ route('state.search', '__id__') }}".replace('__id__', countryId);
                $.ajax({
                    type: 'GET',
                    url: stateUrl,
                    success: function(response) {
                        if (response.status == 200 && response.state) {
                            $.each(response.state, function(key, state) {
                                stateSelect.append(
                                    `<option value="${state.id}">${state.state_name}</option>`);
                            });
                            if (selectedState) {
                                stateSelect.val(selectedState);
                                loadCities(selectedState, citySelect, selectedCity);
                            }
                        }
                    }
                });
            }

            function loadCities(stateId, citySelect, selectedCity) {
                citySelect.empty().append(`<option value="" disabled selected>Select City</option>`);
                if (!stateId) {
                    return;
                }
                let cityUrl = "{{ route('city.search', '__id__') }}".replace('__id__', stateId);
                $.ajax({
                    type: 'GET',
                    url: cityUrl,
                    success: function(response) {
                        if (response.status == 200 && response.city) {
                            $.each(response.city, function(key, city) {
                                citySelect.append(
                                    `<option value="${city.id}">${city.city_name}</option>`);
                            });
                            if (selectedCity) {
                                citySelect.val(selectedCity);
                            }
                        }
                    }
                });
            }

            // ------------- person card -------------
            function personTemplate(i) {
                return `
                <div class="person-card" data-index="${i}">
                    <div class="person-title">
                        <span>Person ${i + 1}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-person" title="Remove Person">
                            <i class="ri-delete-bin-fill"></i>
                        </button>
                    </div>

                    <div class="person-section-title">Personal Details</div>
                    <div class="form-row">
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">First Name:</label><span style="color:red;"> *</span>
                            <input type="text" class="form-control" name="famliypersons[${i}][first_name]" placeholder="First Name" required />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Last Name:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][last_name]" placeholder="Last Name" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Surname:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][surname]" placeholder="Surname" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Photo:</label>
                            <input type="file" class="form-control person-img-input" name="famliypersons[${i}][img]" accept="image/jpeg,image/jpg,image/png" />
                            <img class="person-img-preview mt-1" style="display:none;max-width:80px;border-radius:6px;" alt="Preview" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">DOB:</label>
                            <input type="date" class="form-control" name="famliypersons[${i}][dob]" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Age:</label>
                            <input type="number" class="form-control" name="famliypersons[${i}][age]" min="0" placeholder="Age" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Gender:</label>
                            <select class="form-control" name="famliypersons[${i}][gender]">
                                <option value="" disabled selected>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Marital Status:</label>
                            <select class="form-control" name="famliypersons[${i}][marital_status]">
                                <option value="" disabled selected>Select Marital Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Divorced">Divorced</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Email:</label><span style="color:red;"> *</span>
                            <input type="email" class="form-control" name="famliypersons[${i}][email]" placeholder="Email" required />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Mobile:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][mobile]" maxlength="15" onkeyup="this.value=this.value.replace(/[^+\\d]/g,'')" placeholder="Mobile Number" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Relation:</label>
                            <select class="form-control relation-select" name="famliypersons[${i}][relationship_id]">
                                <option value="" disabled selected>Select Relation</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Shakh:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][shakh]" placeholder="Shakh" />
                        </div>
                        <div class="col-sm-4 mb-2 d-flex align-items-end">
                            <div class="custom-control custom-checkbox">
                                <input type="hidden" name="famliypersons[${i}][main_family_member]" value="0">
                                <input type="checkbox" class="custom-control-input main-member-check"
                                    id="main_member_${i}" name="famliypersons[${i}][main_family_member]" value="1">
                                <label class="custom-control-label" for="main_member_${i}">Main Family Member</label>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2 member-fields" style="display:none;">
                            <label class="form-label">Lifetime Member No:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][lifetime_member_no]" placeholder="Lifetime Member No" />
                        </div>
                        <div class="col-sm-4 mb-2 member-fields" style="display:none;">
                            <label class="form-label">Receipt Number:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][receipt_number]" placeholder="Receipt Number" />
                        </div>
                    </div>

                    <div class="person-section-title">Address Details</div>
                    <div class="form-row">
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">House No / Building:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][address_house_no_building_name]" placeholder="House No / Building" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Landmark:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][address_landmark]" placeholder="Landmark" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Area:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][address_area]" placeholder="Area" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Country:</label>
                            <select class="form-control address-country-select" name="famliypersons[${i}][address_country_id]">
                                <option value="" disabled selected>Select Country</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">State:</label>
                            <select class="form-control address-state-select" name="famliypersons[${i}][address_state_id]">
                                <option value="" disabled selected>Select State</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">City:</label>
                            <select class="form-control address-city-select" name="famliypersons[${i}][address_city_id]">
                                <option value="" disabled selected>Select City</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Pincode:</label>
                            <input type="number" class="form-control" name="famliypersons[${i}][address_pincode]" placeholder="Pincode" />
                        </div>
                    </div>

                    <div class="person-section-title">Business / Company Details</div>
                    <div class="form-row">
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Job Role:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][job_role]" placeholder="Job Role" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Company Name:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][company_name]" placeholder="Company Name" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Company Building:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][company_house_no_building_name]" placeholder="Building Name" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Company Landmark:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][company_landmark]" placeholder="Landmark" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Company Area:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][company_area]" placeholder="Area" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Company Country:</label>
                            <select class="form-control company-country-select" name="famliypersons[${i}][company_country_id]">
                                <option value="" disabled selected>Select Country</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Company State:</label>
                            <select class="form-control company-state-select" name="famliypersons[${i}][company_state_id]">
                                <option value="" disabled selected>Select State</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Company City:</label>
                            <select class="form-control company-city-select" name="famliypersons[${i}][company_city_id]">
                                <option value="" disabled selected>Select City</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Company Pincode:</label>
                            <input type="number" class="form-control" name="famliypersons[${i}][company_pincode]" placeholder="Pincode" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Business Category:</label>
                            <select class="form-control business-category-select" name="famliypersons[${i}][business_category]">
                                <option value="" disabled selected>Select Category</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Business Subcategory:</label>
                            <select class="form-control business-subcategory-select" name="famliypersons[${i}][business_subcategory]">
                                <option value="" disabled selected>Select Subcategory</option>
                            </select>
                        </div>
                        <div class="col-sm-12 mb-2">
                            <label class="form-label">Business Intro:</label>
                            <textarea class="form-control" name="famliypersons[${i}][business_intro]" rows="2" placeholder="Business Introduction"></textarea>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Services:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][services]" placeholder="Services" />
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">SEO Keywords:</label>
                            <input type="text" class="form-control" name="famliypersons[${i}][seo_keywords]" placeholder="SEO Keywords" />
                        </div>
                    </div>
                </div>`;
            }

            function wirePersonCard(card) {
                var i = card.data('index');

                fillSelect(card.find('.address-country-select'), countries, 'id', 'country_name', 'Select Country');
                fillSelect(card.find('.company-country-select'), countries, 'id', 'country_name', 'Select Country');
                fillSelect(card.find('.relation-select'), relations, 'id', 'relation', 'Select Relation');
                fillSelect(card.find('.business-category-select'), businessCategories, 'id', 'name', 'Select Category');

                card.find('.person-img-input').on('change', function() {
                    var preview = card.find('.person-img-preview');
                    if (this.files && this.files[0]) {
                        preview.attr('src', URL.createObjectURL(this.files[0])).show();
                    } else {
                        preview.hide().attr('src', '');
                    }
                });

                card.find('.address-country-select').on('change', function() {
                    loadStates($(this).val(), card.find('.address-state-select'), '', card.find(
                        '.address-city-select'), '');
                });
                card.find('.address-state-select').on('change', function() {
                    loadCities($(this).val(), card.find('.address-city-select'), '');
                });
                card.find('.company-country-select').on('change', function() {
                    loadStates($(this).val(), card.find('.company-state-select'), '', card.find(
                        '.company-city-select'), '');
                });
                card.find('.company-state-select').on('change', function() {
                    loadCities($(this).val(), card.find('.company-city-select'), '');
                });
                card.find('.business-category-select').on('change', function() {
                    var categoryId = $(this).val();
                    var subSelect = card.find('.business-subcategory-select');
                    subSelect.empty().append(`<option value="" disabled selected>Select Subcategory</option>`);
                    if (!categoryId) {
                        return;
                    }
                    let subUrl = "{{ route('loadbusinesssubcategory.show', '__id__') }}".replace('__id__',
                        categoryId);
                    $.ajax({
                        type: 'GET',
                        url: subUrl,
                        data: {
                            token: "{{ session()->get('api_token') }}",
                            company_id: "{{ session()->get('company_id') }}",
                            user_id: "{{ session()->get('user_id') }}"
                        },
                        success: function(response) {
                            if (response.status == 200 && response.businesssubcategories) {
                                $.each(response.businesssubcategories, function(key, sub) {
                                    subSelect.append(
                                        `<option value="${sub.id}">${sub.name}</option>`);
                                });
                            }
                        }
                    });
                });
            }

            function addPersonCard() {
                var card = $(personTemplate(personIndex));
                $('#personsContainer').append(card);
                wirePersonCard(card);
                personIndex++;
                toggleRemoveButtons();
            }

            function toggleRemoveButtons() {
                if ($('#personsContainer .person-card').length > 1) {
                    $('.remove-person').show();
                } else {
                    $('.remove-person').hide();
                }
            }

            // add person button
            $('#addpersonbtn').on('click', function() {
                addPersonCard();
            });

            // remove person
            $(document).on('click', '.remove-person', function() {
                $(this).closest('.person-card').remove();
                toggleRemoveButtons();
                renumberCards();
            });

            function renumberCards() {
                $('#personsContainer .person-card').each(function(idx) {
                    $(this).find('.person-title span').text('Person ' + (idx + 1));
                });
            }

            // only one main family member + show member fields
            $(document).on('change', '.main-member-check', function() {
                var card = $(this).closest('.person-card');
                if ($(this).is(':checked')) {
                    $('.main-member-check').not(this).prop('checked', false);
                    $('.member-fields').hide();
                    card.find('.member-fields').show();
                } else {
                    card.find('.member-fields').hide();
                }
            });

            // init: load dropdowns then add first person card
            loadershow();
            $.when(loadCountries(), loadRelations(), loadBusinessCategories()).always(function() {
                addPersonCard();
                loaderhide();
            });

            //submit form
            $('#familyaddform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');

                if ($('.main-member-check:checked').length !== 1) {
                    loaderhide();
                    Toast.fire({
                        icon: "error",
                        title: "Please select exactly one main family member"
                    });
                    return;
                }

                // omit empty date/int fields so api does not insert '' into date/int columns
                var intDateFields = ['dob', 'age', 'address_country_id', 'address_state_id',
                    'address_city_id', 'address_pincode', 'company_country_id',
                    'company_state_id', 'company_city_id', 'company_pincode',
                    'business_category', 'business_subcategory', 'relationship_id'
                ];
                var emptied = [];
                $(this).find('input,select,textarea').each(function() {
                    var key = ($(this).attr('name') || '').replace(/^.*\[/, '').replace(
                        /\]$/, '');
                    if (intDateFields.indexOf(key) !== -1 && $(this).val() === '') {
                        emptied.push(this);
                        $(this).prop('disabled', true);
                    }
                });
                var formData = new FormData(this);
                $('#personsContainer .person-card').each(function() {
                    var idx = $(this).data('index');
                    var checked = $(this).find('.main-member-check').is(':checked');
                    formData.set('famliypersons[' + idx + '][main_family_member]', checked ? 1 : 0);
                });
                $(emptied).prop('disabled', false);

                ajaxRequest('POST', "{{ route('family.store') }}", formData)
                    .done(function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Family added successfully!'
                            });
                            window.location = "{{ route('admin.societyfamily') }}";
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
