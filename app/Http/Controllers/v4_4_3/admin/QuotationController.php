<?php

namespace App\Http\Controllers\v4_4_3\admin;

use App\Http\Controllers\Controller;
use App\Models\company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class QuotationController extends Controller
{

    public $version, $quotationModel;
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();
        if (isset($_SESSION['folder_name'])) {
            $this->version = $_SESSION['folder_name'];
            $this->quotationModel = 'App\\Models\\' . $this->version . "\\quotation";
        } else {
            $this->quotationModel = 'App\\Models\\v4_4_1\\quotation';
        }
    }

    /**
     * quotation settings pages.
     */
    public function managecolumn()
    {
        return view($this->version . '.admin.Quotation.quotationmanagecolumn', ['user_id' => Session::get('user_id'), 'company_id' => Session::get('company_id')]);
    }
    public function formula()
    {
        return view($this->version . '.admin.Quotation.quotationformula', ['user_id' => Session::get('user_id'), 'company_id' => Session::get('company_id')]);
    }
    public function othersettings()
    {
        return view($this->version . '.admin.Quotation.quotationothersettings', ['user_id' => Session::get('user_id'), 'company_id' => Session::get('company_id')]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        session(['menu' => 'quotation']);
        session(['type' => 'normal']); 
        if (isset($request->search)) {
            $search = $request->search;
        } else {
            $search = '';
        }

        return view($this->version . '.admin.Quotation.quotation', ['search' => $search, 'user_id' => Session::get('user_id'), 'company_id' => Session::get('company_id')]);
    }



    /**
     * Show the form for creating a new resource.
     */

    public function duplicate(Request $request)
    {
        $company_id = session('company_id');
        $duplicate_id = $request->duplicate_id; // ID to duplicate

        if (!$duplicate_id) {
            return redirect()->route('admin.quotation')
                ->with('message', 'No data available for duplication.');
        }

        // Build controller namespace
        $quotationController = "App\\Http\\Controllers\\" . $this->version . "\\api\\quotationController";

        // Get quotation data from edit method
        $datadetails = json_decode(app($quotationController)->edit($duplicate_id)->getContent());

        if (!isset($datadetails) || $datadetails->status != 200) {
            return redirect()->route('admin.quotation')
                ->with('message', 'No data available for duplication.');
        }

        // Store only necessary data in session for duplication
        session([
            'quotation_duplicate_data' => $datadetails,
            'type' => 'duplicate',
        ]);

        return redirect()->route('admin.addquotation');
    }


    public function create(Request $request)
    {
        session(['menu' => 'quotation']);
        $company_id = session('company_id');
        $user_id = session('user_id');
        request()->merge([
            'company_id' => session('company_id'),
            'user_id'    => session('user_id'),
        ]);

        $company_id = session('company_id');

        $quotationcolumnController = "App\\Http\\Controllers\\" . $this->version . "\\api\\tblquotationcolumnController";
        $columndetails = json_decode(app($quotationcolumnController)->column_details($company_id)->getContent());

        if ($columndetails->status != 200) {
            return view($this->version . '.admin.Quotation.quotationmanagecolumn', [
                'user_id'    => $request->user_id,
                'company_id' => $company_id,
                'message'    => 'yes',
            ]);
        }

        $quotationothersettingController = "App\\Http\\Controllers\\" . $this->version . "\\api\\tblquotationothersettingController";
        $quotationothersettingdetails = json_decode(app($quotationothersettingController)->quotationnumberpatternindex($company_id)->getContent());

        if ($quotationothersettingdetails->status != 200 || count($quotationothersettingdetails->pattern) < 2) {
            return view($this->version . '.admin.Quotation.quotationothersettings', [
                'user_id'    => $request->user_id,
                'company_id' => $company_id,
                'message'    => 'yes',
            ]);
        }

        if (session('type') === 'duplicate') {
            $duplicateData = session()->pull('quotation_duplicate_data'); // Get and remove from session

            if (!$duplicateData) {
                // Duplicate data is missing → redirect back with message
               session(['type' => 'normal']);
                return redirect()->route('admin.quotation')->with('message', 'Duplicate data is not available or has expired.');
            }

            // Duplicate data exists → show form with data
            session(['type' => 'duplicate']); // set type for view logic
            return view($this->version . '.admin.Quotation.quotationform', [
                'user_id'       => $user_id,
                'company_id'    => $company_id,
                'duplicateData' => $duplicateData,
            ]);
        }

        // ─── Normal form view ─────────────────────────────────────────────
        session(['type' => 'normal']);
        return view($this->version . '.admin.Quotation.quotationform', [
            'user_id'       => $user_id,
            'company_id'    => $company_id,
            'duplicateData' => null,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $quotation = $this->quotationModel::findOrFail($id);

        $is_editable = $quotation->is_editable;

        return view($this->version . '.admin.Quotation.quotationupdateform', ['edit_id' => $id, 'user_id' => Session::get('user_id'), 'company_id' => Session::get('company_id'), 'is_editable' => $is_editable]);
    }
    public function createInvoice(Request $request)
    {
        $company_id = session('company_id');
        $quotation_id = $request->quotation_id; // ID to duplicate
       
        if (!$quotation_id) {
            return redirect()->route('admin.quotation')
                ->with('message', 'No data available for invoice creation.');
        }

        // Build controller namespace
        $quotationController = "App\\Http\\Controllers\\" . $this->version . "\\api\\quotationController";

        // Get quotation data from edit method
        $datadetails = json_decode(app($quotationController)->edit($quotation_id)->getContent());

        if (!isset($datadetails) || $datadetails->status != 200) {
            return redirect()->route('admin.quotation')
                ->with('message', 'No data available for invoice creation.');
        }

        // Store only necessary data in session for duplication
        session([
            'invoice_data' => $datadetails,
            'type' => 'invoice_creation',
        ]);
        session(['type' => 'invoice_creation']);
        
        return redirect()->route('admin.addinvoice');
    }
}
