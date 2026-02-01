-- Insert 20 Test Events into Events Table
-- Copy and paste into phpMyAdmin SQL tab
-- IMPORTANT: This script TRUNCATES tables completely and resets auto-increment!

-- STEP 1: Disable foreign key checks
SET FOREIGN_KEY_CHECKS=0;

-- STEP 2: Truncate all related tables (clears all data and resets auto-increment)
TRUNCATE TABLE EventsAttendees;
TRUNCATE TABLE EventSkills;
TRUNCATE TABLE Events;

-- STEP 3: Insert 20 new test events (will auto-generate EventIds 1-20)
INSERT INTO Events (OrganizerId, EventTitle, EventDescription, EventLocation, EventType, EventStartDate, EventEndDate, MaxAttendees, CurrentAttendeesNumber, EventCost, EventStatus, CreatedAt) VALUES

(1, 'Web Development Workshop', 'Join us for an intensive hands-on workshop where we''ll dive deep into modern web development practices. This workshop is designed for both beginners and intermediate developers who want to enhance their skills in HTML, CSS, and JavaScript.', 'Community Center, Room 304', 'in-person', DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY) + INTERVAL 6 HOUR, 15, 8, 50, 'upcoming', NOW()),

(2, 'Advanced React.js Bootcamp', 'Deep dive into React.js with advanced concepts including hooks, context API, Redux, and performance optimization. Perfect for developers who already know JavaScript basics.', 'Tech Hub Downtown', 'in-person', DATE_ADD(NOW(), INTERVAL 14 DAY), DATE_ADD(NOW(), INTERVAL 16 DAY), 20, 15, 75, 'upcoming', NOW()),

(3, 'Node.js Backend Development', 'Learn to build scalable backend applications using Node.js and Express.js. We''ll cover REST APIs, database integration, authentication, and deployment.', 'Online', 'online', DATE_ADD(NOW(), INTERVAL 10 DAY), DATE_ADD(NOW(), INTERVAL 12 DAY), 25, 18, 60, 'upcoming', NOW()),

(3, 'Photography Basics Meetup', 'Learn the fundamentals of photography in this practical meetup. We''ll explore camera settings, composition techniques, and lighting principles. Bring your camera or smartphone.', 'City Park', 'in-person', DATE_ADD(NOW(), INTERVAL 5 DAY), DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 3 HOUR, 20, 12, 25, 'upcoming', NOW()),

(4, 'Advanced Photography Workshop', 'Master advanced photography techniques including macro, landscape, and portrait photography. Learn post-processing and portfolio building.', 'Nature Reserve', 'in-person', DATE_ADD(NOW(), INTERVAL 21 DAY), DATE_ADD(NOW(), INTERVAL 21 DAY) + INTERVAL 5 HOUR, 12, 8, 55, 'upcoming', NOW()),

(5, 'Design Thinking Session', 'This workshop explores the design thinking methodology for solving complex problems creatively. Perfect for UX/UI designers and anyone interested in user-centered design.', 'Online', 'online', DATE_ADD(NOW(), INTERVAL 9 DAY), DATE_ADD(NOW(), INTERVAL 9 DAY) + INTERVAL 3 HOUR, 15, 15, 30, 'upcoming', NOW()),

(6, 'UI/UX Design Masterclass', 'Learn professional UI/UX design principles, tools like Figma, prototyping, and user research methods. Create a complete design system from scratch.', 'Design Studio', 'in-person', DATE_ADD(NOW(), INTERVAL 15 DAY), DATE_ADD(NOW(), INTERVAL 17 DAY), 18, 10, 80, 'upcoming', NOW()),

(7, 'Business & Marketing Workshop', 'Learn modern marketing strategies and social media best practices. This workshop covers content creation, audience engagement, and analytics.', 'Business Hub', 'in-person', DATE_ADD(NOW(), INTERVAL 8 DAY), DATE_ADD(NOW(), INTERVAL 8 DAY) + INTERVAL 4 HOUR, 12, 6, 40, 'upcoming', NOW()),

