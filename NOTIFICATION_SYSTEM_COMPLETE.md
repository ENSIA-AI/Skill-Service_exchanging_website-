# Notification System - Complete Implementation Guide

## Overview
This document describes the comprehensive notification system for the Skill Service Exchange platform. The system tracks notifications for bookings, exchanges, events, and credit transactions.

---

## Database Schema

### UserNotifications Table
```sql
CREATE TABLE UserNotifications (
  NotificationId INT AUTO_INCREMENT PRIMARY KEY,
  UserId INT NOT NULL,                    -- Recipient of the notification
  SenderId INT,                           -- Person who triggered (NULL for system notifications)
  NotificationType ENUM(
    'like','comment','rating',            -- Reviews section
    'booking','accepted','completing','being_refused',  -- Exchange section
    'acceptedInEvent','RejectedFromEvent', -- Events section
    'earned','spent'                      -- Credits section
  ) NOT NULL,
  Title VARCHAR(100) NOT NULL,
  Message TEXT NOT NULL,
  IsRead ENUM('yes','no') DEFAULT 'no',
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  NotificationSection ENUM('Reviews','Exchange','events','credits') NOT NULL,
  
  FOREIGN KEY (UserId) REFERENCES Users(UserId) ON DELETE CASCADE,
  FOREIGN KEY (SenderId) REFERENCES Users(UserId) ON DELETE SET NULL
)
```

### Notification Type Rules
- **Reviews Section**: 'like', 'comment', 'rating'
- **Exchange Section**: 'booking', 'accepted', 'completing', 'being_refused'
- **Events Section**: 'acceptedInEvent', 'RejectedFromEvent'
- **Credits Section**: 'earned', 'spent'

---

## Notification Triggers

### 1. Post/Service Bookings

#### A. Booking Request Created (Type: 'booking')
**When**: User requests to book a service (skill exchange OR credits)  
**Recipient**: Post owner (service provider)  
**Sender**: Requester  
**Section**: 'Exchange'  
**File**: `dashboard/post/postdetails.php` (lines 316-356)

**Notification Messages**:
- **Skill Exchange**: `"{RequesterName} has requested to exchange skills for your "{PostTitle}" session ({TimeSlot})"`
- **Credit Booking**: `"{RequesterName} has requested to book your "{PostTitle}" session for {Credits} credits ({TimeSlot})"`

**Implementation**:
```php
// In postdetails.php after inserting into Exchanges table
if ($paymentMethod === 'exchange') {
    $message = "$requesterName has requested to exchange skills for your \"$postTitle\" session ($timeSlotDisplay)";
    $title = "Skill Exchange Request";
} else {
    $message = "$requesterName has requested to book your \"$postTitle\" session for $requiredCredits credits ($timeSlotDisplay)";
    $title = "Booking Request";
}

$insertQuery = "INSERT INTO UserNotifications 
    (RecipientId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
    VALUES (?, ?, 'booking', ?, ?, 'no', NOW(), 'Exchange')";
```

#### B. Booking Request Accepted (Type: 'accepted')
**When**: Post owner accepts the booking request  
**Recipient**: Requester (who requested the service)  
**Sender**: Post owner  
**Section**: 'Exchange'  
**File**: `dashboard/post/acceptExchange.php` (lines 190-215)

**Notification Message**: `"{OwnerName} has accepted your booking request for {PostTitle}"`

**Side Effects** (if payment was credits):
- Credits transferred from requester to owner
- 'spent' notification sent to requester
- 'earned' notification sent to owner

#### C. Booking Request Rejected (Type: 'being_refused')
**When**: Post owner rejects the booking request  
**Recipient**: Requester  
**Sender**: Post owner  
**Section**: 'Exchange'  
**File**: `dashboard/post/rejectExchange.php` (lines 97-122)

**Notification Message**: `"{OwnerName} has rejected your booking request for {PostTitle}"`

#### D. Booking Cancelled
**When**: Requester cancels their booking request  
**Action**: DELETE the booking notification from UserNotifications  
**File**: `dashboard/post/cancelExchange.php` (lines 58-78)

