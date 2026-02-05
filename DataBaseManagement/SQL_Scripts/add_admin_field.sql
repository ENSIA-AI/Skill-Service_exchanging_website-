-- ============================================
-- Add IsAdmin field to Users table
-- ============================================

-- Add IsAdmin column if it doesn't exist
ALTER TABLE Users ADD COLUMN IsAdmin ENUM('yes','no') DEFAULT 'no' AFTER IsBanned;

-- Set first user as admin for initial setup
UPDATE Users SET IsAdmin = 'yes' WHERE UserId = 1;

-- Index for faster admin lookups
CREATE INDEX idx_users_is_admin ON Users(IsAdmin);
