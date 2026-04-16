<?php

namespace App\Http\Controllers\v4_4_1\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InvoiceCommissionPartyController extends Controller
{
    public $version;
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();
        if (isset($_SESSION['folder_name'])) {
            $this->version = $_SESSION['folder_name'];
        } else {
            $this->version = "v4_3_2";
        }
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view($this->version . '.admin.InvoiceCommissionParty.commissionparty');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view($this->version . '.admin.InvoiceCommissionParty.commissionpartyform', ['user_id' => session('user_id'), 'company_id' =>  session('company_id')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view($this->version . '.admin.InvoiceCommissionParty.commissionpartyupdateform', ['user_id' => session('user_id'), 'company_id' =>  session('company_id'), 'edit_id' => $id]);
    }
}
