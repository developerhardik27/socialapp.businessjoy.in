<?php

namespace App\Http\Controllers\v4_4_4\api;
use App\Models\v4_4_4\Family;
use App\Models\v4_4_4\FamilyPerson;
use App\Models\v4_4_4\Member;
use App\Models\v4_4_4\Post;
use App\Models\v4_4_4\PostComment;
use App\Models\v4_4_4\PostLike;
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

class PostController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $businesscategoryModel, $businesssubcategoryModel, $data_formateModel,$user_permissionModel,$donorModel,$postModel,$postCommentModel,$postLikeModel;
    public function __construct(Request $request)
    {
        // dd($request->all());
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
        $this->postModel = $this->getmodel('Post');
        $this->postCommentModel = $this->getmodel('PostComment');
        $this->postLikeModel = $this->getmodel('PostLike');
    }
    public function index(Request $request)
    {
        if ($this->rp['societymodule']['post']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $posts = $this->postModel::leftJoin('family_person', 'posts.familyPersonId', '=', 'family_person.id')
        ->where('posts.is_deleted', 0)
        ->select(
            'posts.*',
            'family_person.full_name as family_person_name'
        );
        
        // if ($this->rp['societymodule']['post']['alldata'] != 1) {
        //     $posts = $posts->where('posts.created_by', $this->userId);
        // }
        $filters = [
            'filter_text' => 'posts.text',
            'filter_from_date' => 'posts.created_at',
            'filter_to_date' => 'posts.created_at',
        ];

        // Loop through the filters and apply them conditionally
        foreach ($filters as $requestKey => $column) {
            $value = $request->$requestKey;

            if (!empty($value)) {
                if ($requestKey === 'filter_from_date' || $requestKey === 'filter_to_date') {
                    // ← FIXED: exact key match instead of strpos()
                    $operator = $requestKey === 'filter_from_date' ? '>=' : '<=';
                    $posts->whereDate($column, $operator, $value);
                } else {
                    $posts->where($column, $value);
                }
            }
        }
        $posts = $posts->get();
        
        
        $postIds = $posts->pluck('id');
        
        // Get comments for these posts
        $comments = $this->postCommentModel::leftJoin('family_person', 'post_comment.family_person_id', '=', 'family_person.id')
            ->whereIn('post_comment.post_id', $postIds)
            ->where('post_comment.is_deleted', 0)
            ->select(
                'post_comment.*',
                'family_person.full_name as family_person_name'
            )
            ->get()
            ->groupBy('post_id');
        
        // Get likes for these posts
        $likes = $this->postLikeModel::leftJoin('family_person', 'post_like.family_person_id', '=', 'family_person.id')
            ->whereIn('post_like.post_id', $postIds)
            ->where('post_like.is_deleted', 0)
            ->select(
                'post_like.*',
                'family_person.full_name as family_person_name'
            )
            ->get()
            ->groupBy('post_id');
        
        // Attach comments and likes to posts
        $posts->each(function($post) use ($comments, $likes) {
            // Attach comments
            $postComments = $comments->get($post->id, collect());
            $post->comments = $postComments->map(function($comment) {
                return [
                    'post_comment' => $comment->comment,
                    'comment_id' => $comment->id,
                    'family_person_name' => $comment->family_person_name
                ];
            })->toArray();
            
            // Attach likes
            $postLikes = $likes->get($post->id, collect());
            $post->likes = $postLikes->map(function($like) {
                return [
                    'like_id' => $like->id,
                    'user_id' => $like->user_id,
                    'family_person_id' => $like->family_person_id,
                    'family_person_name' => $like->family_person_name
                ];
            })->toArray();
            
            // Add counts
            $post->total_likes = $postLikes->count();
            $post->total_comments = $postComments->count();
            
            // Check if current user has liked this post
            $post->is_like = $postLikes->contains('user_id', $this->userId);
        });
        
         if ($posts->isEmpty()) {
            return DataTables::of($posts)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }
        return DataTables::of($posts)
            ->with([
                'status' => 200,
            ])
            ->make(true);
    }
   public function store(Request $request)
    {
        if ($this->rp['societymodule']['post']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        // Optional: Validate request
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:255',
            'text' => 'nullable|string',
            'images' => 'nullable',
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png',
            'videos' => 'nullable',
            'videos.*' => 'nullable|file|mimes:mp4,mov,avi'
        ]);

        $post = $this->postModel::create([
            'title' => $request->title,
            'text' => $request->text,
            'familyPersonId' => $request->familyPersonId,
            'created_by' => $this->userId,
        ]);

        $imagePaths = [];
        $videoPaths = [];

        // Handle Image Upload
        if ($request->hasFile('images')) {
            $imagePath = "uploads/{$this->companyId}/post/{$post->id}/images";

            if (!file_exists(public_path($imagePath))) {
                mkdir(public_path($imagePath), 0777, true);
            }

            // This correctly handles multiple files sent as images[]
            $images = is_array($request->file('images')) 
                        ? $request->file('images') 
                        : [$request->file('images')];

            foreach ($images as $file) {
                if ($file->isValid()) { // Check file is valid before moving
                    $name = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $file->move(public_path($imagePath), $name);
                    $imagePaths[] = "$imagePath/$name";
                }
            }
        }

        // Handle Video Upload
        if ($request->hasFile('videos')) {
            $videoPath = "uploads/{$this->companyId}/post/{$post->id}/videos";

            if (!file_exists(public_path($videoPath))) {
                mkdir(public_path($videoPath), 0777, true);
            }

            // This correctly handles multiple files sent as videos[]
            $videos = is_array($request->file('videos')) 
                        ? $request->file('videos') 
                        : [$request->file('videos')];

            foreach ($videos as $file) {
                if ($file->isValid()) { // Check file is valid before moving
                    $name = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $file->move(public_path($videoPath), $name);
                    $videoPaths[] = "$videoPath/$name";
                }
            }
        }

        $post->update([
            'images' => json_encode($imagePaths),
            'videos' => json_encode($videoPaths),
        ]);
        if(!$post){
            return $this->successresponse(500, 'message', 'Failed to create post');
        }
        return $this->successresponse(200, 'message', 'Post created successfully');
    }
    
    public function show($id)
    {
        if ($this->rp['societymodule']['post']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $post = $this->postModel::leftJoin('family_person', 'posts.familyPersonId', '=', 'family_person.id')
            ->where('posts.id', $id)
            ->where('posts.is_deleted', 0)
            ->select(
                'posts.*',
                'family_person.full_name as family_person_name'
            )
            ->first();
        
        if(!$post){
            return $this->errorresponse(404, 'message', 'No such post found!');
        }
        
        // Get comments for this post
        $comments = $this->postCommentModel::leftJoin('family_person', 'post_comment.family_person_id', '=', 'family_person.id')
            ->where('post_comment.post_id', $id)
            ->where('post_comment.is_deleted', 0)
            ->select(
                'post_comment.*',
                'family_person.full_name as family_person_name'
            )
            ->get();
        
        // Structure comments as array of objects
        $post->comments = $comments->map(function($comment) {
            return [
                'post_comment' => $comment->comment,
                'comment_id' => $comment->id,
                'family_person_name' => $comment->family_person_name
            ];
        })->toArray();
        
        // Get likes for this post
        $likes = $this->postLikeModel::leftJoin('family_person', 'post_like.family_person_id', '=', 'family_person.id')
            ->where('post_like.post_id', $id)
            ->where('post_like.is_deleted', 0)
            ->select(
                'post_like.*',
                'family_person.full_name as family_person_name'
            )
            ->get();
        
        // Structure likes as array of objects
        $post->likes = $likes->map(function($like) {
            return [
                'like_id' => $like->id,
                'user_id' => $like->user_id,
                'family_person_id' => $like->family_person_id,
                'family_person_name' => $like->family_person_name
            ];
        })->toArray();
        
        // Add counts
        $post->total_likes = $likes->count();
        $post->total_comments = $comments->count();
        
        // Check if current user has liked this post
        $post->is_like = $likes->contains('user_id', $this->userId);
        
        return $this->successresponse(200, 'data', $post);
    }
    
    public function edit($id)
    {
        if ($this->rp['societymodule']['post']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $post = $this->postModel::find($id);
        
        if(!$post){
            return $this->errorresponse(404, 'message', 'No such post found!');
        }
        
        return $this->successresponse(200, 'data', $post);
    }
    
    public function update(Request $request, $id)
    {
        if ($this->rp['societymodule']['post']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:255',
            'text' => 'nullable|string',
            'images' => 'nullable',
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png',
            'videos' => 'nullable',
            'videos.*' => 'nullable|file|mimes:mp4,mov,avi'
        ]);
        
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors()->first());
        }
        
        $post = $this->postModel::find($id);
        
        if(!$post){
            return $this->errorresponse(404, 'message', 'No such post found!');
        }
        
        $updateData = [
            'title' => $request->title,
            'text' => $request->text,
            'familyPersonId' => $request->familyPersonId,
            'updated_by' => $this->userId,
        ];
        
        // Handle Image Upload
        if ($request->hasFile('images')) {
            // Delete old images if exists
            if ($post->images) {
                $oldImages = json_decode($post->images, true) ?: [];
                foreach ($oldImages as $oldImage) {
                    if (file_exists(public_path($oldImage))) {
                        unlink(public_path($oldImage));
                    }
                }
            }
            
            $imagePath = "uploads/{$this->companyId}/post/{$post->id}/images";
            
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
            
            $updateData['images'] = json_encode($newImagePaths);
        }
        
        // Handle Video Upload
        if ($request->hasFile('videos')) {
            // Delete old videos if exists
            if ($post->videos) {
                $oldVideos = json_decode($post->videos, true) ?: [];
                foreach ($oldVideos as $oldVideo) {
                    if (file_exists(public_path($oldVideo))) {
                        unlink(public_path($oldVideo));
                    }
                }
            }
            
            $videoPath = "uploads/{$this->companyId}/post/{$post->id}/videos";
            
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
            
            $updateData['videos'] = json_encode($newVideoPaths);
        }
        
        $post->update($updateData);
        
        if(!$post){
            return $this->errorresponse(500, 'message', 'Post not updated!');
        }
        
        return $this->successresponse(200, 'message', 'Post updated successfully.');
    }
    
    public function destory($id)
    {
        if ($this->rp['societymodule']['post']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $post = $this->postModel::find($id);
        
        if(!$post){
            return $this->errorresponse(404, 'message', 'No such post found!');
        }
        
        // Delete files if they exist
        // if ($post->images) {
        //     $images = json_decode($post->images, true) ?: [];
        //     foreach ($images as $image) {
        //         if (file_exists(public_path($image))) {
        //             unlink(public_path($image));
        //         }
        //     }
        // }
        
        // if ($post->videos) {
        //     $videos = json_decode($post->videos, true) ?: [];
        //     foreach ($videos as $video) {
        //         if (file_exists(public_path($video))) {
        //             unlink(public_path($video));
        //         }
        //     }
        // }
        
        $post->update([
            'is_deleted' => 1,
            'updated_by' => $this->userId,
        ]);
        
        return $this->successresponse(200, 'message', 'Post deleted successfully.');
    }
    public function postComment(Request $request)
    {
        if ($this->rp['societymodule']['postcomment']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer',
            'family_person_id' => 'nullable|integer',
            'comment' => 'nullable|string',
            'user_id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return $this->successresponse(422, 'errors', $validator->errors());
        }

        $postComment = $this->postCommentModel::create([
            'post_id' => $request->post_id,
            'family_person_id' => $request->familyPersonId,
            'comment' => $request->comment,
            'user_id' => $request->user_id ?? $this->userId,
            'created_by' => $this->userId,
        ]);
        if(!$postComment){
            return $this->errorresponse(500, 'message', 'Post comment not created!');
        }
        return $this->successresponse(200, 'message', 'Post comment created successfully');
    }
    
    public function updatePostComment(Request $request, $id)
    {
        if ($this->rp['societymodule']['postcomment']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->successresponse(422, 'errors', $validator->errors());
        }

        $postComment = $this->postCommentModel::find($id);
        
        if(!$postComment){
            return $this->errorresponse(404, 'message', 'No such post comment found!');
        }

        $postComment->update([
            'comment' => $request->comment,
            'updated_by' => $this->userId,
        ]);
        
        if(!$postComment){
            return $this->errorresponse(500, 'message', 'Post comment not updated!');
        }
        
        return $this->successresponse(200, 'message', 'Post comment updated successfully.');
    }
    
    public function editPostComment($id)
    {
        if ($this->rp['societymodule']['postcomment']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $postComment = $this->postCommentModel::find($id);
        
        if(!$postComment){
            return $this->errorresponse(404, 'message', 'No such post comment found!');
        }
        
        return $this->successresponse(200, 'data', $postComment);
    }
    
    public function deletePostComment($id)
    {
        if ($this->rp['societymodule']['postcomment']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $postComment = $this->postCommentModel::find($id);
        
        if(!$postComment){
            return $this->errorresponse(404, 'message', 'No such post comment found!');
        }

        $postComment->update([
            'is_deleted' => 1,
            'updated_by' => $this->userId,
        ]);
        
        return $this->successresponse(200, 'message', 'Post comment deleted successfully.');
    }
    public function postlike(Request $request)
    {
        if ($this->rp['societymodule']['postlike']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
       $exist  = $this->postLikeModel::where('post_id', $request->post_id)->where('user_id', $this->userId)->where('family_person_id', $request->family_person_id)->first();
       if($exist){
            if($exist->is_deleted == 1){
                $exist->update([
                    'is_deleted' => 0,
                    'updated_by' => $this->userId,
                ]);
                return $this->successresponse(200, 'message', 'Post liked successfully.');
            }
            $exist->update([
                'is_deleted' => 1,
                'updated_by' => $this->userId,
            ]);
            return $this->successresponse(200, 'message', 'Post unliked successfully.');
       }
       $postLike = $this->postLikeModel::create([
        'post_id' => $request->post_id,
        'user_id' => $this->userId,
        'family_person_id' => $request->family_person_id,
        'created_by' => $this->userId,
        'updated_by' => $this->userId,
       ]);
       if(!$postLike){
        return $this->errorresponse(500, 'message', 'Post not liked!');
       }
       return $this->successresponse(200, 'message', 'Post liked successfully.');
    }
    public function postComments($id)
    {
        if ($this->rp['societymodule']['postcomment']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $postComment = $this->postCommentModel::where('post_id', $id)->get();
        
        if(!$postComment){
            return $this->errorresponse(404, 'message', 'No such post comment found!');
        }
        
        return $this->successresponse(200, 'data', $postComment);
    }
}
