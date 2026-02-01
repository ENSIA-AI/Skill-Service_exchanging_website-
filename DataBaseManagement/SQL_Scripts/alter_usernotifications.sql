-- ============================================
-- Migration Script: Add sender_id and rename UserId to RecipientId
-- in UserNotifications table
-- ============================================

-- Step 1: Delete all existing notifications (clean slate)
DELETE FROM UserNotifications;

-- Step 2: Add the new SenderId column
ALTER TABLE UserNotifications 
ADD COLUMN SenderId INT NULL AFTER NotificationId;

-- Step 3: Rename UserId column to RecipientId
ALTER TABLE UserNotifications 
CHANGE COLUMN UserId RecipientId INT NOT NULL;

-- Step 4: Add foreign key constraint for SenderId
ALTER TABLE UserNotifications
ADD CONSTRAINT fk_notification_sender 
FOREIGN KEY (SenderId) REFERENCES Users(UserId) ON DELETE SET NULL;

-- Step 5: Add index for SenderId for better query performance
ALTER TABLE UserNotifications
ADD INDEX idx_notifications_sender (SenderId);

-- ============================================
-- Step 6: Insert sample notifications with SenderId and RecipientId
-- Following the trigger validation rules:
-- - 'Reviews' section: 'like', 'comment', 'rating'
-- - 'Exchange' section: 'booking', 'accepted', 'completing', 'being_refused'
-- - 'events' section: 'acceptedInEvent', 'RejectedFromEvent'
-- - 'credits' section: 'earned', 'spent'
-- ============================================

-- Event acceptance notifications (events section)
INSERT INTO UserNotifications (SenderId, RecipientId, NotificationType, Title, Message, IsRead, NotificationSection) VALUES
(1, 2, 'acceptedInEvent', 'Event Request Accepted', 'JohnDoe has accepted your request to join Web Development Workshop', 'no', 'events'),
(3, 4, 'acceptedInEvent', 'Event Request Accepted', 'MikeSmith has accepted your request to join UI/UX Design Session', 'yes', 'events'),
(2, 1, 'acceptedInEvent', 'Event Request Accepted', 'JaneDoe has accepted your request to join Python Basics', 'no', 'events');

-- Event rejection notifications (events section)
INSERT INTO UserNotifications (SenderId, RecipientId, NotificationType, Title, Message, IsRead, NotificationSection) VALUES
(2, 5, 'RejectedFromEvent', 'Event Request Declined', 'JaneDoe has declined your request to join Python Basics', 'no', 'events'),
(1, 3, 'RejectedFromEvent', 'Event Request Declined', 'JohnDoe has declined your request to join Web Development Workshop', 'yes', 'events');

-- Exchange/booking notifications (Exchange section - includes event join requests)
INSERT INTO UserNotifications (SenderId, RecipientId, NotificationType, Title, Message, IsRead, NotificationSection) VALUES
(2, 1, 'booking', 'Event Join Request', 'JaneDoe has requested to join your event Web Development Workshop on January 28, 2026', 'no', 'Exchange'),
(3, 1, 'booking', 'Event Join Request', 'MikeSmith has requested to join your event Web Development Workshop on January 27, 2026', 'no', 'Exchange'),
(4, 2, 'booking', 'Event Join Request', 'SarahLee has requested to join your event Python Basics on January 26, 2026', 'no', 'Exchange'),
(1, 3, 'booking', 'Event Join Request', 'JohnDoe has requested to join your event Data Science Meetup on January 25, 2026', 'yes', 'Exchange'),
(3, 1, 'booking', 'New Exchange Request', 'MikeSmith wants to exchange skills with you for JavaScript Fundamentals', 'no', 'Exchange'),
(4, 2, 'accepted', 'Exchange Accepted', 'SarahLee has accepted your skill exchange request', 'yes', 'Exchange'),
(1, 4, 'completing', 'Exchange Completed', 'JohnDoe has marked the exchange as completed', 'no', 'Exchange'),
(2, 3, 'being_refused', 'Exchange Declined', 'JaneDoe has declined your skill exchange request', 'no', 'Exchange');

-- Review/rating notifications (Reviews section)
INSERT INTO UserNotifications (SenderId, RecipientId, NotificationType, Title, Message, IsRead, NotificationSection) VALUES
(2, 1, 'rating', 'New Rating Received', 'JaneDoe gave you a 5-star rating!', 'no', 'Reviews'),
(3, 2, 'rating', 'New Rating Received', 'MikeSmith gave you a 4-star rating', 'yes', 'Reviews'),
(1, 3, 'comment', 'New Comment', 'JohnDoe commented on your post: Great tutorial!', 'no', 'Reviews'),
(4, 1, 'like', 'New Like', 'SarahLee liked your post', 'yes', 'Reviews');

-- Credit notifications (credits section - system notifications, SenderId can be NULL)
INSERT INTO UserNotifications (SenderId, RecipientId, NotificationType, Title, Message, IsRead, NotificationSection) VALUES
(NULL, 1, 'earned', 'Credits Earned', 'You earned 50 credits for completing an exchange', 'no', 'credits'),
(NULL, 2, 'spent', 'Credits Spent', 'You spent 30 credits on Web Development Workshop', 'yes', 'credits'),
(NULL, 3, 'earned', 'Credits Earned', 'You earned 25 credits for receiving a 5-star rating', 'no', 'credits');

-- ============================================
-- Note: After running this migration, the UserNotifications table will have:
-- - RecipientId: The user who receives the notification
-- - SenderId: The user who triggered/sent the notification (can be NULL for system notifications)
-- ============================================
