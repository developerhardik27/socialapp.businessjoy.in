<?php

namespace App\Http\Controllers\v4_4_4\api;
use App\Models\v4_4_4\Family;
use App\Models\v4_4_4\FamilyPerson;
use App\Models\v4_4_4\Member;
use App\Models\v4_4_4\Event;
use App\Models\v4_4_4\EventComment;
use App\Models\v4_4_4\EventLike;
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

class typeorstatusController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $businesscategoryModel, $businesssubcategoryModel, $data_formateModel,$user_permissionModel,$donorModel,$eventModel,$eventCommentModel,$eventLikeModel,$applicationModel,$policieModel,$karobarimeetingModel,$typeModel,$statusModel;
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
        $this->eventModel = $this->getmodel('Event');
        $this->eventCommentModel = $this->getmodel('EventComment');
        $this->eventLikeModel = $this->getmodel('EventLike');
        $this->applicationModel = $this->getmodel('Application');
        $this->policieModel = $this->getmodel('Policie');
        $this->karobarimeetingModel = $this->getmodel('Karobarimeeting');
        $this->karobari_meetings_attendanceModel = $this->getmodel('Karobari_meetings_attendance');
        $this->karobarimemberModel = $this->getmodel('karobarimember');
        $this->familyPersonModel = $this->getmodel('FamilyPerson');
        $this->typeModel = $this->getmodel('type');
        $this->statusModel = $this->getmodel('status');
    }

    public function typeindex(Request $request)
    {
        $types = $this->typeModel::where('category', $request->page)->get();
        if(!$types)
            {
                return $this->successresponse(404, 'message', 'Type not found');
            }
        return $this->successresponse(200,'types',$types);
    }
    public function statusindex(Request $request)
    {
        $status = $this->statusModel::where('category', $request->page)->get();
        if(!$status)
            {
                return $this->successresponse(404, 'message', 'Status not found');
            }
        return $this->successresponse(200,'status',$status);
    }
}
