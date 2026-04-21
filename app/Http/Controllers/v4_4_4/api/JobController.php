<?php

namespace App\Http\Controllers\v4_4_4\api;
use App\Models\v4_4_4\Family;
use App\Models\v4_4_4\FamilyPerson;
use App\Models\v4_4_4\Member;
use App\Models\v4_4_4\Job;
use App\Models\v4_4_4\JobComment;
use App\Models\v4_4_4\JobLike;
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

class JobController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $businesscategoryModel, $businesssubcategoryModel, $data_formateModel,$user_permissionModel,$donorModel,$jobModel,$jobCommentModel,$jobLikeModel;
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
        $this->jobModel = $this->getmodel('Job');
        $this->jobCommentModel = $this->getmodel('JobComment');
        $this->jobLikeModel = $this->getmodel('JobLike');
    }
    public function index(Request $request)
    {
        if ($this->rp['societymodule']['job']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $jobs = $this->jobModel::leftJoin('family_person', 'jobs.familyPersonId', '=', 'family_person.id')
        ->where('jobs.is_deleted', 0)
        ->select(
            'jobs.*',
            'family_person.full_name as family_person_name'
        );
        
        // if ($this->rp['societymodule']['job']['alldata'] != 1) {
        //     $jobs = $jobs->where('jobs.created_by', $this->userId);
        // }
        $filters = [
            'filter_type' => 'jobs.type',
            'filter_city' => 'jobs.company_city_id',
            'filter_state' => 'jobs.company_state_id',
            'filter_pincode' => 'jobs.company_pincode',
            'filter_from_date' => 'jobs.created_at',
            'filter_to_date' => 'jobs.created_at',
        ];

        // Loop through the filters and apply them conditionally
        foreach ($filters as $requestKey => $column) {
            $value = $request->$requestKey;

            if (!empty($value)) {
                if ($requestKey === 'filter_from_date' || $requestKey === 'filter_to_date') {
                    // ← FIXED: exact key match instead of strpos()
                    $operator = $requestKey === 'filter_from_date' ? '>=' : '<=';
                    $jobs->whereDate($column, $operator, $value);
                } else {
                    $jobs->where($column, $value);
                }
            }
        }
        $jobs = $jobs->get();
        
        
        $jobIds = $jobs->pluck('id');
        
        // Get comments for these jobs
        $comments = $this->jobCommentModel::leftJoin('family_person', 'job_comment.family_person_id', '=', 'family_person.id')
            ->whereIn('job_comment.job_id', $jobIds)
            ->where('job_comment.is_deleted', 0)
            ->select(
                'job_comment.*',
                'family_person.full_name as family_person_name'
            )
            ->get()
            ->groupBy('job_id');
        
        // Get likes for these jobs
        $likes = $this->jobLikeModel::leftJoin('family_person', 'job_like.family_person_id', '=', 'family_person.id')
            ->whereIn('job_like.job_id', $jobIds)
            ->where('job_like.is_deleted', 0)
            ->select(
                'job_like.*',
                'family_person.full_name as family_person_name'
            )
            ->get()
            ->groupBy('job_id');
        
        // Attach comments and likes to jobs
        $jobs->each(function($job) use ($comments, $likes) {
            // Attach comments
            $jobComments = $comments->get($job->id, collect());
            $job->comments = $jobComments->map(function($comment) {
                return [
                    'job_comment' => $comment->comment,
                    'comment_id' => $comment->id,
                    'family_person_name' => $comment->family_person_name
                ];
            })->toArray();
            
            // Attach likes
            $jobLikes = $likes->get($job->id, collect());
            $job->likes = $jobLikes->map(function($like) {
                return [
                    'like_id' => $like->id,
                    'user_id' => $like->user_id,
                    'family_person_id' => $like->family_person_id,
                    'family_person_name' => $like->family_person_name
                ];
            })->toArray();
            
            // Add counts
            $job->total_likes = $jobLikes->count();
            $job->total_comments = $jobComments->count();
            
            // Check if current user has liked this job
            $job->is_like = $jobLikes->contains('user_id', $this->userId);
        });
        
         if ($jobs->isEmpty()) {
            return DataTables::of($jobs)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }
        return DataTables::of($jobs)
            ->with([
                'status' => 200,
            ])
            ->make(true);
    }
   public function store(Request $request)
    {
        if ($this->rp['societymodule']['job']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        // Optional: Validate request
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png',
            'type' => 'required|string', // hiring or looking
            'company_name' => 'nullable|string',
            'salary_from' => 'nullable|integer',
            'salary_to' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors()->first());
        }

        $job = $this->jobModel::create([
            'familyPersonId' => $request->familyPersonId,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'company_name' => $request->company_name,
            'company_house_no_building_name' => $request->company_house_no_building_name,
            'company_landmark' => $request->company_landmark,
            'company_area' => $request->company_area,
            'company_country_id' => $request->company_country_id,
            'company_state_id' => $request->company_state_id,
            'company_city_id' => $request->company_city_id,
            'company_pincode' => $request->company_pincode,
            'salary_from' => $request->salary_from,
            'salary_to' => $request->salary_to,
            'created_by' => $this->userId,
        ]);

        // Handle Image Upload
        if ($request->hasFile('image')) {
            $imagePath = "uploads/{$this->companyId}/job/{$job->id}/images";

            if (!file_exists(public_path($imagePath))) {
                mkdir(public_path($imagePath), 0777, true);
            }

            $file = $request->file('image');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path($imagePath), $name);

            $job->update([
                'image' => "$imagePath/$name",
            ]);
        }
        
        if(!$job){
            return $this->successresponse(500, 'message', 'Failed to create job');
        }
        return $this->successresponse(200, 'message', 'Job created successfully');
    }
    
    public function show($id)
    {
        if ($this->rp['societymodule']['job']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $job = $this->jobModel::leftJoin('family_person', 'jobs.familyPersonId', '=', 'family_person.id')
            ->where('jobs.id', $id)
            ->where('jobs.is_deleted', 0)
            ->select(
                'jobs.*',
                'family_person.full_name as family_person_name'
            )
            ->first();
        
        if(!$job){
            return $this->errorresponse(404, 'message', 'No such job found!');
        }
        
        // Get comments for this job
        $comments = $this->jobCommentModel::leftJoin('family_person', 'job_comment.family_person_id', '=', 'family_person.id')
            ->where('job_comment.job_id', $id)
            ->where('job_comment.is_deleted', 0)
            ->select(
                'job_comment.*',
                'family_person.full_name as family_person_name'
            )
            ->get();
        
        // Structure comments as array of objects
        $job->comments = $comments->map(function($comment) {
            return [
                'job_comment' => $comment->comment,
                'comment_id' => $comment->id,
                'family_person_name' => $comment->family_person_name
            ];
        })->toArray();
        
        // Get likes for this job
        $likes = $this->jobLikeModel::leftJoin('family_person', 'job_like.family_person_id', '=', 'family_person.id')
            ->where('job_like.job_id', $id)
            ->where('job_like.is_deleted', 0)
            ->select(
                'job_like.*',
                'family_person.full_name as family_person_name'
            )
            ->get();
        
        // Structure likes as array of objects
        $job->likes = $likes->map(function($like) {
            return [
                'like_id' => $like->id,
                'user_id' => $like->user_id,
                'family_person_id' => $like->family_person_id,
                'family_person_name' => $like->family_person_name
            ];
        })->toArray();
        
        // Add counts
        $job->total_likes = $likes->count();
        $job->total_comments = $comments->count();
        
        // Check if current user has liked this job
        $job->is_like = $likes->contains('user_id', $this->userId);
        
        return $this->successresponse(200, 'data', $job);
    }
    
    public function edit($id)
    {
        if ($this->rp['societymodule']['job']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $job = $this->jobModel::find($id);
        
        if(!$job){
            return $this->errorresponse(404, 'message', 'No such job found!');
        }
        
        return $this->successresponse(200, 'data', $job);
    }
    
    public function update(Request $request, $id)
    {
        if ($this->rp['societymodule']['job']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png',
            'type' => 'required|string',
            'company_name' => 'nullable|string',
            'salary_from' => 'nullable|integer',
            'salary_to' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors()->first());
        }
        
        $job = $this->jobModel::find($id);
        
        if(!$job){
            return $this->errorresponse(404, 'message', 'No such job found!');
        }
        
        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
            'familyPersonId' => $request->familyPersonId,
            'type' => $request->type,
            'company_name' => $request->company_name,
            'company_house_no_building_name' => $request->company_house_no_building_name,
            'company_landmark' => $request->company_landmark,
            'company_area' => $request->company_area,
            'company_country_id' => $request->company_country_id,
            'company_state_id' => $request->company_state_id,
            'company_city_id' => $request->company_city_id,
            'company_pincode' => $request->company_pincode,
            'salary_from' => $request->salary_from,
            'salary_to' => $request->salary_to,
            'updated_by' => $this->userId,
        ];
        
        // Handle Image Upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($job->image && file_exists(public_path($job->image))) {
                unlink(public_path($job->image));
            }
            
            $imagePath = "uploads/{$this->companyId}/job/{$job->id}/images";
            
            if (!file_exists(public_path($imagePath))) {
                mkdir(public_path($imagePath), 0777, true);
            }
            
            $file = $request->file('image');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path($imagePath), $name);
            
            $updateData['image'] = "$imagePath/$name";
        }
        
        $job->update($updateData);
        
        if(!$job){
            return $this->errorresponse(500, 'message', 'Job not updated!');
        }
        
        return $this->successresponse(200, 'message', 'Job updated successfully.');
    }
    
    public function destory($id)
    {
        if ($this->rp['societymodule']['job']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $job = $this->jobModel::find($id);
        
        if(!$job){
            return $this->errorresponse(404, 'message', 'No such job found!');
        }
        
        // Delete file if it exists
        if ($job->image && file_exists(public_path($job->image))) {
            unlink(public_path($job->image));
        }
        
        $job->update([
            'is_deleted' => 1,
            'updated_by' => $this->userId,
        ]);
        
        return $this->successresponse(200, 'message', 'Job deleted successfully.');
    }
    
    public function jobComment(Request $request)
    {
        if ($this->rp['societymodule']['jobcomment']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'job_id' => 'required|integer',
            'family_person_id' => 'nullable|integer',
            'comment' => 'nullable|string',
            'user_id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return $this->successresponse(422, 'errors', $validator->errors());
        }

        $jobComment = $this->jobCommentModel::create([
            'job_id' => $request->job_id,
            'family_person_id' => $request->familyPersonId,
            'comment' => $request->comment,
            'user_id' => $request->user_id ?? $this->userId,
            'created_by' => $this->userId,
        ]);
        if(!$jobComment){
            return $this->errorresponse(500, 'message', 'Job comment not created!');
        }
        return $this->successresponse(200, 'message', 'Job comment created successfully');
    }
    
    public function editJobComment($id)
    {
        if ($this->rp['societymodule']['jobcomment']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $jobComment = $this->jobCommentModel::find($id);
        
        if(!$jobComment){
            return $this->errorresponse(404, 'message', 'No such job comment found!');
        }
        
        return $this->successresponse(200, 'data', $jobComment);
    }
    
    public function updateJobComment(Request $request, $id)
    {
        if ($this->rp['societymodule']['jobcomment']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->successresponse(422, 'errors', $validator->errors());
        }

        $jobComment = $this->jobCommentModel::find($id);
        
        if(!$jobComment){
            return $this->errorresponse(404, 'message', 'No such job comment found!');
        }

        $jobComment->update([
            'comment' => $request->comment,
            'updated_by' => $this->userId,
        ]);
        
        if(!$jobComment){
            return $this->errorresponse(500, 'message', 'Job comment not updated!');
        }
        
        return $this->successresponse(200, 'message', 'Job comment updated successfully.');
    }
    
    public function deleteJobComment($id)
    {
        if ($this->rp['societymodule']['jobcomment']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $jobComment = $this->jobCommentModel::find($id);
        
        if(!$jobComment){
            return $this->errorresponse(404, 'message', 'No such job comment found!');
        }

        $jobComment->update([
            'is_deleted' => 1,
            'updated_by' => $this->userId,
        ]);
        
        return $this->successresponse(200, 'message', 'Job comment deleted successfully.');
    }
    
    public function joblike(Request $request)
    {
        if ($this->rp['societymodule']['joblike']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
       $exist  = $this->jobLikeModel::where('job_id', $request->job_id)->where('user_id', $this->userId)->where('family_person_id', $request->family_person_id)->first();
       if($exist){
            if($exist->is_deleted == 1){
                $exist->update([
                    'is_deleted' => 0,
                    'updated_by' => $this->userId,
                ]);
                return $this->successresponse(200, 'message', 'Job liked successfully.');
            }
            $exist->update([
                'is_deleted' => 1,
                'updated_by' => $this->userId,
            ]);
            return $this->successresponse(200, 'message', 'Job unliked successfully.');
       }
       $jobLike = $this->jobLikeModel::create([
        'job_id' => $request->job_id,
        'user_id' => $this->userId,
        'family_person_id' => $request->family_person_id,
        'created_by' => $this->userId,
        'updated_by' => $this->userId,
       ]);
       if(!$jobLike){
        return $this->errorresponse(500, 'message', 'Job not liked!');
       }
       return $this->successresponse(200, 'message', 'Job liked successfully.');
    }
    public function jobComments($id)
    {
        if ($this->rp['societymodule']['jobcomment']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $jobComment = $this->jobCommentModel::where('job_id', $id)->get();
        
        if(!$jobComment){
            return $this->errorresponse(404, 'message', 'No such job comment found!');
        }
        
        return $this->successresponse(200, 'data', $jobComment);
    }
}
