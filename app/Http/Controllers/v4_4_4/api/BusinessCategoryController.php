<?php

namespace App\Http\Controllers\v4_4_4\api;

use App\Http\Controllers\Controller;
use App\Models\v4_4_4\BusinessCategory;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class BusinessCategoryController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $businesscategoryModel, $businesssubcategoryModel, $letterModel, $letter_variable_settingModel, $generate_letterModel, $data_formateModel;
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
        $this->businesscategoryModel = $this->getmodel('BusinessCategory');
        $this->businesssubcategoryModel = $this->getmodel('BusinessSubCategory');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($this->rp['societymodule']['businesscategory']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        // dd($request->all());
        $businesscategories = $this->businesscategoryModel::where('is_deleted',0);
        if ($this->rp['societymodule']['businesscategory']['alldata'] != 1) {
            $businesscategories = $businesscategories->where('created_by', $this->userId);
        }
        $businesscategories = $businesscategories->get();
        if ($businesscategories->isEmpty()) {
            return DataTables::of($businesscategories)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }
        return DataTables::of($businesscategories)->with([
            'status' => 200,
        ])->make(true);
    }
    public function store(Request $request)
    {
        if ($this->rp['societymodule']['businesscategory']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        // dd($request->all());
        
        $businesscategory = $this->businesscategoryModel::create(
            [
                'name' => $request->name,
                'created_by' => $this->userId,
            ]
        );
        // dd($businesscategory);
         if ($businesscategory) {
            return $this->successresponse(200, 'message', 'Business category succesfully added');
        } else {
            return $this->successresponse(500, 'message', 'Business category not succesfully added !');
        }
    }
   public function show($id)
    {
        $data = $this->businesscategoryModel
            ::leftJoin('business_sub_category', 'business_category.id', '=', 'business_sub_category.category_id')
            ->where('business_category.id', $id)
            ->select(
                'business_category.id',
                'business_category.name',
                'business_sub_category.id as subcategory_id',
                'business_sub_category.name as subcategory_name'
            )
            ->get();

        if ($data->isEmpty()) {
            return $this->errorresponse(404, 'message', 'No such business category found!');
        }

        // Transform data into nested format
        $category = [
            'id' => $data[0]->id,
            'name' => $data[0]->name,
            'subcategories' => []
        ];

        foreach ($data as $item) {
            if ($item->subcategory_id) {
                $category['subcategories'][] = [
                    'id' => $item->subcategory_id,
                    'name' => $item->subcategory_name
                ];
            }
        }

        return $this->successresponse(200, 'data', $category);
    }
    public function edit($id)
    {
        if ($this->rp['societymodule']['businesscategory']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $businesscategory = $this->businesscategoryModel::find($id);
        if(!$businesscategory){
            return $this->errorresponse(404, 'message', 'No such business category not found!');
        }
        return $this->successresponse(200, 'data', $businesscategory);
    }
    public function update(Request $request,$id)
    {
        if ($this->rp['societymodule']['businesscategory']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $businesscategory = $this->businesscategoryModel::find($id);
        if(!$businesscategory){
            return $this->errorresponse(404, 'message', 'No such business category not found!');
        }
        $businesscategory->update([
            'name' => $request->name,
            'updated_by' => $this->userId,
        ]);
        if(!$businesscategory){
            return $this->errorresponse(500, 'message', 'Business category not updated!');
        }
        return $this->successresponse(200, 'message', 'Business category updated successfully.');
    }
    public function destory($id)
    {
        if ($this->rp['societymodule']['businesscategory']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $businesscategory = $this->businesscategoryModel::find($id);

        if (!$businesscategory) {
            return $this->errorresponse(404, 'message', 'No such business category found!');
        }

        // Soft delete category
        $businesscategory->update([
            'is_deleted' => 1,
        ]);

        // Soft delete related subcategories
        $this->businesssubcategoryModel
            ::where('category_id', $id)
            ->update([
                'is_deleted' => 1,
            ]);

        return $this->successresponse(200, 'message', 'Business category deleted successfully.');
    }
}
