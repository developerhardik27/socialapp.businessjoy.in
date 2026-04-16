<?php

namespace App\Http\Controllers\v4_4_4\api;

use App\Http\Controllers\Controller;
use App\Models\v4_4_4\BusinessSubCategory;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class BusinessSubCategoryController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $businesssubcategoryModel, $businesscategoryModel, $letterModel, $letter_variable_settingModel, $generate_letterModel, $data_formateModel;
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
        $this->businesssubcategoryModel = $this->getmodel('BusinessSubCategory');
        $this->businesscategoryModel = $this->getmodel('BusinessCategory');

       
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($this->rp['societymodule']['businessubcategory']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        // dd($request->all());
        $businesssubcategories = $this->businesssubcategoryModel::leftJoin('business_category', 'business_sub_category.category_id', '=', 'business_category.id')
            ->where('business_sub_category.is_deleted', 0)
            ->select('business_sub_category.*', 'business_category.name as business_category_name');
            
        if ($this->rp['societymodule']['businessubcategory']['alldata'] != 1) {
           $businesssubcategories = $businesssubcategories->where('created_by', $this->userId);
        }
        $businesssubcategories = $businesssubcategories->get();
        if ($businesssubcategories->isEmpty()) {
            return DataTables::of($businesssubcategories)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }
        return DataTables::of($businesssubcategories)->with([
            'status' => 200,
        ])->make(true);
    }
    public function store(Request $request)
    {
        // dd($request->all());
        
        $businesssubcategory = $this->businesssubcategoryModel::create(
            [
                'name' => $request->name,
                'category_id' => $request->category_id,
                'created_by' => $this->userId,
            ]
        );
        // dd($businesssubcategory);
         if ($businesssubcategory) {
            return $this->successresponse(200, 'message', 'Business subcategory succesfully added');
        } else {
            return $this->successresponse(500, 'message', 'Business subcategory not succesfully added !');
        }
    }
    public function show($id)
    {
        $businesssubcategory = $this->businesssubcategoryModel::leftJoin('business_category', 'business_sub_category.category_id', '=', 'business_category.id')
            ->where('business_sub_category.id', $id)
            ->select('business_sub_category.*', 'business_category.name as business_category_name')
            ->first();
        if(!$businesssubcategory){
            return $this->errorresponse(404, 'message', 'No such business subcategory not found!');
        }
        return $this->successresponse(200, 'data', $businesssubcategory);
    }
    public function edit($id)
    {
        if ($this->rp['societymodule']['businessubcategory']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $businesssubcategory = $this->businesssubcategoryModel::find($id);
        if(!$businesssubcategory){
            return $this->errorresponse(404, 'message', 'No such business subcategory not found!');
        }
        return $this->successresponse(200, 'data', $businesssubcategory);
    }
    public function update(Request $request,$id)
    {
        if ($this->rp['societymodule']['businessubcategory']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $businesssubcategory = $this->businesssubcategoryModel::find($id);
        if(!$businesssubcategory){
            return $this->errorresponse(404, 'message', 'No such business subcategory not found!');
        }
        $businesssubcategory->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'updated_by' => $this->userId,
        ]);
        if(!$businesssubcategory){
            return $this->errorresponse(500, 'message', 'Business subcategory not updated!');
        }
        return $this->successresponse(200, 'message', 'Business subcategory updated successfully.');
    }
    public function destory($id)
    {
        if ($this->rp['societymodule']['businessubcategory']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $businesssubcategory = $this->businesssubcategoryModel::find($id);
        if(!$businesssubcategory){
            return $this->errorresponse(404, 'message', 'No such business subcategory not found!');
        }
        $businesssubcategory->update([
            'is_deleted' => 1,
        ]);
        return $this->successresponse(200, 'message', 'Business subcategory deleted successfully.');
    }
}
