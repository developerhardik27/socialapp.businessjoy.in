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

class PolicieController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $businesscategoryModel, $businesssubcategoryModel, $data_formateModel,$user_permissionModel,$donorModel,$eventModel,$eventCommentModel,$eventLikeModel,$applicationModel,$policieModel;
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
    }
    public function index(Request $request)
    {
        if ($this->rp['societymodule']['policy']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $policies = $this->policieModel::where('is_deleted', 0);
        if ($this->rp['societymodule']['policy']['alldata'] != 1) {
            $policies = $policies->where('created_by', $this->userId);
        }
        $filters = [
            'filter_from_date' => 'policies.created_at',
            'filter_to_date' => 'policies.created_at',
        ];

        // Loop through the filters and apply them conditionally
        foreach ($filters as $requestKey => $column) {
            $value = $request->$requestKey;

            if (!empty($value)) {
                if ($requestKey === 'filter_from_date' || $requestKey === 'filter_to_date') {
                    // ← FIXED: exact key match instead of strpos()
                    $operator = $requestKey === 'filter_from_date' ? '>=' : '<=';
                    $policies->whereDate($column, $operator, $value);
                } else {
                    $policies->where($column, $value);
                }
            }
        }
        $policies = $policies->get();
        if ($policies->isEmpty()) {
            return DataTables::of($policies)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }
        
        return DataTables::of($policies)->with([
            'status' => 200,
        ])->make(true);
    }
    public function store(Request $request)
    {
        if ($this->rp['societymodule']['policy']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $validator = Validator::make($request->all(), [
            'policy_number' => 'required|string|max:255',
            'text' => 'required|string',
            'date' => 'required|date',
            'pdf_file' => 'nullable|file|mimes:pdf',
        ]);
        
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors());
        }
        $policy = $this->policieModel::create([
            'policy_number' => $request->policy_number,
            'text' => $request->text,
            'date' => $request->date,
            'created_by' => $this->userId,
        ]);
        
        $pdfPaths = [];

        // Handle PDF Upload
        if ($request->hasFile('pdf_file')) {
            $pdfPath = "uploads/{$this->companyId}/policy/{$policy->id}/pdf";

            if (!file_exists(public_path($pdfPath))) {
                mkdir(public_path($pdfPath), 0777, true);
            }

            // This correctly handles multiple files sent as pdf_file[]
            $pdfFiles = is_array($request->file('pdf_file')) 
                        ? $request->file('pdf_file') 
                        : [$request->file('pdf_file')];

            foreach ($pdfFiles as $file) {
                if ($file->isValid()) { // Check file is valid before moving
                    $name = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $file->move(public_path($pdfPath), $name);
                    $pdfPaths[] = "$pdfPath/$name";
                }
            }
        }
        
        $policy->update([
            'pdf_file' => json_encode($pdfPaths),
        ]);
        
        if(!$policy){
            return $this->successresponse(500, 'message', 'Policy not created');
        }   
        return $this->successresponse(200, 'message', 'Policy created successfully');
    }
    
    public function show(Request $request, $id)
    {
        if ($this->rp['societymodule']['policy']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $policy = $this->policieModel::where('id', $id)->where('is_deleted', 0)->first();

        if (!$policy) {
            return $this->successresponse(404, 'message', 'Policy not found');
        }

        return $this->successresponse(200, 'policy', $policy);
    }
    
    public function edit(Request $request, $id)
    {
        if ($this->rp['societymodule']['policy']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $policy = $this->policieModel::find($id);
        if(!$policy){
            return $this->successresponse(404, 'message', 'Policy not found');
        }
        return $this->successresponse(200, 'policy', $policy);
    }
    
    public function update(Request $request, $id)
    {
        if ($this->rp['societymodule']['policy']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $validator = Validator::make($request->all(), [
            'policy_number' => 'required|string|max:255',
            'text' => 'required|string',
            'date' => 'required|date',
            'pdf_file' => 'nullable',
            'pdf_file.*' => 'nullable|file|mimes:pdf',
        ]);
        
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors());
        }
        
        $policy = $this->policieModel::find($id);
        if(!$policy){
            return $this->successresponse(404, 'message', 'Policy not found');
        }
        
        $updateData = [
            'policy_number' => $request->policy_number,
            'text' => $request->text,
            'date' => $request->date,
            'updated_by' => $this->userId
        ];
        
        // Handle PDF Upload
        if ($request->hasFile('pdf_file')) {
            // Delete old PDFs if exists
            if ($policy->pdf_file) {
                $oldPdfs = json_decode($policy->pdf_file, true) ?: [];
                foreach ($oldPdfs as $oldPdf) {
                    if (file_exists(public_path($oldPdf))) {
                        unlink(public_path($oldPdf));
                    }
                }
            }
            
            $pdfPath = "uploads/{$this->companyId}/policy/{$policy->id}/pdf";
            
            if (!file_exists(public_path($pdfPath))) {
                mkdir(public_path($pdfPath), 0777, true);
            }
            
            // This correctly handles multiple files sent as pdf_file[]
            $pdfFiles = is_array($request->file('pdf_file')) 
                        ? $request->file('pdf_file') 
                        : [$request->file('pdf_file')];
            
            $newPdfPaths = [];
            foreach ($pdfFiles as $file) {
                if ($file->isValid()) { // Check file is valid before moving
                    $name = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $file->move(public_path($pdfPath), $name);
                    $newPdfPaths[] = "$pdfPath/$name";
                }
            }
            
            $updateData['pdf_file'] = json_encode($newPdfPaths);
        }
        
        $policy->update($updateData);
        
        return $this->successresponse(200, 'message', 'Policy updated successfully', $policy);
    }
    
    public function destroy(Request $request, $id)
    {
        if ($this->rp['societymodule']['policy']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $policy = $this->policieModel::find($id);
        if(!$policy){
            return $this->successresponse(404, 'message', 'Policy not found');
        }
        
        // Delete PDFs if they exist
        // if ($policy->pdf_file) {
        //     $pdfs = json_decode($policy->pdf_file, true) ?: [];
        //     foreach ($pdfs as $pdf) {
        //         if (file_exists(public_path($pdf))) {
        //             unlink(public_path($pdf));
        //         }
        //     }
        // }
        
        $policy->update([
            'is_deleted' => 1,
            'updated_by' =>$this->userId
        ]);
        return $this->successresponse(200, 'message', 'Policy deleted successfully');
    }
}
