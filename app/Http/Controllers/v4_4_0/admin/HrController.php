<?php

namespace App\Http\Controllers\v4_4_0\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

use function Symfony\Component\String\s;

class HrController extends Controller
{
    public $version, $employeeModel;
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();
        if (isset($_SESSION['folder_name'])) {
            $this->version = $_SESSION['folder_name'];
            $this->employeeModel = 'App\\Models\\' . $this->version . "\\employee";
        } else {
            $this->employeeModel = 'App\\Models\\v4_4_0\\employee';
        }
    }
    public function index(Request $request)
    {

        if (isset($request->search)) {
            $search = $request->search;
        } else {
            $search = '';
        }

        return view($this->version . '.admin.Employee.employee', ["search" => $search]);
    }
    public function create()
    {
        return view($this->version . '.admin.Employee.employeeform', ['company_id' => Session::get('company_id')]);
    }
    public function edit($id)
    {
        return view($this->version . '.admin.Employee.employeeupdateform', compact('id'));
    }
    public function companiesholidays()
    {
        return view($this->version . '.admin.Employee.companiesholidays');
    }
    public function letter()
    {
        return view($this->version . '.admin.letter.letter');
    }
    public function editletter($id)
    {
        return view($this->version . '.admin.letter.letterupdateform', ['edit_id' => $id]);
    }
    public function letter_variable_setting()
    {
        return view($this->version . '.admin.letter.letter_variable_setting');
    }
    public function cerate_letter_variable_setting()
    {
        return view($this->version . '.admin.letter.letter_variable_settingform');
    }
    public function edit_letter_variable_setting($id)
    {
        if ($id <= 10) {
            return redirect()->route('admin.letter_variable_setting');
        }

        return view($this->version . '.admin.letter.letter_variable_settingupdateform', ['edit_id' => $id]);
        return view($this->version . '.admin.Employee.letter');
    }
    public function generateletter()
    {
        return view($this->version . '.admin.Generateletter.generateletter');
    }
    public function cerate_generateletter(Request $request)
    {
        return view($this->version . '.admin.Generateletter.generateletterform', ['user_id' => Session::get('user_id'), 'company_id' => Session::get('company_id')]);
    }
    public function generateletter_session(Request $request)
    {
        $HrControllerController = "App\\Http\\Controllers\\" . $this->version . "\\api\\HrController";

        $jsonempdetails = app($HrControllerController)->edit($request->employee_id);
        $empcontent = $jsonempdetails->getContent();
        $empdetails = json_decode($empcontent);

        $jsonletterdetails = app($HrControllerController)->letteredit($request->letter_id);
        $lettercontent = $jsonletterdetails->getContent();
        $letterdetails = json_decode($lettercontent);

        if ($letterdetails->status != 200) {
            return response()->json([
                'status' => 400,
                'message' => 'Letter not found'
            ]);
        }

        if ($empdetails->status != 200) {
            return response()->json([
                'status' => 400,
                'message' => 'Employee not found'
            ]);
        }

        session([
            'employee_details' => $empdetails->data ?? null,
            'letter_details' => $letterdetails->data ?? null,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Session stored successfully'
        ]);
    }
    public function edit_generateletter($id)
    {
        return view($this->version . '.admin.Generateletter.generateletterupdateform', ['edit_id' => $id, 'user_id' => Session::get('user_id'), 'company_id' => Session::get('company_id')]);
    }
}
