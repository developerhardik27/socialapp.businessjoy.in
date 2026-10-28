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

class FamilyPersonController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $businesscategoryModel, $businesssubcategoryModel, $data_formateModel,$user_permissionModel,$karobarimemberModel;
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
         $this->karobarimemberModel = $this->getmodel('karobarimember');
       
    }
    // family index with all family persons and user details
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
    // family store with all family persons and user details
    // public function familyStore(Request $request)
    // {
    //     if ($this->rp['societymodule']['familymembers']['add'] != 1) {
    //         return $this->successresponse(500, 'message', 'You are Unauthorized');
    //     }
    //     $validator = Validator::make($request->all(), [
    //         'famliypersons' => 'required|array',
    //         'famliypersons.*.email' => 'required|email',
    //     ]);
    //     if ($validator->fails()) {
    //         return $this->successresponse(422, 'message', $validator->errors()->first());
    //     }
    //    $mainMembers = array_filter($request->famliypersons, function ($person) {
    //         return isset($person['main_family_member']) && $person['main_family_member'] == 1;
    //     });
    //     foreach ($request->famliypersons as $index => $person) {
    //          if(isset($person['email']) && !empty($person['email'])) {
    //             $checkEmail = User::where('email', $person['email'])
    //                 ->where('is_deleted', 0)
    //                 ->first();
    //             if ($checkEmail) {
    //                 return $this->successresponse(422, 'message', "Email already exists for family member at index {$index}");
    //             }
    //         }
    //         if (isset($person['main_family_member']) && $person['main_family_member'] == 1) {
    //             if ($person['lifetime_member_no'] !== null && $person['lifetime_member_no'] !== ''){
    //                 $checkLifetimeMemberNo = $this->memberModel::where('lifetime_member_no', $person['lifetime_member_no'])->where('is_deleted', 0)->first();
    //                 if($checkLifetimeMemberNo){
    //                     return $this->successresponse(422, 'message', "Lifetime member number already exists for main family member at index {$index}");
    //                 }
    //             }

    //             if ($person['receipt_number'] !== null && $person['receipt_number'] !== '') {
    //                 $checkReceiptNumber = $this->memberModel::where('receipt_number', $person['receipt_number'])->where('is_deleted', 0)->first();
    //                 if($checkReceiptNumber){
    //                     return $this->successresponse(422, 'message', "Receipt number already exists for main family member at index {$index}");
    //                 }
    //             }
    //         }
    //     }
    //     if (count($mainMembers) == 0) {
    //         return $this->successresponse(422, 'message', 'Main family member is required');
    //     }

    //     if (count($mainMembers) > 1) {
    //         return $this->successresponse(422, 'message', 'Only one main family member is allowed');
    //     }

    //     // Get the single main member
    //     $hasMainMember = array_values($mainMembers)[0];
    //     if ($hasMainMember) {
    //         $mainMenberFullname = ($hasMainMember['first_name'] ?? '') . ' ' .
    //                       ($hasMainMember['last_name'] ?? '') . ' ' .
    //                       ($hasMainMember['surname'] ?? '');
    //         $familystore = [
    //             'mainFamilyMemberFullName' => $mainMenberFullname,
    //             'familyPersonIds' => null,
    //             'mainFamilyPersonId' => null,
    //             'created_by' => $this->userId,
    //         ];
    //         $family = $this->familyModel::create($familystore);
    //     } else {
    //         return $this->successresponse(422, 'message', 'Main family member is required');
    //     }
    //     if(!empty($request->famliypersons)){
    //         foreach($request->famliypersons as $familyPerson){
    //             $familyPersonStoredata = [
    //                 'family_id' => $family->id,
    //                 'first_name' => $familyPerson['first_name'] ?? '',
    //                 'last_name' => $familyPerson['last_name'] ?? '',
    //                 'surname' => $familyPerson['surname'] ?? '',
    //                 'full_name' => ($familyPerson['first_name'] ?? '') . ' ' . ($familyPerson['last_name'] ?? '') . ' ' . ($familyPerson['surname'] ?? ''),
    //                 'dob' => !empty($familyPerson['dob']) ? $familyPerson['dob'] : null,
    //                 'age' => !empty($familyPerson['age']) ? $familyPerson['age'] : null,
    //                 'email' => $familyPerson['email'] ?? '',
    //                 'mobile' => $familyPerson['mobile'] ?? '',
    //                 'address_house_no_building_name' => $familyPerson['address_house_no_building_name'] ?? '',
    //                 'address_landmark' => $familyPerson['address_landmark'] ?? '',
    //                 'address_area' => $familyPerson['address_area'] ?? '',
    //                 'address_country_id' => !empty($familyPerson['address_country_id']) ? $familyPerson['address_country_id'] : null,
    //                 'address_state_id' => !empty($familyPerson['address_state_id']) ? $familyPerson['address_state_id'] : null,
    //                 'address_city_id' => !empty($familyPerson['address_city_id']) ? $familyPerson['address_city_id'] : null,
    //                 'address_pincode' => !empty($familyPerson['address_pincode']) ? $familyPerson['address_pincode'] : null,
    //                 'marital_status' => $familyPerson['marital_status'] ?? '',
    //                 'gender' => $familyPerson['gender'] ?? '',
    //                 'job_role' => $familyPerson['job_role'] ?? '',
    //                 'company_name' => $familyPerson['company_name'] ?? '',
    //                 'company_house_no_building_name' => $familyPerson['company_house_no_building_name'] ?? '',
    //                 'company_landmark' => $familyPerson['company_landmark'] ?? '',
    //                 'company_area' => $familyPerson['company_area'] ?? '',
    //                 'company_country_id' => !empty($familyPerson['company_country_id']) ? $familyPerson['company_country_id'] : null,
    //                 'company_state_id' => !empty($familyPerson['company_state_id']) ? $familyPerson['company_state_id'] : null,
    //                 'company_city_id' => !empty($familyPerson['company_city_id']) ? $familyPerson['company_city_id'] : null,
    //                 'company_pincode' => !empty($familyPerson['company_pincode']) ? $familyPerson['company_pincode'] : null,
    //                 'business_intro' => $familyPerson['business_intro'] ?? '',
    //                 'services' => $familyPerson['services'] ?? '',
    //                 'seo_keywords' => $familyPerson['seo_keywords'] ?? '',
    //                 'business_category' => !empty($familyPerson['business_category']) ? $familyPerson['business_category'] : null,
    //                 'business_subcategory' => !empty($familyPerson['business_subcategory']) ? $familyPerson['business_subcategory'] : null,
    //                 'main_family_member' => $familyPerson['main_family_member'] ?? 0,
    //                 'relationship_id' => !empty($familyPerson['relationship_id']) ? $familyPerson['relationship_id'] : null,
    //                 'shakh' => $familyPerson['shakh'] ?? '',
    //                 'created_by' => $this->userId,
    //             ];
    //             $familyPersonstore = $this->familyPersonModel::create($familyPersonStoredata);
                
    //             $passwordtoken = Str::random(40);
    //             if($familyPersonstore){
    //                 $familyPersonIds[] = (string) $familyPersonstore->id;
    //                 // Update main family table add main family person id and if this main family person then add member record
    //                 if ($familyPersonstore->main_family_member == 1){
    //                     $this->familyModel::where('id', $familyPersonstore->family_id)->update(['mainFamilyPersonId' => $familyPersonstore->id]);
    //                     $member = [
    //                         'family_id' => $familyPersonstore->family_id,
    //                         'family_person_id' => $familyPersonstore->id,
    //                         'lifetime_member_no' => $familyPerson['lifetime_member_no'] ?? '',
    //                         'receipt_number' => $familyPerson['receipt_number'] ?? '',
    //                         'status'      => ($familyPerson['lifetime_member_no'] && $familyPerson['receipt_number']) ? 1 : 0,
    //                         'created_by' => $this->userId,
    //                     ];
    //                 $memberstore = $this->memberModel::create($member);
    //                 $userdata = [
    //                         'firstname' => $familyPersonstore->first_name,
    //                         'lastname' => $familyPersonstore->last_name,
    //                         'role' => 3,
    //                         'email' => $familyPersonstore->email,
    //                         'password' => Hash::make($familyPersonstore->email),
    //                         'contact_no' => $familyPersonstore->mobile,
    //                         'country_id' => $familyPersonstore->address_country_id,
    //                         'state_id' => $familyPersonstore->address_state_id,
    //                         'city_id' => $familyPersonstore->address_city_id,
    //                         'pincode' => $familyPersonstore->address_pincode,
    //                         'role_permissions' => 2,
    //                         'pass_token' => $passwordtoken,
    //                         'company_id' => $this->companyId,
    //                         'family_person_id' => $familyPersonstore->id,
    //                         'created_by' => $this->userId
    //                     ];
    //                     $rp = userrolepermission::where('id', 2)->first();
    //                     $user_rp = $rp->role_permissions;
    //                     $users = User::create($userdata);
    //                     $userrp = $this->user_permissionModel::create([
    //                         'user_id' => $users->id,
    //                         'rp' => $user_rp,
    //                         'created_by' => $this->userId
    //                     ]);
    //                 }
    //                 else{
    //                 $userdata = [
    //                         'firstname' => $familyPersonstore->first_name,
    //                         'lastname' => $familyPersonstore->last_name,
    //                         'role' => 3,
    //                         'email' => $familyPersonstore->email,
    //                         'password' => Hash::make($familyPersonstore->email),
    //                         'contact_no' => $familyPersonstore->mobile,
    //                         'country_id' => $familyPersonstore->address_country_id,
    //                         'state_id' => $familyPersonstore->address_state_id,
    //                         'city_id' => $familyPersonstore->address_city_id,
    //                         'pincode' => $familyPersonstore->address_pincode,
    //                         'role_permissions' => 1,
    //                         'pass_token' => $passwordtoken,
    //                         'company_id' => $this->companyId,
    //                         'family_person_id' => $familyPersonstore->id,
    //                         'created_by' => $this->userId
    //                     ];
    //                     $rp = userrolepermission::where('id', 1)->first();
    //                     $users = User::create($userdata);
    //                     $user_rp = $rp->role_permissions;
    //                     $userrp = $this->user_permissionModel::create([
    //                         'user_id' => $users->id,
    //                         'rp' => $user_rp,
    //                         'created_by' => $this->userId
    //                     ]);
    //                 }
    //             }

    //         }
    //         $this->familyModel::where('id', $family['id'])->update(['familyPersonIds' => json_encode($familyPersonIds)]);

    //     }
    //     else{
    //         return $this->successresponse(422, 'message', 'Family persons are required');
    //     }
    //     return $this->successresponse(200, 'message', 'Family and family persons created successfully');
        
    // }
    // Convert "", "  ", null to null. Keeps 0 and "0" as valid values.
    private function blankToNull($value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        return ($value === '' || $value === null) ? null : $value;
    }

    public function familyStore(Request $request)
    {
        if ($this->rp['societymodule']['familymembers']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        // ── 1. Validation ────────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'famliypersons'                         => 'required|array|min:1',
            'famliypersons.*.first_name'            => 'required|string|max:255',
            'famliypersons.*.email'                 => 'required|email|distinct',
            'famliypersons.*.main_family_member'    => 'required|in:0,1',
            'famliypersons.*.relationship_id'       => 'required_if:famliypersons.*.main_family_member,0|nullable|integer',
            'famliypersons.*.dob'                   => 'nullable|date',
            'famliypersons.*.age'                   => 'nullable|integer|min:0|max:150',
            'famliypersons.*.address_pincode'       => 'nullable|integer',
            'famliypersons.*.company_pincode'       => 'nullable|integer',
        ], [
            'famliypersons.required'                        => 'Family persons are required',
            'famliypersons.array'                           => 'Family persons must be an array',
            'famliypersons.*.first_name.required'           => 'First name is required for family member :position',
            'famliypersons.*.email.required'                => 'Email is required for family member :position',
            'famliypersons.*.email.email'                   => 'Email is invalid for family member :position',
            'famliypersons.*.email.distinct'                => 'Duplicate email found in the list (member :position)',
            'famliypersons.*.main_family_member.required'   => 'Main family member flag is required for member :position',
            'famliypersons.*.main_family_member.in'         => 'Main family member must be 0 or 1 (member :position)',
            'famliypersons.*.relationship_id.required_if'   => 'Relationship is required for family member :position',
            'famliypersons.*.dob.date'                      => 'Date of birth is invalid for member :position',
            'famliypersons.*.age.integer'                   => 'Age must be a number for member :position',
            'famliypersons.*.address_pincode.integer'       => 'Address pincode must be a number for member :position',
            'famliypersons.*.company_pincode.integer'       => 'Company pincode must be a number for member :position',
        ]);

        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors()->first());
        }

        // ── 2. Main member rules ─────────────────────────────────────────
        $mainMembers = array_filter($request->famliypersons, fn($p) => ($p['main_family_member'] ?? 0) == 1);

        if (count($mainMembers) == 0) {
            return $this->successresponse(422, 'message', 'Main family member is required');
        }
        if (count($mainMembers) > 1) {
            return $this->successresponse(422, 'message', 'Only one main family member is allowed');
        }

        // ── 3. Uniqueness checks ─────────────────────────────────────────
        foreach ($request->famliypersons as $index => $person) {
            $position = $index + 1;

            if (User::where('email', $person['email'])->where('is_deleted', 0)->exists()) {
                return $this->successresponse(422, 'message', "Email already exists for family member {$position}");
            }

            if (($person['main_family_member'] ?? 0) == 1) {
                $lifetimeNo = $this->blankToNull($person['lifetime_member_no'] ?? null);
                $receiptNo  = $this->blankToNull($person['receipt_number'] ?? null);

                if ($lifetimeNo !== null &&
                    $this->memberModel::where('lifetime_member_no', $lifetimeNo)->where('is_deleted', 0)->exists()) {
                    return $this->successresponse(422, 'message', "Lifetime member number already exists for main family member");
                }
                if ($receiptNo !== null &&
                    $this->memberModel::where('receipt_number', $receiptNo)->where('is_deleted', 0)->exists()) {
                    return $this->successresponse(422, 'message', "Receipt number already exists for main family member");
                }
            }
        }

        // ── 4. Save ──────────────────────────────────────────────────────
        DB::connection('dynamic_connection')->beginTransaction();
        DB::beginTransaction();

        try {
            $main = array_values($mainMembers)[0];
            $mainFullName = trim(
                ($main['first_name'] ?? '') . ' ' . ($main['last_name'] ?? '') . ' ' . ($main['surname'] ?? '')
            );

            $family = $this->familyModel::create([
                'mainFamilyMemberFullName' => $mainFullName,
                'familyPersonIds'          => null,
                'mainFamilyPersonId'       => null,
                'created_by'               => $this->userId,
            ]);

            $stringFields = [
                'first_name', 'last_name', 'surname', 'email', 'mobile',
                'address_house_no_building_name', 'address_landmark', 'address_area',
                'marital_status', 'gender', 'job_role', 'company_name',
                'company_house_no_building_name', 'company_landmark', 'company_area',
                'business_intro', 'services', 'seo_keywords', 'shakh',
            ];
            $intFields = [
                'dob', 'age',
                'address_country_id', 'address_state_id', 'address_city_id', 'address_pincode',
                'company_country_id', 'company_state_id', 'company_city_id', 'company_pincode',
                'business_category', 'business_subcategory',
            ];

            $familyPersonIds = [];

            foreach ($request->famliypersons as $familyPerson) {

                $personData = [
                    'family_id'          => $family->id,
                    'main_family_member' => (int) ($familyPerson['main_family_member'] ?? 0),
                    'relationship_id'    => $this->blankToNull($familyPerson['relationship_id'] ?? null) ?? 0, // NOT NULL column
                    'created_by'         => $this->userId,
                ];

                foreach (array_merge($stringFields, $intFields) as $field) {
                    $personData[$field] = $this->blankToNull($familyPerson[$field] ?? null);
                }

                $personData['full_name'] = trim(
                    ($familyPerson['first_name'] ?? '') . ' ' .
                    ($familyPerson['last_name'] ?? '') . ' ' .
                    ($familyPerson['surname'] ?? '')
                );

                $familyPersonstore = $this->familyPersonModel::create($personData);
                $familyPersonIds[] = (string) $familyPersonstore->id;

                $isMain = $familyPersonstore->main_family_member == 1;

                if ($isMain) {
                    $lifetimeNo = $this->blankToNull($familyPerson['lifetime_member_no'] ?? null);
                    $receiptNo  = $this->blankToNull($familyPerson['receipt_number'] ?? null);

                    $this->familyModel::where('id', $family->id)
                        ->update(['mainFamilyPersonId' => $familyPersonstore->id]);

                    $this->memberModel::create([
                        'family_id'          => $familyPersonstore->family_id,
                        'family_person_id'   => $familyPersonstore->id,
                        'lifetime_member_no' => $lifetimeNo,
                        'receipt_number'     => $receiptNo,
                        'status'             => ($lifetimeNo !== null && $receiptNo !== null) ? 1 : 0,
                        'created_by'         => $this->userId,
                    ]);
                }

                // Main member -> role_permissions 2, others -> 1
                $roleId = $isMain ? 2 : 1;
                $rp     = userrolepermission::where('id', $roleId)->first();

                $user = User::create([
                    'firstname'        => $familyPersonstore->first_name,
                    'lastname'         => $familyPersonstore->last_name,
                    'role'             => 3,
                    'email'            => $familyPersonstore->email,
                    'password'         => Hash::make($familyPersonstore->email),
                    'contact_no'       => $familyPersonstore->mobile,
                    'country_id'       => $familyPersonstore->address_country_id,
                    'state_id'         => $familyPersonstore->address_state_id,
                    'city_id'          => $familyPersonstore->address_city_id,
                    'pincode'          => $familyPersonstore->address_pincode,
                    'role_permissions' => $roleId,
                    'pass_token'       => Str::random(40),
                    'company_id'       => $this->companyId,
                    'family_person_id' => $familyPersonstore->id,
                    'created_by'       => $this->userId,
                ]);

                $this->user_permissionModel::create([
                    'user_id'    => $user->id,
                    'rp'         => $rp->role_permissions,
                    'created_by' => $this->userId,
                ]);
            }

            $this->familyModel::where('id', $family->id)
                ->update(['familyPersonIds' => json_encode($familyPersonIds)]);

            DB::connection('dynamic_connection')->commit();
            DB::commit();

            return $this->successresponse(200, 'message', 'Family and family persons created successfully');

        } catch (\Throwable $e) {
            DB::connection('dynamic_connection')->rollBack();
            DB::rollBack();
            Log::error('familyStore failed: ' . $e->getMessage());
            return $this->successresponse(500, 'message', 'Something went wrong while saving family');
        }
    }
    // load business categories for family person
    public function loadbusinesscategory(){
        $businesscategories = $this->businesscategoryModel::where('is_deleted',0)->get();
        if($businesscategories->isEmpty()){
            return $this->successresponse(404, 'message', 'No business categories found');
        }
        return $this->successresponse(200, 'businesscategories', $businesscategories);
    }
    // load business subcategories for family person
    public function loadbusinesssubcategory($categoryId){
        $businesssubcategories = $this->businesssubcategoryModel::where('is_deleted',0)->where('category_id',$categoryId)->get();
        if($businesssubcategories->isEmpty()){
            return $this->successresponse(404, 'message', 'No business subcategories found');
        }
        return $this->successresponse(200, 'businesssubcategories', $businesssubcategories);
    }
    // load relations for family person
    public function loadrelation(){
        $relations = $this->familyrelationModel::where('is_deleted',0)->get();
        if($relations->isEmpty()){
            return $this->successresponse(404, 'message', 'No relations found');
        }
        return $this->successresponse(200, 'relations', $relations);
    }
    // family show with all family persons and user details
    public function familyShow($id)
    {
        if ($this->rp['societymodule']['familymembers']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $family = $this->familyModel::where('id', $id)->first();

        if (!$family) {
            return $this->successresponse(404, 'message', 'Family not found');
        }

       $familyPersons = $this->familyPersonModel
        ::leftJoin('business_sub_category as bsc', 'bsc.id', '=', 'family_person.business_subcategory')
        ->leftJoin('business_category as bc', 'bc.id', '=', 'family_person.business_category')

        ->leftJoin($this->masterdbname.'.country as company_country', 'company_country.id', '=', 'family_person.company_country_id')
        ->leftJoin($this->masterdbname.'.country as address_country', 'address_country.id', '=', 'family_person.address_country_id')

        ->leftJoin($this->masterdbname.'.state as company_state', 'company_state.id', '=', 'family_person.company_state_id')
        ->leftJoin($this->masterdbname.'.state as address_state', 'address_state.id', '=', 'family_person.address_state_id')

        ->leftJoin($this->masterdbname.'.city as company_city', 'company_city.id', '=', 'family_person.company_city_id')
        ->leftJoin($this->masterdbname.'.city as address_city', 'address_city.id', '=', 'family_person.address_city_id')

        ->where('family_person.family_id', $id)
        ->where('family_person.is_deleted', 0)

        ->select(
            'family_person.*',
            'bsc.name as business_subcategory_name',
            'bc.name as business_category_name',

            'company_country.country_name as company_country_name',
            'address_country.country_name as address_country_name',

            'company_state.state_name as company_state_name',
            'address_state.state_name as address_state_name',

            'company_city.city_name as company_city_name',
            'address_city.city_name as address_city_name'
        )
        ->get();

        $familydata = [
            'familydetails' => $family,
            'familyPersons' => $familyPersons
        ];

        return $this->successresponse(200, 'family', $familydata);
    }
    // family edit with all family persons and user details
    public function familyEdit($id){
       if ($this->rp['societymodule']['familymembers']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $family = $this->familyModel::where('id', $id)->first();

        if (!$family) {
            return $this->successresponse(404, 'message', 'Family not found');
        }

        $familyPersons = $this->familyPersonModel
            ::where('family_id', $id)
            ->where('is_deleted', 0)
            ->get();

        $familydata = [
            'familydetails' => $family,
            'familyPersons' => $familyPersons
        ];

        return $this->successresponse(200, 'family', $familydata);
    }
    // family update with all family persons and user details
    // public function familyUpdate(Request $request)
    // {
    //     if ($this->rp['societymodule']['familymembers']['edit'] != 1) {
    //         return $this->successresponse(500, 'message', 'You are Unauthorized');
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'family_id'             => 'required|integer',
    //         'famliypersons'         => 'required|array',
    //         'famliypersons.*.email' => 'required|email',
    //     ]);
    //     if ($validator->fails()) {
    //         return $this->successresponse(422, 'message', $validator->errors()->first());
    //     }

    //     // ── 1. Validate main-member rules ────────────────────────────────────────
    //     $mainMembers = array_filter($request->famliypersons, function ($person) {
    //         return isset($person['main_family_member']) && $person['main_family_member'] == 1;
    //     });

    //     if (count($mainMembers) == 0) {
    //         return $this->successresponse(422, 'message', 'Main family member is required');
    //     }
    //     if (count($mainMembers) > 1) {
    //         return $this->successresponse(422, 'message', 'Only one main family member is allowed');
    //     }

    //     // ── 2. Validate email, lifetime_member_no & receipt_number uniqueness ────
    //     foreach ($request->famliypersons as $index => $person) {

    //         // Email uniqueness check
    //         if (isset($person['email']) && !empty($person['email'])) {
    //             $checkEmail = User::where('email', $person['email'])
    //                 ->where('is_deleted', 0)
    //                 ->when(!empty($person['family_person_id']), function ($q) use ($person) {
    //                     $q->where('family_person_id', '!=', $person['family_person_id']);
    //                 })
    //                 ->exists();
    //             if ($checkEmail) {
    //                 return $this->successresponse(422, 'message', "Email already exists for family member at index {$index}");
    //             }
    //         }

    //         if (isset($person['main_family_member']) && $person['main_family_member'] == 1) {

    //             if ($person['lifetime_member_no'] !== null && $person['lifetime_member_no'] !== '') {
    //                 $checkLifetime = $this->memberModel::where('lifetime_member_no', $person['lifetime_member_no'])
    //                     ->where('is_deleted', 0)
    //                     ->when(!empty($person['family_person_id']), function ($q) use ($person) {
    //                         $q->where('family_person_id', '!=', $person['family_person_id']);
    //                     })
    //                     ->first();
    //                 if ($checkLifetime) {
    //                     return $this->successresponse(422, 'message', "Lifetime member number already exists for main family member at index {$index}");
    //                 }
    //             }

    //             if ($person['receipt_number'] !== null && $person['receipt_number'] !== '') {
    //                 $checkReceipt = $this->memberModel::where('receipt_number', $person['receipt_number'])
    //                     ->where('is_deleted', 0)
    //                     ->when(!empty($person['family_person_id']), function ($q) use ($person) {
    //                         $q->where('family_person_id', '!=', $person['family_person_id']);
    //                     })
    //                     ->first();
    //                 if ($checkReceipt) {
    //                     return $this->successresponse(422, 'message', "Receipt number already exists for main family member at index {$index}");
    //                 }
    //             }
    //         }
    //     }

    //     // ── 3. Load existing family ───────────────────────────────────────────────
    //     $family = $this->familyModel::where('id', $request->family_id)->first();
    //     if (!$family) {
    //         return $this->successresponse(422, 'message', 'Family not found');
    //     }

    //     // ── 4. Soft-delete persons & users removed from the list ─────────────────
    //     $incomingPersonIds = array_filter(
    //         array_column($request->famliypersons, 'family_person_id')
    //     );

    //     $removedPersonIds = $this->familyPersonModel::where('family_id', $request->family_id)
    //         ->whereNotIn('id', $incomingPersonIds)
    //         ->pluck('id');

    //     if ($removedPersonIds->isNotEmpty()) {
    //         $this->familyPersonModel::whereIn('id', $removedPersonIds)
    //             ->update(['is_deleted' => 1, 'updated_by' => $this->userId]);

    //         User::whereIn('family_person_id', $removedPersonIds)
    //             ->update(['is_deleted' => 1, 'updated_by' => $this->userId]);
    //     }

    //     // ── 5. Build main member full name ────────────────────────────────────────
    //     $hasMainMember = array_values($mainMembers)[0];
    //     $mainMemberFullName = trim(
    //         ($hasMainMember['first_name'] ?? '') . ' ' .
    //         ($hasMainMember['last_name']  ?? '') . ' ' .
    //         ($hasMainMember['surname']    ?? '')
    //     );

    //     $familyPersonIds    = [];
    //     $mainFamilyPersonId = null;
    //     $karobaripersonids  = $this->karobarimemberModel::where('is_deleted', 0)->get()->pluck('familyPersonId')->toArray();
    //     // ── 6. Loop through each person ───────────────────────────────────────────
    //     foreach ($request->famliypersons as $familyPerson) {
            
    //         // ★ Fix: integer fields use null, string fields use null too
    //         $personData = [
    //             'family_id'                        => $family->id,
    //             'first_name'                       => $familyPerson['first_name'] ?? null,
    //             'last_name'                        => $familyPerson['last_name'] ?? null,
    //             'surname'                          => $familyPerson['surname'] ?? null,
    //             'full_name'                        => trim(($familyPerson['first_name'] ?? '') . ' ' . ($familyPerson['last_name'] ?? '') . ' ' . ($familyPerson['surname'] ?? '')),
    //             'dob'                              => !empty($familyPerson['dob']) ? $familyPerson['dob'] : null,
    //             'age'                              => !empty($familyPerson['age']) ? $familyPerson['age'] : null,
    //             'email'                            => $familyPerson['email'] ?? null,
    //             'mobile'                           => $familyPerson['mobile'] ?? null,
    //             'address_house_no_building_name'   => $familyPerson['address_house_no_building_name'] ?? null,
    //             'address_landmark'                 => $familyPerson['address_landmark'] ?? null,
    //             'address_area'                     => $familyPerson['address_area'] ?? null,
    //             'address_country_id'               => !empty($familyPerson['address_country_id']) ? $familyPerson['address_country_id'] : null,
    //             'address_state_id'                 => !empty($familyPerson['address_state_id']) ? $familyPerson['address_state_id'] : null,
    //             'address_city_id'                  => !empty($familyPerson['address_city_id']) ? $familyPerson['address_city_id'] : null,
    //             'address_pincode'                  => !empty($familyPerson['address_pincode']) ? $familyPerson['address_pincode'] : null,
    //             'marital_status'                   => $familyPerson['marital_status'] ?? null,
    //             'gender'                           => $familyPerson['gender'] ?? null,
    //             'job_role'                         => $familyPerson['job_role'] ?? null,
    //             'company_name'                     => $familyPerson['company_name'] ?? null,
    //             'company_house_no_building_name'   => $familyPerson['company_house_no_building_name'] ?? null,
    //             'company_landmark'                 => $familyPerson['company_landmark'] ?? null,
    //             'company_area'                     => $familyPerson['company_area'] ?? null,
    //             'company_country_id'               => !empty($familyPerson['company_country_id']) ? $familyPerson['company_country_id'] : null,
    //             'company_state_id'                 => !empty($familyPerson['company_state_id']) ? $familyPerson['company_state_id'] : null,
    //             'company_city_id'                  => !empty($familyPerson['company_city_id']) ? $familyPerson['company_city_id'] : null,
    //             'company_pincode'                  => !empty($familyPerson['company_pincode']) ? $familyPerson['company_pincode'] : null,
    //             'business_intro'                   => $familyPerson['business_intro'] ?? null,
    //             'services'                         => $familyPerson['services'] ?? null,
    //             'seo_keywords'                     => $familyPerson['seo_keywords'] ?? null,
    //             'business_category'                => !empty($familyPerson['business_category']) ? $familyPerson['business_category'] : null,
    //             'business_subcategory'             => !empty($familyPerson['business_subcategory']) ? $familyPerson['business_subcategory'] : null,
    //             'main_family_member'               => $familyPerson['main_family_member'] ?? 0,
    //             'relationship_id'                  => !empty($familyPerson['relationship_id']) ? $familyPerson['relationship_id'] : null,
    //             'shakh'                            => $familyPerson['shakh'] ?? null,
    //             'updated_by'                       => $this->userId,
    //         ];

    //         // ── Existing person → UPDATE ──────────────────────────────────────────
    //         if (!empty($familyPerson['family_person_id'])) {
    //             $isKarobari = in_array($familyPerson['family_person_id'], $karobaripersonids);
    //             $familyPersonstore = $this->familyPersonModel::where('id', $familyPerson['family_person_id'])
    //                 ->where('family_id', $family->id)
    //                 ->first();

    //             if (!$familyPersonstore) {
    //                 return $this->successresponse(422, 'message', "Family person not found for family_person_id {$familyPerson['family_person_id']}");
    //             }

    //             $familyPersonstore->update($personData);

    //             // Update linked User record
    //             $user = User::where('family_person_id', $familyPersonstore->id)->first();
    //             if ($user) {
    //                 $user->update([
    //                     'firstname'        => $familyPersonstore->first_name,
    //                     'lastname'         => $familyPersonstore->last_name,
    //                     'email'            => $familyPersonstore->email,
    //                     'contact_no'       => $familyPersonstore->mobile,
    //                     'country_id'       => $familyPersonstore->address_country_id,
    //                     'state_id'         => $familyPersonstore->address_state_id,
    //                     'city_id'          => $familyPersonstore->address_city_id,
    //                     'pincode'          => $familyPersonstore->address_pincode,
    //                     'role_permissions' => $isKarobari ? 3 : ($familyPersonstore->main_family_member == 1 ? 2 : 1),
    //                     'updated_by'       => $this->userId,
    //                 ]);
                    
    //                 $rp = userrolepermission::where('id', $user->role_permissions)->first();
    //                 if ($rp) {
    //                     $user_rp = $rp->role_permissions;
    //                     $this->user_permissionModel::updateOrCreate([
    //                         'user_id' => $user->id,
    //                     ], [
    //                         'rp' => $user_rp,
    //                         'created_by' => $this->userId
    //                     ]);
    //                 }
    //             }
    //             // ★ Member record: update or create, restore if soft-deleted
    //             $isMain         = $familyPersonstore->main_family_member == 1;
    //             $existingMember = $this->memberModel::where('family_person_id', $familyPersonstore->id)->first();

    //             $memberData = [
    //                 'family_id'          => $familyPersonstore->family_id,
    //                 'family_person_id'   => $familyPersonstore->id,
    //                 'lifetime_member_no' => $familyPerson['lifetime_member_no'] ?? null,
    //                 'receipt_number'     => $familyPerson['receipt_number'] ?? null,
    //                 'status'             => (!empty($familyPerson['lifetime_member_no']) && !empty($familyPerson['receipt_number'])) ? 1 : 0,
    //                 'is_deleted'         => $isMain ? 0 : 1,  // restore if was deleted
    //                 'updated_by'         => $this->userId,
    //             ];

    //             if ($existingMember) {
    //                 $existingMember->update($memberData);
    //             } else if ($isMain) {
    //                 $memberData['created_by'] = $this->userId;
    //                 $this->memberModel::create($memberData);
    //             }

    //         // ── New person → CREATE ───────────────────────────────────────────────
    //         } else {

    //             $personData['created_by'] = $this->userId;
    //             $familyPersonstore        = $this->familyPersonModel::create($personData);
    //             $passwordtoken            = Str::random(40);

    //             // Create member record only for main member
    //             if ($familyPersonstore->main_family_member == 1) {
    //                 $this->memberModel::create([
    //                     'family_id'          => $familyPersonstore->family_id,
    //                     'family_person_id'   => $familyPersonstore->id,
    //                     'lifetime_member_no' => $familyPerson['lifetime_member_no'] ?? null,
    //                     'receipt_number'     => $familyPerson['receipt_number'] ?? null,
    //                     'status'             => (!empty($familyPerson['lifetime_member_no']) && !empty($familyPerson['receipt_number'])) ? 1 : 0,
    //                     'is_deleted'         => 0,
    //                     'created_by'         => $this->userId,
    //                 ]);
    //             }

    //             // Create User
    //             $user = User::create([
    //                 'firstname'        => $familyPersonstore->first_name,
    //                 'lastname'         => $familyPersonstore->last_name,
    //                 'role'             => 3,
    //                 'email'            => $familyPersonstore->email,
    //                 'password'         => Hash::make($familyPersonstore->email),
    //                 'contact_no'       => $familyPersonstore->mobile,
    //                 'country_id'       => $familyPersonstore->address_country_id,
    //                 'state_id'         => $familyPersonstore->address_state_id,
    //                 'city_id'          => $familyPersonstore->address_city_id,
    //                 'pincode'          => $familyPersonstore->address_pincode,
    //                 'role_permissions' => $familyPersonstore->main_family_member == 1 ? 2 : 1,
    //                 'pass_token'       => $passwordtoken,
    //                 'company_id'       => $this->companyId,
    //                 'family_person_id' => $familyPersonstore->id,
    //                 'created_by'       => $this->userId,
    //             ]);
    //             $rp = userrolepermission::where('id', $user->role_permissions)->first();
    //             $user_rp = $rp->role_permissions;
    //             $userrp = $this->user_permissionModel::updateOrCreate([
    //                 'user_id' => $user->id,
    //             ], [
    //                 'rp' => $user_rp,
    //                 'created_by' => $this->userId
    //             ]);
    //         }

    //         $familyPersonIds[] = (string) $familyPersonstore->id;

    //         if ($familyPersonstore->main_family_member == 1) {
    //             $mainFamilyPersonId = $familyPersonstore->id;
    //         }
    //     }

    //     // ── 7. Update family header ───────────────────────────────────────────────
    //     $this->familyModel::where('id', $family->id)->update([
    //         'mainFamilyMemberFullName' => $mainMemberFullName,
    //         'familyPersonIds'          => json_encode($familyPersonIds),
    //         'mainFamilyPersonId'       => $mainFamilyPersonId,
    //         'updated_by'               => $this->userId,
    //     ]);

    //     return $this->successresponse(200, 'message', 'Family and family persons updated successfully');
    // }
    public function familyUpdate(Request $request)
    {
        if ($this->rp['societymodule']['familymembers']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        // ── 1. Validation ────────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'family_id'                             => 'required|integer',
            'famliypersons'                         => 'required|array|min:1',
            'famliypersons.*.family_person_id'      => 'nullable|integer',
            'famliypersons.*.first_name'            => 'required|string|max:255',
            'famliypersons.*.email'                 => 'required|email|distinct',
            'famliypersons.*.main_family_member'    => 'required|in:0,1',
            'famliypersons.*.relationship_id'       => 'required_if:famliypersons.*.main_family_member,0|nullable|integer',
            'famliypersons.*.dob'                   => 'nullable|date',
            'famliypersons.*.age'                   => 'nullable|integer|min:0|max:150',
            'famliypersons.*.address_pincode'       => 'nullable|integer',
            'famliypersons.*.company_pincode'       => 'nullable|integer',
        ], [
            'family_id.required'                            => 'Family id is required',
            'family_id.integer'                             => 'Family id must be a number',
            'famliypersons.required'                        => 'Family persons are required',
            'famliypersons.array'                           => 'Family persons must be an array',
            'famliypersons.*.first_name.required'           => 'First name is required for family member :position',
            'famliypersons.*.email.required'                => 'Email is required for family member :position',
            'famliypersons.*.email.email'                   => 'Email is invalid for family member :position',
            'famliypersons.*.email.distinct'                => 'Duplicate email found in the list (member :position)',
            'famliypersons.*.main_family_member.required'   => 'Main family member flag is required for member :position',
            'famliypersons.*.main_family_member.in'         => 'Main family member must be 0 or 1 (member :position)',
            'famliypersons.*.relationship_id.required_if'   => 'Relationship is required for family member :position',
            'famliypersons.*.dob.date'                      => 'Date of birth is invalid for member :position',
            'famliypersons.*.age.integer'                   => 'Age must be a number for member :position',
            'famliypersons.*.address_pincode.integer'       => 'Address pincode must be a number for member :position',
            'famliypersons.*.company_pincode.integer'       => 'Company pincode must be a number for member :position',
        ]);

        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors()->first());
        }

        // ── 2. Main member rules ─────────────────────────────────────────
        $mainMembers = array_filter($request->famliypersons, fn($p) => ($p['main_family_member'] ?? 0) == 1);

        if (count($mainMembers) == 0) {
            return $this->successresponse(422, 'message', 'Main family member is required');
        }
        if (count($mainMembers) > 1) {
            return $this->successresponse(422, 'message', 'Only one main family member is allowed');
        }

        // ── 3. Load existing family ──────────────────────────────────────
        $family = $this->familyModel::where('id', $request->family_id)->first();
        if (!$family) {
            return $this->successresponse(422, 'message', 'Family not found');
        }

        // ── 4. Uniqueness checks (ignore the person's own records) ───────
        foreach ($request->famliypersons as $index => $person) {
            $position = $index + 1;
            $personId = $this->blankToNull($person['family_person_id'] ?? null);

            $emailExists = User::where('email', $person['email'])
                ->where('is_deleted', 0)
                ->when($personId, fn($q) => $q->where('family_person_id', '!=', $personId))
                ->exists();

            if ($emailExists) {
                return $this->successresponse(422, 'message', "Email already exists for family member {$position}");
            }

            if (($person['main_family_member'] ?? 0) == 1) {
                $lifetimeNo = $this->blankToNull($person['lifetime_member_no'] ?? null);
                $receiptNo  = $this->blankToNull($person['receipt_number'] ?? null);

                if ($lifetimeNo !== null) {
                    $exists = $this->memberModel::where('lifetime_member_no', $lifetimeNo)
                        ->where('is_deleted', 0)
                        ->when($personId, fn($q) => $q->where('family_person_id', '!=', $personId))
                        ->exists();
                    if ($exists) {
                        return $this->successresponse(422, 'message', 'Lifetime member number already exists for main family member');
                    }
                }

                if ($receiptNo !== null) {
                    $exists = $this->memberModel::where('receipt_number', $receiptNo)
                        ->where('is_deleted', 0)
                        ->when($personId, fn($q) => $q->where('family_person_id', '!=', $personId))
                        ->exists();
                    if ($exists) {
                        return $this->successresponse(422, 'message', 'Receipt number already exists for main family member');
                    }
                }
            }
        }

        // ── 5. Save everything in one transaction ────────────────────────
        DB::connection('dynamic_connection')->beginTransaction();
        DB::beginTransaction();

        try {
            // 5a. Soft-delete persons, users and members removed from the list
            $incomingPersonIds = array_filter(array_column($request->famliypersons, 'family_person_id'));

            $removedPersonIds = $this->familyPersonModel::where('family_id', $family->id)
                ->where('is_deleted', 0)
                ->whereNotIn('id', $incomingPersonIds)
                ->pluck('id');

            if ($removedPersonIds->isNotEmpty()) {
                $this->familyPersonModel::whereIn('id', $removedPersonIds)
                    ->update(['is_deleted' => 1, 'updated_by' => $this->userId]);

                $this->memberModel::whereIn('family_person_id', $removedPersonIds)
                    ->update(['is_deleted' => 1, 'updated_by' => $this->userId]);

                User::whereIn('family_person_id', $removedPersonIds)
                    ->update(['is_deleted' => 1, 'updated_by' => $this->userId]);
            }

            // 5b. Main member full name
            $main = array_values($mainMembers)[0];
            $mainMemberFullName = trim(
                ($main['first_name'] ?? '') . ' ' . ($main['last_name'] ?? '') . ' ' . ($main['surname'] ?? '')
            );

            $stringFields = [
                'first_name', 'last_name', 'surname', 'email', 'mobile',
                'address_house_no_building_name', 'address_landmark', 'address_area',
                'marital_status', 'gender', 'job_role', 'company_name',
                'company_house_no_building_name', 'company_landmark', 'company_area',
                'business_intro', 'services', 'seo_keywords', 'shakh',
            ];
            $intFields = [
                'dob', 'age',
                'address_country_id', 'address_state_id', 'address_city_id', 'address_pincode',
                'company_country_id', 'company_state_id', 'company_city_id', 'company_pincode',
                'business_category', 'business_subcategory',
            ];

            $familyPersonIds    = [];
            $mainFamilyPersonId = null;
            $karobaripersonids  = $this->karobarimemberModel::where('is_deleted', 0)
                ->pluck('familyPersonId')->toArray();

            // 5c. Loop through each person
            foreach ($request->famliypersons as $familyPerson) {

                $personData = [
                    'family_id'          => $family->id,
                    'main_family_member' => (int) ($familyPerson['main_family_member'] ?? 0),
                    'relationship_id'    => $this->blankToNull($familyPerson['relationship_id'] ?? null) ?? 0, // NOT NULL column
                    'updated_by'         => $this->userId,
                ];

                foreach (array_merge($stringFields, $intFields) as $field) {
                    $personData[$field] = $this->blankToNull($familyPerson[$field] ?? null);
                }

                $personData['full_name'] = trim(
                    ($familyPerson['first_name'] ?? '') . ' ' .
                    ($familyPerson['last_name'] ?? '') . ' ' .
                    ($familyPerson['surname'] ?? '')
                );

                $lifetimeNo = $this->blankToNull($familyPerson['lifetime_member_no'] ?? null);
                $receiptNo  = $this->blankToNull($familyPerson['receipt_number'] ?? null);
                $memberStatus = ($lifetimeNo !== null && $receiptNo !== null) ? 1 : 0;

                $existingId = $this->blankToNull($familyPerson['family_person_id'] ?? null);

                // ── Existing person → UPDATE ─────────────────────────────
                if ($existingId) {
                    $familyPersonstore = $this->familyPersonModel::where('id', $existingId)
                        ->where('family_id', $family->id)
                        ->first();

                    if (!$familyPersonstore) {
                        throw new \RuntimeException("Family person not found for family_person_id {$existingId}");
                    }

                    $familyPersonstore->update($personData);

                    $isMain     = $familyPersonstore->main_family_member == 1;
                    $isKarobari = in_array($familyPersonstore->id, $karobaripersonids);
                    $roleId     = $isKarobari ? 3 : ($isMain ? 2 : 1);

                    // Linked user
                    $user = User::where('family_person_id', $familyPersonstore->id)->first();
                    if ($user) {
                        $user->update([
                            'firstname'        => $familyPersonstore->first_name,
                            'lastname'         => $familyPersonstore->last_name,
                            'email'            => $familyPersonstore->email,
                            'contact_no'       => $familyPersonstore->mobile,
                            'country_id'       => $familyPersonstore->address_country_id,
                            'state_id'         => $familyPersonstore->address_state_id,
                            'city_id'          => $familyPersonstore->address_city_id,
                            'pincode'          => $familyPersonstore->address_pincode,
                            'role_permissions' => $roleId,
                            'updated_by'       => $this->userId,
                        ]);

                        $rp = userrolepermission::where('id', $roleId)->first();
                        if ($rp) {
                            $this->user_permissionModel::updateOrCreate(
                                ['user_id' => $user->id],
                                ['rp' => $rp->role_permissions, 'created_by' => $this->userId]
                            );
                        }
                    }

                    // Member record: update, or create if main, restore if soft-deleted
                    $existingMember = $this->memberModel::where('family_person_id', $familyPersonstore->id)->first();

                    $memberData = [
                        'family_id'          => $familyPersonstore->family_id,
                        'family_person_id'   => $familyPersonstore->id,
                        'lifetime_member_no' => $lifetimeNo,
                        'receipt_number'     => $receiptNo,
                        'status'             => $memberStatus,
                        'is_deleted'         => $isMain ? 0 : 1,
                        'updated_by'         => $this->userId,
                    ];

                    if ($existingMember) {
                        $existingMember->update($memberData);
                    } elseif ($isMain) {
                        $memberData['created_by'] = $this->userId;
                        $this->memberModel::create($memberData);
                    }

                // ── New person → CREATE ──────────────────────────────────
                } else {
                    $personData['created_by'] = $this->userId;
                    $familyPersonstore = $this->familyPersonModel::create($personData);

                    $isMain = $familyPersonstore->main_family_member == 1;
                    $roleId = $isMain ? 2 : 1;

                    if ($isMain) {
                        $this->memberModel::create([
                            'family_id'          => $familyPersonstore->family_id,
                            'family_person_id'   => $familyPersonstore->id,
                            'lifetime_member_no' => $lifetimeNo,
                            'receipt_number'     => $receiptNo,
                            'status'             => $memberStatus,
                            'is_deleted'         => 0,
                            'created_by'         => $this->userId,
                        ]);
                    }

                    $user = User::create([
                        'firstname'        => $familyPersonstore->first_name,
                        'lastname'         => $familyPersonstore->last_name,
                        'role'             => 3,
                        'email'            => $familyPersonstore->email,
                        'password'         => Hash::make($familyPersonstore->email),
                        'contact_no'       => $familyPersonstore->mobile,
                        'country_id'       => $familyPersonstore->address_country_id,
                        'state_id'         => $familyPersonstore->address_state_id,
                        'city_id'          => $familyPersonstore->address_city_id,
                        'pincode'          => $familyPersonstore->address_pincode,
                        'role_permissions' => $roleId,
                        'pass_token'       => Str::random(40),
                        'company_id'       => $this->companyId,
                        'family_person_id' => $familyPersonstore->id,
                        'created_by'       => $this->userId,
                    ]);

                    $rp = userrolepermission::where('id', $roleId)->first();
                    $this->user_permissionModel::updateOrCreate(
                        ['user_id' => $user->id],
                        ['rp' => $rp->role_permissions, 'created_by' => $this->userId]
                    );
                }

                $familyPersonIds[] = (string) $familyPersonstore->id;

                if ($familyPersonstore->main_family_member == 1) {
                    $mainFamilyPersonId = $familyPersonstore->id;
                }
            }

            // 5d. Update family header
            $this->familyModel::where('id', $family->id)->update([
                'mainFamilyMemberFullName' => $mainMemberFullName,
                'familyPersonIds'          => json_encode($familyPersonIds),
                'mainFamilyPersonId'       => $mainFamilyPersonId,
                'updated_by'               => $this->userId,
            ]);

            DB::connection('dynamic_connection')->commit();
            DB::commit();

            return $this->successresponse(200, 'message', 'Family and family persons updated successfully');

        } catch (\Throwable $e) {
            DB::connection('dynamic_connection')->rollBack();
            DB::rollBack();
            Log::error('familyUpdate failed: ' . $e->getMessage());
            return $this->successresponse(500, 'message', 'Something went wrong while updating family');
        }
    }
    // family destroy with all family persons
    public function familyDestroy($id)
    {
        if ($this->rp['societymodule']['familymembers']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $family = $this->familyModel::where('id', $id)->first();

        if (!$family) {
            return $this->successresponse(404, 'message', 'Family not found');
        }

        $familyPersons = $this->familyPersonModel
            ::where('family_id', $id)
            ->where('is_deleted', 0)
            ->get();

        $members = $this->memberModel
            ::where('family_id', $id)
            ->get();

        // Soft delete family
        $family->is_deleted = 1;
        $family->updated_by = $this->userId;
        $family->save();

        // Soft delete members
        foreach ($members as $m) {
            $m->is_deleted = 1;
            $m->updated_by = $this->userId;
            $m->save();
        }

        // Soft delete family persons + users
        foreach ($familyPersons as $familyPerson) {
            $familyPerson->is_deleted = 1;
            $familyPerson->updated_by = $this->userId;
            $familyPerson->save();

            User::where('family_person_id', $familyPerson->id)->update([
                'is_deleted' => 1,
                'updated_by' => $this->userId,
            ]);
        }

        return $this->successresponse(200, 'message', 'Family deleted successfully');
    }

    /**
     * Delete own account - user passes only their own user_id (token params).
     * No permission check: a user can always remove their own account.
     * Soft-deletes everywhere: users, family_person, members, kraobari_member,
     * and removes the person id from families.familyPersonIds.
     */
    public function deleteaccount(Request $request)
    {
        $user = User::where('id', $this->userId)->where('is_deleted', 0)->first();

        if (!$user) {
            return $this->successresponse(404, 'message', 'User not found');
        }

        $familyPersonId = $user->family_person_id;

        DB::connection('dynamic_connection')->beginTransaction();
        DB::beginTransaction();

        try {
            // 1. master users table - deactivate account + clear tokens
            $user->update([
                'is_deleted'      => 1,
                'is_active'       => 0,
                'user_login'      => 0,
                'api_token'       => null,
                'super_api_token' => null,
            ]);

            if ($familyPersonId) {
                $familyPerson = $this->familyPersonModel::where('id', $familyPersonId)->first();

                if ($familyPerson) {
                    // 2. family_person - soft delete
                    $familyPerson->update([
                        'is_deleted' => 1,
                        'updated_by' => $this->userId,
                    ]);

                    // 3. families - remove this id from familyPersonIds + clear mainFamilyPersonId
                    $family = $this->familyModel::where('id', $familyPerson->family_id)->first();
                    if ($family) {
                        $ids = json_decode($family->familyPersonIds, true) ?: [];
                        $ids = array_values(array_filter($ids, fn($pid) => (string) $pid !== (string) $familyPersonId));

                        $familyUpdate = [
                            'familyPersonIds' => json_encode($ids),
                            'updated_by'      => $this->userId,
                        ];
                        if ((string) $family->mainFamilyPersonId === (string) $familyPersonId) {
                            $familyUpdate['mainFamilyPersonId'] = null;
                        }
                        $family->update($familyUpdate);
                    }

                    // 4. members - soft delete
                    $this->memberModel::where('family_person_id', $familyPersonId)
                        ->where('is_deleted', 0)
                        ->update(['is_deleted' => 1, 'updated_by' => $this->userId]);

                    // 5. kraobari_member - soft delete
                    $this->karobarimemberModel::where('familyPersonId', $familyPersonId)
                        ->where('is_deleted', 0)
                        ->update(['is_deleted' => 1, 'updated_by' => $this->userId]);
                }
            }

            DB::connection('dynamic_connection')->commit();
            DB::commit();

            return $this->successresponse(200, 'message', 'Account deleted successfully');

        } catch (\Throwable $e) {
            DB::connection('dynamic_connection')->rollBack();
            DB::rollBack();
            Log::error('deleteaccount failed: ' . $e->getMessage());
            return $this->successresponse(500, 'message', 'Something went wrong while deleting account');
        }
    }
}