---

### 2. Credit Transactions

#### A. Credits Spent (Type: 'spent')
**When**: Credits are deducted from user's account  
**Recipient**: User who spent credits  
**Sender**: NULL (system notification)  
**Section**: 'credits'  
**Files**: 
- `dashboard/post/acceptExchange.php` (lines 167-173) - When booking accepted
- `dashboard/events/eventsAPI/acceptEventAttendee.php` (lines 230-237) - When event accepted

**Notification Messages**:
- **Post Booking**: `"You spent {Amount} credits for {PostTitle} with {ProviderName}."`
- **Event Join**: `"You spent {Amount} credits for the event "{EventTitle}"."`

**Implementation**:
```php
$notifSpent = "INSERT INTO UserNotifications (UserId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection) 
              VALUES (?, 'spent', ?, ?, 'no', NOW(), 'credits')";
$nsStmt = $conn->prepare($notifSpent);
$nsTitle = "Credits Spent";
$nsMsg = "You spent $creditsCost credits for $postTitle with $ownerName.";
$nsStmt->bind_param('iss', $requesterId, $nsTitle, $nsMsg);
$nsStmt->execute();
```

#### B. Credits Earned (Type: 'earned')
**When**: Credits are added to user's account  
**Recipient**: User who earned credits  
**Sender**: NULL (system notification)  
**Section**: 'credits'  
**Files**:
- `dashboard/post/acceptExchange.php` (lines 175-184) - When booking accepted
- `dashboard/events/eventsAPI/acceptEventAttendee.php` (lines 248-255) - When event attendee accepted

**Notification Messages**:
- **Post Booking**: `"You earned {Amount} credits for {PostTitle} from {RequesterName}."`
- **Event Join**: `"You earned {Amount} credits from {AttendeeName} for the event "{EventTitle}"."`

**Implementation**:
```php
$notifEarned = "INSERT INTO UserNotifications (UserId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection) 
               VALUES (?, 'earned', ?, ?, 'no', NOW(), 'credits')";
$neStmt = $conn->prepare($notifEarned);
$neTitle = "Credits Earned";
$neMsg = "You earned $creditsCost credits for $postTitle from $requesterName.";
$neStmt->bind_param('iss', $ownerId, $neTitle, $neMsg);
$neStmt->execute();
```

---

### 3. Event Join Requests

#### A. Event Join Request Created (Type: 'booking')
**When**: User requests to join an event  
**Recipient**: Event organizer  
**Sender**: Requester  
**Section**: 'Exchange' (Note: Event join requests use 'booking' type in 'Exchange' section)  
**File**: `dashboard/events/eventsAPI/sendEventJoinNotification.php` (lines 127-150)

**Notification Message**: `"{Username} has requested to join your event {EventName} on {RequestDate}"`

**Implementation**:
```php
$message = "$senderUsername has requested to join your event $eventTitle on $requestDate";
$title = "Event Join Request";

$insertQuery = "
    INSERT INTO UserNotifications 
    (SenderId, RecipientId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection)
    VALUES (?, ?, 'booking', ?, ?, 'no', NOW(), 'Exchange')
";
```

#### B. Event Join Request Accepted (Type: 'acceptedInEvent')
**When**: Event organizer accepts a join request  
**Recipient**: Requester (who requested to join)  
**Sender**: Event organizer  
**Section**: 'events'  
**File**: `dashboard/events/eventsAPI/acceptEventAttendee.php` (lines 260-275)

**Notification Message**: `"{OrganizerName} has accepted your request to join "{EventTitle}"."`

**Side Effects** (if event has cost):
- Credits transferred from attendee to organizer
- EventsAttendees status updated to 'confirmed'
- CurrentAttendeesNumber incremented
- 'spent' notification sent to attendee
- 'earned' notification sent to organizer

