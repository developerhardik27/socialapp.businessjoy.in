<?php

namespace App\Http\Controllers\v4_4_2\admin;

use Exception;

use ZipArchive;
use App\Models\company;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use Barryvdh\DomPDF\Facade\Pdf;
// use Mpdf\Config\ConfigVariables;
// use Mpdf\Config\FontVariables;
// use Mpdf\Mpdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Dompdf\Options;

class PdfController extends Controller
{
   public $version, $invoiceModel, $paymentdetailsModel, $quotationModel, $consignor_copyModel;
   public function __construct()
   {
      if (session_status() !== PHP_SESSION_ACTIVE)
         session_start();
      if (isset($_SESSION['folder_name'])) {
         $this->version = $_SESSION['folder_name'];
         $this->invoiceModel = 'App\\Models\\' . $this->version . "\\invoice";
         $this->paymentdetailsModel = 'App\\Models\\' . $this->version . "\\payment_details";
         $this->quotationModel = 'App\\Models\\' . $this->version . "\\quotation";
         $this->consignor_copyModel = 'App\\Models\\' . $this->version . "\\consignor_copy";
      } else {
         $this->invoiceModel = 'App\\Models\\v4_4_1\\invoice';
         $this->paymentdetailsModel = 'App\\Models\\v4_4_1\\payment_details';
         $this->quotationModel = 'App\\Models\\v4_4_1\\quotation';
         $this->consignor_copyModel = 'App\\Models\\v4_4_1\\consignor_copyM';
      }
   }



   //this for testing
   public function generatepdf(string $id)
   {

      $dbname = company::find(Session::get('company_id'));
      config(['database.connections.dynamic_connection.database' => $dbname->dbname]);

      // Establish connection to the dynamic database
      DB::purge('dynamic_connection');
      DB::reconnect('dynamic_connection');

      $invoice = $this->invoiceModel::findOrFail($id);
      $this->authorize('view', $invoice);

      $data = $this->prepareDataForPDF($invoice);

      $options = [
         'isPhpEnabled' => true,
         'isHtml5ParserEnabled' => true,
         'isRemoteEnabled' => true,
         'margin_top' => 0,
         'margin_right' => 0,
         'margin_bottom' => 0,
         'margin_left' => 0,
         'defaultFont' => 'Helvetica'
      ];


      $companyname = $data['invdata']['firstname'] . $data['invdata']['lastname']; // if customer company name is not set
      if ($data['invdata']['company_name'] != '') {
         $companyname = $data['invdata']['company_name'];
      }

      // return view($this->version . '.admin.PDF.invoicetemplate', $data);
      $pdfname = $data['invdata']['inv_no'] . ' ' . $companyname . ' ' . date('d-M-y') . '.pdf';

      $pdf = PDF::setOptions($options)->loadView($this->version . '.admin.PDF.invoicetemplate', $data)->setPaper('a4', 'portrait');

      return $pdf->stream($pdfname);
   }
   // generate part partpayment single receipt (id is considering payment details id)
   public function generatereciept(string $id)
   {
      request()->merge([
         'company_id' => session('company_id'),
         'user_id' => session('user_id')
      ]);

      $dbname = company::find(Session::get('company_id'));
      config(['database.connections.dynamic_connection.database' => $dbname->dbname]);

      // Establish connection to the dynamic database
      DB::purge('dynamic_connection');
      DB::reconnect('dynamic_connection');

      $paymentdetail = $this->paymentdetailsModel::findOrFail($id);
      $invoice = $this->invoiceModel::findOrFail($paymentdetail->inv_id);
      $this->authorize('view', $invoice);

      $jsonproductdata = app('App\Http\Controllers\\' . $this->version . '\api\invoiceController')->inv_details($paymentdetail->inv_id);
      $jsoninvdata = app('App\Http\Controllers\\' . $this->version . '\api\invoiceController')->index($paymentdetail->inv_id);
      $jsonpaymentdata = app('App\Http\Controllers\\' . $this->version . '\api\PaymentController')->paymentdetailsforpdf($id);
      // $jsoncompanydetailsdata = app('App\Http\Controllers\\' . $this->version . '\api\companyController')->companydetailspdf($invoice->company_details_id);

      if ($invoice->third_party_invoice == 1) {
         $jsoncompanydetailsdata = app('App\Http\Controllers\\' . $this->version . '\api\thirdpartycompanyController')->invoicecompanycompanydetailspdf($invoice->company_id);
      } else {
         $jsoncompanydetailsdata = app('App\Http\Controllers\\' . $this->version . '\api\companyController')->companydetailspdf($invoice->company_details_id);
      }
      $jsonproductContent = $jsonproductdata->getContent();
      $jsonpaymentContent = $jsonpaymentdata->getContent();
      $jsoninvContent = $jsoninvdata->getContent();
      $jsoncompanyContent = $jsoncompanydetailsdata->getContent();

      // Decode the JSON data
      $productdata = json_decode($jsonproductContent, true);
      $paymentdata = json_decode($jsonpaymentContent, true);
      $invdata = json_decode($jsoninvContent, true);
      $companydetailsdata = json_decode($jsoncompanyContent, true);

      if ($productdata['status'] == 404) {
         session()->flash('custom_error_message', 'Product column not found');
         abort('404');
      }

      if ($paymentdata['status'] == 404) {
         session()->flash('custom_error_message', 'Payment data not found');
         abort('404');
      }

      if ($invdata['status'] == 404) {
         session()->flash('custom_error_message', 'Invoice data not found');
         abort('404');
      }

      if ($companydetailsdata['status'] == 404) {
         session()->flash('custom_error_message', 'Company details not found');
         abort('404');
      }

      $data = [
         'productscolumn' => $productdata['columnswithtype'],
         'products' => $productdata['invoice'],
         'othersettings' => $productdata['othersettings'][0],
         'payment' => $paymentdata['paymentdetail'],
         'invdata' => $invdata['invoice'][0],
         'companydetails' => $companydetailsdata['companydetails'][0],
      ];
      $options = [
         'isPhpEnabled' => true,
         'isHtml5ParserEnabled' => true,
         'margin_top' => 0,
         'margin_right' => 0,
         'margin_bottom' => 0,
         'margin_left' => 0,
         'defaultFont' => 'Helvetica'
      ];

      //return view($this->version . '.admin.PDF.paymentreciept', $data);
      $pdf = PDF::setOptions($options)->loadView($this->version . '.admin.PDF.paymentreciept', $data)->setPaper('a4', 'portrait');

      $name = 'Reciept ' . $paymentdata['paymentdetail'][0]['receipt_number'] . '.pdf';
      // return view($this->version . '.admin.paymentreciept', $data);
      return $pdf->stream($name);
   }

