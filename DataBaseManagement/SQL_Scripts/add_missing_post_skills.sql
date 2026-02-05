-- ============================================
-- ADD MISSING SKILLS TO POSTS
-- Date: 2026-02-05
-- Purpose: Add skills to posts that don't have any PostSkills entries
-- ============================================

-- Post 11: Private JavaScript Lessons (CategoryId likely relates to Programming)
INSERT INTO PostSkills (PostId, SkillId, SkillType) VALUES
(11, 1, 'offered'),   -- JavaScript
(11, 2, 'offered'),   -- React
(11, 7, 'offered');   -- HTML & CSS

-- Post 12: Custom Graphic Design
INSERT INTO PostSkills (PostId, SkillId, SkillType) VALUES
(12, 13, 'offered'),  -- Figma
(12, 15, 'offered'),  -- Adobe Illustrator
(12, 20, 'offered');  -- Brand Design

-- Post 13: Professional Photo Session
INSERT INTO PostSkills (PostId, SkillId, SkillType) VALUES
(13, 25, 'offered'),  -- Lightroom
(13, 26, 'offered'),  -- Video Editing
(13, 28, 'offered');  -- Drone Photography

-- Post 14: Blog Content Writing
INSERT INTO PostSkills (PostId, SkillId, SkillType) VALUES
(14, 37, 'offered'),  -- Blogging
(14, 38, 'offered'),  -- Journalism
(14, 40, 'offered');  -- SEO Writing

-- Post 15: Guitar Lessons All Levels
INSERT INTO PostSkills (PostId, SkillId, SkillType) VALUES
(15, 49, 'offered'),  -- Singing
(15, 51, 'offered');  -- Music Theory

-- Post 16: Algerian Cooking Coaching
INSERT INTO PostSkills (PostId, SkillId, SkillType) VALUES
(16, 61, 'offered'),  -- Halal Cooking
(16, 62, 'offered'),  -- Special Diet Cooking
(16, 63, 'offered');  -- Cooking Techniques

-- Post 17: Personal Fitness Coaching
INSERT INTO PostSkills (PostId, SkillId, SkillType) VALUES
(17, 71, 'offered'),  -- Weight Training
(17, 73, 'offered');  -- Stretching & Flexibility

-- Post 18: Intensive English Tutoring
INSERT INTO PostSkills (PostId, SkillId, SkillType) VALUES
(18, 85, 'offered'),  -- Italian (as a language category example)
(18, 86, 'offered');  -- Mandarin

-- Post 19: Auto Mechanics Diagnostics
INSERT INTO PostSkills (PostId, SkillId, SkillType) VALUES
(19, 93, 'offered'),  -- Automotive Electrical
(19, 94, 'offered');  -- Air Conditioning

-- Post 20: Organic Gardening Consultation
INSERT INTO PostSkills (PostId, SkillId, SkillType) VALUES
(20, 110, 'offered'), -- Composting
(20, 111, 'offered'); -- Bonsai

-- Verify the inserts
SELECT ps.PostId, p.Title, s.SkillName, ps.SkillType 
FROM PostSkills ps 
JOIN Posts p ON ps.PostId = p.PostId 
JOIN Skills s ON ps.SkillId = s.SkillId 
WHERE ps.PostId BETWEEN 11 AND 20
ORDER BY ps.PostId, ps.SkillType;
