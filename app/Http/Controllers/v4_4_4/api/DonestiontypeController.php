<?php

namespace App\Http\Controllers\v4_4_4\api;
use App\Models\v4_4_4\Family;
use App\Models\v4_4_4\FamilyPerson;
use App\Models\v4_4_4\Member;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DonestiontypeController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $businesscategoryModel, $businesssubcategoryModel, $donestiontypeModel;
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
        $this->familyPersonModel= $this->getmodel('FamilyPerson');
        $this->memberModel = $this->getmodel('Member');
        $this->businesscategoryModel = $this->getmodel('BusinessCategory');
        $this->businesssubcategoryModel = $this->getmodel('BusinessSubCategory');
       $this->donestiontypeModel = $this->getmodel('Donestiontype');
    }
    public function index(Request $request)
    {
        if ($this->rp['societymodule']['donationtype']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $donestiontypes = $this->donestiontypeModel::where('is_deleted', 0)->get();
        if ($donestiontypes->isEmpty()) {
            return DataTables::of($donestiontypes)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }
        return DataTables::of($donestiontypes)->with([
            'status' => 200,
        ])->make(true);
    }
    public function store(Request $request)
    {
        if ($this->rp['societymodule']['donationtype']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        $donestiontype = $this->donestiontypeModel::create([
            'name' => $validated['name'],
            'created_by' => $this->userId,
        ]);
        if(!$donestiontype){
            return $this->successresponse(500, 'message', 'Donation type not created');
        }
        
        return $this->successresponse(200, 'message', 'Donation type created successfully');
    }
    public function show(Request $request, $id)
    {
        if ($this->rp['societymodule']['donationtype']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $donestiontype = $this->donestiontypeModel::find($id);
        if(!$donestiontype){
            return $this->successresponse(404, 'message', 'Donation type not found');
        }
        return $this->successresponse(200, 'donestiontype', $donestiontype);
    }
    public function edit(Request $request, $id)
    {
        if ($this->rp['societymodule']['donationtype']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $donestiontype = $this->donestiontypeModel::find($id);
        if(!$donestiontype){
            return $this->successresponse(404, 'message', 'Donation type not found');
        }
        return $this->successresponse(200, 'donestiontype', $donestiontype);
    }
    public function update(Request $request, $id)
    {
        if ($this->rp['societymodule']['donationtype']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
         $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        $donestiontype = $this->donestiontypeModel::where('id', $id)->update([
            'name' => $validated['name'],
            'updated_by' => $this->userId,
        ]);
        if(!$donestiontype){
            return $this->successresponse(500, 'message', 'Donation type not updated');
        }
        
        return $this->successresponse(200, 'message', 'Donation type updated successfully');
    }
    public function destory(Request $request, $id)
    {
        if ($this->rp['societymodule']['donationtype']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $donestiontype = $this->donestiontypeModel::find($id);
        if(!$donestiontype){
            return $this->successresponse(404, 'message', 'Donation type not found');
        }
        $donestiontype->update([
            'is_deleted' => 1,
        ]);
        return $this->successresponse(200, 'message', 'Donation type deleted successfully');
    }
}
