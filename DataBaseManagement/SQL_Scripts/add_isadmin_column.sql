-- Script to add IsAdmin column to Users table
-- This column indicates whether a user has admin privileges

USE skill_service_exchange_db;

-- Add IsAdmin column with ENUM type (yes/no), default to 'no'
ALTER TABLE Users 
ADD COLUMN IsAdmin ENUM('yes', 'no') NOT NULL DEFAULT 'no';

-- Verify column was added
DESCRIBE Users;

SELECT 'IsAdmin column added successfully' AS Status;
