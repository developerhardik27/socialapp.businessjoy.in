<?php

namespace App\Http\Controllers\v4_4_4\api;

use Throwable;
use App\Models\company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class commonController extends Controller
{
    public $companyVersion;

    public function dbname($id = null)
    {
        if ($id == null) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 500,
                'message' => 'Database Not Found'
            ]);
            die();
        }
        $dbname = company::find($id);

        $this->companyVersion = $dbname->app_version;
        config(['database.connections.dynamic_connection.database' => $dbname->dbname]);

        // Establish connection to the dynamic database
        DB::purge('dynamic_connection');
        DB::reconnect('dynamic_connection');

        return true;
    }

    public function customerrorresponse()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 403,
            'message' => 'Forbidden!'
        ]);
        die();
    }

    public function getmodel($model)
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();
        if (isset($_SESSION['folder_name'])) {
            $version = $_SESSION['folder_name'];
            return 'App\\Models\\' . $version . "\\" . $model;
        } else {
            return 'App\\Models\\v4_4_4\\' . $model;
        }
    }
 
    public function returnresponse()
    {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function successresponse($status, $statuskey, $message, $extrakey = null, $extrakeyvalue = null, $code = 200)
    {

        $response = [
            'status' => $status,
            $statuskey => $message
        ];
        if (isset($extrakey) && isset($extrakeyvalue)) {
            $response[$extrakey] = $extrakeyvalue;
        }

        return response()->json($response, $code);
    }

    public function errorresponse($status, $errorsdata, $code = 422)
    {
        if ($status == 422) {
            $response = [
                'status' => $status,
                'errors' => $errorsdata
            ];
            return response()->json($response, $code);
        }
        

        $response = [
            'status' => $status,
            "message" => $errorsdata
        ];

        return response()->json($response, 200);
    }


    protected function executeTransaction(callable $callback)
    {
        $defaultDb = DB::connection();
        $dynamicDb = DB::connection('dynamic_connection');

        try {
            $defaultDb->beginTransaction();
            $dynamicDb->beginTransaction();

            $result = $callback();

            // Commit in reverse order
            $dynamicDb->commit();
            $defaultDb->commit();

            return $result;
        } catch (Throwable $e) {

            // Rollback only if active
            if ($dynamicDb->transactionLevel() > 0) {
                $dynamicDb->rollBack();
            }

            if ($defaultDb->transactionLevel() > 0) {
                $defaultDb->rollBack();
            }

            Log::error('Transaction Rolled Back', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            throw $e; // always rethrow
        }
    }
}