**Implementation**:
```php
$notifTitle = "Event Request Accepted";
$notifMessage = "$organizerName has accepted your request to join \"$eventTitle\".";

$notifSql = "INSERT INTO UserNotifications 
            (UserId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection) 
            VALUES (?, ?, 'acceptedInEvent', ?, ?, 'no', NOW(), 'events')";
```

#### C. Event Join Request Rejected (Type: 'RejectedFromEvent')
**When**: Event organizer rejects a join request  
**Recipient**: Requester  
**Sender**: Event organizer  
**Section**: 'events'  
**File**: `dashboard/events/eventsAPI/rejectEventAttendee.php` (lines 150-165)

**Notification Message**: `"{OrganizerName} has declined your request to join "{EventTitle}"."`

**Implementation**:
```php
$notifTitle = "Event Request Declined";
$notifMessage = "$organizerName has declined your request to join \"$eventTitle\".";

$notifSql = "INSERT INTO UserNotifications 
            (UserId, SenderId, NotificationType, Title, Message, IsRead, CreatedAt, NotificationSection) 
            VALUES (?, ?, 'RejectedFromEvent', ?, ?, 'no', NOW(), 'events')";
```

---

## Transaction Flow Diagrams

### Skill Exchange Booking Flow
```
1. User A books User B's service (skill exchange)
   → Exchange record created with CreditsCost = 0
   → Notification to User B: "booking" type

2. User B accepts the exchange
   → Exchange status → 'accepted'
   → Notification to User A: "accepted" type
   → NO credit transfer

3. User B rejects the exchange
   → Exchange status → 'rejected'
   → Notification to User A: "being_refused" type
```

### Credit Booking Flow
```
1. User A books User B's service (credits: 50)
   → User A validation: CreditBalance >= 50
   → Exchange record created with CreditsCost = 50
   → Notification to User B: "booking" type

2. User B accepts the exchange
   → Exchange status → 'accepted'
   → Credits transfer:
      * User A: CreditBalance -= 50
      * User B: CreditBalance += 50
   → Notifications:
      * User A: "accepted" (Exchange) + "spent" (credits)
      * User B: "earned" (credits)
   → CreditTransactions records created for both users
```

### Event Join Flow (With Credits)
```
1. User A requests to join Event by User B (cost: 30 credits)
   → EventsAttendees record created (Status: 'registered')
   → Notification to User B: "booking" type (in 'Exchange' section)

2. User B accepts the request
   → User A validation: CreditBalance >= 30
   → Credits transfer:
      * User A: CreditBalance -= 30
      * User B: CreditBalance += 30
   → EventsAttendees Status → 'confirmed'
   → CurrentAttendeesNumber += 1
   → Notifications:
      * User A: "acceptedInEvent" (events) + "spent" (credits)
      * User B: "earned" (credits)
   → CreditTransactions records created for both users

3. User B rejects the request
   → EventsAttendees Status → 'cancelled'
   → Notification to User A: "RejectedFromEvent" (events)
   → NO credit transfer
```

---

## Key Files & Responsibilities

### Booking Notifications
| File | Responsibility |
|------|---------------|
| `dashboard/post/postdetails.php` | Creates booking request + notification |
| `dashboard/post/acceptExchange.php` | Accepts booking, transfers credits, sends acceptance + credit notifications |
| `dashboard/post/rejectExchange.php` | Rejects booking, sends rejection notification |
| `dashboard/post/cancelExchange.php` | Deletes booking + associated notification |

### Event Notifications
| File | Responsibility |
|------|---------------|
| `dashboard/events/eventsAPI/sendEventJoinNotification.php` | Creates event join request + notification |
| `dashboard/events/eventsAPI/acceptEventAttendee.php` | Accepts join request, transfers credits, sends acceptance + credit notifications |
| `dashboard/events/eventsAPI/rejectEventAttendee.php` | Rejects join request, sends rejection notification |

### Credit Management
| File | Responsibility |
|------|---------------|
| `dashboard/notification/getCredits.php` | Fetches user's current credit balance |
| `add_test_credits.php` | Dev tool: adds credits to user account |

---

## Important Notes

