-- ============================================
-- Skill Service Exchange Database - SEED DATA
-- Combined Insertions from Multiple Sources
-- ============================================
-- Created: February 6, 2026
-- Includes all test data and sample information for development
-- 
-- Data Sources:
-- ✓ Users (10 users)
-- ✓ Categories (16 categories)
-- ✓ Skills (160+ skills across all categories)
-- ✓ UserSkills (26 user skill relationships)
-- ✓ Events (20 events with dates using NOW() + INTERVAL)
-- ✓ Posts (10 service posts)
-- ✓ EventsAttendees (attendees for each event)
-- ✓ Exchanges (10 completed/pending exchanges)
-- ✓ Rating (10 reviews from exchanges)
-- ✓ UserComments (10 comments on posts)
-- ✓ UserNotifications (Enhanced with SenderId/RecipientId)
-- ✓ CreditTransactions (10 transactions)
-- ✓ PostSkills (26+ skills linked to posts)
-- ✓ EventSkills (skills required for each event)
-- ✓ ProfileReviews (4 user profile reviews)

SET FOREIGN_KEY_CHECKS=0;

-- ============================================
-- INSERT USERS (10 users)
-- ============================================
INSERT INTO users (UserName, FullName, Email, Password, Description, ProfessionalTitle, Location, PhoneNumber, BirthDate, Gender, Rating, RatingCount, CreditBalance, IsAdmin, IsBanned) VALUES
('youssef_dev', 'Youssef Benhadj', 'youssef@outlook.dz', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Experienced web developer passionate about teaching and mentoring', 'Senior Developer', 'Algiers, Hydra', '+213 05 12 345 678', '1990-05-15', 'M', 5.00, 8, 250, 'no', 'no'),
('fatima_design', 'Fatima Debbache', 'fatima@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Creative art director with 8 years of experience in digital design', 'UI/UX Designer', 'Oran, Downtown', '+213 06 23 456 789', '1988-08-22', 'F', 5.00, 6, 300, 'no', 'no'),
('ahmed_photo', 'Ahmed Medjahed', 'ahmed.photo@outlook.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Professional photographer and certified trainer in visual media', 'Photographer', 'Constantine, Belkaid', '+213 07 34 567 890', '1985-03-10', 'M', 4.50, 5, 180, 'no', 'no'),
('leila_writer', 'Leila Saidane', 'leila.writer@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Content writer and expert in copywriting and SEO optimization', 'Content Writer', 'Annaba, Sidi Salem', '+213 05 45 678 901', '1992-11-30', 'F', 5.00, 7, 220, 'no', 'no'),
('karim_music', 'Karim Bouchikhi', 'karim.music@outlook.dz', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Music teacher specialized in guitar instruction for all levels', 'Music Instructor', 'Tlemcen', '+213 06 56 789 012', '1987-07-18', 'M', 5.00, 9, 190, 'no', 'no'),
('amina_chef', 'Amina Hadj-Aissa', 'amina.chef@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Professional chef trained in culinary arts and traditional cooking', 'Chef', 'Blida, Downtown', '+213 07 67 890 123', '1991-04-25', 'F', 4.75, 4, 280, 'no', 'no'),
('ali_fitness', 'Ali Bouchta', 'ali.fitness@outlook.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Certified personal trainer and nutritionist with 12 years experience', 'Fitness Trainer', 'Setif, Haouchias', '+213 05 78 901 234', '1989-09-12', 'M', 4.00, 3, 210, 'no', 'no'),
('samira_language', 'Samira Kebaili', 'samira.lang@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Polyglot fluent in 6 languages with international teaching credentials', 'Language Teacher', 'Medea', '+213 06 89 012 345', '1993-06-08', 'F', 4.25, 5, 260, 'no', 'no'),
('moussa_mechanic', 'Moussa Aidel', 'moussa.mechanic@outlook.dz', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Auto mechanic with 15 years experience in vehicle diagnostics', 'Mechanic', 'Tipaza, Chenoua', '+213 07 90 123 456', '1982-11-20', 'M', 5.00, 10, 230, 'no', 'no'),
('zainab_garden', 'Zainab Benkhalifa', 'zainab.garden@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Master horticulturist and expert in sustainable landscaping design', 'Horticulturist', 'Boumerdes, Baya', '+213 05 01 234 567', '1986-02-14', 'F', 4.50, 6, 270, 'no', 'no');

-- ============================================
-- INSERT CATEGORIES (16 categories)
-- ============================================
INSERT INTO category (CategoryName, CategoryDescription) VALUES
('Technology & Programming', 'Web development, software engineering, databases, and IT skills'),
('Design & Creative', 'Graphic design, UI/UX, illustration, and digital art'),
('Photography & Video', 'Photography, videography, editing, and visual media'),
('Writing & Content', 'Creative writing, copywriting, blogging, and content creation'),
('Music & Performance', 'Musical instruments, singing, music theory, and performance arts'),
('Cooking & Culinary', 'Cooking techniques, baking, cuisine specialties, and food preparation'),
('Health & Fitness', 'Exercise, nutrition, wellness, and physical training'),
('Languages', 'Foreign languages, translation, and communication skills'),
('Automotive & Mechanics', 'Car maintenance, repairs, and automotive knowledge'),
('Gardening & Nature', 'Gardening, landscaping, plant care, and horticulture'),
('Business & Finance', 'Entrepreneurship, investing, accounting, and business management'),
('Arts & Crafts', 'Painting, drawing, sculpture, and handmade crafts'),
('Home Improvement', 'DIY projects, construction, repairs, and home maintenance'),
('Cleaning & Organization', 'Home organization, cleaning techniques, and decluttering'),
('Science & Education', 'STEM subjects, tutoring, and educational support'),
('Fashion & Beauty', 'Sewing, styling, makeup, and personal appearance');

-- ============================================
-- INSERT SKILLS (160+ skills)
-- ============================================
INSERT INTO skills (SkillName, CategoryId, SkillDescription) VALUES
('JavaScript', 1, 'Modern JavaScript programming and ES6+ features'),
('React', 1, 'React framework for building user interfaces'),
('Python', 1, 'Python programming for various applications'),
('Node.js', 1, 'Server-side JavaScript development'),
('SQL Database', 1, 'Relational database design and queries'),
('Git Version Control', 1, 'Source code management with Git'),
('HTML & CSS', 1, 'Web markup and stylesheet design'),
('PHP', 1, 'Server-side scripting language'),
('Java', 1, 'Object-oriented programming with Java'),
('Cloud Computing', 1, 'AWS, Azure and cloud services'),
('Cybersecurity', 1, 'Network security and ethical hacking'),
('Mobile App Development', 1, 'iOS and Android development'),
('Figma', 2, 'UI/UX design tool and prototyping'),
('Adobe Photoshop', 2, 'Professional photo editing software'),
('Adobe Illustrator', 2, 'Vector graphics and illustration'),
('UI/UX Design', 2, 'Interface design and user experience'),
('Logo Design', 2, 'Brand identity and logo creation'),
('Typography', 2, 'Font design and text layout'),
('Color Theory', 2, 'Color selection and harmony'),
('Brand Design', 2, 'Complete brand identity systems'),
('3D Modeling', 2, 'Three-dimensional design and rendering'),
('Animation', 2, 'Animated graphics and character animation'),
('InDesign', 2, 'Document layout and publishing'),
('Portrait Photography', 3, 'Professional portrait techniques'),
('Lightroom', 3, 'Photo editing and color grading'),
('Video Editing', 3, 'Post-production and video assembly'),
('Adobe Premiere', 3, 'Professional video editing software'),
('Drone Photography', 3, 'Aerial photography and videography'),
('Wedding Photography', 3, 'Event and wedding documentation'),
('Product Photography', 3, 'Commercial product imagery'),
('Street Photography', 3, 'Urban candid photography'),
('Studio Lighting', 3, 'Professional lighting setup'),
('Final Cut Pro', 3, 'Apple video editing suite'),
('After Effects', 3, 'Motion graphics and visual effects'),
('Creative Writing', 4, 'Fiction writing and storytelling'),
('Copywriting', 4, 'Promotional and marketing writing'),
('Blogging', 4, 'Blog content creation and management'),
('Journalism', 4, 'Journalism and news writing'),
('Screenwriting', 4, 'Script and screenplay writing'),
('SEO Writing', 4, 'Search engine optimized content'),
('Editing & Proofreading', 4, 'Text correction and improvement'),
('Technical Writing', 4, 'Technical documentation and manuals'),
('Poetry', 4, 'Poetry composition and technique'),
('Social Media Content', 4, 'Social media content writing'),
('Ghostwriting', 4, 'Ghostwriting for authors'),
('Classical Guitar', 5, 'Classical guitar instruction'),
('Acoustic Guitar', 5, 'Acoustic guitar techniques'),
('Electric Guitar', 5, 'Advanced electric guitar techniques'),
('Singing', 5, 'Vocal instruction and technique'),
('Piano', 5, 'Piano lessons and music theory'),
('Music Theory', 5, 'Harmony, melody and composition'),
('Ukulele', 5, 'Ukulele learning and teaching'),
('Drums', 5, 'Drum techniques and rhythm'),
('Bass', 5, 'Bass instruction and accompaniment'),
('Music Composition', 5, 'Music creation and composition'),
('Stage Performance', 5, 'Performance and stage techniques'),
('Mediterranean Cuisine', 6, 'Mediterranean cooking preparation'),
('Baking & Pastry', 6, 'Baking techniques and pastry'),
('Algerian Cuisine', 6, 'Traditional Algerian dishes and recipes'),
('International Cuisine', 6, 'Asian, French, and Italian cooking'),
('Halal Cooking', 6, 'Certified halal food preparation'),
('Special Diet Cooking', 6, 'Vegan, gluten-free, vegetarian meals'),
('Cooking Techniques', 6, 'Roasting, braising, and culinary methods'),
('Sauce Preparation', 6, 'Homemade sauces and condiments'),
('Kitchen Preparation', 6, 'Kitchen setup and organization'),
('Catering & Events', 6, 'Professional catering and events'),
('Healthy Nutrition Cooking', 6, 'Healthy and balanced meal preparation'),
('Personal Training', 7, 'Customized training programs'),
('Yoga & Pilates', 7, 'Yoga and Pilates instruction'),
('Cardio Fitness', 7, 'Cardiovascular training'),
('Weight Training', 7, 'Muscle strengthening and bodybuilding'),
('Sports Nutrition', 7, 'Nutritional advice for athletes'),
('Stretching & Flexibility', 7, 'Flexibility and stretching techniques'),
('Fitness Dance', 7, 'Dance fitness classes'),
('Crossfit', 7, 'Intense functional training'),
('Health Coaching', 7, 'Overall health and wellness coaching'),
('Physical Rehabilitation', 7, 'Recovery and physical rehabilitation'),
('Meditation & Relaxation', 7, 'Relaxation and meditation techniques'),
('French', 8, 'French language instruction'),
('English', 8, 'English courses for all levels'),
('Classical Arabic', 8, 'Arabic grammar and literature'),
('Algerian Dialect', 8, 'Algerian Arabic (Darija)'),
('Spanish', 8, 'Spanish language and culture'),
('German', 8, 'German language instruction'),
('Italian', 8, 'Italian language and culture'),
('Mandarin', 8, 'Mandarin Chinese and culture'),
('TOEFL & IELTS', 8, 'English language test preparation'),
('Translation', 8, 'Professional translation services'),
('Language Conversation', 8, 'Conversation practice in foreign languages'),
('Auto Mechanics', 9, 'General automobile repair'),
('Engines & Transmissions', 9, 'Engine and transmission repair'),
('Braking Systems', 9, 'Brake systems and maintenance'),
('Automotive Electrical', 9, 'Vehicle electrical systems'),
('Air Conditioning', 9, 'AC repair and recharging'),
('Automotive Painting', 9, 'Car painting and bodywork'),
('Computer Diagnostics', 9, 'OBD diagnostics and electronics'),
('Suspension & Alignment', 9, 'Suspension and wheel alignment'),
('Preventive Maintenance', 9, 'Regular maintenance and upkeep'),
('Tires & Wheels', 9, 'Tire replacement and balancing'),
('Two-Wheeler Mechanics', 9, 'Motorcycle and scooter repair'),
('Ornamental Gardening', 10, 'Decorative garden creation'),
('Permaculture', 10, 'Permanent and sustainable cultivation'),
('Vegetable Gardening', 10, 'Vegetable gardening and crops'),
('Plant Care', 10, 'Plant maintenance and care'),
('Landscaping', 10, 'Professional landscape design'),
('Ecological Gardening', 10, 'Ecological gardening techniques'),
('Garden Composition', 10, 'Garden design and composition'),
('Indoor Plants', 10, 'Indoor plant cultivation'),
('Aromatic Herbs', 10, 'Herb and aromatic plant growing'),
('Composting', 10, 'Composting and fertilization technique'),
('Bonsai', 10, 'Bonsai art and miniature cultivation'),
('Entrepreneurship', 11, 'Business launch and management'),
('Financial Management', 11, 'Business finance management'),
('Accounting', 11, 'Bookkeeping and accounting'),
('Investing', 11, 'Investment advice and strategies'),
('Digital Marketing', 11, 'Online marketing strategies'),
('Project Management', 11, 'Project management techniques'),
('Leadership', 11, 'Leadership development'),
('Business Negotiation', 11, 'Negotiation techniques'),
('Business Plan', 11, 'Business plan creation'),
('Sales & Commerce', 11, 'Sales techniques'),
('Human Resources', 11, 'Human resources management'),
('Acrylic Painting', 12, 'Acrylic painting techniques'),
('Oil Painting', 12, 'Oil painting techniques'),
('Watercolor', 12, 'Watercolor painting'),
('Pencil Drawing', 12, 'Classical drawing techniques'),
('Sculpture', 12, 'Sculpture and modeling techniques'),
('Ceramics', 12, 'Pottery and ceramic work'),
('Weaving', 12, 'Traditional weaving techniques'),
('Embroidery', 12, 'Embroidery and thread work'),
('Calligraphy', 12, 'Calligraphy and artistic lettering'),
('Digital Art', 12, 'Digital artistic creation'),
('Engraving', 12, 'Engraving and printing techniques'),
('Masonry', 13, 'Masonry techniques'),
('Carpentry', 13, 'Woodworking and carpentry'),
('Home Electrical', 13, 'Home electrical installation'),
('Plumbing', 13, 'Plumbing installation and repair'),
('Interior Painting', 13, 'Interior painting and finishing'),
('Flooring', 13, 'Tile and flooring installation'),
('Wallpapering', 13, 'Wallpaper and tapestry installation'),
('Bathroom Renovation', 13, 'Bathroom renovation'),
('Kitchen Renovation', 13, 'Kitchen renovation'),
('Doors & Windows', 13, 'Door and window installation'),
('Heating & Cooling', 13, 'Heating and air conditioning installation'),
('Home Organization', 14, 'Home organization and storage'),
('Eco Cleaning', 14, 'Ecological and natural cleaning'),
('Decluttering', 14, 'Decluttering technique'),
('Wardrobe Organization', 14, 'Clothing organization'),
('Professional Cleaning', 14, 'Professional home cleaning'),
('Kitchen Organization', 14, 'Kitchen organization'),
('Home Office Organization', 14, 'Work space organization'),
('Storage Management', 14, 'Efficient storage systems'),
('Detail Cleaning', 14, 'Detailed and thorough cleaning'),
('Space Evaluation', 14, 'Space evaluation and optimization'),
('Lifestyle Advice', 14, 'Minimalist lifestyle advice'),
('Mathematics', 15, 'Mathematics instruction'),
('Chemistry', 15, 'Chemistry courses and laboratory'),
('Physics', 15, 'Physics instruction'),
('Biology', 15, 'Biology and science courses'),
('Academic Tutoring', 15, 'Homework help and catch-up'),
('Exam Preparation', 15, 'Baccalaureate and exam preparation'),
('Scientific English', 15, 'Technical and scientific English'),
('Computer Science Education', 15, 'Computer science instruction'),
('Natural Sciences', 15, 'Natural sciences instruction'),
('Study Methodology', 15, 'Effective study techniques'),
('Academic Orientation', 15, 'Academic guidance and orientation'),
('Sewing', 16, 'Sewing and garment construction'),
('Clothing Design', 16, 'Clothing design and creation'),
('Textile Alteration', 16, 'Clothing alteration and repair'),
('Professional Makeup', 16, 'Artistic and professional makeup'),
('Hairstyling', 16, 'Hair cutting and styling'),
('Skincare', 16, 'Facial and skin care'),
('Body Beauty', 16, 'Body care and beauty'),
('Personal Styling', 16, 'Personal style and image consultation'),
('Fashion & Trends', 16, 'Fashion advice and trends'),
('Fashion Accessories', 16, 'Accessory and jewelry creation'),
('Nail Care', 16, 'Manicure and pedicure services');

-- ============================================
-- INSERT USER SKILLS (26 user-skill relationships)
-- ============================================
INSERT INTO userskills (UserId, SkillId, SkillType, ProficiencyLevel) VALUES
(1, 1, 'teach', 100),
(1, 2, 'teach', 75),
(1, 7, 'teach', 100),
(2, 13, 'teach', 100),
(2, 14, 'teach', 75),
(2, 20, 'teach', 75),
(3, 24, 'teach', 100),
(3, 25, 'teach', 75),
(3, 27, 'teach', 75),
(4, 35, 'teach', 100),
(4, 36, 'teach', 75),
(4, 39, 'teach', 50),
(5, 47, 'teach', 100),
(5, 50, 'teach', 75),
(5, 56, 'teach', 50),
(6, 59, 'teach', 100),
(6, 60, 'teach', 75),
(6, 61, 'teach', 50),
(7, 69, 'teach', 100),
(7, 71, 'teach', 75),
(8, 83, 'teach', 100),
(8, 84, 'teach', 100),
(9, 91, 'teach', 100),
(9, 92, 'teach', 75),
(10, 108, 'teach', 100),
(10, 109, 'teach', 75);

-- ============================================
-- INSERT POSTS (10 service posts)
-- ============================================
INSERT INTO posts (UserId, CategoryId, Title, Description, PostType, MeetLocation, AvailableDate, Duration, PaymentMethod, RequiredCredits, PostStatus, LikeCount) VALUES
(1, 1, 'Private JavaScript Lessons', 'Private lessons in JavaScript for beginners to intermediate. Fast learning guaranteed.', 'in-person', 'Algiers, Hydra - Cultural Center', '2026-03-03 10:00:00', 120, 'credit', 50, 'active', 8),
(2, 2, 'Custom Graphic Design', 'Creation of custom designs for your brand. Logos, brochures, and marketing materials.', 'online', NULL, '2026-03-01 14:30:00', 180, 'exchange', 0, 'active', 12),
(3, 3, 'Professional Photo Session', 'Professional photography for portraits, products, and events. Retouching included.', 'in-person', 'Constantine, Belkaid - Photo Studio', '2026-03-06 09:00:00', 90, 'credit', 60, 'active', 15),
(4, 4, 'Blog Content Writing', 'Writing SEO-optimized articles for your blog. Engaging and relevant content.', 'online', NULL, '2026-03-04 11:00:00', 240, 'exchange', 0, 'active', 6),
(5, 5, 'Guitar Lessons All Levels', 'Teaching acoustic and electric guitar. Structured and progressive method.', 'in-person', 'Tlemcen - Music Studio', '2026-03-12 15:00:00', 60, 'credit', 45, 'active', 10),
(6, 6, 'Algerian Cooking Coaching', 'Traditional Algerian cuisine training. Learning authentic recipes.', 'in-person', 'Blida, Downtown - Kitchen', '2026-03-07 18:00:00', 150, 'credit', 55, 'active', 9),
(7, 7, 'Personal Fitness Coaching', 'Personal training adapted to your goals. With nutrition and regular follow-up.', 'in-person', 'Setif, Haouchias - ProFit Gym', '2026-03-02 06:00:00', 120, 'exchange', 0, 'active', 14),
(8, 8, 'Intensive English Tutoring', 'Intensive English conversation and grammar course. Rapid improvement guaranteed.', 'online', NULL, '2026-02-28 13:00:00', 90, 'credit', 40, 'active', 7),
(9, 9, 'Auto Mechanics Diagnostics', 'Complete vehicle diagnostics with detailed report. Repair quote included.', 'in-person', 'Tipaza, Chenoua - Garage', '2026-03-09 08:30:00', 60, 'exchange', 0, 'active', 5),
(10, 10, 'Organic Gardening Consultation', 'Personalized advice for ecological garden. Design and landscape arrangement.', 'in-person', 'Boumerdes, Baya - Green Center', '2026-03-05 10:00:00', 180, 'credit', 50, 'active', 11);

-- ============================================
-- INSERT EXCHANGES (10 exchanges)
-- ============================================
INSERT INTO exchanges (PostId, OfferedByUserId, RequestedByUserId, Status, ProposedDate, ConfirmedDate, CompletedDate, MeetingLocation, CreditsCost) VALUES
(1, 1, 2, 'completed', '2025-12-12 10:00:00', '2025-12-13 14:30:00', '2025-12-15 18:00:00', 'Alger, Hydra', 0),
(2, 2, 3, 'accepted', '2025-12-13 09:00:00', '2025-12-14 11:20:00', NULL, 'En Ligne', 0),
(3, 3, 4, 'completed', '2025-12-14 15:00:00', '2025-12-15 16:45:00', '2025-12-20 18:30:00', 'Constantine, Belkaid', 0),
(4, 4, 5, 'pending', '2025-12-16 10:30:00', NULL, NULL, 'En Ligne', 0),
(5, 5, 6, 'accepted', '2025-12-17 14:00:00', '2025-12-18 09:15:00', NULL, 'Tlemcen', 0),
(6, 6, 7, 'completed', '2025-12-19 11:00:00', '2025-12-21 13:45:00', '2026-01-05 20:00:00', 'Blida', 0),
(7, 7, 8, 'accepted', '2025-12-22 16:30:00', '2025-12-23 10:00:00', NULL, 'Sétif', 0),
(8, 8, 9, 'pending', '2025-12-25 09:00:00', NULL, NULL, 'En Ligne', 0),
(9, 9, 10, 'completed', '2025-12-26 15:20:00', '2025-12-28 11:30:00', '2026-01-08 17:00:00', 'Tipaza', 0),
(10, 10, 1, 'accepted', '2026-01-03 10:45:00', '2026-01-04 14:00:00', NULL, 'Boumerdès', 0);

-- ============================================
-- INSERT RATINGS (10 reviews)
-- ============================================
INSERT INTO ratings (ExchangeId, ReviewerId, ReviewedUserId, Rating, ReviewText) VALUES
(1, 2, 1, 5, 'Excellent teacher! Very pedagogical and patient. Highly recommended!'),
(2, 3, 2, 5, 'Talented designer with excellent understanding of needs. Superb result!'),
(3, 4, 3, 4, 'Very professional. Quality photographs. Some minor delays but final result very good.'),
(4, 5, 4, 5, 'Well-structured content and SEO optimized. Really satisfied!'),
(6, 7, 6, 5, 'Delicious lessons! Chef very welcoming and clear explanations. Will do again!'),
(7, 8, 7, 4, 'Good coach. Motivating and effective. Programs well adapted.'),
(9, 10, 9, 5, 'Complete and very professional diagnostics. Trustworthy mechanic!'),
(10, 1, 10, 5, 'Excellent gardening advice. Very knowledgeable and inspiring!'),
(5, 6, 5, 5, 'Amazing guitar teacher! Patience and excellent method. Rapid progress!'),
(8, 9, 8, 4, 'Good English lessons. Good accent and clear explanations.');

-- ============================================
-- INSERT USER COMMENTS (10 comments)
-- ============================================
INSERT INTO usercomments (PostId, UserId, CommentText, LikeCount) VALUES
(1, 2, 'Very interesting! I am looking for exactly this type of course. What times are available?', 2),
(1, 3, 'Youssef is excellent! I took his courses and made real progress quickly.', 5),
(2, 4, 'Impeccable design! Very creative. How long for a project?', 1),
(2, 5, 'We worked with Fatima on our logo. Fantastic result!', 3),
(3, 6, 'Professional quality photography. Competitive rates!', 4),
(4, 7, 'Very well written articles and SEO optimized. Excellent work!', 2),
(5, 8, 'Ahmed is an exceptional guitar teacher! Highly recommended.', 6),
(6, 9, 'Authentic Algerian cuisine. Warm and friendly atmosphere.', 3),
(7, 10, 'Motivating coaching with visible results quickly. Really satisfied!', 4),
(8, 1, 'Teacher Samira is very patient and explains well. Very good course!', 2);

-- ============================================
-- INSERT POST SKILLS (26+ skills linked to posts)
-- ============================================
INSERT INTO postskills (PostId, SkillId, SkillType) VALUES
(1, 1, 'offered'),
(1, 2, 'offered'),
(1, 7, 'offered'),
(2, 13, 'offered'),
(2, 14, 'offered'),
(2, 20, 'offered'),
(3, 24, 'offered'),
(3, 25, 'offered'),
(3, 27, 'offered'),
(4, 35, 'offered'),
(4, 36, 'offered'),
(4, 39, 'offered'),
(5, 47, 'offered'),
(5, 50, 'offered'),
(6, 59, 'offered'),
(6, 60, 'offered'),
(6, 61, 'offered'),
(7, 69, 'offered'),
(7, 71, 'offered'),
(8, 83, 'offered'),
(8, 84, 'offered'),
(9, 91, 'offered'),
(9, 92, 'offered'),
(10, 108, 'offered'),
(10, 109, 'offered');

-- ============================================
-- INSERT USER NOTIFICATIONS (New structure with SenderId/RecipientId)
-- ============================================
INSERT INTO usernotifications (SenderId, RecipientId, NotificationType, Title, Message, IsRead, NotificationSection) VALUES
(1, 2, 'acceptedInEvent', 'Event Request Accepted', 'JohnDoe has accepted your request to join Web Development Workshop', 'no', 'events'),
(3, 4, 'acceptedInEvent', 'Event Request Accepted', 'MikeSmith has accepted your request to join UI/UX Design Session', 'yes', 'events'),
(2, 1, 'acceptedInEvent', 'Event Request Accepted', 'JaneDoe has accepted your request to join Python Basics', 'no', 'events'),
(2, 5, 'RejectedFromEvent', 'Event Request Declined', 'JaneDoe has declined your request to join Python Basics', 'no', 'events'),
(1, 3, 'RejectedFromEvent', 'Event Request Declined', 'JohnDoe has declined your request to join Web Development Workshop', 'yes', 'events'),
(2, 1, 'booking', 'Event Join Request', 'JaneDoe has requested to join your event Web Development Workshop on January 28, 2026', 'no', 'Exchange'),
(3, 1, 'booking', 'Event Join Request', 'MikeSmith has requested to join your event Web Development Workshop on January 27, 2026', 'no', 'Exchange'),
(4, 2, 'booking', 'Event Join Request', 'SarahLee has requested to join your event Python Basics on January 26, 2026', 'no', 'Exchange'),
(1, 3, 'booking', 'Event Join Request', 'JohnDoe has requested to join your event Data Science Meetup on January 25, 2026', 'yes', 'Exchange'),
(3, 1, 'booking', 'New Exchange Request', 'MikeSmith wants to exchange skills with you for JavaScript Fundamentals', 'no', 'Exchange'),
(4, 2, 'accepted', 'Exchange Accepted', 'SarahLee has accepted your skill exchange request', 'yes', 'Exchange'),
(1, 4, 'completing', 'Exchange Completed', 'JohnDoe has marked the exchange as completed', 'no', 'Exchange'),
(2, 3, 'being_refused', 'Exchange Declined', 'JaneDoe has declined your skill exchange request', 'no', 'Exchange'),
(2, 1, 'rating', 'New Rating Received', 'JaneDoe gave you a 5-star rating!', 'no', 'Reviews'),
(3, 2, 'rating', 'New Rating Received', 'MikeSmith gave you a 4-star rating', 'yes', 'Reviews'),
(1, 3, 'comment', 'New Comment', 'JohnDoe commented on your post: Great tutorial!', 'no', 'Reviews'),
(4, 1, 'like', 'New Like', 'SarahLee liked your post', 'yes', 'Reviews'),
(NULL, 1, 'earned', 'Credits Earned', 'You earned 50 credits for completing an exchange', 'no', 'credits'),
(NULL, 2, 'spent', 'Credits Spent', 'You spent 30 credits on Web Development Workshop', 'yes', 'credits'),
(NULL, 3, 'earned', 'Credits Earned', 'You earned 25 credits for receiving a 5-star rating', 'no', 'credits');

-- ============================================
-- INSERT CREDIT TRANSACTIONS (10 transactions)
-- ============================================
INSERT INTO credittransactions (UserId, TransactionType, Amount, BalanceAfter, RelatedEntityType, RelatedEntityId, Description) VALUES
(1, 'earned', 50, 300, 'exchange', 1, 'Exchange completed with Fatima Debbache'),
(2, 'spent', 45, 255, 'exchange', 1, 'JavaScript course with Youssef Benhadj'),
(3, 'earned', 60, 240, 'exchange', 3, 'Photo session completed with Ahmed'),
(4, 'spent', 50, 170, 'exchange', 4, 'Blog content writing with Leila'),
(5, 'earned', 45, 235, 'exchange', 5, 'Guitar lessons given to Karim'),
(6, 'spent', 55, 225, 'exchange', 6, 'Cooking coaching with Amina'),
(7, 'earned', 100, 310, 'bonus', NULL, 'Bonus welcome new member'),
(8, 'spent', 40, 220, 'exchange', 8, 'English tutoring with Samira'),
(9, 'earned', 80, 310, 'exchange', 9, 'Mechanics diagnostics completed'),
(10, 'earned', 50, 320, 'exchange', 10, 'Organic gardening consultation provided');

-- ============================================
-- INSERT PROFILE REVIEWS (4 user reviews)
-- ============================================
INSERT INTO profile_reviews (ID, ReviewerID, ReviewDate, ReviewText, ReviewRate, userID) VALUES
(13, 3, '2026-01-17 21:37:43', 'NICE', 5, 2),
(14, 2, '2026-01-17 21:38:29', 'NOOOOOOO', 1, 3),
(17, 2, '2026-02-01 23:19:59', 'This is my first test review for this user!', 5, 4),
(18, 4, '2026-02-01 23:36:00', 'This is my first test review for this user!', 5, 2);

-- ============================================
-- INSERT EVENTS (20 events with dynamic dates)
-- ============================================
INSERT INTO events (OrganizerId, EventTitle, EventDescription, EventLocation, EventType, EventStartDate, EventEndDate, MaxAttendees, CurrentAttendeesNumber, EventCost, EventStatus, CreatedAt) VALUES
(1, 'Web Development Workshop', 'Join us for an intensive hands-on workshop where we''ll dive deep into modern web development practices.', 'Community Center, Room 304', 'in-person', DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY) + INTERVAL 6 HOUR, 15, 8, 50, 'upcoming', NOW()),
(2, 'Advanced React.js Bootcamp', 'Deep dive into React.js with advanced concepts including hooks, context API, Redux, and performance optimization.', 'Tech Hub Downtown', 'in-person', DATE_ADD(NOW(), INTERVAL 14 DAY), DATE_ADD(NOW(), INTERVAL 16 DAY), 20, 15, 75, 'upcoming', NOW()),
(3, 'Node.js Backend Development', 'Learn to build scalable backend applications using Node.js and Express.js.', 'Online', 'online', DATE_ADD(NOW(), INTERVAL 10 DAY), DATE_ADD(NOW(), INTERVAL 12 DAY), 25, 18, 60, 'upcoming', NOW()),
(3, 'Photography Basics Meetup', 'Learn the fundamentals of photography in this practical meetup.', 'City Park', 'in-person', DATE_ADD(NOW(), INTERVAL 5 DAY), DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 3 HOUR, 20, 12, 25, 'upcoming', NOW()),
(4, 'Advanced Photography Workshop', 'Master advanced photography techniques including macro, landscape, and portrait photography.', 'Nature Reserve', 'in-person', DATE_ADD(NOW(), INTERVAL 21 DAY), DATE_ADD(NOW(), INTERVAL 21 DAY) + INTERVAL 5 HOUR, 12, 8, 55, 'upcoming', NOW()),
(5, 'Design Thinking Session', 'This workshop explores the design thinking methodology for solving complex problems creatively.', 'Online', 'online', DATE_ADD(NOW(), INTERVAL 9 DAY), DATE_ADD(NOW(), INTERVAL 9 DAY) + INTERVAL 3 HOUR, 15, 15, 30, 'upcoming', NOW()),
(6, 'UI/UX Design Masterclass', 'Learn professional UI/UX design principles, tools like Figma, prototyping, and user research methods.', 'Design Studio', 'in-person', DATE_ADD(NOW(), INTERVAL 15 DAY), DATE_ADD(NOW(), INTERVAL 17 DAY), 18, 10, 80, 'upcoming', NOW()),
(7, 'Business & Marketing Workshop', 'Learn modern marketing strategies and social media best practices.', 'Business Hub', 'in-person', DATE_ADD(NOW(), INTERVAL 8 DAY), DATE_ADD(NOW(), INTERVAL 8 DAY) + INTERVAL 4 HOUR, 12, 6, 40, 'upcoming', NOW()),
(8, 'Digital Marketing & SEO', 'Master SEO, SEM, content marketing, and analytics.', 'Online', 'online', DATE_ADD(NOW(), INTERVAL 12 DAY), DATE_ADD(NOW(), INTERVAL 14 DAY), 30, 22, 45, 'upcoming', NOW()),
(9, 'Entrepreneurship Bootcamp', 'From idea to launch: Learn business planning, funding, pitching, and growth strategies.', 'Innovation Hub', 'in-person', DATE_ADD(NOW(), INTERVAL 20 DAY), DATE_ADD(NOW(), INTERVAL 22 DAY), 25, 18, 65, 'upcoming', NOW()),
(10, 'Language Exchange Meetup', 'A casual meetup for language learners and teachers to practice different languages.', 'Coffee Shop Downtown', 'in-person', DATE_ADD(NOW(), INTERVAL 6 DAY), DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 2 HOUR, 20, 10, 0, 'upcoming', NOW()),
(1, 'Spanish Conversation Workshop', 'Improve your Spanish speaking skills through interactive conversations and cultural activities.', 'Online', 'online', DATE_ADD(NOW(), INTERVAL 11 DAY), DATE_ADD(NOW(), INTERVAL 11 DAY) + INTERVAL 1.5 HOUR, 15, 9, 20, 'upcoming', NOW()),
(2, 'French Language Intensive', 'Comprehensive French course covering grammar, vocabulary, listening, and speaking skills.', 'Language Center', 'in-person', DATE_ADD(NOW(), INTERVAL 13 DAY), DATE_ADD(NOW(), INTERVAL 18 DAY), 12, 8, 90, 'upcoming', NOW()),
(3, 'Python for Data Science', 'Learn Python programming with a focus on data analysis, pandas, NumPy, matplotlib, and machine learning.', 'Tech Hub', 'in-person', DATE_ADD(NOW(), INTERVAL 16 DAY), DATE_ADD(NOW(), INTERVAL 18 DAY), 20, 15, 70, 'upcoming', NOW()),
(4, 'Machine Learning 101', 'Introduction to machine learning concepts, algorithms, and practical applications.', 'Online', 'online', DATE_ADD(NOW(), INTERVAL 19 DAY), DATE_ADD(NOW(), INTERVAL 21 DAY), 25, 20, 75, 'upcoming', NOW()),
(5, 'Graphic Design Fundamentals', 'Learn graphic design principles, color theory, typography, and design software.', 'Design Studio', 'in-person', DATE_ADD(NOW(), INTERVAL 10 DAY), DATE_ADD(NOW(), INTERVAL 10 DAY) + INTERVAL 4 HOUR, 15, 9, 35, 'upcoming', NOW()),
(6, 'Digital Illustration Workshop', 'Learn digital illustration techniques using Procreate, Photoshop, or Clip Studio Paint.', 'Online', 'online', DATE_ADD(NOW(), INTERVAL 17 DAY), DATE_ADD(NOW(), INTERVAL 17 DAY) + INTERVAL 3 HOUR, 12, 7, 40, 'upcoming', NOW()),
(7, 'Skill Exchange Fair 2025', 'A large community event celebrating skill exchange with various instructors and learners.', 'Main Square', 'in-person', DATE_ADD(NOW(), INTERVAL 22 DAY), DATE_ADD(NOW(), INTERVAL 22 DAY) + INTERVAL 8 HOUR, 50, 34, 0, 'upcoming', NOW()),
(8, 'Advanced Mathematics Workshop', 'Explore advanced mathematics topics including calculus, linear algebra, and discrete mathematics.', 'University Campus', 'in-person', DATE_ADD(NOW(), INTERVAL 23 DAY), DATE_ADD(NOW(), INTERVAL 23 DAY) + INTERVAL 5 HOUR, 18, 11, 45, 'upcoming', NOW()),
(9, 'Physics & Science Exploration', 'Hands-on science workshop covering physics concepts, practical experiments, and applications.', 'Science Center', 'in-person', DATE_ADD(NOW(), INTERVAL 24 DAY), DATE_ADD(NOW(), INTERVAL 24 DAY) + INTERVAL 4 HOUR, 22, 14, 30, 'upcoming', NOW());

-- ============================================
-- INSERT EVENT SKILLS (Skills required for each event)
-- ============================================
INSERT INTO eventskills (EventId, SkillId, IsRequired) VALUES
(1, 1, 'yes'), (1, 7, 'yes'), (1, 6, 'yes'), (1, 8, 'yes'),
(2, 1, 'yes'), (2, 2, 'yes'),
(3, 1, 'yes'), (3, 4, 'yes'),
(4, 24, 'yes'),
(5, 24, 'yes'), (5, 25, 'yes'),
(6, 16, 'yes'), (6, 13, 'yes'),
(7, 16, 'yes'), (7, 13, 'yes'),
(8, 20, 'yes'), (8, 17, 'yes'),
(9, 20, 'yes'), (9, 16, 'yes'),
(10, 10, 'yes'), (10, 3, 'yes'),
(11, 7, 'yes'), (11, 1, 'yes'),
(12, 8, 'yes'), (12, 5, 'yes'),
(13, 3, 'yes'), (13, 9, 'yes'),
(14, 3, 'yes'), (14, 5, 'yes'),
(15, 3, 'yes'), (15, 10, 'yes'),
(16, 15, 'yes'), (16, 14, 'yes'),
(17, 15, 'yes'), (17, 22, 'yes'),
(18, 1, 'no'),
(19, 9, 'yes'), (19, 11, 'yes'),
(20, 12, 'yes'), (20, 10, 'yes');

-- ============================================
-- INSERT EVENT ATTENDEES (Attendees for each event)
-- ============================================
INSERT INTO eventsattendees (EventId, UserId) VALUES
(1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9),
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8), (2, 9), (2, 10),
(3, 1), (3, 2), (3, 3), (3, 4), (3, 5), (3, 6), (3, 7), (3, 8), (3, 9), (3, 10),
(4, 1), (4, 2), (4, 3), (4, 4), (4, 5), (4, 6), (4, 7), (4, 8), (4, 9), (4, 10),
(5, 3), (5, 4), (5, 5), (5, 6), (5, 7), (5, 8), (5, 9), (5, 10),
(6, 1), (6, 2), (6, 3), (6, 4), (6, 5), (6, 6), (6, 7), (6, 8), (6, 9), (6, 10),
(7, 1), (7, 2), (7, 3), (7, 4), (7, 5), (7, 6), (7, 7), (7, 8), (7, 9), (7, 10),
(8, 2), (8, 4), (8, 6), (8, 8), (8, 9), (8, 10),
(9, 1), (9, 2), (9, 3), (9, 4), (9, 5), (9, 6), (9, 7), (9, 8), (9, 9), (9, 10),
(10, 1), (10, 2), (10, 3), (10, 4), (10, 5), (10, 6), (10, 7), (10, 8), (10, 9), (10, 10),
(11, 1), (11, 2), (11, 3), (11, 4), (11, 5), (11, 6), (11, 7), (11, 8), (11, 9),
(12, 1), (12, 2), (12, 3), (12, 4), (12, 5), (12, 6), (12, 7), (12, 8), (12, 9), (12, 10),
(13, 2), (13, 3), (13, 4), (13, 5), (13, 6), (13, 7), (13, 8), (13, 9),
(14, 1), (14, 2), (14, 3), (14, 4), (14, 5), (14, 6), (14, 7), (14, 8), (14, 9), (14, 10),
(15, 1), (15, 2), (15, 3), (15, 4), (15, 5), (15, 6), (15, 7), (15, 8), (15, 9), (15, 10),
(16, 1), (16, 2), (16, 3), (16, 4), (16, 5), (16, 6), (16, 7), (16, 8), (16, 9),
(17, 1), (17, 2), (17, 3), (17, 4), (17, 5), (17, 6), (17, 7),
(18, 1), (18, 2), (18, 3), (18, 4), (18, 5), (18, 6), (18, 7), (18, 8), (18, 9), (18, 10),
(19, 1), (19, 2), (19, 3), (19, 4), (19, 5), (19, 6), (19, 7), (19, 8), (19, 9), (19, 10),
(20, 1), (20, 2), (20, 3), (20, 4), (20, 5), (20, 6), (20, 7), (20, 8), (20, 9), (20, 10);

SET FOREIGN_KEY_CHECKS=1;

-- ============================================
-- SEED DATA IMPORT COMPLETE
-- ============================================
-- Total records:
-- ✓ Users: 10
-- ✓ Categories: 16
-- ✓ Skills: 160+
-- ✓ UserSkills: 26
-- ✓ Posts: 10
-- ✓ Exchanges: 10
-- ✓ Ratings: 10
-- ✓ UserComments: 10
-- ✓ PostSkills: 25
-- ✓ UserNotifications: 20
-- ✓ CreditTransactions: 10
-- ✓ ProfileReviews: 4
-- ✓ Events: 20
-- ✓ EventSkills: 40+
-- ✓ EventAttendees: 200+
-- 
-- All foreign key constraints are respected
-- Ready for development and testing
