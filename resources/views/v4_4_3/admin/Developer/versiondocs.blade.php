@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Version Documents
@endsection
@section('table_title')
    Version Features
@endsection

@section('style')
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
@section('table-content')
    @include('partial.versionchangedocuments')
    {{-- Add New Module - Code Guide --}}
    <div class="card">
        <div class="card-header bg-primary">
            <h4>Add New Module - Code Steps Guide</h4>
        </div>
        <div class="card-body">
            <ul>
                <li>
                    <strong>Step 1:</strong> Create the required migrations in the current version
                    migrations folder (individual/master as applicable).
                </li>

                <li>
                    <strong>Step 2:</strong> Create the required models in the current version
                    models directory or in the main models directory (if related to master tables).
                </li>

                <li>
                    <strong>Step 3:</strong> Add new module permissions
                    (e.g., userform, userupdateform, userrolepermissionform,
                    userrolepermissionupdateform).
                </li>

                <li>
                    <strong>Step 4:</strong> Handle the new permissions in
                    <code>api/UserController</code>.
                    Add the permissions array to <code>$modulesConfig</code>
                    (this will automatically manage both add and edit permissions).
                    Ensure the permission names follow the existing project naming structure.
                </li>

                <li>
                    <strong>Step 5:</strong> Add new permissions in
                    <code>api/CompanyController</code> (inside the <code>store()</code> method → <code>$rp</code> variable).
                </li>

                <li>
                    <strong>Step 6:</strong> Add version update code and migration path in
                    <code>versionupdate.blade.php</code> and
                    <code>api/VersionUpdateController</code>.
                </li>

                <li>
                    <strong>Step 7:</strong> Verify new module permissions in
                    <code>AdminLoginController</code>
                    (dashboard permissions and related access checks).
                </li>

                <li>
                    <strong>Step 8:</strong> Create related Web & API controllers.
                </li>

                <li>
                    <strong>Step 9:</strong> Create related views.
                </li>

                <li>
                    <strong>Step 10:</strong> Define related Web & API routes.
                </li>

                <li>
                    <strong>Step 11:</strong> Configure module name in Middleware → Checkpermission.
                </li>

                <li>
                    <strong>Step 12:</strong> Update and configure the navbar and sidebar menus to include the new module
                    links and apply proper permission checks.
                </li>

                <li>
                    <strong>Step 13:</strong> Update and configure the homepage settings under
                    <code>Profile → Others → Home Page</code>.
                </li>

                <li>
                    <strong>Step 14:</strong> Update the company version from version control
                    using Super Admin login.
                </li>
            </ul>
        </div>
    </div>


    {{-- Add New Version - Code Guide --}}
    <div class="card">
        <div class="card-header bg-primary">
            <h4>Add New Version - Code Steps Guide</h4>
        </div>
        <div class="card-body">
            <ul>
                <li>
                    <strong>Step 1:</strong> Copy and rename the latest version folders
                    (Controllers, Models, and Views).
                </li>

                <li>
                    <strong>Step 2:</strong> Update the namespace version in all new files.
                    If changing globally, ensure the migration path name is not affected in
                    <code>VersionUpdateController</code> and
                    <code>CompanyController (store method)</code>.
                </li>

                <li>
                    <strong>Step 3:</strong> Update the latest version reference in
                    <code>web.php</code> and <code>api.php</code>.
                </li>

                <li>
                    <strong>Step 4:</strong> Update the latest version text in the footer
                    copyright section.
                </li>

                <li>
                    <strong>Step 5:</strong> Add version update logic in
                    <code>versionupdate.blade.php</code> and
                    <code>api/VersionUpdateController</code>.
                </li>

                <li>
                    <strong>Step 6:</strong> Update the company version using
                    Super Admin login through version control.
                </li>
            </ul>
        </div>
    </div>
@endsection


@push('ajax')
    <script>
        $('document').ready(function() {

            loaderhide();

        });
    </script>
@endpush
