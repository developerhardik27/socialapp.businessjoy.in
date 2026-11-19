<?php

namespace App\Http\Controllers\v4_4_4\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $notificationModel, $userDeviceModel;
    public function __construct(Request $request)
    {

        $this->companyId = $request->company_id;
        $this->userId = $request->user_id;

        $this->dbname($this->companyId);

        $this->masterdbname = DB::connection()->getDatabaseName();
        $this->notificationModel = $this->getmodel('Notification');
        $this->userDeviceModel = $this->getmodel('UserDevice');

    }

    // true only when the resolved company DB actually has the notification tables
    private function tablesReady($table)
    {
        try {
            return Schema::connection('dynamic_connection')->hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * POST device/register
     * Register the logged-in user's device token for push notifications.
     */
    public function registerDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'push_token' => 'required|string|max:500',
            'platform'   => 'required|string|in:android,ios',
        ]);
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors()->first());
        }
        if (!$this->tablesReady('user_devices')) {
            return $this->successresponse(500, 'message', 'Notification feature not available for this company');
        }

        // one row per token: re-registering assigns it to the current user
        $device = $this->userDeviceModel::updateOrCreate(
            ['push_token' => $request->push_token],
            ['user_id' => $this->userId, 'platform' => $request->platform]
        );

        return $this->successresponse(200, 'message', 'Device registered successfully', 'device', $device);
    }

    /**
     * POST device/unregister
     * Remove this user's device token (e.g. during logout).
     */
    public function unregisterDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'push_token' => 'required|string|max:500',
        ]);
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors()->first());
        }
        if (!$this->tablesReady('user_devices')) {
            return $this->successresponse(200, 'message', 'Device unregistered successfully');
        }

        $this->userDeviceModel::where('user_id', $this->userId)
            ->where('push_token', $request->push_token)
            ->delete();

        return $this->successresponse(200, 'message', 'Device unregistered successfully');
    }

    /**
     * GET notifications (paginated, newest first)
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = ($perPage > 0 && $perPage <= 100) ? $perPage : 20;

        if (!$this->tablesReady('notifications')) {
            $empty = new LengthAwarePaginator([], 0, $perPage, 1, [
                'path' => $request->url(), 'query' => $request->query(),
            ]);
            return $this->successresponse(200, 'data', $empty);
        }

        $notifications = $this->notificationModel
            ::leftJoin($this->masterdbname . '.users as actor', 'actor.id', '=', 'notifications.actor_id')
            ->where('notifications.user_id', $this->userId)
            ->when($request->get('unread'), function ($q) {
                $q->where('notifications.is_read', 0);
            })
            ->select(
                'notifications.id',
                'notifications.type',
                'notifications.title',
                'notifications.body',
                'notifications.item_type',
                'notifications.item_id',
                'notifications.is_read',
                'notifications.created_at',
                DB::raw("CONCAT(COALESCE(actor.firstname,''), ' ', COALESCE(actor.lastname,'')) as actor_name"),
                'actor.img as actor_img'
            )
            ->orderBy('notifications.created_at', 'desc')
            ->paginate($perPage);

        return $this->successresponse(200, 'data', $notifications);
    }

    /**
     * POST notifications/{id}/read
     */
    public function markAsRead($id)
    {
        if (!$this->tablesReady('notifications')) {
            return $this->successresponse(404, 'message', 'No such notification found');
        }
        $notification = $this->notificationModel
            ::where('id', $id)
            ->where('user_id', $this->userId) // users can only touch their own
            ->first();

        if (!$notification) {
            return $this->successresponse(404, 'message', 'No such notification found');
        }

        $notification->update(['is_read' => 1]);

        return $this->successresponse(200, 'message', 'Notification marked as read');
    }

    /**
     * POST notifications/read-all
     */
    public function markAllAsRead()
    {
        if (!$this->tablesReady('notifications')) {
            return $this->successresponse(200, 'message', 'All notifications marked as read');
        }
        $this->notificationModel::where('user_id', $this->userId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return $this->successresponse(200, 'message', 'All notifications marked as read');
    }

    /**
     * GET notifications/unread-count
     */
    public function unreadCount()
    {
        if (!$this->tablesReady('notifications')) {
            return $this->successresponse(200, 'count', 0);
        }
        $count = $this->notificationModel::where('user_id', $this->userId)
            ->where('is_read', 0)
            ->count();

        return $this->successresponse(200, 'count', $count);
    }
}
