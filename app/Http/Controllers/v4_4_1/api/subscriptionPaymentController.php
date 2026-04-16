<?php

namespace App\Http\Controllers\v4_4_1\api;

use App\Models\company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class subscriptionPaymentController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $subscriptionPaymentModel, $subscriptionHistoryModel, $subscriptionModel;

    public function __construct(Request $request)
    {
        $this->companyId = $request->company_id;
        $this->userId = $request->user_id;

        $this->dbname($this->companyId);

        // check permissions
        $user_rp = DB::connection('dynamic_connection')->table('user_permissions')->where('user_id', $this->userId)->value('rp');

        if (empty($user_rp)) {
            $this->customerrorresponse();
        }

        $this->rp = json_decode($user_rp, true);
        $this->masterdbname = DB::connection()->getDatabaseName();

        $this->subscriptionPaymentModel = $this->getmodel('SubscriptionPayment');
        $this->subscriptionHistoryModel = $this->getmodel('SubscriptionHistory');
        $this->subscriptionModel = $this->getmodel('Subscription');
    }

    public function index(Request $request)
    {
        // permission check if exists
        if ($this->rp['adminmodule']['subscription']['view'] != 1) {
            return response()->json([
                'status' => 500,
                'message' => 'You are Unauthorized',
                'data' => [],
                'recordsTotal' => 0
            ]);
        }

        $payments = $this->subscriptionPaymentModel::leftJoin('subscriptions', 'subscription_payments.subscription_id', '=', 'subscriptions.id')
            ->leftJoin($this->masterdbname . '.company', 'subscriptions.company_id', '=', $this->masterdbname . '.company.id')
            ->leftJoin($this->masterdbname . '.company_details', 'company.company_details_id', '=', $this->masterdbname . '.company_details.id')
            ->leftJoin($this->masterdbname . '.packages', 'subscriptions.package_id', '=', $this->masterdbname . '.packages.id')
            ->select(
                'subscription_payments.id',
                'subscription_payments.subscription_id',
                'subscription_payments.payment_start_date',
                'subscription_payments.payment_end_date',
                'subscription_payments.next_billing_date',
                'subscription_payments.payment_status',
                'subscription_payments.created_at',
                'subscriptions.emi_cost',
                'company_details.name as company_name',
                'packages.name as package_name'
            )
            ->where('subscription_payments.is_deleted', 0)
            ->where('subscriptions.is_deleted', 0);

        // Multi Select Filters Mapping
        $multiSelectFilters = [
            'payment_status' => 'subscription_payments.payment_status',
            'company'        => 'subscriptions.company_id',
            'package'        => 'subscriptions.package_id',
        ];

        foreach ($multiSelectFilters as $requestKey => $column) {
            if ($request->filled($requestKey) && is_array($request->$requestKey)) {
                $values = array_filter($request->$requestKey);
                if (!empty($values)) {
                    $payments->whereIn($column, $values);
                }
            }
        }


        // Date Range Filters Mapping
        $dateRangeFilters = [
            'next_billing'  => 'subscription_payments.next_billing_date',
            'payment_start' => 'subscription_payments.payment_start_date',
            'payment_end'   => 'subscription_payments.payment_end_date',
        ];

        foreach ($dateRangeFilters as $prefix => $column) {

            $fromKey = "{$prefix}_from_date";
            $toKey   = "{$prefix}_to_date";

            if ($request->filled($fromKey)) {
                $payments->where($column, '>=', $request->$fromKey);
            }

            if ($request->filled($toKey)) {
                $payments->where($column, '<=', $request->$toKey);
            }
        }

        // Only show latest payment per subscription
        $latestPayments = $payments->orderBy('subscription_payments.created_at', 'desc')
            ->get()
            ->groupBy('subscription_id');

        $finalData = [];
        foreach ($latestPayments as $subscriptionId => $paymentGroup) {
            $finalData[] = $paymentGroup->first();
        }

        $total = count($finalData);
        $rows = collect($finalData);

        if ($rows->isEmpty()) {
            return DataTables::of($rows)->with([
                'status' => 404,
                'message' => 'No Data Found',
                'recordsTotal' => $total
            ])->make(true);
        }

        return DataTables::of($rows)->with([
            'status' => 200,
            'recordsTotal' => $total
        ])->make(true);
    }

    public function updateStatus(Request $request, $id)
    {

        return $this->executeTransaction(function () use ($request, $id) {
            if ($this->rp['adminmodule']['subscription']['edit'] != 1) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }

            $payment = $this->subscriptionPaymentModel::find($id);

            if (!$payment) {
                return $this->successresponse(404, 'message', 'No such payment found');
            }

            $validator = Validator::make($request->all(), [
                'payment_status' => 'required|string|in:pending,paid',
            ]);

            if ($validator->fails()) {
                return $this->errorresponse(422, $validator->messages());
            }

            $oldStatus = $payment->payment_status;
            $payment->update([
                'payment_status' => $request->payment_status,
                'updated_by' => $this->userId,
            ]);

            // Get subscription details
            $subscription = $this->subscriptionModel::find($payment->subscription_id);
            $oldSubscriptionStatus = $subscription->status;

            // If payment status changed to paid
            if ($oldStatus !== 'paid' && $request->payment_status === 'paid') {
                // Update subscription status to active if not already active
                if ($subscription->status !== 'active') {
                    $subscription->update([
                        'status' => 'active',
                        'updated_by' => $this->userId,
                    ]);
                }

                // Set company users is_active = 1 where is_active = 2 if subscription status not active already
                if ($oldSubscriptionStatus !== 'active') {
                    $this->reactivateCompanyUsers($subscription->company_id);
                }

                // Increase subscription renew count
                $subscription->increment('renew_count', 1);

                $company = company::find($subscription->company_id);

                config(['database.connections.dynamic_connection.database' => $company->dbname]);

                // Establish connection to the dynamic database
                DB::purge('dynamic_connection');
                DB::reconnect('dynamic_connection');

                // Update status to paid in billing_cycle_notes with matching billing cycle record
                DB::connection('dynamic_connection')
                    ->table('billing_cycle_notes')
                    ->where('subscription_id', $payment->subscription_id)
                    ->where('billing_cycle_start_date', $payment->payment_start_date)
                    ->update([
                        'payment_status' => 'paid',
                        'updated_by' => $this->userId,
                        'updated_at' => now(),
                    ]);
            }

            // Create history record
            $this->subscriptionHistoryModel::create([
                'subscription_id' => $payment->subscription_id,
                'action' => 'payment status updated',
                'notes' => 'Payment status updated to ' . $request->payment_status,
                'action_date' => now()->toDateString(),
                'created_by' => $this->userId,
            ]);

            return $this->successresponse(200, 'message', 'Payment status updated');
        });
    }

    private function reactivateCompanyUsers($companyId)
    {
        try {
            DB::table('users')
                ->where('company_id', $companyId)
                ->where('is_active', 2)
                ->update(['is_active' => 1]);

        } catch (\Exception $e) {
            Log::error("Error reactivating users for company {$companyId}: " . $e->getMessage());
        }
    }
}
