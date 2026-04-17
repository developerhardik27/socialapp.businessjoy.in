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
 
class MemberController extends commonController
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
        if ($this->rp['societymodule']['member']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $members = $this->memberModel::leftJoin('family_person', 'members.family_person_id', '=', 'family_person.id')
        ->leftJoin('business_sub_category as bsc', 'bsc.id', '=', 'family_person.business_subcategory')
        ->leftJoin('business_category as bc', 'bc.id', '=', 'family_person.business_category')    
        ->leftJoin($this->masterdbname.'.country as company_country', 'company_country.id', '=', 'family_person.company_country_id')
            ->leftJoin($this->masterdbname.'.country as address_country', 'address_country.id', '=', 'family_person.address_country_id')

            ->leftJoin($this->masterdbname.'.state as company_state', 'company_state.id', '=', 'family_person.company_state_id')
            ->leftJoin($this->masterdbname.'.state as address_state', 'address_state.id', '=', 'family_person.address_state_id')

            ->leftJoin($this->masterdbname.'.city as company_city', 'company_city.id', '=', 'family_person.company_city_id')
            ->leftJoin($this->masterdbname.'.city as address_city', 'address_city.id', '=', 'family_person.address_city_id')
            ->where('members.is_deleted', 0)
            ->where('family_person.is_deleted', 0)
            
            ->select('members.*', 'family_person.*','bsc.name as business_subcategory_name',
            'bc.name as business_category_name',

            'company_country.country_name as company_country_name',
            'address_country.country_name as address_country_name',

            'company_state.state_name as company_state_name',
            'address_state.state_name as address_state_name',

            'company_city.city_name as company_city_name',
            'address_city.city_name as address_city_name');
             if ($this->rp['societymodule']['member']['alldata'] != 1) {
                $members = $members->where('members.created_by', $this->userId);
            }
            $members = $members->get();
            if ($members->isEmpty()) {
            return DataTables::of($members)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }
        return DataTables::of($members)
            ->with([
                'status' => 200,
            ])
            ->make(true);
    }
    public function show(Request $request,$id)
    {
        if ($this->rp['societymodule']['member']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $members = $this->memberModel::leftJoin('family_person', 'members.family_person_id', '=', 'family_person.id')
        ->leftJoin('business_sub_category as bsc', 'bsc.id', '=', 'family_person.business_subcategory')
        ->leftJoin('business_category as bc', 'bc.id', '=', 'family_person.business_category')    
        ->leftJoin($this->masterdbname.'.country as company_country', 'company_country.id', '=', 'family_person.company_country_id')
            ->leftJoin($this->masterdbname.'.country as address_country', 'address_country.id', '=', 'family_person.address_country_id')

            ->leftJoin($this->masterdbname.'.state as company_state', 'company_state.id', '=', 'family_person.company_state_id')
            ->leftJoin($this->masterdbname.'.state as address_state', 'address_state.id', '=', 'family_person.address_state_id')

            ->leftJoin($this->masterdbname.'.city as company_city', 'company_city.id', '=', 'family_person.company_city_id')
            ->leftJoin($this->masterdbname.'.city as address_city', 'address_city.id', '=', 'family_person.address_city_id')
            ->where('members.is_deleted', 0)
            ->where('family_person.is_deleted', 0)
            
            ->select('members.*', 'family_person.*','bsc.name as business_subcategory_name',
            'bc.name as business_category_name',

            'company_country.country_name as company_country_name',
            'address_country.country_name as address_country_name',

            'company_state.state_name as company_state_name',
            'address_state.state_name as address_state_name',

            'company_city.city_name as company_city_name',
            'address_city.city_name as address_city_name')->where('members.id', $id)->get();
            if ($members->isEmpty()) {
                return $this->successresponse(404, 'message', 'No Data Found');
            }
        return $this->successresponse(200, 'data', $members);
    }
    public function edit(Request $request,$id)
    {
        if ($this->rp['societymodule']['member']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $members = $this->memberModel::leftJoin('family_person', 'members.family_person_id', '=', 'family_person.id')
        ->where('members.is_deleted', 0)
            ->where('family_person.is_deleted', 0)
            
            ->select('members.*', 'family_person.*')
            ->where('members.id', $id)->first();
            if (!$members) {
                return $this->successresponse(404, 'message', 'No Data Found');
            }
        return $this->successresponse(200, 'data', $members);
    }
    public function update(Request $request,$id)
    {
        if ($this->rp['societymodule']['member']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $members = $this->memberModel::where('id', $id)->first();

        if (!$members) {
            return $this->successresponse(404, 'message', 'No Data Found');
        }

        if (!empty($request->family_person_id) && (int)$request->main_family_member === 1) {

            $personData = [
                'family_id'                        => $members->family_id,
                'first_name'                       => $request->first_name ?? null,
                'last_name'                        => $request->last_name ?? null,
                'surname'                          => $request->surname ?? null,
                'full_name'                        => trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? '') . ' ' . ($request->surname ?? '')),
                'dob'                              => !empty($request->dob) ? $request->dob : null,
                'age'                              => !empty($request->age) ? $request->age : null,
                'email'                            => $request->email ?? null,
                'mobile'                           => $request->mobile ?? null,
                'address_house_no_building_name'   => $request->address_house_no_building_name ?? null,
                'address_landmark'                 => $request->address_landmark ?? null,
                'address_area'                     => $request->address_area ?? null,
                'address_country_id'               => !empty($request->address_country_id) ? $request->address_country_id : null,
                'address_state_id'                 => !empty($request->address_state_id) ? $request->address_state_id : null,
                'address_city_id'                  => !empty($request->address_city_id) ? $request->address_city_id : null,
                'address_pincode'                  => !empty($request->address_pincode) ? $request->address_pincode : null,
                'marital_status'                   => $request->marital_status ?? null,
                'gender'                           => $request->gender ?? null,
                'job_role'                         => $request->job_role ?? null,
                'company_name'                     => $request->company_name ?? null,
                'company_house_no_building_name'   => $request->company_house_no_building_name ?? null,
                'company_landmark'                 => $request->company_landmark ?? null,
                'company_area'                     => $request->company_area ?? null,
                'company_country_id'               => !empty($request->company_country_id) ? $request->company_country_id : null,
                'company_state_id'                 => !empty($request->company_state_id) ? $request->company_state_id : null,
                'company_city_id'                  => !empty($request->company_city_id) ? $request->company_city_id : null,
                'company_pincode'                  => !empty($request->company_pincode) ? $request->company_pincode : null,
                'business_intro'                   => $request->business_intro ?? null,
                'services'                         => $request->services ?? null,
                'seo_keywords'                     => $request->seo_keywords ?? null,
                'business_category'                => !empty($request->business_category) ? $request->business_category : null,
                'business_subcategory'             => !empty($request->business_subcategory) ? $request->business_subcategory : null,
                'relationship_id'                  => !empty($request->relationship_id) ? $request->relationship_id : null,
                'shakh'                            => $request->shakh ?? null,
                'updated_by'                       => $this->userId,
            ];

            $familyperson = $this->familyPersonModel::find($request->family_person_id);

            if ($familyperson) {

                $familyperson->update($personData);

                // Update user
                User::where('family_person_id', $request->family_person_id)->update([
                    'firstname'        => $personData['first_name'],
                    'lastname'         => $personData['last_name'],
                    'email'            => $personData['email'],
                    'contact_no'       => $personData['mobile'],
                    'country_id'       => $personData['address_country_id'],
                    'state_id'         => $personData['address_state_id'],
                    'city_id'          => $personData['address_city_id'],
                    'pincode'          => $personData['address_pincode'],
                    'updated_by'       => $this->userId,
                ]);
                // dd($familyperson);
                // Update family
                $this->familyModel
                    ::where('id', $familyperson['family_id'])
                    ->where('mainFamilyPersonId', $familyperson['id'])
                    ->update([
                        'mainFamilyMemberFullName' => $personData['full_name'],
                        'updated_by' => $this->userId,
                    ]);
                
               $memberupdate = $this->memberModel::where('id', $id)->update([
                    'lifetime_member_no' => $request->lifetime_member_no,
                    'receipt_number' => $request->receipt_number,
                    'updated_by' => $this->userId,
                ]);
            }
        }
        if(!$memberupdate){
            return $this->successresponse(400, 'message', 'Member not updated');
        }
        return $this->successresponse(200, 'message', 'Member updated successfully');
    }
}