   /**
    * Summary of generaterecieptall - generate full payment history
    * @param string $id
    * @return \Illuminate\Http\Response
    */
   public function generaterecieptall(string $id)
   {

      $dbname = company::find(Session::get('company_id'));
      config(['database.connections.dynamic_connection.database' => $dbname->dbname]);

      // Establish connection to the dynamic database
      DB::purge('dynamic_connection');
      DB::reconnect('dynamic_connection');


      $invoice = $this->invoiceModel::findOrFail($id);
      $this->authorize('view', $invoice);



      $data = $this->prepareDataForPDF($invoice, 'paymentdetails');
      $options = [
         'isPhpEnabled' => true,
         'isHtml5ParserEnabled' => true,
         'margin_top' => 0,
         'margin_right' => 0,
         'margin_bottom' => 0,
         'margin_left' => 0,
      ];

      $pdf = PDF::setOptions($options)->loadView($this->version . '.admin.PDF.paymentpaidreciept', $data)->setPaper('a4', 'portrait');

      $name = 'Receipt ' . $data['payment'][0]['receipt_number'] . '.pdf';

      if (count($data['payment']) > 1) {
         $name = 'PaymentHistory ' . $data['invdata']['inv_no'] . '.pdf';
      }

      // return view($this->version . '.admin.paymentpaidreciept', $data);
      return $pdf->stream($name);
   }

