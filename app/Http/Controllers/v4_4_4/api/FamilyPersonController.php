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

class FamilyPersonController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $memberModel, $generate_letterModel, $data_formateModel;
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
       
    }
    public function familyIndex(Request $request)
{
    if ($this->rp['societymodule']['familymembers']['view'] != 1) {
        return $this->successresponse(500, 'message', 'You are Unauthorized');
    }

    $family = $this->familyModel::where('is_deleted', 0);

    if ($this->rp['societymodule']['familymembers']['alldata'] != 1) {
        $family = $family->where('created_by', $this->userId);
    }

    $family = $family->get();

    if ($family->isEmpty()) {
        return DataTables::of($family)
            ->with([
                'status' => 404,
                'message' => 'No Data Found',
            ])
            ->make(true);
    }

    $family->map(function ($item) {
        $ids = json_decode($item->familyPersonIds, true) ?? [];

        $item->family_persons = $this->familyPersonModel::whereIn('id', $ids)->get();

        return $item;
    });

    return DataTables::of($family)->with([
        'status' => 200,
    ])->make(true);
}

    public function familyStore(Request $request)
    {
        if ($this->rp['societymodule']['familymembers']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $validator = Validator::make($request->all(), [
            'famliypersons' => 'required|array',
        ]);
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors()->first());
        }
        $hasMainMember = false;
        $familyPersonIds = [];
        foreach ($request->famliypersons as $person) {
            if (isset($person['main_family_member']) && $person['main_family_member'] == 1) {
                $hasMainMember = $person;
                break;
            }
        }
        if ($hasMainMember) {
            $mainMenberFullname = ($hasMainMember['first_name'] ?? '') . ' ' .
                          ($hasMainMember['last_name'] ?? '') . ' ' .
                          ($hasMainMember['surname'] ?? '');
            $familystore = [
                'mainFamilyMemberFullName' => $mainMenberFullname,
                'familyPersonIds' => null,
                'mainFamilyPersonId' => null,
                'created_by' => $this->userId,
            ];
            $family = $this->familyModel::create($familystore);
        } else {
            return $this->successresponse(422, 'message', 'Main family member is required');
        }
        if(!empty($request->famliypersons)){
            foreach($request->famliypersons as $familyPerson){
                $familyPersonStoredata = [
                    'family_id' => $family->id,
                    'first_name' => $familyPerson['first_name'] ?? '',
                    'last_name' => $familyPerson['last_name'] ?? '',
                    'surname' => $familyPerson['surname'] ?? '',
                    'full_name' => ($familyPerson['first_name'] ?? '') . ' ' . ($familyPerson['last_name'] ?? '') . ' ' . ($familyPerson['surname'] ?? ''),
                    'dob' => $familyPerson['dob'] ?? '',
                    'age' => $familyPerson['age'] ?? '',
                    'email' => $familyPerson['email'] ?? '',
                    'mobile' => $familyPerson['mobile'] ?? '',
                    'address_house_no_building_name' => $familyPerson['address_house_no_building_name'] ?? '',
                    'address_landmark' => $familyPerson['address_landmark'] ?? '',
                    'address_area' => $familyPerson['address_area'] ?? '',
                    'address_country_id' => $familyPerson['address_country_id'] ?? '',
                    'address_state_id' => $familyPerson['address_state_id'] ?? '',
                    'address_city_id' => $familyPerson['address_city_id'] ?? '',
                    'address_pincode' => $familyPerson['address_pincode'] ?? '',
                    'marital_status' => $familyPerson['marital_status'] ?? '',
                    'gender' => $familyPerson['gender'] ?? '',
                    'job_role' => $familyPerson['job_role'] ?? '',
                    'company_name' => $familyPerson['company_name'] ?? '',
                    'company_house_no_building_name' => $familyPerson['company_house_no_building_name'] ?? '',
                    'company_landmark' => $familyPerson['company_landmark'] ?? '',
                    'company_area' => $familyPerson['company_area'] ?? '',
                    'company_country_id' => $familyPerson['company_country_id'] ?? '',
                    'company_state_id' => $familyPerson['company_state_id'] ?? '',
                    'company_city_id' => $familyPerson['company_city_id'] ?? '',
                    'company_pincode' => $familyPerson['company_pincode'] ?? '',
                    'business_intro' => $familyPerson['business_intro'] ?? '',
                    'services' => $familyPerson['services'] ?? '',
                    'seo_keywords' => $familyPerson['seo_keywords'] ?? '',
                    'business_category' => $familyPerson['business_category'] ?? '',
                    'business_subcategory' => $familyPerson['business_subcategory'] ?? '',
                    'main_family_member' => $familyPerson['main_family_member'] ?? 0,
                    'relationship_id' => $familyPerson['relationship_id'] ?? '',
                    'shakh' => $familyPerson['shakh'] ?? '',
                    'created_by' => $this->userId,
                ];
                $familyPersonstore = $this->familyPersonModel::create($familyPersonStoredata);
                
                $passwordtoken = Str::random(40);
                if($familyPersonstore){
                    $familyPersonIds[] = (string) $familyPersonstore->id;
                    // Update main family table add main family person id and if this main family person then add member record
                    if ($familyPersonstore->main_family_member == 1){
                        $this->familyModel::where('id', $familyPersonstore->family_id)->update(['mainFamilyPersonId' => $familyPersonstore->id]);
                        $member = [
                            'family_id' => $familyPersonstore->family_id,
                            'family_person_id' => $familyPersonstore->id,
                            'lifetime_member_no' => $familyPerson['lifetime_member_no'] ?? '',
                            'receipt_number' => $familyPerson['receipt_number'] ?? '',
                            'created_by' => $this->userId,
                        ];
                    $memberstore = $this->memberModel::create($member);
                    $userdata = [
                            'firstname' => $familyPersonstore->first_name,
                            'lastname' => $familyPersonstore->last_name,
                            'role' => 3,
                            'email' => $familyPersonstore->email,
                            'password' => Hash::make($familyPersonstore->email),
                            'contact_no' => $familyPersonstore->mobile,
                            'country_id' => $familyPersonstore->address_country_id,
                            'state_id' => $familyPersonstore->address_state_id,
                            'city_id' => $familyPersonstore->address_city_id,
                            'pincode' => $familyPersonstore->address_pincode,
                            'role_permissions' => 2,
                            'pass_token' => $passwordtoken,
                            'company_id' => $this->companyId,
                            'family_person_id' => $familyPersonstore->id,
                            'created_by' => $this->userId
                        ];
                    $users = User::create($userdata);
                    }
                    else{
                    $userdata = [
                        'firstname' => $familyPersonstore->first_name,
                            'lastname' => $familyPersonstore->last_name,
                            'role' => 3,
                            'email' => $familyPersonstore->email,
                            'password' => Hash::make($familyPersonstore->email),
                            'contact_no' => $familyPersonstore->mobile,
                            'country_id' => $familyPersonstore->address_country_id,
                            'state_id' => $familyPersonstore->address_state_id,
                            'city_id' => $familyPersonstore->address_city_id,
                            'pincode' => $familyPersonstore->address_pincode,
                            'role_permissions' => 1,
                            'pass_token' => $passwordtoken,
                            'company_id' => $this->companyId,
                            'family_person_id' => $familyPersonstore->id,
                            'created_by' => $this->userId
                        ];
                        $users = User::create($userdata);
                    }
                }

            }
            $this->familyModel::where('id', $family['id'])->update(['familyPersonIds' => json_encode($familyPersonIds)]);

        }
        else{
            return $this->successresponse(422, 'message', 'Family persons are required');
        }
        return $this->successresponse(200, 'message', 'Family and family persons created successfully');
        
    }
}


