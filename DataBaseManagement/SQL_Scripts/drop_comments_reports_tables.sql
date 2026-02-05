-- Script to drop UserComments and Reports tables
-- Run this script if these tables are no longer needed

USE skill_service_exchange_db;

-- Drop UserComments table if it exists
DROP TABLE IF EXISTS UserComments;

-- Drop Reports table if it exists
DROP TABLE IF EXISTS Reports;

-- Verify tables are dropped
SELECT 'Tables dropped successfully' AS Status;
