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

class EventController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $businesscategoryModel, $businesssubcategoryModel, $data_formateModel,$user_permissionModel,$donorModel,$eventModel,$eventCommentModel,$eventLikeModel;
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
    }
    public function index(Request $request)
    {
        if ($this->rp['societymodule']['event']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $events = $this->eventModel::leftJoin('family_person', 'events.familyPersonId', '=', 'family_person.id')
        ->where('events.is_deleted', 0)
        ->select(
            'events.*',
            'family_person.full_name as family_person_name'
        );
        
        // if ($this->rp['societymodule']['event']['alldata'] != 1) {
        //     $events = $events->where('events.created_by', $this->userId);
        // }
        $filters = [
            'filter_type' => 'events.event_type',
            'filter_status' => 'events.event_status',
            'filter_city' => 'events.city_id',
            'filter_state' => 'events.state_id',
            'filter_pincode' => 'events.pincode',
            'filter_from_date' => 'events.event_date_from',
            'filter_to_date' => 'events.event_date_to',
        ];

        // Loop through the filters and apply them conditionally
        foreach ($filters as $requestKey => $column) {
            $value = $request->$requestKey;

            if (!empty($value)) {
                if ($requestKey === 'filter_from_date' || $requestKey === 'filter_to_date') {
                    // ← FIXED: exact key match instead of strpos()
                    $operator = $requestKey === 'filter_from_date' ? '>=' : '<=';
                    $events->whereDate($column, $operator, $value);
                } else {
                    $events->where($column, $value);
                }
            }
        }
        $events = $events->get();
        
        
        $eventIds = $events->pluck('id');
        
        // Get comments for these events
        $comments = $this->eventCommentModel::leftJoin('family_person', 'events_comment.family_person_id', '=', 'family_person.id')
            ->whereIn('events_comment.event_id', $eventIds)
            ->where('events_comment.is_deleted', 0)
            ->select(
                'events_comment.*',
                'family_person.full_name as family_person_name'
            )
            ->get()
            ->groupBy('event_id');
        
        // Get likes for these events
        $likes = $this->eventLikeModel::leftJoin('family_person', 'events_like.family_person_id', '=', 'family_person.id')
            ->whereIn('events_like.event_id', $eventIds)
            ->where('events_like.is_deleted', 0)
            ->select(
                'events_like.*',
                'family_person.full_name as family_person_name'
            )
            ->get()
            ->groupBy('event_id');
        
        // Attach comments and likes to events
        $events->each(function($event) use ($comments, $likes) {
            // Attach comments
            $eventComments = $comments->get($event->id, collect());
            $event->comments = $eventComments->map(function($comment) {
                return [
                    'event_comment' => $comment->comment,
                    'comment_id' => $comment->id,
                    'family_person_name' => $comment->family_person_name
                ];
            })->toArray();
            
            // Attach likes
            $eventLikes = $likes->get($event->id, collect());
            $event->likes = $eventLikes->map(function($like) {
                return [
                    'like_id' => $like->id,
                    'user_id' => $like->user_id,
                    'family_person_id' => $like->family_person_id,
                    'family_person_name' => $like->family_person_name
                ];
            })->toArray();
            
            // Add counts
            $event->total_likes = $eventLikes->count();
            $event->total_comments = $eventComments->count();
            
            // Check if current user has liked this event
            $event->is_like = $eventLikes->contains('user_id', $this->userId);
        });
        
         if ($events->isEmpty()) {
            return DataTables::of($events)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }
        return DataTables::of($events)
            ->with([
                'status' => 200,
            ])
            ->make(true);
    }
    public function store(Request $request)
    {
        if ($this->rp['societymodule']['event']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'images'           => 'nullable',
            'images.*'         => 'nullable|file|mimes:jpg,jpeg,png',
            'videos'           => 'nullable',
            'videos.*'         => 'nullable|file|mimes:mp4,mov,avi',
            'event_date_from'  => 'nullable|date',
            'event_date_to'    => 'nullable|date',
            'event_time_from'  => 'nullable|date_format:H:i',
            'event_time_to'    => 'nullable|date_format:H:i',
            'event_status'     => 'nullable|string',
            'event_type'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors()->first());
        }

        $event = $this->eventModel::create([
            'familyPersonId'  => $request->familyPersonId,
            'title'           => $request->title,
            'description'     => $request->description,
            'event_date_from' => $request->event_date_from,
            'event_date_to'   => $request->event_date_to,
            'event_time_from' => $request->event_time_from,
            'event_time_to'   => $request->event_time_to,
            'event_status'    => $request->event_status,
            'event_type'      => $request->event_type,
            'building_name'   => $request->building_name,
            'landmark'        => $request->landmark,
            'area'            => $request->area,
            'country_id'      => $request->country_id,
            'state_id'        => $request->state_id,
            'city_id'         => $request->city_id,
            'pincode'         => $request->pincode,
            'created_by'      => $this->userId,
            'updated_by'      => $this->userId,
        ]);

        if (!$event) {
            return $this->errorresponse(500, 'message', 'Failed to create event');
        }

        $imagePaths = [];
        $videoPaths = [];

    // Handle Image Upload
        if ($request->hasFile('images')) {
            $imagePath = "uploads/{$this->companyId}/event/{$event->id}/images";

            if (!file_exists(public_path($imagePath))) {
                mkdir(public_path($imagePath), 0777, true);
            }

            // ✅ This correctly handles multiple files sent as images[]
            $images = is_array($request->file('images')) 
                        ? $request->file('images') 
                        : [$request->file('images')];

            foreach ($images as $file) {
                if ($file->isValid()) { // ✅ Check file is valid before moving
                    $name = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $file->move(public_path($imagePath), $name);
                    $imagePaths[] = "$imagePath/$name";
                }
            }
        }

        // Handle Video Upload
        if ($request->hasFile('videos')) {
            $videoPath = "uploads/{$this->companyId}/event/{$event->id}/videos";

            if (!file_exists(public_path($videoPath))) {
                mkdir(public_path($videoPath), 0777, true);
            }

            // ✅ This correctly handles multiple files sent as videos[]
            $videos = is_array($request->file('videos')) 
                        ? $request->file('videos') 
                        : [$request->file('videos')];

            foreach ($videos as $file) {
                if ($file->isValid()) { // ✅ Check file is valid before moving
                    $name = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $file->move(public_path($videoPath), $name);
                    $videoPaths[] = "$videoPath/$name";
                }
            }
        }

        $event->update([
            'image' => json_encode($imagePaths),
            'video' => json_encode($videoPaths),
        ]);

        return $this->successresponse(200, 'message', 'Event created successfully');
    }
    
    public function show($id)
    {
        if ($this->rp['societymodule']['event']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $event = $this->eventModel::leftJoin('family_person', 'events.familyPersonId', '=', 'family_person.id')
            ->where('events.id', $id)
            ->where('events.is_deleted', 0)
            ->select(
                'events.*',
                'family_person.full_name as family_person_name'
            )
            ->first();
        
        if(!$event){
            return $this->errorresponse(404, 'message', 'No such event found!');
        }
        
        // Get comments for this event
        $comments = $this->eventCommentModel::leftJoin('family_person', 'events_comment.family_person_id', '=', 'family_person.id')
            ->where('events_comment.event_id', $id)
            ->where('events_comment.is_deleted', 0)
            ->select(
                'events_comment.*',
                'family_person.full_name as family_person_name'
            )
            ->get();
        
        // Structure comments as array of objects
        $event->comments = $comments->map(function($comment) {
            return [
                'event_comment' => $comment->comment,
                'comment_id' => $comment->id,
                'family_person_name' => $comment->family_person_name
            ];
        })->toArray();
        
        // Get likes for this event
        $likes = $this->eventLikeModel::leftJoin('family_person', 'events_like.family_person_id', '=', 'family_person.id')
            ->where('events_like.event_id', $id)
            ->where('events_like.is_deleted', 0)
            ->select(
                'events_like.*',
                'family_person.full_name as family_person_name'
            )
            ->get();
        
        // Structure likes as array of objects
        $event->likes = $likes->map(function($like) {
            return [
                'like_id' => $like->id,
                'user_id' => $like->user_id,
                'family_person_id' => $like->family_person_id,
                'family_person_name' => $like->family_person_name
            ];
        })->toArray();
        
        // Add counts
        $event->total_likes = $likes->count();
        $event->total_comments = $comments->count();
        
        // Check if current user has liked this event
        $event->is_like = $likes->contains('user_id', $this->userId);
        
        return $this->successresponse(200, 'data', $event);
    }
    
    public function edit($id)
    {
        if ($this->rp['societymodule']['event']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $event = $this->eventModel::find($id);
        
        if(!$event){
            return $this->errorresponse(404, 'message', 'No such event found!');
        }
        
        return $this->successresponse(200, 'data', $event);
    }
    
    public function update(Request $request, $id)
    {
        if ($this->rp['societymodule']['event']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'images' => 'nullable',
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png',
            'videos' => 'nullable',
            'videos.*' => 'nullable|file|mimes:mp4,mov,avi',
            'event_date_from' => 'nullable|date',
            'event_date_to' => 'nullable|date',
            'event_time_from' => 'nullable|date_format:H:i',
            'event_time_to' => 'nullable|date_format:H:i',
            'event_status' => 'nullable|string',
            'event_type' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors()->first());
        }
        
        $event = $this->eventModel::find($id);
        
        if(!$event){
            return $this->errorresponse(404, 'message', 'No such event found!');
        }
        
        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
            'familyPersonId' => $request->familyPersonId,
            'event_date_from' => $request->event_date_from,
            'event_date_to' => $request->event_date_to,
            'event_time_from' => $request->event_time_from,
            'event_time_to' => $request->event_time_to,
            'event_status' => $request->event_status,
            'event_type' => $request->event_type,
            'building_name' => $request->building_name,
            'landmark' => $request->landmark,
            'area' => $request->area,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'pincode' => $request->pincode,
            'updated_by' => $this->userId,
        ];
        
        // Handle Image Upload
        if ($request->hasFile('images')) {
            // Delete old images if exists
            if ($event->image) {
                $oldImages = json_decode($event->image, true) ?: [];
                foreach ($oldImages as $oldImage) {
                    if (file_exists(public_path($oldImage))) {
                        unlink(public_path($oldImage));
                    }
                }
            }
            
            $imagePath = "uploads/{$this->companyId}/event/{$event->id}/images";
            
            if (!file_exists(public_path($imagePath))) {
                mkdir(public_path($imagePath), 0777, true);
            }
            
            // This correctly handles multiple files sent as images[]
            $images = is_array($request->file('images')) 
                        ? $request->file('images') 
                        : [$request->file('images')];
            
            $newImagePaths = [];
            foreach ($images as $file) {
                if ($file->isValid()) { // Check file is valid before moving
                    $name = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $file->move(public_path($imagePath), $name);
                    $newImagePaths[] = "$imagePath/$name";
                }
            }
            
            $updateData['image'] = json_encode($newImagePaths);
        }
        
        // Handle Video Upload
        if ($request->hasFile('videos')) {
            // Delete old videos if exists
            if ($event->video) {
                $oldVideos = json_decode($event->video, true) ?: [];
                foreach ($oldVideos as $oldVideo) {
                    if (file_exists(public_path($oldVideo))) {
                        unlink(public_path($oldVideo));
                    }
                }
            }
            
            $videoPath = "uploads/{$this->companyId}/event/{$event->id}/videos";
            
            if (!file_exists(public_path($videoPath))) {
                mkdir(public_path($videoPath), 0777, true);
            }
            
            // This correctly handles multiple files sent as videos[]
            $videos = is_array($request->file('videos')) 
                        ? $request->file('videos') 
                        : [$request->file('videos')];
            
            $newVideoPaths = [];
            foreach ($videos as $file) {
                if ($file->isValid()) { // Check file is valid before moving
                    $name = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $file->move(public_path($videoPath), $name);
                    $newVideoPaths[] = "$videoPath/$name";
                }
            }
            
            $updateData['video'] = json_encode($newVideoPaths);
        }
        
        $event->update($updateData);
        
        if(!$event){
            return $this->errorresponse(500, 'message', 'Event not updated!');
        }
        
        return $this->successresponse(200, 'message', 'Event updated successfully.');
    }
    
    public function destory($id)
    {
        if ($this->rp['societymodule']['event']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $event = $this->eventModel::find($id);
        
        if(!$event){
            return $this->errorresponse(404, 'message', 'No such event found!');
        }
        
        // Delete files if they exist
        // if ($event->image) {
        //     $images = json_decode($event->image, true) ?: [];
        //     foreach ($images as $image) {
        //         if (file_exists(public_path($image))) {
        //             unlink(public_path($image));
        //         }
        //     }
        // }
        
        // if ($event->video) {
        //     $videos = json_decode($event->video, true) ?: [];
        //     foreach ($videos as $video) {
        //         if (file_exists(public_path($video))) {
        //             unlink(public_path($video));
        //         }
        //     }
        // }
        
        $event->update([
            'is_deleted' => 1,
            'updated_by' => $this->userId,
        ]);
        
        return $this->successresponse(200, 'message', 'Event deleted successfully.');
    }
    
    public function eventComment(Request $request)
    {
        if ($this->rp['societymodule']['eventcomment']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'event_id' => 'required|integer',
            'family_person_id' => 'nullable|integer',
            'comment' => 'nullable|string',
            'user_id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return $this->successresponse(422, 'errors', $validator->errors());
        }

        $eventComment = $this->eventCommentModel::create([
            'event_id' => $request->event_id,
            'family_person_id' => $request->familyPersonId,
            'comment' => $request->comment,
            'user_id' => $request->user_id ?? $this->userId,
            'created_by' => $this->userId,
        ]);
        if(!$eventComment){
            return $this->errorresponse(500, 'message', 'Event comment not created!');
        }
        return $this->successresponse(200, 'message', 'Event comment created successfully');
    }
    
    public function editEventComment($id)
    {
        if ($this->rp['societymodule']['eventcomment']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $eventComment = $this->eventCommentModel::find($id);
        
        if(!$eventComment){
            return $this->errorresponse(404, 'message', 'No such event comment found!');
        }
        
        return $this->successresponse(200, 'data', $eventComment);
    }
    
    public function updateEventComment(Request $request, $id)
    {
        if ($this->rp['societymodule']['eventcomment']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->successresponse(422, 'errors', $validator->errors());
        }

        $eventComment = $this->eventCommentModel::find($id);
        
        if(!$eventComment){
            return $this->errorresponse(404, 'message', 'No such event comment found!');
        }

        $eventComment->update([
            'comment' => $request->comment,
            'updated_by' => $this->userId,
        ]);
        
        if(!$eventComment){
            return $this->errorresponse(500, 'message', 'Event comment not updated!');
        }
        
        return $this->successresponse(200, 'message', 'Event comment updated successfully.');
    }
    
    public function deleteEventComment($id)
    {
        if ($this->rp['societymodule']['eventcomment']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $eventComment = $this->eventCommentModel::find($id);
        
        if(!$eventComment){
            return $this->errorresponse(404, 'message', 'No such event comment found!');
        }

        $eventComment->update([
            'is_deleted' => 1,
            'updated_by' => $this->userId,
        ]);
        
        return $this->successresponse(200, 'message', 'Event comment deleted successfully.');
    }
    
    public function eventComments($id)
    {
        if ($this->rp['societymodule']['eventcomment']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $eventComment = $this->eventCommentModel::where('event_id', $id)->get();
        
        if(!$eventComment){
            return $this->errorresponse(404, 'message', 'No such event comment found!');
        }
        
        return $this->successresponse(200, 'data', $eventComment);
    }
    
    public function eventlike(Request $request)
    {
        if ($this->rp['societymodule']['eventlike']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
       $exist  = $this->eventLikeModel::where('event_id', $request->event_id)->where('user_id', $this->userId)->where('family_person_id', $request->family_person_id)->first();
       if($exist){
            if($exist->is_deleted == 1){
                $exist->update([
                    'is_deleted' => 0,
                    'updated_by' => $this->userId,
                ]);
                return $this->successresponse(200, 'message', 'Event liked successfully.');
            }
            $exist->update([
                'is_deleted' => 1,
                'updated_by' => $this->userId,
            ]);
            return $this->successresponse(200, 'message', 'Event unliked successfully.');
       }
       $eventLike = $this->eventLikeModel::create([
        'event_id' => $request->event_id,
        'user_id' => $this->userId,
        'family_person_id' => $request->family_person_id,
        'created_by' => $this->userId,
        'updated_by' => $this->userId,
       ]);
       if(!$eventLike){
        return $this->errorresponse(500, 'message', 'Event not liked!');
       }
       return $this->successresponse(200, 'message', 'Event liked successfully.');
    }
}
