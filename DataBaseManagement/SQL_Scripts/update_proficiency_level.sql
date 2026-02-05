-- ============================================
-- SQL Script: Update Proficiency Level Column
-- Description: Changes proficiency_level from ENUM to INT (0-100)
-- Author: System
-- Date: 2026-02-05
-- ============================================

-- This script will:
-- 1. Add a temporary column for the new integer proficiency level
-- 2. Convert existing enum values to integer percentages
-- 3. Drop the old enum column
-- 4. Rename the new column to ProficiencyLevel

-- ============================================
-- Step 1: Add temporary INT column for proficiency
-- ============================================
ALTER TABLE userSkills 
ADD COLUMN ProficiencyLevelInt INT DEFAULT 0 
CHECK (ProficiencyLevelInt >= 0 AND ProficiencyLevelInt <= 100);

-- ============================================
-- Step 2: Convert existing ENUM values to INT (0-100)
-- Mapping:
--   beginner     -> 25
--   intermediate -> 50
--   advanced     -> 75
--   expert       -> 100
-- ============================================
UPDATE UserSkills 
SET ProficiencyLevelInt = CASE 
    WHEN ProficiencyLevel = 'beginner' THEN 25
    WHEN ProficiencyLevel = 'intermediate' THEN 50
    WHEN ProficiencyLevel = 'advanced' THEN 75
    WHEN ProficiencyLevel = 'expert' THEN 100
    ELSE 0
END;

-- ============================================
-- Step 3: Drop the old ENUM column
-- ============================================
ALTER TABLE UserSkills 
DROP COLUMN ProficiencyLevel;

-- ============================================
-- Step 4: Rename the new INT column to ProficiencyLevel
-- ============================================
ALTER TABLE UserSkills 
CHANGE COLUMN ProficiencyLevelInt ProficiencyLevel INT NOT NULL DEFAULT 0
CHECK (ProficiencyLevel >= 0 AND ProficiencyLevel <= 100);

-- ============================================
-- Verification Query (Optional - Run to verify changes)
-- ============================================
-- SELECT UserSkillId, UserId, SkillId, SkillType, ProficiencyLevel 
-- FROM UserSkills 
-- ORDER BY UserSkillId;

-- ============================================
-- Rollback Script (In case you need to revert)
-- ============================================
-- To rollback, you would need to:
-- 1. Add the ENUM column back
-- ALTER TABLE UserSkills 
-- ADD COLUMN ProficiencyLevelEnum ENUM('beginner','intermediate','advanced','expert') NOT NULL DEFAULT 'beginner';
--
-- 2. Convert INT values back to ENUM
-- UPDATE UserSkills 
-- SET ProficiencyLevelEnum = CASE 
--     WHEN ProficiencyLevel <= 25 THEN 'beginner'
--     WHEN ProficiencyLevel <= 50 THEN 'intermediate'
--     WHEN ProficiencyLevel <= 75 THEN 'advanced'
--     ELSE 'expert'
-- END;
--
-- 3. Drop INT column and rename ENUM column
-- ALTER TABLE UserSkills DROP COLUMN ProficiencyLevel;
-- ALTER TABLE UserSkills CHANGE COLUMN ProficiencyLevelEnum ProficiencyLevel 
--     ENUM('beginner','intermediate','advanced','expert') NOT NULL;
