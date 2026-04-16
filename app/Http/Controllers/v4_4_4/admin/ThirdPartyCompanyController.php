<?php

namespace App\Http\Controllers\v4_4_4\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ThirdPartyCompanyController extends Controller
{
    public $version, $customerModel;
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();

        $this->version = "v4_4_1";

        if (isset($_SESSION['folder_name'])) {
            $this->version = $_SESSION['folder_name'];
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function quotationcompanyindex()
    {
        return view($this->version . '.admin.ThirdPartyCompanies.thirdparty');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function quotationcompanycreate()
    {
        return view($this->version . '.admin.ThirdPartyCompanies.thirdpartyform', ['user_id' => session('user_id'), 'company_id' => session('company_id')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function quotationcompanyedit(string $id)
    {
        return view($this->version . '.admin.ThirdPartyCompanies.thirdpartyupdateform', ['company_id' => session('company_id'), 'user_id' => session('user_id'), 'edit_id' => $id]);
    }
    public function invoicecompanyindex()
    {
        return view($this->version . '.admin.ThirdPartyCompanieInvoice.thirdparty');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function invoicecompanycreate()
    {
        return view($this->version . '.admin.ThirdPartyCompanieInvoice.thirdpartyform', ['user_id' => session('user_id'), 'company_id' => session('company_id')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function invoicompanyedit(string $id)
    {
        return view($this->version . '.admin.ThirdPartyCompanieInvoice.thirdpartyupdateform', ['company_id' => session('company_id'), 'user_id' => session('user_id'), 'edit_id' => $id]);
    }
}
