<?php

namespace App\Http\Controllers\v4_4_1\api;

use App\Models\Proof;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class HrController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $employeeModel, $companiesholidayModel, $letterModel, $letter_variable_settingModel, $generate_letterModel, $data_formateModel;

    public function __construct(Request $request)
    {

        $this->companyId = $request->company_id;
        $this->userId = $request->user_id;

        $this->dbname($this->companyId);
        $user_rp = DB::connection('dynamic_connection')->table('user_permissions')->where('user_id', $this->userId)->value('rp');

        if (empty($user_rp)) {
            $this->customerrorresponse();
        }

        $this->rp = json_decode($user_rp, true);

        $this->masterdbname = DB::connection()->getDatabaseName();
        $this->employeeModel = $this->getmodel('employee');
        $this->companiesholidayModel = $this->getmodel('companiesholiday');
        $this->letterModel = $this->getmodel('letter');
        $this->letter_variable_settingModel = $this->getmodel('letter_variable_setting');
        $this->generate_letterModel = $this->getmodel('generate_letter');
        $this->data_formateModel = $this->getmodel('data_formate');
    }

    public function proofsName()
    {
        if ($this->rp['hrmodule']['employees']['add'] != 1 && $this->rp['hrmodule']['employees']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are unauthorized');
        }

        $proof = Proof::where('is_deleted', 0)->select('id', 'proof_name')->orderBy('proof_name')->get();

        if ($proof->isEmpty()) {
            return $this->successresponse(404, 'message', "No such proof name found!");
        }

        return $this->successresponse(200, 'proof', $proof);
    }

    public function index()
    {
        if ($this->rp['hrmodule']['employees']['view'] != 1) {
            return response()->json([
                'status' => 500,
                'message' => 'You are Unauthorized',
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ]);
        }

        $employees = $this->employeeModel::leftjoin($this->masterdbname . '.country', 'employees.country_id', '=', $this->masterdbname . '.country.id')
            ->leftjoin($this->masterdbname . '.state', 'employees.state_id', '=', $this->masterdbname . '.state.id')
            ->leftjoin($this->masterdbname . '.city', 'employees.city_id', '=', $this->masterdbname . '.city.id')
            ->where("employees.is_deleted", 0)
            ->select(
                'employees.id',
                'employees.first_name',
                'employees.middle_name',
                'employees.surname',
                'employees.email',
                'employees.mobile',
                'employees.house_no_building_name',
                'employees.road_name_area_colony',
                'country.country_name',
                'state.state_name',
                'city.city_name',
                'employees.pincode',
                'employees.holder_name',
                'employees.account_no',
                'employees.swift_code',
                'employees.ifsc_code',
                'employees.branch_name',
                'employees.bank_name',
                'employees.cv_resume',
                'employees.id_proofs',
                'employees.address_proofs',
                'employees.other_attachments',
                'employees.created_by',
                'employees.updated_by',
                'employees.created_at',
                'employees.updated_at'
            )
            ->get();

        if ($employees->isEmpty()) {
            return DataTables::of($employees)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }


        return DataTables::of($employees)->with([
            'status' => 200,
        ])->make(true);
    }

    public function store(Request $request)
    {
        if ($this->rp['hrmodule']['employees']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|numeric|digits_between:10,15',
            'house_no_building_name' => 'nullable|string|max:255',
            'road_name_area_colony' => 'nullable|string|max:255',
            'country' => 'nullable|numeric',
            'state' => 'nullable|numeric',
            'city' => 'nullable|numeric',
            'pincode' => 'nullable|numeric',
            'holder_name' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:50',
            'account_number' => 'nullable|numeric',
            'ifsc_code' => 'nullable|string|min:6',

            'cv_resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',

            'id_proofs' => 'nullable|array',
            'id_proofs.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
            'id_proofs_type' => 'nullable|array',

            'address_proofs' => 'nullable|array',
            'address_proofs.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
            'address_proofs_type' => 'nullable|array',

            'other_attachments' => 'nullable|array',
            'other_attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        /* ===================== BASIC DATA ===================== */
        $employee = $this->employeeModel::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'surname' => $request->surname,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'house_no_building_name' => $request->house_no_building_name,
            'road_name_area_colony' => $request->road_name_area_colony,
            'country_id' => $request->country,
            'state_id' => $request->state,
            'city_id' => $request->city,
            'pincode' => $request->pincode,
            'holder_name' => $request->holder_name,
            'account_no' => $request->account_number,
            'swift_code' => $request->swift_code,
            'ifsc_code' => $request->ifsc_code,
            'bank_name' => $request->bank_name,
            'branch_name' => $request->branch_name,
            'created_by' => $this->userId
        ]);

        $basePath = "uploads/{$this->companyId}/hr/{$employee->id}";

        /* ===================== CV ===================== */
        if ($request->hasFile('cv_resume')) {
            $file = $request->file('cv_resume');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path("$basePath/cv_resume"), $name);
            $employee->cv_resume = "$basePath/cv_resume/$name";
            $employee->save();
        }

        /* ===================== ID PROOFS ===================== */
        if ($request->hasFile('id_proofs')) {

            $data = [];

            foreach ($request->file('id_proofs') as $i => $file) {

                if (empty($request->id_proofs_type[$i])) {
                    continue;
                }

                $name = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path("$basePath/id_proofs"), $name);

                $data[] = [
                    'proof_type' => $request->id_proofs_type[$i],
                    'file_path' => "$basePath/id_proofs/$name"
                ];
            }

            $employee->id_proofs = json_encode($data);
        }

        /* ===================== ADDRESS PROOFS ===================== */
        if ($request->hasFile('address_proofs')) {

            $data = [];

            foreach ($request->file('address_proofs') as $i => $file) {

                if (empty($request->address_proofs_type[$i])) {
                    continue;
                }

                $name = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path("$basePath/address_proofs"), $name);

                $data[] = [
                    'proof_type' => $request->address_proofs_type[$i],
                    'file_path' => "$basePath/address_proofs/$name"
                ];
            }

            $employee->address_proofs = json_encode($data);
        }

        /* ===================== OTHER ATTACHMENTS ===================== */
        if ($request->hasFile('other_attachments')) {

            $data = [];

            foreach ($request->file('other_attachments') as $file) {

                $name = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path("$basePath/other_attachments"), $name);

                $data[] = [
                    'proof_type' => null,
                    'file_path' => "$basePath/other_attachments/$name"
                ];
            }

            $employee->other_attachments = json_encode($data);
        }

        $employee = $employee->save();

        if ($employee) {
            return $this->successresponse(200, 'message', 'Employee succesfully added');
        } else {
            return $this->successresponse(500, 'message', 'Employee not succesfully added !');
        }
    }

    public function edit($id)
    {

        if ($this->rp['hrmodule']['employees']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $employee = $this->employeeModel::find($id);

        if (!$employee) {
            return $this->successresponse(404, 'message', 'No such employee not found!');
        }

        if ($this->rp['hrmodule']['employees']['alldata'] != 1) {
            if ($employee->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        return $this->successresponse(200, 'data', $employee);
    }

    public function update(Request $request, $id)
    {
        if ($this->rp['hrmodule']['employees']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|numeric|digits_between:10,15',
            'house_no_building_name' => 'nullable|string|max:255',
            'road_name_area_colony' => 'nullable|string|max:255',
            'country' => 'nullable|numeric',
            'state' => 'nullable|numeric',
            'city' => 'nullable|numeric',
            'pincode' => 'nullable|numeric',
            'holder_name' => 'nullable|string|max:50',
            'branch_name' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:50',
            'account_number' => 'nullable|numeric',
            'swift_code' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|min:6',

            'cv_resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',

            'id_proofs' => 'nullable|array',
            'id_proofs.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
            'id_proofs_type' => 'nullable|array',

            'address_proofs' => 'nullable|array',
            'address_proofs.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
            'address_proofs_type' => 'nullable|array',

            'other_attachments' => 'nullable|array',
            'other_attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',

            'removed_files' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        $employee = $this->employeeModel::find($id);

        if (!$employee) {
            return $this->successresponse(404, 'message', 'Employee not found');
        }

        if ($this->rp['hrmodule']['employees']['alldata'] != 1) {
            if ($employee->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        $companyId = $this->companyId;
        $basePath = "uploads/{$companyId}/hr/{$employee->id}";

        /* ===================== UPDATE BASIC DATA ===================== */
        $employee->update([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'surname' => $request->surname,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'house_no_building_name' => $request->house_no_building_name,
            'road_name_area_colony' => $request->road_name_area_colony,
            'country_id' => $request->country,
            'state_id' => $request->state,
            'city_id' => $request->city,
            'pincode' => $request->pincode,
            'holder_name' => $request->holder_name,
            'account_no' => $request->account_number,
            'swift_code' => $request->swift_code,
            'ifsc_code' => $request->ifsc_code,
            'bank_name' => $request->bank_name,
            'branch_name' => $request->branch_name,
            'updated_by' => $this->userId
        ]);

        /* ===================== REMOVE SELECTED FILES ===================== */
        if ($request->filled('removed_files')) {

            $removed = $request->removed_files;

            foreach ($removed as $filePath) {

                $fullPath = public_path($filePath);

                // delete file physically
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }

                // remove from JSON arrays
                foreach (['id_proofs', 'address_proofs', 'other_attachments'] as $field) {

                    $existing = json_decode($employee->$field, true) ?: [];

                    foreach ($existing as $key => $item) {
                        if ($item['file_path'] == $filePath) {
                            unset($existing[$key]);
                        }
                    }

                    $employee->$field = json_encode(array_values($existing));
                }
            }

            $employee->save();
        }

        /* ===================== CV RESUME ===================== */
        if ($request->hasFile('cv_resume')) {
            $file = $request->file('cv_resume');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path("$basePath/cv_resume"), $name);

            $employee->cv_resume = "$basePath/cv_resume/$name";
            $employee->save();
        }

        /* ===================== ADD NEW ID PROOFS ===================== */
        if ($request->hasFile('id_proofs')) {

            $existing = json_decode($employee->id_proofs, true) ?: [];
            $newData = [];

            foreach ($request->file('id_proofs') as $i => $file) {

                if (empty($request->id_proofs_type[$i])) {
                    continue;
                }

                $name = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path("$basePath/id_proofs"), $name);

                $newData[] = [
                    'proof_type' => $request->id_proofs_type[$i],
                    'file_path' => "$basePath/id_proofs/$name"
                ];
            }

            $employee->id_proofs = json_encode(array_merge($existing, $newData));
            $employee->save();
        }

        /* ===================== ADD NEW ADDRESS PROOFS ===================== */
        if ($request->hasFile('address_proofs')) {

            $existing = json_decode($employee->address_proofs, true) ?: [];
            $newData = [];

            foreach ($request->file('address_proofs') as $i => $file) {

                if (empty($request->address_proofs_type[$i])) {
                    continue;
                }

                $name = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path("$basePath/address_proofs"), $name);

                $newData[] = [
                    'proof_type' => $request->address_proofs_type[$i],
                    'file_path' => "$basePath/address_proofs/$name"
                ];
            }

            $employee->address_proofs = json_encode(array_merge($existing, $newData));
            $employee->save();
        }

        /* ===================== ADD NEW OTHER ATTACHMENTS ===================== */
        if ($request->hasFile('other_attachments')) {

            $existing = json_decode($employee->other_attachments, true) ?: [];
            $newData = [];

            foreach ($request->file('other_attachments') as $file) {
                $name = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path("$basePath/other_attachments"), $name);

                $newData[] = [
                    'proof_type' => null,
                    'file_path' => "$basePath/other_attachments/$name"
                ];
            }

            $employee->other_attachments = json_encode(array_merge($existing, $newData));
            $employee->save();
        }

        return $this->successresponse(200, 'message', 'Employee updated successfully.');
    }

    public function destroy($id)
    {
        if ($this->rp['hrmodule']['employees']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $employee = $this->employeeModel::find($id);

        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Employee not found'], 404);
        }

        if ($this->rp['hrmodule']['employees']['alldata'] != 1) {
            if ($employee->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        $employee->update([
            "is_deleted" => 1
        ]);

        return $this->successresponse(200, 'message', 'Employee deleted successfully.');
    }

    public function holidayindex(Request $request)
    {
        if ($this->rp['hrmodule']['companiesholidays']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are unauthorized');
        }

        $holiday = $this->companiesholidayModel::where('event_type', 'holiday')->where("is_delete", 0);

        $filters = [
            'filter_date_from'  => 'companiesholidays.event_date',
            'filter_date_to'    => 'companiesholidays.event_date',
        ];

        foreach ($filters as $requestKey => $column) {

            $value = $request->input($requestKey);

            if (!empty($value)) {

                if ($requestKey === 'filter_date_from') {
                    $holiday->whereDate($column, '>=', $value);
                } elseif ($requestKey === 'filter_date_to') {
                    $holiday->whereDate($column, '<=', $value);
                } else {
                    $holiday->where($column, $value);
                }
            }
        }
        $holiday = $holiday->get();

        if ($holiday->isEmpty()) {
            return $this->successresponse(404, 'message', 'No holidays found.');
        }

        return $this->successresponse(200, 'holiday', $holiday);
    }

    public function holidaystore(Request $request)
    {
        // dd($request->all());
        if ($this->rp['hrmodule']['companiesholidays']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $validator = Validator::make($request->all(), [

            'event_type' => 'required|string|max:255',

            'event_title' => 'required_if:event_type,meeting,holiday,seminar',

            'event_date' => 'required',

            'description' => 'nullable',

            'employee_name' => 'required_if:event_type,increment,leave,meeting',

            'candidate_name' => 'required_if:event_type,joining,interview',

            'place_name' => 'required_if:event_type,tours',

        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        $holiday = $this->companiesholidayModel::create([
            'event_type' => $request->event_type,
            'event_title' => $request->event_title,
            'event_date' => $request->event_date,
            'description' => $request->description,
            'employee_name' => $request->employee_name,
            'candidate_name' => $request->candidate_name,
            'place_name' => $request->place_name,
            'created_by' => $this->userId
        ]);
        $add_message = $request->event_type . ' Details Added successfully.';
        if ($holiday) {
            return $this->successresponse(200, 'message', $add_message);
        } else {
            return $this->errorresponse(500, 'message', 'Failed to update ' . $add_message . '.');
        }
    }

    public function holidayedit($id)
    {
        if ($this->rp['hrmodule']['companiesholidays']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $holiday = $this->companiesholidayModel::find($id);

        if (!$holiday) {
            return $this->successresponse(404, 'message', 'No such holiday not found');
        }

        if ($this->rp['hrmodule']['companiesholidays']['alldata'] != 1) {
            if ($holiday->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        return $this->successresponse(200, 'data', $holiday);
    }

    public function holidayupdate(Request $request, $id)
    {
        if ($this->rp['hrmodule']['companiesholidays']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [

            'event_type' => 'required|string|max:255',

            'event_title' => 'required_if:event_type,meeting,holiday,seminar',

            'event_date' => 'required',

            'description' => 'nullable',

            'employee_name' => 'required_if:event_type,increment,leave,meeting',

            'candidate_name' => 'required_if:event_type,joining,interview',

            'place_name' => 'required_if:event_type,tours',

        ]);


        $holiday = $this->companiesholidayModel::find($id);

        if (!$holiday) {
            return $this->successresponse(404, 'message', 'No such holiday not found');
        }

        if ($this->rp['hrmodule']['companiesholidays']['alldata'] != 1) {
            if ($holiday->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        $holiday = $holiday->update([
            'event_type' => $request->event_type,
            'event_title' => $request->event_title,
            'event_date' => $request->event_date,
            'description' => $request->description,
            'employee_name' => $request->employee_name,
            'candidate_name' => $request->candidate_name,
            'place_name' => $request->place_name,
            'updated_by' => $this->userId
        ]);
        $update_message = $request->event_type . ' Details updated successfully.';

        if ($holiday) {
            return $this->successresponse(200, 'message', $update_message);
        }

        return $this->errorresponse(500, 'message', 'Failed to update ' . $update_message . '.');
    }

    public function holidaydestroy($id)
    {

        if ($this->rp['hrmodule']['companiesholidays']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $holiday = $this->companiesholidayModel::find($id);

        if (!$holiday) {
            return response()->json(['status' => 'error', 'message' => 'holiday not found'], 404);
        }

        if ($this->rp['hrmodule']['companiesholidays']['alldata'] != 1) {
            if ($holiday->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        $delete = $holiday->update([
            "is_delete" => 1
        ]);

        if ($delete) {
            return $this->successresponse(200, 'message', 'Holiday deleted successfully.');
        }

        return $this->successresponse(500, 'message', 'Holiday deleted successfully.');
    }

    public function calendarindex(Request $request)
    {
        if ($this->rp['hrmodule']['companiesholidays']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are unauthorized');
        }

        $holiday = $this->companiesholidayModel
            ::whereNot('event_type', 'holiday')
            ->where('is_delete', 0);



        $filters = [
            'filter_event'      => 'companiesholidays.event_type',
            'filter_date_from'  => 'companiesholidays.event_date',
            'filter_date_to'    => 'companiesholidays.event_date',
        ];

        foreach ($filters as $requestKey => $column) {

            $value = $request->input($requestKey);

            if (!empty($value)) {

                if ($requestKey === 'filter_date_from') {
                    $holiday->whereDate($column, '>=', $value);
                } elseif ($requestKey === 'filter_date_to') {
                    $holiday->whereDate($column, '<=', $value);
                } else {
                    $holiday->where($column, $value);
                }
            }
        }
        $holiday = $holiday->get();
        if ($holiday->isEmpty()) {
            return $this->successresponse(404, 'message', 'No found Calendar Event.');
        }

        return $this->successresponse(200, 'holiday', $holiday);
    }

    public function calendarstore(Request $request)
    {
        if ($this->rp['hrmodule']['companiesholidays']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [

            'event_type' => 'required|string|max:255',

            'event_title' => 'required_if:event_type,meeting,holiday,seminar',

            'event_date' => 'required',

            'description' => 'nullable',

            'emp_id' => 'required_if:event_type,increment,leave,meeting',

            'candidate_name' => 'required_if:event_type,joining,interview',

            'place_name' => 'required_if:event_type,tours',

        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        // Handle employee IDs
        $employeeIds = $request->emp_id;
        $employeeNames = $request->emp_name;
        if (is_array($employeeIds)) {
            $employeeIds = json_encode($employeeIds);
        }
        if (is_array($employeeNames)) {
            $employeeNames = json_encode($employeeNames);
        }
        $holiday = $this->companiesholidayModel::create([
            'event_type' => $request->event_type,
            'event_title' => $request->event_title,
            'event_date' => $request->event_date,
            'description' => $request->description,

            'employee_id' => $employeeIds,
            'employee_name' => $employeeNames,

            'candidate_name' => $request->candidate_name,
            'place_name' => $request->place_name,
            'created_by' => $this->userId
        ]);

        $add_message = ucfirst($request->event_type) . ' Details Added successfully.';

        if ($holiday) {
            return $this->successresponse(200, 'message', $add_message);
        } else {
            return $this->errorresponse(500, 'message', 'Failed to add ' . $add_message . '.');
        }
    }
    public function calendaredit($id)
    {
        if ($this->rp['hrmodule']['companiesholidays']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $holiday = $this->companiesholidayModel::find($id);

        if (!$holiday) {
            return $this->successresponse(404, 'message', 'No such calendar Event not found');
        }

        if ($this->rp['hrmodule']['companiesholidays']['alldata'] != 1) {
            if ($holiday->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        return $this->successresponse(200, 'data', $holiday);
    }

    public function calendarupdate(Request $request, $id)
    {
        if ($this->rp['hrmodule']['companiesholidays']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [

            'event_type' => 'required|string|max:255',

            'event_title' => 'required_if:event_type,meeting,holiday,seminar',

            'event_date' => 'required',

            'description' => 'nullable',

            'emp_id' => 'required_if:event_type,increment,leave,meeting',

            'candidate_name' => 'required_if:event_type,joining,interview',

            'place_name' => 'required_if:event_type,tours',

        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        $holiday = $this->companiesholidayModel::find($id);

        if (!$holiday) {
            return $this->successresponse(404, 'message', 'No such calendar Event not found');
        }

        if ($this->rp['hrmodule']['companiesholidays']['alldata'] != 1) {
            if ($holiday->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        $holiday->update([

            'event_type' => $request->event_type,

            'event_title' => $request->event_title,

            'event_date' => $request->event_date,

            'description' => $request->description,

            'employee_name' => $request->emp_name,

            'employee_id' => $request->emp_id,

            'candidate_name' => $request->candidate_name,

            'place_name' => $request->place_name,

            'updated_by' => $this->userId

        ]);

        $update_message = $request->event_type . ' Details updated successfully.';

        return $this->successresponse(200, 'message', $update_message);
    }

    public function calendardestroy($id)
    {

        if ($this->rp['hrmodule']['companiesholidays']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $holiday = $this->companiesholidayModel::find($id);

        if (!$holiday) {
            return response()->json(['status' => 'error', 'message' => 'calendar Event not found'], 404);
        }

        if ($this->rp['hrmodule']['companiesholidays']['alldata'] != 1) {
            if ($holiday->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        $delete = $holiday->update([
            "is_delete" => 1
        ]);

        if ($delete) {
            return $this->successresponse(200, 'message', 'calendar Event deleted successfully.');
        }

        return $this->successresponse(500, 'message', 'calendar Event deleted successfully.');
    }

    public function letterindex()
    {
        if ($this->rp['hrmodule']['letters']['view'] != 1) {
            return response()->json([
                'status' => 500,
                'message' => 'You are Unauthorized',
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ]);
        }

        $letters = $this->letterModel::where("is_delete", 0)->get();

        if ($letters->isEmpty()) {
            return DataTables::of($letters)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }

        return DataTables::of($letters)
            ->with([
                'status' => 200,
            ])
            ->make(true);
    }

    public function letterstore(Request $request)
    {

        if ($this->rp['hrmodule']['letters']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'letter_name'     => 'required|string|max:255',
            'header_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'header_align'    => 'required|string',
            'header_width'    => 'required|integer|min:1|max:100',
            'header_content'  => 'nullable|string',
            'body_content'    => 'nullable|string',
            'footer_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'footer_align'    => 'required|string',
            'footer_width'    => 'required|integer|min:1|max:100',
            'footer_content'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        // Create letter first
        $letter = $this->letterModel::create([
            'letter_name'     => $request->letter_name,
            'header_align'    => $request->header_align,
            'header_width'    => $request->header_width,
            'header_content'  => $request->header_content,
            'body_content'    => $request->body_content,
            'footer_align'    => $request->footer_align,
            'footer_width'    => $request->footer_width,
            'footer_content'  => $request->footer_content,
            'created_by' => $this->userId
        ]);

        // Make folder using the letter ID
        $uploadPath = public_path("uploads/{$this->companyId}/hr/letter/{$letter->id}");
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Upload header image
        if ($request->hasFile('header_image')) {
            $headerFile = $request->file('header_image');
            $headerFileName = 'header_image.' . $headerFile->getClientOriginalExtension();
            $headerFile->move($uploadPath, $headerFileName);

            $headerPath = "uploads/{$this->companyId}/hr/letter/{$letter->id}/{$headerFileName}";
            $letter->update(['header_image' => $headerPath]);
        }

        // Upload footer image
        if ($request->hasFile('footer_image')) {
            $footerFile = $request->file('footer_image');
            $footerFileName = 'footer_image.' . $footerFile->getClientOriginalExtension();
            $footerFile->move($uploadPath, $footerFileName);

            $footerPath = "uploads/{$this->companyId}/hr/letter/{$letter->id}/{$footerFileName}";
            $letter->update(['footer_image' => $footerPath]);
        }

        if ($letter) {
            return $this->successresponse(200, 'message', 'Letter succesfully added', 'letter_id', $letter->id);
        } else {
            return $this->successresponse(500, 'message', 'Letter not succesfully added!');
        }
    }

    public function letteredit($id)
    {
        if ($this->rp['hrmodule']['letters']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $letter = $this->letterModel::find($id);

        if (!$letter) {
            return response()->json(['status' => 'error', 'message' => 'letter not found'], 404);
        }

        if ($this->rp['hrmodule']['letters']['alldata'] != 1) {
            if ($letter->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        return $this->successresponse(200, 'data', $letter);
    }

    public function letterupdate(Request $request, $id)
    {
        if ($this->rp['hrmodule']['letters']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'letter_name'     => 'required|string|max:255',
            'header_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'header_align'    => 'required|string',
            'header_width'    => 'required|integer|min:1|max:100',
            'header_content'  => 'nullable|string',
            'body_content'    => 'nullable|string',
            'footer_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'footer_align'    => 'required|string',
            'footer_width'    => 'required|integer|min:1|max:100',
            'footer_content'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        // Find the letter
        $letter = $this->letterModel::find($id);

        if (!$letter) {
            return $this->successresponse(404, 'message', 'Employee not found');
        }

        if ($this->rp['hrmodule']['letters']['alldata'] != 1) {
            if ($letter->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        $validatedData = [
            'letter_name'     => $request->letter_name,
            'header_align'    => $request->header_align,
            'header_width'    => $request->header_width,
            'header_content'  => $request->header_content,
            'body_content'    => $request->body_content,
            'footer_align'    => $request->footer_align,
            'footer_width'    => $request->footer_width,
            'footer_content'  => $request->footer_content,
            'updated_by'  => $this->userId,
        ];

        $uploadPath = public_path("uploads/{$this->companyId}/hr/letter/{$id}");

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if ($request->hasFile('header_image')) {
            $headerFile = $request->file('header_image');
            $headerFileName = 'header_image.' . $headerFile->getClientOriginalExtension();
            $headerFile->move($uploadPath, $headerFileName);

            $headerPath = "uploads/{$this->companyId}/hr/letter//{$id}/{$headerFileName}";
            $validatedData['header_image'] = $headerPath;
        }

        if ($request->hasFile('footer_image')) {
            $footerFile = $request->file('footer_image');
            $footerFileName = 'footer_image.' . $footerFile->getClientOriginalExtension();
            $footerFile->move($uploadPath, $footerFileName);

            $footerPath = "uploads/{$this->companyId}/hr/letter//{$id}/{$footerFileName}";
            $validatedData['footer_image'] = $footerPath;
        }

        $letter = $letter->update($validatedData);

        if ($letter) {
            return $this->successresponse(200, 'message', 'Letter updated successfully.');
        }

        return $this->successresponse(404, 'message', 'Letter updated successfully.');
    }

    public function letterdestroy($id)
    {
        if ($this->rp['hrmodule']['letters']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $letter = $this->letterModel::find($id);

        if (!$letter) {
            return $this->successresponse(404, 'message', 'No such letter found!');
        }

        $delete = $letter->update([
            "is_delete" => 1
        ]);

        if ($delete) {
            return $this->successresponse(200, 'message', 'Letter deleted successfully.');
        }

        return $this->successresponse(404, 'message', 'Letter not deleted successfully.');
    }

    public function lettervariablesettingindex()
    {
        if ($this->rp['hrmodule']['letter_variable_setting']['view'] != 1) {
            return response()->json([
                'status' => 500,
                'message' => 'You are Unauthorized',
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ]);
        }

        $letter_variable_setting = $this->letter_variable_settingModel::where("is_deleted", 0)->get();

        if ($letter_variable_setting->isEmpty()) {
            return DataTables::of($letter_variable_setting)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }

        return DataTables::of($letter_variable_setting)
            ->with([
                'status' => 200,
            ])
            ->make(true);
    }
    public function lettervariablesettingstore(Request $request)
    {
        if ($this->rp['hrmodule']['letter_variable_setting']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'variable_name' => 'required|string|max:255|regex:/^[A-Za-z_][A-Za-z0-9_]*$/',
        ], [
            'variable_name.regex' => 'Variable name must start with a letter or underscore and contain only letters, numbers, and underscores.'
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        if ($this->letter_variable_settingModel::where('variable', "$" . $request->variable_name)
            ->where("is_deleted", 0)
            ->exists()
        ) {
            return $this->errorresponse(422, [
                'variable_name' => ['This variable already exists']
            ]);
        }

        $variables = $request->variables ?? [];

        // Flatten and remove empty strings safely
        $flattened = [];
        foreach ($variables as $component) {
            if (is_array($component)) {
                $flattened = array_merge($flattened, $component);
            } else {
                $flattened[] = $component;
            }
        }

        $nonEmptyVariables = array_filter($flattened, fn($v) => is_string($v) && trim($v) !== '');

        if (count($nonEmptyVariables) === 0) {
            return $this->errorresponse(422, [
                'variables' => ['At least one variable or text must be provided.']
            ]);
        }

        $variables_json = json_encode(array_values($nonEmptyVariables));

        $letter_variable_setting = $this->letter_variable_settingModel::create([
            'variable'        => "$" . $request->variable_name,
            'description'     => $request->description,
            'employee_fields' => $variables_json,
            'created_by'      => $this->userId
        ]);

        if ($letter_variable_setting) {
            return $this->successresponse(200, 'message', 'Letter variable successfully added');
        } else {
            return $this->successresponse(500, 'message', 'Letter variable not successfully added!');
        }
    }
    public function lettervariablesettingedit($id)
    {
        if ($this->rp['hrmodule']['letter_variable_setting']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $letter = $this->letter_variable_settingModel::find($id);

        if (!$letter) {
            return response()->json(['status' => 'error', 'message' => 'Letter variable setting not found'], 404);
        }

        if ($this->rp['hrmodule']['letter_variable_setting']['alldata'] != 1) {
            if ($letter->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        return $this->successresponse(200, 'data', $letter);
    }
    public function lettervariablesettingupdate(Request $request, $id)
    {
        if ($this->rp['hrmodule']['letter_variable_setting']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'variable_name' => 'required|string|max:255|regex:/^[A-Za-z_][A-Za-z0-9_]*$/',
            'variables'     => 'required|array|min:1',
        ], [
            'variable_name.regex' => 'Variable name must start with a letter or underscore and contain only letters, numbers, and underscores. No spaces or special characters allowed.',
            'variables.required' => 'At least one variable or text must be provided.'
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        // Check if variable already exists (excluding this id)
        if ($this->letter_variable_settingModel
            ::where('id', '!=', $id)
            ->where('variable', "$" . $request->variable_name)
            ->where("is_deleted", 0)
            ->exists()
        ) {
            return $this->errorresponse(422, [
                'variable_name' => ['This variable already exists']
            ]);
        }

        // Filter out empty dynamic components
        $nonEmptyVariables = array_filter($request->variables ?? [], function ($v) {
            return trim($v) !== '';
        });

        if (count($nonEmptyVariables) === 0) {
            return $this->errorresponse(422, [
                'variables' => ['At least one variable or text must be provided.']
            ]);
        }

        // Encode for database
        $variables_json = json_encode(array_values($nonEmptyVariables));

        // Update
        $updateData = [
            'variable'        => '$' . $request->variable_name,
            'employee_fields' => $variables_json,
            'description'        =>  $request->description,
            'updated_by'        =>  $request->user_id,
        ];

        $this->letter_variable_settingModel::where('id', $id)->update($updateData);

        return response()->json([
            'status'  => 200,
            'message' => 'Letter variable updated successfully'
        ]);
    }
    public function lettervariablesettingdestroy($id)
    {
        if ($this->rp['hrmodule']['letter_variable_setting']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $letter = $this->letter_variable_settingModel::find($id);

        if (!$letter) {
            return $this->successresponse(404, 'message', 'No such Letter variable  found!');
        }

        $delete = $letter->update([
            "is_deleted" => 1
        ]);

        if ($delete) {
            return $this->successresponse(200, 'message', 'Letter variable  deleted successfully.');
        }

        return $this->successresponse(404, 'message', 'Letter variable  not deleted successfully.');
    }
    public function generateletterindex()
    {
        if ($this->rp['hrmodule']['generate_letter']['view'] != 1) {
            return response()->json([
                'status' => 500,
                'message' => 'You are Unauthorized',
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ]);
        }
        $letters = $this->generate_letterModel
            ::join('employees', 'employees.id', '=', 'generate_letter.emp_id')
            ->join('letters', 'letters.id', '=', 'generate_letter.letter_id')
            ->select(
                'generate_letter.*',
                'letters.letter_name',
                DB::raw("CONCAT(employees.first_name, ' ', employees.middle_name, ' ', employees.surname) as employee_name"),
                DB::raw("DATE_FORMAT(generate_letter.created_at, '%d-%m-%Y') as created_at_formatted")
            )
            ->where('generate_letter.is_deleted', 0)
            ->get();

        if ($letters->isEmpty()) {
            return DataTables::of($letters)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }

        return DataTables::of($letters)
            ->with([
                'status' => 200,
            ])
            ->make(true);
    }
    public function generateletterstore(Request $request)
    {
        if ($this->rp['hrmodule']['generate_letter']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $letter_id =  $request->letter_id;
        $data_formate_date = $this->letterModel::where('id', $letter_id)->where('is_delete', 0)->first();


        // Create letter first
        $letter = $this->data_formateModel::create([
            'letter_name'     => $data_formate_date->letter_name,
            'header_align'    => $data_formate_date->header_align,
            'header_image'    => $data_formate_date->header_image,
            'header_width'    => $data_formate_date->header_width,
            'header_content'  => $data_formate_date->header_content,
            'body_content'    => $data_formate_date->body_content,
            'footer_align'    => $data_formate_date->footer_align,
            'footer_width'    => $data_formate_date->footer_width,
            'footer_image'    => $data_formate_date->footer_image,
            'footer_content'  => $data_formate_date->footer_content,
            'created_by' => $this->userId
        ]);
        $data_formate_id = $letter->id;
        $empID  = $request->emp_id;
        $letter_val = $request->data_variable;

        $generateletter = $this->generate_letterModel::create([
            'emp_id'     => $empID,
            'letter_id'    => $letter_id,
            'data_formate_id'    => $data_formate_id,
            'letter_value'  => $letter_val,
            'created_by' => $this->userId
        ]);

        if ($generateletter) {
            return $this->successresponse(200, 'message', 'Genrate Letter successfully');
        } else {
            return $this->successresponse(500, 'message', ' Not Genrate Letter  !');
        }
    }
    public function generateletteredit($id)
    {
        if ($this->rp['hrmodule']['generate_letter']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $letter = $this->generate_letterModel::find($id);

        if (!$letter) {
            return response()->json(['status' => 'error', 'message' => 'Genrate Letter Details not found'], 404);
        }

        if ($this->rp['hrmodule']['generate_letter']['alldata'] != 1) {
            if ($letter->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        return $this->successresponse(200, 'data', $letter);
    }
    public function generateletterupdate(Request $request, $id)
    {
        if ($this->rp['hrmodule']['generate_letter']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $letter = $this->generate_letterModel::find($id);
        if (!$letter) {
            return response()->json(['status' => 'error', 'message' => 'Genrate Letter Details not found'], 404);
        }

        if ($this->rp['hrmodule']['generate_letter']['alldata'] != 1) {
            if ($letter->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }
        $updated = $this->generate_letterModel
            ::where('id', $id)
            ->update([
                'emp_id' => $request->emp_id,
                'letter_id' => $request->letter_id,
                'data_formate_id' => $request->data_formate_id,
                'letter_value' => $request->data_variable,
                'updated_by' => $this->userId
            ]);

        if ($updated) {
            return $this->successresponse(200, 'message', 'Letter updated successfully.');
        }
        return $this->successresponse(500, 'message', 'Letter updated successfully.');
    }

    public function dataformate($id)
    {
        if ($this->rp['hrmodule']['generate_letter']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        // dd($id);
        $letter = $this->data_formateModel::find($id);

        if (!$letter) {
            return response()->json(['status' => 'error', 'message' => 'In Data Formate not found Letter Details'], 404);
        }

        // if ($this->rp['hrmodule']['generate_letter']['alldata'] != 1) {
        //     if ($letter->created_by != $this->userId) {
        //         return $this->successresponse(500, 'message', 'You are Unauthorized');
        //     }
        // }

        return $this->successresponse(200, 'data', $letter);
    }
    public function generateletterdestroy($id)
    {
        if ($this->rp['hrmodule']['generate_letter']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $letter = $this->generate_letterModel::find($id);
        if (!$letter) {
            return response()->json(['status' => 'error', 'message' => 'Genrate Letter Details not found'], 404);
        }

        if ($this->rp['hrmodule']['generate_letter']['alldata'] != 1) {
            if ($letter->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }
        $data_formate = $this->data_formateModel::find($letter->data_formate_id);
        if (!$data_formate) {
            return response()->json(['status' => 'error', 'message' => ' Letter Formate Details not found'], 404);
        }
        // dd($id,$letter->data_formate_id);
        $delete_leter = $this->generate_letterModel::where('id', $id)->update([
            "is_deleted" => 1
        ]);
        $delete_data_formate = $this->generate_letterModel::where('id', $letter->data_formate_id)->update([
            "is_deleted" => 1
        ]);
        if ($delete_leter) {
            return $this->successresponse(200, 'message', 'Letter  deleted successfully.');
        }

        return $this->successresponse(404, 'message', 'Letter  not deleted successfully.');
    }
}
