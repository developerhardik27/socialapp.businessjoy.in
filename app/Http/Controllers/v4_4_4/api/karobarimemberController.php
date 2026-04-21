<?php

namespace App\Http\Controllers\v4_4_4\api;
use App\Models\v4_4_4\Family;
use App\Models\v4_4_4\FamilyPerson;
use App\Models\v4_4_4\Member;
use App\Models\v4_4_4\Biodata;
use App\Models\v4_4_4\karobarimember;
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


class karobarimemberController extends commonController
{
   public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $businesscategoryModel, $businesssubcategoryModel, $biodataModel,$karobarimemberModel,$user_permissionModel;
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
// dd($this->rp);
        $this->masterdbname = DB::connection()->getDatabaseName();
        $this->familyrelationModel = $this->getmodel('FamilyRelation');
        $this->familyModel = $this->getmodel('Family');
        $this->familyPersonModel = $this->getmodel('FamilyPerson');
        $this->memberModel = $this->getmodel('Member');
        $this->businesscategoryModel = $this->getmodel('BusinessCategory');
        $this->businesssubcategoryModel = $this->getmodel('BusinessSubCategory');
        $this->biodataModel = $this->getmodel('Biodata');
        $this->user_permissionModel = $this->getmodel('user_permission');
        $this->karobarimemberModel = $this->getmodel('karobarimember');
    }
    public function index(Request $request)
    {
        if ($this->rp['societymodule']['karobarimember']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $members = $this->karobarimemberModel::leftJoin('family_person', 'kraobari_member.familyPersonId', '=', 'family_person.id')
        ->leftJoin('business_sub_category as bsc', 'bsc.id', '=', 'family_person.business_subcategory')
        ->leftJoin('business_category as bc', 'bc.id', '=', 'family_person.business_category')    
        ->leftJoin($this->masterdbname.'.country as company_country', 'company_country.id', '=', 'family_person.company_country_id')
            ->leftJoin($this->masterdbname.'.country as address_country', 'address_country.id', '=', 'family_person.address_country_id')

            ->leftJoin($this->masterdbname.'.state as company_state', 'company_state.id', '=', 'family_person.company_state_id')
            ->leftJoin($this->masterdbname.'.state as address_state', 'address_state.id', '=', 'family_person.address_state_id')

            ->leftJoin($this->masterdbname.'.city as company_city', 'company_city.id', '=', 'family_person.company_city_id')
            ->leftJoin($this->masterdbname.'.city as address_city', 'address_city.id', '=', 'family_person.address_city_id')
            ->where('kraobari_member.is_deleted', 0)
            ->where('family_person.is_deleted', 0)
            
            ->select('kraobari_member.*', 'family_person.*','bsc.name as business_subcategory_name',
            'bc.name as business_category_name',

            'company_country.country_name as company_country_name',
            'address_country.country_name as address_country_name',

            'company_state.state_name as company_state_name',
            'address_state.state_name as address_state_name',

            'company_city.city_name as company_city_name',
            'address_city.city_name as address_city_name');
             if ($this->rp['societymodule']['karobarimember']['alldata'] != 1) {
                $members = $members->where('kraobari_member.created_by', $this->userId);
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
    // ── Store: single family_person_ids array ─────────────────────────────────────
    public function store(Request $request)
    {
        if ($this->rp['societymodule']['karobarimember']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $request->validate([
            'family_person_ids' => 'required|array',
        ]);

        $newFamilyPersonIds = $request->family_person_ids;

        // ── 1. Add / restore selected karobari members ────────────────────────────
        foreach ($newFamilyPersonIds as $familyPersonId) {

            // ✅ Auto check: does this family_person have a member record?
            $member   = $this->memberModel::where('family_person_id', $familyPersonId)
                            ->where('is_deleted', 0)
                            ->first();
            $memberId = $member ? $member->id : null;

            $existing = $this->karobarimemberModel
                ::where('familyPersonId', $familyPersonId)
                ->first();

            if ($existing) {
                if ($existing->is_deleted == 1) {
                    $existing->update([
                        'is_deleted' => 0,
                        'updated_by' => $this->userId,
                    ]);
                }
            } else {
                $this->karobarimemberModel::create([
                    'familyPersonId' => $familyPersonId,
                    'memberId'       => $memberId,   // ← member id if exists, null if not
                    'created_by'     => $this->userId,
                    'is_deleted'     => 0,
                ]);
            }

            $this->updateUserRole($familyPersonId, 3);
        }

        // ── 2. Soft-delete removed karobari members ───────────────────────────────
        $removedKarobaris = $this->karobarimemberModel
            ::whereNotIn('familyPersonId', $newFamilyPersonIds)
            ->where('is_deleted', 0)
            ->get();

        foreach ($removedKarobaris as $removed) {
            $removed->update([
                'is_deleted' => 1,
                'updated_by' => $this->userId,
            ]);

            $familyPerson = $this->familyPersonModel::where('id', $removed->familyPersonId)->first();
            $revertRole   = ($familyPerson && $familyPerson->main_family_member == 1) ? 2 : 1;

            $this->updateUserRole($removed->familyPersonId, $revertRole);
        }

        return $this->successresponse(200, 'message', 'Karobari members added successfully');
    }

    // ── Load: all family persons from eligible families ───────────────────────────
    public function loadmemberforkarobari(Request $request)
    {
        if ($this->rp['societymodule']['karobarimember']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        // Step 1: Get family_ids where member is active (status=1, is_deleted=0)
        $eligibleFamilyIds = $this->memberModel::where('status', 1)
            ->where('is_deleted', 0)
            ->pluck('family_id')
            ->unique()
            ->toArray();

        if (empty($eligibleFamilyIds)) {
            return $this->successresponse(404, 'message', 'No Data Found');
        }

        // Step 2: Get already selected karobari family_person_ids
        $alreadyKarobariIds = $this->karobarimemberModel::where('is_deleted', 0)
            ->pluck('familyPersonId')
            ->toArray();

        // Step 3: Fetch ALL family persons from eligible families
        $members = $this->familyPersonModel
            ::leftJoin('members', 'members.family_person_id', '=', 'family_person.id')
            ->leftJoin('business_sub_category as bsc', 'bsc.id', '=', 'family_person.business_subcategory')
            ->leftJoin('business_category as bc', 'bc.id', '=', 'family_person.business_category')
            ->leftJoin($this->masterdbname . '.country as company_country', 'company_country.id', '=', 'family_person.company_country_id')
            ->leftJoin($this->masterdbname . '.country as address_country', 'address_country.id', '=', 'family_person.address_country_id')
            ->leftJoin($this->masterdbname . '.state as company_state', 'company_state.id', '=', 'family_person.company_state_id')
            ->leftJoin($this->masterdbname . '.state as address_state', 'address_state.id', '=', 'family_person.address_state_id')
            ->leftJoin($this->masterdbname . '.city as company_city', 'company_city.id', '=', 'family_person.company_city_id')
            ->leftJoin($this->masterdbname . '.city as address_city', 'address_city.id', '=', 'family_person.address_city_id')
            ->whereIn('family_person.family_id', $eligibleFamilyIds)
            ->where('family_person.is_deleted', 0)
            ->select(
                'family_person.*',
                'members.id as member_id',
                'members.lifetime_member_no',
                'members.receipt_number',
                'members.status as member_status',
                'bsc.name as business_subcategory_name',
                'bc.name as business_category_name',
                'company_country.country_name as company_country_name',
                'address_country.country_name as address_country_name',
                'company_state.state_name as company_state_name',
                'address_state.state_name as address_state_name',
                'company_city.city_name as company_city_name',
                'address_city.city_name as address_city_name'
            )
            // ✅ Mark each person if already selected as karobari
            ->selectRaw('CASE WHEN family_person.id IN (' . implode(',', $alreadyKarobariIds ?: [0]) . ') THEN 1 ELSE 0 END as is_karobari')
            ->get();

        if ($members->isEmpty()) {
            return $this->successresponse(404, 'message', 'No Data Found');
        }

        return $this->successresponse(200, 'members', $members);
    }   

    // ── Helper: update user role & permissions ────────────────────────────────────
    private function updateUserRole($familyPersonId, $role)
    {
        $user = User::where('family_person_id', $familyPersonId)->first();
        if ($user) {
            $user->update([
                'role_permissions' => $role,
                'updated_by'       => $this->userId,
            ]);

            $rp = userrolepermission::where('id', $user->role_permissions)->first();
            if ($rp) {
                $this->user_permissionModel::updateOrCreate(
                    ['user_id' => $user->id],
                    ['rp' => $rp->role_permissions, 'created_by' => $this->userId]
                );
            }
        }
    }
}
