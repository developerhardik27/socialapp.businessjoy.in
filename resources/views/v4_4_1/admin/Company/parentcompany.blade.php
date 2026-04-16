@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterpage')
@section('page_title')
    {{ config('app.name') }} - Manage Parent Company
@endsection
@section('style')
    <link rel="stylesheet" href="{{ asset('admin/css/select2.min.css') }}">
    <style>
        .btn-info {
            background-color: #253566 !important;
            border-color: #253566 !important;
            color: white !important;
        }

        .btn-info:hover {
            background-color: #39519b !important;
            color: rgb(255, 255, 255);
        }

        .version-section {
            margin-bottom: 2rem;
        }

        .main-version {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #007bff;
        }

        .sub-versions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .version-card {
            padding: 1rem;
            border: 1px solid #17a2b8;
            background-color: #e9f7fc;
            border-radius: 6px;
            text-align: center;
            min-width: 160px;
        }

        .version-card a {
            text-decoration: none;
        }
    </style>
@endsection

@section('page-content')
    <div id="content-page" class="content-page ">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12 col-lg-12">
                    <div class="iq-card">
                        <div class="iq-card-header d-flex justify-content-between">
                            <div class="iq-header-title">
                                <h4 class="card-title">Manage Parent Company</h4>
                            </div>
                        </div>
                        <div class="iq-card-body">
                            <form id="parentcompanyform" name="parentcompanyform">
                                @csrf
                                <div class="form-group">
                                    <div class="form-row">
                                        <div class="col-sm-4">
                                            <input type="hidden" name="company_id" class="form-control"
                                                value="{{ session('company_id') }}" placeholder="company id" required />
                                            <input type="hidden" name="user_id" class="form-control"
                                                value="{{ session('user_id') }}" placeholder="user id" required />
                                            <input type="hidden" name="token" class="form-control"
                                                value="{{ session('api_token') }}" placeholder="token" required />
                                            <label for="company">Company</label><span style="color:red;">*</span>
                                            <select class="form-control select2" id="company" name="company" required>
                                                <option selected disabled> Select Company</option>
                                            </select>
                                            <span class="error-msg" id="error-company" style="color: red"></span>
                                        </div> 
                                    </div>
                                    <div class="form-row">
                                        <div class="col-sm-12 mt-2">
                                            <button type="reset" data-toggle="tooltip" data-placement="bottom"
                                                data-original-title="Reset" class="btn iq-bg-danger float-right mr-2"
                                                id="resetBtn">Reset</button>
                                            <button type="submit" data-toggle="tooltip" data-placement="bottom"
                                                data-original-title="Save"
                                                class="btn btn-primary float-right my-0">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            Parent Company : <span id="parentcompany">Not set yet</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('ajax')
    <script src="{{ asset('admin/js/select2.min.js') }}"></script>
    <script>
        $('document').ready(function() {

            var global_response = ''; // declare global variable for using company detail globally
           
            // function for get company data and load into table
            function loaddata() {
                loadershow();
                $.ajax({
                    type: 'GET',
                    url: "{{ route('company.companylist') }}",
                    data: {
                        user_id: {{ session()->get('user_id') }},
                        company_id: {{ session()->get('company_id') }},
                        token: "{{ session()->get('api_token') }}",
                        parent_company : true
                    },
                    success: function(response) {
                        // You can update your HTML with the data here if needed
                        if (response.status == 200 && response.company != '') {
                            global_response = response;
                            $('#company').html(
                                `<option selected=""> Select Company</option>`);
                            $.each(response.company, function(key, company) {
                                var companydetails = [
                                    company.name,
                                    company.contact_no,
                                    company.email,
                                    company.app_version ? company.app_version.replace(/_/g,'.') : null
                                ].filter(Boolean).join(' - ');
                                $('#company').append(`
                                     <option value="${company.id}">${companydetails}</option>
                                `);
                                if(company.parent_company_name){
                                    $('#parentcompany').text(company.parent_company_name);
                                }
                            }); 
                            $('.select2').select2();
                        } else if (response.status == 500) {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        } else {
                            $('#company').html(`<option>No Data Found<option>`);
                        }
                        loaderhide();
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr.responseText); // Log the full error response for debugging
                        handleAjaxError(xhr);
                    }
                });
            }
            //call loaddata function for make ajax call
            loaddata(); // this function is for get company details


            $('#resetBtn').on('click', function(e) {
                e.preventDefault();
                $('#parentcompanyform')[0].reset();
                $('.select2').select2();
            })

            $('#parentcompanyform').submit(function(event) {
                event.preventDefault();
                showConfirmationDialog(
                    'Are you sure?', // Title
                    'to change this company version?', // Text
                    'Yes, change', // Confirm button text
                    'No, cancel', // Cancel button text
                    'question', // Icon type (question icon)
                    () => {
                        // Success callback
                        loadershow();
                        $('.error-msg').text('');
                        const formdata = $(this).serialize();
                        $.ajax({
                            type: 'PUT',
                            url: "{{ route('company.updateparentcompany') }}",
                            data: formdata,
                            success: function(response) {
                                // Handle the response from the server
                                if (response.status == 200) {
                                    // You can perform additional actions, such as showing a success message or redirecting the user
                                    Toast.fire({
                                        icon: "success",
                                        title: response.message
                                    });
                                    $('#parentcompanyform')[0].reset();
                                    loaddata();
                                } else if (response.status == 500) {
                                    Toast.fire({
                                        icon: "error",
                                        title: response.message
                                    });
                                } else {
                                    Toast.fire({
                                        icon: "error",
                                        title: "something went wrong!"
                                    });
                                }
                                loaderhide();
                            },
                            error: function(xhr, status,
                                error) { // if calling api request error 
                                loaderhide();
                                console.log(xhr
                                    .responseText
                                ); // Log the full error response for debugging
                                handleAjaxError(xhr);
                            }
                        });
                    }
                );
            });

        });
    </script>
@endpush
