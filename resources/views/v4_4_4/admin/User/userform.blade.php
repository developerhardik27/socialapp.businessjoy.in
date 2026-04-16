@php
    $folder = session('folder_name');
    if (
        session('user_permissions.adminmodule.company.max') &&
        session('user_permissions.adminmodule.company.max') == '1'
    ) {
        $rowspan = 3;
    } else {
        $rowspan = 1;
    }

@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    Add New User
@endsection
@section('title')
    New User
@endsection

@section('style')
    <style>
        .password-container {
            position: relative;
        }

        /* Style for the eye icon */
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            /* Adjust color as needed */
        }

        .special-color {
            color: #6546d2;
        }

        table,
        table th,
        table td {
            border-right: transparent !important;
            border-left: transparent !important;
        }
    </style>
@endsection

@section('form-content')

    @if ($allow == 'no')
        <p class="text-primary">You are reached your max user limit</p>
    @else
        <form id="userform" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <div class="form-row">
                    <div class="col-sm-6 mb-2">
                        <input type="hidden" name="token" class="form-control" value="{{ session('api_token') }}"
                            placeholder="token" required />
                        <input type="hidden" value="{{ $user_id }}" name="user_id" class="form-control">
                        <input type="hidden" value="{{ $company_id }}" name="company_id" class="form-control">
                        <label for="firstname">FirstName</label><span style="color:red;">*</span>
                        <input type="text" id="firstname" name='firstname' class="form-control" placeholder="First name"
                            required />
                        <span class="error-msg" id="error-firstname" style="color: red"></span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="lastname">LastName</label><span style="color:red;">*</span>
                        <input type="text" id="lastname" name='lastname' class="form-control" placeholder="Last name"
                            required />
                        <span class="error-msg" id="error-lastname" style="color: red"></span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="email">Email</label><span style="color:red;">*</span>
                        <input type="email" name='email' class="form-control" id="email" value=""
                            placeholder="Enter Email" autocomplete="off" required />
                        <span class="error-msg" id="error-email" style="color: red"></span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="password">Password</label>
                        <div class="password-container">
                            <input type="password" name="password" class="form-control" id="password" value=""
                                placeholder="Enter Password" autocomplete="new-password" />
                            <i class="toggle-password fa fa-eye-slash" onclick="togglePasswordVisibility()"></i>
                        </div>
                        <span class="error-msg" id="error-password" style="color: red"></span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="contact_number">Contact Number</label><span style="color:red;">*</span>
                        <input type="tel" name='contact_number' class="form-control" id="contact_number" value=""
                            placeholder="0123456789" required />
                        <span class="error-msg" id="error-contact_number" style="color: red"></span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="country">Select Country</label><span style="color:red;">*</span>
                        <select id="country" class="form-control" name='country' required>
                            <option selected="" disabled="">Select your Country</option>
                        </select>
                        <span class="error-msg" id="error-country" style="color: red"></span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="state">Select State</label><span style="color:red;">*</span>
                        <select class="form-control" name='state' id="state" required>
                            <option selected="" disabled="">Select your State</option>
                        </select>
                        <span class="error-msg" id="error-state" style="color: red"></span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="city">Select City</label><span style="color:red;">*</span>
                        <select class="form-control" name='city' id="city" required>
                            <option selected="" disabled="">Select your City</option>
                        </select>
                        <span class="error-msg" id="error-city" style="color: red"></span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="pincode">Pincode</label><span style="color:red;">*</span>
                        <input type="text" id="pincode" name='pincode' class="form-control" placeholder="Pin Code"
                            required />
                        <span class="error-msg" id="error-pincode" style="color: red"></span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="img">Image</label><br>
                        <input type="file" name="img" id="img" width="100%" />
                        <p class="text-primary">Please select a photo file (JPG, JPEG, or PNG) that is smaller than 1 MB.
                        </p>
                        <span class="error-msg" id="error-img" style="color: red"></span>
                    </div>
                    <div class="col-sm-12">
                        <button type="button" data-toggle="tooltip" data-placement="bottom"
                            data-original-title="Cancel" class="btn btn-secondary float-right cancelbtn">
                            Cancel
                        </button>
                        <button type="reset" id="formreset" data-toggle="tooltip" data-placement="bottom"
                            data-original-title="Reset" class="btn iq-bg-danger float-right resetbtn mr-2">
                            Reset
                        </button>
                        <button type="submit" id="formsubmit" data-toggle="tooltip" data-placement="bottom"
                            data-original-title="Save" class="btn btn-primary float-right my-0 submitBtn">
                            Save
                        </button>
                    </div>
                </div>
            </div>

            @if (session('user_permissions.adminmodule.userpermission.view') == '1' ||
                    session('user_permissions.adminmodule.userpermission.add') == '1' ||
                    $user_id == 1)
                <div class="form-group">
                    <div class="form-row">
                        <div class="col-sm-6 mb-2">
                            <label for="user_role_permission">User Permission Group</label>
                            <select type="text" id="user_role_permission" name='user_role_permission'
                                class="form-control">
                                <option value="" selected>Select User Role</option>
                            </select>
                            <span class="error-msg" id="error-user_role_permission" style="color: red"></span>
                        </div>
                    </div>
                </div>

                <div class="row permission-row">
                    <div class="col-sm-12">
                        @if ((Session::has('admin') && Session::get('admin') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Admin Modules</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right"><b>Select All </b>
                                                    <input type="checkbox" id="adminallcheck" data-module="admin"
                                                        class="allcheck">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15%">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="admincheckboxes">
                                            @if (session('user_permissions.adminmodule.admindashboard.add') == '1' || $user_id == 1)
                                                <tr id="admindashboard">
                                                    <td>Admin Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='admindashboard' id="showadmindashboardmenu"
                                                            name="showadmindashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.admindashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showadmindashboardmenu' id="addadmindashboard"
                                                                name="addadmindashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.admindashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showadmindashboardmenu'
                                                                id="viewadmindashboard" name="viewadmindashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.admindashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showadmindashboardmenu'
                                                                id="editadmindashboard" name="editadmindashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.admindashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showadmindashboardmenu'
                                                                id="deleteadmindashboard" name="deleteadmindashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.admindashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showadmindashboardmenu'
                                                                id="alldataadmindashboard" name="alldataadmindashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.adminmodule.company.add') == '1' || $user_id == 1)
                                                <tr id="company">
                                                    <td rowspan="{{ $rowspan }}">Company</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='company'
                                                            id="showcompanymenu" name="showcompanymenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.company.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcompanymenu' id="addcompany"
                                                                name="addcompany" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.company.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcompanymenu' id="viewcompany"
                                                                name="viewcompany" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.company.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcompanymenu' id="editcompany"
                                                                name="editcompany" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.company.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcompanymenu' id="deletecompany"
                                                                name="deletecompany" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.company.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcompanymenu' id="alldatacompany"
                                                                name="alldatacompany" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                                @if (
                                                    (Session::has('user_permissions.adminmodule.company.max') &&
                                                        session('user_permissions.adminmodule.company.max') == '1') ||
                                                        $user_id == 1)
                                                    <tr>
                                                        <td>Max Users</td>
                                                        {{-- add more option title here if needed --}}
                                                    </tr>
                                                    <tr id="company">
                                                        <td>
                                                            @if (session('user_permissions.adminmodule.company.max') == '1' || $user_id == 1)
                                                                <input type="checkbox" class="clicksubmenu"
                                                                    data-value='showcompanymenu' id="maxcompany"
                                                                    name="maxuser" value="1">
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        {{-- add more checkboxe title here if needed --}}
                                                    </tr>
                                                @endif
                                            @endif

                                            @if (session('user_permissions.adminmodule.user.add') == '1' || $user_id == 1)
                                                <tr id="user">
                                                    <td>User</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='user'
                                                            id="showusermenu" name="showusermenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.user.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showusermenu' id="adduser" name="adduser"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.user.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showusermenu' id="viewuser" name="viewuser"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.user.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showusermenu' id="edituser" name="edituser"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.user.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showusermenu' id="deleteuser"
                                                                name="deleteuser" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.user.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showusermenu' id="alldatauser"
                                                                name="alldatauser" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if ((Session::has('admin_role') && Session::get('admin_role') == 1) || $user_id == 1)
                                                <tr id="techsupport">
                                                    <td>Tech support</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='techsupport'
                                                            id="showtechsupportmenu" name="showtechsupportmenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="clicksubmenu"
                                                            data-value='showtechsupportmenu' id="addtechsupport"
                                                            name="addtechsupport" value="1">
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="clicksubmenu"
                                                            data-value='showtechsupportmenu' id="viewtechsupport"
                                                            name="viewtechsupport" value="1">
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="clicksubmenu"
                                                            data-value='showtechsupportmenu' id="edittechsupport"
                                                            name="edittechsupport" value="1">
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="clicksubmenu"
                                                            data-value='showtechsupportmenu' id="deletetechsupport"
                                                            name="deletetechsupport" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.user.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtechsupportmenu' id="alldatatechsupport"
                                                                name="alldatatechsupport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.adminmodule.userpermission.add') == '1' || $user_id == 1)
                                                <tr id="userpermission">
                                                    <td>User Permission</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='userpermission' id="showuserpermissionmenu"
                                                            name="showuserpermissionmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.userpermission.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showuserpermissionmenu' id="adduserpermission"
                                                                name="adduserpermission" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.userpermission.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showuserpermissionmenu'
                                                                id="viewuserpermission" name="viewuserpermission"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.userpermission.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showuserpermissionmenu'
                                                                id="edituserpermission" name="edituserpermission"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.userpermission.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showuserpermissionmenu'
                                                                id="deleteuserpermission" name="deleteuserpermission"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.userpermission.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showuserpermissionmenu'
                                                                id="alldatauserpermission" name="alldatauserpermission"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.adminmodule.adminapi.add') == '1' || $user_id == 1)
                                                <tr id="adminapi">
                                                    <td>API</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='adminapi'
                                                            id="showadminapimenu" name="showadminapimenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.adminapi.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showadminapimenu' id="addadminapi"
                                                                name="addadminapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.adminapi.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showadminapimenu' id="viewadminapi"
                                                                name="viewadminapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.adminapi.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showadminapimenu' id="editadminapi"
                                                                name="editadminapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.adminapi.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showadminapimenu' id="deleteadminapi"
                                                                name="deleteadminapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.adminapi.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showadminapimenu' id="alldataadminapi"
                                                                name="alldataadminapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.adminmodule.loginhistory.add') == '1' || $user_id == 1)
                                                <tr id="loginhistory">
                                                    <td>Login History</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='loginhistory' id="showloginhistorymenu"
                                                            name="showloginhistorymenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.loginhistory.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showloginhistorymenu' id="addloginhistory"
                                                                name="addloginhistory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.loginhistory.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showloginhistorymenu' id="viewloginhistory"
                                                                name="viewloginhistory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.loginhistory.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showloginhistorymenu' id="editloginhistory"
                                                                name="editloginhistory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.loginhistory.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showloginhistorymenu' id="deleteloginhistory"
                                                                name="deleteloginhistory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.adminmodule.loginhistory.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showloginhistorymenu' id="alldataloginhistory"
                                                                name="alldataloginhistory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12 text-right">
                                                <button type="submit" id="adminmodulesubmit"
                                                    class="btn btn-primary submitBtn" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Save">Save</button>
                                                <button type="button" id="adminmodulereset" data-module="admin"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Reset Admin Module"
                                                    class="btn iq-bg-danger resetbtn">
                                                    Reset
                                                </button>
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel" class="btn btn-secondary cancelbtn">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ((Session::has('quotation') && Session::get('quotation') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Quotation Module</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right">
                                                    <b>Select All </b>
                                                    <input type="checkbox" id="quotationallcheck" data-module="quotation"
                                                        class="allcheck">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15% ;">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="quotationcheckboxes">

                                            @if (session('user_permissions.quotationmodule.quotationdashboard.add') == '1' || $user_id == 1)
                                                <tr id="quotationdashboard">
                                                    <td>Quotation Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='quotationdashboard'
                                                            id="showquotationdashboardmenu"
                                                            name="showquotationdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationdashboardmenu'
                                                                id="addquotationdashboard" name="addquotationdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationdashboardmenu'
                                                                id="viewquotationdashboard" name="viewquotationdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationdashboardmenu'
                                                                id="editquotationdashboard" name="editquotationdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationdashboardmenu'
                                                                id="deletequotationdashboard"
                                                                name="deletequotationdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationdashboardmenu'
                                                                id="alldataquotationdashboard"
                                                                name="alldataquotationdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session(key: 'user_permissions.quotationmodule.quotation.add') == '1' || $user_id == 1)
                                                <tr id="quotation">
                                                    <td>Quotation</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='quotation'
                                                            id="showquotationmenu" name="showquotationmenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotation.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationmenu' id="addquotation"
                                                                name="addquotation" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotation.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationmenu' id="viewquotation"
                                                                name="viewquotation" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotation.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationmenu' id="editquotation"
                                                                name="editquotation" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotation.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationmenu' id="deletequotation"
                                                                name="deletequotation" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotation.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationmenu' id="alldataquotation"
                                                                name="alldataquotation" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.quotationmodule.quotationsetting.add') == '1' || $user_id == 1)
                                                <tr>
                                                    <td class="p-0" style="border-right: 0;">
                                                        <span class="btn expandsettingsbutton"
                                                            data-target="quotation_settings_rows">
                                                            <i class="ri ri-2x ri-arrow-right-circle-fill"></i>
                                                            Quotation Settings
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.quotationmodule.quotationmngcol.add') == '1' || $user_id == 1)
                                                <tr id="quotationmngcol" class="quotation_settings_rows subsettingrows">
                                                    <td>Manage Quotation Column</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='quotationmngcol' id="showquotationmngcolmenu"
                                                            name="showquotationmngcolmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationmngcol.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationmngcolmenu'
                                                                id="addquotationmngcol" name="addquotationmngcol"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationmngcol.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationmngcolmenu'
                                                                id="viewquotationmngcol" name="viewquotationmngcol"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationmngcol.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationmngcolmenu'
                                                                id="editquotationmngcol" name="editquotationmngcol"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationmngcol.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationmngcolmenu'
                                                                id="deletequotationmngcol" name="deletequotationmngcol"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationmngcol.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationmngcolmenu'
                                                                id="alldataquotationmngcol" name="alldataquotationmngcol"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.quotationmodule.quotationformula.add') == '1' || $user_id == 1)
                                                <tr id="quotationformula" class="quotation_settings_rows subsettingrows">
                                                    <td>Quotation Formula</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='quotationformula' id="showquotationformulamenu"
                                                            name="showquotationformulamenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationformula.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationformulamenu'
                                                                id="addquotationformula" name="addquotationformula"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationformula.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationformulamenu'
                                                                id="viewquotationformula" name="viewquotationformula"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationformula.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationformulamenu'
                                                                id="editquotationformula" name="editquotationformula"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationformula.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationformulamenu'
                                                                id="deletequotationformula" name="deletequotationformula"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationformula.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationformulamenu'
                                                                id="alldataquotationformula"
                                                                name="alldataquotationformula" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.quotationmodule.quotationsetting.add') == '1' || $user_id == 1)
                                                <tr id="quotationsetting" class="quotation_settings_rows subsettingrows">
                                                    <td><span class="btn expandsettingsbutton"
                                                            data-target="quotation_other_settings_rows"><i
                                                                class="ri ri-2x ri-arrow-right-circle-fill"></i>
                                                            Quotation/Settings</span></td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='quotationsetting' id="showquotationsettingmenu"
                                                            name="showquotationsettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationsettingmenu'
                                                                id="addquotationsetting" name="addquotationsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationsettingmenu'
                                                                id="viewquotationsetting" name="viewquotationsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationsettingmenu'
                                                                id="editquotationsetting" name="editquotationsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationsettingmenu'
                                                                id="deletequotationsetting" name="deletequotationsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationsettingmenu'
                                                                id="alldataquotationsetting"
                                                                name="alldataquotationsetting" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.quotationmodule.quotationnumbersetting.add') == '1' || $user_id == 1)
                                                <tr id="quotationnumbersetting"
                                                    class="quotation_other_settings_rows subsettingrows">
                                                    <td>Quotation Number Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='quotationnumbersetting'
                                                            id="showquotationnumbersettingmenu"
                                                            name="showquotationnumbersettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationnumbersetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationnumbersettingmenu'
                                                                id="addquotationnumbersetting"
                                                                name="addquotationnumbersetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationnumbersetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationnumbersettingmenu'
                                                                id="viewquotationnumbersetting"
                                                                name="viewquotationnumbersetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationnumbersetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationnumbersettingmenu'
                                                                id="editquotationnumbersetting"
                                                                name="editquotationnumbersetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationnumbersetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationnumbersettingmenu'
                                                                id="deletequotationnumbersetting"
                                                                name="deletequotationnumbersetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationnumbersetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationnumbersettingmenu'
                                                                id="alldataquotationnumbersetting"
                                                                name="alldataquotationnumbersetting" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.quotationmodule.quotationtandcsetting.add') == '1' || $user_id == 1)
                                                <tr id="quotationtandcsetting"
                                                    class="quotation_other_settings_rows subsettingrows">
                                                    <td>Quotation T&C Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='quotationtandcsetting'
                                                            id="showquotationtandcsettingmenu"
                                                            name="showquotationtandcsettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationtandcsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationtandcsettingmenu'
                                                                id="addquotationtandcsetting"
                                                                name="addquotationtandcsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationtandcsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationtandcsettingmenu'
                                                                id="viewquotationtandcsetting"
                                                                name="viewquotationtandcsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationtandcsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationtandcsettingmenu'
                                                                id="editquotationtandcsetting"
                                                                name="editquotationtandcsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationtandcsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationtandcsettingmenu'
                                                                id="deletequotationtandcsetting"
                                                                name="deletequotationtandcsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationtandcsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationtandcsettingmenu'
                                                                id="alldataquotationtandcsetting"
                                                                name="alldataquotationtandcsetting" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.quotationmodule.quotationstandardsetting.add') == '1' || $user_id == 1)
                                                <tr id="quotationstandardsetting"
                                                    class="quotation_other_settings_rows subsettingrows">
                                                    <td>Quotation Standard Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='quotationstandardsetting'
                                                            id="showquotationstandardsettingmenu"
                                                            name="showquotationstandardsettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationstandardsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationstandardsettingmenu'
                                                                id="addquotationstandardsetting"
                                                                name="addquotationstandardsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationstandardsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationstandardsettingmenu'
                                                                id="viewquotationstandardsetting"
                                                                name="viewquotationstandardsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationstandardsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationstandardsettingmenu'
                                                                id="editquotationstandardsetting"
                                                                name="editquotationstandardsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationstandardsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationstandardsettingmenu'
                                                                id="deletequotationstandardsetting"
                                                                name="deletequotationstandardsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationstandardsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationstandardsettingmenu'
                                                                id="alldataquotationstandardsetting"
                                                                name="alldataquotationstandardsetting" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.quotationmodule.quotationgstsetting.add') == '1' || $user_id == 1)
                                                <tr id="quotationgstsetting"
                                                    class="quotation_other_settings_rows subsettingrows">
                                                    <td>Quotation GST Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='quotationgstsetting'
                                                            id="showquotationgstsettingmenu"
                                                            name="showquotationgstsettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationgstsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationgstsettingmenu'
                                                                id="addquotationgstsetting" name="addquotationgstsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationgstsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationgstsettingmenu'
                                                                id="viewquotationgstsetting"
                                                                name="viewquotationgstsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationgstsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationgstsettingmenu'
                                                                id="editquotationgstsetting"
                                                                name="editquotationgstsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationgstsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationgstsettingmenu'
                                                                id="deletequotationgstsetting"
                                                                name="deletequotationgstsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationgstsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationgstsettingmenu'
                                                                id="alldataquotationgstsetting"
                                                                name="alldataquotationgstsetting" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.quotationmodule.quotationformsetting.add') == '1' || $user_id == 1)
                                                <tr id="quotationformsetting"
                                                    class="quotation_other_settings_rows subsettingrows">
                                                    <td>Quotation Form Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='quotationformsetting'
                                                            id="showquotationformsettingmenu"
                                                            name="showquotationformsettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationformsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationformsettingmenu'
                                                                id="addquotationformsetting"
                                                                name="addquotationformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationformsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationformsettingmenu'
                                                                id="viewquotationformsetting"
                                                                name="viewquotationformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationformsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationformsettingmenu'
                                                                id="editquotationformsetting"
                                                                name="editquotationformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationformsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationformsettingmenu'
                                                                id="deletequotationformsetting"
                                                                name="deletequotationformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationformsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationformsettingmenu'
                                                                id="alldataquotationformsetting"
                                                                name="alldataquotationformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.quotationmodule.quotationcustomer.add') == '1' || $user_id == 1)
                                                <tr id="quotationcustomer">
                                                    <td>Customer</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='quotationcustomer' id="showquotationcustomermenu"
                                                            name="showquotationcustomermenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationcustomer.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationcustomermenu'
                                                                id="addquotationcustomer" name="addquotationcustomer"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationcustomer.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationcustomermenu'
                                                                id="viewquotationcustomer" name="viewquotationcustomer"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationcustomer.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationcustomermenu'
                                                                id="editquotationcustomer" name="editquotationcustomer"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationcustomer.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationcustomermenu'
                                                                id="deletequotationcustomer"
                                                                name="deletequotationcustomer" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationcustomer.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationcustomermenu'
                                                                id="alldataquotationcustomer"
                                                                name="alldataquotationcustomer" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.quotationmodule.quotationapi.add') == '1' || $user_id == 1)
                                                <tr id="quotationapi">
                                                    <td>API</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='quotationapi' id="showquotationapimenu"
                                                            name="showquotationapimenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationapi.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationapimenu' id="addquotationapi"
                                                                name="addquotationapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationapi.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationapimenu' id="viewquotationapi"
                                                                name="viewquotationapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationapi.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationapimenu' id="editquotationapi"
                                                                name="editquotationapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationapi.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationapimenu' id="deletequotationapi"
                                                                name="deletequotationapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.quotationapi.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showquotationapimenu' id="alldataquotationapi"
                                                                name="alldataquotationapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.quotationmodule.thirdpartyquotation.add') == '1' || $user_id == 1)
                                                <tr id="thirdpartyquotation">
                                                    <td>Third Party Company</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='thirdpartyquotation'
                                                            id="showthirdpartyquotationmenu"
                                                            name="showthirdpartyquotationmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.thirdpartyquotation.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showthirdpartyquotationmenu'
                                                                id="addthirdpartyquotation" name="addthirdpartyquotation"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.thirdpartyquotation.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showthirdpartyquotationmenu'
                                                                id="viewthirdpartyquotation"
                                                                name="viewthirdpartyquotation" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.thirdpartyquotation.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showthirdpartyquotationmenu'
                                                                id="editthirdpartyquotation"
                                                                name="editthirdpartyquotation" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.thirdpartyquotation.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showthirdpartyquotationmenu'
                                                                id="deletethirdpartyquotation"
                                                                name="deletethirdpartyquotation" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.quotationmodule.thirdpartyquotation.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showthirdpartyquotationmenu'
                                                                id="alldatathirdpartyquotation"
                                                                name="alldatathirdpartyquotation" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12">
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel"
                                                    class="btn btn-secondary float-right cancelbtn">
                                                    Cancel
                                                </button>
                                                <button type="button" id="quotationmodulereset"
                                                    data-module="quotation" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Reset Quotation Module"
                                                    class="btn iq-bg-danger float-right resetbtn mr-2">
                                                    Reset
                                                </button>
                                                <button type="submit" id="quotationmodulesubmit"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Save"
                                                    class="btn btn-primary float-right my-0 submitBtn">
                                                    Save
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ((Session::has('invoice') && Session::get('invoice') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Invoice Module</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right">
                                                    <b>Select All </b>
                                                    <input type="checkbox" id="invoiceallcheck" data-module="invoice"
                                                        class="allcheck">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15% ;">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="invoicecheckboxes">

                                            @if (session('user_permissions.invoicemodule.invoicedashboard.add') == '1' || $user_id == 1)
                                                <tr id="invoicedashboard">
                                                    <td>Invoice Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='invoicedashboard' id="showinvoicedashboardmenu"
                                                            name="showinvoicedashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicedashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicedashboardmenu'
                                                                id="addinvoicedashboard" name="addinvoicedashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicedashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicedashboardmenu'
                                                                id="viewinvoicedashboard" name="viewinvoicedashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicedashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicedashboardmenu'
                                                                id="editinvoicedashboard" name="editinvoicedashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicedashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicedashboardmenu'
                                                                id="deleteinvoicedashboard"
                                                                name="deleteinvoicedashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicedashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicedashboardmenu'
                                                                id="alldatainvoicedashboard"
                                                                name="alldatainvoicedashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoice.add') == '1' || $user_id == 1)
                                                <tr id="invoice">
                                                    <td>Invoice</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='invoice'
                                                            id="showinvoicemenu" name="showinvoicemenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoice.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicemenu' id="addinvoice"
                                                                name="addinvoice" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoice.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicemenu' id="viewinvoice"
                                                                name="viewinvoice" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoice.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicemenu' id="editinvoice"
                                                                name="editinvoice" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoice.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicemenu' id="deleteinvoice"
                                                                name="deleteinvoice" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoice.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicemenu' id="alldatainvoice"
                                                                name="alldatainvoice" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.tdsregister.add') == '1' || $user_id == 1)
                                                <tr id="tdsregister">
                                                    <td>TDS Register</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='tdsregister' id="showtdsregistermenu"
                                                            name="showtdsregistermenu" value="1">
                                                    </td>
                                                    <td>
                                                        NA
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.tdsregister.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtdsregistermenu' id="viewtdsregister"
                                                                name="viewtdsregister" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.tdsregister.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtdsregistermenu' id="edittdsregister"
                                                                name="edittdsregister" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        NA
                                                    </td>
                                                    <td>
                                                        NA
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoicesetting.add') == '1' || $user_id == 1)
                                                <tr>
                                                    <td class="p-0" style="border-right: 0;">
                                                        <span class="btn expandsettingsbutton"
                                                            data-target="invoice_settings_rows">
                                                            <i class="ri ri-2x ri-arrow-right-circle-fill"></i>
                                                            Invoice Settings
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.mngcol.add') == '1' || $user_id == 1)
                                                <tr id="mngcol" class="invoice_settings_rows subsettingrows">
                                                    <td>Manage Invoice Column</td>
                                                    <td> <input type="checkbox" class="clickmenu" data-value='mngcol'
                                                            id="showmngcolmenu" name="showmngcolmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.mngcol.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showmngcolmenu' id="addmngcol"
                                                                name="addmngcol" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.mngcol.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showmngcolmenu' id="viewmngcol"
                                                                name="viewmngcol" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.mngcol.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showmngcolmenu' id="editmngcol"
                                                                name="editmngcol" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.mngcol.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showmngcolmenu' id="deletemngcol"
                                                                name="deletemngcol" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.mngcol.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showmngcolmenu' id="alldatamngcol"
                                                                name="alldatamngcol" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.formula.add') == '1' || $user_id == 1)
                                                <tr id="formula" class="invoice_settings_rows subsettingrows">
                                                    <td>Invoice Formula</td>
                                                    <td> <input type="checkbox" class="clickmenu" data-value='formula'
                                                            id="showformulamenu" name="showformulamenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.formula.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showformulamenu' id="addformula"
                                                                name="addformula" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.formula.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showformulamenu' id="viewformula"
                                                                name="viewformula" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.formula.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showformulamenu' id="editformula"
                                                                name="editformula" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.formula.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showformulamenu' id="deleteformula"
                                                                name="deleteformula" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.formula.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showformulamenu' id="alldataformula"
                                                                name="alldataformula" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoicesetting.add') == '1' || $user_id == 1)
                                                <tr id="invoicesetting" class="invoice_settings_rows subsettingrows">
                                                    <td class="p-0"><span class="btn expandsettingsbutton"
                                                            data-target="invoice_other_settings_rows"><i
                                                                class="ri ri-2x ri-arrow-right-circle-fill"></i>
                                                            Invoice/Settings</span></td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='invoicesetting' id="showinvoicesettingmenu"
                                                            name="showinvoicesettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicesetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicesettingmenu'
                                                                id="addinvoicesetting" name="addinvoicesetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicesetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicesettingmenu'
                                                                id="viewinvoicesetting" name="viewinvoicesetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicesetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicesettingmenu'
                                                                id="editinvoicesetting" name="editinvoicesetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicesetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicesettingmenu'
                                                                id="deleteinvoicesetting" name="deleteinvoicesetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicesetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicesettingmenu'
                                                                id="alldatainvoicesetting" name="alldatainvoicesetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoicenumbersetting.add') == '1' || $user_id == 1)
                                                <tr id="invoicenumbersetting"
                                                    class="invoice_other_settings_rows subsettingrows">
                                                    <td>Invoice Number Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='invoicenumbersetting'
                                                            id="showinvoicenumbersettingmenu"
                                                            name="showinvoicenumbersettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicenumbersetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicenumbersettingmenu'
                                                                id="addinvoicenumbersetting"
                                                                name="addinvoicenumbersetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicenumbersetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicenumbersettingmenu'
                                                                id="viewinvoicenumbersetting"
                                                                name="viewinvoicenumbersetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicenumbersetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicenumbersettingmenu'
                                                                id="editinvoicenumbersetting"
                                                                name="editinvoicenumbersetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicenumbersetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicenumbersettingmenu'
                                                                id="deleteinvoicenumbersetting"
                                                                name="deleteinvoicenumbersetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicenumbersetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicenumbersettingmenu'
                                                                id="alldatainvoicenumbersetting"
                                                                name="alldatainvoicenumbersetting" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoiceformsetting.add') == '1' || $user_id == 1)
                                                <tr id="invoiceformsetting"
                                                    class="invoice_form_settings_rows subsettingrows">
                                                    <td>Invoice Form Settings</td>

                                                    <td>
                                                        <input type="checkbox"class="clickmenu"
                                                            data-value="invoiceformsetting"
                                                            id="showinvoiceformsettingmenu"
                                                            name="showinvoiceformsettingmenu" value="1">
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceformsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showinvoiceformsettingmenu"
                                                                id="addinvoiceformsetting" name="addinvoiceformsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceformsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showinvoiceformsettingmenu"
                                                                id="viewinvoiceformsetting"
                                                                name="viewinvoiceformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceformsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showinvoiceformsettingmenu"
                                                                id="editinvoiceformsetting"
                                                                name="editinvoiceformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceformsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showinvoiceformsettingmenu"
                                                                id="deleteinvoiceformsetting"
                                                                name="deleteinvoiceformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceformsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showinvoiceformsettingmenu"
                                                                id="alldatainvoiceformsetting"
                                                                name="alldatainvoiceformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoiceformsetting.add') == '1' || $user_id == 1)
                                                <tr id="invoiceformsetting"
                                                    class="invoice_other_settings_rows subsettingrows">
                                                    <td>Invoice Form Settings</td>

                                                    <td>
                                                        <input type="checkbox"class="clickmenu"
                                                            data-value="invoiceformsetting"
                                                            id="showinvoiceformsettingmenu"
                                                            name="showinvoiceformsettingmenu" value="1">
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceformsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showinvoiceformsettingmenu"
                                                                id="addinvoiceformsetting" name="addinvoiceformsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceformsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showinvoiceformsettingmenu"
                                                                id="viewinvoiceformsetting"
                                                                name="viewinvoiceformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceformsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showinvoiceformsettingmenu"
                                                                id="editinvoiceformsetting"
                                                                name="editinvoiceformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceformsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showinvoiceformsettingmenu"
                                                                id="deleteinvoiceformsetting"
                                                                name="deleteinvoiceformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceformsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showinvoiceformsettingmenu"
                                                                id="alldatainvoiceformsetting"
                                                                name="alldatainvoiceformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoicetandcsetting.add') == '1' || $user_id == 1)
                                                <tr id="invoicetandcsetting"
                                                    class="invoice_other_settings_rows subsettingrows">
                                                    <td>Invoice T&C Settings</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='invoicetandcsetting'
                                                            id="showinvoicetandcsettingmenu"
                                                            name="showinvoicetandcsettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicetandcsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicetandcsettingmenu'
                                                                id="addinvoicetandcsetting"
                                                                name="addinvoicetandcsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicetandcsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicetandcsettingmenu'
                                                                id="viewinvoicetandcsetting"
                                                                name="viewinvoicetandcsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicetandcsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicetandcsettingmenu'
                                                                id="editinvoicetandcsetting"
                                                                name="editinvoicetandcsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicetandcsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicetandcsettingmenu'
                                                                id="deleteinvoicetandcsetting"
                                                                name="deleteinvoicetandcsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicetandcsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicetandcsettingmenu'
                                                                id="alldatainvoicetandcsetting"
                                                                name="alldatainvoicetandcsetting" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoicestandardsetting.add') == '1' || $user_id == 1)
                                                <tr id="invoicestandardsetting"
                                                    class="invoice_other_settings_rows subsettingrows">
                                                    <td>Invoice Standard Settings</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='invoicestandardsetting'
                                                            id="showinvoicestandardsettingmenu"
                                                            name="showinvoicestandardsettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicestandardsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicestandardsettingmenu'
                                                                id="addinvoicestandardsetting"
                                                                name="addinvoicestandardsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicestandardsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicestandardsettingmenu'
                                                                id="viewinvoicestandardsetting"
                                                                name="viewinvoicestandardsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicestandardsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicestandardsettingmenu'
                                                                id="editinvoicestandardsetting"
                                                                name="editinvoicestandardsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicestandardsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicestandardsettingmenu'
                                                                id="deleteinvoicestandardsetting"
                                                                name="deleteinvoicestandardsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicestandardsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicestandardsettingmenu'
                                                                id="alldatainvoicestandardsetting"
                                                                name="alldatainvoicestandardsetting" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoicegstsetting.add') == '1' || $user_id == 1)
                                                <tr id="invoicegstsetting"
                                                    class="invoice_other_settings_rows subsettingrows">
                                                    <td>Invoice GST Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='invoicegstsetting'
                                                            id="showinvoicegstsettingmenu"
                                                            name="showinvoicegstsettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicegstsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicegstsettingmenu'
                                                                id="addinvoicegstsetting" name="addinvoicegstsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicegstsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicegstsettingmenu'
                                                                id="viewinvoicegstsetting" name="viewinvoicegstsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicegstsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicegstsettingmenu'
                                                                id="editinvoicegstsetting" name="editinvoicegstsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicegstsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicegstsettingmenu'
                                                                id="deleteinvoicegstsetting"
                                                                name="deleteinvoicegstsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicegstsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicegstsettingmenu'
                                                                id="alldatainvoicegstsetting"
                                                                name="alldatainvoicegstsetting" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoicecustomeridsetting.add') == '1' || $user_id == 1)
                                                <tr id="invoicecustomeridsetting"
                                                    class="invoice_other_settings_rows subsettingrows">
                                                    <td>Invoice Customer Id Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='invoicecustomeridsetting'
                                                            id="showinvoicecustomeridsettingmenu"
                                                            name="showinvoicecustomeridsettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecustomeridsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecustomeridsettingmenu'
                                                                id="addinvoicecustomeridsetting"
                                                                name="addinvoicecustomeridsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecustomeridsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecustomeridsettingmenu'
                                                                id="viewinvoicecustomeridsetting"
                                                                name="viewinvoicecustomeridsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecustomeridsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecustomeridsettingmenu'
                                                                id="editinvoicecustomeridsetting"
                                                                name="editinvoicecustomeridsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecustomeridsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecustomeridsettingmenu'
                                                                id="deleteinvoicecustomeridsetting"
                                                                name="deleteinvoicecustomeridsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecustomeridsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecustomeridsettingmenu'
                                                                id="alldatainvoicecustomeridsetting"
                                                                name="alldatainvoicecustomeridsetting" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoicecommissionsetting.add') == '1' || $user_id == 1)
                                                <tr id="invoicecommissionsetting"
                                                    class="invoice_other_settings_rows subsettingrows">
                                                    <td>Invoice Commission Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='invoicecommissionsetting'
                                                            id="showinvoicecommissionsettingmenu"
                                                            name="showinvoicecommissionsettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommissionsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionsettingmenu'
                                                                id="addinvoicecommissionsetting"
                                                                name="addinvoicecommissionsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommissionsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionsettingmenu'
                                                                id="viewinvoicecommissionsetting"
                                                                name="viewinvoicecommissionsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommissionsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionsettingmenu'
                                                                id="editinvoicecommissionsetting"
                                                                name="editinvoicecommissionsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommissionsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionsettingmenu'
                                                                id="deleteinvoicecommissionsetting"
                                                                name="deleteinvoicecommissionsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommissionsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionsettingmenu'
                                                                id="alldatainvoicecommissionsetting"
                                                                name="alldatainvoicecommissionsetting" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.bank.add') == '1' || $user_id == 1)
                                                <tr id="bank">
                                                    <td>Bank</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='bank'
                                                            id="showbankmenu" name="showbankmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.bank.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showbankmenu' id="addbank"
                                                                name="addbank" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.bank.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showbankmenu' id="viewbank"
                                                                name="viewbank" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.bank.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showbankmenu' id="editbank"
                                                                name="editbank" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.bank.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showbankmenu' id="deletebank"
                                                                name="deletebank" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.bank.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showbankmenu' id="alldatabank"
                                                                name="alldatabank" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.customer.add') == '1' || $user_id == 1)
                                                <tr id="customer">
                                                    <td>Customer</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='customer'
                                                            id="showcustomermenu" name="showcustomermenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.customer.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomermenu' id="addcustomer"
                                                                name="addcustomer" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.customer.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomermenu' id="viewcustomer"
                                                                name="viewcustomer" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.customer.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomermenu' id="editcustomer"
                                                                name="editcustomer" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.customer.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomermenu' id="deletecustomer"
                                                                name="deletecustomer" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.customer.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomermenu' id="alldatacustomer"
                                                                name="alldatacustomer" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoiceapi.add') == '1' || $user_id == 1)
                                                <tr id="invoiceapi">
                                                    <td>API</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='invoiceapi' id="showinvoiceapimenu"
                                                            name="showinvoiceapimenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceapi.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoiceapimenu' id="addinvoiceapi"
                                                                name="addinvoiceapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceapi.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoiceapimenu' id="viewinvoiceapi"
                                                                name="viewinvoiceapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceapi.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoiceapimenu' id="editinvoiceapi"
                                                                name="editinvoiceapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceapi.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoiceapimenu' id="deleteinvoiceapi"
                                                                name="deleteinvoiceapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoiceapi.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoiceapimenu' id="alldatainvoiceapi"
                                                                name="alldatainvoiceapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoicecommission.add') == '1' || $user_id == 1)
                                                <tr id="invoicecommission">
                                                    <td>Invoice Commission</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='invoicecommission'
                                                            id="showinvoicecommissionmenu"
                                                            name="showinvoicecommissionmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommission.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionmenu'
                                                                id="addinvoicecommission" name="addinvoicecommission"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommission.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionmenu'
                                                                id="viewinvoicecommission" name="viewinvoicecommission"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommission.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionmenu'
                                                                id="editinvoicecommission" name="editinvoicecommission"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommission.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionmenu'
                                                                id="deleteinvoicecommission"
                                                                name="deleteinvoicecommission" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommission.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionmenu'
                                                                id="alldatainvoicecommission"
                                                                name="alldatainvoicecommission" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.invoicemodule.invoicecommissionparty.add') == '1' || $user_id == 1)
                                                <tr id="invoicecommissionparty">
                                                    <td>Invoice Commission Party</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='invoicecommissionparty'
                                                            id="showinvoicecommissionpartymenu"
                                                            name="showinvoicecommissionpartymenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommissionparty.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionpartymenu'
                                                                id="addinvoicecommissionparty"
                                                                name="addinvoicecommissionparty" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommissionparty.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionpartymenu'
                                                                id="viewinvoicecommissionparty"
                                                                name="viewinvoicecommissionparty" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommissionparty.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionpartymenu'
                                                                id="editinvoicecommissionparty"
                                                                name="editinvoicecommissionparty" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommissionparty.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionpartymenu'
                                                                id="deleteinvoicecommissionparty"
                                                                name="deleteinvoicecommissionparty" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.invoicecommissionparty.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinvoicecommissionpartymenu'
                                                                id="alldatainvoicecommissionparty"
                                                                name="alldatainvoicecommissionparty" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.invoicemodule.thirdpartyinvoice.add') == '1' || $user_id == 1)
                                                <tr id="thirdpartyinvoice">
                                                    <td>Third Party Company</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='thirdpartyinvoice'
                                                            id="showthirdpartyinvoicemenu"
                                                            name="showthirdpartyinvoicemenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.thirdpartyinvoice.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showthirdpartyinvoicemenu'
                                                                id="addthirdpartyinvoice" name="addthirdpartyinvoice"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.thirdpartyinvoice.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showthirdpartyinvoicemenu'
                                                                id="viewthirdpartyinvoice" name="viewthirdpartyinvoice"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.thirdpartyinvoice.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showthirdpartyinvoicemenu'
                                                                id="editthirdpartyinvoice" name="editthirdpartyinvoice"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.thirdpartyinvoice.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showthirdpartyinvoicemenu'
                                                                id="deletethirdpartyinvoice"
                                                                name="deletethirdpartyinvoice" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.invoicemodule.thirdpartyinvoice.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showthirdpartyinvoicemenu'
                                                                id="alldatathirdpartyinvoice"
                                                                name="alldatathirdpartyinvoice" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>

                                    </table>
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12">
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel"
                                                    class="btn btn-secondary float-right cancelbtn">
                                                    Cancel
                                                </button>
                                                <button type="button" id="invoicemodulereset" data-module="invoice"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Reset Invoice Module"
                                                    class="btn iq-bg-danger float-right resetbtn mr-2">
                                                    Reset
                                                </button>
                                                <button type="submit" id="invoicemodulesubmit" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Save"
                                                    class="btn btn-primary float-right my-0 submitBtn">
                                                    Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ((Session::has('lead') && Session::get('lead') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Lead Modules</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right">
                                                    <b>Select All </b>
                                                    <input type="checkbox" id="leadallcheck" data-module="lead"
                                                        class="allcheck">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15%">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="leadcheckboxes">
                                            @if (session('user_permissions.leadmodule.leaddashboard.add') == '1' || $user_id == 1)
                                                <tr id="leaddashboard">
                                                    <td>Lead Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='leaddashboard' id="showleaddashboardmenu"
                                                            name="showleaddashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leaddashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleaddashboardmenu' id="addleaddashboard"
                                                                name="addleaddashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leaddashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleaddashboardmenu'
                                                                id="viewleaddashboard" name="viewleaddashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leaddashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleaddashboardmenu'
                                                                id="editleaddashboard" name="editleaddashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leaddashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleaddashboardmenu'
                                                                id="deleteleaddashboard" name="deleteleaddashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leaddashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleaddashboardmenu'
                                                                id="alldataleaddashboard" name="alldataleaddashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.leadmodule.lead.add') == '1' || $user_id == 1)
                                                <tr id="lead">
                                                    <td>Lead</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='lead'
                                                            id="showleadmenu" name="showleadmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.lead.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadmenu' id="addlead"
                                                                name="addlead" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.lead.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadmenu' id="viewlead"
                                                                name="viewlead" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.lead.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadmenu' id="editlead"
                                                                name="editlead" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.lead.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadmenu' id="deletelead"
                                                                name="deletelead" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.lead.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadmenu' id="alldatalead"
                                                                name="alldatalead" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.leadmodule.leadsettings.add') == '1' || $user_id == 1)
                                                <tr id="leadsettings">
                                                    <td>Lead Settings</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='leadsettings' id="showleadsettingsmenu"
                                                            name="showleadsettingsmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadsettings.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadsettingsmenu' id="addleadsettings"
                                                                name="addleadsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadsettings.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadsettingsmenu' id="viewleadsettings"
                                                                name="viewleadsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadsettings.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadsettingsmenu' id="editleadsettings"
                                                                name="editleadsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadsettings.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadsettingsmenu'
                                                                id="deleteleadsettings" name="deleteleadsettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadsettings.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadsettingsmenu'
                                                                id="alldataleadsettings" name="alldataleadsettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.leadmodule.upcomingfollowup.add') == '1' || $user_id == 1)
                                                <tr id="upcomingfollowup">
                                                    <td>Upcoming Follow-up</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='upcomingfollowup' id="showupcomingfollowupmenu"
                                                            name="showupcomingfollowupmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.upcomingfollowup.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showupcomingfollowupmenu'
                                                                id="addupcomingfollowup" name="addupcomingfollowup"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.upcomingfollowup.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showupcomingfollowupmenu'
                                                                id="viewupcomingfollowup" name="viewupcomingfollowup"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.upcomingfollowup.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showupcomingfollowupmenu'
                                                                id="editupcomingfollowup" name="editupcomingfollowup"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.upcomingfollowup.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showupcomingfollowupmenu'
                                                                id="deleteupcomingfollowup"
                                                                name="deleteupcomingfollowup" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.upcomingfollowup.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showupcomingfollowupmenu'
                                                                id="alldataupcomingfollowup"
                                                                name="alldataupcomingfollowup" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.leadmodule.analysis.add') == '1' || $user_id == 1)
                                                <tr id="analysis">
                                                    <td>Analysis</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='analysis'
                                                            id="showanalysismenu" name="showanalysismenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.analysis.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showanalysismenu' id="addanalysis"
                                                                name="addanalysis" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.analysis.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showanalysismenu' id="viewanalysis"
                                                                name="viewanalysis" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.analysis.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showanalysismenu' id="editanalysis"
                                                                name="editanalysis" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.analysis.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showanalysismenu' id="deleteanalysis"
                                                                name="deleteanalysis" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.analysis.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showanalysismenu' id="alldataanalysis"
                                                                name="alldataanalysis" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.leadmodule.leadownerperformance.add') == '1' || $user_id == 1)
                                                <tr id="leadownerperformance">
                                                    <td>Lead Owner Performance</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='leadownerperformance'
                                                            id="showleadownerperformancemenu"
                                                            name="showleadownerperformancemenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadownerperformance.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadownerperformancemenu'
                                                                id="addleadownerperformance"
                                                                name="addleadownerperformance" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadownerperformance.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadownerperformancemenu'
                                                                id="viewleadownerperformance"
                                                                name="viewleadownerperformance" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadownerperformance.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadownerperformancemenu'
                                                                id="editleadownerperformance"
                                                                name="editleadownerperformance" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadownerperformance.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadownerperformancemenu'
                                                                id="deleteleadownerperformance"
                                                                name="deleteleadownerperformance" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadownerperformance.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadownerperformancemenu'
                                                                id="alldataleadownerperformance"
                                                                name="alldataleadownerperformance" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.leadmodule.recentactivity.add') == '1' || $user_id == 1)
                                                <tr id="recentactivity">
                                                    <td>Recent Activity</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='recentactivity' id="showrecentactivitymenu"
                                                            name="showrecentactivitymenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.recentactivity.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showrecentactivitymenu'
                                                                id="addrecentactivity" name="addrecentactivity"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.recentactivity.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showrecentactivitymenu'
                                                                id="viewrecentactivity" name="viewrecentactivity"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.recentactivity.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showrecentactivitymenu'
                                                                id="editrecentactivity" name="editrecentactivity"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.recentactivity.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showrecentactivitymenu'
                                                                id="deleterecentactivity" name="deleterecentactivity"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.recentactivity.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showrecentactivitymenu'
                                                                id="alldatarecentactivity" name="alldatarecentactivity"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.leadmodule.calendar.add') == '1' || $user_id == 1)
                                                <tr id="calendar">
                                                    <td>Calendar</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='calendar'
                                                            id="showcalendarmenu" name="showcalendarmenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.calendar.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcalendarmenu' id="addcalendar"
                                                                name="addcalendar" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.calendar.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcalendarmenu' id="viewcalendar"
                                                                name="viewcalendar" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.calendar.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcalendarmenu' id="editcalendar"
                                                                name="editcalendar" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.calendar.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcalendarmenu' id="deletecalendar"
                                                                name="deletecalendar" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.calendar.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcalendarmenu' id="alldatacalendar"
                                                                name="alldatacalendar" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.leadmodule.leadapi.add') == '1' || $user_id == 1)
                                                <tr id="leadapi">
                                                    <td>API</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='leadapi'
                                                            id="showleadapimenu" name="showleadapimenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadapi.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadapimenu' id="addleadapi"
                                                                name="addleadapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadapi.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadapimenu' id="viewleadapi"
                                                                name="viewleadapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadapi.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadapimenu' id="editleadapi"
                                                                name="editleadapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadapi.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadapimenu' id="deleteleadapi"
                                                                name="deleteleadapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.leadapi.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showleadapimenu' id="alldataleadapi"
                                                                name="alldataleadapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.leadmodule.import.add') == '1' || $user_id == 1)
                                                <tr id="import">
                                                    <td>Import</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='import'
                                                            id="showimportmenu" name="showimportmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.import.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showimportmenu' id="addimport"
                                                                name="addimport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.import.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showimportmenu' id="viewimport"
                                                                name="viewimport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.import.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showimportmenu' id="editimport"
                                                                name="editimport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.import.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showimportmenu' id="deleteimport"
                                                                name="deleteimport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.import.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showimportmenu' id="alldataimport"
                                                                name="alldataimport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.leadmodule.export.add') == '1' || $user_id == 1)
                                                <tr id="export">
                                                    <td>Export</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='export'
                                                            id="showexportmenu" name="showexportmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.export.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showexportmenu' id="addexport"
                                                                name="addexport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.export.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showexportmenu' id="viewexport"
                                                                name="viewexport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.export.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showexportmenu' id="editexport"
                                                                name="editexport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.export.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showexportmenu' id="deleteexport"
                                                                name="deleteexport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.leadmodule.export.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showexportmenu' id="alldataexport"
                                                                name="alldataexport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12">
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel"
                                                    class="btn btn-secondary float-right cancelbtn">
                                                    Cancel
                                                </button>
                                                <button type="button" id="leadmodulereset" data-module="lead"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Reset Lead Module"
                                                    class="btn iq-bg-danger float-right resetbtn mr-2">
                                                    Reset
                                                </button>
                                                <button type="submit" id="leadmodulesubmit" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Save"
                                                    class="btn btn-primary float-right my-0 submitBtn">
                                                    Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ((Session::has('customersupport') && Session::get('customersupport') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Customer Support Modules</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right">
                                                    <b>Select All </b>
                                                    <input type="checkbox" id="customersupportallcheck"
                                                        data-module="customersupport" class="allcheck">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15%">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="customersupportcheckboxes">
                                            @if (session('user_permissions.customersupportmodule.customersupportdashboard.add') == '1' || $user_id == 1)
                                                <tr id="customersupportdashboard">
                                                    <td>Customer Support Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='customersupportdashboard'
                                                            id="showcustomersupportdashboardmenu"
                                                            name="showcustomersupportdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupportdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportdashboardmenu'
                                                                id="addcustomersupportdashboard"
                                                                name="addcustomersupportdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupportdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportdashboardmenu'
                                                                id="viewcustomersupportdashboard"
                                                                name="viewcustomersupportdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupportdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportdashboardmenu'
                                                                id="editcustomersupportdashboard"
                                                                name="editcustomersupportdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupportdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportdashboardmenu'
                                                                id="deletecustomersupportdashboard"
                                                                name="deletecustomersupportdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupportdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportdashboardmenu'
                                                                id="alldatacustomersupportdashboard"
                                                                name="alldatacustomersupportdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.customersupportmodule.customersupport.add') == '1' || $user_id == 1)
                                                <tr id="customersupport">
                                                    <td>Customer Support</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='customersupport' id="showcustomersupportmenu"
                                                            name="showcustomersupportmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupport.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportmenu'
                                                                id="addcustomersupport" name="addcustomersupport"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupport.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportmenu'
                                                                id="viewcustomersupport" name="viewcustomersupport"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupport.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportmenu'
                                                                id="editcustomersupport" name="editcustomersupport"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupport.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportmenu'
                                                                id="deletecustomersupport" name="deletecustomersupport"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupport.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportmenu'
                                                                id="alldatacustomersupport"
                                                                name="alldatacustomersupport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.customersupportmodule.customersupportapi.add') == '1' || $user_id == 1)
                                                <tr id="customersupportapi">
                                                    <td>API</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='customersupportapi'
                                                            id="showcustomersupportapimenu"
                                                            name="showcustomersupportapimenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupportapi.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportapimenu'
                                                                id="addcustomersupportapi" name="addcustomersupportapi"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupportapi.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportapimenu'
                                                                id="viewcustomersupportapi"
                                                                name="viewcustomersupportapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupportapi.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportapimenu'
                                                                id="editcustomersupportapi"
                                                                name="editcustomersupportapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupportapi.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportapimenu'
                                                                id="deletecustomersupportapi"
                                                                name="deletecustomersupportapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.customersupportmodule.customersupportapi.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcustomersupportapimenu'
                                                                id="alldatacustomersupportapi"
                                                                name="alldatacustomersupportapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12">
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel"
                                                    class="btn btn-secondary float-right cancelbtn">
                                                    Cancel
                                                </button>
                                                <button type="button" id="customersupportmodulereset"
                                                    data-module="customersupport" data-toggle="tooltip"
                                                    data-placement="bottom"
                                                    data-original-title="Reset Customer Support Module"
                                                    class="btn iq-bg-danger float-right resetbtn mr-2">
                                                    Reset
                                                </button>
                                                <button type="submit" id="customersupportmodulesubmit"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Save"
                                                    class="btn btn-primary float-right my-0 submitBtn">
                                                    Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ((Session::has('inventory') && Session::get('inventory') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Inventory Modules</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right"><b>Select All </b> <input
                                                        type="checkbox" id="inventoryallcheck" data-module="inventory"
                                                        class="allcheck"></th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15%">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="inventorycheckboxes">
                                            @if (session('user_permissions.inventorymodule.inventorydashboard.add') == '1' || $user_id == 1)
                                                <tr id="inventorydashboard">
                                                    <td>Inventory Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='inventorydashboard'
                                                            id="showinventorydashboardmenu"
                                                            name="showinventorydashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventorydashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventorydashboardmenu'
                                                                id="addinventorydashboard" name="addinventorydashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventorydashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventorydashboardmenu'
                                                                id="viewinventorydashboard"
                                                                name="viewinventorydashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventorydashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventorydashboardmenu'
                                                                id="editinventorydashboard"
                                                                name="editinventorydashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventorydashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventorydashboardmenu'
                                                                id="deleteinventorydashboard"
                                                                name="deleteinventorydashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventorydashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventorydashboardmenu'
                                                                id="alldatainventorydashboard"
                                                                name="alldatainventorydashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.inventorymodule.product.add') == '1' || $user_id == 1)
                                                <tr id="product">
                                                    <td>Product</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='product'
                                                            id="showproductmenu" name="showproductmenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.product.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductmenu' id="addproduct"
                                                                name="addproduct" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.product.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductmenu' id="viewproduct"
                                                                name="viewproduct" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.product.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductmenu' id="editproduct"
                                                                name="editproduct" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.product.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductmenu' id="deleteproduct"
                                                                name="deleteproduct" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.product.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductmenu' id="alldataproduct"
                                                                name="alldataproduct" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            <tr>
                                                <td class="p-0" style="border-right: 0;">
                                                    <span class="btn expandsettingsbutton"
                                                        data-target="product_settings_rows">
                                                        <i class="ri ri-2x ri-arrow-right-circle-fill"></i>
                                                        Product Settings
                                                    </span>
                                                </td>
                                            </tr>

                                            @if (session('user_permissions.inventorymodule.productcategory.add') == '1' || $user_id == 1)
                                                <tr id="productcategory" class="product_settings_rows subsettingrows">
                                                    <td>Product Category</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='productcategory' id="showproductcategorymenu"
                                                            name="showproductcategorymenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.productcategory.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductcategorymenu'
                                                                id="addproductcategory" name="addproductcategory"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.productcategory.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductcategorymenu'
                                                                id="viewproductcategory" name="viewproductcategory"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.productcategory.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductcategorymenu'
                                                                id="editproductcategory" name="editproductcategory"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.productcategory.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductcategorymenu'
                                                                id="deleteproductcategory" name="deleteproductcategory"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.productcategory.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductcategorymenu'
                                                                id="alldataproductcategory"
                                                                name="alldataproductcategory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.inventorymodule.productcolumnmapping.add') == '1' || $user_id == 1)
                                                <tr id="productcolumnmapping"
                                                    class="product_settings_rows subsettingrows">
                                                    <td>Product Column Mapping</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='productcolumnmapping'
                                                            id="showproductcolumnmappingmenu"
                                                            name="showproductcolumnmappingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.productcolumnmapping.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductcolumnmappingmenu'
                                                                id="addproductcolumnmapping"
                                                                name="addproductcolumnmapping" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.productcolumnmapping.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductcolumnmappingmenu'
                                                                id="viewproductcolumnmapping"
                                                                name="viewproductcolumnmapping" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.productcolumnmapping.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductcolumnmappingmenu'
                                                                id="editproductcolumnmapping"
                                                                name="editproductcolumnmapping" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.productcolumnmapping.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductcolumnmappingmenu'
                                                                id="deleteproductcolumnmapping"
                                                                name="deleteproductcolumnmapping" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.productcolumnmapping.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showproductcolumnmappingmenu'
                                                                id="alldataproductcolumnmapping"
                                                                name="alldataproductcolumnmapping" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.inventorymodule.purchase.add') == '1' || $user_id == 1)
                                                <tr id="purchase">
                                                    <td>Purchase</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='purchase'
                                                            id="showpurchasemenu" name="showpurchasemenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.purchase.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showpurchasemenu' id="addpurchase"
                                                                name="addpurchase" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.purchase.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showpurchasemenu' id="viewpurchase"
                                                                name="viewpurchase" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.purchase.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showpurchasemenu' id="editpurchase"
                                                                name="editpurchase" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.purchase.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showpurchasemenu' id="deletepurchase"
                                                                name="deletepurchase" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.purchase.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showpurchasemenu' id="alldatapurchase"
                                                                name="alldatapurchase" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.inventorymodule.inventory.add') == '1' || $user_id == 1)
                                                <tr id="inventory">
                                                    <td>Inventory</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='inventory' id="showinventorymenu"
                                                            name="showinventorymenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventory.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventorymenu' id="addinventory"
                                                                name="addinventory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventory.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventorymenu' id="viewinventory"
                                                                name="viewinventory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventory.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventorymenu' id="editinventory"
                                                                name="editinventory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventory.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventorymenu' id="deleteinventory"
                                                                name="deleteinventory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventory.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventorymenu' id="alldatainventory"
                                                                name="alldatainventory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.inventorymodule.supplier.add') == '1' || $user_id == 1)
                                                <tr id="supplier">
                                                    <td>Supplier</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='supplier'
                                                            id="showsuppliermenu" name="showsuppliermenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.supplier.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showsuppliermenu' id="addsupplier"
                                                                name="addsupplier" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.supplier.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showsuppliermenu' id="viewsupplier"
                                                                name="viewsupplier" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.supplier.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showsuppliermenu' id="editsupplier"
                                                                name="editsupplier" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.supplier.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showsuppliermenu' id="deletesupplier"
                                                                name="deletesupplier" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.supplier.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showsuppliermenu' id="alldatasupplier"
                                                                name="alldatasupplier" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.inventorymodule.inventoryapi.add') == '1' || $user_id == 1)
                                                <tr id="inventoryapi">
                                                    <td>API</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='inventoryapi' id="showinventoryapimenu"
                                                            name="showinventoryapimenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventoryapi.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventoryapimenu' id="addinventoryapi"
                                                                name="addinventoryapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventoryapi.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventoryapimenu' id="viewinventoryapi"
                                                                name="viewinventoryapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventoryapi.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventoryapimenu' id="editinventoryapi"
                                                                name="editinventoryapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventoryapi.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventoryapimenu'
                                                                id="deleteinventoryapi" name="deleteinventoryapi"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.inventoryapi.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showinventoryapimenu'
                                                                id="alldatainventoryapi" name="alldatainventoryapi"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12">
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel"
                                                    class="btn btn-secondary float-right cancelbtn">
                                                    Cancel
                                                </button>
                                                <button type="button" id="inventorymodulereset"
                                                    data-module="inventory" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Reset Inventory Module"
                                                    class="btn iq-bg-danger float-right resetbtn mr-2">
                                                    Reset
                                                </button>
                                                <button type="submit" id="inventorymodulesubmit"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Save"
                                                    class="btn btn-primary float-right my-0 submitBtn">
                                                    Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ((Session::has('reminder') && Session::get('reminder') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Reminder Modules</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right"><b>Select All </b> <input
                                                        type="checkbox" id="reminderallcheck" data-module="reminder"
                                                        class="allcheck"></th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15%">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="remindercheckboxes">
                                            @if (session('user_permissions.remindermodule.reminderdashboard.add') == '1' || $user_id == 1)
                                                <tr id="reminderdashboard">
                                                    <td>Reminder Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='reminderdashboard'
                                                            id="showreminderdashboardmenu"
                                                            name="showreminderdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminderdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreminderdashboardmenu'
                                                                id="addreminderdashboard" name="addreminderdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminderdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreminderdashboardmenu'
                                                                id="viewreminderdashboard" name="viewreminderdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminderdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreminderdashboardmenu'
                                                                id="editreminderdashboard" name="editreminderdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminderdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreminderdashboardmenu'
                                                                id="deletereminderdashboard"
                                                                name="deletereminderdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminderdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreminderdashboardmenu'
                                                                id="alldatareminderdashboard"
                                                                name="alldatareminderdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.remindermodule.reminder.add') == '1' || $user_id == 1)
                                                <tr id="reminder">
                                                    <td>Reminder</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='reminder'
                                                            id="showremindermenu" name="showremindermenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminder.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showremindermenu' id="addreminder"
                                                                name="addreminder" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminder.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showremindermenu' id="viewreminder"
                                                                name="viewreminder" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminder.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showremindermenu' id="editreminder"
                                                                name="editreminder" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminder.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showremindermenu' id="deletereminder"
                                                                name="deletereminder" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminder.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showremindermenu' id="alldatareminder"
                                                                name="alldatareminder" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.remindermodule.remindercustomer.add') == '1' || $user_id == 1)
                                                <tr id="remindercustomer">
                                                    <td>Reminder Customer</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='remindercustomer' id="showremindercustomermenu"
                                                            name="showremindercustomermenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.remindercustomer.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showremindercustomermenu'
                                                                id="addremindercustomer" name="addremindercustomer"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.remindercustomer.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showremindercustomermenu'
                                                                id="viewremindercustomer" name="viewremindercustomer"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.remindercustomer.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showremindercustomermenu'
                                                                id="editremindercustomer" name="editremindercustomer"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.remindercustomer.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showremindercustomermenu'
                                                                id="deleteremindercustomer"
                                                                name="deleteremindercustomer" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.remindercustomer.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showremindercustomermenu'
                                                                id="alldataremindercustomer"
                                                                name="alldataremindercustomer" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.remindermodule.reminderapi.add') == '1' || $user_id == 1)
                                                <tr id="reminderapi">
                                                    <td>API</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='reminderapi' id="showreminderapimenu"
                                                            name="showreminderapimenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminderapi.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreminderapimenu' id="addreminderapi"
                                                                name="addreminderapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminderapi.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreminderapimenu' id="viewreminderapi"
                                                                name="viewreminderapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminderapi.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreminderapimenu' id="editreminderapi"
                                                                name="editreminderapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminderapi.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreminderapimenu' id="deletereminderapi"
                                                                name="deletereminderapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.remindermodule.reminderapi.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreminderapimenu' id="alldatareminderapi"
                                                                name="alldatareminderapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12">
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel"
                                                    class="btn btn-secondary float-right cancelbtn">
                                                    Cancel
                                                </button>
                                                <button type="button" id="remindermodulereset" data-module="reminder"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Reset Reminder Module"
                                                    class="btn iq-bg-danger float-right resetbtn mr-2">
                                                    Reset
                                                </button>
                                                <button type="submit" id="remindermodulesubmit" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Save"
                                                    class="btn btn-primary float-right my-0 submitBtn">
                                                    Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ((Session::has('report') && Session::get('report') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Report Modules</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right"><b>Select All </b> <input
                                                        type="checkbox" id="reportallcheck" data-module="report"
                                                        class="allcheck"></th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15%">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="reportcheckboxes">
                                            @if (session('user_permissions.reportmodule.reportdashboard.add') == '1' || $user_id == 1)
                                                <tr id="reportdashboard">
                                                    <td>Report Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='reportdashboard' id="showreportdashboardmenu"
                                                            name="showreportdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.reportdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportdashboardmenu'
                                                                id="addreportdashboard" name="addreportdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.reportdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportdashboardmenu'
                                                                id="viewreportdashboard" name="viewreportdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.reportdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportdashboardmenu'
                                                                id="editreportdashboard" name="editreportdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.reportdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportdashboardmenu'
                                                                id="deletereportdashboard" name="deletereportdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.reportdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportdashboardmenu'
                                                                id="alldatareportdashboard"
                                                                name="alldatareportdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.reportmodule.report.add') == '1' || $user_id == 1)
                                                <tr id="report">
                                                    <td rowspan="3">report</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='report'
                                                            id="showreportmenu" name="showreportmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.report.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportmenu' id="addreport"
                                                                name="addreport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.report.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportmenu' id="viewreport"
                                                                name="viewreport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.report.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportmenu' id="editreport"
                                                                name="editreport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.report.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportmenu' id="deletereport"
                                                                name="deletereport" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <select name="assignedto[]" class="form-control multiple"
                                                            id="assignedto" multiple>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>All Log</td>
                                                    {{-- add more option title here if needed --}}
                                                </tr>
                                                <tr id="report">
                                                    <td>
                                                        <input type="checkbox" class="clicksubmenu"
                                                            data-value='showreportmenu' id="logreport"
                                                            name="logreport" value="1">
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.reportmodule.reportapi.add') == '1' || $user_id == 1)
                                                <tr id="reportapi">
                                                    <td>API</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='reportapi' id="showreportapimenu"
                                                            name="showreportapimenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.reportapi.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportapimenu' id="addreportapi"
                                                                name="addreportapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.reportapi.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportapimenu' id="viewreportapi"
                                                                name="viewreportapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.reportapi.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportapimenu' id="editreportapi"
                                                                name="editreportapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.reportapi.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportapimenu' id="deletereportapi"
                                                                name="deletereportapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.reportmodule.reportapi.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showreportapimenu' id="alldatareportapi"
                                                                name="alldatareportapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12">
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel"
                                                    class="btn btn-secondary float-right cancelbtn">
                                                    Cancel
                                                </button>
                                                <button type="button" id="reportmodulereset" data-module="report"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Reset report Module"
                                                    class="btn iq-bg-danger float-right resetbtn mr-2">
                                                    Reset
                                                </button>
                                                <button type="submit" id="reportmodulesubmit" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Save"
                                                    class="btn btn-primary float-right my-0 submitBtn">
                                                    Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ((Session::has('blog') && Session::get('blog') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">blog Modules</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right">
                                                    <b>Select All </b>
                                                    <input type="checkbox" id="blogallcheck" data-module="blog"
                                                        class="allcheck">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15%">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="blogcheckboxes">
                                            @if (session('user_permissions.blogmodule.blogdashboard.add') == '1' || $user_id == 1)
                                                <tr id="blogdashboard">
                                                    <td>Blog Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='blogdashboard' id="showblogdashboardmenu"
                                                            name="showblogdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogdashboardmenu' id="addblogdashboard"
                                                                name="addblogdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogdashboardmenu'
                                                                id="viewblogdashboard" name="viewblogdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogdashboardmenu'
                                                                id="editblogdashboard" name="editblogdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogdashboardmenu'
                                                                id="deleteblogdashboard" name="deleteblogdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogdashboardmenu'
                                                                id="alldatablogdashboard" name="alldatablogdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.blogmodule.blog.add') == '1' || $user_id == 1)
                                                <tr id="blog">
                                                    <td>blog</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='blog'
                                                            id="showblogmenu" name="showblogmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blog.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogmenu' id="addblog"
                                                                name="addblog" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blog.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogmenu' id="viewblog"
                                                                name="viewblog" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blog.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogmenu' id="editblog"
                                                                name="editblog" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blog.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogmenu' id="deleteblog"
                                                                name="deleteblog" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blog.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogmenu' id="alldatablog"
                                                                name="alldatablog" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.blogmodule.blogsettings.add') == '1' || $user_id == 1)
                                                <tr id="blogsettings">
                                                    <td>Blog Settings</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='blogsettings' id="showblogsettingsmenu"
                                                            name="showblogsettingsmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogsettings.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogsettingsmenu' id="addblogsettings"
                                                                name="addblogsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogsettings.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogsettingsmenu' id="viewblogsettings"
                                                                name="viewblogsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogsettings.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogsettingsmenu' id="editblogsettings"
                                                                name="editblogsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogsettings.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogsettingsmenu'
                                                                id="deleteblogsettings" name="deleteblogsettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogsettings.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogsettingsmenu'
                                                                id="alldatablogsettings" name="alldatablogsettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.blogmodule.blogapi.add') == '1' || $user_id == 1)
                                                <tr id="blogapi">
                                                    <td>API</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='blogapi'
                                                            id="showblogapimenu" name="showblogapimenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogapi.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogapimenu' id="addblogapi"
                                                                name="addblogapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogapi.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogapimenu' id="viewblogapi"
                                                                name="viewblogapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogapi.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogapimenu' id="editblogapi"
                                                                name="editblogapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogapi.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogapimenu' id="deleteblogapi"
                                                                name="deleteblogapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.blogmodule.blogapi.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showblogapimenu' id="alldatablogapi"
                                                                name="alldatablogapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12">
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel"
                                                    class="btn btn-secondary float-right cancelbtn">
                                                    Cancel
                                                </button>
                                                <button type="button" id="blogmodulereset" data-module="blog"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Reset blog Module"
                                                    class="btn iq-bg-danger float-right resetbtn mr-2">
                                                    Reset
                                                </button>
                                                <button type="submit" id="blogmodulesubmit" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Save"
                                                    class="btn btn-primary float-right my-0 submitBtn">
                                                    Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ((Session::has('logistic') && Session::get('logistic') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Logistic Module</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right">
                                                    <b>Select All </b>
                                                    <input type="checkbox" id="logisticallcheck"
                                                        data-module="logistic" class="allcheck">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15% ;">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="logisticcheckboxes">
                                            @if (session('user_permissions.logisticmodule.logisticdashboard.add') == '1' || $user_id == 1)
                                                <tr id="logisticdashboard">
                                                    <td>Logistic Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='logisticdashboard'
                                                            id="showlogisticdashboardmenu"
                                                            name="showlogisticdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticdashboardmenu'
                                                                id="addlogisticdashboard" name="addlogisticdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticdashboardmenu'
                                                                id="viewlogisticdashboard" name="viewlogisticdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticdashboardmenu'
                                                                id="editlogisticdashboard" name="editlogisticdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticdashboardmenu'
                                                                id="deletelogisticdashboard"
                                                                name="deletelogisticdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticdashboardmenu'
                                                                id="alldatalogisticdashboard"
                                                                name="alldatalogisticdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.consignorcopy.add') == '1' || $user_id == 1)
                                                <tr id="consignorcopy">
                                                    <td>Consignor Copy</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='consignorcopy' id="showconsignorcopymenu"
                                                            name="showconsignorcopymenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopy.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopymenu' id="addconsignorcopy"
                                                                name="addconsignorcopy" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopy.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopymenu'
                                                                id="viewconsignorcopy" name="viewconsignorcopy"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopy.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopymenu'
                                                                id="editconsignorcopy" name="editconsignorcopy"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopy.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopymenu'
                                                                id="deleteconsignorcopy" name="deleteconsignorcopy"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopy.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopymenu'
                                                                id="alldataconsignorcopy" name="alldataconsignorcopy"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.logisticsettings.add') == '1' || $user_id == 1)
                                                <tr id="logisticsettings" class="logistic_settings_rows">
                                                    <td>
                                                        <span class="btn expandsettingsbutton"
                                                            data-target="logistic_other_settings_rows">
                                                            <i class="ri ri-2x ri-arrow-right-circle-fill"></i>
                                                            logistic/Settings
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='logisticsettings' id="showlogisticsettingsmenu"
                                                            name="showlogisticsettingsmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticsettings.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticsettingsmenu'
                                                                id="addlogisticsettings" name="addlogisticsettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticsettings.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticsettingsmenu'
                                                                id="viewlogisticsettings" name="viewlogisticsettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticsettings.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticsettingsmenu'
                                                                id="editlogisticsettings" name="editlogisticsettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticsettings.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticsettingsmenu'
                                                                id="deletelogisticsettings"
                                                                name="deletelogisticsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticsettings.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticsettingsmenu'
                                                                id="alldatalogisticsettings"
                                                                name="alldatalogisticsettings" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.consignmentnotenumbersettings.add') == '1' || $user_id == 1)
                                                <tr id="consignmentnotenumbersettings"
                                                    class="logistic_other_settings_rows subsettingrows">
                                                    <td>Consignment Note Number Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='consignmentnotenumbersettings'
                                                            id="showconsignmentnotenumbersettingsmenu"
                                                            name="showconsignmentnotenumbersettingsmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignmentnotenumbersettings.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignmentnotenumbersettingsmenu'
                                                                id="addconsignmentnotenumbersettings"
                                                                name="addconsignmentnotenumbersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignmentnotenumbersettings.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignmentnotenumbersettingsmenu'
                                                                id="viewconsignmentnotenumbersettings"
                                                                name="viewconsignmentnotenumbersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignmentnotenumbersettings.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignmentnotenumbersettingsmenu'
                                                                id="editconsignmentnotenumbersettings"
                                                                name="editconsignmentnotenumbersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignmentnotenumbersettings.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignmentnotenumbersettingsmenu'
                                                                id="deleteconsignmentnotenumbersettings"
                                                                name="deleteconsignmentnotenumbersettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignmentnotenumbersettings.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignmentnotenumbersettingsmenu'
                                                                id="alldataconsignmentnotenumbersettings"
                                                                name="alldataconsignmentnotenumbersettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.consignorcopytandcsettings.add') == '1' || $user_id == 1)
                                                <tr id="consignorcopytandcsettings"
                                                    class="logistic_other_settings_rows subsettingrows">
                                                    <td>Consignor Copy T&C Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='consignorcopytandcsettings'
                                                            id="showconsignorcopytandcsettingsmenu"
                                                            name="showconsignorcopytandcsettingsmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopytandcsettings.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopytandcsettingsmenu'
                                                                id="addconsignorcopytandcsettings"
                                                                name="addconsignorcopytandcsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopytandcsettings.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopytandcsettingsmenu'
                                                                id="viewconsignorcopytandcsettings"
                                                                name="viewconsignorcopytandcsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopytandcsettings.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopytandcsettingsmenu'
                                                                id="editconsignorcopytandcsettings"
                                                                name="editconsignorcopytandcsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopytandcsettings.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopytandcsettingsmenu'
                                                                id="deleteconsignorcopytandcsettings"
                                                                name="deleteconsignorcopytandcsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopytandcsettings.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopytandcsettingsmenu'
                                                                id="alldataconsignorcopytandcsettings"
                                                                name="alldataconsignorcopytandcsettings" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.logisticothersettings.add') == '1' || $user_id == 1)
                                                <tr id="logisticothersettings"
                                                    class="logistic_other_settings_rows subsettingrows">
                                                    <td>Logistic Other Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='logisticothersettings'
                                                            id="showlogisticothersettingsmenu"
                                                            name="showlogisticothersettingsmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticothersettings.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticothersettingsmenu'
                                                                id="addlogisticothersettings"
                                                                name="addlogisticothersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticothersettings.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticothersettingsmenu'
                                                                id="viewlogisticothersettings"
                                                                name="viewlogisticothersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticothersettings.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticothersettingsmenu'
                                                                id="editlogisticothersettings"
                                                                name="editlogisticothersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticothersettings.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticothersettingsmenu'
                                                                id="deletelogisticothersettings"
                                                                name="deletelogisticothersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticothersettings.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticothersettingsmenu'
                                                                id="alldatalogisticothersettings"
                                                                name="alldatalogisticothersettings" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.consignee.add') == '1' || $user_id == 1)
                                                <tr id="consignee">
                                                    <td>Consignee</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='consignee' id="showconsigneemenu"
                                                            name="showconsigneemenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignee.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsigneemenu' id="addconsignee"
                                                                name="addconsignee" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignee.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsigneemenu' id="viewconsignee"
                                                                name="viewconsignee" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignee.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsigneemenu' id="editconsignee"
                                                                name="editconsignee" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignee.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsigneemenu' id="deleteconsignee"
                                                                name="deleteconsignee" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignee.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsigneemenu' id="alldataconsignee"
                                                                name="alldataconsignee" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.consignor.add') == '1' || $user_id == 1)
                                                <tr id="consignor">
                                                    <td>consignor</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='consignor' id="showconsignormenu"
                                                            name="showconsignormenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignor.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignormenu' id="addconsignor"
                                                                name="addconsignor" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignor.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignormenu' id="viewconsignor"
                                                                name="viewconsignor" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignor.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignormenu' id="editconsignor"
                                                                name="editconsignor" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignor.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignormenu' id="deleteconsignor"
                                                                name="deleteconsignor" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignor.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignormenu' id="alldataconsignor"
                                                                name="alldataconsignor" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.logisticapi.add') == '1' || $user_id == 1)
                                                <tr id="logisticapi">
                                                    <td>API</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='logisticapi' id="showlogisticapimenu"
                                                            name="showlogisticapimenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticapi.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticapimenu' id="addlogisticapi"
                                                                name="addlogisticapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticapi.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticapimenu' id="viewlogisticapi"
                                                                name="viewlogisticapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticapi.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticapimenu' id="editlogisticapi"
                                                                name="editlogisticapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticapi.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticapimenu' id="deletelogisticapi"
                                                                name="deletelogisticapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticapi.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticapimenu' id="alldatalogisticapi"
                                                                name="alldatalogisticapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.watermark.add') == '1' || $user_id == 1)
                                                <tr id="watermark">
                                                    <td>PDF Watermark</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='watermark' id="showwatermarkmenu"
                                                            name="showwatermarkmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.watermark.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showwatermarkmenu' id="addwatermark"
                                                                name="addwatermark" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.watermark.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showwatermarkmenu' id="viewwatermark"
                                                                name="viewwatermark" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.watermark.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showwatermarkmenu' id="editwatermark"
                                                                name="editwatermark" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.watermark.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showwatermarkmenu' id="deletewatermark"
                                                                name="deletewatermark" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.watermark.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showwatermarkmenu' id="alldatawatermark"
                                                                name="alldatawatermark" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.downloadcopysetting.add') == '1' || $user_id == 1)
                                                <tr id="downloadcopysetting">
                                                    <td>Download Copy Setting</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='downloadcopysetting'
                                                            id="showdownloadcopysettingmenu"
                                                            name="showdownloadcopysettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.downloadcopysetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdownloadcopysettingmenu'
                                                                id="adddownloadcopysetting"
                                                                name="adddownloadcopysetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.downloadcopysetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdownloadcopysettingmenu'
                                                                id="viewdownloadcopysetting"
                                                                name="viewdownloadcopysetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.downloadcopysetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdownloadcopysettingmenu'
                                                                id="editdownloadcopysetting"
                                                                name="editdownloadcopysetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.downloadcopysetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdownloadcopysettingmenu'
                                                                id="deletedownloadcopysetting"
                                                                name="deletedownloadcopysetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.downloadcopysetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdownloadcopysettingmenu'
                                                                id="alldatadownloadcopysetting"
                                                                name="alldatadownloadcopysetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.transporterbilling.add') == '1' || $user_id == 1)
                                                <tr id="transporterbilling">
                                                    <td>Transporter Billing</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='transporterbilling'
                                                            id="showtransporterbillingmenu"
                                                            name="showtransporterbillingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.transporterbilling.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtransporterbillingmenu'
                                                                id="addtransporterbilling" name="addtransporterbilling"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.transporterbilling.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtransporterbillingmenu'
                                                                id="viewtransporterbilling"
                                                                name="viewtransporterbilling" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.transporterbilling.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtransporterbillingmenu'
                                                                id="edittransporterbilling"
                                                                name="edittransporterbilling" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.transporterbilling.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtransporterbillingmenu'
                                                                id="deletetransporterbilling"
                                                                name="deletetransporterbilling" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.transporterbilling.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtransporterbillingmenu'
                                                                id="alldatatransporterbilling"
                                                                name="alldatatransporterbilling" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12">
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel"
                                                    class="btn btn-secondary float-right cancelbtn">
                                                    Cancel
                                                </button>
                                                <button type="button" id="logisticmodulereset" data-module="logistic"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Reset Logistic Module"
                                                    class="btn iq-bg-danger float-right resetbtn mr-2">
                                                    Reset
                                                </button>
                                                <button type="submit" id="logisticmodulesubmit" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Save"
                                                    class="btn btn-primary float-right my-0 submitBtn">
                                                    Save
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ((Session::has('logistic') && Session::get('logistic') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Logistic Module</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right">
                                                    <b>Select All </b>
                                                    <input type="checkbox" id="logisticallcheck"
                                                        data-module="logistic" class="allcheck">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15% ;">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="logisticcheckboxes">
                                            @if (session('user_permissions.logisticmodule.logisticdashboard.add') == '1' || $user_id == 1)
                                                <tr id="logisticdashboard">
                                                    <td>Logistic Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='logisticdashboard'
                                                            id="showlogisticdashboardmenu"
                                                            name="showlogisticdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticdashboardmenu'
                                                                id="addlogisticdashboard" name="addlogisticdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticdashboardmenu'
                                                                id="viewlogisticdashboard" name="viewlogisticdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticdashboardmenu'
                                                                id="editlogisticdashboard" name="editlogisticdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticdashboardmenu'
                                                                id="deletelogisticdashboard"
                                                                name="deletelogisticdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticdashboardmenu'
                                                                id="alldatalogisticdashboard"
                                                                name="alldatalogisticdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.consignorcopy.add') == '1' || $user_id == 1)
                                                <tr id="consignorcopy">
                                                    <td>Consignor Copy</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='consignorcopy' id="showconsignorcopymenu"
                                                            name="showconsignorcopymenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopy.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopymenu' id="addconsignorcopy"
                                                                name="addconsignorcopy" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopy.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopymenu'
                                                                id="viewconsignorcopy" name="viewconsignorcopy"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopy.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopymenu'
                                                                id="editconsignorcopy" name="editconsignorcopy"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopy.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopymenu'
                                                                id="deleteconsignorcopy" name="deleteconsignorcopy"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopy.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopymenu'
                                                                id="alldataconsignorcopy" name="alldataconsignorcopy"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.logisticsettings.add') == '1' || $user_id == 1)
                                                <tr id="logisticsettings" class="logistic_settings_rows">
                                                    <td>
                                                        <span class="btn expandsettingsbutton"
                                                            data-target="logistic_other_settings_rows">
                                                            <i class="ri ri-2x ri-arrow-right-circle-fill"></i>
                                                            logistic/Settings
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='logisticsettings' id="showlogisticsettingsmenu"
                                                            name="showlogisticsettingsmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticsettings.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticsettingsmenu'
                                                                id="addlogisticsettings" name="addlogisticsettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticsettings.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticsettingsmenu'
                                                                id="viewlogisticsettings" name="viewlogisticsettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticsettings.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticsettingsmenu'
                                                                id="editlogisticsettings" name="editlogisticsettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticsettings.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticsettingsmenu'
                                                                id="deletelogisticsettings"
                                                                name="deletelogisticsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticsettings.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticsettingsmenu'
                                                                id="alldatalogisticsettings"
                                                                name="alldatalogisticsettings" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.lrcolumnmapping.add') == '1' || $user_id == 1)
                                                <tr id="columnmapping"
                                                    class="logistic_other_settings_rows subsettingrows">
                                                    <td>LR Column Mapping</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='lrcolumnmapping' id="showlrcolumnmappingmenu"
                                                            name="showlrcolumnmappingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.lrcolumnmapping.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlrcolumnmappingmenu'
                                                                id="addlrcolumnmapping" name="addlrcolumnmapping"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.lrcolumnmapping.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlrcolumnmappingmenu'
                                                                id="viewlrcolumnmapping" name="viewlrcolumnmapping"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.lrcolumnmapping.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlrcolumnmappingmenu'
                                                                id="editlrcolumnmapping" name="editlrcolumnmapping"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.lrcolumnmapping.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlrcolumnmappingmenu'
                                                                id="deletelrcolumnmapping" name="deletelrcolumnmapping"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.inventorymodule.lrcolumnmapping.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlrcolumnmappingmenu'
                                                                id="alldatalrcolumnmapping"
                                                                name="alldatalrcolumnmapping" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.logisticmodule.logisticformsetting.add') == '1' || $user_id == 1)
                                                <tr id="logisticformsetting"
                                                    class="logistic_other_settings_rows subsettingrows">
                                                    <td>Logistic Form Settings</td>

                                                    <td>
                                                        <input type="checkbox"class="clickmenu"
                                                            data-value="logisticformsetting"
                                                            id="showlogisticformsettingmenu"
                                                            name="showlogisticformsettingmenu" value="1">
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticformsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showlogisticformsettingmenu"
                                                                id="addlogisticformsetting" name="addlogisticformsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticformsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showlogisticformsettingmenu"
                                                                id="viewlogisticformsetting" name="viewlogisticformsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticformsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showlogisticformsettingmenu"
                                                                id="editlogisticformsetting" name="editlogisticformsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticformsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showlogisticformsettingmenu"
                                                                id="deletelogisticformsetting"
                                                                name="deletelogisticformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticformsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox"class="clicksubmenu"
                                                                data-value="showlogisticformsettingmenu"
                                                                id="alldatalogisticformsetting"
                                                                name="alldatalogisticformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.consignmentnotenumbersettings.add') == '1' || $user_id == 1)
                                                <tr id="consignmentnotenumbersettings"
                                                    class="logistic_other_settings_rows subsettingrows">
                                                    <td>Consignment Note Number Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='consignmentnotenumbersettings'
                                                            id="showconsignmentnotenumbersettingsmenu"
                                                            name="showconsignmentnotenumbersettingsmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignmentnotenumbersettings.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignmentnotenumbersettingsmenu'
                                                                id="addconsignmentnotenumbersettings"
                                                                name="addconsignmentnotenumbersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignmentnotenumbersettings.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignmentnotenumbersettingsmenu'
                                                                id="viewconsignmentnotenumbersettings"
                                                                name="viewconsignmentnotenumbersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignmentnotenumbersettings.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignmentnotenumbersettingsmenu'
                                                                id="editconsignmentnotenumbersettings"
                                                                name="editconsignmentnotenumbersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignmentnotenumbersettings.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignmentnotenumbersettingsmenu'
                                                                id="deleteconsignmentnotenumbersettings"
                                                                name="deleteconsignmentnotenumbersettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignmentnotenumbersettings.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignmentnotenumbersettingsmenu'
                                                                id="alldataconsignmentnotenumbersettings"
                                                                name="alldataconsignmentnotenumbersettings"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.consignorcopytandcsettings.add') == '1' || $user_id == 1)
                                                <tr id="consignorcopytandcsettings"
                                                    class="logistic_other_settings_rows subsettingrows">
                                                    <td>Consignor Copy T&C Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='consignorcopytandcsettings'
                                                            id="showconsignorcopytandcsettingsmenu"
                                                            name="showconsignorcopytandcsettingsmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopytandcsettings.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopytandcsettingsmenu'
                                                                id="addconsignorcopytandcsettings"
                                                                name="addconsignorcopytandcsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopytandcsettings.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopytandcsettingsmenu'
                                                                id="viewconsignorcopytandcsettings"
                                                                name="viewconsignorcopytandcsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopytandcsettings.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopytandcsettingsmenu'
                                                                id="editconsignorcopytandcsettings"
                                                                name="editconsignorcopytandcsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopytandcsettings.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopytandcsettingsmenu'
                                                                id="deleteconsignorcopytandcsettings"
                                                                name="deleteconsignorcopytandcsettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignorcopytandcsettings.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignorcopytandcsettingsmenu'
                                                                id="alldataconsignorcopytandcsettings"
                                                                name="alldataconsignorcopytandcsettings" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.logisticothersettings.add') == '1' || $user_id == 1)
                                                <tr id="logisticothersettings"
                                                    class="logistic_other_settings_rows subsettingrows">
                                                    <td>Logistic Other Settings</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='logisticothersettings'
                                                            id="showlogisticothersettingsmenu"
                                                            name="showlogisticothersettingsmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticothersettings.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticothersettingsmenu'
                                                                id="addlogisticothersettings"
                                                                name="addlogisticothersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticothersettings.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticothersettingsmenu'
                                                                id="viewlogisticothersettings"
                                                                name="viewlogisticothersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticothersettings.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticothersettingsmenu'
                                                                id="editlogisticothersettings"
                                                                name="editlogisticothersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticothersettings.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticothersettingsmenu'
                                                                id="deletelogisticothersettings"
                                                                name="deletelogisticothersettings" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticothersettings.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticothersettingsmenu'
                                                                id="alldatalogisticothersettings"
                                                                name="alldatalogisticothersettings" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.consignee.add') == '1' || $user_id == 1)
                                                <tr id="consignee">
                                                    <td>Consignee</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='consignee' id="showconsigneemenu"
                                                            name="showconsigneemenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignee.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsigneemenu' id="addconsignee"
                                                                name="addconsignee" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignee.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsigneemenu' id="viewconsignee"
                                                                name="viewconsignee" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignee.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsigneemenu' id="editconsignee"
                                                                name="editconsignee" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignee.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsigneemenu' id="deleteconsignee"
                                                                name="deleteconsignee" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignee.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsigneemenu' id="alldataconsignee"
                                                                name="alldataconsignee" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.consignor.add') == '1' || $user_id == 1)
                                                <tr id="consignor">
                                                    <td>consignor</td>
                                                    <td> <input type="checkbox" class="clickmenu"
                                                            data-value='consignor' id="showconsignormenu"
                                                            name="showconsignormenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignor.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignormenu' id="addconsignor"
                                                                name="addconsignor" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignor.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignormenu' id="viewconsignor"
                                                                name="viewconsignor" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignor.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignormenu' id="editconsignor"
                                                                name="editconsignor" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignor.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignormenu' id="deleteconsignor"
                                                                name="deleteconsignor" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.consignor.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showconsignormenu' id="alldataconsignor"
                                                                name="alldataconsignor" value="1">
                                                        @else
                                                            -
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.logisticapi.add') == '1' || $user_id == 1)
                                                <tr id="logisticapi">
                                                    <td>API</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='logisticapi' id="showlogisticapimenu"
                                                            name="showlogisticapimenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticapi.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticapimenu' id="addlogisticapi"
                                                                name="addlogisticapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticapi.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticapimenu' id="viewlogisticapi"
                                                                name="viewlogisticapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticapi.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticapimenu' id="editlogisticapi"
                                                                name="editlogisticapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticapi.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticapimenu' id="deletelogisticapi"
                                                                name="deletelogisticapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.logisticapi.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlogisticapimenu' id="alldatalogisticapi"
                                                                name="alldatalogisticapi" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.watermark.add') == '1' || $user_id == 1)
                                                <tr id="watermark">
                                                    <td>PDF Watermark</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='watermark' id="showwatermarkmenu"
                                                            name="showwatermarkmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.watermark.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showwatermarkmenu' id="addwatermark"
                                                                name="addwatermark" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.watermark.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showwatermarkmenu' id="viewwatermark"
                                                                name="viewwatermark" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.watermark.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showwatermarkmenu' id="editwatermark"
                                                                name="editwatermark" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.watermark.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showwatermarkmenu' id="deletewatermark"
                                                                name="deletewatermark" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.watermark.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showwatermarkmenu' id="alldatawatermark"
                                                                name="alldatawatermark" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.downloadcopysetting.add') == '1' || $user_id == 1)
                                                <tr id="downloadcopysetting">
                                                    <td>Download Copy Setting</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='downloadcopysetting'
                                                            id="showdownloadcopysettingmenu"
                                                            name="showdownloadcopysettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.downloadcopysetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdownloadcopysettingmenu'
                                                                id="adddownloadcopysetting"
                                                                name="adddownloadcopysetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.downloadcopysetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdownloadcopysettingmenu'
                                                                id="viewdownloadcopysetting"
                                                                name="viewdownloadcopysetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.downloadcopysetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdownloadcopysettingmenu'
                                                                id="editdownloadcopysetting"
                                                                name="editdownloadcopysetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.downloadcopysetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdownloadcopysettingmenu'
                                                                id="deletedownloadcopysetting"
                                                                name="deletedownloadcopysetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.downloadcopysetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdownloadcopysettingmenu'
                                                                id="alldatadownloadcopysetting"
                                                                name="alldatadownloadcopysetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.logisticmodule.transporterbilling.add') == '1' || $user_id == 1)
                                                <tr id="transporterbilling">
                                                    <td>Transporter Billing</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='transporterbilling'
                                                            id="showtransporterbillingmenu"
                                                            name="showtransporterbillingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.transporterbilling.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtransporterbillingmenu'
                                                                id="addtransporterbilling" name="addtransporterbilling"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.transporterbilling.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtransporterbillingmenu'
                                                                id="viewtransporterbilling"
                                                                name="viewtransporterbilling" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.transporterbilling.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtransporterbillingmenu'
                                                                id="edittransporterbilling"
                                                                name="edittransporterbilling" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.transporterbilling.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtransporterbillingmenu'
                                                                id="deletetransporterbilling"
                                                                name="deletetransporterbilling" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.logisticmodule.transporterbilling.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtransporterbillingmenu'
                                                                id="alldatatransporterbilling"
                                                                name="alldatatransporterbilling" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12">
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel"
                                                    class="btn btn-secondary float-right cancelbtn">
                                                    Cancel
                                                </button>
                                                <button type="button" id="logisticmodulereset" data-module="logistic"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Reset Logistic Module"
                                                    class="btn iq-bg-danger float-right resetbtn mr-2">
                                                    Reset
                                                </button>
                                                <button type="submit" id="logisticmodulesubmit" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Save"
                                                    class="btn btn-primary float-right my-0 submitBtn">
                                                    Save
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ((Session::has('developer') && Session::get('developer') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Developer Module</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class=" table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right">
                                                    <b>Select All </b>
                                                    <input type="checkbox" id="developerallcheck"
                                                        data-module="developer" class="allcheck">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15% ;">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="developercheckboxes">
                                            @if (session('user_permissions.developermodule.developerdashboard.add') == '1' || $user_id == 1)
                                                <tr id="developerdashboard">
                                                    <td>Developer Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='developerdashboard'
                                                            id="showdeveloperdashboardmenu"
                                                            name="showdeveloperdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.developerdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdeveloperdashboardmenu'
                                                                id="adddeveloperdashboard" name="adddeveloperdashboard"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.developerdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdeveloperdashboardmenu'
                                                                id="viewdeveloperdashboard"
                                                                name="viewdeveloperdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.developerdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdeveloperdashboardmenu'
                                                                id="editdeveloperdashboard"
                                                                name="editdeveloperdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.developerdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdeveloperdashboardmenu'
                                                                id="deletedeveloperdashboard"
                                                                name="deletedeveloperdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.developerdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showdeveloperdashboardmenu'
                                                                id="alldatadeveloperdashboard"
                                                                name="alldatadeveloperdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.developermodule.slowpage.add') == '1' || $user_id == 1)
                                                <tr id="slowpage">
                                                    <td>Slow Page</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='slowpage'
                                                            id="showslowpagemenu" name="showslowpagemenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.slowpage.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showslowpagemenu' id="addslowpage"
                                                                name="addslowpage" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.slowpage.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showslowpagemenu' id="viewslowpage"
                                                                name="viewslowpage" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.slowpage.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showslowpagemenu' id="editslowpage"
                                                                name="editslowpage" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.slowpage.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showslowpagemenu' id="deleteslowpage"
                                                                name="deleteslowpage" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.slowpage.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showslowpagemenu' id="alldataslowpage"
                                                                name="alldataslowpage" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.developermodule.errorlog.add') == '1' || $user_id == 1)
                                                <tr id="errorlog">
                                                    <td>Error Log</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='errorlog'
                                                            id="showerrorlogmenu" name="showerrorlogmenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.errorlog.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showerrorlogmenu' id="adderrorlog"
                                                                name="adderrorlog" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.errorlog.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showerrorlogmenu' id="viewerrorlog"
                                                                name="viewerrorlog" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.errorlog.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showerrorlogmenu' id="editerrorlog"
                                                                name="editerrorlog" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.errorlog.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showerrorlogmenu' id="deleteerrorlog"
                                                                name="deleteerrorlog" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.errorlog.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showerrorlogmenu' id="alldataerrorlog"
                                                                name="alldataerrorlog" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.developermodule.cronjob.add') == '1' || $user_id == 1)
                                                <tr id="cronjob">
                                                    <td>Cron Job</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='cronjob'
                                                            id="showcronjobmenu" name="showcronjobmenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.cronjob.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcronjobmenu' id="addcronjob"
                                                                name="addcronjob" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.cronjob.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcronjobmenu' id="viewcronjob"
                                                                name="viewcronjob" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.cronjob.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcronjobmenu' id="editcronjob"
                                                                name="editcronjob" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.cronjob.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcronjobmenu' id="deletecronjob"
                                                                name="deletecronjob" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.cronjob.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcronjobmenu' id="alldatacronjob"
                                                                name="alldatacronjob" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.developermodule.techdoc.add') == '1' || $user_id == 1)
                                                <tr id="techdoc">
                                                    <td>Technical Documents</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='techdoc'
                                                            id="showtechdocmenu" name="showtechdocmenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.techdoc.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtechdocmenu' id="addtechdoc"
                                                                name="addtechdoc" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.techdoc.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtechdocmenu' id="viewtechdoc"
                                                                name="viewtechdoc" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.techdoc.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtechdocmenu' id="edittechdoc"
                                                                name="edittechdoc" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.techdoc.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtechdocmenu' id="deletetechdoc"
                                                                name="deletetechdoc" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.techdoc.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showtechdocmenu' id="alldatatechdoc"
                                                                name="alldatatechdoc" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.developermodule.versiondoc.add') == '1' || $user_id == 1)
                                                <tr id="versiondoc">
                                                    <td>Version Documents</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='versiondoc' id="showversiondocmenu"
                                                            name="showversiondocmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.versiondoc.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showversiondocmenu' id="addversiondoc"
                                                                name="addversiondoc" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.versiondoc.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showversiondocmenu' id="viewversiondoc"
                                                                name="viewversiondoc" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.versiondoc.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showversiondocmenu' id="editversiondoc"
                                                                name="editversiondoc" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.versiondoc.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showversiondocmenu' id="deleteversiondoc"
                                                                name="deleteversiondoc" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.versiondoc.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showversiondocmenu' id="alldataversiondoc"
                                                                name="alldataversiondoc" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.developermodule.recentactivitydata.add') == '1' || $user_id == 1)
                                                <tr id="recentactivitydata">
                                                    <td>Activity/Recent Data</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='recentactivitydata'
                                                            id="showrecentactivitydatamenu"
                                                            name="showrecentactivitydatamenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.recentactivitydata.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showrecentactivitydatamenu'
                                                                id="addrecentactivitydata" name="addrecentactivitydata"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.recentactivitydata.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showrecentactivitydatamenu'
                                                                id="viewrecentactivitydata"
                                                                name="viewrecentactivitydata" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.recentactivitydata.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showrecentactivitydatamenu'
                                                                id="editrecentactivitydata"
                                                                name="editrecentactivitydata" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.recentactivitydata.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showrecentactivitydatamenu'
                                                                id="deleterecentactivitydata"
                                                                name="deleterecentactivitydata" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.recentactivitydata.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showrecentactivitydatamenu'
                                                                id="alldatarecentactivitydata"
                                                                name="alldatarecentactivitydata" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.developermodule.automatetest.add') == '1' || $user_id == 1)
                                                <tr id="automatetest">
                                                    <td>Automate Test</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='automatetest' id="showautomatetestmenu"
                                                            name="showautomatetestmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.automatetest.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showautomatetestmenu' id="addautomatetest"
                                                                name="addautomatetest" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.automatetest.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showautomatetestmenu' id="viewautomatetest"
                                                                name="viewautomatetest" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.automatetest.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showautomatetestmenu' id="editautomatetest"
                                                                name="editautomatetest" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.automatetest.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showautomatetestmenu'
                                                                id="deleteautomatetest" name="deleteautomatetest"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.automatetest.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showautomatetestmenu'
                                                                id="alldataautomatetest" name="alldataautomatetest"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.developermodule.cleardata.add') == '1' || $user_id == 1)
                                                <tr id="cleardata">
                                                    <td>Clear Data</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='cleardata' id="showcleardatamenu"
                                                            name="showcleardatamenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.cleardata.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcleardatamenu' id="addcleardata"
                                                                name="addcleardata" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.cleardata.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcleardatamenu' id="viewcleardata"
                                                                name="viewcleardata" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.cleardata.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcleardatamenu' id="editcleardata"
                                                                name="editcleardata" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.cleardata.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcleardatamenu' id="deletecleardata"
                                                                name="deletecleardata" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.cleardata.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcleardatamenu' id="alldatacleardata"
                                                                name="alldatacleardata" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.developermodule.queues.add') == '1' || $user_id == 1)
                                                <tr id="queues">
                                                    <td>Queues</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='queues'
                                                            id="showqueuesmenu" name="showqueuesmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.queues.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showqueuesmenu' id="addqueues"
                                                                name="addqueues" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.queues.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showqueuesmenu' id="viewqueues"
                                                                name="viewqueues" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.queues.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showqueuesmenu' id="editqueues"
                                                                name="editqueues" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.queues.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showqueuesmenu' id="deletequeues"
                                                                name="deletequeues" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.developermodule.queues.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showqueuesmenu' id="alldataqueues"
                                                                name="alldataqueues" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12">
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel"
                                                    class="btn btn-secondary float-right cancelbtn">
                                                    Cancel
                                                </button>
                                                <button type="button" id="developermodulereset"
                                                    data-module="developer" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Reset Developer Module"
                                                    class="btn iq-bg-danger float-right resetbtn mr-2">
                                                    Reset
                                                </button>
                                                <button type="submit" id="developermodulesubmit"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Save"
                                                    class="btn btn-primary float-right my-0 submitBtn">
                                                    Save
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ((Session::has('hr') && Session::get('hr') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">HR Modules</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right">
                                                    <b>Select All </b>
                                                    <input type="checkbox" id="hrallcheck" data-module="hr"
                                                        class="allcheck">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15%">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="hrcheckboxes">
                                            @if (session('user_permissions.hrmodule.hrdashboard.add') == '1' || $user_id == 1)
                                                <tr id="hrdashboard">
                                                    <td>HR Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='hrdashboard' id="showhrdashboardmenu"
                                                            name="showhrdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.hrdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showhrdashboardmenu' id="addhrdashboard"
                                                                name="addhrdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.hrdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showhrdashboardmenu' id="viewhrdashboard"
                                                                name="viewhrdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.hrdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showhrdashboardmenu' id="edithrdashboard"
                                                                name="edithrdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.hrdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showhrdashboardmenu' id="deletehrdashboard"
                                                                name="deletehrdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.hrdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showhrdashboardmenu' id="alldatahrdashboard"
                                                                name="alldatahrdashboard" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.hrmodule.employees.add') == '1' || $user_id == 1)
                                                <tr id="employees">
                                                    <td>Employees</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='employees' id="showemployeesmenu"
                                                            name="showemployeesmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.employees.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showemployeesmenu' id="addemployees"
                                                                name="addemployees" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.employees.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showemployeesmenu' id="viewemployees"
                                                                name="viewemployees" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.employees.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showemployeesmenu' id="editemployees"
                                                                name="editemployees" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.employees.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showemployeesmenu' id="deleteemployees"
                                                                name="deleteemployees" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.employees.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showemployeesmenu' id="alldataemployees"
                                                                name="alldataemployees" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                </tr>
                                            @endif
                                            @if (session('user_permissions.hrmodule.companiesholidays.add') == '1' || $user_id == 1)
                                                <tr id="companiesholidays">
                                                    <td>Companies Holidays</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='companiesholidays'
                                                            id="showcompaniesholidaysmenu"
                                                            name="showcompaniesholidaysmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.companiesholidays.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcompaniesholidaysmenu'
                                                                id="addcompaniesholidays" name="addcompaniesholidays"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.companiesholidays.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcompaniesholidaysmenu'
                                                                id="viewcompaniesholidays" name="viewcompaniesholidays"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.companiesholidays.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcompaniesholidaysmenu'
                                                                id="editcompaniesholidays" name="editcompaniesholidays"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.companiesholidays.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcompaniesholidaysmenu'
                                                                id="deletecompaniesholidays"
                                                                name="deletecompaniesholidays" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.companiesholidays.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcompaniesholidaysmenu'
                                                                id="alldatacompaniesholidays"
                                                                name="alldatacompaniesholidays" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.hrmodule.letters.add') == '1' || $user_id == 1)
                                                <tr id="letters">
                                                    <td>letters</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='letters'
                                                            id="showlettersmenu" name="showlettersmenu"
                                                            value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.letters.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlettersmenu' id="addletters"
                                                                name="addletters" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.letters.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlettersmenu' id="viewletters"
                                                                name="viewletters" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.letters.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlettersmenu' id="editletters"
                                                                name="editletters" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.letters.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlettersmenu' id="deleteletters"
                                                                name="deleteletters" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.letters.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showlettersmenu' id="alldataletters"
                                                                name="alldataletters" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.hrmodule.letter_variable_setting.add') == '1' || $user_id == 1)
                                                <tr id="letter_variable_setting">
                                                    <td>letter variable setting</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='letter_variable_setting'
                                                            id="showletter_variable_settingmenu"
                                                            name="showletter_variable_settingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.letter_variable_setting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showletter_variable_settingmenu'
                                                                id="addletter_variable_setting"
                                                                name="addletter_variable_setting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.letter_variable_setting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showletter_variable_settingmenu'
                                                                id="viewletter_variable_setting"
                                                                name="viewletter_variable_setting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.letter_variable_setting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showletter_variable_settingmenu'
                                                                id="editletter_variable_setting"
                                                                name="editletter_variable_setting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.letter_variable_setting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showletter_variable_settingmenu'
                                                                id="deleteletter_variable_setting"
                                                                name="deleteletter_variable_setting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.letter_variable_setting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showletter_variable_settingmenu'
                                                                id="alldataletter_variable_setting"
                                                                name="alldataletter_variable_setting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                </tr>
                                            @endif
                                            @if (session('user_permissions.hrmodule.generate_letter.add') == '1' || $user_id == 1)
                                                <tr id="generate_letter">
                                                    <td>Generate letter</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='generate_letter' id="showgenerate_lettermenu"
                                                            name="showgenerate_lettermenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.generate_letter.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showgenerate_lettermenu'
                                                                id="addgenerate_letter" name="addgenerate_letter"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.generate_letter.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showgenerate_lettermenu'
                                                                id="viewgenerate_letter" name="viewgenerate_letter"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.generate_letter.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showgenerate_lettermenu'
                                                                id="editgenerate_letter" name="editgenerate_letter"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.generate_letter.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showgenerate_lettermenu'
                                                                id="deletegenerate_letter" name="deletegenerate_letter"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.hrmodule.generate_letter.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showgenerate_lettermenu'
                                                                id="alldatagenerate_letter"
                                                                name="alldatagenerate_letter" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12">
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel"
                                                    class="btn btn-secondary float-right cancelbtn">
                                                    Cancel
                                                </button>
                                                <button type="button" id="hrmodulereset" data-module="hr"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Reset hr Module"
                                                    class="btn iq-bg-danger float-right resetbtn mr-2">
                                                    Reset
                                                </button>
                                                <button type="submit" id="hrmodulesubmit" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Save"
                                                    class="btn btn-primary float-right my-0 submitBtn">
                                                    Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ((Session::has('account') && Session::get('account') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Account Modules</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right"><b>Select All</b>
                                                    <input type="checkbox" id="accountallcheck" data-module="account"
                                                        class="allcheck">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15%">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="accountcheckboxes">

                                            {{-- ── Income ── --}}
                                            @if (session('user_permissions.accountmodule.income.add') == '1' || $user_id == 1)
                                                <tr id="income">
                                                    <td>Income</td>
                                                    <td><input type="checkbox" class="clickmenu" data-value='income'
                                                            id="showincomemenu" name="showincomemenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.income.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showincomemenu' id="addincome"
                                                                name="addincome" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.income.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showincomemenu' id="viewincome"
                                                                name="viewincome" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.income.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showincomemenu' id="editincome"
                                                                name="editincome" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.income.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showincomemenu' id="deleteincome"
                                                                name="deleteincome" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.income.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showincomemenu' id="alldataincome"
                                                                name="alldataincome" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Expense ── --}}
                                            @if (session('user_permissions.accountmodule.expense.add') == '1' || $user_id == 1)
                                                <tr id="expense">
                                                    <td>Expense</td>
                                                    <td><input type="checkbox" class="clickmenu" data-value='expense'
                                                            id="showexpensemenu" name="showexpensemenu"
                                                            value="1"></td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.expense.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showexpensemenu' id="addexpense"
                                                                name="addexpense" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.expense.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showexpensemenu' id="viewexpense"
                                                                name="viewexpense" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.expense.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showexpensemenu' id="editexpense"
                                                                name="editexpense" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.expense.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showexpensemenu' id="deleteexpense"
                                                                name="deleteexpense" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.expense.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showexpensemenu' id="alldataexpense"
                                                                name="alldataexpense" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Ledger ── --}}
                                            @if (session('user_permissions.accountmodule.ledger.add') == '1' || $user_id == 1)
                                                <tr id="ledger">
                                                    <td>Ledger</td>
                                                    <td><input type="checkbox" class="clickmenu" data-value='ledger'
                                                            id="showledgermenu" name="showledgermenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.ledger.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showledgermenu' id="addledger"
                                                                name="addledger" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.ledger.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showledgermenu' id="viewledger"
                                                                name="viewledger" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.ledger.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showledgermenu' id="editledger"
                                                                name="editledger" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.ledger.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showledgermenu' id="deleteledger"
                                                                name="deleteledger" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.ledger.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showledgermenu' id="alldataledger"
                                                                name="alldataledger" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Category ── --}}
                                            @if (session('user_permissions.accountmodule.category.add') == '1' || $user_id == 1)
                                                <tr id="category">
                                                    <td>Category</td>
                                                    <td><input type="checkbox" class="clickmenu" data-value='category'
                                                            id="showcategorymenu" name="showcategorymenu"
                                                            value="1"></td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.category.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcategorymenu' id="addcategory"
                                                                name="addcategory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.category.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcategorymenu' id="viewcategory"
                                                                name="viewcategory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.category.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcategorymenu' id="editcategory"
                                                                name="editcategory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.category.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcategorymenu' id="deletecategory"
                                                                name="deletecategory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.category.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showcategorymenu' id="alldatacategory"
                                                                name="alldatacategory" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.accountmodule.accountcustomer.add') == '1' || $user_id == 1)
                                                <tr id="accountcustomer">
                                                    <td>Customer</td>
                                                    <td><input type="checkbox" class="clickmenu" data-value='accountcustomer'
                                                            id="showaccountcustomermenu" name="showaccountcustomermenu"
                                                            value="1"></td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.accountcustomer.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showaccountcustomermenu' id="addaccountcustomer"
                                                                name="addaccountcustomer" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.accountcustomer.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showaccountcustomermenu' id="viewaccountcustomer"
                                                                name="viewaccountcustomer" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.accountcustomer.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showaccountcustomermenu' id="editaccountcustomer"
                                                                name="editaccountcustomer" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.accountcustomer.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showaccountcustomermenu' id="deleteaccountcustomer"
                                                                name="deleteaccountcustomer" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.accountcustomer.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showaccountcustomermenu' id="alldataaccountcustomer"
                                                                name="alldataaccountcustomer" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            {{-- ── Account Form Setting (expandable, like Invoice Settings) ── --}}
                                            @if (session('user_permissions.accountmodule.accountformsetting.add') == '1' || $user_id == 1)
                                                <tr>
                                                    <td class="p-0" style="border-right: 0;">
                                                        <span class="btn expandsettingsbutton"
                                                            data-target="account_settings_rows">
                                                            <i class="ri ri-2x ri-arrow-right-circle-fill"></i>
                                                            Account Settings
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (session('user_permissions.accountmodule.accountformsetting.add') == '1' || $user_id == 1)
                                                <tr id="accountformsetting"
                                                    class="account_settings_rows subsettingrows">
                                                    <td>Account Form Setting</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu"
                                                            data-value='accountformsetting'
                                                            id="showaccountformsettingmenu"
                                                            name="showaccountformsettingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.accountformsetting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showaccountformsettingmenu'
                                                                id="addaccountformsetting" name="addaccountformsetting"
                                                                value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.accountformsetting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showaccountformsettingmenu'
                                                                id="viewaccountformsetting"
                                                                name="viewaccountformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.accountformsetting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showaccountformsettingmenu'
                                                                id="editaccountformsetting"
                                                                name="editaccountformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.accountformsetting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showaccountformsettingmenu'
                                                                id="deleteaccountformsetting"
                                                                name="deleteaccountformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.accountmodule.accountformsetting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu"
                                                                data-value='showaccountformsettingmenu'
                                                                id="alldataaccountformsetting"
                                                                name="alldataaccountformsetting" value="1">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12 text-right">
                                                <button type="submit" id="accountmodulesubmit"
                                                    class="btn btn-primary submitBtn" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Save">Save</button>
                                                <button type="button" id="accountmodulereset" data-module="account"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Reset Account Module"
                                                    class="btn iq-bg-danger resetbtn mr-2">Reset</button>
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel"
                                                    class="btn btn-secondary cancelbtn">Cancel</button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endif
                        @if ((Session::has('society') && Session::get('society') == 'yes') || $user_id == 1)
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Society Modules</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <table class="table table-bordered table-responsive-sm w-100 text-center p-0">
                                        <thead>
                                            <tr>
                                                <th colspan="7" class="text-right">
                                                    <b>Select All </b>
                                                    <input type="checkbox" id="societyallcheck" data-module="society" class="allcheck">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col">Menus</th>
                                                <th scope="col" style="width:15%">Show/Hide</th>
                                                <th scope="col">Add</th>
                                                <th scope="col">View</th>
                                                <th scope="col">Edit</th>
                                                <th scope="col">Delete</th>
                                                <th scope="col">All Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="societycheckboxes">
                                            @if (session('user_permissions.societymodule.familyrelation.add') == '1' || $user_id == 1)
                                                <tr id="familyrelation">
                                                    <td>Family Relation</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='familyrelation'
                                                            id="showfamilyrelationmenu" name="showfamilyrelationmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.familyrelation.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showfamilyrelationmenu'
                                                                id="addfamilyrelation" name="addfamilyrelation" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.familyrelation.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showfamilyrelationmenu'
                                                                id="viewfamilyrelation" name="viewfamilyrelation" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.familyrelation.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showfamilyrelationmenu'
                                                                id="editfamilyrelation" name="editfamilyrelation" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.familyrelation.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showfamilyrelationmenu'
                                                                id="deletefamilyrelation" name="deletefamilyrelation" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.familyrelation.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showfamilyrelationmenu'
                                                                id="alldatafamilyrelation" name="alldatafamilyrelation" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.societymodule.businesscategory.add') == '1' || $user_id == 1)
                                                <tr id="businesscategory">
                                                    <td>Business Category</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='businesscategory'
                                                            id="showbusinesscategorymenu" name="showbusinesscategorymenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.businesscategory.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showbusinesscategorymenu'
                                                                id="addbusinesscategory" name="addbusinesscategory" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.businesscategory.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showbusinesscategorymenu'
                                                                id="viewbusinesscategory" name="viewbusinesscategory" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.businesscategory.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showbusinesscategorymenu'
                                                                id="editbusinesscategory" name="editbusinesscategory" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.businesscategory.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showbusinesscategorymenu'
                                                                id="deletebusinesscategory" name="deletebusinesscategory" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.businesscategory.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showbusinesscategorymenu'
                                                                id="alldatabusinesscategory" name="alldatabusinesscategory" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.societymodule.businessubcategory.add') == '1' || $user_id == 1)
                                                <tr id="businessubcategory">
                                                    <td>Business SubCategory</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='businessubcategory'
                                                            id="showbusinessubcategorymenu" name="showbusinessubcategorymenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.businessubcategory.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showbusinessubcategorymenu'
                                                                id="addbusinessubcategory" name="addbusinessubcategory" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.businessubcategory.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showbusinessubcategorymenu'
                                                                id="viewbusinessubcategory" name="viewbusinessubcategory" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.businessubcategory.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showbusinessubcategorymenu'
                                                                id="editbusinessubcategory" name="editbusinessubcategory" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.businessubcategory.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showbusinessubcategorymenu'
                                                                id="deletebusinessubcategory" name="deletebusinessubcategory" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.businessubcategory.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showbusinessubcategorymenu'
                                                                id="alldatabusinessubcategory" name="alldatabusinessubcategory" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            {{-- ── Member Dashboard ── --}}
                                            @if (session('user_permissions.societymodule.memberdashboard.add') == '1' || $user_id == 1)
                                                <tr id="memberdashboard">
                                                    <td>Member Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='memberdashboard'
                                                            id="showmemberdashboardmenu" name="showmemberdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.memberdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmemberdashboardmenu'
                                                                id="addmemberdashboard" name="addmemberdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.memberdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmemberdashboardmenu'
                                                                id="viewmemberdashboard" name="viewmemberdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.memberdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmemberdashboardmenu'
                                                                id="editmemberdashboard" name="editmemberdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.memberdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmemberdashboardmenu'
                                                                id="deletememberdashboard" name="deletememberdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.memberdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmemberdashboardmenu'
                                                                id="alldatamemberdashboard" name="alldatamemberdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Member ── --}}
                                            @if (session('user_permissions.societymodule.member.add') == '1' || $user_id == 1)
                                                <tr id="member">
                                                    <td>Member</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='member'
                                                            id="showmembermenu" name="showmembermenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.member.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmembermenu'
                                                                id="addmember" name="addmember" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.member.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmembermenu'
                                                                id="viewmember" name="viewmember" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.member.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmembermenu'
                                                                id="editmember" name="editmember" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.member.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmembermenu'
                                                                id="deletemember" name="deletemember" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.member.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmembermenu'
                                                                id="alldatamember" name="alldatamember" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Family Members ── --}}
                                            @if (session('user_permissions.societymodule.familymembers.add') == '1' || $user_id == 1)
                                                <tr id="familymembers">
                                                    <td>Family Members</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='familymembers'
                                                            id="showfamilymembersmenu" name="showfamilymembersmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.familymembers.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showfamilymembersmenu'
                                                                id="addfamilymembers" name="addfamilymembers" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.familymembers.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showfamilymembersmenu'
                                                                id="viewfamilymembers" name="viewfamilymembers" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.familymembers.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showfamilymembersmenu'
                                                                id="editfamilymembers" name="editfamilymembers" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.familymembers.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showfamilymembersmenu'
                                                                id="deletefamilymembers" name="deletefamilymembers" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.familymembers.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showfamilymembersmenu'
                                                                id="alldatafamilymembers" name="alldatafamilymembers" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Karobari Dashboard ── --}}
                                            @if (session('user_permissions.societymodule.karobaridashboard.add') == '1' || $user_id == 1)
                                                <tr id="karobaridashboard">
                                                    <td>Karobari Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='karobaridashboard'
                                                            id="showkarobaridashboardmenu" name="showkarobaridashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobaridashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobaridashboardmenu'
                                                                id="addkarobaridashboard" name="addkarobaridashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobaridashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobaridashboardmenu'
                                                                id="viewkarobaridashboard" name="viewkarobaridashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobaridashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobaridashboardmenu'
                                                                id="editkarobaridashboard" name="editkarobaridashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobaridashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobaridashboardmenu'
                                                                id="deletekarobaridashboard" name="deletekarobaridashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobaridashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobaridashboardmenu'
                                                                id="alldatakarobaridashboard" name="alldatakarobaridashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Karobari Member ── --}}
                                            @if (session('user_permissions.societymodule.karobarimember.add') == '1' || $user_id == 1)
                                                <tr id="karobarimember">
                                                    <td>Karobari Member</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='karobarimember'
                                                            id="showkarobarimembermenu" name="showkarobarimembermenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobarimember.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobarimembermenu'
                                                                id="addkarobarimember" name="addkarobarimember" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobarimember.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobarimembermenu'
                                                                id="viewkarobarimember" name="viewkarobarimember" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobarimember.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobarimembermenu'
                                                                id="editkarobarimember" name="editkarobarimember" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobarimember.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobarimembermenu'
                                                                id="deletekarobarimember" name="deletekarobarimember" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobarimember.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobarimembermenu'
                                                                id="alldatakarobarimember" name="alldatakarobarimember" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Karobari Meeting ── --}}
                                            @if (session('user_permissions.societymodule.karobarimeeting.add') == '1' || $user_id == 1)
                                                <tr id="karobarimeeting">
                                                    <td>Karobari Meeting</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='karobarimeeting'
                                                            id="showkarobarimeetingmenu" name="showkarobarimeetingmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobarimeeting.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobarimeetingmenu'
                                                                id="addkarobarimeeting" name="addkarobarimeeting" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobarimeeting.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobarimeetingmenu'
                                                                id="viewkarobarimeeting" name="viewkarobarimeeting" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobarimeeting.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobarimeetingmenu'
                                                                id="editkarobarimeeting" name="editkarobarimeeting" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobarimeeting.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobarimeetingmenu'
                                                                id="deletekarobarimeeting" name="deletekarobarimeeting" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.karobarimeeting.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showkarobarimeetingmenu'
                                                                id="alldatakarobarimeeting" name="alldatakarobarimeeting" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Meeting Attendance ── --}}
                                            @if (session('user_permissions.societymodule.meetingattendance.add') == '1' || $user_id == 1)
                                                <tr id="meetingattendance">
                                                    <td>Meeting Attendance</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='meetingattendance'
                                                            id="showmeetingattendancemenu" name="showmeetingattendancemenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.meetingattendance.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmeetingattendancemenu'
                                                                id="addmeetingattendance" name="addmeetingattendance" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.meetingattendance.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmeetingattendancemenu'
                                                                id="viewmeetingattendance" name="viewmeetingattendance" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.meetingattendance.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmeetingattendancemenu'
                                                                id="editmeetingattendance" name="editmeetingattendance" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.meetingattendance.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmeetingattendancemenu'
                                                                id="deletemeetingattendance" name="deletemeetingattendance" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.meetingattendance.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showmeetingattendancemenu'
                                                                id="alldatameetingattendance" name="alldatameetingattendance" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Donation Dashboard ── --}}
                                            @if (session('user_permissions.societymodule.donationdashboard.add') == '1' || $user_id == 1)
                                                <tr id="donationdashboard">
                                                    <td>Donation Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='donationdashboard'
                                                            id="showdonationdashboardmenu" name="showdonationdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donationdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationdashboardmenu'
                                                                id="adddonationdashboard" name="adddonationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donationdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationdashboardmenu'
                                                                id="viewdonationdashboard" name="viewdonationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donationdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationdashboardmenu'
                                                                id="editdonationdashboard" name="editdonationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donationdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationdashboardmenu'
                                                                id="deletedonationdashboard" name="deletedonationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donationdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationdashboardmenu'
                                                                id="alldatadonationdashboard" name="alldatadonationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Donation Type ── --}}
                                            @if (session('user_permissions.societymodule.donationtype.add') == '1' || $user_id == 1)
                                                <tr id="donationtype">
                                                    <td>Donation Type</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='donationtype'
                                                            id="showdonationtypemenu" name="showdonationtypemenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donationtype.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationtypemenu'
                                                                id="adddonationtype" name="adddonationtype" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donationtype.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationtypemenu'
                                                                id="viewdonationtype" name="viewdonationtype" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donationtype.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationtypemenu'
                                                                id="editdonationtype" name="editdonationtype" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donationtype.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationtypemenu'
                                                                id="deletedonationtype" name="deletedonationtype" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donationtype.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationtypemenu'
                                                                id="alldatadonationtype" name="alldatadonationtype" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Donation ── --}}
                                            @if (session('user_permissions.societymodule.donation.add') == '1' || $user_id == 1)
                                                <tr id="donation">
                                                    <td>Donation</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='donation'
                                                            id="showdonationmenu" name="showdonationmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donation.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationmenu'
                                                                id="adddonation" name="adddonation" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donation.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationmenu'
                                                                id="viewdonation" name="viewdonation" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donation.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationmenu'
                                                                id="editdonation" name="editdonation" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donation.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationmenu'
                                                                id="deletedonation" name="deletedonation" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.donation.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showdonationmenu'
                                                                id="alldatadonation" name="alldatadonation" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Post Dashboard ── --}}
                                            @if (session('user_permissions.societymodule.postdashboard.add') == '1' || $user_id == 1)
                                                <tr id="postdashboard">
                                                    <td>Post Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='postdashboard'
                                                            id="showpostdashboardmenu" name="showpostdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostdashboardmenu'
                                                                id="addpostdashboard" name="addpostdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostdashboardmenu'
                                                                id="viewpostdashboard" name="viewpostdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostdashboardmenu'
                                                                id="editpostdashboard" name="editpostdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostdashboardmenu'
                                                                id="deletepostdashboard" name="deletepostdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostdashboardmenu'
                                                                id="alldatapostdashboard" name="alldatapostdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Post ── --}}
                                            @if (session('user_permissions.societymodule.post.add') == '1' || $user_id == 1)
                                                <tr id="post">
                                                    <td>Post</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='post'
                                                            id="showpostmenu" name="showpostmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.post.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostmenu'
                                                                id="addpost" name="addpost" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.post.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostmenu'
                                                                id="viewpost" name="viewpost" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.post.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostmenu'
                                                                id="editpost" name="editpost" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.post.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostmenu'
                                                                id="deletepost" name="deletepost" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.post.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostmenu'
                                                                id="alldatapost" name="alldatapost" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Post Comment ── --}}
                                            @if (session('user_permissions.societymodule.postcomment.add') == '1' || $user_id == 1)
                                                <tr id="postcomment">
                                                    <td>Post Comment</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='postcomment'
                                                            id="showpostcommentmenu" name="showpostcommentmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postcomment.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostcommentmenu'
                                                                id="addpostcomment" name="addpostcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postcomment.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostcommentmenu'
                                                                id="viewpostcomment" name="viewpostcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postcomment.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostcommentmenu'
                                                                id="editpostcomment" name="editpostcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postcomment.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostcommentmenu'
                                                                id="deletepostcomment" name="deletepostcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postcomment.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostcommentmenu'
                                                                id="alldatapostcomment" name="alldatapostcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Post Share ── --}}
                                            @if (session('user_permissions.societymodule.postshare.add') == '1' || $user_id == 1)
                                                <tr id="postshare">
                                                    <td>Post Share</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='postshare'
                                                            id="showpostsharemenu" name="showpostsharemenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postshare.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostsharemenu'
                                                                id="addpostshare" name="addpostshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postshare.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostsharemenu'
                                                                id="viewpostshare" name="viewpostshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postshare.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostsharemenu'
                                                                id="editpostshare" name="editpostshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postshare.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostsharemenu'
                                                                id="deletepostshare" name="deletepostshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postshare.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostsharemenu'
                                                                id="alldatapostshare" name="alldatapostshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.societymodule.postlike.add') == '1' || $user_id == 1)
                                                <tr id="postlike">
                                                    <td>Post Like</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='postlike'
                                                            id="showpostlikemenu" name="showpostlikemenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postlike.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostlikemenu'
                                                                id="addpostlike" name="addpostlike" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postlike.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostlikemenu'
                                                                id="viewpostlike" name="viewpostlike" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postlike.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostlikemenu'
                                                                id="editpostlike" name="editpostlike" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postlike.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostlikemenu'
                                                                id="deletepostlike" name="deletepostlike" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.postlike.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpostlikemenu'
                                                                id="alldatapostlike" name="alldatapostlike" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            {{-- ── Job Dashboard ── --}}
                                            @if (session('user_permissions.societymodule.jobdashboard.add') == '1' || $user_id == 1)
                                                <tr id="jobdashboard">
                                                    <td>Job Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='jobdashboard'
                                                            id="showjobdashboardmenu" name="showjobdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobdashboardmenu'
                                                                id="addjobdashboard" name="addjobdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobdashboardmenu'
                                                                id="viewjobdashboard" name="viewjobdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobdashboardmenu'
                                                                id="editjobdashboard" name="editjobdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobdashboardmenu'
                                                                id="deletejobdashboard" name="deletejobdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobdashboardmenu'
                                                                id="alldatajobdashboard" name="alldatajobdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Job ── --}}
                                            @if (session('user_permissions.societymodule.job.add') == '1' || $user_id == 1)
                                                <tr id="job">
                                                    <td>Job</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='job'
                                                            id="showjobmenu" name="showjobmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.job.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobmenu'
                                                                id="addjob" name="addjob" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.job.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobmenu'
                                                                id="viewjob" name="viewjob" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.job.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobmenu'
                                                                id="editjob" name="editjob" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.job.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobmenu'
                                                                id="deletejob" name="deletejob" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.job.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobmenu'
                                                                id="alldatajob" name="alldatajob" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Job Comment ── --}}
                                            @if (session('user_permissions.societymodule.jobcomment.add') == '1' || $user_id == 1)
                                                <tr id="jobcomment">
                                                    <td>Job Comment</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='jobcomment'
                                                            id="showjobcommentmenu" name="showjobcommentmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobcomment.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobcommentmenu'
                                                                id="addjobcomment" name="addjobcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobcomment.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobcommentmenu'
                                                                id="viewjobcomment" name="viewjobcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobcomment.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobcommentmenu'
                                                                id="editjobcomment" name="editjobcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobcomment.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobcommentmenu'
                                                                id="deletejobcomment" name="deletejobcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobcomment.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobcommentmenu'
                                                                id="alldatajobcomment" name="alldatajobcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Job Share ── --}}
                                            @if (session('user_permissions.societymodule.jobshare.add') == '1' || $user_id == 1)
                                                <tr id="jobshare">
                                                    <td>Job Share</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='jobshare'
                                                            id="showjobsharemenu" name="showjobsharemenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobshare.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobsharemenu'
                                                                id="addjobshare" name="addjobshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobshare.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobsharemenu'
                                                                id="viewjobshare" name="viewjobshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobshare.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobsharemenu'
                                                                id="editjobshare" name="editjobshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobshare.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobsharemenu'
                                                                id="deletejobshare" name="deletejobshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.jobshare.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjobsharemenu'
                                                                id="alldatajobshare" name="alldatajobshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.societymodule.joblike.add') == '1' || $user_id == 1)
                                                <tr id="joblike">
                                                    <td>Job Like</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='joblike'
                                                            id="showjoblikemenu" name="showjoblikemenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.joblike.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjoblikemenu'
                                                                id="addjoblike" name="addjoblike" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.joblike.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjoblikemenu'
                                                                id="viewjoblike" name="viewjoblike" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.joblike.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjoblikemenu'
                                                                id="editjoblike" name="editjoblike" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.joblike.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjoblikemenu'
                                                                id="deletejoblike" name="deletejoblike" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.joblike.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showjoblikemenu'
                                                                id="alldatajoblike" name="alldatajoblike" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            {{-- ── Event Dashboard ── --}}
                                            @if (session('user_permissions.societymodule.eventdashboard.add') == '1' || $user_id == 1)
                                                <tr id="eventdashboard">
                                                    <td>Event Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='eventdashboard'
                                                            id="showeventdashboardmenu" name="showeventdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventdashboardmenu'
                                                                id="addeventdashboard" name="addeventdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventdashboardmenu'
                                                                id="vieweventdashboard" name="vieweventdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventdashboardmenu'
                                                                id="editeventdashboard" name="editeventdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventdashboardmenu'
                                                                id="deleteeventdashboard" name="deleteeventdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventdashboardmenu'
                                                                id="alldataeventdashboard" name="alldataeventdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Event ── --}}
                                            @if (session('user_permissions.societymodule.event.add') == '1' || $user_id == 1)
                                                <tr id="event">
                                                    <td>Event</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='event'
                                                            id="showeventmenu" name="showeventmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.event.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventmenu'
                                                                id="addevent" name="addevent" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.event.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventmenu'
                                                                id="viewevent" name="viewevent" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.event.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventmenu'
                                                                id="editevent" name="editevent" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.event.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventmenu'
                                                                id="deleteevent" name="deleteevent" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.event.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventmenu'
                                                                id="alldataevent" name="alldataevent" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Event Comment ── --}}
                                            @if (session('user_permissions.societymodule.eventcomment.add') == '1' || $user_id == 1)
                                                <tr id="eventcomment">
                                                    <td>Event Comment</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='eventcomment'
                                                            id="showeventcommentmenu" name="showeventcommentmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventcomment.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventcommentmenu'
                                                                id="addeventcomment" name="addeventcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventcomment.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventcommentmenu'
                                                                id="vieweventcomment" name="vieweventcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventcomment.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventcommentmenu'
                                                                id="editeventcomment" name="editeventcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventcomment.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventcommentmenu'
                                                                id="deleteeventcomment" name="deleteeventcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventcomment.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventcommentmenu'
                                                                id="alldataeventcomment" name="alldataeventcomment" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Event Share ── --}}
                                            @if (session('user_permissions.societymodule.eventshare.add') == '1' || $user_id == 1)
                                                <tr id="eventshare">
                                                    <td>Event Share</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='eventshare'
                                                            id="showeventsharemenu" name="showeventsharemenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventshare.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventsharemenu'
                                                                id="addeventshare" name="addeventshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventshare.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventsharemenu'
                                                                id="vieweventshare" name="vieweventshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventshare.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventsharemenu'
                                                                id="editeventshare" name="editeventshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventshare.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventsharemenu'
                                                                id="deleteeventshare" name="deleteeventshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventshare.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventsharemenu'
                                                                id="alldataeventshare" name="alldataeventshare" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (session('user_permissions.societymodule.eventlike.add') == '1' || $user_id == 1)
                                                <tr id="eventlike">
                                                    <td>Event Like</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='eventlike'
                                                            id="showeventlikemenu" name="showeventlikemenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventlike.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventlikemenu'
                                                                id="addeventlike" name="addeventlike" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventlike.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventlikemenu'
                                                                id="vieweventlike" name="vieweventlike" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventlike.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventlikemenu'
                                                                id="editeventlike" name="editeventlike" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventlike.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventlikemenu'
                                                                id="deleteeventlike" name="deleteeventlike" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.eventlike.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showeventlikemenu'
                                                                id="alldataeventlike" name="alldataeventlike" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            {{-- ── Policy Dashboard ── --}}
                                            @if (session('user_permissions.societymodule.policydashboard.add') == '1' || $user_id == 1)
                                                <tr id="policydashboard">
                                                    <td>Policy Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='policydashboard'
                                                            id="showpolicydashboardmenu" name="showpolicydashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.policydashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpolicydashboardmenu'
                                                                id="addpolicydashboard" name="addpolicydashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.policydashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpolicydashboardmenu'
                                                                id="viewpolicydashboard" name="viewpolicydashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.policydashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpolicydashboardmenu'
                                                                id="editpolicydashboard" name="editpolicydashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.policydashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpolicydashboardmenu'
                                                                id="deletepolicydashboard" name="deletepolicydashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.policydashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpolicydashboardmenu'
                                                                id="alldatapolicydashboard" name="alldatapolicydashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Policy ── --}}
                                            @if (session('user_permissions.societymodule.policy.add') == '1' || $user_id == 1)
                                                <tr id="policy">
                                                    <td>Policy</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='policy'
                                                            id="showpolicymenu" name="showpolicymenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.policy.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpolicymenu'
                                                                id="addpolicy" name="addpolicy" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.policy.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpolicymenu'
                                                                id="viewpolicy" name="viewpolicy" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.policy.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpolicymenu'
                                                                id="editpolicy" name="editpolicy" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.policy.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpolicymenu'
                                                                id="deletepolicy" name="deletepolicy" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.policy.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showpolicymenu'
                                                                id="alldatapolicy" name="alldatapolicy" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Application Dashboard ── --}}
                                            @if (session('user_permissions.societymodule.applicationdashboard.add') == '1' || $user_id == 1)
                                                <tr id="applicationdashboard">
                                                    <td>Application Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='applicationdashboard'
                                                            id="showapplicationdashboardmenu" name="showapplicationdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.applicationdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showapplicationdashboardmenu'
                                                                id="addapplicationdashboard" name="addapplicationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.applicationdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showapplicationdashboardmenu'
                                                                id="viewapplicationdashboard" name="viewapplicationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.applicationdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showapplicationdashboardmenu'
                                                                id="editapplicationdashboard" name="editapplicationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.applicationdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showapplicationdashboardmenu'
                                                                id="deleteapplicationdashboard" name="deleteapplicationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.applicationdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showapplicationdashboardmenu'
                                                                id="alldataapplicationdashboard" name="alldataapplicationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Application ── --}}
                                            @if (session('user_permissions.societymodule.application.add') == '1' || $user_id == 1)
                                                <tr id="application">
                                                    <td>Application</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='application'
                                                            id="showapplicationmenu" name="showapplicationmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.application.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showapplicationmenu'
                                                                id="addapplication" name="addapplication" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.application.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showapplicationmenu'
                                                                id="viewapplication" name="viewapplication" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.application.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showapplicationmenu'
                                                                id="editapplication" name="editapplication" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.application.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showapplicationmenu'
                                                                id="deleteapplication" name="deleteapplication" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.application.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='showapplicationmenu'
                                                                id="alldataapplication" name="alldataapplication" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Notification Dashboard ── --}}
                                            @if (session('user_permissions.societymodule.notificationdashboard.add') == '1' || $user_id == 1)
                                                <tr id="notificationdashboard">
                                                    <td>Notification Dashboard</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='notificationdashboard'
                                                            id="shownotificationdashboardmenu" name="shownotificationdashboardmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.notificationdashboard.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='shownotificationdashboardmenu'
                                                                id="addnotificationdashboard" name="addnotificationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.notificationdashboard.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='shownotificationdashboardmenu'
                                                                id="viewnotificationdashboard" name="viewnotificationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.notificationdashboard.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='shownotificationdashboardmenu'
                                                                id="editnotificationdashboard" name="editnotificationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.notificationdashboard.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='shownotificationdashboardmenu'
                                                                id="deletenotificationdashboard" name="deletenotificationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.notificationdashboard.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='shownotificationdashboardmenu'
                                                                id="alldatanotificationdashboard" name="alldatanotificationdashboard" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- ── Notification ── --}}
                                            @if (session('user_permissions.societymodule.notification.add') == '1' || $user_id == 1)
                                                <tr id="notification">
                                                    <td>Notification</td>
                                                    <td>
                                                        <input type="checkbox" class="clickmenu" data-value='notification'
                                                            id="shownotificationmenu" name="shownotificationmenu" value="1">
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.notification.add') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='shownotificationmenu'
                                                                id="addnotification" name="addnotification" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.notification.view') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='shownotificationmenu'
                                                                id="viewnotification" name="viewnotification" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.notification.edit') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='shownotificationmenu'
                                                                id="editnotification" name="editnotification" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.notification.delete') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='shownotificationmenu'
                                                                id="deletenotification" name="deletenotification" value="1">
                                                        @else - @endif
                                                    </td>
                                                    <td>
                                                        @if (session('user_permissions.societymodule.notification.alldata') == '1' || $user_id == 1)
                                                            <input type="checkbox" class="clicksubmenu" data-value='shownotificationmenu'
                                                                id="alldatanotification" name="alldatanotification" value="1">
                                                        @else - @endif
                                                    </td>
                                                </tr>
                                            @endif

                                        </tbody>
                                    </table>

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-sm-12">
                                                <button type="button" data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Cancel" class="btn btn-secondary float-right cancelbtn">
                                                    Cancel
                                                </button>
                                                <button type="button" id="societymodulereset" data-module="society"
                                                    data-toggle="tooltip" data-placement="bottom"
                                                    data-original-title="Reset Society Module"
                                                    class="btn iq-bg-danger float-right resetbtn mr-2">
                                                    Reset
                                                </button>
                                                <button type="submit" id="societymodulesubmit" data-toggle="tooltip"
                                                    data-placement="bottom" data-original-title="Save"
                                                    class="btn btn-primary float-right my-0 submitBtn">
                                                    Save
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </form>
    @endif

