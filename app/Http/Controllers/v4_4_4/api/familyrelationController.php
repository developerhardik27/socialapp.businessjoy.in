<?php

namespace App\Http\Controllers\v4_4_4\api;

use App\Http\Controllers\Controller;
use App\Models\v4_4_4\FamilyRelation;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class familyrelationController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $companiesholidayModel, $letterModel, $letter_variable_settingModel, $generate_letterModel, $data_formateModel;
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
       
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($this->rp['societymodule']['familyrelation']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        // dd($request->all());
        $familyrelations = $this->familyrelationModel::where('is_deleted',0);
        
        if ($this->rp['societymodule']['familyrelation']['alldata'] != 1) {
            $familyrelations = $familyrelations->where('created_by', $this->userId);
        }
        $familyrelations = $familyrelations->get();
        if ($familyrelations->isEmpty()) {
            return DataTables::of($familyrelations)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }
        return DataTables::of($familyrelations)->with([
            'status' => 200,
        ])->make(true);
    }
    public function store(Request $request)
    {
        if ($this->rp['societymodule']['familyrelation']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        // dd($request->all());
        
        $familyrelation = $this->familyrelationModel::create(
            [
                'relation' => $request->relation,
                'created_by' => $this->userId,
            ]
        );
        // dd($familyrelation);
         if ($familyrelation) {
            return $this->successresponse(200, 'message', 'Family relation succesfully added');
        } else {
            return $this->successresponse(500, 'message', 'Family relation not succesfully added !');
        }
    }
    public function show($id)
    {
        if ($this->rp['societymodule']['familyrelation']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $familyrelation = $this->familyrelationModel::find($id);
        if(!$familyrelation){
            return $this->errorresponse(404, 'message', 'No such family relation not found!');
        }
        return $this->successresponse(200, 'data', $familyrelation);
    }
    public function edit($id)
    {
        if ($this->rp['societymodule']['familyrelation']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $familyrelation = $this->familyrelationModel::find($id);
        if(!$familyrelation){
            return $this->errorresponse(404, 'message', 'No such family relation not found!');
        }
        return $this->successresponse(200, 'data', $familyrelation);
    }
    public function update(Request $request,$id)
    {
        if ($this->rp['societymodule']['familyrelation']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $familyrelation = $this->familyrelationModel::find($id);
        if(!$familyrelation){
            return $this->errorresponse(404, 'message', 'No such family relation not found!');
        }
        $familyrelation->update([
            'relation' => $request->relation,
            'updated_by' => $this->userId,
        ]);
        if(!$familyrelation){
            return $this->errorresponse(500, 'message', 'Family relation not updated!');
        }
        return $this->successresponse(200, 'message', 'Family relation updated successfully.');
    }
    public function destory($id)
    {
        if ($this->rp['societymodule']['familyrelation']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $familyrelation = $this->familyrelationModel::find($id);
        if(!$familyrelation){
            return $this->errorresponse(404, 'message', 'No such family relation not found!');
        }
        $familyrelation->update([
            'is_deleted' => 1,
        ]);
        return $this->successresponse(200, 'message', 'Family relation deleted successfully.');
    }
}
