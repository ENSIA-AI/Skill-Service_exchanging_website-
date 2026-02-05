-- ============================================
-- FIX NOTIFICATION TRIGGER
-- Date: 2026-02-05
-- Purpose: Replace restrictive validation triggers with auto-correcting triggers
-- 
-- PROBLEM: The old triggers rejected INSERT statements when NotificationType
-- didn't match NotificationSection, causing PHP exceptions and failed inserts.
--
-- SOLUTION: New triggers auto-correct the NotificationSection based on
-- NotificationType, ensuring inserts ALWAYS succeed.
-- ============================================

-- Step 1: Drop ALL existing triggers (old and new)
DROP TRIGGER IF EXISTS trg_validate_notification_section;
DROP TRIGGER IF EXISTS trg_validate_notification_section_update;
DROP TRIGGER IF EXISTS trg_auto_correct_notification_section;
DROP TRIGGER IF EXISTS trg_auto_correct_notification_section_update;

-- Step 2: Create auto-correcting INSERT trigger
DELIMITER $$

CREATE TRIGGER trg_auto_correct_notification_section
BEFORE INSERT ON UserNotifications
FOR EACH ROW
BEGIN
    -- Auto-correct NotificationSection based on NotificationType
    -- Reviews section: like, comment, rating
    IF NEW.NotificationType IN ('like', 'comment', 'rating') THEN
        SET NEW.NotificationSection = 'Reviews';
    -- Exchange section: booking, accepted, completing, being_refused
    ELSEIF NEW.NotificationType IN ('booking', 'accepted', 'completing', 'being_refused') THEN
        SET NEW.NotificationSection = 'Exchange';
    -- Events section: acceptedInEvent, RejectedFromEvent
    ELSEIF NEW.NotificationType IN ('acceptedInEvent', 'RejectedFromEvent') THEN
        SET NEW.NotificationSection = 'events';
    -- Credits section: earned, spent
    ELSEIF NEW.NotificationType IN ('earned', 'spent') THEN
        SET NEW.NotificationSection = 'credits';
    END IF;
    -- NEVER REJECT - just auto-correct to the proper section
END$$

-- Step 3: Create auto-correcting UPDATE trigger
CREATE TRIGGER trg_auto_correct_notification_section_update
BEFORE UPDATE ON UserNotifications
FOR EACH ROW
BEGIN
    -- Auto-correct NotificationSection based on NotificationType
    IF NEW.NotificationType IN ('like', 'comment', 'rating') THEN
        SET NEW.NotificationSection = 'Reviews';
    ELSEIF NEW.NotificationType IN ('booking', 'accepted', 'completing', 'being_refused') THEN
        SET NEW.NotificationSection = 'Exchange';
    ELSEIF NEW.NotificationType IN ('acceptedInEvent', 'RejectedFromEvent') THEN
        SET NEW.NotificationSection = 'events';
    ELSEIF NEW.NotificationType IN ('earned', 'spent') THEN
        SET NEW.NotificationSection = 'credits';
    END IF;
    -- NEVER REJECT - just auto-correct to the proper section
END$$

DELIMITER ;

-- Step 4: Verify the new triggers exist
SELECT 'Triggers after fix:' AS Status;
SHOW TRIGGERS WHERE `Table` = 'usernotifications';

-- ============================================
-- MAPPING REFERENCE:
-- ============================================
-- NotificationType          -> NotificationSection
-- -------------------------------------------
-- like, comment, rating     -> Reviews
-- booking, accepted,        -> Exchange
-- completing, being_refused
-- acceptedInEvent,          -> events
-- RejectedFromEvent
-- earned, spent             -> credits
-- ============================================