(8, 'Digital Marketing & SEO', 'Master SEO, SEM, content marketing, and analytics. Learn how to drive organic traffic and maximize ROI for your digital campaigns.', 'Online', 'online', DATE_ADD(NOW(), INTERVAL 12 DAY), DATE_ADD(NOW(), INTERVAL 14 DAY), 30, 22, 45, 'upcoming', NOW()),

(9, 'Entrepreneurship Bootcamp', 'From idea to launch: Learn business planning, funding, pitching, and growth strategies. Perfect for aspiring entrepreneurs and startup founders.', 'Innovation Hub', 'in-person', DATE_ADD(NOW(), INTERVAL 20 DAY), DATE_ADD(NOW(), INTERVAL 22 DAY), 25, 18, 65, 'upcoming', NOW()),

(10, 'Language Exchange Meetup', 'A casual meetup for language learners and teachers to practice different languages and exchange cultural knowledge in a relaxed environment.', 'Coffee Shop Downtown', 'in-person', DATE_ADD(NOW(), INTERVAL 6 DAY), DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 2 HOUR, 20, 10, 0, 'upcoming', NOW()),

(1, 'Spanish Conversation Workshop', 'Improve your Spanish speaking skills through interactive conversations, games, and cultural activities. All levels welcome.', 'Online', 'online', DATE_ADD(NOW(), INTERVAL 11 DAY), DATE_ADD(NOW(), INTERVAL 11 DAY) + INTERVAL 1.5 HOUR, 15, 9, 20, 'upcoming', NOW()),

(2, 'French Language Intensive', 'Comprehensive French course covering grammar, vocabulary, listening, and speaking skills. Perfect for beginners to intermediate learners.', 'Language Center', 'in-person', DATE_ADD(NOW(), INTERVAL 13 DAY), DATE_ADD(NOW(), INTERVAL 18 DAY), 12, 8, 90, 'upcoming', NOW()),

(3, 'Python for Data Science', 'Learn Python programming with a focus on data analysis, pandas, NumPy, matplotlib, and machine learning fundamentals.', 'Tech Hub', 'in-person', DATE_ADD(NOW(), INTERVAL 16 DAY), DATE_ADD(NOW(), INTERVAL 18 DAY), 20, 15, 70, 'upcoming', NOW()),

(4, 'Machine Learning 101', 'Introduction to machine learning concepts, algorithms, and practical applications. We''ll use scikit-learn and TensorFlow.', 'Online', 'online', DATE_ADD(NOW(), INTERVAL 19 DAY), DATE_ADD(NOW(), INTERVAL 21 DAY), 25, 20, 75, 'upcoming', NOW()),

(5, 'Graphic Design Fundamentals', 'Learn graphic design principles, color theory, typography, and design software. Create professional-looking designs from day one.', 'Design Studio', 'in-person', DATE_ADD(NOW(), INTERVAL 10 DAY), DATE_ADD(NOW(), INTERVAL 10 DAY) + INTERVAL 4 HOUR, 15, 9, 35, 'upcoming', NOW()),

(6, 'Digital Illustration Workshop', 'Learn digital illustration techniques using Procreate, Photoshop, or Clip Studio Paint. Perfect for artists wanting to go digital.', 'Online', 'online', DATE_ADD(NOW(), INTERVAL 17 DAY), DATE_ADD(NOW(), INTERVAL 17 DAY) + INTERVAL 3 HOUR, 12, 7, 40, 'upcoming', NOW()),

(7, 'Skill Exchange Fair 2025', 'A large community event celebrating skill exchange. Meet various instructors and learners, explore different skill categories, and connect with people.', 'Main Square', 'in-person', DATE_ADD(NOW(), INTERVAL 22 DAY), DATE_ADD(NOW(), INTERVAL 22 DAY) + INTERVAL 8 HOUR, 50, 34, 0, 'upcoming', NOW()),

(8, 'Advanced Mathematics Workshop', 'Explore advanced mathematics topics including calculus, linear algebra, and discrete mathematics. Great for students and professionals.', 'University Campus', 'in-person', DATE_ADD(NOW(), INTERVAL 23 DAY), DATE_ADD(NOW(), INTERVAL 23 DAY) + INTERVAL 5 HOUR, 18, 11, 45, 'upcoming', NOW()),

