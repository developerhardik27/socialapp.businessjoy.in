<?php

namespace App\Http\Controllers\v4_3_2\api;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class thirdpartycompanyController extends commonController
{
    public $userId, $companyId, $rp, $quotation_other_settingModel, $masterdbname, $third_party_companyModel;

    public function __construct(Request $request)
    {
        $this->companyId = $request->company_id;
        $this->userId = $request->user_id;

        $this->dbname($this->companyId);
        // **** for checking user has permission to action on all data 
        $user_rp = DB::connection('dynamic_connection')->table('user_permissions')->where('user_id', $this->userId)->value('rp');

        if (empty($user_rp)) {
            $this->customerrorresponse();
        }

        $this->rp = json_decode($user_rp, true);

        $this->masterdbname = DB::connection()->getDatabaseName();

        $this->third_party_companyModel = $this->getmodel('third_party_company');
        $this->quotation_other_settingModel = $this->getmodel('quotation_other_setting');
    }

    // for using pdf 
    public function companydetailspdf($id)
    {
        $companydetails = $this->third_party_companyModel::join($this->masterdbname . '.country', 'third_party_companies.country_id', '=', $this->masterdbname . '.country.id')
            ->join($this->masterdbname . '.state', 'third_party_companies.state_id', '=', $this->masterdbname . '.state.id')
            ->join($this->masterdbname . '.city', 'third_party_companies.city_id', '=', $this->masterdbname . '.city.id')
            ->select(
                'third_party_companies.name',
                'third_party_companies.id as company_id',
                'third_party_companies.email',
                'third_party_companies.contact_no',
                'third_party_companies.alternative_number',
                'third_party_companies.house_no_building_name',
                'third_party_companies.road_name_area_colony',
                'third_party_companies.gst_no',
                'third_party_companies.pincode',
                'third_party_companies.img',
                'country.country_name',
                'state.state_name',
                'city.city_name'
            )
            ->where('third_party_companies.id', $id)->get();

        if ($companydetails->isEmpty()) {
            return $this->successresponse(404, 'companydetails', 'No Records Found');
        }

        return $this->successresponse(200, 'companydetails', $companydetails);
    }

    /**
     * company list
     */

    public function companylist(Request $request)
    {
        if ($this->rp['quotationmodule']['thirdpartyquotation']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $company = $this->third_party_companyModel::where('is_deleted', 0);

        if ($this->rp['quotationmodule']['thirdpartyquotation']['alldata'] != 1) {
            $company->where('created_by', $this->userId);
        }

        $company =  $company->get();

        if ($company->isEmpty()) {
            return $this->successresponse(404, 'company', 'No Records Found');
        }

        return $this->successresponse(200, 'company', $company);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($this->rp['quotationmodule']['thirdpartyquotation']['view'] != 1) {
            return response()->json([
                'status' => 500,
                'message' => 'You are Unauthorized',
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ]);
        }

        $company = $this->third_party_companyModel::join($this->masterdbname . '.country', 'third_party_companies.country_id', '=', $this->masterdbname . '.country.id')
            ->join($this->masterdbname . '.state', 'third_party_companies.state_id', '=', $this->masterdbname . '.state.id')
            ->join($this->masterdbname . '.city', 'third_party_companies.city_id', '=', $this->masterdbname . '.city.id')
            ->select(
                'third_party_companies.id',
                'third_party_companies.name',
                'third_party_companies.email',
                DB::raw("CAST(third_party_companies.contact_no AS CHAR) as contact_no"),
                DB::raw("CAST(third_party_companies.alternative_number AS CHAR) as alternative_number"),
                'third_party_companies.house_no_building_name',
                'third_party_companies.road_name_area_colony',
                'third_party_companies.gst_no',
                'third_party_companies.pan_number',
                'third_party_companies.img',
                'country.country_name',
                'state.state_name',
                'city.city_name',
                'third_party_companies.created_by',
                'third_party_companies.updated_by',
                DB::raw("DATE_FORMAT(third_party_companies.created_at,'%d-%M-%Y %h:%i %p')as created_at_formatted"),
                'third_party_companies.updated_at',
                'third_party_companies.is_active',
                'third_party_companies.is_deleted'
            )
            ->where('third_party_companies.is_deleted', 0);

        if ($this->rp['quotationmodule']['thirdpartyquotation']['alldata'] != 1) {
            $company->where('created_by', $this->userId);
        }

        $company = $company->get();

        if ($company->isEmpty()) {
            return DataTables::of($company)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                ])
                ->make(true);
        }