   public function generatepdfzip(Request $request)
   {

      set_time_limit(120);
      try {
         // Your existing code for generating PDFs and creating the zip file
         $dbname = company::find(Session::get('company_id'));
         config(['database.connections.dynamic_connection.database' => $dbname->dbname]);

         // Establish connection to the dynamic database
         DB::purge('dynamic_connection');
         DB::reconnect('dynamic_connection');

         $user_rp = DB::connection('dynamic_connection')->table('user_permissions')->select('rp')->where('user_id', $request->user_id)->get();
         $permissions = json_decode($user_rp, true);
         $rp = json_decode($permissions[0]['rp'], true);
         $reportuserlist = $rp['reportmodule']['report']['alldata'];

         if (!$reportuserlist) {
            return response()->json([
               'status' => 'error',
               'message' => "You have not access to report any user's data"
            ]);
         }

         $startDate = $request->fromdate;
         $endDate = Carbon::parse($request->todate);

         $invoices = $this->invoiceModel::whereBetween('inv_date', [$startDate, $endDate->addDay()])
            ->where([
               'is_deleted' => 0,
            ])
            ->whereIn('created_by', [$reportuserlist])
            ->get();

         if (count($invoices) == 0) {
            return response()->json([
               'status' => 'error',
               'message' => 'Not any invoice exists between this  date'
            ]);
         }

         $tempDir = storage_path('app/temp_pdf');
         if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
         }

         foreach ($invoices as $invoice) {
            $data = $this->prepareDataForPDF($invoice);
            $pdf = PDF::loadView($this->version . '.admin.PDF.invoicetemplate', $data)->setPaper('a4', 'portrait');
            $pdfFileName = $invoice->inv_no . '_' . $invoice->company_name . '_' . $invoice->created_at->format('d-M-y') . '.pdf';
            $pdf->save($tempDir . '/' . $pdfFileName);
         }

         $withoutextensionzipFileName = 'invoices_' . date('Ymdhis');
         $zipFileName = $withoutextensionzipFileName . '.zip';
         $zip = new ZipArchive;
         if ($zip->open(storage_path('app/' . $zipFileName), ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $files = Storage::files('temp_pdf');
            foreach ($files as $file) {
               $zip->addFile(storage_path('app/' . $file), basename($file));
            }
            $zip->close();
         } else {
            throw new Exception('Unable to create zip file');
         }

         Storage::deleteDirectory('temp_pdf');
         DB::connection('dynamic_connection')->table('reportlogs')->insert([
            'module_name' => 'invoice',
            'from_date' => $request->fromdate,
            'to_date' => $request->todate,
            'created_by' => $request->user_id,
         ]);

         return response()->json([
            'status' => 'success',
            'zipFileName' => route('file.download', $withoutextensionzipFileName) // Return the URL for downloading
         ]);
      } catch (Exception $e) {
         // Log the error
         Log::error($e->getMessage());

         return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong while creating the zip file'
         ]);
      }
   }

   public function generateconsignorcopypdf(Request $request, int $id)
   {
      if (!$request->copies) {
         abort(404, 'invalid url');
      }
      // Convert the comma-separated string into an array and check if any value is invalid
      $copies = array_map('strtolower', explode(',', $request->copies));

      if (count($copies) > 3) {
         abort(404, 'invalid url');
      }

      foreach ($copies as $copy) {
         if (!in_array($copy, ['consignor', 'consignee', 'driver'])) {
            abort(404, 'Invalid URL');
         }
      }

      request()->merge([
         'company_id' => session('company_id'),
         'user_id' => session('user_id')
      ]);

      $consignor_copy = $this->consignor_copyModel::findOrFail($id);

      $jsonconsignorcopydata = app('App\Http\Controllers\\' . $this->version . '\api\consignorcopyController')->show($id);

      $jsonconsignercopyContent = $jsonconsignorcopydata->getContent();

      $consignorcopydata = json_decode($jsonconsignercopyContent, true);

      $options = [
         'isPhpEnabled' => true,
         'isFontSubsettingEnabled' => true,
         'margin_top' => 0,
         'margin_right' => 0,
         'margin_bottom' => 0,
         'margin_left' => 0,
         'padding_top' => 0,
         'padding_right' => 0,
         'padding_bottom' => 0,
         'padding_left' => 0,
         'defaultFont' => 'Helvetica',
         //    'isHtml5ParserEnabled' => true,
         //    'isRemoteEnabled' => true,
      ];

      if ($consignorcopydata['status'] != 200) {
         return redirect()->back()->with('message', 'failed');
      }

      $consignorcopydata['data']['copies'] = explode(',', $request->copies);

      // return view($this->version . '.admin.PDF.consignorcopy', $consignorcopydata);

      $pdfname = 'ConsignorCopy_' . $consignorcopydata['data']['consignorcopy']['consignment_note_no'] . '_' . $consignorcopydata['data']['consignorcopy']['consignor'] . '_' . date('d-M-y') . '.pdf';

      $pdf = PDF::setOptions($options)->loadView($this->version . '.admin.PDF.consignorcopy', $consignorcopydata)->setPaper('a4', 'portrait');

      return $pdf->stream($pdfname);
   }

   public function downloadZip(string $fileName)
   {
      $filePath = storage_path('app/');
      if (file_exists($filePath)) {
         return response()->download($filePath . $fileName . '.zip')->deleteFileAfterSend(true);
      }

      return response()->json([
         'status' => 'error',
         'message' => 'File not found'
      ], 404);
   }


   /**
    * Summary of generatepdf
    * generate quotation pdf
    * @param string $id
    * @return \Illuminate\Http\Response
    */
   public function generatequotationpdf(string $id)
   {

      $dbname = company::find(Session::get('company_id'));
      config(['database.connections.dynamic_connection.database' => $dbname->dbname]);

      // Establish connection to the dynamic database
      DB::purge('dynamic_connection');
      DB::reconnect('dynamic_connection');

      $quotation = $this->quotationModel::findOrFail($id);

      $data = $this->prepareDataForQuotationPDF($quotation);


      $options = [
         'isPhpEnabled' => true,
         'isHtml5ParserEnabled' => true,
         'margin_top' => 0,
         'margin_right' => 0,
         'margin_bottom' => 0,
         'margin_left' => 0,
         'defaultFont' => 'Helvetica'
      ];


      $companyname = $data['quotationdata']['firstname'] . $data['quotationdata']['lastname']; // if customer company name is not set

      if ($data['quotationdata']['company_name'] != '') {
         $companyname = $data['quotationdata']['company_name'];
      }

      // return view($this->version . '.admin.PDF.quotationtemplate', $data);
      $pdfname = $data['quotationdata']['quotation_number'] . ' ' . $companyname . ' ' . date('d-M-y') . '.pdf';

      $pdf = PDF::setOptions($options)->loadView($this->version . '.admin.PDF.quotationtemplate', $data)->setPaper('a4', 'portrait');

      return $pdf->stream($pdfname);
   }

   //download ledger
   public function generateLedgerPdfDownload(Request $request)
   {
      try {
         $ledgerdetails = app('App\Http\Controllers\\' . $this->version . '\api\accountController')->ledgerdetails($request);

         $ledgerdetails = $ledgerdetails->getContent();

         $ledgerdetails = json_decode($ledgerdetails, true);

         if ($ledgerdetails['status'] != 200) {
            return response()->json([
               'status'  => 'error',
               'message' => 'No ledger entry between this date'
            ]);
         }

         $balance = 0;
         $totalCredited = 0;
         $totalDebited  = 0;

         $ledgers = $ledgerdetails['ledgers'];

         /* loop through actual ledger entries */
         foreach ($ledgers as &$ledger) {
            $totalCredited += $ledger['credited'];
            $totalDebited  += $ledger['debited'];

            $balance += $ledger['credited'] - $ledger['debited'];
            $ledger['balance'] = $balance; // running balance
         }

         $totalBalance = $totalCredited - $totalDebited;

         // Temporary directory
         $tempDir = storage_path('app/temp_pdf');
         if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
         }

         // File name
         $fileName = 'ledger_' . date('YmdHis') . '.pdf';
         $filePath = $tempDir . '/' . $fileName;

         $options = [
            'isPhpEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'Helvetica'
         ];

         $companyDetails = $this->getCompanyDetails();

         $startDate = $request->from_date;
         $endDate   = Carbon::parse($request->to_date)->endOfDay();

         // Generate PDF
         $pdf = Pdf::setOptions($options)
            ->loadView($this->version . '.admin.PDF.ledger', compact(
               'ledgers',
               'startDate',
               'endDate',
               'totalCredited',
               'totalDebited',
               'totalBalance',
               'companyDetails'
            ))
            ->setPaper('A4', 'portrait')
            ->save($filePath);

         // Return URL for download
         return response()->json([
            'status' => 'success',
            'pdfFileUrl' => route('pdf.download', $fileName) // You need a route to download this file
         ]);
      } catch (\Exception $e) {
         Log::error($e->getMessage());
         return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong while generating the PDF'
         ]);
      }
   }

   public function downloadPdf(string $fileName)
   {
      $filePath = storage_path('app/temp_pdf/' . $fileName);

      if (file_exists($filePath)) {
         // Download the file and delete it after sending
         return response()->download($filePath)->deleteFileAfterSend(true);
      }

      return response()->json([
         'status'  => 'error',
         'message' => 'File not found'
      ], 404);
   }

   // helper functions start

   // Helper function to prepare data for invoice PDF generation
   private function prepareDataForPDF($invoice, $paymentdetails = null)
   {
      request()->merge([
         'company_id' => session('company_id'),
         'user_id' => session('user_id')
      ]);
      $jsonproductdata = app('App\Http\Controllers\\' . $this->version . '\api\invoiceController')->inv_details($invoice->id);
      $jsoninvdata = app('App\Http\Controllers\\' . $this->version . '\api\invoiceController')->index($invoice->id);
      $jsonbankdetailsdata = app('App\Http\Controllers\\' . $this->version . '\api\bankdetailsController')->bankdetailspdf($invoice->account_id);

      if ($invoice->third_party_invoice == 1) {
         $jsoncompanydetailsdata = app('App\Http\Controllers\\' . $this->version . '\api\thirdpartycompanyController')->invoicecompanycompanydetailspdf($invoice->company_id);
      } else {
         $jsoncompanydetailsdata = app('App\Http\Controllers\\' . $this->version . '\api\companyController')->companydetailspdf($invoice->company_details_id);
      }
      $jsonproductContent = $jsonproductdata->getContent();
      $jsoninvContent = $jsoninvdata->getContent();
      $jsoncompanyContent = $jsoncompanydetailsdata->getContent();
      $jsonbankContent = $jsonbankdetailsdata->getContent();

      // Decode the JSON data
      $productdata = json_decode($jsonproductContent, true);
      $invdata = json_decode($jsoninvContent, true);
      $companydetailsdata = json_decode($jsoncompanyContent, true);
      $bankdetailsdata = json_decode($jsonbankContent, true);


      if ($productdata['status'] == 404) {
         session()->flash('custom_error_message', 'Product column not found');
         abort('404');
      }

      if ($bankdetailsdata['status'] == 404) {
         session()->flash('custom_error_message', 'Bank details not found');
         abort('404');
      }

      if ($bankdetailsdata['status'] == 500) {
         session()->flash('custom_error_message', 'Bank details Unauthorized');
         abort('404');
      }

      if ($invdata['status'] == 404) {
         session()->flash('custom_error_message', 'Invoice data not found');
         abort('404');
      }

      if ($companydetailsdata['status'] == 404) {
         session()->flash('custom_error_message', 'Company details not found');
         abort('404');
      }


      $data = [
         'productscolumn' => $productdata['columnswithtype'],
         'products' => $productdata['invoice'],
         'othersettings' => $productdata['othersettings'][0],
         'invoiceothersettings' => $productdata['invoiceothersettings'],
         'invdata' => $invdata['invoice'][0],
         'companydetails' => $companydetailsdata['companydetails'][0],
         'bankdetails' => $bankdetailsdata['bankdetail'][0]
      ];

      if (isset($paymentdetails)) {
         $jsonpaymentdata = app('App\Http\Controllers\\' . $this->version . '\api\PaymentController')->index($invoice->id);
         $jsonpaymentContent = $jsonpaymentdata->getContent();
         $paymentdata = json_decode($jsonpaymentContent, true);

         if ($paymentdata['status'] == 404) {
            session()->flash('custom_error_message', 'Payment data not found');
            abort('404');
         }

         $data['payment'] = $paymentdata['payment'];
      }

      return $data;
   }


   // Helper function to prepare data for PDF generation
   private function prepareDataForQuotationPDF($quotation, $paymentdetails = null)
   {
      request()->merge([
         'company_id' => session('company_id'),
         'user_id' => session('user_id')
      ]);


      $jsonproductdata = app('App\Http\Controllers\\' . $this->version . '\api\quotationController')->quotation_details($quotation->id);
      $jsonquotationdata = app('App\Http\Controllers\\' . $this->version . '\api\quotationController')->index($quotation->id);

      if ($quotation->third_party_quotation == 1) {
         $jsoncompanydetailsdata = app('App\Http\Controllers\\' . $this->version . '\api\thirdpartycompanyController')->quotationcompanycompanydetailspdf($quotation->company_id);
      } else {
         $jsoncompanydetailsdata = app('App\Http\Controllers\\' . $this->version . '\api\companyController')->companydetailspdf($quotation->company_details_id);
      }

      $jsonproductContent = $jsonproductdata->getContent();
      $jsonquotationContent = $jsonquotationdata->getContent();
      $jsoncompanyContent = $jsoncompanydetailsdata->getContent();

      // Decode the JSON data
      $productdata = json_decode($jsonproductContent, true);
      $quotationdata = json_decode($jsonquotationContent, true);
      $companydetailsdata = json_decode($jsoncompanyContent, true);

      if ($productdata['status'] == 404) {
         return redirect()->back()->with('message', 'yes');
      }

      if ($quotationdata['status'] == 404) {
         session()->flash('custom_error_message', 'Quotation data not found');
         abort('404');
      }

      if ($companydetailsdata['status'] == 404) {
         session()->flash('custom_error_message', 'Company details not found');
         abort('404');
      }

      $data = [
         'productscolumn' => $productdata['columnswithtype'],
         'products' => $productdata['quotation'],
         'othersettings' => $productdata['othersettings'][0],
         'quotationdata' => $quotationdata['quotation'][0],
         'companydetails' => $companydetailsdata['companydetails'][0],
      ];

      return $data;
   }

   private function getCompanyDetails($id = null)
   {
      $companyDetails = company::join('company_details', 'company.company_details_id', 'company_details.id')
         ->join('state', 'company_details.state_id', 'state.id')
         ->join('city', 'company_details.city_id', 'city.id')
         ->where('company.id', session('company_id'))
         ->select('company_details.*', 'state.state_name', 'city.city_name')
         ->first();

      return $companyDetails;
   }
   public function letterdownload(string $id)
   {
      request()->merge([
         'company_id' => session('company_id'),
         'user_id' => session('user_id')
      ]);

      $dbname = company::find(Session::get('company_id'));
      config(['database.connections.dynamic_connection.database' => $dbname->dbname]);

      // Establish connection to the dynamic database
      DB::purge('dynamic_connection');
      DB::reconnect('dynamic_connection');


      $jsonletterdata = app('App\Http\Controllers\\' . $this->version . '\api\HrController')->generateletteredit($id);
      $jsonletterContent = $jsonletterdata->getContent();
      $letterdata = json_decode($jsonletterContent, true);
      $emp_id = $letterdata['data']['emp_id'];
      $data_formate_id = $letterdata['data']['data_formate_id'];

      $jsonempdata = app('App\Http\Controllers\\' . $this->version . '\api\HrController')->edit($emp_id);
      $jsondataformatedata = app('App\Http\Controllers\\' . $this->version . '\api\HrController')->dataformate($data_formate_id);
      $jsonempContent = $jsonempdata->getContent();
      $jsondataformateContent = $jsondataformatedata->getContent();
      $empdata = json_decode($jsonempContent, true);
      $dataformatedata = json_decode($jsondataformateContent, true);

      if ($letterdata['status'] == 404) {
         session()->flash('custom_error_message', 'letter Data not found');
         abort('404');
      }
      if ($empdata['status'] == 404) {
         session()->flash('custom_error_message', 'Employee Data not found');
         abort('404');
      }
      if ($dataformatedata['status'] == 404) {
         session()->flash('custom_error_message', 'Employee Data not found');
         abort('404');
      }

      $data = [
         'letterdata' => $letterdata['data'],
         'empdata' => $empdata['data'],
         'dataformate' => $dataformatedata['data'],
      ];
      $fullName = $data['empdata']['first_name'] . ' ' .
         $data['empdata']['middle_name'] . ' ' .
         $data['empdata']['surname'];
      //   dd($data);
      $options = [
         'isPhpEnabled' => true,
         'isHtml5ParserEnabled' => true,
         'margin_top' => 0,
         'margin_right' => 0,
         'margin_bottom' => 0,
         'margin_left' => 0,
         'defaultFont' => 'Helvetica'
      ];

      // return view($this->version . '.admin.PDF.generateletter', ['data' => $data]);
      $pdf = PDF::setOptions($options)->loadView($this->version . '.admin.PDF.generateletter', ['data' => $data])->setPaper('a4', 'portrait');

      $name = $data['dataformate']['letter_name'] .'-Of-'. $fullName . '.pdf';

      return $pdf->stream($name);
   }
}
