<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessSubscriptionCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:process-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process daily subscription billing cycles and status updates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Starting daily subscription processing: ' . Carbon::now());

        // Get all companies with active subscriptions
        $companies = DB::table('company')
            ->where('is_deleted', 0)
            ->where('is_active', 1)
            ->get();



        foreach ($companies as $company) {
            Log::info("Processing company {$company->id} with dbname {$company->dbname}");
            if ($company->id != 1) {
                $this->processCompanySubscriptions($company);
            }
        }

        Log::info('Daily subscription processing completed: ' . Carbon::now());
        $this->info('Daily subscription processing completed successfully.');
        return 0;
    }

    private function processCompanySubscriptions($company)
    {
        try {
            config(['database.connections.dynamic_connection.database' => $company->dbname]);
            // Establish connection to the dynamic database
            DB::purge('dynamic_connection');
            DB::reconnect('dynamic_connection');

            // Get active subscriptions (not expired or suspended)
            $subscriptions = DB::table('subscriptions')
                ->where('is_deleted', 0)
                ->where('company_id', $company->id)
                ->whereNotIn('status', ['expired', 'suspended'])
                ->get();

            foreach ($subscriptions as $subscription) {
                Log::info("Processing subscription {$subscription->subscription_start_date} for company {$company->id}");
                $this->processSubscription($subscription, $company);
            }
        } catch (\Exception $e) {
            Log::error("Error processing company {$company->id}: " . $e->getMessage());
        }
    }

    private function processSubscription($subscription, $company)
    {
        $today = Carbon::now()->format('Y-m-d');
        Log::info("Processing subscription {$subscription->id} for company {$company->id} on {$today}");

        // 2.1 Check payment_cycle_start_date
        if ($subscription->payment_cycle_start_date == $today) {
            $this->handlePaymentCycleStart($subscription, $company);
        }

        // 2.2 Check next_billing_date and payment status not pending
        if ($subscription->next_billing_date == $today) {
            // Check if current payment is not pending
            $currentPayment = DB::table('subscription_payments')
                ->where('subscription_id', $subscription->id)
                ->where('payment_status', '!=', 'pending')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($currentPayment) {
                $this->handleNextBillingDate($subscription, $company);
            }
        }
    }

    private function handlePaymentCycleStart($subscription, $company)
    {
        try {
            // Change subscription status to active
            DB::table('subscriptions')
                ->where('id', $subscription->id)
                ->update(['status' => 'active']);

            // Check if payment status is pending
            $payment = DB::table('subscription_payments')
                ->where('subscription_id', $subscription->id)
                ->where('payment_status', 'pending')
                ->first();

            if ($payment) {
                // Change subscription status to expired
                DB::table('subscriptions')
                    ->where('id', $subscription->id)
                    ->update(['status' => 'expired']);

                // Set company users is_active = 2 where is_active = 1
                $this->deactivateCompanyUsers($company);
            }

            Log::info("Processed payment cycle start for subscription {$subscription->id}");
        } catch (\Exception $e) {
            Log::error("Error handling payment cycle start for subscription {$subscription->id}: " . $e->getMessage());
        }
    }

    private function handleNextBillingDate($subscription, $company)
    {
        try {
            // Calculate new dates based on current payment cycle
            $currentStartDate = new \DateTime($subscription->payment_cycle_start_date);
            $currentEndDate = new \DateTime($subscription->payment_cycle_end_date);
            $cycleDays = $this->getCycleDays($subscription->billing_cycle);

            // New payment cycle starts day after current cycle ends
            $newStartDate = clone $currentEndDate;
            $newStartDate->modify('+1 day');

            // New payment cycle ends after cycle days from start date
            $newEndDate = clone $newStartDate;
            $newEndDate->modify('+' . ($cycleDays - 1) . ' days');

            // Next billing date is 15 days before payment cycle ends
            $newNextBillingDate = clone $newEndDate;
            $newNextBillingDate->modify('-15 days');

            // Update subscription
            DB::table('subscriptions')
                ->where('id', $subscription->id)
                ->update([
                    'payment_cycle_start_date' => $newStartDate->format('Y-m-d'),
                    'payment_cycle_end_date' => $newEndDate->format('Y-m-d'),
                    'next_billing_date' => $newNextBillingDate->format('Y-m-d'),
                    'updated_by' => 0, // System generated
                ]);

            // Create new payment entry
            $paymentId = DB::table('subscription_payments')->insertGetId([
                'subscription_id' => $subscription->id,
                'payment_start_date' => $newStartDate->format('Y-m-d'),
                'payment_end_date' => $newEndDate->format('Y-m-d'),
                'next_billing_date' => $newNextBillingDate->format('Y-m-d'),
                'payment_status' => 'pending',
                'created_by' => 0, // System generated
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // add billing cycle notes
            DB::connection('dynamic_connection')->table('billing_cycle_notes')->insert([
                'subscription_id' => $subscription->id,
                'billing_cycle_id' => $paymentId,
                'billing_cycle_start_date' => $newStartDate->format('Y-m-d'),
                'billing_cycle_end_date' => $newEndDate->format('Y-m-d'),
                'next_billing_date' => $newNextBillingDate->format('Y-m-d'),
                'payment_status' => 'pending',
                'created_by' => 0, // System generated
            ]);

            Log::info("Created new billing cycle for subscription {$subscription->id}");
        } catch (\Exception $e) {
            Log::error("Error handling next billing date for subscription {$subscription->id}: " . $e->getMessage());
        }
    }

    private function getCycleDays($billingCycle)
    {
        switch ($billingCycle) {
            case 'monthly':
                return 30;
            case 'quarterly':
                return 90;
            case 'yearly':
                return 365;
            default:
                return 30;
        }
    }

    private function deactivateCompanyUsers($company)
    {
        try {
            Log::info("Deactivating users for company {$company->id}");
            DB::table('users')
                ->where('company_id', $company->id)
                ->where('is_active', 1)
                ->update([
                    'is_active' => 2,
                    'api_token' => null,
                    'super_api_token' => null,
                ]);
        } catch (\Exception $e) {
            Log::error("Error deactivating users for company {$company->id}: " . $e->getMessage());
        }
    }
}
