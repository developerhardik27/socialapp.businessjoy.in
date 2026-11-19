@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Update Member
@endsection
@section('title')
    Update Member
@endsection

@section('style')
    <style>
        .person-section-title {
            font-size: 13px;
            font-weight: 600;
            color: #253566;
            margin: 10px 0 5px;
        }
    </style>
@endsection

@section('form-content')
    <form id="memberupdateform">
        @csrf
        <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
        <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
        <input type="hidden" name="token" value="{{ session('api_token') }}" />
        <input type="hidden" name="family_id" id="family_id" />
        <input type="hidden" name="family_person_id" id="family_person_id" />
        <input type="hidden" name="main_family_member" id="main_family_member" value="1" />

        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-12">
                    <div class="person-section-title">Member Details</div>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Lifetime Member No:</label>
                    <input type="text" class="form-control" name="lifetime_member_no" id="lifetime_member_no"
                        placeholder="Lifetime Member No" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Receipt Number:</label>
                    <input type="text" class="form-control" name="receipt_number" id="receipt_number"
                        placeholder="Receipt Number" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Shakh:</label>
                    <select class="form-control" name="shakh_id" id="shakh_id">
                        <option value="" disabled selected>Select Shakh</option>
                    </select>
                </div>

                <div class="col-sm-12">
                    <div class="person-section-title">Personal Details</div>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">First Name:</label><span style="color:red;"> *</span>
                    <input type="text" class="form-control" name="first_name" id="first_name"
                        placeholder="First Name" required />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Last Name:</label>
                    <input type="text" class="form-control" name="last_name" id="last_name"
                        placeholder="Last Name" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Surname:</label>
                    <input type="text" class="form-control" name="surname" id="surname" placeholder="Surname" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">DOB:</label>
                    <input type="date" class="form-control" name="dob" id="dob" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Age:</label>
                    <input type="number" class="form-control" name="age" id="age" min="0"
                        placeholder="Age" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Gender:</label>
                    <select class="form-control" name="gender" id="gender">
                        <option value="" disabled selected>Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Marital Status:</label>
                    <select class="form-control" name="marital_status" id="marital_status">
                        <option value="" disabled selected>Select Marital Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                        <option value="Divorced">Divorced</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Email:</label>
                    <input type="email" class="form-control" name="email" id="email" placeholder="Email" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Mobile:</label>
                    <input type="text" class="form-control" name="mobile" id="mobile" maxlength="15"
                        onkeyup="this.value=this.value.replace(/[^+\d]/g,'')" placeholder="Mobile Number" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Relation:</label>
                    <select class="form-control" name="relationship_id" id="relationship_id">
                        <option value="" disabled selected>Select Relation</option>
                    </select>
                </div>

                <div class="col-sm-12">
                    <div class="person-section-title">Address Details</div>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">House No / Building:</label>
                    <input type="text" class="form-control" name="address_house_no_building_name"
                        id="address_house_no_building_name" placeholder="House No / Building" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Landmark:</label>
                    <input type="text" class="form-control" name="address_landmark" id="address_landmark"
                        placeholder="Landmark" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Area:</label>
                    <input type="text" class="form-control" name="address_area" id="address_area"
                        placeholder="Area" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Country:</label>
                    <select class="form-control" name="address_country_id" id="address_country_id">
                        <option value="" disabled selected>Select Country</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">State:</label>
                    <select class="form-control" name="address_state_id" id="address_state_id">
                        <option value="" disabled selected>Select State</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">City:</label>
                    <select class="form-control" name="address_city_id" id="address_city_id">
                        <option value="" disabled selected>Select City</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Pincode:</label>
                    <input type="number" class="form-control" name="address_pincode" id="address_pincode"
                        placeholder="Pincode" />
                </div>

                <div class="col-sm-12">
                    <div class="person-section-title">Business / Company Details</div>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Job Role:</label>
                    <input type="text" class="form-control" name="job_role" id="job_role"
                        placeholder="Job Role" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Company Name:</label>
                    <input type="text" class="form-control" name="company_name" id="company_name"
                        placeholder="Company Name" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Company Building:</label>
                    <input type="text" class="form-control" name="company_house_no_building_name"
                        id="company_house_no_building_name" placeholder="Building Name" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Company Landmark:</label>
                    <input type="text" class="form-control" name="company_landmark" id="company_landmark"
                        placeholder="Landmark" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Company Area:</label>
                    <input type="text" class="form-control" name="company_area" id="company_area"
                        placeholder="Area" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Company Country:</label>
                    <select class="form-control" name="company_country_id" id="company_country_id">
                        <option value="" disabled selected>Select Country</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Company State:</label>
                    <select class="form-control" name="company_state_id" id="company_state_id">
                        <option value="" disabled selected>Select State</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Company City:</label>
                    <select class="form-control" name="company_city_id" id="company_city_id">
                        <option value="" disabled selected>Select City</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Company Pincode:</label>
                    <input type="number" class="form-control" name="company_pincode" id="company_pincode"
                        placeholder="Pincode" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Business Category:</label>
                    <select class="form-control" name="business_category" id="business_category">
                        <option value="" disabled selected>Select Category</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Business Subcategory:</label>
                    <select class="form-control" name="business_subcategory" id="business_subcategory">
                        <option value="" disabled selected>Select Subcategory</option>
                    </select>
                </div>
                <div class="col-sm-12 mb-2">
                    <label class="form-label">Business Intro:</label>
                    <textarea class="form-control" name="business_intro" id="business_intro" rows="2"
                        placeholder="Business Introduction"></textarea>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label">Services:</label>
                    <input type="text" class="form-control" name="services" id="services"
                        placeholder="Services" />
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label">SEO Keywords:</label>
                    <input type="text" class="form-control" name="seo_keywords" id="seo_keywords"
                        placeholder="SEO Keywords" />
                </div>

                <div class="col-sm-12">
                    <div class="person-section-title">Work &amp; Education Details</div>
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Work Type:</label>
                    <select class="form-control" name="work_type" id="work_type">
                        <option value="" disabled selected>Select Work Type</option>
                        <option value="job">Job</option>
                        <option value="business">Business</option>
                        <option value="homemaker">Homemaker</option>
                        <option value="retired">Retired</option>
                        <option value="student">Student</option>
                        <option value="none">None</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2" id="retired_title_wrap" style="display:none;">
                    <label class="form-label">Retired Title:</label>
                    <input type="text" class="form-control" name="retired_title" id="retired_title"
                        placeholder="Previous Work Title" />
                </div>
                <div class="col-sm-4 mb-2" id="retired_description_wrap" style="display:none;">
                    <label class="form-label">Retired Description:</label>
                    <input type="text" class="form-control" name="retired_description" id="retired_description"
                        placeholder="Previous Work Description" />
                </div>
                <div class="col-sm-4 mb-2" id="study_level_wrap" style="display:none;">
                    <label class="form-label">Study Level:</label>
                    <select class="form-control" name="study_level" id="study_level">
                        <option value="" disabled selected>Select Level</option>
                        <option value="school">School</option>
                        <option value="college">College</option>
                    </select>
                </div>
                <div class="col-sm-4 mb-2" id="study_institute_wrap" style="display:none;">
                    <label class="form-label">Institute:</label>
                    <input type="text" class="form-control" name="study_institute" id="study_institute"
                        placeholder="School / College Name" />
                </div>
                <div class="col-sm-4 mb-2" id="study_course_wrap" style="display:none;">
                    <label class="form-label">Course:</label>
                    <input type="text" class="form-control" name="study_course" id="study_course"
                        placeholder="Standard / Course / Field" />
                </div>
                <div class="col-sm-4 mb-2" id="study_year_wrap" style="display:none;">
                    <label class="form-label">Year / Semester:</label>
                    <input type="text" class="form-control" name="study_year" id="study_year"
                        placeholder="e.g. Second Year" />
                </div>
                <div class="col-sm-4 mb-2">
                    <label class="form-label">Education:</label>
                    <input type="text" class="form-control" name="education" id="education"
                        placeholder="Highest Education" />
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
            // edit_id here is family_person_id (member list api returns person id)
            var person_id = @json($edit_id);
            var edit_id = null; // resolved members.id
            var memberData = null;

            // redirect on member page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societymember') }}";
            });

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

            // change handlers for cascading selects
            $('#address_country_id').on('change', function() {
                loadStates($(this).val(), $('#address_state_id'), '', $('#address_city_id'), '');
            });
            $('#address_state_id').on('change', function() {
                loadCities($(this).val(), $('#address_city_id'), '');
            });
            $('#company_country_id').on('change', function() {
                loadStates($(this).val(), $('#company_state_id'), '', $('#company_city_id'), '');
            });
            $('#company_state_id').on('change', function() {
                loadCities($(this).val(), $('#company_city_id'), '');
            });
            $('#business_category').on('change', function() {
                loadSubcategories($(this).val(), $('#business_subcategory'), '');
            });
            $('#work_type').on('change', function() {
                var v = $(this).val();
                $('#retired_title_wrap, #retired_description_wrap').toggle(v === 'retired');
                $('#study_level_wrap, #study_institute_wrap, #study_course_wrap, #study_year_wrap')
                    .toggle(v === 'student');
            });

            // load static dropdowns
            function loadCountries() {
                return $.ajax({
                    type: 'GET',
                    url: "{{ route('country.index') }}",
                    success: function(response) {
                        if (response.status == 200 && response.country) {
                            fillSelect($('#address_country_id'), response.country, 'id',
                                'country_name', 'Select Country');
                            fillSelect($('#company_country_id'), response.country, 'id',
                                'country_name', 'Select Country');
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
                            fillSelect($('#relationship_id'), response.relations, 'id', 'relation',
                                'Select Relation');
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
                            fillSelect($('#shakh_id'), response.data, 'id', 'name',
                                'Select Shakh');
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
                            fillSelect($('#business_category'), response.businesscategories, 'id',
                                'name', 'Select Category');
                        }
                    }
                });
            }

            // resolve members.id from family_person_id via karobari member list
            function resolveMemberId() {
                return $.ajax({
                    type: 'GET',
                    url: "{{ route('karobarimember.loadmemberforkarobari') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.members) {
                            $.each(response.members, function(key, m) {
                                if (m.id == person_id) {
                                    edit_id = m.member_id;
                                    return false;
                                }
                            });
                        }
                    },
                    error: function() {
                        edit_id = null;
                    }
                });
            }

            // load member data and fill form
            function loaddata() {
                // fallback: if member id could not be resolved, try the given id
                if (!edit_id) {
                    edit_id = person_id;
                }
                let memberEditUrl = "{{ route('member.edit', '__editId__') }}".replace('__editId__',
                    edit_id);
                $.ajax({
                    type: 'GET',
                    url: memberEditUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.data) {
                            memberData = response.data;
                            $('#family_id').val(memberData.family_id);
                            $('#family_person_id').val(memberData.family_person_id);
                            $('#lifetime_member_no').val(memberData.lifetime_member_no);
                            $('#receipt_number').val(memberData.receipt_number);
                            $('#shakh_id').val(memberData.shakh_id);
                            $('#work_type').val(memberData.work_type).trigger('change');
                            $('#retired_title').val(memberData.retired_title);
                            $('#retired_description').val(memberData.retired_description);
                            $('#study_level').val(memberData.study_level);
                            $('#study_institute').val(memberData.study_institute);
                            $('#study_course').val(memberData.study_course);
                            $('#study_year').val(memberData.study_year);
                            $('#education').val(memberData.education);
                            $('#first_name').val(memberData.first_name);
                            $('#last_name').val(memberData.last_name);
                            $('#surname').val(memberData.surname);
                            $('#dob').val(memberData.dob ? String(memberData.dob).split('T')[0] :
                                '');
                            $('#age').val(memberData.age);
                            $('#gender').val(memberData.gender);
                            $('#marital_status').val(memberData.marital_status);
                            $('#email').val(memberData.email);
                            $('#mobile').val(memberData.mobile);
                            $('#relationship_id').val(memberData.relationship_id);
                            $('#address_house_no_building_name').val(memberData
                                .address_house_no_building_name);
                            $('#address_landmark').val(memberData.address_landmark);
                            $('#address_area').val(memberData.address_area);
                            $('#address_country_id').val(memberData.address_country_id);
                            $('#address_pincode').val(memberData.address_pincode);
                            $('#job_role').val(memberData.job_role);
                            $('#company_name').val(memberData.company_name);
                            $('#company_house_no_building_name').val(memberData
                                .company_house_no_building_name);
                            $('#company_landmark').val(memberData.company_landmark);
                            $('#company_area').val(memberData.company_area);
                            $('#company_country_id').val(memberData.company_country_id);
                            $('#company_pincode').val(memberData.company_pincode);
                            $('#business_category').val(memberData.business_category);
                            $('#business_intro').val(memberData.business_intro);
                            $('#services').val(memberData.services);
                            $('#seo_keywords').val(memberData.seo_keywords);

                            if (memberData.address_country_id) {
                                loadStates(memberData.address_country_id, $('#address_state_id'),
                                    memberData.address_state_id, $('#address_city_id'),
                                    memberData.address_city_id);
                            }
                            if (memberData.company_country_id) {
                                loadStates(memberData.company_country_id, $('#company_state_id'),
                                    memberData.company_state_id, $('#company_city_id'),
                                    memberData.company_city_id);
                            }
                            if (memberData.business_category) {
                                loadSubcategories(memberData.business_category, $(
                                    '#business_subcategory'), memberData.business_subcategory);
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
            }

            loadershow();
            $.when(loadCountries(), loadRelations(), loadBusinessCategories(), loadShakhs(), resolveMemberId())
                .always(function() {
                    loaddata();
                });

            //submit form
            $('#memberupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                let memberUpdateUrl = "{{ route('member.update', '__editId__') }}".replace('__editId__',
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
                ajaxRequest('PUT', memberUpdateUrl, formData)
                    .done(function(response) {
                        if (response.status == 200) {
                            loaderhide();
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Member updated successfully!'
                            });
                            window.location = "{{ route('admin.societymember') }}";
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