(9, 'Physics & Science Exploration', 'Hands-on science workshop covering physics concepts, practical experiments, and real-world applications.', 'Science Center', 'in-person', DATE_ADD(NOW(), INTERVAL 24 DAY), DATE_ADD(NOW(), INTERVAL 24 DAY) + INTERVAL 4 HOUR, 22, 14, 30, 'upcoming', NOW());

-- STEP 4: Insert EventSkills relationships
-- Skill ID Reference from database (swapdb.sql):
-- 1=JavaScript, 2=React, 3=Python, 4=Node.js, 5=SQL Database, 6=Git, 7=HTML&CSS, 8=PHP, 9=Java, 10=Cloud
-- 11=Cybersecurity, 12=Mobile App, 13=Figma, 14=Photoshop, 15=Illustrator, 16=UI/UX, 17=Logo, 18=Typography
-- 19=Color Theory, 20=Brand, 21=3D Model, 22=Animation, 23=InDesign, 24=Portrait Photo, 25=Lightroom, 26=Video Edit

-- Event 1: Web Development Workshop → Skills: JavaScript(1), HTML&CSS(7), Git(6), PHP(8)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(1, 1, 'yes'),
(1, 7, 'yes'),
(1, 6, 'yes'),
(1, 8, 'yes');

-- Event 2: Advanced React.js Bootcamp → Skills: JavaScript(1), React(2)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(2, 1, 'yes'),
(2, 2, 'yes');

-- Event 3: Node.js Backend Development → Skills: JavaScript(1), Node.js(4)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(3, 1, 'yes'),
(3, 4, 'yes');

-- Event 4: Photography Basics → Skills: Portrait Photography(24)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(4, 24, 'yes');

-- Event 5: Advanced Photography Workshop → Skills: Portrait Photography(24), Lightroom(25)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(5, 24, 'yes'),
(5, 25, 'yes');

-- Event 6: Design Thinking Session → Skills: UI/UX Design(16), Figma(13)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(6, 16, 'yes'),
(6, 13, 'yes');

-- Event 7: UI/UX Design Masterclass → Skills: UI/UX Design(16), Figma(13)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(7, 16, 'yes'),
(7, 13, 'yes');

-- Event 8: Business & Marketing Workshop → Skills: Brand Design(20), Logo Design(17)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(8, 20, 'yes'),
(8, 17, 'yes');

-- Event 9: Digital Marketing & SEO → Skills: Brand Design(20), UI/UX Design(16)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(9, 20, 'yes'),
(9, 16, 'yes');

-- Event 10: Entrepreneurship Bootcamp → Skills: Cloud Computing(10), Python(3)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(10, 10, 'yes'),
(10, 3, 'yes');

-- Event 11: Language Exchange Meetup → Skills: HTML&CSS(7), JavaScript(1)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(11, 7, 'yes'),
(11, 1, 'yes');

-- Event 12: Spanish Conversation Workshop → Skills: PHP(8), SQL Database(5)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(12, 8, 'yes'),
(12, 5, 'yes');

-- Event 13: French Language Intensive → Skills: Python(3), Java(9)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(13, 3, 'yes'),
(13, 9, 'yes');

-- Event 14: Python for Data Science → Skills: Python(3), SQL Database(5)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(14, 3, 'yes'),
(14, 5, 'yes');

-- Event 15: Machine Learning 101 → Skills: Python(3), Cloud Computing(10)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(15, 3, 'yes'),
(15, 10, 'yes');

-- Event 16: Graphic Design Fundamentals → Skills: Adobe Illustrator(15), Photoshop(14)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(16, 15, 'yes'),
(16, 14, 'yes');

-- Event 17: Digital Illustration Workshop → Skills: Adobe Illustrator(15), Animation(22)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(17, 15, 'yes'),
(17, 22, 'yes');

-- Event 18: Skill Exchange Fair 2025 → Skills: JavaScript(1)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(18, 1, 'no');

-- Event 19: Advanced Mathematics Workshop → Skills: Java(9), Cybersecurity(11)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(19, 9, 'yes'),
(19, 11, 'yes');

-- Event 20: Physics & Science Exploration → Skills: Mobile App Development(12), Cloud Computing(10)
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(20, 12, 'yes'),
(20, 10, 'yes');

