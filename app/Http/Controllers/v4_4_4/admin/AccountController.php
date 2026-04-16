<?php

namespace App\Http\Controllers\v4_4_4\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AccountController extends Controller
{
    public $version;
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();
        if (isset($_SESSION['folder_name'])) {
            $this->version = $_SESSION['folder_name'];
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function expenseindex()
    {
        return view($this->version . '.admin.account.expense');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function expensecreate()
    {
        return view($this->version . '.admin.account.expenseform');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function expenseedit(string $id)
    {
        return view($this->version . '.admin.account.expenseupdateform', ['edit_id' => $id]);
    }

    /**
     * Display a listing of the resource.
     */
    public function incomeindex()
    {
        return view($this->version . '.admin.account.income');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function incomecreate()
    {
        return view($this->version . '.admin.account.incomeform');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function incomeedit(string $id)
    {
        return view($this->version . '.admin.account.incomeupdateform', ['edit_id' => $id]);
    }


    /**
     * Display a listing of the resource.
     */
    public function ledger()
    {
        return view($this->version . '.admin.account.ledger');
    }
    public function categoryindex()
    {
        return view($this->version . '.admin.account.category');
    }
    public function categorycreate()
    {
        return view($this->version . '.admin.account.categoryform');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function categoryedit(string $id)
    {
        return view($this->version . '.admin.account.categoryupdateform', ['edit_id' => $id]);
    }
    public function othersettings()
    {
        return view($this->version . '.admin.account.othersettings', ['user_id' => Session::get('user_id'), 'company_id' => Session::get('company_id')]);
    }
}
