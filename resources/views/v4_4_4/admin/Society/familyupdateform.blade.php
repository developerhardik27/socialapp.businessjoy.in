@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Update Family
@endsection
@section('title')
    Update Family
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
    <form id="familyupdateform">
        @csrf
        <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
        <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
        <input type="hidden" name="token" value="{{ session('api_token') }}" />
        <input type="hidden" name="family_id" value="{{ $edit_id }}" />

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
                <button type="submit" data-toggle="tooltip" data-placement="bottom" data-original-title="Update"
                    class="btn btn-primary float-right my-0">Save</button>
            </div>
        </div>
    </form>
@endsection

@push('ajax')
    <script>
        $('document').ready(function() {
            loaderhide();
            var edit_id = @json($edit_id);
            var personIndex = 0;
            var countries = [];
            var relations = [];
            var businessCategories = [];
            var shakhs = [];

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

            function loadShakhs() {
                return $.ajax({
                    type: 'GET',
                    url: "{{ route('shakh.index') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.data) {
                            shakhs = response.data;
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

            function loadSubcategories(categoryId, subSelect, selectedSub) {
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
                                subSelect.append(`<option value="${sub.id}">${sub.name}</option>`);
                            });
                            if (selectedSub) {
                                subSelect.val(selectedSub);
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
                    <input type="hidden" class="person-id-input" name="famliypersons[${i}][family_person_id]" value="" />

                    <div class="person-section-title">Personal Details</div>
                    <div class="form-row">
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">First Name:</label><span style="color:red;"> *</span>
                            <input type="text" class="form-control f-first_name" name="famliypersons[${i}][first_name]" placeholder="First Name" required />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Last Name:</label>
                            <input type="text" class="form-control f-last_name" name="famliypersons[${i}][last_name]" placeholder="Last Name" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Surname:</label>
                            <input type="text" class="form-control f-surname" name="famliypersons[${i}][surname]" placeholder="Surname" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Photo:</label>
                            <input type="file" class="form-control person-img-input" name="famliypersons[${i}][img]" accept="image/jpeg,image/jpg,image/png" />
                            <img class="person-img-preview mt-1" style="display:none;max-width:80px;border-radius:6px;" alt="Preview" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">DOB:</label>
                            <input type="date" class="form-control f-dob" name="famliypersons[${i}][dob]" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Age:</label>
                            <input type="number" class="form-control f-age" name="famliypersons[${i}][age]" min="0" placeholder="Age" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Gender:</label>
                            <select class="form-control f-gender" name="famliypersons[${i}][gender]">
                                <option value="" disabled selected>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Marital Status:</label>
                            <select class="form-control f-marital_status" name="famliypersons[${i}][marital_status]">
                                <option value="" disabled selected>Select Marital Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Divorced">Divorced</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Email:</label><span style="color:red;"> *</span>
                            <input type="email" class="form-control f-email" name="famliypersons[${i}][email]" placeholder="Email" required />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Mobile:</label>
                            <input type="text" class="form-control f-mobile" name="famliypersons[${i}][mobile]" maxlength="15" onkeyup="this.value=this.value.replace(/[^+\\d]/g,'')" placeholder="Mobile Number" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Relation:</label>
                            <select class="form-control relation-select" name="famliypersons[${i}][relationship_id]">
                                <option value="" disabled selected>Select Relation</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Shakh:</label>
                            <select class="form-control shakh-select" name="famliypersons[${i}][shakh_id]">
                                <option value="" disabled selected>Select Shakh</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2 d-flex align-items-end">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input main-member-check" id="main_member_${i}" name="famliypersons[${i}][main_family_member]" value="1">
                                <label class="custom-control-label" for="main_member_${i}">Main Family Member</label>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2 member-fields" style="display:none;">
                            <label class="form-label">Lifetime Member No:</label>
                            <input type="text" class="form-control f-lifetime_member_no" name="famliypersons[${i}][lifetime_member_no]" placeholder="Lifetime Member No" />
                        </div>
                        <div class="col-sm-4 mb-2 member-fields" style="display:none;">
                            <label class="form-label">Receipt Number:</label>
                            <input type="text" class="form-control f-receipt_number" name="famliypersons[${i}][receipt_number]" placeholder="Receipt Number" />
                        </div>
                    </div>

                    <div class="person-section-title">Address Details</div>
                    <div class="form-row">
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">House No / Building:</label>
                            <input type="text" class="form-control f-address_house_no_building_name" name="famliypersons[${i}][address_house_no_building_name]" placeholder="House No / Building" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Landmark:</label>
                            <input type="text" class="form-control f-address_landmark" name="famliypersons[${i}][address_landmark]" placeholder="Landmark" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Area:</label>
                            <input type="text" class="form-control f-address_area" name="famliypersons[${i}][address_area]" placeholder="Area" />
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
                            <input type="number" class="form-control f-address_pincode" name="famliypersons[${i}][address_pincode]" placeholder="Pincode" />
                        </div>
                    </div>

                    <div class="person-section-title">Business / Company Details</div>
                    <div class="form-row">
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Job Role:</label>
                            <input type="text" class="form-control f-job_role" name="famliypersons[${i}][job_role]" placeholder="Job Role" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Company Name:</label>
                            <input type="text" class="form-control f-company_name" name="famliypersons[${i}][company_name]" placeholder="Company Name" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Company Building:</label>
                            <input type="text" class="form-control f-company_house_no_building_name" name="famliypersons[${i}][company_house_no_building_name]" placeholder="Building Name" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Company Landmark:</label>
                            <input type="text" class="form-control f-company_landmark" name="famliypersons[${i}][company_landmark]" placeholder="Landmark" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Company Area:</label>
                            <input type="text" class="form-control f-company_area" name="famliypersons[${i}][company_area]" placeholder="Area" />
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
                            <input type="number" class="form-control f-company_pincode" name="famliypersons[${i}][company_pincode]" placeholder="Pincode" />
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
                            <textarea class="form-control f-business_intro" name="famliypersons[${i}][business_intro]" rows="2" placeholder="Business Introduction"></textarea>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Services:</label>
                            <input type="text" class="form-control f-services" name="famliypersons[${i}][services]" placeholder="Services" />
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">SEO Keywords:</label>
                            <input type="text" class="form-control f-seo_keywords" name="famliypersons[${i}][seo_keywords]" placeholder="SEO Keywords" />
                        </div>
                    </div>

                    <div class="person-section-title">Work &amp; Education Details</div>
                    <div class="form-row">
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Work Type:</label>
                            <select class="form-control f-work_type work-type-select" name="famliypersons[${i}][work_type]">
                                <option value="" disabled selected>Select Work Type</option>
                                <option value="job">Job</option>
                                <option value="business">Business</option>
                                <option value="homemaker">Homemaker</option>
                                <option value="retired">Retired</option>
                                <option value="student">Student</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2 retired-fields" style="display:none;">
                            <label class="form-label">Retired Title:</label>
                            <input type="text" class="form-control f-retired_title" name="famliypersons[${i}][retired_title]" placeholder="Previous Work Title" />
                        </div>
                        <div class="col-sm-4 mb-2 retired-fields" style="display:none;">
                            <label class="form-label">Retired Description:</label>
                            <input type="text" class="form-control f-retired_description" name="famliypersons[${i}][retired_description]" placeholder="Previous Work Description" />
                        </div>
                        <div class="col-sm-4 mb-2 study-fields" style="display:none;">
                            <label class="form-label">Study Level:</label>
                            <select class="form-control f-study_level" name="famliypersons[${i}][study_level]">
                                <option value="" disabled selected>Select Level</option>
                                <option value="school">School</option>
                                <option value="college">College</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2 study-fields" style="display:none;">
                            <label class="form-label">Institute:</label>
                            <input type="text" class="form-control f-study_institute" name="famliypersons[${i}][study_institute]" placeholder="School / College Name" />
                        </div>
                        <div class="col-sm-4 mb-2 study-fields" style="display:none;">
                            <label class="form-label">Course:</label>
                            <input type="text" class="form-control f-study_course" name="famliypersons[${i}][study_course]" placeholder="Standard / Course / Field" />
                        </div>
                        <div class="col-sm-4 mb-2 study-fields" style="display:none;">
                            <label class="form-label">Year / Semester:</label>
                            <input type="text" class="form-control f-study_year" name="famliypersons[${i}][study_year]" placeholder="e.g. Second Year" />
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Education:</label>
                            <input type="text" class="form-control f-education" name="famliypersons[${i}][education]" placeholder="Highest Education" />
                        </div>
                    </div>
                </div>`;
            }

            function wirePersonCard(card) {
                fillSelect(card.find('.address-country-select'), countries, 'id', 'country_name', 'Select Country');
                fillSelect(card.find('.company-country-select'), countries, 'id', 'country_name', 'Select Country');
                fillSelect(card.find('.relation-select'), relations, 'id', 'relation', 'Select Relation');
                fillSelect(card.find('.business-category-select'), businessCategories, 'id', 'name', 'Select Category');
                fillSelect(card.find('.shakh-select'), shakhs, 'id', 'name', 'Select Shakh');

                card.find('.work-type-select').on('change', function() {
                    var cardEl = $(this).closest('.person-card');
                    cardEl.find('.retired-fields').toggle($(this).val() === 'retired');
                    cardEl.find('.study-fields').toggle($(this).val() === 'student');
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
                    loadSubcategories($(this).val(), card.find('.business-subcategory-select'), '');
                });
            }

            function addPersonCard(person) {
                var card = $(personTemplate(personIndex));
                $('#personsContainer').append(card);
                wirePersonCard(card);

                if (person) {
                    card.find('.person-id-input').val(person.id);
                    card.find('.f-first_name').val(person.first_name);
                    card.find('.f-last_name').val(person.last_name);
                    card.find('.f-surname').val(person.surname);
                    card.find('.f-dob').val(person.dob ? String(person.dob).split('T')[0] : '');
                    card.find('.f-age').val(person.age);
                    card.find('.f-gender').val(person.gender);
                    card.find('.f-marital_status').val(person.marital_status);
                    card.find('.f-email').val(person.email);
                    card.find('.f-mobile').val(person.mobile);
                    card.find('.relation-select').val(person.relationship_id);
                    card.find('.shakh-select').val(person.shakh_id);
                    card.find('.f-work_type').val(person.work_type).trigger('change');
                    card.find('.f-retired_title').val(person.retired_title);
                    card.find('.f-retired_description').val(person.retired_description);
                    card.find('.f-study_level').val(person.study_level);
                    card.find('.f-study_institute').val(person.study_institute);
                    card.find('.f-study_course').val(person.study_course);
                    card.find('.f-study_year').val(person.study_year);
                    card.find('.f-education').val(person.education);
                    card.find('.f-address_house_no_building_name').val(person
                        .address_house_no_building_name);
                    card.find('.f-address_landmark').val(person.address_landmark);
                    card.find('.f-address_area').val(person.address_area);
                    card.find('.address-country-select').val(person.address_country_id);
                    card.find('.f-address_pincode').val(person.address_pincode);
                    card.find('.f-job_role').val(person.job_role);
                    card.find('.f-company_name').val(person.company_name);
                    card.find('.f-company_house_no_building_name').val(person
                        .company_house_no_building_name);
                    card.find('.f-company_landmark').val(person.company_landmark);
                    card.find('.f-company_area').val(person.company_area);
                    card.find('.company-country-select').val(person.company_country_id);
                    card.find('.f-company_pincode').val(person.company_pincode);
                    card.find('.business-category-select').val(person.business_category);
                    card.find('.f-business_intro').val(person.business_intro);
                    card.find('.f-services').val(person.services);
                    card.find('.f-seo_keywords').val(person.seo_keywords);

                    if (person.main_family_member == 1) {
                        card.find('.main-member-check').prop('checked', true).trigger('change');
                        card.find('.f-lifetime_member_no').val(person.lifetime_member_no || '');
                        card.find('.f-receipt_number').val(person.receipt_number || '');
                    }

                    // cascade select chains
                    if (person.address_country_id) {
                        loadStates(person.address_country_id, card.find('.address-state-select'),
                            person.address_state_id, card.find('.address-city-select'), person
                            .address_city_id);
                    }
                    if (person.company_country_id) {
                        loadStates(person.company_country_id, card.find('.company-state-select'),
                            person.company_state_id, card.find('.company-city-select'), person
                            .company_city_id);
                    }
                    if (person.business_category) {
                        loadSubcategories(person.business_category, card.find(
                            '.business-subcategory-select'), person.business_subcategory);
                    }
                }

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

            $('#addpersonbtn').on('click', function() {
                addPersonCard(null);
            });

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

            // init: load dropdowns + existing family data
            loadershow();
            $.when(loadCountries(), loadRelations(), loadBusinessCategories(), loadShakhs()).always(function() {
                let familyEditUrl = "{{ route('family.edit', '__editId__') }}".replace('__editId__',
                    edit_id);
                $.ajax({
                    type: 'GET',
                    url: familyEditUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.family && response.family
                            .familyPersons) {
                            $.each(response.family.familyPersons, function(key, person) {
                                addPersonCard(person);
                            });
                            if ($('#personsContainer .person-card').length == 0) {
                                addPersonCard(null);
                            }
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
            });

            //submit form
            $('#familyupdateform').submit(function(event) {
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

                let familyUpdateUrl = "{{ route('family.update', '__editId__') }}".replace('__editId__',
                    edit_id);
                // omit empty date/int fields so api does not update '' into date/int columns
                var intDateFields = ['dob', 'age', 'address_country_id', 'address_state_id',
                    'address_city_id', 'address_pincode', 'company_country_id',
                    'company_state_id', 'company_city_id', 'company_pincode',
                    'business_category', 'business_subcategory', 'relationship_id', 'shakh_id'
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
                var formData = $(this).serialize();
                $(emptied).prop('disabled', false);
                ajaxRequest('PUT', familyUpdateUrl, formData)
                    .done(function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Family updated successfully!'
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
