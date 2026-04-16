<?php 

namespace App\Http\Controllers\v4_4_2\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class PackageController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $packageModel;

    public function __construct(Request $request)
    {
        $this->companyId = $request->company_id;
        $this->userId = $request->user_id;
        
        $this->dbname($this->companyId);
        
        // Check user permissions
        $user_rp = DB::connection('dynamic_connection')->table('user_permissions')->where('user_id', $this->userId)->value('rp');

        if (empty($user_rp)) {
            $this->customerrorresponse();
        }

        $this->rp = json_decode($user_rp, true);
        $this->masterdbname = DB::connection()->getDatabaseName();

        $this->packageModel = $this->getmodel('Package');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check permission
        if ($this->rp['adminmodule']['package']['view'] != 1) {
            return response()->json([
                'status' => 500,
                'message' => 'You are Unauthorized',
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ]);
        }

        $packagesRes = $this->packageModel::leftJoin($this->masterdbname . '.country', 'packages.currency', '=', $this->masterdbname . '.country.id')
            ->select(
                'packages.id',
                'packages.name',
                'packages.type',
                'packages.price',
                'packages.trial_days',
                'packages.description',
                'packages.created_at',
                'packages.is_active',
                'packages.is_deleted',
                'packages.subscribed_count',
                DB::raw("DATE_FORMAT(packages.created_at, '%d-%M-%Y') as created_at_formatted"),
                'country.currency',
                'country.currency_symbol'
            )
            ->where('packages.is_deleted', 0)
            ->orderBy('packages.created_at', 'desc');

        if ($this->rp['adminmodule']['package']['alldata'] != 1) {
            $packagesRes->where('packages.created_by', $this->userId);
        }

        $totalcount = $packagesRes->count();
        $packages = $packagesRes->get();

        if ($packages->isEmpty()) {
            return DataTables::of($packages)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                    'recordsTotal' => $totalcount,
                ])
                ->make(true);
        }

        return DataTables::of($packages)
            ->with([
                'status' => 200,
                'recordsTotal' => $totalcount,
            ])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->executeTransaction(function () use ($request) {
            
            // Check permission
            if ($this->rp['adminmodule']['package']['add'] != 1) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'type' => 'required|string|max:100',
                'price' => 'required|numeric|min:0',
                'currency' => 'nullable|integer',
                'trial_days' => 'nullable|integer',
                'description' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->errorresponse(422, $validator->messages());
            } else {

                $package = $this->packageModel::create([
                    'name' => $request->name,
                    'type' => $request->type,
                    'price' => $request->price,
                    'currency' => $request->currency,
                    'trial_days' => $request->trial_days ?? 0,
                    'description' => $request->description,
                    'created_by' => $this->userId,
                ]);

                if ($package) { 
                    return $this->successresponse(200, 'message', 'Package successfully created', 'id', $package->id);
                } else {
                    throw new \Exception('Package creation failed!');
                }
            }
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Check permission
        if ($this->rp['adminmodule']['package']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $package = $this->packageModel::leftJoin($this->masterdbname . '.country', 'packages.currency', '=', $this->masterdbname . '.country.id')
            ->select(
                'packages.id',
                'packages.name',
                'packages.type',
                'packages.price',
                'packages.currency as currency_id',
                'packages.trial_days',
                'packages.description',
                'packages.created_at',
                'packages.is_active',
                'packages.created_by',
                DB::raw("DATE_FORMAT(packages.created_at, '%d-%M-%Y') as created_at_formatted"),
                'country.currency',
                'country.currency_symbol'
            )
            ->where('packages.id', $id)
            ->where('packages.is_deleted', 0)
            ->first();

        if (!$package) {
            return $this->successresponse(404, 'message', "No such package found!");
        }

        if ($this->rp['adminmodule']['package']['alldata'] != 1) {
            if ($package->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        return $this->successresponse(200, 'package', $package);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Check permission
        if ($this->rp['adminmodule']['package']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $package = $this->packageModel::where('packages.id', $id)
            ->where('packages.is_deleted', 0)
            ->first();

        if (!$package) {
            return $this->successresponse(404, 'message', "No such package found!");
        }

        if ($this->rp['adminmodule']['package']['alldata'] != 1) {
            if ($package->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        return $this->successresponse(200, 'package', $package);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return $this->executeTransaction(function () use ($request, $id) {
            
            // Check permission
            if ($this->rp['adminmodule']['package']['edit'] != 1) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'type' => 'required|string|max:100',
                'price' => 'required|numeric|min:0',
                'currency' => 'nullable|integer',
                'trial_days' => 'nullable|integer',
                'description' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->errorresponse(422, $validator->messages());
            } else {
                $package = $this->packageModel::find($id);

                if (!$package) {
                    return $this->successresponse(404, 'message', 'No such package found!');
                }

                if ($this->rp['adminmodule']['package']['alldata'] != 1) {
                    if ($package->created_by != $this->userId) {
                        return $this->successresponse(500, 'message', 'You are Unauthorized');
                    }
                }

                $update = $package->update([
                    'name' => $request->name,
                    'type' => $request->type,
                    'price' => $request->price,
                    'currency' => $request->currency,
                    'trial_days' => $request->trial_days ?? $package->trial_days,
                    'description' => $request->description ?? $package->description,
                    'updated_by' => $this->userId,
                ]);

                if ($update) { 
                    return $this->successresponse(200, 'message', 'Package successfully updated');
                } else {
                    throw new \Exception("Package update failed!");
                }
            }
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->executeTransaction(function () use ($id) {
            
            // Check permission
            if ($this->rp['adminmodule']['package']['delete'] != 1) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }

            $package = $this->packageModel::find($id);

            if (!$package) {
                return $this->successresponse(404, 'message', 'No such package found!');
            }

            if ($this->rp['adminmodule']['package']['alldata'] != 1) {
                if ($package->created_by != $this->userId) {
                    return $this->successresponse(500, 'message', 'You are Unauthorized');
                }
            }

            $package->update([
                'is_deleted' => 1
            ]);
 
            return $this->successresponse(200, 'message', 'Package successfully deleted.');
        });
    }

    /**
     * Change package status (active/inactive)
     */
    public function changeStatus(Request $request, int $id)
    {
        if ($this->rp['adminmodule']['package']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $package = $this->packageModel::find($id);

        if (!$package) {
            return $this->successresponse(404, 'message', "No such package found!");
        }

        if ($this->rp['adminmodule']['package']['alldata'] != 1) {
            if ($package->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        $message = '';

        switch ($request->status) {
            case "active":
                $package->is_active = 1;
                $message = "Package activated.";
                break;

            case "inactive":
                $package->is_active = 0;
                $message = "Package deactivated.";
                break;
            default:
                return $this->successresponse(500, 'message', 'Invalid status');
        }

        $package->save();

        return $this->successresponse(200, 'message', $message);
    }  
}
