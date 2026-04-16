<?php

namespace App\Http\Controllers\v4_4_3\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public $version;
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();

        if (isset($_SESSION['folder_name'])) {
            $this->version = $_SESSION['folder_name'];
        } else {
            $this->version = 'v4_4_1';
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view($this->version . '.admin.Package.package');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view($this->version . '.admin.Package.packageform');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view($this->version . '.admin.Package.packageupdateform', ['edit_id' => $id]);
    }
}
