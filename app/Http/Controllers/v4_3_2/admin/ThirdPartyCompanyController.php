<?php

namespace App\Http\Controllers\v4_3_2\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ThirdPartyCompanyController extends Controller
{
    public $version, $customerModel;
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();

        $this->version = "v4_3_2";

        if (isset($_SESSION['folder_name'])) {
            $this->version = $_SESSION['folder_name'];
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view($this->version . '.admin.ThirdPartyCompanies.thirdparty');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view($this->version . '.admin.ThirdPartyCompanies.thirdpartyform', ['user_id' => session('user_id'), 'company_id' => session('company_id')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view($this->version . '.admin.ThirdPartyCompanies.thirdpartyupdateform', ['company_id' => session('company_id'), 'user_id' => session('user_id'), 'edit_id' => $id]);
    }
}
