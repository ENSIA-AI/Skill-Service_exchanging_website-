-- ======================================================
-- Migration Script: Refine Add Post Form
-- Description: Adds SkillType to PostSkills and updates ENUMs in Posts table.
-- Author: Antigravity (AI Assistant)
-- Date: 2026-02-03
-- ======================================================

-- 1. Update PostSkills table to distinguish between offered and requested skills
ALTER TABLE PostSkills 
ADD COLUMN SkillType ENUM('offered', 'requested') NOT NULL DEFAULT 'offered';

-- 2. Update Posts table to allow 'both' as a PostType (Online & In-person)
ALTER TABLE Posts 
MODIFY COLUMN PostType ENUM('in-person', 'online', 'both') NOT NULL;

-- 3. Update Posts table to allow 'both' as a PaymentMethod (Credits & Exchange)
ALTER TABLE Posts 
MODIFY COLUMN PaymentMethod ENUM('exchange', 'credit', 'both') NOT NULL;

-- 4. Ensure PostAvailableDates exists (it was in the schema but vital for this feature)
-- CREATE TABLE IF NOT EXISTS PostAvailableDates (
--   PostAvailableDateId INT AUTO_INCREMENT PRIMARY KEY,
--   PostId INT NOT NULL,
--   AvailableDate DATETIME NOT NULL,
--   FOREIGN KEY (PostId) REFERENCES Posts(PostId) ON DELETE CASCADE,
--   INDEX idx_post_available_dates (PostId, AvailableDate)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: All PHP files (process_addpost.php, postdetails.php, etc.) have been updated
-- to utilize these schema changes.
