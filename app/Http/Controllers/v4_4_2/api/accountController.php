<?php

namespace App\Http\Controllers\v4_4_2\api;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class accountController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $ledgerModel, $expenseModel, $incomeModel, $user_permissionModel, $categoryModel, $subcategoryModel, $account_other_settingModel;

    public function __construct(Request $request)
    {
        $this->dbname($request->company_id);
        $this->companyId = $request->company_id;
        $this->userId = $request->user_id;
        $this->masterdbname = DB::connection()->getDatabaseName();

        $user_rp = DB::connection('dynamic_connection')
            ->table('user_permissions')
            ->select('rp')
            ->where('user_id', $this->userId)
            ->get();

        $permissions = json_decode($user_rp, true);
        $this->rp = json_decode($permissions[0]['rp'], true);

        $this->ledgerModel = $this->getmodel('Ledger');
        $this->expenseModel = $this->getmodel('Expense');
        $this->incomeModel = $this->getmodel('Income');
        $this->categoryModel = $this->getmodel('category');
        $this->subcategoryModel = $this->getmodel('subcategory');
        $this->user_permissionModel = $this->getmodel('user_permission');
        $this->account_other_settingModel = $this->getmodel('account_other_setting');
    }

    /**
     * get financial year for payment receipt pdf
     */
    private function getFinancialYear($date = null)
    {
        $date = $date ? \Carbon\Carbon::parse($date) : now();
        $year = $date->year;

        // If before April, it's previous financial year
        if ($date->month < 4) {
            return ($year - 1) . '-' . substr($year, -2);
        }

        return $year . '-' . substr($year + 1, -2);
    }


    public function voucherdetails(Request $request, int $voucherId)
    {

        $expense = $this->expenseModel::find($voucherId);

        if (!$expense) {
            return $this->successresponse(404, 'message', "No Such expense Found!");
        }

        if ($this->rp['accountmodule']['expense']['alldata'] != 1) {
            if ($expense->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        if ($this->rp['accountmodule']['expense']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        return $this->successresponse(200, 'expense', $expense);
    }

    // use for pdf
    public function ledgerdetails(Request $request)
    {

        $startDate = $request->from_date;
        $endDate   = Carbon::parse($request->to_date)->endOfDay();

        $ledgers =  $this->ledgerModel::whereBetween('date', [$startDate, $endDate])
            ->where('is_deleted', 0)
            ->orderBy('date', 'asc')
            ->get();

        if ($ledgers->isEmpty()) {
            $this->successresponse(500, 'message', 'No ledger entry between this date');
        }

        return $this->successresponse(200, 'ledgers', $ledgers);
    }

    /**
     * Display a listing of the resource.
     */
    public function expenseindex(Request $request)
    {
        if ($this->rp['accountmodule']['expense']['view'] != 1) {
            return response()->json([
                'status' => 500,
                'message' => 'You are Unauthorized',
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ]);
        }

        $expenses = $this->expenseModel::select(
            'expenses.*',
            DB::raw("DATE_FORMAT(expenses.date, '%d-%M-%Y') as date_formatted"),
            DB::raw("DATE_FORMAT(expenses.created_at, '%d-%M-%Y %h:%i %p') as created_date_formatted"),
            'category.name as category_name',
            'subcategory.name as subcategory_name',
        )
            ->leftJoin('category', function ($join) {
                $join->on('category.id', '=', 'expenses.category_id')
                    ->where('category.is_deleted', 0);
            })
            ->leftJoin('subcategory', function ($join) {
                $join->on('subcategory.id', '=', 'expenses.subcategory_id')
                    ->where('subcategory.is_deleted', 0);
            })
            ->where('expenses.is_deleted', 0);

        if ($this->rp['accountmodule']['expense']['alldata'] != 1) {
            $expenses->where('expenses.created_by', $this->userId);
        }
        $filters = [
            'filter_paid_to' => 'expenses.customer_id',
            'filter_from_date' => 'expenses.created_at',
            'filter_to_date' => 'expenses.created_at',
            'filter_category'   => 'expenses.category_id',
            'filter_subcategory'   => 'expenses.subcategory_id',
        ];

        // Loop through the filters and apply them conditionally
        foreach ($filters as $requestKey => $column) {
            $value = $request->$requestKey;

            if (!empty($value)) {
                if ($requestKey === 'filter_from_date' || $requestKey === 'filter_to_date') {
                    // ← FIXED: exact key match instead of strpos()
                    $operator = $requestKey === 'filter_from_date' ? '>=' : '<=';
                    $expenses->whereDate($column, $operator, $value);
                } else {
                    $expenses->where($column, $value);
                }
            }
        }
        $totalcount = $expenses->get()->count(); // count total record


        $expenses = $expenses->get();

        if ($expenses->isEmpty()) {
            return DataTables::of($expenses)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                    'recordsTotal' => $totalcount, // Total records count
                ])
                ->make(true);
        }


        return DataTables::of($expenses)
            ->with([
                'status' => 200,
                'recordsTotal' => $totalcount, // Total records count
            ])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function expensestore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date'         => 'required|date',
            'amount'       => 'required|numeric|min:1',
            'paid_to'      => 'nullable|string',
            'customer_id'  => 'nullable',
            'payment_type' => 'required|string',
            'description'  => 'nullable|string',
            'expense_category'  => 'nullable',
            'expense_subcategory'  => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        if ($this->rp['accountmodule']['expense']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $expense = [
            'description'  => $request->description,
            'amount'       => $request->amount,
            'payment_type' => $request->payment_type,
            'paid_to'      => $request->paid_to,      // ✅ stores customer name
            'customer_id'  => $request->customer_id ?? null, // ✅ stores customer id
            'date'         => $request->date ?? now(),
            'entry_type'   => 'm',
            'category_id'   => $request->expense_category,
            'subcategory_id'   => $request->expense_subcategory,
            'created_by'   => $this->userId
        ];

        $savedExpense = $this->expenseModel::create($expense);

        // Extract numeric part from VOU/8 → 8
        $latestExpenseVoucher = $this->expenseModel::whereNotNull('voucher_no')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(voucher_no, '/', -1) AS UNSIGNED)) as max_voucher")
            ->value('max_voucher') ?? 0;

        $latestIncomeVoucher = $this->incomeModel::whereNotNull('voucher_no')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(voucher_no, '/', -1) AS UNSIGNED)) as max_voucher")
            ->value('max_voucher') ?? 0;

        $newVoucherNo = max((int)$latestExpenseVoucher, (int)$latestIncomeVoucher) + 1;

        $billNo     = "BILL/$savedExpense->id";
        $voucher_no = "VOU/$newVoucherNo";

        $this->expenseModel::find($savedExpense->id)->update([
            'bill_no'    => $billNo,
            'voucher_no' => $voucher_no,
            'reference_no' => $voucher_no,
        ]);
        $description = '';

        if (!empty($request->paid_to)) {
            $description .= $request->paid_to;
        }

        if (!empty($request->description)) {
            if (!empty($description)) {
                $description .= '<br>';
            }
            $description .= $request->description;
        }
        $ledger = [
            'payment_id' => $savedExpense->id,
            'description' => $description,
            'reference_no' => $voucher_no,
            'debited' => $request->amount,
            'type' => 'expense',
            'paid_to' => $request->paid_to,
            'date' => $request->date ?? now(),
            'created_by' => $this->userId
        ];

        $savedLedger = $this->ledgerModel::create($ledger);

        if ($savedLedger) {
            return $this->successresponse(200, 'message', 'Expense entry successfully added');
        } else {
            return $this->successresponse(500, 'message', 'Expense entry not successfully added!');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function expenseedit(string $id)
    {
        $expense = $this->expenseModel::find($id);

        if (!$expense) {
            return $this->successresponse(404, 'message', "No Such expense Found!");
        }

        if ($this->rp['accountmodule']['expense']['alldata'] != 1) {
            if ($expense->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        if ($this->rp['accountmodule']['expense']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        if ($expense->entry_type == 'a') {
            return $this->successresponse(500, 'message', 'This record not editable!');
        }

        return $this->successresponse(200, 'expense', $expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function expenseupdate(Request $request, string $id)
    {

        // validate incoming request data 
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'paid_to' => 'nullable|string',
            'payment_type' => 'required|string',
            'description' => 'nullable|string',
            'customer_id'  => 'nullable',
            'expense_category'  => 'nullable',
            'expense_subcategory'  => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        } else {
            $expense = $this->expenseModel::find($id); // find expense record

            if (!$expense) {
                return $this->successresponse(404, 'message', 'No Such expense Found!');
            }

            if ($this->rp['accountmodule']['expense']['alldata'] != 1) {
                if ($expense->created_by != $this->userId) {
                    return $this->successresponse(500, 'message', 'You are Unauthorized');
                }
            }

            if ($this->rp['accountmodule']['expense']['edit'] != 1) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }

            if ($expense->entry_type == 'a') {
                return $this->successresponse(500, 'message', 'This record not editable!');
            }
            $voucher_no = $expense->voucher_no;
            $expenseupdatedata = [
                'description' => $request->description,
                'amount' => $request->amount,
                'reference_no' => $voucher_no,
                'payment_type' => $request->payment_type,
                'paid_to'      => $request->paid_to,      // ✅ stores customer name
                'customer_id'  => $request->customer_id ?? null, // ✅ stores customer id
                'date' => $request->date ?? now(),
                'category_id'   => $request->expense_category,
                'subcategory_id'   => $request->expense_subcategory,
                'updated_by' => $this->userId,
            ];
            // dd($expenseupdatedata);
            $description = '';
            $expense = $expense->update($expenseupdatedata);


            if (!empty($request->paid_to)) {
                $description .= $request->paid_to;
            }

            if (!empty($request->description)) {
                if (!empty($description)) {
                    $description .= '<br>';
                }
                $description .= $request->description;
            }
            $ledger = [
                'description' => $description,
                'debited' => $request->amount,
                'reference_no' => $voucher_no,
                'paid_to' => $request->paid_to,
                'date' => $request->date ?? now(),
                'updated_by' => $this->userId
            ];

            $savedLedger = $this->ledgerModel::where('payment_id', $id)->where('type', 'expense')->update($ledger);

            if ($savedLedger) {
                return $this->successresponse(200, 'message', 'Expense succesfully updated');
            } else {
                return $this->successresponse(500, 'message', 'Expense not successfully updated!');
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function expensedestroy(string $id)
    {
        $expense = $this->expenseModel::find($id);

        if (!$expense) {
            return $this->successresponse(404, 'message', 'No Such expense Found!');
        }

        if ($this->rp['accountmodule']['expense']['alldata'] != 1) {
            if ($expense->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        if ($this->rp['accountmodule']['expense']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        if ($expense->entry_type == 'a') {
            return $this->successresponse(500, 'message', 'This record not deletable!');
        }

        $expense->update([
            'is_deleted' => 1
        ]);

        $deleteLedger = $this->ledgerModel::where('payment_id', $id)->where('type', 'expense')
            ->update([
                'is_deleted' => 1
            ]);

        return $this->successresponse(200, 'message', 'expense succesfully deleted');
    }

    /**
     * Display a listing of the resource.
     */
    public function incomeindex(Request $request)
    {
        if ($this->rp['accountmodule']['income']['view'] != 1) {
            return response()->json([
                'status' => 500,
                'message' => 'You are Unauthorized',
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ]);
        }

        $incomes = $this->incomeModel::join('ledgers', function ($join) {
            $join->on('ledgers.payment_id', '=', 'incomes.id')
                ->where('ledgers.type', 'income');
            if ($this->rp['accountmodule']['income']['alldata'] != 1) {
                $join->where('ledgers.created_by', $this->userId);
            }
        })
            ->leftJoin('category', function ($join) {
                $join->on('category.id', '=', 'incomes.category_id')
                    ->where('category.is_deleted', 0);
            })
            ->leftJoin('subcategory', function ($join) {
                $join->on('subcategory.id', '=', 'incomes.subcategory_id')
                    ->where('subcategory.is_deleted', 0);
            })
            ->select(
                'ledgers.id as ledger_id',
                'ledgers.description',
                'ledgers.subtype as type',
                'incomes.id',
                'incomes.income_details_id',
                'incomes.voucher_no',
                'incomes.reference_no',
                'incomes.receipt_no',
                'incomes.description',
                'incomes.amount',
                'incomes.payment_type',
                'incomes.entry_type',
                'incomes.paid_by',
                'category.name as category_name',
                'subcategory.name as subcategory_name',
                DB::raw("DATE_FORMAT(ledgers.date, '%d-%M-%Y') as date_formatted"),
            )
            ->where('incomes.is_deleted', 0)
            ->where('ledgers.is_deleted', 0);
        $filters = [
            'filter_paid_by'   => 'incomes.customer_id',
            'filter_category'   => 'incomes.category_id',
            'filter_subcategory'   => 'incomes.subcategory_id',
            'filter_from_date' => 'incomes.created_at',
            'filter_to_date'   => 'incomes.created_at',
        ];

        foreach ($filters as $requestKey => $column) {
            $value = $request->$requestKey;

            if (!empty($value)) {
                if (strpos($requestKey, 'from') !== false || strpos($requestKey, 'to') !== false) {
                    $operator = strpos($requestKey, 'from') !== false ? '>=' : '<=';
                    $incomes->whereDate($column, $operator, $value);
                } else {
                    $incomes->where($column, $value);
                }
            }
        }
        $totalcount = $incomes->get()->count(); // count total record

        $incomes = $incomes->get();

        if ($incomes->isEmpty()) {
            return DataTables::of($incomes)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                    'recordsTotal' => $totalcount, // Total records count
                ])
                ->make(true);
        }

        return DataTables::of($incomes)
            ->with([
                'status' => 200,
                'recordsTotal' => $totalcount, // Total records count
            ])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function incomestore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'type' => 'nullable|string',
            'paid_by' => 'nullable|string',
            'payment_type' => 'required|string',
            'description' => 'nullable|string',
            'customer_id'  => 'nullable',
            'income_category'  => 'nullable',
            'income_subcategory'  => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        if ($this->rp['accountmodule']['income']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $income = [
            'description' => $request->description,
            'amount' => $request->amount,
            'payment_type' => $request->payment_type,
            'paid_by' => $request->paid_by,
            'customer_id'  => $request->customer_id ?? null,
            'entry_type' => 'm',
            'category_id'   => $request->income_category,
            'subcategory_id'   => $request->income_subcategory,
        ];
        $savedIncome = $this->incomeModel::create($income);

        // Extract numeric part from VOU/8 → 8
        $latestExpenseVoucher = $this->expenseModel::whereNotNull('voucher_no')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(voucher_no, '/', -1) AS UNSIGNED)) as max_voucher")
            ->value('max_voucher') ?? 0;

        $latestIncomeVoucher = $this->incomeModel::whereNotNull('voucher_no')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(voucher_no, '/', -1) AS UNSIGNED)) as max_voucher")
            ->value('max_voucher') ?? 0;

        $newVoucherNo = max((int)$latestExpenseVoucher, (int)$latestIncomeVoucher) + 1;

        $receiptNo  = "REC/$savedIncome->id";
        $voucher_no = "VOU/$newVoucherNo";

        $this->incomeModel::find($savedIncome->id)->update([
            'receipt_no' => $receiptNo,
            'voucher_no' => $voucher_no,
            'reference_no' => $voucher_no,
        ]);
        $description = '';

        if (!empty($request->paid_by)) {
            $description .= $request->paid_by;
        }

        if (!empty($request->description)) {
            if (!empty($description)) {
                $description .= '<br>';
            }
            $description .= $request->description;
        }

        $ledger = [
            'payment_id' => $savedIncome->id,
            'description' => $description,
            'credited' => $request->amount,
            'reference_no' => $voucher_no,
            'type' => 'income',
            'subtype' => $request->type,
            'date' => $request->date ?? now(),
            'paid_by' => $request->paid_by,
            'created_by' => $this->userId
        ];

        $savedLedger = $this->ledgerModel::create($ledger);

        if ($savedLedger) {
            return $this->successresponse(200, 'message', 'Income entry successfully added');
        } else {
            return $this->successresponse(500, 'message', 'Income entry not successfully added!');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function incomeedit(string $id)
    {
        $income = $this->ledgerModel::join('incomes', function ($join) {
            $join->on('ledgers.payment_id', '=', 'incomes.id')
                ->where('ledgers.type', 'income');
        })->select(
            'incomes.description',
            'ledgers.subtype as type',
            'incomes.amount',
            'incomes.payment_type',
            'incomes.paid_by',
            'incomes.customer_id',
            'incomes.category_id',
            'incomes.subcategory_id',
            'ledgers.date',
        )
            ->where('ledgers.payment_id', $id)->where('ledgers.type', 'income')->first();

        if (!$income) {
            return $this->successresponse(404, 'message', "No Such income Found!");
        }

        if ($this->rp['accountmodule']['income']['alldata'] != 1) {
            if ($income->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        if ($this->rp['accountmodule']['income']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        if ($income->entry_type == 'a') {
            return $this->successresponse(500, 'message', 'This record not editable!');
        }

        return $this->successresponse(200, 'income', $income);
    }

    /**
     * Update the specified resource in storage.
     */
    public function incomeupdate(Request $request, string $id)
    {

        // validate incoming request data 
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'type' => 'nullable|string',
            'paid_by' => 'nullable|string',
            'payment_type' => 'required|string',
            'description' => 'nullable|string',
            'customer_id'  => 'nullable',
            'income_category'  => 'nullable',
            'income_subcategory'  => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        } else {
            $income = $this->incomeModel::find($id); // find income record

            if (!$income) {
                return $this->successresponse(404, 'message', 'No Such income Found!');
            }

            $ledger = $this->ledgerModel::where('payment_id', $id)->where('type', 'income')->first();

            if ($this->rp['accountmodule']['income']['alldata'] != 1) {
                if (!$ledger || $ledger->created_by != $this->userId) {
                    return $this->successresponse(500, 'message', 'You are Unauthorized');
                }
            }

            if ($this->rp['accountmodule']['income']['edit'] != 1) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }

            if ($income->entry_type == 'a') {
                return $this->successresponse(500, 'message', 'This record not editable!');
            }

            $financialYear = $this->getFinancialYear($request->date);
            $voucher_no = $income->voucher_no;
            $incomeupdatedata = [
                'description' => $request->description,
                'reference_no' => $voucher_no,
                'amount' => $request->amount,
                'payment_type' => $request->payment_type,
                'paid_by' => $request->paid_by,
                'customer_id'  => $request->customer_id ?? null,
                'category_id'   => $request->income_category,
                'subcategory_id'   => $request->income_subcategory,
            ];

            $description = '';
            $income = $income->update($incomeupdatedata);

            if (!empty($request->paid_by)) {
                $description .= $request->paid_by;
            }

            if (!empty($request->description)) {
                if (!empty($description)) {
                    $description .= '<br>';
                }
                $description .= $request->description;
            }
            $ledger = [
                'description' => $description,
                'credited' => $request->amount,
                'reference_no' => $voucher_no,
                'subtype' => $request->type,
                'paid_by' => $request->paid_by,
                'date' => $request->date ?? now(),
                'updated_by' => $this->userId
            ];

            $savedLedger = $this->ledgerModel::where('payment_id', $id)->where('type', 'income')->update($ledger);

            if ($savedLedger) {
                return $this->successresponse(200, 'message', 'Income succesfully updated');
            } else {
                return $this->successresponse(500, 'message', 'Income not successfully updated!');
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function incomedestroy(string $id)
    {
        $income = $this->incomeModel::find($id);

        if (!$income) {
            return $this->successresponse(404, 'message', 'No Such income Found!');
        }

        $ledger = $this->ledgerModel::where('payment_id', $id)->where('type', 'income')->first();

        if ($this->rp['accountmodule']['income']['alldata'] != 1) {
            if (!$ledger || $ledger->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        if ($this->rp['accountmodule']['income']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        if ($income->entry_type == 'a') {
            return $this->successresponse(500, 'message', 'This record not deletable!');
        }

        $income->update([
            'is_deleted' => 1
        ]);

        $ledger->update([
            'is_deleted' => 1
        ]);

        return $this->successresponse(200, 'message', 'income succesfully deleted');
    }

    public function ledgerindex()
    {
        if ($this->rp['accountmodule']['ledger']['view'] != 1) {
            return response()->json([
                'status' => 500,
                'message' => 'You are Unauthorized',
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ]);
        }

        $ledgersQuery = $this->ledgerModel::select(
            'ledgers.*',
            DB::raw("DATE_FORMAT(date, '%d-%M-%Y') as date_formatted"),
            DB::raw("DATE_FORMAT(created_at, '%d-%M-%Y %h:%i %p') as created_date_formatted")
        )
            ->where('is_deleted', 0);

        if ($this->rp['accountmodule']['ledger']['alldata'] != 1) {
            $ledgersQuery->where('created_by', $this->userId);
        }

        // total count
        $totalcount = $ledgersQuery->count();

        // Fetch ASC for balance calculation, then reverse for display
        $ledgers = $ledgersQuery->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        if ($ledgers->isEmpty()) {
            return DataTables::of($ledgers)
                ->with([
                    'status' => 404,
                    'message' => 'No Data Found',
                    'recordsTotal' => $totalcount,
                ])
                ->make(true);
        }

        // Calculate running balance
        $balance = 0;

        $totalCredited = $ledgers->sum('credited');
        $totalDebited  = $ledgers->sum('debited');
        $totalBalance  = $totalCredited - $totalDebited;

        $ledgers = $ledgers->map(function ($ledger) use (&$balance) {
            $balance += $ledger->credited;
            $balance -= $ledger->debited;
            $ledger->balance = $balance;
            return $ledger;
        });

        // Reverse the collection to show newest records first
        $ledgers = $ledgers->reverse();

        return DataTables::of($ledgers)
            ->with([
                'status' => 200,
                'recordsTotal' => $totalcount,
                'totalCredited' => number_format($totalCredited, 2),
                'totalDebited'  => number_format($totalDebited, 2),
                'totalBalance'  => number_format($totalBalance, 2),
            ])
            ->make(true);
    }
    public function categoryindex(Request $request)
    {
        if ($this->rp['accountmodule']['category']['view'] != 1) {
            return response()->json([
                'status'          => 500,
                'message'         => 'You are Unauthorized',
                'data'            => [],
                'recordsTotal'    => 0,
                'recordsFiltered' => 0
            ]);
        }

        $categories = $this->categoryModel::leftJoin('subcategory', function ($join) {
            $join->on('subcategory.category_id', '=', 'category.id')
                ->where('subcategory.is_deleted', 0)
                ->where('subcategory.is_active', 1);
        })
            ->select(
                'category.id as category_id',
                'category.name as category_name',
                'category.type as category_type',
                'subcategory.name as subcategory_name'
            )
            ->where('category.is_deleted', 0)
            ->where('category.is_active', 1);
        $filters = [
            'filter_type' => 'category.type',
        ];

        // Loop through the filters and apply them conditionally
        foreach ($filters as $requestKey => $column) {
            $value = $request->$requestKey;

            if (isset($value)) {
                if (
                    strpos($requestKey, 'from') !== false || strpos($requestKey, 'to') !== false
                ) {
                    // For date filters (loading_date, stuffing_date), we apply range conditions
                    $operator = strpos($requestKey, 'from') !== false ? '>=' : '<=';
                    $categories->whereDate($column, $operator, $value);
                } else {
                    // For other filters, apply simple equality checks
                    $categories->where($column, $value);
                }
            }
        }

        $categories = $categories->get();

        $grouped = [];
        foreach ($categories as $item) {
            $catId = $item->category_id;

            if (!isset($grouped[$catId])) {
                $grouped[$catId] = [
                    'category_id'   => $catId,
                    'category_name' => $item->category_name,
                    'category_type' => $item->category_type,
                    'subcategories' => []
                ];
            }

            if ($item->subcategory_name) {
                $grouped[$catId]['subcategories'][] = $item->subcategory_name;
            }
        }

        foreach ($grouped as $key => $value) {
            $grouped[$key]['subcategories'] = count($value['subcategories']) > 0 ? implode(', ', $value['subcategories']) : null;
        }

        $result      = array_values($grouped);
        $totalCount  = count($result);

        if ($totalCount === 0) {
            return DataTables::of([])
                ->with([
                    'status'          => 404,
                    'message'         => 'No Data Found',
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0
                ])
                ->make(true);
        }

        return DataTables::of($result)
            ->with([
                'status'          => 200,
                'recordsTotal'    => $totalCount,
                'recordsFiltered' => $totalCount
            ])
            ->make(true);
    }
    public function categorystore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:income,expense',
            'subcategories'   => 'nullable|array',
            'subcategories.*' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        if ($this->rp['accountmodule']['category']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $category = $this->categoryModel::create([
            'name'       => $request->name,
            'type'       => $request->type,
            'created_by' => $request->user_id,
        ]);

        if (!$category) {
            return $this->successresponse(500, 'message', 'Category not saved!');
        }

        if ($request->filled('subcategories')) {
            foreach ($request->subcategories as $subName) {
                if (!empty($subName)) {
                    $this->subcategoryModel::create([
                        'name'        => $subName,
                        'category_id' => $category->id,
                        'created_by'  => $request->user_id,
                    ]);
                }
            }
        }

        return $this->successresponse(200, 'message', 'Category successfully added', 'category_id', $category->id);
    }

    public function categoryedit($id, Request $request)
    {
        if ($this->rp['accountmodule']['category']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $category = $this->categoryModel::where('id', $id)
            ->where('is_deleted', 0)
            ->first();

        if (!$category) {
            return $this->successresponse(500, 'message', 'Category not found');
        }

        $subcategories = $this->subcategoryModel::where('category_id', $id)
            ->where('is_deleted', 0)
            ->where('is_active', 1)
            ->select('id', 'name')
            ->get();
        if ($this->rp['accountmodule']['category']['alldata'] != 1) {
            if ($category->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }
        $category->subcategories = $subcategories;

        return $this->successresponse(200, 'category', $category);
    }

    public function categoryupdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'type'              => 'required|in:income,expense',
            'subcategories'     => 'nullable|array',
            'subcategories.*'   => 'nullable|string|max:255',
            'subcategory_ids'   => 'nullable|array',
            'subcategory_ids.*' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        if ($this->rp['accountmodule']['category']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $this->categoryModel::where('id', $id)->update([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        $subNames = $request->subcategories ?? [];
        $subIds   = $request->subcategory_ids ?? [];
        $keptIds  = [];

        $filteredNames = array_filter(array_map('trim', $subNames));
        $uniqueNames   = array_unique(array_map('strtolower', $filteredNames));

        if (count($filteredNames) !== count($uniqueNames)) {
            return $this->errorresponse(422, [
                'subcategories' => ['Duplicate subcategory names are not allowed.']
            ]);
        }

        foreach ($subNames as $index => $name) {
            $name  = trim($name);
            $subId = $subIds[$index] ?? null;

            if (empty($name)) continue;

            if ($subId) {
                $this->subcategoryModel::where('id', $subId)
                    ->where('category_id', $id)
                    ->update(['name' => $name]);

                $keptIds[] = $subId;
            } else {
                $exists = $this->subcategoryModel::where('category_id', $id)
                    ->where('name', $name)
                    ->where('is_deleted', 0)
                    ->exists();

                if ($exists) {
                    return $this->errorresponse(422, [
                        'subcategories' => ["Subcategory name '{$name}' already exists in this category."]
                    ]);
                }

                $newSub  = $this->subcategoryModel::create([
                    'name'        => $name,
                    'category_id' => $id,
                    'created_by'  => $request->user_id,
                ]);
                $keptIds[] = $newSub->id;
            }
        }

        $this->subcategoryModel::where('category_id', $id)
            ->whereNotIn('id', $keptIds)
            ->update(['is_deleted' => 1, 'is_active' => 0]);

        return $this->successresponse(200, 'message', 'Category updated successfully');
    }
    public function categorydestroy(Request $request, $id)
    {
        if ($this->rp['accountmodule']['category']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $category = $this->categoryModel::where('id', $id)
            ->where('is_deleted', 0)
            ->first();

        if (!$category) {
            return $this->successresponse(500, 'message', 'Category not found');
        }

        $this->categoryModel::where('id', $id)
            ->update([
                'is_deleted' => 1,
                'is_active'  => 0,
            ]);

        $this->subcategoryModel::where('category_id', $id)
            ->where('is_deleted', 0)
            ->update([
                'is_deleted' => 1,
                'is_active'  => 0,
            ]);

        return $this->successresponse(200, 'message', 'Category deleted successfully');
    }
    //add subcategory for in category list (add sub category and income and expense form)
    public function subcategorystore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'category_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        if ($this->rp['accountmodule']['category']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $exists = $this->subcategoryModel::where('category_id', $request->category_id)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->where('is_deleted', 0)
            ->exists();

        if ($exists) {
            return $this->errorresponse(422, [
                'name' => ["'{$request->name}' already exists in this category."]
            ]);
        }

        $create = $this->subcategoryModel::create([
            'name'        => $request->name,
            'category_id' => $request->category_id,
            'created_by'  => $request->user_id,
        ]);
        $data = [
            'category_id' => $request->category_id,
            'subcategory_id' => $create->id
        ];
        return $this->successresponse(200, 'message', 'Sub Category added successfully', 'data', $data);
    }
    //load only category where type is income
    public function incomecategory()
    {
        if ($this->rp['accountmodule']['category']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $category = $this->categoryModel::where('type', 'income')
            ->where('is_deleted', 0)
            ->where('is_active', 1)
            ->get();

        return $this->successresponse(200, 'category', $category);
    }

    //load only category where type is expense
    public function expensecategory()
    {
        if ($this->rp['accountmodule']['category']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $category = $this->categoryModel::where('type', 'expense')
            ->where('is_deleted', 0)
            ->where('is_active', 1)
            ->get();

        return $this->successresponse(200, 'category', $category);
    }

    // load sub category list for form income and expense
    public function subcategorylist(Request $request, $id)
    {
        if ($this->rp['accountmodule']['category']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        if (!is_numeric($id)) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid category ID'
            ]);
        }

        $subcategory = $this->subcategoryModel::where('category_id', $id)->get(['id', 'name']);

        if ($subcategory->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No subcategory found'
            ]);
        }
        return $this->successresponse(200, 'subcategory', $subcategory);
    }
    public function accountothersettings(Request $request)
    {
        $settings = $this->account_other_settingModel::where('is_deleted', 0)->get();

        if ($settings->isEmpty()) {
            return $this->successresponse(404, 'settings', 'No Records Found');
        }

        return $this->successresponse(200, 'settings', $settings);
    }
    public function customerdropdown(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'income_customer_dropdown' => 'required|array',
            'income_customer_dropdown.*' => 'string',
            'expense_customer_dropdown' => 'required|array',
            'expense_customer_dropdown.*' => 'string',
            'user_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        // Permission check
        if ($this->rp['accountmodule']['accountformsetting']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $getsettings = $this->account_other_settingModel::find(1);

        if (!$getsettings) {
            return $this->successresponse(404, 'message', 'No Such Customer Dropdown Setting Found!');
        }

        date_default_timezone_set('Asia/Kolkata');
        $incomeDropdown = json_encode($request->income_customer_dropdown);
        $expenseDropdown = json_encode($request->expense_customer_dropdown);
        $getsettings->update([
            // store as JSON
            'income_customer_dropdown' => $incomeDropdown,
            'expense_customer_dropdown' => $expenseDropdown,
            'updated_by' => $this->userId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->successresponse(200, 'message', 'Customer dropdown settings successfully updated');
    }
}
