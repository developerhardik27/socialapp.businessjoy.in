<?php

namespace App\Http\Controllers\v4_4_4\api;
use App\Models\v4_4_4\Family;
use App\Models\v4_4_4\FamilyPerson;
use App\Models\v4_4_4\Member;
use App\Models\User;
use App\Models\userrolepermission;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DonorController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $businesscategoryModel, $businesssubcategoryModel, $data_formateModel,$user_permissionModel,$donorModel;
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
        $this->user_permissionModel = $this->getmodel('user_permission');
        $this->donorModel = $this->getmodel('Donor');
        $this->donestiontypeModel = $this->getmodel('Donestiontype');
       
    }
    public function index(Request $request)
    {
        if ($this->rp['societymodule']['donation']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $donors = $this->donorModel::leftjoin('donestion_type', 'donor.donation_type_id', '=', 'donestion_type.id')->leftjoin('family_person', 'donor.familyPersonId', '=', 'family_person.id')->where('donor.is_deleted', 0);
        
        if ($this->rp['societymodule']['donation']['alldata'] != 1) {
            $donors = $donors->where('donor.created_by', $this->userId);
        }
        $donors = $donors->select('donor.*', 'donestion_type.name as donation_type_name', 'family_person.full_name as family_person_name');
        $donors = $donors->get();
        
         if ($donors->isEmpty()) {
            return DataTables::of($donors)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }
        return DataTables::of($donors)
            ->with([
                'status' => 200,
            ])
            ->make(true);

    }
    public function store(Request $request)
    {
        if ($this->rp['societymodule']['donation']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        // dd($request->all());
        $validated = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'mobile' => 'required',
            'donate_date' => 'required|date',
            'donation_type_id' => 'required|integer',
        ]);
        
        if ($validated->fails()) {
            return $this->successresponse(422, 'message', $validated->errors()->first());
        }
        
        $donor = $this->donorModel::create([
            'amount' => $request->amount,
            'familyPersonId' => $request->familyPersonId,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'surname' => $request->surname,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'donate_date' => $request->donate_date,
            'donation_type_id' => $request->donation_type_id,
            'created_by' => $this->userId,
        ]);
        if(!$donor){
            return $this->successresponse(500, 'message', 'Donor not created');
        }
        return $this->successresponse(200, 'message', 'Donor created successfully');
    }
    
    public function show($id)
    {
        if ($this->rp['societymodule']['donation']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $donor = $this->donorModel::leftjoin('donestion_type', 'donor.donation_type_id', '=', 'donestion_type.id')->leftjoin('family_person', 'donor.familyPersonId', '=', 'family_person.id')->select('donor.*', 'donestion_type.name as donation_type_name', 'family_person.full_name as family_person_name')->find($id);
        
        if(!$donor){
            return $this->errorresponse(404, 'message', 'No such donor found!');
        }
        
        return $this->successresponse(200, 'data', $donor);
    }
    
    public function edit($id)
    {
        if ($this->rp['societymodule']['donation']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $donor = $this->donorModel::leftjoin('donestion_type', 'donor.donation_type_id', '=', 'donestion_type.id')->leftjoin('family_person', 'donor.familyPersonId', '=', 'family_person.id')->where('donor.id', $id)->select('donor.*', 'donestion_type.name as donation_type_name', 'family_person.full_name as family_person_name')->first();
        
        if(!$donor){
            return $this->errorresponse(404, 'message', 'No such donor found!');
        }
        
        return $this->successresponse(200, 'data', $donor);
    }
    
    public function update(Request $request, $id)
    {
        if ($this->rp['societymodule']['donation']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $validated = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'mobile' => 'required',
            'donate_date' => 'required|date',
            'donation_type_id' => 'required|integer',
        ]);
        
        if ($validated->fails()) {
            return $this->successresponse(422, 'message', $validated->errors()->first());
        }
        
        $donor = $this->donorModel::find($id);
        
        if(!$donor){
            return $this->errorresponse(404, 'message', 'No such donor found!');
        }
        
        $donor->update([
            'amount' => $request->amount,
            'familyPersonId' => $request->familyPersonId,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'surname' => $request->surname,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'donate_date' => $request->donate_date,
            'donation_type_id' => $request->donation_type_id,
            'updated_by' => $this->userId,
        ]);
        
        if(!$donor){
            return $this->errorresponse(500, 'message', 'Donor not updated!');
        }
        
        return $this->successresponse(200, 'message', 'Donor updated successfully.');
    }
    
    public function destory($id)
    {
        if ($this->rp['societymodule']['donation']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $donor = $this->donorModel::find($id);
        
        if(!$donor){
            return $this->errorresponse(404, 'message', 'No such donor found!');
        }
        
        $donor->update([
            'is_deleted' => 1,
            'updated_by' => $this->userId,
        ]);
        
        return $this->successresponse(200, 'message', 'Donor deleted successfully.');
    }
    public function donationTypes(Request $request)
    {
        $donationTypes = $this->donestiontypeModel::where('is_deleted', 0)->get();
        return $this->successresponse(200, 'data', $donationTypes);
    }
    public function persons(Request $request)
    {
        $persons = $this->familyPersonModel::where('is_deleted', 0)->get();
        return $this->successresponse(200, 'data', $persons);
    }
}
