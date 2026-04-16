<?php

namespace App\Http\Controllers\v4_4_4\api;

use App\Models\company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class subscriptionController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $subscriptionModel, $subscriptionHistoryModel, $subscriptionPaymentModel, $billingCycleNoteModel;

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

        $this->subscriptionModel = $this->getmodel('Subscription');
        $this->subscriptionHistoryModel = $this->getmodel('SubscriptionHistory');
        $this->subscriptionPaymentModel = $this->getmodel('SubscriptionPayment');
        $this->billingCycleNoteModel = $this->getmodel('BillingCycleNote');
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

        $subs = $this->subscriptionModel::leftJoin($this->masterdbname . '.company', 'subscriptions.company_id', '=', $this->masterdbname . '.company.id')
            ->join($this->masterdbname . '.company_details', 'company.company_details_id', '=', $this->masterdbname . '.company_details.id')
            ->leftJoin($this->masterdbname . '.packages', 'subscriptions.package_id', '=', $this->masterdbname . '.packages.id')
            ->select(
                'subscriptions.id',
                'subscriptions.company_id',
                'subscriptions.package_id',
                'subscriptions.trial_start_date',
                'subscriptions.trial_end_date',
                'subscriptions.trial_days',
                'subscriptions.subscription_start_date',
                'subscriptions.subscription_end_date',
                'subscriptions.billing_cycle',
                'subscriptions.cycle_duration',
                'subscriptions.payment_cycle_start_date',
                'subscriptions.payment_cycle_end_date',
                'subscriptions.next_billing_date',
                'subscriptions.package_type',
                'subscriptions.package_price',
                'subscriptions.emi_cost',
                'subscriptions.emi_calculation',
                'subscriptions.auto_generate_invoice',
                'subscriptions.status',
                'subscriptions.created_at',
                'company_details.name as company_name',
                'packages.name as package_name'
            )
            ->where('subscriptions.is_deleted', 0);

        if ($this->rp['adminmodule']['subscription']['alldata'] != 1) {
            $subs->where('subscriptions.created_by', $this->userId);
        }

        // Multi Select Filters Mapping
        $multiSelectFilters = [
            'status'        => 'subscriptions.status',
            'company'       => 'subscriptions.company_id',
            'package'       => 'subscriptions.package_id',
            'billing_cycle' => 'subscriptions.billing_cycle',
        ];

        foreach ($multiSelectFilters as $requestKey => $column) {
            if ($request->filled($requestKey) && is_array($request->$requestKey)) {
                $values = array_filter($request->$requestKey);
                if (!empty($values)) {
                    $subs->whereIn($column, $values);
                }
            }
        }


        // Date Range Filters Mapping
        $dateRangeFilters = [
            'payment_start' => 'subscriptions.payment_cycle_start_date',
            'payment_end'   => 'subscriptions.payment_cycle_end_date',
            'next_billing' => 'subscriptions.next_billing_date',
        ];

        foreach ($dateRangeFilters as $prefix => $column) {

            $fromKey = "{$prefix}_from_date";
            $toKey   = "{$prefix}_to_date";

            if ($request->filled($fromKey)) {
                $subs->where($column, '>=', $request->$fromKey);
            }

            if ($request->filled($toKey)) {
                $subs->where($column, '<=', $request->$toKey);
            }
        }


        $total = $subs->count();
        $rows = $subs->orderBy('subscriptions.created_at', 'desc')->get();

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

    public function store(Request $request)
    {

        if ($this->rp['adminmodule']['subscription']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'company' => 'required|integer',
            'package' => 'required|integer',
            'trial_start_date' => 'nullable|date',
            'trial_end_date' => 'nullable|date',
            'trial_days' => 'required|integer|min:0',
            'subscription_start_date' => 'required|date',
            'subscription_end_date' => 'nullable|date',
            'billing_cycle' => 'required|string',
            'cycle_duration' => 'required|string',
            'payment_cycle_start_date' => 'required|date',
            'payment_cycle_end_date' => 'required|date',
            'next_billing_date' => 'required|date',
            'package_type' => 'required|string',
            'package_price' => 'required|numeric',
            'emi_cost' => 'required|numeric',
            'emi_calculation' => 'required|string',
            'auto_generate_invoice' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        $company = company::find($request->company);

        config(['database.connections.dynamic_connection.database' => $company->dbname]);

        // Establish connection to the dynamic database
        DB::purge('dynamic_connection');
        DB::reconnect('dynamic_connection');

        return $this->executeTransaction(function () use ($request) {

            $subscription = $this->subscriptionModel::create([
                'company_id' => $request->company,
                'package_id' => $request->package,
                'trial_start_date' => $request->trial_start_date,
                'trial_end_date' => $request->trial_end_date,
                'trial_days' => $request->trial_days,
                'subscription_start_date' => $request->subscription_start_date,
                'subscription_end_date' => $request->subscription_end_date,
                'billing_cycle' => $request->billing_cycle,
                'cycle_duration' => $request->cycle_duration,
                'payment_cycle_start_date' => $request->payment_cycle_start_date,
                'payment_cycle_end_date' => $request->payment_cycle_end_date,
                'next_billing_date' => $request->next_billing_date,
                'package_type' => $request->package_type,
                'package_price' => $request->package_price ?? 0,
                'emi_cost' => $request->emi_cost ?? 0,
                'emi_calculation' => $request->emi_calculation,
                'auto_generate_invoice' => $request->auto_generate_invoice ? 1 : 0,
                'status' => $request->trial_days ? 'trial' : 'active',
                'created_by' => $this->userId,
            ]);

            if (!$subscription) {
                throw new \Exception('Subscription creation failed');
            }

            // Create default payment entry
            $subscriptionPayment = $this->subscriptionPaymentModel::create([
                'subscription_id' => $subscription->id,
                'payment_start_date' => $request->payment_cycle_start_date,
                'payment_end_date' => $request->payment_cycle_end_date,
                'next_billing_date' => $request->next_billing_date,
                'payment_status' => 'pending',
                'created_by' => $this->userId,
            ]);


            // Update package subscribed_count
            DB::table('packages')
                ->where('id', $request->package)
                ->increment('subscribed_count', 1);

            $this->subscriptionHistoryModel::create([
                'subscription_id' => $subscription->id,
                'action' => 'created',
                'notes' => 'Subscription created',
                'action_date' => now()->toDateString(),
                'created_by' => $this->userId,
            ]);

            // Create billing cycle notes entry
            $this->billingCycleNoteModel::create([
                'subscription_id' => $subscription->id,
                'billing_cycle_id' => $subscriptionPayment->id,
                'billing_cycle_start_date' => $request->payment_cycle_start_date,
                'billing_cycle_end_date' => $request->payment_cycle_end_date,
                'next_billing_date' => $request->next_billing_date,
                'payment_status' => 'pending',
                'created_by' => $this->userId,
            ]);
            return $this->successresponse(200, 'message', 'Subscription created', 'id', $subscription->id);
        });
    }

    public function show(string $id)
    {
        if ($this->rp['adminmodule']['subscription']['view'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $subscription = $this->subscriptionModel::where('id', $id)->where('is_deleted', 0)->first();

        if (!$subscription) {
            return $this->successresponse(404, 'message', 'No such subscription found');
        }

        if ($this->rp['adminmodule']['subscription']['alldata'] != 1) {
            if ($subscription->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        return $this->successresponse(200, 'subscription', $subscription);
    }

    public function update(Request $request, string $id)
    {
        if ($this->rp['adminmodule']['subscription']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'company' => 'required|integer',
            'package' => 'required|integer',
            'trial_start_date' => 'nullable|date',
            'trial_end_date' => 'nullable|date',
            'trial_days' => 'required|integer|min:0',
            'subscription_start_date' => 'required|date',
            'subscription_end_date' => 'nullable|date',
            'billing_cycle' => 'required|string',
            'cycle_duration' => 'required|string',
            'payment_cycle_start_date' => 'required|date',
            'payment_cycle_end_date' => 'required|date',
            'next_billing_date' => 'required|date',
            'package_type' => 'required|string',
            'package_price' => 'required|numeric',
            'emi_cost' => 'required|numeric',
            'emi_calculation' => 'required|string',
            'auto_generate_invoice' => 'required|boolean',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorresponse(422, $validator->messages());
        }

        $company = company::find($request->company);

        config(['database.connections.dynamic_connection.database' => $company->dbname]);

        // Establish connection to the dynamic database
        DB::purge('dynamic_connection');
        DB::reconnect('dynamic_connection');

        return $this->executeTransaction(function () use ($request, $id) {

            $subscription = $this->subscriptionModel::find($id);

            if (!$subscription) {
                return $this->successresponse(404, 'message', 'No such subscription found');
            }

            if ($this->rp['adminmodule']['subscription']['alldata'] != 1) {
                if ($subscription->created_by != $this->userId) {
                    return $this->successresponse(500, 'message', 'You are Unauthorized');
                }
            }

            $oldPackageId = $subscription->package_id;
            $newPackageId = $request->package;
            $oldPaymentCycleStartDate = $subscription->payment_cycle_start_date;
            $newPaymentCycleStartDate = $request->payment_cycle_start_date;

            $subscription->update([
                'company_id' => $request->company,
                'package_id' => $request->package,
                'trial_start_date' => $request->trial_start_date,
                'trial_end_date' => $request->trial_end_date,
                'trial_days' => $request->trial_days,
                'subscription_start_date' => $request->subscription_start_date,
                'subscription_end_date' => $request->subscription_end_date,
                'billing_cycle' => $request->billing_cycle,
                'cycle_duration' => $request->cycle_duration,
                'payment_cycle_start_date' => $request->payment_cycle_start_date,
                'payment_cycle_end_date' => $request->payment_cycle_end_date,
                'next_billing_date' => $request->next_billing_date,
                'package_type' => $request->package_type,
                'package_price' => $request->package_price ?? 0,
                'emi_cost' => $request->emi_cost ?? 0,
                'emi_calculation' => $request->emi_calculation,
                'auto_generate_invoice' => $request->auto_generate_invoice ? 1 : 0,
                'status' => $request->status,
                'updated_by' => $this->userId,
            ]);

            // Update package subscribed_count if package changed
            if ($oldPackageId != $newPackageId) {
                DB::table('packages')
                    ->where('id', $oldPackageId)
                    ->decrement('subscribed_count', 1);

                DB::table('packages')
                    ->where('id', $newPackageId)
                    ->increment('subscribed_count', 1);
            }

            // If payment_cycle_start_date updated, update latest subscription payment and billing cycle notes
            if ($oldPaymentCycleStartDate != $newPaymentCycleStartDate) {
                // Update latest subscription payment who has status pending
                $latestPayment = $this->subscriptionPaymentModel::where('subscription_id', $subscription->id)
                    ->where('payment_status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($latestPayment) {
                    $latestPayment->update([
                        'payment_start_date' => $request->payment_cycle_start_date,
                        'payment_end_date' => $request->payment_cycle_end_date,
                        'next_billing_date' => $request->next_billing_date,
                        'updated_by' => $this->userId,
                    ]);

                    // Update billing cycle notes
                    $this->billingCycleNoteModel::where('subscription_id', $subscription->id)
                        ->where('billing_cycle_id', $latestPayment->id)
                        ->update([
                            'billing_cycle_start_date' => $request->payment_cycle_start_date,
                            'billing_cycle_end_date' => $request->payment_cycle_end_date,
                            'next_billing_date' => $request->next_billing_date,
                            'updated_by' => $this->userId,
                            'updated_at' => now(),
                        ]);
                }
            }

            $this->subscriptionHistoryModel::create([
                'subscription_id' => $subscription->id,
                'action' => 'updated',
                'notes' => 'Subscription updated',
                'action_date' => now()->toDateString(),
                'created_by' => $this->userId,
            ]);

            return $this->successresponse(200, 'message', 'Subscription updated');
        });
    }

    public function destroy(string $id)
    {
        return $this->executeTransaction(function () use ($id) {
            if ($this->rp['adminmodule']['subscription']['delete'] != 1) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }

            $subscription = $this->subscriptionModel::find($id);

            if (!$subscription) {
                return $this->successresponse(404, 'message', 'No such subscription found');
            }

            if ($this->rp['adminmodule']['subscription']['alldata'] != 1) {
                if ($subscription->created_by != $this->userId) {
                    return $this->successresponse(500, 'message', 'You are Unauthorized');
                }
            }

            $subscription->update(['is_deleted' => 1]);

            // Update package subscribed_count
            DB::table('packages')
                ->where('id', $subscription->package_id)
                ->decrement('subscribed_count', 1);

            $this->subscriptionHistoryModel::create([
                'subscription_id' => $subscription->id,
                'action' => 'deleted',
                'notes' => 'Subscription deleted',
                'action_date' => now()->toDateString(),
                'created_by' => $this->userId,
            ]);

            return $this->successresponse(200, 'message', 'Subscription deleted');
        });
    }

    public function changeStatus(Request $request, int $id)
    {
        if ($this->rp['adminmodule']['subscription']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }

        $subscription = $this->subscriptionModel::find($id);

        if (!$subscription) {
            return $this->successresponse(404, 'message', 'No such subscription found');
        }

        if ($this->rp['adminmodule']['subscription']['alldata'] != 1) {
            if ($subscription->created_by != $this->userId) {
                return $this->successresponse(500, 'message', 'You are Unauthorized');
            }
        }

        $status = $request->status;
        $allowed = ['active', 'trial', 'expired', 'suspended'];
        if (!in_array($status, $allowed)) {
            return $this->successresponse(500, 'message', 'Invalid status');
        }

        $subscription->status = $status;
        $subscription->save();

        $this->subscriptionHistoryModel::create([
            'subscription_id' => $subscription->id,
            'action' => 'status:' . $status,
            'notes' => 'Status changed to ' . $status,
            'action_date' => now()->toDateString(),
            'created_by' => $this->userId,
        ]);

        return $this->successresponse(200, 'message', 'Status updated');
    }

    // Extra: fetch history for a subscription
    public function history($id)
    {
        $history = $this->subscriptionHistoryModel::where('subscription_id', $id)->orderBy('created_at', 'desc')->get();
        return $this->successresponse(200, 'history', $history);
    }

    // Renew subscription (called by task scheduler)
    public function renewSubscription(Request $request)
    {
        return $this->executeTransaction(function () use ($request) {
            $subscriptions = $this->subscriptionModel::where('next_billing_date', '<=', now()->toDateString())
                ->notWhereIn('status', ['expired', 'suspended'])
                ->where('is_deleted', 0)
                ->get();

            foreach ($subscriptions as $subscription) {
                // Calculate new dates based on current payment cycle
                $currentStartDate = new \DateTime($subscription->payment_cycle_start_date);
                $currentEndDate = new \DateTime($subscription->payment_cycle_end_date);
                $cycleDays = $this->getCycleDays($subscription->billing_cycle);

                // New payment cycle starts the day after current cycle ends
                $newStartDate = clone $currentEndDate;
                $newStartDate->modify('+1 day');

                // New payment cycle ends after cycle days from start date
                $newEndDate = clone $newStartDate;
                $newEndDate->modify('+' . ($cycleDays - 1) . ' days');

                // Next billing date is 15 days before payment cycle ends
                $newNextBillingDate = clone $newEndDate;
                $newNextBillingDate->modify('-15 days');

                // Update subscription
                $subscription->update([
                    'payment_cycle_start_date' => $newStartDate->format('Y-m-d'),
                    'payment_cycle_end_date' => $newEndDate->format('Y-m-d'),
                    'next_billing_date' => $newNextBillingDate->format('Y-m-d'),
                    'renew_count' => $subscription->renew_count + 1,
                    'updated_by' => $this->userId,
                ]);

                // Create new payment entry
                $this->subscriptionPaymentModel::create([
                    'subscription_id' => $subscription->id,
                    'payment_start_date' => $newStartDate->format('Y-m-d'),
                    'payment_end_date' => $newEndDate->format('Y-m-d'),
                    'next_billing_date' => $newNextBillingDate->format('Y-m-d'),
                    'payment_status' => 'pending',
                    'created_by' => $this->userId,
                ]);

                // Add history entry
                $this->subscriptionHistoryModel::create([
                    'subscription_id' => $subscription->id,
                    'action' => 'renewed',
                    'notes' => 'Subscription renewed (Renewal #' . ($subscription->renew_count + 1) . ')',
                    'action_date' => now()->toDateString(),
                    'created_by' => $this->userId,
                ]);
            }

            return $this->successresponse(200, 'message', 'Subscriptions renewed successfully');
        });
    }

    // Helper method to get cycle days
    private function getCycleDays($cycleType)
    {
        $days = [
            'monthly' => 30,
            'quarterly' => 90,
            'yearly' => 365
        ];
        return $days[$cycleType] ?? 30;
    }
}
