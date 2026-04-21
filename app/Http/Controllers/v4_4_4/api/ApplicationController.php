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

class ApplicationController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $familyrelationModel, $familyModel, $familyPersonModel, $businesscategoryModel, $businesssubcategoryModel, $data_formateModel,$user_permissionModel,$donorModel,$eventModel,$eventCommentModel,$eventLikeModel,$applicationModel;
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
    }
    public function index(Request $request)
    {
        if ($this->rp['societymodule']['application']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $applications = $this->applicationModel::where('is_deleted', 0);
        if ($this->rp['societymodule']['application']['alldata'] != 1) {
            $applications = $applications->where('created_by', $this->userId);
        }
        $filters = [
            'filter_type' => 'applications.type',
            'filter_status' => 'applications.status',
            'filter_from_date' => 'applications.created_at',
            'filter_to_date' => 'applications.created_at',
        ];

        // Loop through the filters and apply them conditionally
        foreach ($filters as $requestKey => $column) {
            $value = $request->$requestKey;

            if (!empty($value)) {
                if ($requestKey === 'filter_from_date' || $requestKey === 'filter_to_date') {
                    // ← FIXED: exact key match instead of strpos()
                    $operator = $requestKey === 'filter_from_date' ? '>=' : '<=';
                    $applications->whereDate($column, $operator, $value);
                } else {
                    $applications->where($column, $value);
                }
            }
        }
        $applications = $applications->get();
        if ($applications->isEmpty()) {
            return DataTables::of($applications)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }
        
        return DataTables::of($applications)->with([
            'status' => 200,
        ])->make(true);
    }
    public function store(Request $request)
    {
        if ($this->rp['societymodule']['application']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $validator = Validator::make($request->all(), [
            'details' => 'required|string|max:255',
            'amount' => 'required',
            'type' => 'required|string|max:20',
            'documents' => 'nullable',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png'
        ]);
        
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors());
        }
        $application = $this->applicationModel::create([
            'familyPersonId' => $request->familyPersonId,
            'details' => $request->details,
            'amount' => $request->amount,
            'type' => $request->type,
            'status' =>'pending',
            'created_by' => $this->userId,
        ]);
         $documentsPaths = [];
       

        // Handle Image Upload
        if ($request->hasFile('documents')) {
            $imagePath = "uploads/{$this->companyId}/application/{$application->id}/documents";

            if (!file_exists(public_path($imagePath))) {
                mkdir(public_path($imagePath), 0777, true);
            }

            // ✅ This correctly handles multiple files sent as documents[]
            $images = is_array($request->file('documents')) 
                        ? $request->file('documents') 
                        : [$request->file('documents')];

            foreach ($images as $file) {
                if ($file->isValid()) { // ✅ Check file is valid before moving
                    $name = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $file->move(public_path($imagePath), $name);
                    $documentsPaths[] = "$imagePath/$name";
                }
            }
        }
        $application->update([
            'documents' => json_encode($documentsPaths),
        ]);
        if(!$application){
            return $this->successresponse(500, 'message', 'Application not created');
        }   
        return $this->successresponse(200, 'message', 'Application created successfully');
    }
    public function show(Request $request, $id)
    {
        if ($this->rp['societymodule']['application']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $application = $this->applicationModel
            ::leftJoin('family_person', 'applications.familyPersonId', '=', 'family_person.id')

            ->leftJoin('business_sub_category as bsc', 'bsc.id', '=', 'family_person.business_subcategory')
            ->leftJoin('business_category as bc', 'bc.id', '=', 'family_person.business_category')

            ->leftJoin($this->masterdbname.'.country as company_country', 'company_country.id', '=', 'family_person.company_country_id')
            ->leftJoin($this->masterdbname.'.country as address_country', 'address_country.id', '=', 'family_person.address_country_id')

            ->leftJoin($this->masterdbname.'.state as company_state', 'company_state.id', '=', 'family_person.company_state_id')
            ->leftJoin($this->masterdbname.'.state as address_state', 'address_state.id', '=', 'family_person.address_state_id')

            ->leftJoin($this->masterdbname.'.city as company_city', 'company_city.id', '=', 'family_person.company_city_id')
            ->leftJoin($this->masterdbname.'.city as address_city', 'address_city.id', '=', 'family_person.address_city_id')

            ->select(
                'applications.*',
                'family_person.full_name as familyPersonName',
                'bc.name as businessCategory',
                'bsc.name as businessSubCategory',
                'company_country.country_name as companyCountry',
                'address_country.country_name as addressCountry',
                'company_state.state_name as companyState',
                'address_state.state_name as addressState',
                'company_city.city_name as companyCity',
                'address_city.city_name as addressCity'
            )
            ->where('applications.id', $id)
            ->first();

        if (!$application) {
            return $this->successresponse(404, 'message', 'Application not found');
        }

        return $this->successresponse(200, 'application', $application);
    }
    public function edit(Request $request, $id)
    {
        if ($this->rp['societymodule']['application']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $application = $this->applicationModel::find($id);
        if(!$application){
            return $this->successresponse(404, 'message', 'Application not found');
        }
        return $this->successresponse(200, 'application', $application);
    }
    public function update(Request $request, $id)
    {
        if ($this->rp['societymodule']['application']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        
        $validator = Validator::make($request->all(), [
            'details' => 'required|string|max:255',
            'amount' => 'required',
            'type' => 'required|string|max:20',
            'documents' => 'nullable',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);
        
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors());
        }
        
        $application = $this->applicationModel::find($id);
        if(!$application){
            return $this->successresponse(404, 'message', 'Application not found');
        }
        
        $updateData = [
            'details' => $request->details,
            'amount' => $request->amount,
            'type' => $request->type,
            'status' => $request->status,
            'updated_by' => $this->userId
        ];
        
        // Handle Document Upload
        if ($request->hasFile('documents')) {
            // Delete old documents if exists
            if ($application->documents) {
                $oldDocuments = json_decode($application->documents, true) ?: [];
                foreach ($oldDocuments as $oldDocument) {
                    if (file_exists(public_path($oldDocument))) {
                        unlink(public_path($oldDocument));
                    }
                }
            }
            
            $documentPath = "uploads/{$this->companyId}/application/{$application->id}/documents";
            
            if (!file_exists(public_path($documentPath))) {
                mkdir(public_path($documentPath), 0777, true);
            }
            
            // This correctly handles multiple files sent as documents[]
            $documents = is_array($request->file('documents')) 
                        ? $request->file('documents') 
                        : [$request->file('documents')];
            
            $newDocumentPaths = [];
            foreach ($documents as $file) {
                if ($file->isValid()) { // Check file is valid before moving
                    $name = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $file->move(public_path($documentPath), $name);
                    $newDocumentPaths[] = "$documentPath/$name";
                }
            }
            
            $updateData['documents'] = json_encode($newDocumentPaths);
        }
        
        $application->update($updateData);
        
        return $this->successresponse(200, 'message', 'Application updated successfully', $application);
    }
    public function destroy(Request $request, $id)
    {
        if ($this->rp['societymodule']['application']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $application = $this->applicationModel::find($id);
        if(!$application){
            return $this->successresponse(404, 'message', 'Application not found');
        }
        
        // Delete documents if they exist
        // if ($application->documents) {
        //     $documents = json_decode($application->documents, true) ?: [];
        //     foreach ($documents as $document) {
        //         if (file_exists(public_path($document))) {
        //             unlink(public_path($document));
        //         }
        //     }
        // }
        
        $application->update([
            'is_deleted' => 1,
            'updated_by' =>$this->userId
        ]);
        return $this->successresponse(200, 'message', 'Application deleted successfully');
    }
}
