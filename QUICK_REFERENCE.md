# Quick Reference: Notification System

## Notification Types at a Glance

| Type | Section | When | Recipient | Sender |
|------|---------|------|-----------|--------|
| `booking` | Exchange | Service/event booking request | Provider/Organizer | Requester |
| `accepted` | Exchange | Booking accepted | Requester | Provider |
| `being_refused` | Exchange | Booking rejected | Requester | Provider |
| `acceptedInEvent` | events | Event join accepted | Requester | Organizer |
| `RejectedFromEvent` | events | Event join rejected | Requester | Organizer |
| `spent` | credits | Credits deducted | User who spent | NULL (system) |
| `earned` | credits | Credits received | User who earned | NULL (system) |

---

## Common Code Snippets

### Create Booking Notification
```php
$notifStmt = $conn->prepare("INSERT INTO UserNotifications 
    (UserId, SenderId, NotificationType, Title, Message, NotificationSection)
    VALUES (?, ?, 'booking', ?, ?, 'Exchange')");
$notifStmt->bind_param('iiss', $recipientId, $senderId, $title, $message);
$notifStmt->execute();
```

### Create Credit Spent Notification
```php
$notifStmt = $conn->prepare("INSERT INTO UserNotifications 
    (UserId, NotificationType, Title, Message, NotificationSection)
    VALUES (?, 'spent', 'Credits Spent', ?, 'credits')");
$notifStmt->bind_param('is', $userId, $message);
$notifStmt->execute();
```

### Create Event Acceptance Notification
```php
$notifStmt = $conn->prepare("INSERT INTO UserNotifications 
    (UserId, SenderId, NotificationType, Title, Message, NotificationSection)
    VALUES (?, ?, 'acceptedInEvent', ?, ?, 'events')");
$notifStmt->bind_param('iiss', $attendeeId, $organizerId, $title, $message);
$notifStmt->execute();
```

---

## API Endpoints Quick Reference

| Endpoint | Method | Purpose | Parameters |
|----------|--------|---------|------------|
| `/dashboard/post/acceptExchange.php` | POST | Accept service booking | `exchangeId` |
| `/dashboard/post/rejectExchange.php` | POST | Reject service booking | `exchangeId` |
| `/dashboard/events/eventsAPI/acceptEventAttendee.php` | POST | Accept event join | `eventId`, `attendeeId` |
| `/dashboard/events/eventsAPI/rejectEventAttendee.php` | POST | Reject event join | `eventId`, `attendeeId` |
| `/api/notifications/fetch.php` | GET | Get user notifications | - |
| `/api/notifications/mark_read.php` | POST | Mark as read | `notificationId` |

---

## Validation Rules

### Required Fields
- `UserId` - Recipient (required)
- `NotificationType` - Must match allowed types for section
- `Title` - Min 5 characters
- `Message` - Required
- `NotificationSection` - Required

### Section-Type Matching (enforced by trigger)
```
Exchange  → booking, accepted, completing, being_refused
events    → acceptedInEvent, RejectedFromEvent
credits   → earned, spent
Reviews   → like, comment, rating
```

---

## Common Queries

### Get User's Unread Notifications
```php
$sql = "SELECT * FROM UserNotifications 
        WHERE UserId = ? AND IsRead = 'no' 
        ORDER BY CreatedAt DESC";
```

### Count Notifications by Section
```php
$sql = "SELECT NotificationSection, COUNT(*) as count 
        FROM UserNotifications 
        WHERE UserId = ? AND IsRead = 'no' 
        GROUP BY NotificationSection";
```

### Delete Old Notifications
```php
$sql = "DELETE FROM UserNotifications 
        WHERE UserId = ? AND CreatedAt < DATE_SUB(NOW(), INTERVAL 30 DAY)";
```

---

## Testing Commands

### Run Full Test Suite
```bash
cd /path/to/project
php test_notification_system.php
```

### Add Test Credits
```bash
php add_test_credits.php?amount=100
```

### Check Notification Count
```sql
SELECT NotificationType, NotificationSection, COUNT(*) as count 
FROM UserNotifications 
GROUP BY NotificationType, NotificationSection;
```

---

## Troubleshooting

### Notification Not Created
1. Check notification type matches section
2. Verify foreign key constraints (UserId, SenderId exist)
3. Check Title length (min 5 characters)
4. Verify database trigger is not blocking insert

### Credit Transfer Failed
1. Check user has sufficient balance
2. Verify transaction is wrapped in BEGIN/COMMIT
3. Check CreditsCost is set correctly in Exchanges/Events
4. Verify Users.CreditBalance is not negative

### Event Acceptance Issues
1. Verify organizer owns the event
2. Check EventsAttendees record exists
3. Verify attendee status is 'registered' (not 'confirmed')
4. Check event is not full (CurrentAttendeesNumber < MaxAttendees)

---

*Quick Reference v1.0 - Last Updated: February 5, 2026*
