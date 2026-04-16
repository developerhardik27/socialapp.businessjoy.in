<?php

namespace App\Http\Controllers\v4_4_4\admin;

use App\Http\Controllers\Controller;
// use Barryvdh\DomPDF\PDF;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use function Symfony\Component\String\s;

class HrController extends Controller
{
    public $version, $employeeModel, $letterModel;
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();
        if (isset($_SESSION['folder_name'])) {
            $this->version = $_SESSION['folder_name'];
            $this->employeeModel = 'App\\Models\\' . $this->version . "\\employee";
            $this->letterModel = 'App\\Models\\' . $this->version . "\\letter";
        } else {
            $this->employeeModel = 'App\\Models\\v4_4_1\\employee';
            $this->letterModel = 'App\\Models\\v4_4_1\\letter';
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
    public function holidays()
    {
        return view($this->version . '.admin.Employee.holidays');
    }
    public function calendar()
    {
        return view($this->version . '.admin.Employee.calendar');
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
    public function letterfomateview(Request $request, $id)
    {
        $company_id = session('company_id');
        $user_id    = session('user_id');

        // Merge values into request
        $request->merge([
            'company_id' => $company_id,
            'user_id'    => $user_id,
            'letter_id'  => $id
        ]);

        $HrControllerController = "App\\Http\\Controllers\\" . $this->version . "\\api\\HrController";

        $jsonletterdetails = app($HrControllerController)->letteredit($id);

        $lettercontent = $jsonletterdetails->getContent();
        $letterdetails = json_decode($lettercontent);

        if ($letterdetails->status != 200) {
            return response()->json([
                'status' => 400,
                'message' => 'Letter not found'
            ]);
        }
        $letterdetails = $letterdetails->data;

        $options = [
            'isPhpEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'margin_top' => 0,
            'margin_right' => 0,
            'margin_bottom' => 0,
            'margin_left' => 0,
            'defaultFont' => 'Helvetica'
        ];
        //this for view in html templete and (admin.letter.letterformatview) in this file image path where public that this to change asset and public comment 
        // return view($this->version . '.admin.letter.letterformatview', compact('letterdetails'));
        $pdf = PDF::setOptions($options)->loadView($this->version . '.admin.letter.letterformatview', compact('letterdetails'))->setPaper('a4', 'portrait');

        $name =  $letterdetails->letter_name .' - Letter Format Preview'.'.pdf';

        return $pdf->stream($name);
    }
    public function preview(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('header_image')) {

            $file = $request->file('header_image');
            $type = $file->getMimeType();
            $base64 = base64_encode(file_get_contents($file->getRealPath()));

            $data['header_image'] = "data:$type;base64,$base64";
        } 
        elseif (!empty($request->header_image)) {

            $data['header_image'] = public_path($request->header_image);
        }


        if ($request->hasFile('footer_image')) {

            $file = $request->file('footer_image');
            $type = $file->getMimeType();
            $base64 = base64_encode(file_get_contents($file->getRealPath()));

            $data['footer_image'] = "data:$type;base64,$base64";
        } 
        elseif (!empty($request->footer_image)) {

            $data['footer_image'] = public_path($request->footer_image);
        }
        // dd($data);
        $pdf = Pdf::loadView(
            $this->version . '.admin.letter.letterformatview',
            ['letterdetails' => (object)$data]
        );

        return $pdf->stream('preview.pdf');
    }
}