1. **Exchanges Table**: Tracks ONLY pending and accepted bookings for posts/services
2. **UserNotifications Table**: Tracks ALL events including:
   - Bookings (pending, accepted, rejected)
   - Credit transactions (earned, spent)
   - Event activities (accepted, rejected)
   - Reviews (likes, comments, ratings)

3. **Credit Transfers**:
   - Always done in transactions with rollback on failure
   - Both CreditTransactions AND UserNotifications records created
   - Validation ensures sufficient balance before transfer

4. **Notification Sections Must Match Types**:
   - Enforced by database trigger `trg_validate_notification_section`
   - 'spent'/'earned' → 'credits' section
   - 'acceptedInEvent'/'RejectedFromEvent' → 'events' section
   - 'booking'/'accepted'/'being_refused' → 'Exchange' section

5. **Event Join Requests**:
   - Use 'booking' type but stored in 'Exchange' section
   - After acceptance, status updates in EventsAttendees table
   - Event acceptance/rejection use 'events' section

---

## Testing Checklist

### Service Bookings
- [ ] Skill exchange booking creates 'booking' notification to provider
- [ ] Credit booking creates 'booking' notification to provider
- [ ] Credit booking validates requester has sufficient balance
- [ ] Acceptance with skill exchange sends 'accepted' notification only
- [ ] Acceptance with credits sends 'accepted', 'spent', and 'earned' notifications
- [ ] Acceptance with credits transfers correct amount
- [ ] Rejection sends 'being_refused' notification
- [ ] Cancellation deletes the 'booking' notification

### Events
- [ ] Event join request creates 'booking' notification to organizer
- [ ] Acceptance with free event sends 'acceptedInEvent' notification only
- [ ] Acceptance with paid event validates attendee has sufficient balance
- [ ] Acceptance with paid event sends 'acceptedInEvent', 'spent', and 'earned' notifications
- [ ] Acceptance with paid event transfers correct amount
- [ ] Acceptance increments CurrentAttendeesNumber
- [ ] Rejection sends 'RejectedFromEvent' notification
- [ ] Cannot reject already confirmed attendees

### Credit Transactions
- [ ] Credit transfer creates CreditTransaction records for both parties
- [ ] Credit transfer updates CreditBalance correctly
- [ ] Credit notifications show accurate amounts and descriptions
- [ ] Insufficient balance prevents acceptance

---

## API Endpoints Summary

### Post/Service Booking APIs
- `POST /dashboard/post/postdetails.php` - Create booking request
- `POST /dashboard/post/acceptExchange.php` - Accept booking (exchangeId)
- `POST /dashboard/post/rejectExchange.php` - Reject booking (exchangeId)
- `POST /dashboard/post/cancelExchange.php` - Cancel booking (exchangeId)

### Event APIs
- `POST /dashboard/events/eventsAPI/sendEventJoinNotification.php` - Request to join event (eventId)
- `POST /dashboard/events/eventsAPI/acceptEventAttendee.php` - Accept join request (eventId, attendeeId)
- `POST /dashboard/events/eventsAPI/rejectEventAttendee.php` - Reject join request (eventId, attendeeId)

### Notification APIs
- `GET /api/notifications/fetch.php` - Fetch user's notifications
- `POST /api/notifications/mark_read.php` - Mark notification as read
- `DELETE /api/notifications/delete.php` - Delete notification
- `GET /dashboard/notification/getCredits.php` - Get current credit balance

---

## Future Enhancements

1. **Real-time Notifications**: Implement WebSocket/Server-Sent Events for instant updates
2. **Email Notifications**: Send email for important events (booking accepted, credits earned)
3. **Push Notifications**: Mobile app push notifications
4. **Notification Preferences**: Allow users to customize which notifications they receive
5. **Notification Batching**: Group similar notifications (e.g., "3 new booking requests")
6. **Read Receipts**: Track when notifications are viewed
7. **Notification History**: Archive for older notifications beyond certain date
8. **Reminder Notifications**: Upcoming event reminders, pending booking expiration

---

*Last Updated: February 5, 2026*
