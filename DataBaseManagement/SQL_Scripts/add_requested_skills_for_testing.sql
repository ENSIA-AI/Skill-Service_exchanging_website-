-- ============================================
-- ADD REQUESTED SKILLS TO ALL POSTS FOR TESTING
-- Date: 2026-02-05
-- Purpose: Add sample requested/seeking skills to all posts for testing display
-- Note: Uses INSERT IGNORE to skip duplicates
-- ============================================

-- First, let's see what skills are available
-- SELECT SkillId, SkillName FROM Skills ORDER BY SkillId;

-- Add requested skills to posts 1-10 (these already have offered skills)
INSERT IGNORE INTO PostSkills (PostId, SkillId, SkillType) VALUES
-- Post 1: Private JavaScript Lessons - seeking design skills
(1, 13, 'requested'),  -- Figma
(1, 15, 'requested'),  -- Adobe Illustrator

-- Post 2: Custom Graphic Design - seeking programming skills
(2, 1, 'requested'),   -- JavaScript
(2, 3, 'requested'),   -- Python

-- Post 3: Professional Photo Session - seeking writing skills
(3, 37, 'requested'),  -- Blogging
(3, 40, 'requested'),  -- SEO Writing

-- Post 4: Blog Content Writing - seeking photography skills
(4, 25, 'requested'),  -- Lightroom
(4, 26, 'requested'),  -- Video Editing

-- Post 5: Guitar Lessons - seeking cooking skills
(5, 58, 'requested'),  -- Asian Cuisine (changed from 57)
(5, 61, 'requested'),  -- Halal Cooking

-- Post 6: Algerian Cooking - seeking fitness skills
(6, 71, 'requested'),  -- Weight Training
(6, 73, 'requested'),  -- Stretching & Flexibility

-- Post 7: Personal Fitness Coaching - seeking language skills
(7, 85, 'requested'),  -- Italian
(7, 86, 'requested'),  -- Mandarin

-- Post 8: Intensive English Tutoring - seeking gardening skills
(8, 110, 'requested'), -- Composting
(8, 111, 'requested'), -- Bonsai

-- Post 9: Auto Mechanics Diagnostics - seeking music skills
(9, 49, 'requested'),  -- Singing
(9, 51, 'requested'),  -- Music Theory

-- Post 10: Organic Gardening - seeking tech skills
(10, 93, 'requested'), -- Automotive Electrical
(10, 94, 'requested'); -- Air Conditioning

-- Add requested skills to posts 11-20 (these might not have any skills yet)
INSERT IGNORE INTO PostSkills (PostId, SkillId, SkillType) VALUES
-- Post 11: seeking design skills
(11, 13, 'requested'), -- Figma
(11, 20, 'requested'), -- Brand Design

-- Post 12: seeking programming skills
(12, 2, 'requested'),  -- React
(12, 4, 'requested'),  -- Node.js

-- Post 13: seeking language skills
(13, 85, 'requested'), -- Italian
(13, 86, 'requested'), -- Mandarin

-- Post 14: seeking music skills
(14, 49, 'requested'), -- Singing
(14, 51, 'requested'), -- Music Theory

-- Post 15: seeking cooking skills
(15, 58, 'requested'), -- Asian Cuisine
(15, 63, 'requested'), -- Cooking Techniques

-- Post 16: seeking gardening skills
(16, 110, 'requested'), -- Composting
(16, 111, 'requested'), -- Bonsai

-- Post 17: seeking programming skills
(17, 1, 'requested'),  -- JavaScript
(17, 7, 'requested'),  -- HTML & CSS

-- Post 18: seeking photography skills
(18, 25, 'requested'), -- Lightroom
(18, 28, 'requested'), -- Drone Photography

-- Post 19: seeking writing skills
(19, 37, 'requested'), -- Blogging
(19, 38, 'requested'), -- Journalism

-- Post 20: seeking fitness skills
(20, 71, 'requested'), -- Weight Training
(20, 73, 'requested'); -- Stretching & Flexibility

-- Add to posts 21-24 if they exist (ignore errors if they don't)
INSERT IGNORE INTO PostSkills (PostId, SkillId, SkillType) VALUES
(21, 13, 'requested'), -- Figma
(21, 15, 'requested'), -- Adobe Illustrator
(22, 1, 'requested'),  -- JavaScript
(22, 3, 'requested'),  -- Python
(23, 49, 'requested'), -- Singing
(23, 51, 'requested'), -- Music Theory
(24, 72, 'requested'), -- Cardio (changed to avoid duplicate)
(24, 74, 'requested'); -- Yoga

-- Verify the inserts - show all posts with their requested skills
SELECT 
    ps.PostId, 
    p.Title as PostTitle, 
    GROUP_CONCAT(s.SkillName SEPARATOR ', ') as RequestedSkills
FROM PostSkills ps 
JOIN Posts p ON ps.PostId = p.PostId 
JOIN Skills s ON ps.SkillId = s.SkillId 
WHERE ps.SkillType = 'requested'
GROUP BY ps.PostId, p.Title
ORDER BY ps.PostId;