@endsection

@push('ajax')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
    <script>
        function togglePasswordVisibility() {
            var passwordField = document.getElementById("password");
            var toggleIcon = document.querySelector(".toggle-password");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            }
        }
    </script>
    <script>
        $('document').ready(function() {
            // companyId and userId both are required in every ajax request for all action */*/*/
            // response status == 200 that means response succesfully recieved
            // response status == 500 that means database not found
            // response status == 422 that means api has not got valid or required data

            var userrp = "{{ session('user_permissions.adminmodule.userpermission.add') }}";

            let userrolepermission = '';

            if (userrp != 1) {
                $('.permission-row input[type="checkbox"]').attr('disabled',
                    true); // permission related all checkoboxes disable if user has not permission
            }

            $('.subsettingrows').slideUp();

            $('.expandsettingsbutton').on('click', function() {
                let targetrows = $(this).data('target');
                let isInvoiceSettings = targetrows === 'invoice_settings_rows';
                let isQuotationSettings = targetrows === 'quotation_settings_rows';
                let isLogistitcSettings = targetrows === 'logistitc_settings_rows';
                $('tr.' + targetrows).slideToggle();
                // Toggle 'special-color' class on both the button and the target rows
                $(this).toggleClass('special-color'); // Add/remove class on the button
                $('tr.' + targetrows).toggleClass('special-color'); // Add/remove class on the target rows
                $(this).find('i').toggleClass('ri-arrow-right-circle-fill ri-arrow-down-circle-fill');

                // If 'Invoice Settings' is collapsed, also collapse 'Invoice Other Settings'
                if (isInvoiceSettings) {
                    let isInvoiceSettingsVisible = $('tr.' + targetrows).hasClass("special-color");
                    if (!
                        isInvoiceSettingsVisible
                    ) { // If Invoice Settings is collapsed, collapse Invoice Other Settings as well
                        $('tr.invoice_other_settings_rows').slideUp();
                        $('tr.invoice_other_settings_rows').removeClass('special-color');
                        $('.expandsettingsbutton[data-target="invoice_other_settings_rows"]').find('i')
                            .removeClass('ri-arrow-down-circle-fill').addClass(
                                'ri-arrow-right-circle-fill');
                    }
                }

                // If 'Quotation Settings' is collapsed, also collapse 'Quotation Other Settings'
                else if (isQuotationSettings) {
                    let isQuotationSettingsVisible = $('tr.' + targetrows).hasClass("special-color");
                    if (!
                        isQuotationSettingsVisible
                    ) { // If Invoice Settings is collapsed, collapse Invoice Other Settings as well
                        $('tr.quotation_other_settings_rows').slideUp();
                        $('tr.quotation_other_settings_rows').removeClass('special-color');
                        $('.expandsettingsbutton[data-target="quotation_other_settings_rows"]').find('i')
                            .removeClass('ri-arrow-down-circle-fill').addClass(
                                'ri-arrow-right-circle-fill');
                    }
                }

                // If 'Logistic Settings' is collapsed, also collapse 'Logistic Other Settings'
                else if (islogisticsettingss) {
                    let islogisticsettingssVisible = $('tr.' + targetrows).hasClass("special-color");
                    if (!
                        islogisticsettingssVisible
                    ) { // If Invoice Settings is collapsed, collapse Invoice Other Settings as well
                        $('tr.logistic_other_settings_rows').slideUp();
                        $('tr.logistic_other_settings_rows').removeClass('special-color');
                        $('.expandsettingsbutton[data-target="logistic_other_settings_rows"]').find('i')
                            .removeClass('ri-arrow-down-circle-fill').addClass(
                                'ri-arrow-right-circle-fill');
                    }
                }
            });

            // function for get user record - it will return promise
            function getUserData() {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('user.invoiceuserindex') }}",
                        data: {
                            user_id: "{{ session()->get('user_id') }}",
                            company_id: "{{ session()->get('company_id') }}",
                            token: "{{ session()->get('api_token') }}"
                        },
                        success: function(response) {
                            resolve(response);
                        },
                        error: function(error) {
                            reject(error);
                        }
                    });
                });
            }

            async function initialize() {
                try {
                    // Perform AJAX calls concurrently
                    const [userDataResponse] = await Promise.all(
                        [
                            getUserData()
                        ]);

                    // Check if user data is successfully fetched
                    if (userDataResponse.status == 200 && userDataResponse.user != '') {
                        // You can update your HTML with the data here if needed     
                        $.each(userDataResponse.user, function(key, value) {
                            var optionValue = ((value.firstname != null) ? value.firstname : '') + ' ' +
                                ((value.lastname != null) ? value.lastname : '');
                            $('#assignedto').append(
                                `<option value="${value.id}">${optionValue}</option>`);
                        });
                        $('#assignedto').multiselect(
                            'rebuild'); // Rebuild multiselect after appending options 
                        if (userrp != 1) {
                            $('.permission-row input[type="checkbox"]').attr('disabled', true);
                        }

                    } else if (userDataResponse.status == 500) {
                        Toast.fire({
                            icon: "error",
                            title: userDataResponse.message
                        });
                    } else {
                        $('#assignedto').append(`<option> No User Found </option>`);
                    }

                    loaderhide();

                    // Further code execution after successful AJAX calls and HTML appending


                } catch (error) {
                    console.error('Error:', error);
                    Toast.fire({
                        icon: "error",
                        title: "An error occurred while initializing"
                    });

                }
            }

            initialize();

            $.ajax({
                type: 'GET',
                url: "{{ route('userrolepermission.index') }}",
                data: {
                    user_id: "{{ session()->get('user_id') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    token: "{{ session()->get('api_token') }}"
                },
                success: function(response) {
                    if (response.status == 200 && response.userrolepermission != '') {
                        userrolepermission = response.userrolepermission;
                        // You can update your HTML with the data here if needed
                        $.each(userrolepermission, function(key, value) {
                            $('#user_role_permission').append(
                                `<option value='${value.id}'> ${value.role_name}</option>`
                            )
                        });
                    } else {
                        userrolepermission = '';
                        $('#user_role_permission').append(`<option value=""> No Data Found</option>`);
                    }
                    loaderhide();
                },
                error: function(error) {
                    loaderhide();
                    userrolepermission = '';
                    console.log(error);
                    $('#user_role_permission').append(`<option value=""> No Data Found</option>`);
                }
            });

            $('#assignedto').multiselect({
                nonSelectedText: 'Select User',
                enableFiltering: true,
                includeSelectAllOption: true,
                enableCaseInsensitiveFiltering: true
            });

            // show country data in dropdown and set default value according logged in user
            $.ajax({
                type: 'GET',
                url: "{{ route('country.index') }}",
                data: {
                    token: "{{ session()->get('api_token') }}"
                },
                success: function(response) {

                    if (response.status == 200 && response.country != '') {
                        // You can update your HTML with the data here if needed
                        $.each(response.country, function(key, value) {
                            $('#country').append(
                                `<option value='${value.id}'> ${value.country_name}</option>`
                            )
                        });
                        country_id = "{{ session('user')['country_id'] }}";
                        $('#country').val(country_id);
                        loadstate();
                    } else {
                        $('#country').append(`<option> No Data Found</option>`);
                    }
                    loaderhide();
                },
                error: function(xhr, status, error) { // if calling api request error 
                    loaderhide();
                    console.log(xhr
                        .responseText); // Log the full error response for debugging
                    var errorMessage = "";
                    try {
                        var responseJSON = JSON.parse(xhr.responseText);
                        errorMessage = responseJSON.message || "An error occurred";
                    } catch (e) {
                        errorMessage = "An error occurred";
                    }
                    Toast.fire({
                        icon: "error",
                        title: errorMessage
                    });
                }
            });


            // load state in dropdown and select state according to user
            function loadstate(id = 0) {
                $('#state').html(`<option selected="" disabled="">Select your State</option>`);
                let stateSearchUrl = "{{ route('state.search', '__countryId__') }}".replace('__countryId__', id);
                var url = stateSearchUrl;
                if (id == 0) {
                    url = "{{ route('state.search', session('user')['country_id']) }}";
                }
                $.ajax({
                    type: 'GET',
                    url: url,
                    data: {
                        token: "{{ session()->get('api_token') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.state != '') {
                            // You can update your HTML with the data here if needed
                            $.each(response.state, function(key, value) {
                                $('#state').append(
                                    `<option value='${value.id}'> ${value.state_name}</option>`
                                )
                            });
                            if (id == 0) {
                                state_id = "{{ session('user')['state_id'] }}";
                                $('#state').val(state_id);
                                loadcity();
                            }
                        } else {
                            $('#state').append(`<option> No Data Found</option>`);
                        }
                        loaderhide();
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr
                            .responseText); // Log the full error response for debugging
                        var errorMessage = "";
                        try {
                            var responseJSON = JSON.parse(xhr.responseText);
                            errorMessage = responseJSON.message || "An error occurred";
                        } catch (e) {
                            errorMessage = "An error occurred";
                        }
                        Toast.fire({
                            icon: "error",
                            title: errorMessage
                        });
                    }
                });
            }

            // load city in dropdown and select state according to user
            function loadcity(id = 0) {
                $('#city').html(`<option selected="" disabled="">Select your City</option>`);
                let citySearchUrl = "{{ route('city.search', '__stateId__') }}".replace('__stateId__', id);
                url = citySearchUrl;
                if (id == 0) {
                    url = "{{ route('city.search', session('user')['state_id']) }}";
                }
                $.ajax({
                    type: 'GET',
                    url: url,
                    data: {
                        token: "{{ session()->get('api_token') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.city != '') {
                            // You can update your HTML with the data here if needed
                            $.each(response.city, function(key, value) {
                                $('#city').append(
                                    `<option value='${value.id}'> ${value.city_name}</option>`
                                )
                            });
                            if (id == 0) {
                                $('#city').val("{{ session('user')['city_id'] }}");
                            }
                        } else {
                            $('#city').append(`<option> No Data Found</option>`);
                        }
                        loaderhide();
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr
                            .responseText); // Log the full error response for debugging
                        var errorMessage = "";
                        try {
                            var responseJSON = JSON.parse(xhr.responseText);
                            errorMessage = responseJSON.message || "An error occurred";
                        } catch (e) {
                            errorMessage = "An error occurred";
                        }
                        Toast.fire({
                            icon: "error",
                            title: errorMessage
                        });
                    }
                });
            }

            // load state in dropdown when country change
            $('#country').on('change', function() {
                loadershow();
                $('#city').html(`<option selected="" disabled="">Select your city</option>`);
                var country_id = $(this).val();
                loadstate(country_id);
            });

            // load city in dropdown when state select/change
            $('#state').on('change', function() {
                loadershow();
                var state_id = $(this).val();
                loadcity(state_id);
            });

            // check/uncheck all checkboxes module wise
            $(document).on('click', '.allcheck', function() {
                $('#user_role_permission').val(''); //remove role if user modify manually
                if (userrp == 1) {
                    var module = $(this).data('module');
                    if (!$(`#${module}allcheck`).prop('checked')) {
                        $(`#${module}checkboxes input[type="checkbox"]`).prop('checked', false);
                        if (module == 'report') {
                            $('#assignedto option').prop('selected', false);
                        }
                    } else {
                        $(`#${module}checkboxes input[type="checkbox"]`).prop('checked', $(this).prop(
                            'checked'));
                        if (module == 'report') {
                            $('#reportcheckboxes input[type="checkbox"]').prop('checked', $(this).prop(
                                'checked'));
                            $('#assignedto option').prop('selected', true);
                        }
                    }

                    if (module == 'report') {
                        $('#assignedto').multiselect('refresh');
                        $('#assignedto').multiselect('rebuild');
                    }
                }
            })


            // check all checkboxes in the row if click on any menu
            $(document).on('change', '.clickmenu', function() {
                $('#user_role_permission').val(''); //remove role if user modify manually
                // if (userrp == 1) {
                //     value = $(this).data('value');
                //     if (!$(this).prop('checked')) {
                //         $(`#${value} input[type="checkbox"]`).prop('checked', false);
                //         if (value == 'report') {
                //             $('#assignedto option').prop('selected', false);
                //             $('#assignedto').multiselect('refresh');
                //             $('#assignedto').multiselect('rebuild');
                //         }
                //     } else {
                //         $(`#${value} input[type="checkbox"]`).prop('checked', $(this).prop('checked'));
                //         if (value == 'report') {
                //             $('#assignedto option').prop('selected', true);
                //             $('#assignedto').multiselect('refresh');
                //             $('#assignedto').multiselect('rebuild');
                //         }
                //     }
                // }
            })

            // check  mian menu if check any submenu(edit,delete,add...)
            $(document).on('change', '.clicksubmenu', function() {
                $('#user_role_permission').val(''); //remove role if user modify manually
                // if (userrp == 1) {
                //     value = $(this).data('value');
                //     if (!$(`#${value}`).prop('checked')) {
                //         $(`#${value}`).prop('checked', true);
                //     }
                // }
            })


            //for checkboxes reset 
            $(document).on('click', '.resetbtn', function() {
                if (userrp == 1) {
                    var module = $(this).data('module');
                    $(`#${module}checkboxes input[type="checkbox"] , #${module}allcheck`).prop(
                        'checked', false);

                    if (module == 'report') {
                        $('#assignedto option').prop('selected', false);
                        $('#assignedto').multiselect('refresh');
                        $('#assignedto').multiselect('rebuild');
                    }
                }

            })

            // redirect to user list page if click any cancel btn
            $(document).on('click', '.cancelbtn', function() {
                loadershow();
                window.location.href = "{{ route('admin.user') }}"
            });


            $('#user_role_permission').on('change', function() {
                $('.permission-row input[type="checkbox"]').prop('checked', false);
                let user_role = $(this).val();
                if (user_role) {
                    $.each(userrolepermission, function(key, value) {
                        if (value.id == user_role) {
                            rp = JSON.parse(value.role_permissions);
                            if (rp.reportmodule) {
                                var assignedreportuser = rp.reportmodule.report.alldata;
                                if (assignedreportuser != 'null' && assignedreportuser != null) {
                                    assignedreportuser.forEach(function(value) {
                                        $('#assignedto').multiselect('select', value);
                                    });
                                    $('#assignedto').multiselect('rebuild');
                                    if (userrp != 1) {
                                        $('#assignedto option').prop('disabled', true);
                                    }
                                }
                            }
                            $.each(rp, function(key, value) {
                                $.each(value, function(key2, value2) {
                                    $.each(value2, function(key3, value3) {
                                        if (value3 == 1) {
                                            if (key3 == "show") {
                                                $(`#show${key2}menu`).prop(
                                                    'checked', true)
                                            } else {
                                                $(`#${key3}${key2}`).prop(
                                                    'checked',
                                                    true)
                                            }
                                        }
                                    });
                                });
                            });
                        }
                    });
                } else {
                    $('.permission-row input[type="checkbox"]').prop('checked', false);
                }
            });

            //submit form
            $('#userform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                var formdata = new FormData($(this)[0]);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('user.store') }}",
                    data: formdata,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Handle the response from the server
                        if (response.status == 200) {
                            // You can perform additional actions, such as showing a success message or redirecting the user
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            })
                            window.location = "{{ route('admin.user') }}";
                        } else if (response.status == 500) {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            })
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            })
                        }
                        loaderhide();
                    },
                    error: function(xhr, status, error) { // if calling api request error 
                        loaderhide();
                        console.log(xhr
                            .responseText); // Log the full error response for debugging
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#error-' + key).text(value[0]);
                            });
                        } else {
                            var errorMessage = "";
                            try {
                                var responseJSON = JSON.parse(xhr.responseText);
                                errorMessage = responseJSON.message || "An error occurred";
                            } catch (e) {
                                errorMessage = "An error occurred";
                            }
                            Toast.fire({
                                icon: "error",
                                title: errorMessage
                            });
                        }
                    }
                });
            })

        });
    </script>
@endpush
