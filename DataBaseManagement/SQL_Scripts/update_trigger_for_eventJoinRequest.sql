-- Update the trigger to include eventJoinRequest in events section
-- Run this in phpMyAdmin or MySQL directly

DROP TRIGGER IF EXISTS trg_auto_correct_notification_section;

DELIMITER $$

CREATE TRIGGER trg_auto_correct_notification_section
BEFORE INSERT ON UserNotifications
FOR EACH ROW
BEGIN
    -- Auto-correct NotificationSection based on NotificationType
    IF NEW.NotificationType IN ('like', 'comment', 'rating') THEN
        SET NEW.NotificationSection = 'Reviews';
    ELSEIF NEW.NotificationType IN ('booking', 'accepted', 'completing', 'being_refused') THEN
        SET NEW.NotificationSection = 'Exchange';
    ELSEIF NEW.NotificationType IN ('acceptedInEvent', 'RejectedFromEvent', 'eventJoinRequest') THEN
        SET NEW.NotificationSection = 'events';
    ELSEIF NEW.NotificationType IN ('earned', 'spent') THEN
        SET NEW.NotificationSection = 'credits';
    END IF;
END$$

DELIMITER ;
