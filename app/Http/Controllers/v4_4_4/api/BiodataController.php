<?php

namespace App\Http\Controllers\v4_4_4\api;
use App\Models\v4_4_4\Family;
use App\Models\v4_4_4\FamilyPerson;
use App\Models\v4_4_4\Member;
use App\Models\v4_4_4\Biodata;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class BiodataController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $businesscategoryModel, $businesssubcategoryModel, $biodataModel;
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
        $this->familyrelationModel = $this->getmodel('FamilyRelation');
        $this->familyModel = $this->getmodel('Family');
        $this->familyPersonModel = $this->getmodel('FamilyPerson');
        $this->memberModel = $this->getmodel('Member');
        $this->businesscategoryModel = $this->getmodel('BusinessCategory');
        $this->businesssubcategoryModel = $this->getmodel('BusinessSubCategory');
        $this->biodataModel = $this->getmodel('Biodata');
    }
    public function index(Request $request)
    {
        if ($this->rp['societymodule']['familymembers']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $biodata = $this->biodataModel::leftJoin('family_person', 'biodata.familyPersonId', '=', 'family_person.id')
            ->where('biodata.is_deleted', 0)
            ->select('biodata.*', 'family_person.first_name as familyPersonName');

        if ($this->rp['societymodule']['familymembers']['alldata'] != 1) {
            $biodata = $biodata->where('biodata.created_by', $this->userId);
        }

        $biodata = $biodata->get();

        if ($biodata->isEmpty()) {
            return DataTables::of($biodata)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }
        return DataTables::of($biodata)->with([
            'status' => 200,
        ])->make(true);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdfPath' => 'required|mimes:pdf',
            'familyPersonId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->successresponse(500, 'message', $validator->errors()->first());
        }
        if(!$request->familyPersonId){
            return $this->successresponse(500, 'message', 'Family Person ID is required');
        }
        if(!$this->familyPersonModel::find($request->familyPersonId)){
            return $this->successresponse(500, 'message', 'Family Person not found');
        }
        $pdfPath = null;
        $basePath = "uploads/{$this->companyId}/biodata/{$request->familyPersonId}";
        if ($request->hasFile('pdfPath')) {
            $file = $request->file('pdfPath');

            $name = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path("$basePath"), $name);

            $pdfPath = "$basePath/$name";
        }
        $biodata = $this->biodataModel::create([
            'description' => $request->description,
            'pdfPath' => $pdfPath,
            'familyPersonId' => $request->familyPersonId,
            'created_by' => $this->userId,
        ]);

        return $this->successresponse(200, 'message', 'Biodata created successfully');
    }
    public function show($id)
    {
        $biodata = $this->biodataModel::leftJoin('family_person', 'biodata.familyPersonId', '=', 'family_person.id')
            ->where('biodata.id', $id)
            ->select('biodata.*', 'family_person.first_name as familyPersonName')
            ->first();
        if (!$biodata) {
            return $this->successresponse(500, 'message', 'Biodata not found');
        }
        return $this->successresponse(200, 'biodata', $biodata);
    }
    public function edit($id)
    {
        $biodata = $this->biodataModel::find($id);
        if (!$biodata) {
            return $this->successresponse(500, 'message', 'Biodata not found');
        }
        return $this->successresponse(200, 'biodata', $biodata);
    }
    public function update(Request $request, $id)
    {
        $biodata = $this->biodataModel::find($id);

        if (!$biodata) {
            return $this->successresponse(500, 'message', 'Biodata not found');
        }

        $pdfPath = $biodata->pdfPath; // keep old file by default

        $basePath = "uploads/{$this->companyId}/biodata/{$request->familyPersonId}";
        $fullPath = public_path($basePath);

        // If new file uploaded
        if ($request->hasFile('pdfPath')) {

            $file = $request->file('pdfPath');

            // Create folder if not exists
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0777, true);
            }
            // 🔥 Delete old file (important)
            if ($biodata->pdfPath && file_exists(public_path($biodata->pdfPath))) {
                unlink(public_path($biodata->pdfPath));
            }

            // Upload new file
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move($fullPath, $name);

            $pdfPath = "$basePath/$name";
        }

        // Update data
        $biodata->update([
            'description' => $request->description,
            'pdfPath' => $pdfPath,
            'familyPersonId' => $request->familyPersonId,
            'updated_by' => $this->userId,
        ]);

        return $this->successresponse(200, 'message', 'Biodata updated successfully');
    }
    public function destroy($id)
    {
        $biodata = $this->biodataModel::find($id);
        if (!$biodata) {
            return $this->successresponse(500, 'message', 'Biodata not found');
        }
        $biodata->update([
            'is_deleted' => 1,
            'updated_by' => $this->userId,
        ]);
        return $this->successresponse(200, 'message', 'Biodata deleted successfully');
    }
    // this for create biodata
    public function getFamilyPersonId(Request $request)
    {
        $familyPerson = $this->familyPersonModel::rightJoin('biodata', 'family_person.id', '!=', 'biodata.familyPersonId')->where('biodata.is_deleted', 0)->where('family_person.is_deleted', 0)->get();
        if ($familyPerson->isEmpty()) {
            return $this->successresponse(500, 'message', 'Family person not found');
        }
        return $this->successresponse(200, 'familyPerson', $familyPerson);
    }

    // this for edit biodata
    public function getFamilyPersonIdForEdit(Request $request)
    {
        $familyPerson = $this->familyPersonModel::leftJoin('biodata', 'family_person.id', '=', 'biodata.familyPersonId')->where('biodata.is_deleted', 0)->where('family_person.is_deleted', 0)->get();
        if ($familyPerson->isEmpty()) {
            return $this->successresponse(500, 'message', 'Family person not found');
        }
        return $this->successresponse(200, 'familyPerson', $familyPerson);
    }
}