-- STEP 5: Insert EventsAttendees relationships
-- NOTE: Each user can only attend an event once (UNIQUE constraint on EventId, UserId)
-- CurrentAttendeesNumber represents total attendances, but unique users are limited to 10 (we have 10 users max)

-- Event 1: 8 attendees (Users 2, 3, 4, 5, 6, 7, 8, 9)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9);

-- Event 2: 10 attendees (Users 1-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8), (2, 9), (2, 10);

-- Event 3: 10 attendees (Users 1-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(3, 1), (3, 2), (3, 3), (3, 4), (3, 5), (3, 6), (3, 7), (3, 8), (3, 9), (3, 10);

-- Event 4: 10 attendees (Users 1-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(4, 1), (4, 2), (4, 3), (4, 4), (4, 5), (4, 6), (4, 7), (4, 8), (4, 9), (4, 10);

-- Event 5: 8 attendees (Users 3-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(5, 3), (5, 4), (5, 5), (5, 6), (5, 7), (5, 8), (5, 9), (5, 10);

-- Event 6: 10 attendees (Users 1-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(6, 1), (6, 2), (6, 3), (6, 4), (6, 5), (6, 6), (6, 7), (6, 8), (6, 9), (6, 10);

-- Event 7: 10 attendees (Users 1-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(7, 1), (7, 2), (7, 3), (7, 4), (7, 5), (7, 6), (7, 7), (7, 8), (7, 9), (7, 10);

-- Event 8: 6 attendees (Users 2, 4, 6, 8, 9, 10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(8, 2), (8, 4), (8, 6), (8, 8), (8, 9), (8, 10);

-- Event 9: 10 attendees (Users 1-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(9, 1), (9, 2), (9, 3), (9, 4), (9, 5), (9, 6), (9, 7), (9, 8), (9, 9), (9, 10);

-- Event 10: 10 attendees (Users 1-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(10, 1), (10, 2), (10, 3), (10, 4), (10, 5), (10, 6), (10, 7), (10, 8), (10, 9), (10, 10);

-- Event 11: 9 attendees (Users 1-9)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(11, 1), (11, 2), (11, 3), (11, 4), (11, 5), (11, 6), (11, 7), (11, 8), (11, 9);

-- Event 12: 10 attendees (Users 1-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(12, 1), (12, 2), (12, 3), (12, 4), (12, 5), (12, 6), (12, 7), (12, 8), (12, 9), (12, 10);

-- Event 13: 8 attendees (Users 2-9)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(13, 2), (13, 3), (13, 4), (13, 5), (13, 6), (13, 7), (13, 8), (13, 9);

-- Event 14: 10 attendees (Users 1-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(14, 1), (14, 2), (14, 3), (14, 4), (14, 5), (14, 6), (14, 7), (14, 8), (14, 9), (14, 10);

-- Event 15: 10 attendees (Users 1-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(15, 1), (15, 2), (15, 3), (15, 4), (15, 5), (15, 6), (15, 7), (15, 8), (15, 9), (15, 10);

-- Event 16: 9 attendees (Users 1-9)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(16, 1), (16, 2), (16, 3), (16, 4), (16, 5), (16, 6), (16, 7), (16, 8), (16, 9);

-- Event 17: 7 attendees (Users 1-7)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(17, 1), (17, 2), (17, 3), (17, 4), (17, 5), (17, 6), (17, 7);

-- Event 18: 10 attendees (Users 1-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(18, 1), (18, 2), (18, 3), (18, 4), (18, 5), (18, 6), (18, 7), (18, 8), (18, 9), (18, 10);

-- Event 19: 10 attendees (Users 1-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(19, 1), (19, 2), (19, 3), (19, 4), (19, 5), (19, 6), (19, 7), (19, 8), (19, 9), (19, 10);

-- Event 20: 10 attendees (Users 1-10)
INSERT INTO EventsAttendees (EventId, UserId) VALUES
(20, 1), (20, 2), (20, 3), (20, 4), (20, 5), (20, 6), (20, 7), (20, 8), (20, 9), (20, 10);

-- STEP 6: Re-enable foreign key checks  
SET FOREIGN_KEY_CHECKS=1;
