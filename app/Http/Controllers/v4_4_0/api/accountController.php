<?php

namespace App\Http\Controllers\v4_4_0\api;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class accountController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $ledgerModel, $expenseModel, $incomeModel, $user_permissionModel;

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
        $this->user_permissionModel = $this->getmodel('user_permission');
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
    public function expenseindex()
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
            DB::raw("DATE_FORMAT(date, '%d-%M-%Y') as date_formatted"),
            DB::raw("DATE_FORMAT(created_at, '%d-%M-%Y %h:%i %p') as created_date_formatted"),
        )
            ->where('is_deleted', 0);

        if ($this->rp['accountmodule']['expense']['alldata'] != 1) {
            $expenses->where('created_by', $this->userId);
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
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'paid_to' => 'nullable|string',
            'payment_type' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        if ($this->rp['accountmodule']['expense']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $expense = [
            'description' => $request->description,
            'amount' => $request->amount,
            'payment_type' => $request->payment_type,
            'paid_to' => $request->paid_to,
            'date' => $request->date ?? now(),
            'entry_type' => 'm',
            'created_by' => $this->userId
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
        ]);

        $ledger = [
            'payment_id' => $savedExpense->id,
            'description' => $request->description,
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
            'description' => 'nullable|string'
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

            $expenseupdatedata = [
                'description' => $request->description,
                'amount' => $request->amount,
                'payment_type' => $request->payment_type,
                'paid_to' => $request->paid_to,
                'date' => $request->date ?? now(),
                'updated_by' => $this->userId,
            ];

            $expense = $expense->update($expenseupdatedata);

            $ledger = [
                'description' => $request->description,
                'debited' => $request->amount,
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
    public function incomeindex()
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
            ->select(
                'ledgers.id as ledger_id',
                'ledgers.description',
                'ledgers.subtype as type',
                'incomes.id',
                'incomes.income_details_id',
                'incomes.voucher_no',
                'incomes.receipt_no',
                'incomes.description',
                'incomes.amount',
                'incomes.payment_type',
                'incomes.entry_type',
                'incomes.paid_by',
                DB::raw("DATE_FORMAT(ledgers.date, '%d-%M-%Y') as date_formatted"),
            )
            ->where('incomes.is_deleted', 0)
            ->where('ledgers.is_deleted', 0);

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
            'entry_type' => 'm',
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
        ]);

        $ledger = [
            'payment_id' => $savedIncome->id,
            'description' => $request->description,
            'credited' => $request->amount,
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
            'ledgers.description',
            'ledgers.subtype as type',
            'incomes.amount',
            'incomes.payment_type',
            'incomes.paid_by',
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

            $incomeupdatedata = [
                'description' => $request->description,
                'amount' => $request->amount,
                'payment_type' => $request->payment_type,
                'paid_by' => $request->paid_by,
            ];

            $income = $income->update($incomeupdatedata);

            $ledger = [
                'description' => $request->description,
                'credited' => $request->amount,
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
}
