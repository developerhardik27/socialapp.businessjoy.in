# Notification & Push Notification API — Mobile Integration Guide

Base URL: `{BASE}/api`
All endpoints require `token`, `user_id`, `company_id` (same as every other API — query params or form fields).

---

## 1. Device Registration

Call after login and after the app receives a new Expo push token.

```
POST /api/device/register
```

| Param | Required | Notes |
|---|---|---|
| `push_token` | yes | Expo push token e.g. `ExponentPushToken[xxxx]` |
| `platform` | yes | `android` or `ios` |

Response `200`:
```json
{ "status": 200, "message": "Device registered successfully",
  "device": { "id": 1, "user_id": "2", "push_token": "ExponentPushToken[...]", "platform": "android" } }
```
Re-registering the same token is idempotent (updates platform, reassigns to the logged-in user). Multiple devices per user are supported.

## 2. Unregister Device

Call on logout BEFORE clearing local session.

```
POST /api/device/unregister
Body: { "push_token": "ExponentPushToken[xxxx]" }
→ { "status": 200, "message": "Device unregistered successfully" }
```

## 3. List Notifications

```
GET /api/notifications?page=1&per_page=20
```

Response: Laravel paginator — `data` contains `current_page`, `last_page`, `next_page_url` for infinite scroll, and `data[]` items:

```json
{
  "id": 13,
  "type": "like",               // post|job|event|policy|biodata|karobari_meeting|karobari_meeting_edited|comment|like
  "title": "New Like",
  "body": "Someone liked your post",
  "item_type": "post",          // navigate to this entity
  "item_id": 5,
  "actor_name": "Test Dev",
  "actor_img": "2/user_9/img.png",
  "is_read": 0,
  "created_at": "2026-11-19T10:47:06.000000Z"
}
```

## 4. Mark One Read

```
POST /api/notifications/{id}/read
→ { "status": 200, "message": "Notification marked as read" }
→ 404 if not your notification
```

## 5. Mark All Read

```
POST /api/notifications/read-all
→ { "status": 200, "message": "All notifications marked as read" }
```

## 6. Unread Count (badge)

```
GET /api/notifications/unread-count
→ { "status": 200, "count": 3 }
```

---

## Push Notifications

- Server sends via **Expo Push API** — the app just needs `expo-notifications` + a valid `ExponentPushToken[...]`.
- Push payload `data`:
  ```json
  { "notification_id": 13, "type": "like", "item_type": "post", "item_id": 5 }
  ```
- Handle tap by routing on `item_type` + `item_id`.
- Dead tokens are auto-cleaned server-side — no action needed.

## Deep-link types

| `item_type` | Meaning |
|---|---|
| `post` / `job` / `event` / `policy` / `biodata` | open that record (type=`comment`/`like` → open the same item) |
| `karobari_meeting` | open meeting detail |

## When notifications are created

| Event | Who gets it |
|---|---|
| new post / job / event / policy / biodata | all users except actor |
| comment on your post / job / event | item owner |
| like on your post / job / event | item owner |
| new / edited karobari meeting | karobari members only |