        return DataTables::of($company)
            ->with([
                'status' => 200,
            ])->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        if ($this->rp['quotationmodule']['thirdpartyquotation']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        // validate incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'email' => 'nullable|string|max:50',
            'contact_number' => 'required|numeric|digits:10',
            'alternative_number' => 'nullable|numeric|digits:10',
            'house_no_building_name' => 'required|string|max:255',
            'road_name_area_colony' => 'required|string|max:255',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:50',
            'country' => 'required|numeric',
            'state' => 'required|numeric',
            'city' => 'required|numeric',
            'pincode' => 'required|numeric',
            'img' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
            'user_id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            // return error response
            return $this->errorresponse(422, $validator->messages());
        } else {

            return $this->executeTransaction(function () use ($request) {

                // upload company image
                $imageName = null;

                if (($request->hasFile('img') && $request->file('img') != null)) {

                    $image = $request->file('img');

                    $dirPath = public_path('uploads/') . $this->companyId . '/thirdpartyquotaton';

                    if (!file_exists($dirPath)) {
                        mkdir($dirPath, 0755, true);
                    }

                    // Check if image file is uploaded
                    if ($image) {
                        $imageName = $request->name . time() . '.' . $image->getClientOriginalExtension();
                        $image->move($dirPath, $imageName); // upload image
                        $imageName = $this->companyId . '/thirdpartyquotaton/' . $imageName;
                    }
                }

                $company_details_data = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'contact_no' => $request->contact_number,
                    'alternative_number' => $request->alternative_number,
                    'gst_no' => $request->gst_number,
                    'pan_number' => $request->pan_number,
                    'house_no_building_name' => $request->house_no_building_name,
                    'road_name_area_colony' => $request->road_name_area_colony,
                    'country_id' => $request->country,
                    'state_id' => $request->state,
                    'city_id' => $request->city,
                    'pincode' => $request->pincode,
                    'created_by' => $this->userId,
                    'img' => $imageName
                ];

                $company_details = $this->third_party_companyModel::create($company_details_data); // insert company details

                if ($company_details) {
                    return $this->successresponse(200, 'message', 'Company succesfully added');
                } else {
                    throw new \Exception("Company details creation failed!");
                }
            });
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if ($this->rp['quotationmodule']['thirdpartyquotation']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $company = $this->third_party_companyModel::find($id);

        if (!$company) {
            return $this->successresponse(404, 'message', "No Such company Found!");
        }

        if ($this->rp['quotationmodule']['thirdpartyquotation']['alldata'] != 1) {
            if ($company->created_by != $this->userId) {
                return $this->successresponse(500, 'message', "You are Unauthorized!");
            }
        }

        return $this->successresponse(200, 'company', $company);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if ($this->rp['quotationmodule']['thirdpartyquotation']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $company = $this->third_party_companyModel::find($id);

        if (!$company) {
            return $this->successresponse(404, 'message', "No Such company Found!");
        }

        if ($this->rp['quotationmodule']['thirdpartyquotation']['alldata'] != 1) {
            if ($company->created_by != $this->userId) {
                return $this->successresponse(500, 'message', "You are Unauthorized!");
            }
        }

        return $this->successresponse(200, 'company', $company);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if ($this->rp['quotationmodule']['thirdpartyquotation']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        return $this->executeTransaction(function () use ($request, $id) {
            // validate incoming request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:50',
                'email' => 'nullable|string|max:50',
                'contact_number' => 'required|numeric|digits:10',
                'alternative_number' => 'nullable|numeric|digits:10',
                'house_no_building_name' => 'required|string|max:255',
                'road_name_area_colony' => 'required|string|max:255',
                'gst_number' => 'nullable|string|max:50',
                'pan_number' => 'nullable|string|max:50',
                'country' => 'required|numeric',
                'state' => 'required|numeric',
                'city' => 'required|numeric',
                'pincode' => 'required|numeric',
                'img' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
                'user_id' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                // return error response if validator fails
                return $this->errorresponse(422, $validator->messages());
            } else {

                $company = $this->third_party_companyModel::find($id);

                if (!$company) {
                    return $this->successresponse(500, 'message', 'You are Unauthorized');
                }

                if ($this->rp['quotationmodule']['thirdpartyquotation']['alldata'] != 1) {
                    if ($company->created_by != $this->userId) {
                        return $this->successresponse(500, 'message', "You are Unauthorized!");
                    }
                }

                $imageName = $company->img;

                if (($request->hasFile('img') && $request->file('img') != null)) {

                    $image = $request->file('img');

                    $dirPath = public_path('uploads/') . $id . '/thirdpartyquotaton';

                    // Check if image file is uploaded
                    if ($image) {
                        $imageName = $request->name . time() . '.' . $image->getClientOriginalExtension();
                        $image->move($dirPath, $imageName); // upload image
                        $imageName = $id . '/thirdpartyquotaton/' . $imageName;
                    }
                }

                $company_details_data = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'contact_no' => $request->contact_number,
                    'alternative_number' => $request->alternative_number,
                    'gst_no' => $request->gst_number,
                    'pan_number' => $request->pan_number,
                    'house_no_building_name' => $request->house_no_building_name,
                    'road_name_area_colony' => $request->road_name_area_colony,
                    'country_id' => $request->country,
                    'state_id' => $request->state,
                    'city_id' => $request->city,
                    'pincode' => $request->pincode,
                    'img' => $imageName,
                    'updated_by' => $this->userId
                ];

                $company_details = $company->update($company_details_data); // company update

                if (!$company_details) {
                    return $this->successresponse(500, 'message', 'Oops ! Something Went wrong');
                }

                return $this->successresponse(200, 'message', 'company succesfully updated');
            }
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        return $this->executeTransaction(function () use ($id) {

            if ($this->rp['quotationmodule']['thirdpartyquotation']['delete'] != 1) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }

            $company = $this->third_party_companyModel::find($id);

            if (!$company) {
                return $this->successresponse(404, 'message', 'No Such company Found!');
            }

            if ($this->rp['quotationmodule']['thirdpartyquotation']['alldata'] != 1) {
                if ($company->created_by != $this->userId) {
                    return $this->successresponse(500, 'message', "You are Unauthorized!");
                }
            }

            $company->update([
                'is_deleted' => 1
            ]);

            return $this->successresponse(200, 'message', 'company succesfully deleted');
        });
    }

    // company  
    public function statusupdate(Request $request, string $id)
    {
        if ($this->rp['quotationmodule']['thirdpartyquotation']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        return $this->executeTransaction(function () use ($request, $id) {

            $company = $this->third_party_companyModel::find($id);

            if (!$company) {
                return $this->successresponse(404, 'message', 'No Such Company Found!');
            }

            if ($this->rp['quotationmodule']['thirdpartyquotation']['alldata'] != 1) {
                if ($company->created_by != $this->userId) {
                    return $this->successresponse(500, 'message', "You are Unauthorized!");
                }
            }

            $company->update([
                'is_active' => $request->status
            ]);

            return $this->successresponse(200, 'message', 'Comapny status succesfully updated');
        });
    }
}
