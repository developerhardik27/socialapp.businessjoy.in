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

class KarobarimeetingController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $businesscategoryModel, $businesssubcategoryModel, $data_formateModel,$user_permissionModel,$donorModel,$eventModel,$eventCommentModel,$eventLikeModel,$applicationModel,$policieModel,$karobarimeetingModel;
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
    }
    public function index(Request $request)
    {
        if ($this->rp['societymodule']['karobarimeeting']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $meetings = $this->karobarimeetingModel::where('karobari_meetings.is_deleted', 0)->leftJoin($this->masterdbname.'.country as address_country', 'address_country.id', '=', 'karobari_meetings.country_id')
         ->leftJoin($this->masterdbname.'.state as address_state', 'address_state.id', '=', 'karobari_meetings.state_id')
        ->leftJoin($this->masterdbname.'.city as address_city', 'address_city.id', '=', 'karobari_meetings.city_id');
        if ($this->rp['societymodule']['karobarimeeting']['alldata'] != 1) {
            $meetings = $meetings->where('karobari_meetings.created_by', $this->userId);
        }

        $meetings = $meetings->select('karobari_meetings.*',
            'address_country.country_name as address_country_name',
            'address_state.state_name as address_state_name',
            'address_city.city_name as address_city_name')->get();
        if ($meetings->isEmpty()) {
            return DataTables::of($meetings)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }
        
        return DataTables::of($meetings)->with([
            'status' => 200,
        ])->make(true);
    }
    public function store(Request $request)
    {
        if ($this->rp['societymodule']['karobarimeeting']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $validator = Validator::make($request->all(), [
            'meeting_name' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'meeting_time_from' => 'required|date_format:H:i',
            'meeting_time_to' => 'required|date_format:H:i',
            'building_name' => 'nullable|string',
            'landmark' => 'nullable|string',
            'area' => 'nullable|string',
            'country_id' => 'nullable|integer',
            'state_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'pincode' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors());
        }
        $meeting = $this->karobarimeetingModel::create([
            'meeting_name' => $request->meeting_name,
            'meeting_date' => $request->meeting_date,
            'meeting_time_from' => $request->meeting_time_from,
            'meeting_time_to' => $request->meeting_time_to,
            'building_name' => $request->building_name,
            'landmark' => $request->landmark,
            'area' => $request->area,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'pincode' => $request->pincode,
            'created_by' => $this->userId,
        ]);
        
        if(!$meeting){
            return $this->successresponse(500, 'message', 'Failed to create meeting');
        }
        return $this->successresponse(200, 'message', 'Meeting created successfully');
    }
    
    public function show(Request $request, $id)
    {
        if ($this->rp['societymodule']['karobarimeeting']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $meeting = $this->karobarimeetingModel::where('karobari_meetings.id', $id)->where('karobari_meetings.is_deleted', 0)
            ->leftJoin($this->masterdbname.'.country as address_country', 'address_country.id', '=', 'karobari_meetings.country_id')
            ->leftJoin($this->masterdbname.'.state as address_state', 'address_state.id', '=', 'karobari_meetings.state_id')
            ->leftJoin($this->masterdbname.'.city as address_city', 'address_city.id', '=', 'karobari_meetings.city_id')
            ->select('karobari_meetings.*',
                'address_country.country_name as address_country_name',
                'address_state.state_name as address_state_name',
                'address_city.city_name as address_city_name')
            ->first();

        if (!$meeting) {
            return $this->successresponse(404, 'message', 'Meeting not found');
        }

        return $this->successresponse(200, 'meeting', $meeting);
    }
    
    public function edit(Request $request, $id)
    {
        if ($this->rp['societymodule']['karobarimeeting']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $meeting = $this->karobarimeetingModel::find($id);
        if(!$meeting){
            return $this->successresponse(404, 'message', 'Meeting not found');
        }
        return $this->successresponse(200, 'meeting', $meeting);
    }
    
    public function update(Request $request, $id)
    {
        if ($this->rp['societymodule']['karobarimeeting']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $validator = Validator::make($request->all(), [
            'meeting_name' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'meeting_time_from' => 'required|date_format:H:i',
            'meeting_time_to' => 'required|date_format:H:i',
            'building_name' => 'nullable|string',
            'landmark' => 'nullable|string',
            'area' => 'nullable|string',
            'country_id' => 'nullable|integer',
            'state_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'pincode' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors());
        }
        
        $meeting = $this->karobarimeetingModel::find($id);
        if(!$meeting){
            return $this->successresponse(404, 'message', 'Meeting not found');
        }
        
        $meeting->update([
            'meeting_name' => $request->meeting_name,
            'meeting_date' => $request->meeting_date,
            'meeting_time_from' => $request->meeting_time_from,
            'meeting_time_to' => $request->meeting_time_to,
            'building_name' => $request->building_name,
            'landmark' => $request->landmark,
            'area' => $request->area,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'pincode' => $request->pincode,
            'updated_by' => $this->userId
        ]);
        
        return $this->successresponse(200, 'message', 'Meeting updated successfully', $meeting);
    }
    
    public function destroy(Request $request, $id)
    {
        if ($this->rp['societymodule']['karobarimeeting']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $meeting = $this->karobarimeetingModel::find($id);
        if(!$meeting){
            return $this->successresponse(404, 'message', 'Meeting not found');
        }
        
        $meeting->update([
            'is_deleted' => 1,
            'updated_by' =>$this->userId
        ]);
        return $this->successresponse(200, 'message', 'Meeting deleted successfully');
    }
    
    // Attendance Methods
    public function storeAttendance(Request $request)
    {
        if ($this->rp['societymodule']['karobarimeeting']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $validator = Validator::make($request->all(), [
            'karobari_meeting_id' => 'required|integer',
            'karobari_member_ids' => 'required|array',
        ]);
        
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors());
        }
        
        // Delete existing attendance for this meeting
        $this->karobari_meetings_attendanceModel::where('karobari_meeting_id', $request->karobari_meeting_id)
            ->where('is_deleted', 0)
            ->update(['is_deleted' => 1, 'updated_by' => $this->userId]);
        
        // Store single attendance record with arrays
        $karobariMemberIds = $request->karobari_member_ids;
        
        $familyPersonIds = [];
        $memberIds = [];
        $karobariMemberData = [];
        
        foreach ($karobariMemberIds as $memberId => $status) {
            // Get family person and member details for this karobari member
            $karobariMember = $this->karobarimemberModel::where('id', $memberId)
                ->where('is_deleted', 0)
                ->first();
                
            if ($karobariMember) {
                $familyPersonIds[] = $karobariMember->familyPersonId;
                $memberIds[] = $memberId;
                $karobariMemberData[] = [
                    'id' => $memberId,
                    'status' => $status
                ];
            }
        }
        
        // Create single attendance record with arrays
        $this->karobari_meetings_attendanceModel::create([
            'karobari_meeting_id' => $request->karobari_meeting_id,
            'family_person_id' => json_encode($familyPersonIds),
            'karobari_member_id' => json_encode($karobariMemberData),
            'member_id' => json_encode($memberIds),
            'status' => 'complete',
            'created_by' => $this->userId,
        ]);
        
        return $this->successresponse(200, 'message', 'Attendance recorded successfully');
    }
    
    public function showAttendance(Request $request, $meetingId)
    {
        if ($this->rp['societymodule']['karobarimeeting']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $attendance = $this->karobari_meetings_attendanceModel::where('karobari_meeting_id', $meetingId)
            ->where('karobari_meetings_attendance.is_deleted', 0)
            ->leftJoin('family_person', 'family_person.id', '=', 'karobari_meetings_attendance.family_person_id')
            ->leftJoin('members', 'members.id', '=', 'karobari_meetings_attendance.member_id')
            ->select(
                'karobari_meetings_attendance.*',
                'family_person.full_name as family_person_name',
                'members.lifetime_member_no',
                'members.receipt_number'
            )
            ->get();
            
        if ($attendance->isEmpty()) {
            return $this->successresponse(404, 'message', 'No attendance records found');
        }
        
        // Parse karobari_member_id JSON for each record
        $attendance->each(function ($record) {
            if ($record->karobari_member_id) {
                $record->karobari_member_id = json_decode($record->karobari_member_id, true);
            }
        });
        
        return $this->successresponse(200, 'attendance', $attendance);
    }
    
    public function editAttendance(Request $request, $meetingId)
    {
        if ($this->rp['societymodule']['karobarimeeting']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $attendance = $this->karobari_meetings_attendanceModel::where('karobari_meeting_id', $meetingId)
            ->where('karobari_meetings_attendance.is_deleted', 0)
            ->leftJoin('family_person', 'family_person.id', '=', 'karobari_meetings_attendance.family_person_id')
            ->leftJoin('members', 'members.id', '=', 'karobari_meetings_attendance.member_id')
            ->select(
                'karobari_meetings_attendance.*',
                'family_person.full_name as family_person_name',
                'members.lifetime_member_no',
                'members.receipt_number'
            )
            ->get();
            
        if ($attendance->isEmpty()) {
            return $this->successresponse(404, 'message', 'No attendance records found');
        }
        
        // Parse karobari_member_id JSON for each record
        $attendance->each(function ($record) {
            if ($record->karobari_member_id) {
                $record->karobari_member_id = json_decode($record->karobari_member_id, true);
            }
        });
        
        return $this->successresponse(200, 'attendance', $attendance);
    }
    
    public function updateAttendance(Request $request, $meetingId)
    {
        if ($this->rp['societymodule']['karobarimeeting']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'karobari_member_ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors());
        }

        $karobariMemberIds = $request->karobari_member_ids;

        $familyPersonIds    = [];
        $memberIds          = [];
        $karobariMemberData = [];

        foreach ($karobariMemberIds as $memberId => $status) {
            $karobariMember = $this->karobarimemberModel::where('id', $memberId)
                ->where('is_deleted', 0)
                ->first();

            if ($karobariMember) {
                $familyPersonIds[]    = $karobariMember->familyPersonId;
                $memberIds[]          = $memberId;
                $karobariMemberData[] = [
                    'id'     => $memberId,
                    'status' => $status,
                ];
            }
        }

        // ✅ Check if attendance record already exists for this meeting
        $existing = $this->karobari_meetings_attendanceModel::where('karobari_meeting_id', $meetingId)
            ->where('is_deleted', 0)
            ->first();

        if ($existing) {
            // ✅ EXISTS → just UPDATE
            $existing->update([
                'family_person_id'   => json_encode($familyPersonIds),
                'karobari_member_id' => json_encode($karobariMemberData),
                'member_id'          => json_encode($memberIds),
                'status'             => 'complete',
                'updated_by'         => $this->userId,
            ]);
        } else {
            // ✅ NOT EXISTS → CREATE new
            $this->karobari_meetings_attendanceModel::create([
                'karobari_meeting_id' => $meetingId,
                'family_person_id'    => json_encode($familyPersonIds),
                'karobari_member_id'  => json_encode($karobariMemberData),
                'member_id'           => json_encode($memberIds),
                'status'              => 'complete',
                'created_by'          => $this->userId,
            ]);
        }

        return $this->successresponse(200, 'message', 'Attendance updated successfully');
    }
    
    public function deleteAttendance(Request $request, $meetingId)
    {
        if ($this->rp['societymodule']['karobarimeeting']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $attendance = $this->karobari_meetings_attendanceModel::where('karobari_meeting_id', $meetingId)
            ->where('karobari_meetings_attendance.is_deleted', 0)
            ->get();
            
        if ($attendance->isEmpty()) {
            return $this->successresponse(404, 'message', 'No attendance records found');
        }
        
        // Soft delete all attendance records for this meeting
        $this->karobari_meetings_attendanceModel::where('karobari_meeting_id', $meetingId)
            ->where('is_deleted', 0)
            ->update(['is_deleted' => 1, 'updated_by' => $this->userId]);
        
        return $this->successresponse(200, 'message', 'Attendance deleted successfully');
    }
    
    public function attendanceIndex(Request $request)
    {
        if ($this->rp['societymodule']['karobarimeeting']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $attendance = $this->karobari_meetings_attendanceModel::where('karobari_meetings_attendance.is_deleted', 0)
            ->leftJoin('karobari_meetings', 'karobari_meetings.id', '=', 'karobari_meetings_attendance.karobari_meeting_id')
            ->select(
                'karobari_meetings_attendance.*',
                'karobari_meetings.meeting_name',
                'karobari_meetings.meeting_date',
                'karobari_meetings.meeting_time_from',
                'karobari_meetings.meeting_time_to'
            );
            
        // Filter by meeting name if provided
        if ($request->has('meeting_name') && !empty($request->meeting_name)) {
            $attendance = $attendance->where('karobari_meetings.meeting_name', 'like', '%' . $request->meeting_name . '%');
        }
        
        // Filter by karobari_meeting_id if provided
        if ($request->has('karobari_meeting_id') && !empty($request->karobari_meeting_id)) {
            $attendance = $attendance->where('karobari_meetings_attendance.karobari_meeting_id', $request->karobari_meeting_id);
        }
        
        $attendance = $attendance->get();
        if ($attendance->isEmpty()) {
            return DataTables::of($attendance)
                ->with([
                    'status' => 404,
                    'message' => 'No attendance records found',
                ])
                ->make(true);
        }
        
        // Parse JSON fields and get family person names
        $attendance->each(function ($record) {
            // Parse family_person_ids array
            if ($record->family_person_id) {
                $familyPersonIds = json_decode($record->family_person_id, true) ?: [];
                $familyPersonNames = [];
                
                foreach ($familyPersonIds as $familyPersonId) {
                    $familyPerson = $this->familyPersonModel::where('id', $familyPersonId)
                        ->where('is_deleted', 0)
                        ->first();
                    if ($familyPerson) {
                        $familyPersonNames[] = $familyPerson->full_name;
                    }
                }
                $record->family_person_names = implode(', ', $familyPersonNames);
            }
            
            // Parse karobari_member_ids array and map status
            if ($record->karobari_member_id) {
                $karobariMembers = json_decode($record->karobari_member_id, true) ?: [];
                $attendanceDetails = [];
                
                foreach ($karobariMembers as $member) {
                    $familyPerson = $this->familyPersonModel::where('id', $member['id'])
                        ->where('is_deleted', 0)
                        ->first();
                        
                    if ($familyPerson) {
                        $attendanceDetails[] = [
                            'name' => $familyPerson->full_name,
                            'status' => $member['status'] ? 'present' : 'absent'
                        ];
                    }
                }
                $record->attendance_details = $attendanceDetails;
            }
            
            // Parse member_ids array
            if ($record->member_id) {
                $record->member_ids = json_decode($record->member_id, true) ?: [];
            }
        });
        
        return DataTables::of($attendance)->with([
            'status' => 200,
        ])->make(true);
    }

    public function loadkarobarimember(Request $request)
    {
        if ($this->rp['societymodule']['karobarimeeting']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        // Get all karobari members who are not deleted
        $karobariMembers = $this->karobarimemberModel::where('kraobari_member.is_deleted', 0)
            ->leftJoin('family_person', 'family_person.id', '=', 'kraobari_member.familyPersonId')
            ->leftJoin('members', 'members.id', '=', 'kraobari_member.memberId')
            ->leftJoin('business_sub_category as bsc', 'bsc.id', '=', 'family_person.business_subcategory')
            ->leftJoin('business_category as bc', 'bc.id', '=', 'family_person.business_category')
            ->leftJoin($this->masterdbname . '.country as company_country', 'company_country.id', '=', 'family_person.company_country_id')
            ->leftJoin($this->masterdbname . '.country as address_country', 'address_country.id', '=', 'family_person.address_country_id')
            ->leftJoin($this->masterdbname . '.state as company_state', 'company_state.id', '=', 'family_person.company_state_id')
            ->leftJoin($this->masterdbname . '.state as address_state', 'address_state.id', '=', 'family_person.address_state_id')
            ->leftJoin($this->masterdbname . '.city as company_city', 'company_city.id', '=', 'family_person.company_city_id')
            ->leftJoin($this->masterdbname . '.city as address_city', 'address_city.id', '=', 'family_person.address_city_id')
            ->select(
                'kraobari_member.*',
                'family_person.full_name',
                'family_person.email',
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
            ->get();
            
        if ($karobariMembers->isEmpty()) {
            return $this->successresponse(404, 'message', 'No karobari members found');
        }
        
        return $this->successresponse(200, 'karobari_members', $karobariMembers);
    }
    
    public function loadmettings(Request $request)
    {
        if ($this->rp['societymodule']['karobarimeeting']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        // Get all meetings for settings (dropdown or selection)
        $meetings = $this->karobarimeetingModel::where('karobari_meetings.is_deleted', 0)
            ->leftJoin($this->masterdbname . '.country as address_country', 'address_country.id', '=', 'karobari_meetings.country_id')
            ->leftJoin($this->masterdbname . '.state as address_state', 'address_state.id', '=', 'karobari_meetings.state_id')
            ->leftJoin($this->masterdbname . '.city as address_city', 'address_city.id', '=', 'karobari_meetings.city_id')
            ->select(
                'karobari_meetings.*',
                'address_country.country_name as address_country_name',
                'address_state.state_name as address_state_name',
                'address_city.city_name as address_city_name'
            )
            ->orderBy('karobari_meetings.meeting_date', 'desc')
            ->get();
            
        if ($meetings->isEmpty()) {
            return $this->successresponse(404, 'message', 'No meetings found');
        }
        
        return $this->successresponse(200, 'meetings', $meetings);
    }
}
