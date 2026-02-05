-- ============================================
-- REMOVE MEETINGLINK FROM POSTS TABLE
-- Date: 2026-02-05
-- Purpose: Remove the MeetingLink column from Posts table
-- ============================================

-- Drop the MeetingLink column from Posts table
ALTER TABLE Posts DROP COLUMN MeetingLink;

-- Verify the column was removed
DESCRIBE Posts;
