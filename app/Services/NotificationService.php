<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Creates notification records and dispatches Expo push messages.
 *
 * Conventions for this codebase:
 *  - notifications / user_devices live on `dynamic_connection` (company DB)
 *  - users live on the master connection; `users.id` == `$this->userId`
 *    everywhere and `users.family_person_id` links to family_person
 *
 * The class is instantiated per event: `new NotificationService($companyId, $actorId)`
 * and must be used after `commonController::dbname()` has connected
 * the dynamic company database.
 */
class NotificationService
{
    protected $companyId;
    protected $actorId;
    protected $actorName;
    protected $notificationModel;
    protected $userDeviceModel;

    public function __construct($companyId, $actorId)
    {
        $this->companyId = $companyId;
        $this->actorId = $actorId;
        $this->notificationModel = 'App\\Models\\v4_4_4\\Notification';
        $this->userDeviceModel = 'App\\Models\\v4_4_4\\UserDevice';
    }

    /**
     * Notify every non-deleted user of this company (actor excluded).
     */
    public function notifyAllUsers(string $type, string $title, string $body, string $itemType, $itemId)
    {
        $recipientIds = DB::connection()->table('users')
            ->where('company_id', $this->companyId)
            ->where('is_deleted', 0)
            ->pluck('id')
            ->all();

        $this->createAndPush($recipientIds, $type, $title, $body, $itemType, $itemId);
    }

    /**
     * Notify a single user (the item owner). No-op when owner is the actor.
     */
    public function notifyUser($recipientId, string $type, string $title, string $body, string $itemType, $itemId)
    {
        if (empty($recipientId)) {
            Log::info("NotificationService: {$type}/{$itemType}#{$itemId} skipped - no owner found");
            return;
        }
        $this->createAndPush([(int) $recipientId], $type, $title, $body, $itemType, $itemId);
    }

    /**
     * Notify all Karobari members (kraobari_member -> family_person -> users).
     */
    public function notifyKarobariMembers(string $type, string $title, string $body, string $itemType, $itemId)
    {
        $familyPersonIds = DB::connection('dynamic_connection')
            ->table('kraobari_member')
            ->where('is_deleted', 0)
            ->pluck('familyPersonId');

        if ($familyPersonIds->isEmpty()) {
            return;
        }

        $recipientIds = DB::connection()->table('users')
            ->where('company_id', $this->companyId)
            ->where('is_deleted', 0)
            ->whereIn('family_person_id', $familyPersonIds)
            ->pluck('id')
            ->all();

        $this->createAndPush($recipientIds, $type, $title, $body, $itemType, $itemId);
    }

    /**
     * Actor's display name (firstname + lastname from users table).
     */
    protected function actorDisplayName()
    {
        if ($this->actorName === null) {
            $actor = DB::connection()->table('users')->where('id', $this->actorId)
                ->select('firstname', 'lastname')->first();
            $this->actorName = $actor ? trim($actor->firstname . ' ' . $actor->lastname) : '';
        }
        return $this->actorName;
    }

    /**
     * Insert one notification per recipient (skipping the actor and
     * exact-duplicate unread notifications) then push via Expo.
     * `{actor}` in title/body is replaced with the actor's display name.
     */
    protected function createAndPush(array $recipientIds, string $type, string $title, string $body, string $itemType, $itemId)
    {
        // company DB may not have the notification tables yet - skip silently
        try {
            if (!\Illuminate\Support\Facades\Schema::connection('dynamic_connection')->hasTable('notifications')) {
                Log::warning("NotificationService: notifications table missing on dynamic_connection - skipped {$type}");
                return;
            }
        } catch (\Throwable $e) {
            return;
        }

        $title = str_replace('{actor}', $this->actorDisplayName(), $title);
        $body  = str_replace('{actor}', $this->actorDisplayName(), $body);

        $recipientIds = array_values(array_unique(array_filter($recipientIds, function ($id) {
            return $id && $id != $this->actorId;
        })));

        if (empty($recipientIds)) {
            Log::info("NotificationService: {$type}/{$itemType}#{$itemId} skipped - no recipients (actor {$this->actorId} excluded)");
            return;
        }

        $created = [];
        foreach ($recipientIds as $userId) {
            // avoid exact duplicates for the same user/actor/item/type that are still unread
            $exists = $this->notificationModel::where('user_id', $userId)
                ->where('actor_id', $this->actorId)
                ->where('type', $type)
                ->where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->where('is_read', 0)
                ->exists();
            if ($exists) {
                Log::info("NotificationService: {$type}/{$itemType}#{$itemId} duplicate skipped for user {$userId}");
                continue;
            }

            $created[] = $this->notificationModel::create([
                'user_id'    => $userId,
                'actor_id'   => $this->actorId,
                'type'       => $type,
                'title'      => $title,
                'body'       => $body,
                'item_type'  => $itemType,
                'item_id'    => $itemId,
                'is_read'    => 0,
            ]);
        }

        if (empty($created)) {
            return;
        }

        Log::info('NotificationService: ' . count($created) . " notification(s) created for {$type}/{$itemType}#{$itemId} by actor {$this->actorId}");
        $this->push($created, $type, $title, $body, $itemType, $itemId);
    }

    /**
     * Fetch device tokens for the recipients and send Expo messages.
     * Push failures never surface to the caller.
     */
    protected function push(array $notifications, string $type, string $title, string $body, string $itemType, $itemId)
    {
        try {
            $userIds = array_map(fn($n) => $n->user_id, $notifications);

            $devices = $this->userDeviceModel::whereIn('user_id', $userIds)->get();
            if ($devices->isEmpty()) {
                return;
            }

            $notifByUser = collect($notifications)->keyBy('user_id');

            $messages = $devices->map(function ($device) use ($notifByUser, $type, $title, $body, $itemType, $itemId) {
                $notif = $notifByUser->get($device->user_id);
                if (!$notif) {
                    return null;
                }
                return [
                    'to'    => $device->push_token,
                    'title' => $title,
                    'body'  => $body,
                    'sound' => 'default',
                    'data'  => [
                        'notification_id' => $notif->id,
                        'type'            => $type,
                        'item_type'       => $itemType,
                        'item_id'         => $itemId,
                    ],
                ];
            })->filter()->values()->all();

            if (empty($messages)) {
                return;
            }

            ExpoPushService::send($messages, function ($token) {
                // token is dead on Expo side -> drop it so we never retry it
                $this->userDeviceModel::where('push_token', $token)->delete();
            });
        } catch (\Throwable $e) {
            Log::warning('Push notification delivery failed: ' . $e->getMessage());
        }
    }
}
