<?php

namespace App\Http\Controllers\v4_4_4\api;
use App\Models\v4_4_4\Family;
use App\Models\v4_4_4\FamilyPerson;
use App\Models\v4_4_4\Member;
use App\Models\v4_4_4\Biodata;
use App\Models\v4_4_4\karobarimember;
use App\Models\User;
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
   public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $businesscategoryModel, $businesssubcategoryModel, $biodataModel,$karobarimemberModel;
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
    public function store(Request $request)
    {
        if ($this->rp['societymodule']['karobarimember']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $request->validate([
            'members' => 'required|array',
        ]);

        $selectedMembers = $this->memberModel
            ::whereIn('id', $request->members)
            ->get();

        $newMemberIds = $selectedMembers->pluck('id')->toArray();
        foreach ($selectedMembers as $data) {

            $existing = $this->karobarimemberModel
                ::where('familyPersonId', $data->family_person_id)
                ->where('memberId', $data->id)
                ->first();

            if ($existing) {
                // ✅ If exists → just restore
                if ($existing->is_deleted == 1) {
                    $existing->update([
                        'is_deleted' => 0,
                        'updated_by' => $this->userId,
                    ]);
                }
            } else {
                // ✅ Insert new
                $this->karobarimemberModel::create([
                    'familyPersonId' => $data->family_person_id,
                    'memberId' => $data->id,
                    'created_by' => $this->userId,
                    'is_deleted' => 0,
                ]);
            }
        }

        // ✅ Now handle removed members (soft delete)
        $this->karobarimemberModel
            ::whereNotIn('memberId', $newMemberIds)
            ->update([
                'is_deleted' => 1,
                'updated_by' => $this->userId,
            ]);

        return $this->successresponse(200, 'message', 'Karobari members Added successfully');
    }

    public function loadmemberforkarobari(Request $request)
    {
        if ($this->rp['societymodule']['karobarimember']['add'] != 1) {
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
            'address_city.city_name as address_city_name')->where('members.status', 1)->where('members.is_deleted', 0)->get();
            if ($members->isEmpty()) {
                return $this->successresponse(404, 'message', 'No Data Found');
            }
        return $this->successresponse(200, 'members', $members);
    }
}
