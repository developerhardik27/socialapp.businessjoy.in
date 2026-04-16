<?php

namespace App\Http\Controllers\v4_4_2\api;

use Exception;
use App\Models\User;
use App\Models\Proof;
use App\Models\company;
use App\Models\tech_support;
use Illuminate\Http\Request;
use App\Models\company_detail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class versionupdateController extends commonController
{
    public $companyId, $userId;
    public function __construct(Request $request)
    {
        $this->companyId = $request->company_id;
        $this->userId = $request->user_id;
    }

    /**
     * Summary of updatecompanyversion
     * upgrade company version
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */

    public function updatecompanyversion(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'company' => 'required',
            'version' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        } else {

            $company = company::find($request->company);

            if ($company) {
                config(['database.connections.dynamic_connection.database' => $company->dbname]);

                // Establish connection to the dynamic database
                DB::purge('dynamic_connection');
                DB::reconnect('dynamic_connection');


                if ($company->app_version) {
                    // Define migration paths per version
                    $migrationMap = [
                        'v1_1_1' => $request->company == 1
                            ? ['database/migrations/newmasterdbtable']
                            : ['database/migrations/v1_1_1'],

                        'v1_2_1' => $request->company == 1
                            ? ['database/migrations/newmasterdbtable']
                            : ['database/migrations/v1_2_1'],

                        'v2_0_0' => $request->company == 1
                            ? []
                            : ['database/migrations/v2_0_0'],

                        'v3_0_0' => $request->company == 1
                            ? ['database/migrations/newmasterdbtable']
                            : ['database/migrations/v3_0_0'],

                        'v4_0_0' => $request->company == 1
                            ? ['database/migrations/newmasterdbtable']
                            : ['database/migrations/v4_0_0'],

                        'v4_1_0' => $request->company != 1
                            ? ['database/migrations/v4_1_0']
                            : [],

                        'v4_2_0' => $request->company == 1
                            ? ['database/migrations/newmasterdbtable']
                            : ['database/migrations/v4_2_0'],

                        'v4_2_1' => $request->company == 1
                            ? ['database/migrations/v4_2_1/master']
                            : ['database/migrations/v4_2_1/individual'],

                        'v4_2_2' => $request->company == 1
                            ? ['database/migrations/v4_2_2/master']
                            : ['database/migrations/v4_2_2/individual'],

                        'v4_2_3' => $request->company != 1
                            ? ['database/migrations/v4_2_3/individual']
                            : [],

                        'v4_3_0' => $request->company == 1
                            ? ['database/migrations/v4_3_0/master']
                            : ['database/migrations/v4_3_0/individual'],

                        'v4_3_1' => $request->company == 1
                            ? ['database/migrations/v4_3_1/master']
                            : ['database/migrations/v4_3_1/individual'],

                        'v4_3_2' => $request->company == 1
                            ? ['database/migrations/v4_3_2/master']
                            : ['database/migrations/v4_3_2/individual'],

                        'v4_4_0' => $request->company == 1
                            ? ['database/migrations/v4_4_0/master']
                            : ['database/migrations/v4_4_0/individual'],

                        'v4_4_1' => $request->company == 1
                            ? ['database/migrations/v4_4_1/master']
                            : ['database/migrations/v4_4_1/individual'],

                        'v4_4_2' => $request->company == 1
                            ? ['database/migrations/v4_4_2/master']
                            : ['database/migrations/v4_4_2/individual'],

                        'v4_4_3' => $request->company == 1
                            ? ['database/migrations/v4_4_3/master']
                            : ['database/migrations/v4_4_3/individual'],

                    ];

                    $paths = $migrationMap[$request->version] ?? [];

                    Log::info('Running migrations for paths: ', $paths);

                    // Run migrations
                    foreach ($paths as $path) {
                        try {
                            // if ($request->company == 1) {
                            //     // Company 1: use default database
                            //     Artisan::call('migrate', [
                            //         '--path' => $path,
                            //     ]);
                            // } else {
                            // Other companies: use dynamic_connection
                            Artisan::call('migrate', [
                                '--path' => $path,
                                '--database' => 'dynamic_connection',
                                '--force' => true
                            ]);
                            // }

                            // Log the Artisan output
                            Log::info("Migration output for $path: " . Artisan::output());
                        } catch (\Exception $e) {
                            Log::error("Migration failed for $path: " . $e->getMessage());
                        }
                    }

                    Log::info('Migrations completed.');


                    return $this->executeTransaction(function () use ($request, $company) {

                        switch ($request->version) {
                            case 'v1_1_1':
                                try {
                                    if (Schema::connection('dynamic_connection')->hasTable('invoice_other_settings') && Schema::hasTable('invoices')) {
                                        $getGstSettings = DB::connection('dynamic_connection')
                                            ->table('invoice_other_settings')
                                            ->select('sgst', 'cgst', 'gst')
                                            ->first();

                                        if ($getGstSettings) {
                                            // Encode GST settings as JSON
                                            $gstSettingsJson = json_encode([
                                                'sgst' => $getGstSettings->sgst,
                                                'cgst' => $getGstSettings->cgst,
                                                'gst' => $getGstSettings->gst,
                                            ]);

                                            // Update 'invoices' table where 'gstsettings' is NULL or empty
                                            DB::connection('dynamic_connection')
                                                ->table('invoices')
                                                ->where('is_deleted', 0)
                                                ->whereIn('gstsettings', [null, ''])
                                                ->update(['gstsettings' => $gstSettingsJson]);
                                        }
                                    }
                                } catch (Exception $e) {
                                    Log::error($e);
                                }
                                break;
                            case 'v1_2_1':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);
                                        $newrp = [
                                            "invoicenumbersetting" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                            "invoicetandcsetting" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                            "invoicestandardsetting" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                            "invoicegstsetting" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                            "invoicecustomeridsetting" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                        ];

                                        if (!isset($jsonrp['invoicemodule']['invoicenumbersetting'])) {

                                            // Update the 'invoicemodule' section with new permissions
                                            $jsonrp['invoicemodule'] = array_merge($jsonrp['invoicemodule'], $newrp);

                                            // Encode updated permissions back to JSON
                                            $updatedRpJson = json_encode($jsonrp);
                                            // Update the database
                                            DB::connection('dynamic_connection')->table('user_permissions')
                                                ->where('user_id', $userrp->user_id)
                                                ->update(['rp' => $updatedRpJson]);
                                        }
                                    }
                                }
                                break;
                            case 'v2_0_0':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);
                                        $newrp = [
                                            'quotationmodule' => [
                                                "quotation" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "quotationmngcol" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "quotationformula" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "quotationsetting" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "quotationnumbersetting" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "quotationtandcsetting" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "quotationstandardsetting" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "quotationgstsetting" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "quotationcustomer" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                            ]
                                        ];

                                        if (!isset($jsonrp['quotationmodule'])) {

                                            // add the 'quotation module' section with new permissions
                                            $jsonrp = array_merge($jsonrp, $newrp);

                                            // Encode updated permissions back to JSON
                                            $updatedRpJson = json_encode($jsonrp);
                                            // Update the database
                                            DB::connection('dynamic_connection')->table('user_permissions')
                                                ->where('user_id', $userrp->user_id)
                                                ->update(['rp' => $updatedRpJson]);
                                        }
                                    }
                                }

                                if (Schema::connection('dynamic_connection')->hasTable('quotation_other_settings')) {
                                    $existingData = DB::connection('dynamic_connection')
                                        ->table('quotation_other_settings')
                                        ->count();

                                    if ($existingData == 0) {
                                        $setDefaultSettings = DB::connection('dynamic_connection')
                                            ->table('quotation_other_settings')
                                            ->insert([
                                                'overdue_day' => 30,
                                                'year_start' => date('Y-m-d', strtotime(date('Y') . '-04-01')),
                                                'sgst' => 9,
                                                'cgst' => 9,
                                                'gst' => 0,
                                                'customer_id' => 1,
                                                'current_customer_id' => 1,
                                                'created_by' => $this->userId,
                                            ]);
                                    }
                                }
                                break;
                            case 'v3_0_0':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);

                                        if (isset($jsonrp['accountmodule']['purchase'])) {

                                            unset($jsonrp['accountmodule']['purchase']);

                                            // Add or update the 'purchase' section in the 'inventorymodule'
                                            $jsonrp['inventorymodule']['purchase'] = ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null];
                                            $jsonrp['inventorymodule']['productcategory'] = ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null];
                                            $jsonrp['inventorymodule']['productcolumnmapping'] = ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null];
                                            $jsonrp['inventorymodule']['inventory'] = ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null];
                                            $jsonrp['inventorymodule']['supplier'] = ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null];

                                            // Encode updated permissions back to JSON
                                            $updatedRpJson = json_encode($jsonrp);
                                            // Update the database
                                            DB::connection('dynamic_connection')->table('user_permissions')
                                                ->where('user_id', $userrp->user_id)
                                                ->update(['rp' => $updatedRpJson]);
                                        }
                                    }
                                }
                                break;
                            case 'v4_0_0':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);
                                        $newrp = [
                                            'logisticmodule' => [
                                                "consignorcopy" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "logisticsettings" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "consignmentnotenumbersettings" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "consignorcopytandcsettings" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "consignee" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "consignor" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                            ]
                                        ];

                                        if (!isset($jsonrp['logisticmodule'])) {

                                            // add the 'logistic module' section with new permissions
                                            $jsonrp = array_merge($jsonrp, $newrp);

                                            // Encode updated permissions back to JSON
                                            $updatedRpJson = json_encode($jsonrp);
                                            // Update the database
                                            DB::connection('dynamic_connection')->table('user_permissions')
                                                ->where('user_id', $userrp->user_id)
                                                ->update(['rp' => $updatedRpJson]);
                                        }
                                    }
                                }

                                // add default setting for logistic module
                                if (Schema::connection('dynamic_connection')->hasTable('logistic_settings')) {
                                    $existingData = DB::connection('dynamic_connection')
                                        ->table('logistic_settings')
                                        ->count();

                                    if ($existingData == 0) {
                                        $setDefaultSettings = DB::connection('dynamic_connection')
                                            ->table('logistic_settings')
                                            ->insert([
                                                'created_by' => $this->userId,
                                            ]);
                                    }
                                }

                                break;
                            case 'v4_1_0':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);

                                        if (!isset($jsonrp['invoicemodule']['invoicedashboard'])) {
                                            $jsonrp['invoicemodule']['invoicedashboard'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['leadmodule']['leaddashboard'])) {
                                            $jsonrp['leadmodule']['leaddashboard'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['leadmodule']['upcomingfollowup'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['leadmodule']['analysis'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['leadmodule']['leadownerperformance'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['leadmodule']['recentactivity'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['leadmodule']['calendar'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['customersupportmodule']['customersupportdashboard'])) {
                                            $jsonrp['customersupportmodule']['customersupportdashboard'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['adminmodule']['admindashboard'])) {
                                            $jsonrp['adminmodule']['admindashboard'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['inventorymodule']['inventorydashboard'])) {
                                            $jsonrp['inventorymodule']['inventorydashboard'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['remindermodule']['reminderdashboard'])) {
                                            $jsonrp['remindermodule']['reminderdashboard'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['reportmodule']['reportdashboard'])) {
                                            $jsonrp['reportmodule']['reportdashboard'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['blogmodule']['blogdashboard'])) {
                                            $jsonrp['blogmodule']['blogdashboard'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['quotationmodule']['quotationdashboard'])) {
                                            $jsonrp['quotationmodule']['quotationdashboard'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['logisticmodule']['logisticdashboard'])) {
                                            $jsonrp['logisticmodule']['logisticdashboard'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        // Encode updated permissions back to JSON
                                        $updatedRpJson = json_encode($jsonrp);
                                        // Update the database
                                        DB::connection('dynamic_connection')->table('user_permissions')
                                            ->where('user_id', $userrp->user_id)
                                            ->update(['rp' => $updatedRpJson]);
                                    }
                                }

                                break;

                            case 'v4_2_0':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);
                                        $newrp = [
                                            'developermodule' => [
                                                "slowpage" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "errorlog" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "cronjob" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "techdoc" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                                "versiondoc" => ["show" => null, "add" => null, "view" => null, "edit" => null, "delete" => null, "alldata" => null],
                                            ]
                                        ];

                                        if (!isset($jsonrp['developermodule'])) {
                                            // add the 'developer module' section with new permissions
                                            $jsonrp = array_merge($jsonrp, $newrp);
                                        }

                                        if (!isset($jsonrp['logisticmodule']['logisticothersettings'])) {
                                            $jsonrp['logisticmodule']['logisticothersettings'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        if (!isset($jsonrp['invoicemodule']['invoiceapi'])) {
                                            $jsonrp['invoicemodule']['invoiceapi'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['leadmodule']['leadapi'])) {
                                            $jsonrp['leadmodule']['leadapi'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['customersupportmodule']['customersupportapi'])) {
                                            $jsonrp['customersupportmodule']['customersupportapi'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['adminmodule']['adminapi'])) {
                                            $jsonrp['adminmodule']['adminapi'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['inventorymodule']['inventoryapi'])) {
                                            $jsonrp['inventorymodule']['inventoryapi'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['remindermodule']['reminderapi'])) {
                                            $jsonrp['remindermodule']['reminderapi'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['reportmodule']['reportapi'])) {
                                            $jsonrp['reportmodule']['reportapi'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['blogmodule']['blogapi'])) {
                                            $jsonrp['blogmodule']['blogapi'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['quotationmodule']['quotationapi'])) {
                                            $jsonrp['quotationmodule']['quotationapi'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['logisticmodule']['logisticapi'])) {
                                            $jsonrp['logisticmodule']['logisticapi'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }


                                        // Encode updated permissions back to JSON
                                        $updatedRpJson = json_encode($jsonrp);
                                        // Update the database
                                        DB::connection('dynamic_connection')->table('user_permissions')
                                            ->where('user_id', $userrp->user_id)
                                            ->update(['rp' => $updatedRpJson]);
                                    }
                                }

                                break;

                            case 'v4_2_1':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);

                                        if (!isset($jsonrp['logisticmodule']['watermark'])) {
                                            $jsonrp['logisticmodule']['watermark'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        if (!isset($jsonrp['developermodule']['recentactivitydata'])) {
                                            $jsonrp['developermodule']['recentactivitydata'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        // Encode updated permissions back to JSON
                                        $updatedRpJson = json_encode($jsonrp);
                                        // Update the database
                                        DB::connection('dynamic_connection')->table('user_permissions')
                                            ->where('user_id', $userrp->user_id)
                                            ->update(['rp' => $updatedRpJson]);
                                    }
                                }

                                break;

                            case 'v4_2_2':

                                if ($company->id != 1) {
                                    $blogsettings = DB::connection('dynamic_connection')->table('blog_settings')
                                        ->count();

                                    if ($blogsettings == 0) {
                                        DB::connection('dynamic_connection')->table('blog_settings')
                                            ->insert([
                                                'details_endpoint' => '',
                                                'img_allowed_filetype' => 'jpg,jpeg,png',
                                                'img_max_size' => '10',
                                                'img_width' => '600',
                                                'img_height' => '400',
                                                'thumbnail_img_width' => '400',
                                                'thumbnail_img_height' => '266',
                                                'validate_dimension' => '0',
                                                'created_at' => now()
                                            ]);
                                    }
                                }

                                $directoryPath = public_path('uploads/') . $company->id;

                                if (!file_exists($directoryPath)) {
                                    mkdir($directoryPath, 0755, true);
                                    mkdir($directoryPath . '/blog', 0755, true);
                                    mkdir($directoryPath . '/product', 0755, true);
                                    mkdir($directoryPath . '/lead/attachments/', 0755, true);
                                    mkdir($directoryPath . '/lead/callhistory/', 0755, true);
                                }

                                // products img move
                                if ($company->id != 1) {
                                    $products = DB::connection('dynamic_connection')->table('products')->where('is_deleted', 0)->get();

                                    if (!$products->isEmpty()) {

                                        foreach ($products as $product) {
                                            $productImgs = $product->product_media;

                                            if (!empty($productImgs)) {
                                                $newImgName = [];
                                                $productImgs = explode(',', $productImgs);

                                                foreach ($productImgs as $productImg) {

                                                    $checkProductImg = public_path('uploads/products/' . $productImg);

                                                    if (File::exists($checkProductImg)) {
                                                        // Generate a new unique name for the productimg
                                                        $extension = File::extension($productImg);
                                                        $productImgNewName = uniqid() . '-' . time() . '.' . $extension;

                                                        $dirPath = public_path('uploads/' . $company->id . '/product/' . $product->id . '/');

                                                        if (!File::exists($dirPath)) {
                                                            File::makeDirectory($dirPath, 0755, true);
                                                        }

                                                        $destinationPath = $dirPath . $productImgNewName;

                                                        // Move file
                                                        File::move($checkProductImg, $destinationPath);

                                                        // Save the relative path to the DB
                                                        $newImgName[] = $company->id . '/product/' . $product->id . '/' . $productImgNewName;
                                                    }
                                                }

                                                $newImgString = implode(',', $newImgName);

                                                DB::connection('dynamic_connection')->table('products')->where('id', $product->id)->update([
                                                    'product_media' => $newImgString
                                                ]);
                                            }
                                        }
                                    }
                                }

                                // blog img move
                                if ($company->id != 1) {
                                    $blogs = DB::connection('dynamic_connection')->table('blogs')->where('is_deleted', 0)->get();

                                    if (!$blogs->isEmpty()) {
                                        foreach ($blogs as $blog) {
                                            $blogOldImg = $blog->img;

                                            if (!empty($blogOldImg)) {
                                                // Prepare paths
                                                $blogImagePath = public_path('blog/' . ltrim($blogOldImg, '/'));
                                                $thumbnailPath = public_path('blog/thumbnail/' . ltrim($blogOldImg, '/')); // ✅ Fixed missing slash

                                                // Generate new names
                                                $extension = pathinfo($blogOldImg, PATHINFO_EXTENSION);
                                                $newFileName = uniqid() . '-' . time() . '.' . $extension;
                                                $newThumbFileName = uniqid() . '-' . time() . '.' . $extension;

                                                $dateFolder = date('dmY');
                                                $baseDir = public_path("uploads/{$company->id}/blog/{$dateFolder}/");

                                                // Move main blog image
                                                if (File::exists($blogImagePath)) {
                                                    if (!File::exists($baseDir)) {
                                                        File::makeDirectory($baseDir, 0755, true);
                                                    }

                                                    $destinationImagePath = $baseDir . $newFileName;
                                                    File::move($blogImagePath, $destinationImagePath);

                                                    $blog->img = "{$company->id}/blog/{$dateFolder}/{$newFileName}";
                                                }

                                                // Move thumbnail image
                                                $thumbDir = $baseDir . 'thumbnail/';
                                                if (File::exists($thumbnailPath)) {
                                                    if (!File::exists($thumbDir)) {
                                                        File::makeDirectory($thumbDir, 0755, true);
                                                    }

                                                    $destinationThumbPath = $thumbDir . $newThumbFileName;
                                                    File::move($thumbnailPath, $destinationThumbPath);

                                                    $blog->thumbnail_img = "{$company->id}/blog/{$dateFolder}/thumbnail/{$newThumbFileName}";
                                                }

                                                // Save updated blog
                                                DB::connection('dynamic_connection')->table('blogs')->where('id', $blog->id)->update([
                                                    'img' => $blog->img,
                                                    'thumbnail_img' => $blog->thumbnail_img,
                                                ]);
                                            }
                                        }
                                    }
                                }

                                $company_details = company_detail::find($company->company_details_id);

                                if ($company_details) {
                                    $fields = ['img', 'pr_sign_img', 'watermark_img'];

                                    foreach ($fields as $field) {
                                        $value = $company_details->$field;
                                        if (!empty($value)) {
                                            $image = public_path('uploads/' . $company_details->$field);

                                            if (File::exists($image)) {
                                                // Generate a new unique name for the image
                                                $extension = File::extension($company_details->$field);
                                                $newName = uniqid() . '-' . time() . '.' . $extension;

                                                $dirPath = public_path('uploads/' . $company->id . '/');

                                                $destinationPath = $dirPath . $newName;

                                                // Move file
                                                File::move($image, $destinationPath);

                                                // Save the relative path to the DB
                                                $company_details->$field = $company->id . '/' . $newName;
                                            }
                                        }
                                    }

                                    // Save updated record
                                    $company_details->save();
                                }

                                // update user permissions and change img/attachments paths
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {

                                    foreach ($rp as $userrp) {

                                        if (!file_exists($directoryPath . '/user_' . $userrp->user_id)) { // create user folder for photos
                                            mkdir($directoryPath . '/user_' . $userrp->user_id, 0755, true);
                                            mkdir($directoryPath . '/techsupport/user_' . $userrp->user_id, 0755, true);
                                        }

                                        $jsonrp = json_decode($userrp->rp, true);

                                        if (!isset($jsonrp['developermodule']['developerdashboard'])) {
                                            $jsonrp['developermodule']['developerdashboard'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        if (!isset($jsonrp['adminmodule']['loginhistory'])) {
                                            $jsonrp['adminmodule']['loginhistory'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        if (!isset($jsonrp['blogmodule']['blogsettings'])) {
                                            $jsonrp['blogmodule']['blogsettings'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        // Encode updated permissions back to JSON
                                        $updatedRpJson = json_encode($jsonrp);
                                        // Update the database
                                        DB::connection('dynamic_connection')->table('user_permissions')
                                            ->where('user_id', $userrp->user_id)
                                            ->update(['rp' => $updatedRpJson]);


                                        // move img/files to new path and update path in db
                                        $user = User::find($userrp->user_id);

                                        if ($user) {
                                            $oldimg = $user->img;
                                            if (!empty($oldimg)) {
                                                $userimage = public_path('uploads/' . $user->img);

                                                if (File::exists($userimage)) {
                                                    // Generate a new unique name for the userimage
                                                    $extension = File::extension($user->img);
                                                    $userImgNewName = uniqid() . '-' . time() . '.' . $extension;

                                                    $dirPath = public_path('uploads/' . $company->id . '/user_' . $user->id . '/');

                                                    $destinationPath = $dirPath . $userImgNewName;

                                                    // Move file
                                                    File::move($userimage, $destinationPath);

                                                    // Save the relative path to the DB
                                                    $user->img = $company->id . '/user_' . $user->id . '/' . $userImgNewName;
                                                    $user->save();
                                                }
                                            }
                                        }

                                        //techsupport attachment move 
                                        $techSupports = tech_support::where('is_deleted', 0)->get();

                                        if (!$techSupports->isEmpty()) {
                                            foreach ($techSupports as $techSupport) {

                                                if (!in_array($techSupport->attachment, ['[]', ''])) {
                                                    $newImgName = [];
                                                    $attachments = json_decode($techSupport->attachment, true);

                                                    foreach ($attachments as $attachment) {
                                                        $checkAttachment = public_path('uploads/files/' . $attachment);

                                                        if (File::exists($checkAttachment)) {
                                                            // Generate a new unique name for the userimage
                                                            $extension = File::extension($attachment);
                                                            $newAttachmentName = uniqid() . '-' . time() . '.' . $extension;

                                                            $dirPath = public_path('uploads/' . $company->id . '/techsupport/' . $techSupport->id . '/');

                                                            if (!File::exists($dirPath)) {
                                                                File::makeDirectory($dirPath, 0755, true);
                                                            }

                                                            $destinationPath = $dirPath . $newAttachmentName;

                                                            // Move file
                                                            File::move($checkAttachment, $destinationPath);

                                                            // Save the relative path to the DB
                                                            $newImgName[] = $company->id . '/techsupport/' . $techSupport->id . '/' . $newAttachmentName;
                                                        }
                                                    }

                                                    if (!empty($newImgName)) {
                                                        $newImgString = json_encode($newImgName);

                                                        tech_support::where('id', $techSupport->id)->update([
                                                            'attachment' => $newImgString
                                                        ]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                break;

                            case 'v4_2_3':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);

                                        if (!isset($jsonrp['leadmodule']['import'])) {
                                            $jsonrp['leadmodule']['import'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        if (!isset($jsonrp['leadmodule']['export'])) {
                                            $jsonrp['leadmodule']['export'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        // Encode updated permissions back to JSON
                                        $updatedRpJson = json_encode($jsonrp);
                                        // Update the database
                                        DB::connection('dynamic_connection')->table('user_permissions')
                                            ->where('user_id', $userrp->user_id)
                                            ->update(['rp' => $updatedRpJson]);
                                    }
                                }
                                break;
                            case 'v4_3_0':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);

                                        if (!isset($jsonrp['invoicemoduel']['tdsregister'])) {
                                            $jsonrp['invoicemoduel']['tdsregister'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        if (!isset($jsonrp['leadmodule']['leadsettings'])) {
                                            $jsonrp['leadmodule']['leadsettings'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        if (!isset($jsonrp['logisticmodule']['transporterbilling'])) {
                                            $jsonrp['logisticmodule']['transporterbilling'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        if (!isset($jsonrp['logisticmodule']['downloadcopysetting'])) {
                                            $jsonrp['logisticmodule']['downloadcopysetting'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        if (!isset($jsonrp['developermodule']['cleardata'])) {
                                            $jsonrp['developermodule']['cleardata'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        // Encode updated permissions back to JSON
                                        $updatedRpJson = json_encode($jsonrp);
                                        // Update the database
                                        DB::connection('dynamic_connection')->table('user_permissions')
                                            ->where('user_id', $userrp->user_id)
                                            ->update(['rp' => $updatedRpJson]);
                                    }
                                }

                                if ($company->id != 1) {
                                    $leadsettings = DB::connection('dynamic_connection')->table('lead_settings')
                                        ->count();

                                    if ($leadsettings == 0) {
                                        DB::connection('dynamic_connection')->table('lead_settings')
                                            ->insert([
                                                'country' => 0,
                                                'state' => 0,
                                                'city' => 0,
                                                'autofill_value' => 'As Per User',
                                                'country_default_value' => null,
                                                'state_default_value' => null,
                                                'city_default_value' => null,
                                                'created_at' => now()
                                            ]);
                                    }
                                }
                                break;
                            case 'v4_3_1':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    // update user permissions
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);
                                        // Encode updated permissions back to JSON
                                        if (!isset($jsonrp['logisticmodule']['lrcolumnmapping'])) {
                                            $jsonrp['logisticmodule']['lrcolumnmapping'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['developermodule']['automatetest'])) {
                                            $jsonrp['developermodule']['automatetest'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['invoicemodule']['invoiceformsetting'])) {
                                            $jsonrp['invoicemodule']['invoiceformsetting'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (isset($jsonrp['invoicemodule']['formsetting'])) {
                                            unset($jsonrp['invoicemodule']['formsetting']);
                                        }
                                        if (!isset($jsonrp['logisticmodule']['logisticformsetting'])) {
                                            $jsonrp['logisticmodule']['logisticformsetting'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (isset($jsonrp['logisticmodule']['formsetting'])) {
                                            unset($jsonrp['logisticmodule']['formsetting']);
                                        }
                                        $updatedRpJson = json_encode($jsonrp);
                                        // Update the database
                                        DB::connection('dynamic_connection')->table('user_permissions')
                                            ->where('user_id', $userrp->user_id)
                                            ->update(['rp' => $updatedRpJson]);
                                    }
                                }
                                if ($company->id != 1) {
                                    // get consignee from consignee table
                                    $consignees = DB::connection('dynamic_connection')->table('consignees')->get();

                                    // move consignee from consignee table to customers table and delete from consignee table
                                    if ($consignees->isNotEmpty()) {
                                        foreach ($consignees as $consignee) {
                                            $customer =  DB::connection('dynamic_connection')->table('customers')->insertGetId([
                                                'firstname' => $consignee->firstname,
                                                'lastname' => $consignee->lastname,
                                                'company_name' => $consignee->company_name,
                                                'email' => $consignee->email,
                                                'contact_no' => $consignee->contact_no,
                                                'house_no_building_name' => $consignee->house_no_building_name,
                                                'road_name_area_colony' => $consignee->road_name_area_colony,
                                                'country_id' => $consignee->country_id,
                                                'state_id' => $consignee->state_id,
                                                'city_id' => $consignee->city_id,
                                                'pincode' => $consignee->pincode,
                                                'gst_no' => $consignee->gst_no,
                                                'pan_number' => $consignee->pan_number,
                                                'customer_type' => 'consignee',
                                                'company_id' => $company->id,
                                                'created_by' => $consignee->created_by,
                                                'updated_by' => $consignee->updated_by,
                                                'created_at' => $consignee->created_at,
                                                'updated_at' => $consignee->updated_at,
                                                'is_active' => $consignee->is_active,
                                                'is_deleted' => $consignee->is_deleted,
                                            ]);

                                            DB::connection('dynamic_connection')->table('consignor_copy')
                                                ->where('consignee_id', $consignee->id)->update([
                                                    'consignee_id' => $customer
                                                ]);
                                        }
                                        DB::connection('dynamic_connection')->table('consignees')
                                            ->delete();
                                    }

                                    // get consignors from consignors table
                                    $consignors = DB::connection('dynamic_connection')->table('consignors')->get();

                                    // move consignors from consignors table to customers table and delete from consignors table
                                    if ($consignors->isNotEmpty()) {
                                        foreach ($consignors as $consignor) {
                                            $customer =  DB::connection('dynamic_connection')->table('customers')->insertGetId([
                                                'firstname' => $consignor->firstname,
                                                'lastname' => $consignor->lastname,
                                                'company_name' => $consignor->company_name,
                                                'email' => $consignor->email,
                                                'contact_no' => $consignor->contact_no,
                                                'house_no_building_name' => $consignor->house_no_building_name,
                                                'road_name_area_colony' => $consignor->road_name_area_colony,
                                                'country_id' => $consignor->country_id,
                                                'state_id' => $consignor->state_id,
                                                'city_id' => $consignor->city_id,
                                                'pincode' => $consignor->pincode,
                                                'gst_no' => $consignor->gst_no,
                                                'pan_number' => $consignor->pan_number,
                                                'customer_type' => 'consignor',
                                                'company_id' => $company->id,
                                                'created_by' => $consignor->created_by,
                                                'updated_by' => $consignor->updated_by,
                                                'created_at' => $consignor->created_at,
                                                'updated_at' => $consignor->updated_at,
                                                'is_active' => $consignor->is_active,
                                                'is_deleted' => $consignor->is_deleted,
                                            ]);

                                            DB::connection('dynamic_connection')->table('consignor_copy')
                                                ->where('consignor_id', $consignor->id)->update([
                                                    'consignor_id' => $customer
                                                ]);
                                        }
                                        DB::connection('dynamic_connection')->table('consignors')
                                            ->delete();
                                    }
                                }
                                break;
                            case 'v4_3_2':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    // update user permissions
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);
                                        // Encode updated permissions back to JSON
                                        if (!isset($jsonrp['hrmodule'])) {
                                            $jsonrp['hrmodule']['hrdashboard'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['hrmodule']['employees'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['hrmodule']['companiesholidays'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['hrmodule']['letters'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        if (!isset($jsonrp['developermodule']['queues'])) {
                                            $jsonrp['developermodule']['queues'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        if (!isset($jsonrp['invoicemodule']['invoicecommission'])) {
                                            $jsonrp['invoicemodule']['invoicecommission'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['invoicemodule']['invoicecommissionsetting'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['invoicemodule']['invoicecommissionparty'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['invoicemodule']['thirdpartyinvoice'])) {
                                            $jsonrp['invoicemodule']['thirdpartyinvoice'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['quotationmodule']['thirdpartyquotation'])) {
                                            $jsonrp['quotationmodule']['thirdpartyquotation'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        if (!isset($jsonrp['quotationmodule']['quotationformsetting'])) {
                                            $jsonrp['quotationmodule']['quotationformsetting'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        if (!isset($jsonrp['accountmodule']['income'])) {
                                            $jsonrp['accountmodule']['income'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['accountmodule']['expense'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['accountmodule']['ledger'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['hrmodule']['letter_variable_setting'])) {
                                            $jsonrp['hrmodule']['letter_variable_setting'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        $updatedRpJson = json_encode($jsonrp);
                                        // Update the database
                                        DB::connection('dynamic_connection')->table('user_permissions')
                                            ->where('user_id', $userrp->user_id)
                                            ->update(['rp' => $updatedRpJson]);
                                    }
                                }

                                if ($company->id == 1) {
                                    $checkProof = Proof::count();

                                    if ($checkProof < 1) {
                                        try {
                                            $seeder = 'Database\\Seeders\\masterSeeders\\ProofTableSeeder';
                                            Artisan::call('db:seed', ['--class' => $seeder]);
                                        } catch (Exception $e) {
                                            Log::error($e);
                                        }
                                    }
                                } else {
                                    // -----insert income entry code start--------
                                    // get invoice payment entries
                                    $payments = DB::connection('dynamic_connection')
                                        ->table('payment_details')
                                        ->where('is_deleted', 0)
                                        ->get();

                                    if ($payments->isNotEmpty()) {

                                        foreach ($payments as $payment) {

                                            // check if income already exists for this payment
                                            $incomeExists = DB::connection('dynamic_connection')
                                                ->table('incomes')
                                                ->where('income_id', $payment->inv_id)
                                                ->where('income_details_id', $payment->id)
                                                ->exists();

                                            $invoice = DB::connection('dynamic_connection')
                                                ->table('invoices')
                                                ->where('id', $payment->inv_id)
                                                ->first();

                                            if (!$incomeExists && ($invoice != null)) {
                                                $description = "Invoice:$invoice->inv_no, Payment Received <br> $payment->description";
                                                $income = [
                                                    'income_id' => $payment->inv_id,
                                                    'income_details_id' => $payment->id,
                                                    'description' => $description,
                                                    'amount' => $payment->paid_amount + $payment->tds_amount,
                                                    'payment_type' => $payment->paid_type,
                                                    'paid_by' => $payment->paid_by,
                                                    'date' => $payment->datetime ?? $payment->created_at,
                                                    'entry_type' => 'a',
                                                    'created_by' => $payment->created_by ?? $this->userId
                                                ];

                                                $savedIncomeId = DB::connection('dynamic_connection')
                                                    ->table('incomes')
                                                    ->insertGetId($income);

                                                $receiptNo = "REC/" . $savedIncomeId;

                                                DB::connection('dynamic_connection')
                                                    ->table('incomes')
                                                    ->where('id', $savedIncomeId)
                                                    ->update([
                                                        'receipt_no' => $receiptNo
                                                    ]);

                                                $ledger = [
                                                    'payment_id' => $savedIncomeId,
                                                    'description' => $description,
                                                    'credited' => $payment->paid_amount + $payment->tds_amount,
                                                    'type' => 'income',
                                                    'subtype' => 'invoice payment',
                                                    'paid_by' => $payment->paid_by,
                                                    'date' => $payment->datetime ?? $payment->created_at,
                                                    'created_by' => $payment->created_by ?? $this->userId
                                                ];

                                                DB::connection('dynamic_connection')
                                                    ->table('ledgers')
                                                    ->insert($ledger);
                                            }
                                        }
                                    }
                                    // -------insert income entry code end--------

                                    // -------insert expense entry code start-------
                                    // get invoice commissions entries
                                    $commissions = DB::connection('dynamic_connection')
                                        ->table('invoice_commissions')
                                        ->where('is_deleted', 0)
                                        ->get();

                                    // add commission entry to expense and ledger    
                                    if ($commissions->isNotEmpty()) {

                                        foreach ($commissions as $commission) {

                                            $invoice = DB::connection('dynamic_connection')
                                                ->table('invoices')
                                                ->where('id', $commission->invoice_id)
                                                ->first();

                                            // check if expense already exists for this payment
                                            $expenseExists = DB::connection('dynamic_connection')
                                                ->table('expenses')
                                                ->where('expense_id', $commission->invoice_id)
                                                ->where('expense_details_id', $commission->id)
                                                ->where('subtype', 'invoice commission')
                                                ->exists();

                                            if (!$expenseExists && ($invoice != null)) {

                                                $paidTo = DB::connection('dynamic_connection')
                                                    ->table('customers')
                                                    ->where('id', $commission->commission_party_id)
                                                    ->select('firstname', 'lastname', 'company_name')
                                                    ->first();

                                                $paid_to = $paidTo->company_name
                                                    ?? trim($paidTo->firstname . ' ' . $paidTo->lastname);

                                                $description = "invoice:$invoice->inv_no, Paid Commission <br> $commission->description";

                                                $expense = [
                                                    'expense_id' => $commission->invoice_id,
                                                    'expense_details_id' => $commission->id,
                                                    'description' => $description,
                                                    'amount' => $commission->commission,
                                                    'payment_type' => 'Online',
                                                    'paid_to' => $paid_to,
                                                    'date' => $commission->created_at ?? now(),
                                                    'entry_type' => 'a',
                                                    'subtype' => 'invoice commission',
                                                    'created_by' => $commission->created_by ?? $this->userId
                                                ];

                                                $savedExpenseId = DB::connection('dynamic_connection')
                                                    ->table('expenses')
                                                    ->insertGetId($expense);

                                                $voucherNo = "VOU/" . $savedExpenseId;

                                                DB::connection('dynamic_connection')
                                                    ->table('expenses')
                                                    ->where('id', $savedExpenseId)
                                                    ->update([
                                                        'voucher_no' => $voucherNo
                                                    ]);

                                                $ledger = [
                                                    'payment_id' => $savedExpenseId,
                                                    'description' =>  $description,
                                                    'debited' => $commission->commission,
                                                    'type' => 'expense',
                                                    'subtype' => 'invoice commission',
                                                    'paid_to' => $paid_to,
                                                    'date' => $commission->created_at ?? now(),
                                                    'created_by' => $commission->created_by ?? $this->userId
                                                ];

                                                DB::connection('dynamic_connection')
                                                    ->table('ledgers')
                                                    ->insert($ledger);
                                            }
                                        }
                                    }


                                    // get transporter billing payment entries
                                    $transporterBillingPayments = DB::connection('dynamic_connection')
                                        ->table('transporter_billing_payment')
                                        ->where('is_deleted', 0)
                                        ->get();

                                    // add commission entry to expense and ledger    
                                    if ($transporterBillingPayments->isNotEmpty()) {

                                        foreach ($transporterBillingPayments as $transporterBillingPayment) {

                                            // check if expense already exists for this payment
                                            $expenseExists = DB::connection('dynamic_connection')
                                                ->table('expenses')
                                                ->where('expense_id', $transporterBillingPayment->transporter_billing_id)
                                                ->where('expense_details_id', $transporterBillingPayment->id)
                                                ->where('subtype', 'transporter bill')
                                                ->exists();

                                            if (!$expenseExists && ($invoice != null)) {

                                                $transporterBilling = DB::connection('dynamic_connection')
                                                    ->table('transporter_billing')
                                                    ->where('id', $transporterBillingPayment->transporter_billing_id)
                                                    ->first();

                                                $description = "Transporter Bills:$transporterBilling->bill_no, Payment Paid <br> $transporterBillingPayment->remarks";

                                                $expense = [
                                                    'expense_id' => $transporterBillingPayment->transporter_billing_id,
                                                    'expense_details_id' => $transporterBillingPayment->id,
                                                    'description' => $description,
                                                    'amount' => $transporterBillingPayment->paid_amount,
                                                    'payment_type' => $transporterBillingPayment->paid_type,
                                                    'paid_to' => $transporterBillingPayment->paid_by,
                                                    'date' => $transporterBillingPayment->datetime ?? $transporterBillingPayment->created_at,
                                                    'entry_type' => 'a',
                                                    'subtype' => 'transporter bill',
                                                    'created_by' => $transporterBillingPayment->created_by ?? $this->userId
                                                ];

                                                $savedExpenseId = DB::connection('dynamic_connection')
                                                    ->table('expenses')
                                                    ->insertGetId($expense);

                                                $voucherNo = "VOU/" . $savedExpenseId;

                                                DB::connection('dynamic_connection')
                                                    ->table('expenses')
                                                    ->where('id', $savedExpenseId)
                                                    ->update([
                                                        'voucher_no' => $voucherNo
                                                    ]);

                                                $ledger = [
                                                    'payment_id' => $savedExpenseId,
                                                    'description' =>  $description,
                                                    'debited' => $transporterBillingPayment->paid_amount,
                                                    'type' => 'expense',
                                                    'subtype' => 'transporter bill',
                                                    'paid_to' => $transporterBillingPayment->paid_by,
                                                    'date' => $transporterBillingPayment->datetime ?? $transporterBillingPayment->created_at,
                                                    'created_by' => $transporterBillingPayment->created_by ?? $this->userId
                                                ];

                                                DB::connection('dynamic_connection')
                                                    ->table('ledgers')
                                                    ->insert($ledger);
                                            }
                                        }
                                    }

                                    // -------insert expense entry code end-------
                                }

                                break;
                            case 'v4_4_0':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);

                                        if (!isset($jsonrp['adminmodule']['package'])) {
                                            $jsonrp['adminmodule']['package'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['adminmodule']['subscription'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['hrmodule']['letter_variable_setting'])) {
                                            $jsonrp['hrmodule']['letter_variable_setting'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['hrmodule']['generate_letter'])) {
                                            $jsonrp['hrmodule']['generate_letter'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        $updatedRpJson = json_encode($jsonrp);
                                        DB::connection('dynamic_connection')
                                            ->table('user_permissions')
                                            ->where('user_id', $userrp->user_id)
                                            ->update(['rp' => $updatedRpJson]);
                                    }
                                }

                                if ($company->id != 1) {
                                    $seederPaths = [
                                        'Database\\Seeders\\individual\\letter_variable_settingsSeeder',
                                    ];
                                    foreach ($seederPaths as $seederClass) {
                                        try {
                                            Artisan::call('db:seed', [
                                                '--class' => $seederClass,
                                                '--database' => 'dynamic_connection',
                                                '--force'  => true,
                                            ]);
                                            Log::info("Seeder output for $seederClass: " . Artisan::output());
                                        } catch (Exception $e) {
                                            Log::error("Seeder failed for $seederClass: " . $e->getMessage());
                                        }
                                    }
                                    // ---- Step 1: Update receipt_no for ALL incomes ----
                                    $incomes = DB::connection('dynamic_connection')
                                        ->table('incomes')
                                        ->orderBy('id')
                                        ->get();

                                    foreach ($incomes as $income) {
                                        DB::connection('dynamic_connection')
                                            ->table('incomes')
                                            ->where('id', $income->id)
                                            ->update([
                                                'receipt_no' => "REC/$income->id",
                                            ]);
                                    }

                                    // ---- Step 2: Update bill_no for ALL expenses ----
                                    $expenses = DB::connection('dynamic_connection')
                                        ->table('expenses')
                                        ->orderBy('id')
                                        ->get();

                                    foreach ($expenses as $expense) {
                                        DB::connection('dynamic_connection')
                                            ->table('expenses')
                                            ->where('id', $expense->id)
                                            ->update([
                                                'bill_no' => "BILL/$expense->id",
                                            ]);
                                    }

                                    // ---- Step 3: Reassign ALL voucher_no uniquely based on created_at ----

                                    // Get ALL incomes
                                    $allIncomes = DB::connection('dynamic_connection')
                                        ->table('incomes')
                                        ->orderBy('created_at')
                                        ->orderBy('id')
                                        ->get()
                                        ->map(function ($item) {
                                            $item->table = 'incomes';
                                            return $item;
                                        });

                                    // Get ALL expenses
                                    $allExpenses = DB::connection('dynamic_connection')
                                        ->table('expenses')
                                        ->orderBy('created_at')
                                        ->orderBy('id')
                                        ->get()
                                        ->map(function ($item) {
                                            $item->table = 'expenses';
                                            return $item;
                                        });

                                    // Merge both and sort chronologically by created_at then id
                                    $allRecords = $allIncomes
                                        ->concat($allExpenses)
                                        ->sortBy(function ($item) {
                                            return $item->created_at . '_' . str_pad($item->id, 10, '0', STR_PAD_LEFT);
                                        })
                                        ->values();

                                    // Assign unique voucher_no starting from VOU/1
                                    $voucherNo = 1;
                                    foreach ($allRecords as $record) {
                                        DB::connection('dynamic_connection')
                                            ->table($record->table)
                                            ->where('id', $record->id)
                                            ->update([
                                                'voucher_no' => "VOU/$voucherNo",
                                            ]);
                                        $voucherNo++;
                                    }
                                }

                                break;
                            case 'v4_4_1':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    // update user permissions
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);
                                        // Encode updated permissions back to JSON
                                        if (!isset($jsonrp['adminmodule']['package'])) {
                                            $jsonrp['adminmodule']['package'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                            $jsonrp['adminmodule']['subscription'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['hrmodule']['letter_variable_setting'])) {
                                            $jsonrp['hrmodule']['letter_variable_setting'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['hrmodule']['generate_letter'])) {
                                            $jsonrp['hrmodule']['generate_letter'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }

                                        $updatedRpJson = json_encode($jsonrp);
                                        // Update the database
                                        DB::connection('dynamic_connection')->table('user_permissions')
                                            ->where('user_id', $userrp->user_id)
                                            ->update(['rp' => $updatedRpJson]);
                                    }
                                }

                                break;
                            case 'v4_4_2':
                                $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                if ($rp) {
                                    // update user permissions
                                    foreach ($rp as $userrp) {
                                        $jsonrp = json_decode($userrp->rp, true);
                                        // Encode updated permissions back to JSON
                                        if (!isset($jsonrp['accountmodule']['category'])) {
                                            $jsonrp['accountmodule']['category'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['accountmodule']['accountformsetting'])) {
                                            $jsonrp['accountmodule']['accountformsetting'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        if (!isset($jsonrp['accountmodule']['accountcustomer'])) {
                                            $jsonrp['accountmodule']['accountcustomer'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                        }
                                        $updatedRpJson = json_encode($jsonrp);
                                        // Update the database
                                        DB::connection('dynamic_connection')->table('user_permissions')
                                            ->where('user_id', $userrp->user_id)
                                            ->update(['rp' => $updatedRpJson]);
                                    }
                                }
                                if ($company->id != 1) {

                                    // add default account other settings if not exists
                                    if (Schema::connection('dynamic_connection')->hasTable('account_other_settings')) {
                                        $existingData = DB::connection('dynamic_connection')
                                            ->table('account_other_settings')
                                            ->count();

                                        if ($existingData == 0) {
                                            $incomeDropdown = json_encode(["account"]);
                                            $expenseDropdown = json_encode(["account"]);
                                            $setDefaultSettings = DB::connection('dynamic_connection')
                                                ->table('account_other_settings')
                                                ->insert([
                                                    'expense_customer_dropdown' => $expenseDropdown,
                                                    'income_customer_dropdown' => $incomeDropdown,
                                                    'created_by' => $this->userId,
                                                ]);
                                        }
                                    }

                                    $expenses = DB::connection('dynamic_connection')
                                        ->table('expenses')
                                        ->orderBy('id')
                                        ->get();

                                    foreach ($expenses as $expense) {

                                        $reference_no = null;

                                        // Case 1: subtype is like 'invoice commission' → use expense_details_id directly
                                        if (!empty($expense->subtype) && stripos($expense->subtype, 'invoice commission') !== false) {
                                            $reference_no = $expense->expense_details_id;
                                        }

                                        // Case 2: type is 'transporter bill' → lookup receipt_number from transporter_billing_payment
                                        elseif (!empty($expense->subtype) && strtolower($expense->subtype) == 'transporter bill') {
                                            $payment = DB::connection('dynamic_connection')
                                                ->table('transporter_billing_payment')
                                                ->where('id', $expense->expense_details_id)
                                                ->first();

                                            if ($payment) {
                                                $billing = DB::connection('dynamic_connection')
                                                    ->table('transporter_billing_payment')
                                                    ->where('id', $payment->id)
                                                    ->first();

                                                $reference_no = $billing->receipt_number ?? null;
                                            }
                                        }

                                        // Case 3: subtype is null → use voucher_no
                                        elseif (is_null($expense->subtype)) {
                                            $reference_no = $expense->voucher_no;
                                        }

                                        // Update only if reference_no resolved
                                        if (!is_null($reference_no)) {
                                            DB::connection('dynamic_connection')
                                                ->table('expenses')
                                                ->where('id', $expense->id)
                                                ->update(['reference_no' => $reference_no]);
                                        }
                                    }
                                    $incomes = DB::connection('dynamic_connection')
                                        ->table('incomes')
                                        ->orderBy('id')
                                        ->get();
                                    foreach ($incomes as $income) {

                                        $reference_no = null;

                                        if (!empty($income->entry_type) && stripos($income->entry_type, 'm') !== false) {
                                            $reference_no = $income->voucher_no;
                                        }

                                        // Case 2: type is 'transporter bill' → lookup receipt_number from transporter_billing_payment
                                        elseif (!empty($income->entry_type) && strtolower($income->entry_type) == 'a') {
                                            $payment = DB::connection('dynamic_connection')
                                                ->table('payment_details')
                                                ->where('id', $income->income_details_id)
                                                ->first();

                                            if ($payment) {
                                                $billing = DB::connection('dynamic_connection')
                                                    ->table('payment_details')
                                                    ->where('id', $payment->id)
                                                    ->first();

                                                $reference_no = $billing->receipt_number ?? null;
                                            }
                                        }

                                        // Case 3: subtype is null → use voucher_no
                                        elseif (is_null($income->entry_type)) {
                                            $reference_no = $income->voucher_no;
                                        }

                                        // Update only if reference_no resolved
                                        if (!is_null($reference_no)) {
                                            DB::connection('dynamic_connection')
                                                ->table('incomes')
                                                ->where('id', $income->id)
                                                ->update(['reference_no' => $reference_no]);
                                        }
                                    }
                                    $ledgers = DB::connection('dynamic_connection')
                                        ->table('ledgers')
                                        ->orderBy('id')
                                        ->get();

                                    foreach ($ledgers as $ledger) {

                                        $referenceNo = null;

                                        if ($ledger->type == 'expense') {
                                            $expense = DB::connection('dynamic_connection')
                                                ->table('expenses')
                                                ->where('id', $ledger->payment_id)
                                                ->first();

                                            $referenceNo = $expense->reference_no ?? null;
                                        } elseif ($ledger->type == 'income') {
                                            $income = DB::connection('dynamic_connection')
                                                ->table('incomes')
                                                ->where('id', $ledger->payment_id)
                                                ->first();

                                            $referenceNo = $income->reference_no ?? null;
                                        }

                                        if (!is_null($referenceNo)) {
                                            DB::connection('dynamic_connection')
                                                ->table('ledgers')
                                                ->where('id', $ledger->id)
                                                ->update([
                                                    'reference_no' => $referenceNo,
                                                ]);
                                        }
                                    }
                                }
                                break;

                            case 'v4_4_3':
                                // $rp = DB::connection('dynamic_connection')->table('user_permissions')->get();
                                // if ($rp) {
                                //     // update user permissions
                                //     foreach ($rp as $userrp) {
                                //         $jsonrp = json_decode($userrp->rp, true);
                                //         // Encode updated permissions back to JSON
                                //         if (!isset($jsonrp['adminmodule']['package'])) {
                                //             $jsonrp['adminmodule']['package'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                //             $jsonrp['adminmodule']['subscription'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                //         }
                                //         if (!isset($jsonrp['hrmodule']['letter_variable_setting'])) {
                                //             $jsonrp['hrmodule']['letter_variable_setting'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                //         }
                                //         if (!isset($jsonrp['hrmodule']['generate_letter'])) {
                                //             $jsonrp['hrmodule']['generate_letter'] = ["show" => 0, "add" => 0, "view" => 0, "edit" => 0, "delete" => 0, "alldata" => 0];
                                //         }


                                //         $updatedRpJson = json_encode($jsonrp);
                                //         // Update the database
                                //         DB::connection('dynamic_connection')->table('user_permissions')
                                //             ->where('user_id', $userrp->user_id)
                                //             ->update(['rp' => $updatedRpJson]);
                                //     }
                                // }

                                break;
                        }

                        $company->app_version = $request->version;
                        $company->save();

                        return $this->successresponse(200, 'message', 'Company version succesfully updated');
                    });
                } else {
                    return $this->successresponse(500, 'message', 'This company is already in latest version.');
                }
            } else {
                return $this->successresponse(404, 'message', 'No such company found!');
            }
        }
    }
}
