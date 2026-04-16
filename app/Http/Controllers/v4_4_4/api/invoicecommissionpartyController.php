<?php

namespace App\Http\Controllers\v4_4_4\api;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class invoicecommissionpartyController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $customerModel, $invoice_other_settingModel;

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

        $this->customerModel = $this->getmodel('customer');
        $this->invoice_other_settingModel = $this->getmodel('invoice_other_setting');
    }

    public function datatable()
    {

        if ($this->rp['invoicemodule']['invoicecommissionparty']['view'] != 1) {
            return response()->json([
                'status' => 500,
                'message' => 'You are unauthorized.',
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ]);
        }

        $invoicecommissionparty = $this->customerModel::leftjoin($this->masterdbname . '.country', 'customers.country_id', '=', $this->masterdbname . '.country.id')
            ->leftjoin($this->masterdbname . '.state', 'customers.state_id', '=', $this->masterdbname . '.state.id')
            ->leftjoin($this->masterdbname . '.city', 'customers.city_id', '=', $this->masterdbname . '.city.id')
            ->leftjoin($this->masterdbname . '.users', 'customers.created_by', '=', $this->masterdbname . '.users.id')
            ->select('customers.id', 'customers.customer_id', 'customers.firstname', 'customers.lastname', 'customers.company_name', 'customers.email', 'customers.contact_no', 'customers.house_no_building_name', 'customers.road_name_area_colony', 'country.country_name', 'state.state_name', 'city.city_name', 'customers.pincode', 'customers.gst_no', 'customers.company_id', 'customers.created_by', 'customers.updated_by', DB::raw("DATE_FORMAT(customers.created_at, '%d-%M-%Y %h:%i %p') as created_at_formatted"), 'customers.updated_at', 'customers.is_active', 'users.firstname as createdby_fname', 'users.lastname as createdby_lname')
            ->whereIn('customers.customer_type', ['invoicecommissionparty'])
            ->where('customers.is_deleted', 0);

        if ($this->rp['invoicemodule']['invoicecommissionparty']['alldata'] != 1) {
            $invoicecommissionparty->where('customers.created_by', $this->userId);
        }

        $totalcount = $invoicecommissionparty->count(); // count total record

        $invoicecommissionparty = $invoicecommissionparty->get();

        if ($invoicecommissionparty->isEmpty()) {
            return DataTables::of($invoicecommissionparty)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                    'recordsTotal' => $totalcount, // Total records count
                ])
                ->make(true);
        }

        return DataTables::of($invoicecommissionparty)
            ->with([
                'status' => 200,
                'recordsTotal' => $totalcount, // Total records count
            ])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($this->rp['invoicemodule']['invoicecommissionparty']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are unauthorized.');
        }

        $invoicecommissionparty = $this->customerModel::leftjoin($this->masterdbname . '.country', 'customers.country_id', '=', $this->masterdbname . '.country.id')
            ->leftjoin($this->masterdbname . '.state', 'customers.state_id', '=', $this->masterdbname . '.state.id')
            ->leftjoin($this->masterdbname . '.city', 'customers.city_id', '=', $this->masterdbname . '.city.id')
            ->leftjoin($this->masterdbname . '.users', 'customers.created_by', '=', $this->masterdbname . '.users.id')
            ->select('customers.id', 'customers.customer_id', 'customers.firstname', 'customers.lastname', 'customers.company_name', 'customers.email', 'customers.contact_no', 'customers.house_no_building_name', 'customers.road_name_area_colony', 'country.country_name', 'state.state_name', 'city.city_name', 'customers.pincode', 'customers.gst_no', 'customers.company_id', 'customers.created_by', 'customers.updated_by', DB::raw("DATE_FORMAT(customers.created_at, '%d-%M-%Y %h:%i %p') as created_at_formatted"), 'customers.updated_at', 'customers.is_active', 'users.firstname as createdby_fname', 'users.lastname as createdby_lname')
            ->whereIn('customers.customer_type', ['invoicecommissionparty'])
            ->where('customers.is_deleted', 0);

        if ($this->rp['invoicemodule']['invoicecommissionparty']['alldata'] != 1) {
            $invoicecommissionparty->where('customers.created_by', $this->userId);
        }

        $invoicecommissionparty = $invoicecommissionparty->get();

        if ($invoicecommissionparty->isEmpty()) {
            return $this->successresponse(404, 'invoicecommissionparty', 'No records found');
        }

        return $this->successresponse(200, 'invoicecommissionparty', $invoicecommissionparty);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($this->rp['invoicemodule']['invoicecommissionparty']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are unauthorized.');
        }

        // dynamically validate incoming request data (company name/first name required)
        $validator = Validator::make($request->all(), [
            'firstname' => 'nullable|required_without:company_name|string|max:50',
            'lastname' => 'nullable|string|max:50',
            'company_name' => 'nullable|required_without:firstname|string|max:50',
            'gst_number' => 'nullable|alpha_num|max:50',
            'email' => 'nullable|email|max:50',
            'pincode' => 'nullable|numeric',
            'contact_number' => 'nullable|numeric|digits:10',
            'house_no_building_name' => 'nullable|string|max:191',
            'road_name_area_colony' => 'nullable|string|max:191',
            'country' => 'nullable|numeric',
            'state' => 'nullable|numeric',
            'city' => 'nullable|numeric',
            'user_id' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        } else {

            $customerid = $this->invoice_other_settingModel::find(1);

            $customerType = 'invoicecommissionparty';

            $invoicecommissionparty = $this->customerModel::create([ //insert customer record and return customer id
                'customer_id' => $customerid->current_customer_id,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'company_name' => $request->company_name,
                'email' => $request->email,
                'contact_no' => $request->contact_number,
                'house_no_building_name' => $request->house_no_building_name,
                'road_name_area_colony' => $request->road_name_area_colony,
                'country_id' => $request->country,
                'state_id' => $request->state,
                'city_id' => $request->city,
                'pincode' => $request->pincode,
                'gst_no' => $request->gst_number,
                'customer_type' => $customerType,
                'company_id' => $this->companyId,
                'created_by' => $this->userId,
            ]);

            if ($invoicecommissionparty) {
                return $this->successresponse(200, 'message', 'Commission party successfully added.', 'commission_party_id', $invoicecommissionparty->id);
            } else {
                return $this->successresponse(500, 'message', 'Commission party not successfully added !');
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if ($this->rp['invoicemodule']['invoicecommissionparty']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are unauthorized.');
        }

        $invoicecommissionparty = $this->customerModel::find($id);

        if (!$invoicecommissionparty) {
            return $this->successresponse(404, 'message', "No such commission party found!");
        }

        if ($this->rp['invoicemodule']['invoicecommissionparty']['alldata'] != 1) {
            if ($invoicecommissionparty->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are unauthorized.');
            }
        }

        return $this->successresponse(200, 'invoicecommissionparty', $invoicecommissionparty);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if ($this->rp['invoicemodule']['invoicecommissionparty']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are unauthorized.');
        }

        $invoicecommissionparty = $this->customerModel::find($id);

        if (!$invoicecommissionparty) {
            return $this->successresponse(404, 'message', "No such commission party found!");
        }

        if ($this->rp['invoicemodule']['invoicecommissionparty']['alldata'] != 1) {
            if ($invoicecommissionparty->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are unauthorized.');
            }
        }

        return $this->successresponse(200, 'invoicecommissionparty', $invoicecommissionparty);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if ($this->rp['invoicemodule']['invoicecommissionparty']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are unauthorized.');
        }

        // validate incoming request data
        $validator = Validator::make($request->all(), [
            'firstname' => 'nullable|required_without:company_name|string|max:50',
            'lastname' => 'nullable|string|max:50',
            'company_name' => 'nullable|required_without:firstname|string|max:50',
            'gst_number' => 'nullable|alpha_num|max:50',
            'email' => 'nullable|email|max:50',
            'pincode' => 'nullable|numeric',
            'contact_number' => 'nullable|numeric|digits:10',
            'house_no_building_name' => 'nullable|string|max:191',
            'road_name_area_colony' => 'nullable|string|max:191',
            'country' => 'nullable|numeric',
            'state' => 'nullable|numeric',
            'city' => 'nullable|numeric',
            'user_id' => 'nullable|numeric',
        ]);


        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        } else {
            $invoicecommissionparty = $this->customerModel::find($id); // find customer record

            if (!$invoicecommissionparty) {
                return $this->successresponse(404, 'message', 'No such commission party Found!');
            }

            if ($this->rp['invoicemodule']['invoicecommissionparty']['alldata'] != 1) {
                if ($invoicecommissionparty->created_by != $this->userId) {
                    return $this->successresponse(500, 'message', 'You are unauthorized.');
                }
            }

            $invoicecommissionparty->update([  // update customer data
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'company_name' => $request->company_name,
                'email' => $request->email,
                'contact_no' => $request->contact_number,
                'house_no_building_name' => $request->house_no_building_name,
                'road_name_area_colony' => $request->road_name_area_colony,
                'country_id' => $request->country,
                'state_id' => $request->state,
                'city_id' => $request->city,
                'pincode' => $request->pincode,
                'gst_no' => $request->gst_number,
                'company_id' => $this->companyId,
                'updated_by' => $this->userId,
                'updated_at' => date('Y-m-d')
            ]);

            return $this->successresponse(200, 'message', 'Commission party successfully updated.');
        }
    }

    // customer status update (active/deactive)
    public function statusupdate(Request $request, string $id)
    {
        if ($this->rp['invoicemodule']['invoicecommissionparty']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are unauthorized.');
        }

        $invoicecommissionparty = $this->customerModel::find($id);

        if (!$invoicecommissionparty) {
            return $this->successresponse(404, 'message', 'No such commission party found!');
        }

        if ($this->rp['invoicemodule']['invoicecommissionparty']['alldata'] != 1) {
            if ($invoicecommissionparty->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are unauthorized.');
            }
        }

        $invoicecommissionparty->update([
            'is_active' => $request->status
        ]);

        return $this->successresponse(200, 'message', 'Commission party status successfully updated.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if ($this->rp['invoicemodule']['invoicecommissionparty']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are unauthorized.');
        }

        $invoicecommissionparty = $this->customerModel::find($id);

        if (!$invoicecommissionparty) {
            return $this->successresponse(404, 'message', 'No such commission party found!');
        }

        if ($this->rp['invoicemodule']['invoicecommissionparty']['alldata'] != 1) {
            if ($invoicecommissionparty->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are unauthorized.');
            }
        }

        $invoicecommissionparty->update([
            'is_deleted' => 1
        ]);

        return $this->successresponse(200, 'message', 'Commission party successfully deleted.');
    }
}
