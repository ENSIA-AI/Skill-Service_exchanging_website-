-- ============================================
-- Database Schema for Skill Exchange Platform
-- Compatible with phpMyAdmin / MySQL
-- ============================================

-- Drop tables if they exist (in reverse order of dependencies)
DROP TABLE IF EXISTS CreditTransactions;
DROP TABLE IF EXISTS Reports;
DROP TABLE IF EXISTS Rating;
DROP TABLE IF EXISTS Exchanges;
DROP TABLE IF EXISTS PostAvailableDates;
DROP TABLE IF EXISTS PostSkills;
DROP TABLE IF EXISTS EventSkills;
DROP TABLE IF EXISTS UserSkills;
DROP TABLE IF EXISTS Skills;
DROP TABLE IF EXISTS PostLikes;
DROP TABLE IF EXISTS UserComments;
DROP TABLE IF EXISTS EventsAttendees;
DROP TABLE IF EXISTS Events;
DROP TABLE IF EXISTS Posts;
DROP TABLE IF EXISTS UserNotifications;
DROP TABLE IF EXISTS Category;
DROP TABLE IF EXISTS Users;

-- ============================================
-- Users Table
-- ============================================
CREATE TABLE Users (
  UserId INT AUTO_INCREMENT PRIMARY KEY,
  UserName VARCHAR(45) UNIQUE NOT NULL,
  FullName VARCHAR(45) NOT NULL,
  Email VARCHAR(45) UNIQUE NOT NULL,
  Password VARCHAR(255) NOT NULL,
  Description TEXT,
  ProfilePicture VARCHAR(255),
  ProfessionalTitle VARCHAR(100),
  Location VARCHAR(100),
  PhoneNumber VARCHAR(20),
  BirthDate DATE,
  Gender ENUM('M', 'F'),
  UserSince DATETIME DEFAULT CURRENT_TIMESTAMP,
  IsBanned ENUM('yes','no') DEFAULT 'no',
  Rating DECIMAL(3,2) DEFAULT 0.00 CHECK (Rating >= 0.00 AND Rating <= 5.00),
  RatingCount INT DEFAULT 0 CHECK (RatingCount >= 0),
  ExchangeCount INT DEFAULT 0 CHECK (ExchangeCount >= 0),
  CreditBalance INT DEFAULT 0 CHECK (CreditBalance >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Category Table
-- ============================================
CREATE TABLE Category (
  CategoryId INT AUTO_INCREMENT PRIMARY KEY,
  CategoryName VARCHAR(100) UNIQUE NOT NULL,
  CategoryDescription TEXT,
  PostCount INT DEFAULT 0 CHECK (PostCount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- UserNotifications Table
-- ============================================
CREATE TABLE UserNotifications (
  NotificationId INT AUTO_INCREMENT PRIMARY KEY,
  UserId INT NOT NULL,
  NotificationType ENUM(
    'like','comment','rating',
    'booking','accepted','completing','being_refused',
    'acceptedInEvent','RejectedFromEvent',
    'earned','spent'
  ) NOT NULL,
  Title VARCHAR(100) NOT NULL CHECK (CHAR_LENGTH(Title) >= 5),
  Message TEXT NOT NULL,
  IsRead ENUM('yes','no') DEFAULT 'no',
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  NotificationSection ENUM('Reviews','Exchange','events','credits') NOT NULL,
  
  FOREIGN KEY (UserId) REFERENCES Users(UserId) ON DELETE CASCADE,
  
  INDEX idx_notifications_user_read (UserId, IsRead),
  INDEX idx_notifications_section (NotificationSection)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Posts Table
-- ============================================
CREATE TABLE Posts (
  PostId INT AUTO_INCREMENT PRIMARY KEY,
  UserId INT NOT NULL,
  Title VARCHAR(150) NOT NULL,
  Description TEXT NOT NULL,
  PostType ENUM('in-person','online') NOT NULL,
  PostStatus ENUM('active','disabled') DEFAULT 'active',
  CategoryId INT NOT NULL,
  Duration INT CHECK (Duration > 0 AND Duration <= 480),
  MeetLocation VARCHAR(150),
  AvailableDate DATETIME NOT NULL,
  Prerequisites TEXT,
  Requirements TEXT,
  PaymentMethod ENUM('exchange','credit') NOT NULL,
  RequiredCredits INT DEFAULT 0 CHECK (RequiredCredits >= 0),
  TeachingMethodology TEXT,
  ExchangeExpectations TEXT,
  LikeCount INT DEFAULT 0 CHECK (LikeCount >= 0),
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (UserId) REFERENCES Users(UserId) ON DELETE CASCADE,
  FOREIGN KEY (CategoryId) REFERENCES Category(CategoryId) ON DELETE RESTRICT,
  
  INDEX idx_posts_status_date (PostStatus, AvailableDate),
  INDEX idx_posts_user (UserId),
  INDEX idx_posts_category (CategoryId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PostAvailableDates Table
-- ============================================
CREATE TABLE PostAvailableDates (
  PostAvailableDateId INT AUTO_INCREMENT PRIMARY KEY,
  PostId INT NOT NULL,
  AvailableDate DATETIME NOT NULL,
  
  FOREIGN KEY (PostId) REFERENCES Posts(PostId) ON DELETE CASCADE,
  
  INDEX idx_post_available_dates (PostId, AvailableDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Events Table
-- ============================================
CREATE TABLE Events (
  EventId INT AUTO_INCREMENT PRIMARY KEY,
  OrganizerId INT NOT NULL,
  EventTitle VARCHAR(150) NOT NULL,
  EventDescription TEXT NOT NULL,
  EventLocation VARCHAR(150),
  EventType ENUM('online','in-person') NOT NULL,
  EventStartDate DATETIME NOT NULL,
  EventEndDate DATETIME NOT NULL,
  MaxAttendees INT NOT NULL CHECK (MaxAttendees > 0 AND MaxAttendees <= 1000),
  CurrentAttendeesNumber INT DEFAULT 0 CHECK (CurrentAttendeesNumber >= 0),
  EventCost INT DEFAULT 0 CHECK (EventCost >= 0),
  EventStatus ENUM('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  UpdatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (OrganizerId) REFERENCES Users(UserId) ON DELETE CASCADE,
  
  INDEX idx_events_status_date (EventStatus, EventStartDate),
  INDEX idx_events_organizer (OrganizerId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- EventsAttendees Table
-- ============================================
CREATE TABLE EventsAttendees (
  AttendanceId INT AUTO_INCREMENT PRIMARY KEY,
  EventId INT NOT NULL,
  UserId INT NOT NULL,
  Status ENUM('registered','confirmed','attended','cancelled') DEFAULT 'registered',
  RegisteredAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  ConfirmedAt DATETIME,
  
  FOREIGN KEY (EventId) REFERENCES Events(EventId) ON DELETE CASCADE,
  FOREIGN KEY (UserId) REFERENCES Users(UserId) ON DELETE CASCADE,
  
  UNIQUE KEY unique_event_user (EventId, UserId),
  INDEX idx_attendees_event (EventId),
  INDEX idx_attendees_user (UserId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- UserComments Table
-- ============================================
CREATE TABLE UserComments (
  CommentId INT AUTO_INCREMENT PRIMARY KEY,
  PostId INT NOT NULL,
  UserId INT NOT NULL,
  ParentCommentId INT,
  CommentText TEXT NOT NULL,
  LikeCount INT DEFAULT 0 CHECK (LikeCount >= 0),
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (PostId) REFERENCES Posts(PostId) ON DELETE CASCADE,
  FOREIGN KEY (UserId) REFERENCES Users(UserId) ON DELETE CASCADE,
  FOREIGN KEY (ParentCommentId) REFERENCES UserComments(CommentId) ON DELETE CASCADE,
  
  INDEX idx_comments_post (PostId),
  INDEX idx_comments_user (UserId),
  INDEX idx_comments_parent (ParentCommentId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PostLikes Table
-- ============================================
CREATE TABLE PostLikes (
  LikeId INT AUTO_INCREMENT PRIMARY KEY,
  PostId INT NOT NULL,
  UserId INT NOT NULL,
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (PostId) REFERENCES Posts(PostId) ON DELETE CASCADE,
  FOREIGN KEY (UserId) REFERENCES Users(UserId) ON DELETE CASCADE,
  
  UNIQUE KEY unique_post_like (PostId, UserId),
  INDEX idx_likes_post (PostId),
  INDEX idx_likes_user (UserId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Skills Table
-- ============================================
CREATE TABLE Skills (
  SkillId INT AUTO_INCREMENT PRIMARY KEY,
  SkillName VARCHAR(100) UNIQUE NOT NULL,
  CategoryId INT NOT NULL,
  SkillDescription TEXT,
  
  FOREIGN KEY (CategoryId) REFERENCES Category(CategoryId) ON DELETE CASCADE,
  INDEX idx_skills_category (CategoryId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- UserSkills Table
-- ============================================
CREATE TABLE UserSkills (
  UserSkillId INT AUTO_INCREMENT PRIMARY KEY,
  UserId INT NOT NULL,
  SkillId INT NOT NULL,
  SkillType ENUM('teach','learn') NOT NULL,
  ProficiencyLevel ENUM('beginner','intermediate','advanced','expert') NOT NULL,
  
  FOREIGN KEY (UserId) REFERENCES Users(UserId) ON DELETE CASCADE,
  FOREIGN KEY (SkillId) REFERENCES Skills(SkillId) ON DELETE CASCADE,
  
  UNIQUE KEY unique_user_skill (UserId, SkillId, SkillType),
  INDEX idx_userskills_user (UserId),
  INDEX idx_userskills_skill (SkillId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- EventSkills Table
-- ============================================
CREATE TABLE EventSkills (
  EventSkillId INT AUTO_INCREMENT PRIMARY KEY,
  EventId INT NOT NULL,
  SkillId INT NOT NULL,
  IsRequired ENUM('yes','no') DEFAULT 'no',
  
  FOREIGN KEY (EventId) REFERENCES Events(EventId) ON DELETE CASCADE,
  FOREIGN KEY (SkillId) REFERENCES Skills(SkillId) ON DELETE CASCADE,
  
  UNIQUE KEY unique_event_skill (EventId, SkillId),
  INDEX idx_eventskills_event (EventId),
  INDEX idx_eventskills_skill (SkillId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PostSkills Table
-- ============================================
CREATE TABLE PostSkills (
  PostSkillId INT AUTO_INCREMENT PRIMARY KEY,
  PostId INT NOT NULL,
  SkillId INT NOT NULL,
  
  FOREIGN KEY (PostId) REFERENCES Posts(PostId) ON DELETE CASCADE,
  FOREIGN KEY (SkillId) REFERENCES Skills(SkillId) ON DELETE CASCADE,
  
  UNIQUE KEY unique_post_skill (PostId, SkillId),
  INDEX idx_postskills_post (PostId),
  INDEX idx_postskills_skill (SkillId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Exchanges Table
-- ============================================
CREATE TABLE Exchanges (
  ExchangeId INT AUTO_INCREMENT PRIMARY KEY,
  PostId INT NOT NULL,
  OfferedByUserId INT NOT NULL,
  RequestedByUserId INT NOT NULL,
  Status ENUM('pending','accepted','rejected','completed','cancelled') DEFAULT 'pending',
  ProposedDate DATETIME NOT NULL,
  ConfirmedDate DATETIME,
  CompletedDate DATETIME,
  MeetingLink VARCHAR(255),
  MeetingLocation VARCHAR(150),
  CreditsCost INT DEFAULT 0 CHECK (CreditsCost >= 0),
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (PostId) REFERENCES Posts(PostId) ON DELETE CASCADE,
  FOREIGN KEY (OfferedByUserId) REFERENCES Users(UserId) ON DELETE CASCADE,
  FOREIGN KEY (RequestedByUserId) REFERENCES Users(UserId) ON DELETE CASCADE,
  
  INDEX idx_exchanges_status (Status),
  INDEX idx_exchanges_offered_by (OfferedByUserId),
  INDEX idx_exchanges_requested_by (RequestedByUserId),
  INDEX idx_exchanges_post (PostId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Rating Table
-- ============================================
CREATE TABLE Rating (
  ReviewId INT AUTO_INCREMENT PRIMARY KEY,
  ExchangeId INT NOT NULL,
  ReviewerId INT NOT NULL,
  ReviewedUserId INT NOT NULL,
  Rating INT NOT NULL CHECK (Rating >= 1 AND Rating <= 5),
  ReviewText TEXT,
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  UpdatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (ExchangeId) REFERENCES Exchanges(ExchangeId) ON DELETE CASCADE,
  FOREIGN KEY (ReviewerId) REFERENCES Users(UserId) ON DELETE CASCADE,
  FOREIGN KEY (ReviewedUserId) REFERENCES Users(UserId) ON DELETE CASCADE,
  
  UNIQUE KEY unique_exchange_reviewer (ExchangeId, ReviewerId),
  INDEX idx_rating_exchange (ExchangeId),
  INDEX idx_rating_reviewer (ReviewerId),
  INDEX idx_rating_reviewed (ReviewedUserId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Reports Table
-- ============================================
CREATE TABLE Reports (
  ReportId INT AUTO_INCREMENT PRIMARY KEY,
  ReporterId INT NOT NULL,
  ReportedUserId INT,
  ReportedEntityType ENUM('user','post','comment','event') NOT NULL,
  ReportedEntityId INT NOT NULL,
  ReasonCategory ENUM('spam','harassment','inappropriate','scam','other') NOT NULL,
  ReasonDescription TEXT NOT NULL,
  Status ENUM('pending','reviewing','resolved','dismissed') DEFAULT 'pending',
  ResolvedBy INT,
  ResolvedAt DATETIME,
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (ReporterId) REFERENCES Users(UserId) ON DELETE CASCADE,
  FOREIGN KEY (ReportedUserId) REFERENCES Users(UserId) ON DELETE SET NULL,
  FOREIGN KEY (ResolvedBy) REFERENCES Users(UserId) ON DELETE SET NULL,
  
  INDEX idx_reports_reporter (ReporterId),
  INDEX idx_reports_reported_user (ReportedUserId),
  INDEX idx_reports_status (Status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CreditTransactions Table
-- ============================================
CREATE TABLE CreditTransactions (
  TransactionId INT AUTO_INCREMENT PRIMARY KEY,
  UserId INT NOT NULL,
  TransactionType ENUM('earned','spent') NOT NULL,
  Amount INT NOT NULL CHECK (Amount > 0),
  BalanceAfter INT NOT NULL CHECK (BalanceAfter >= 0),
  RelatedEntityType ENUM('exchange','event','bonus','refund'),
  RelatedEntityId INT,
  Description VARCHAR(255),
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (UserId) REFERENCES Users(UserId) ON DELETE CASCADE,
  
  INDEX idx_credits_user_type (UserId, TransactionType),
  INDEX idx_credits_created (CreatedAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TRIGGERS for Validation
-- ============================================
DELIMITER $$

CREATE TRIGGER trg_validate_notification_section
BEFORE INSERT ON UserNotifications
FOR EACH ROW
BEGIN
  IF (NEW.NotificationSection = 'Reviews' AND NEW.NotificationType NOT IN ('like', 'comment', 'rating')) OR
     (NEW.NotificationSection = 'Exchange' AND NEW.NotificationType NOT IN ('booking', 'accepted', 'completing', 'being_refused')) OR
     (NEW.NotificationSection = 'events' AND NEW.NotificationType NOT IN ('acceptedInEvent', 'RejectedFromEvent')) OR
     (NEW.NotificationSection = 'credits' AND NEW.NotificationType NOT IN ('earned', 'spent'))
  THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'NotificationSection must match NotificationType';
  END IF;
END$$

CREATE TRIGGER trg_validate_notification_section_update
BEFORE UPDATE ON UserNotifications
FOR EACH ROW
BEGIN
  IF (NEW.NotificationSection = 'Reviews' AND NEW.NotificationType NOT IN ('like', 'comment', 'rating')) OR
     (NEW.NotificationSection = 'Exchange' AND NEW.NotificationType NOT IN ('booking', 'accepted', 'completing', 'being_refused')) OR
     (NEW.NotificationSection = 'events' AND NEW.NotificationType NOT IN ('acceptedInEvent', 'RejectedFromEvent')) OR
     (NEW.NotificationSection = 'credits' AND NEW.NotificationType NOT IN ('earned', 'spent'))
  THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'NotificationSection must match NotificationType';
  END IF;
END$$

CREATE TRIGGER trg_validate_user_age
BEFORE INSERT ON Users
FOR EACH ROW
BEGIN
  IF NEW.BirthDate IS NOT NULL AND NEW.BirthDate > DATE_SUB(CURDATE(), INTERVAL 13 YEAR) THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'User must be at least 13 years old';
  END IF;
END$$

CREATE TRIGGER trg_validate_user_age_update
BEFORE UPDATE ON Users
FOR EACH ROW
BEGIN
  IF NEW.BirthDate IS NOT NULL AND NEW.BirthDate > DATE_SUB(CURDATE(), INTERVAL 13 YEAR) THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'User must be at least 13 years old';
  END IF;
END$$

CREATE TRIGGER trg_validate_event_dates
BEFORE INSERT ON Events
FOR EACH ROW
BEGIN
  IF NEW.EventEndDate <= NEW.EventStartDate THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'EventEndDate must be after EventStartDate (format: YYYY-MM-DD HH:MM:SS)';
  END IF;
  IF NEW.EventStartDate < NEW.CreatedAt THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'EventStartDate cannot be in the past';
  END IF;
  IF NEW.EventStartDate < NOW() THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Event cannot be scheduled in the past';
  END IF;
  IF NEW.CurrentAttendeesNumber > NEW.MaxAttendees THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'CurrentAttendeesNumber cannot exceed MaxAttendees';
  END IF;
  IF DATEDIFF(NEW.EventEndDate, NEW.EventStartDate) > 30 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Event duration cannot exceed 30 days';
  END IF;
END$$

CREATE TRIGGER trg_validate_event_dates_update
BEFORE UPDATE ON Events
FOR EACH ROW
BEGIN
  IF NEW.EventEndDate <= NEW.EventStartDate THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'EventEndDate must be after EventStartDate (format: YYYY-MM-DD HH:MM:SS)';
  END IF;
  IF NEW.CurrentAttendeesNumber > NEW.MaxAttendees THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'CurrentAttendeesNumber cannot exceed MaxAttendees';
  END IF;
  IF DATEDIFF(NEW.EventEndDate, NEW.EventStartDate) > 30 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Event duration cannot exceed 30 days';
  END IF;
END$$

CREATE TRIGGER trg_validate_exchange_users
BEFORE INSERT ON Exchanges
FOR EACH ROW
BEGIN
  IF NEW.OfferedByUserId = NEW.RequestedByUserId THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Cannot exchange with yourself';
  END IF;
  IF NEW.ProposedDate < NOW() THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Proposed date cannot be in the past';
  END IF;
END$$

CREATE TRIGGER trg_validate_exchange_dates_insert
BEFORE INSERT ON Exchanges
FOR EACH ROW
BEGIN
  IF NEW.ConfirmedDate IS NOT NULL AND NEW.ConfirmedDate < NEW.ProposedDate THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Confirmed date must be after or equal to proposed date';
  END IF;
  IF NEW.CompletedDate IS NOT NULL AND NEW.ConfirmedDate IS NOT NULL AND NEW.CompletedDate <= NEW.ConfirmedDate THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Completed date must be greater than confirmed date (format: YYYY-MM-DD HH:MM:SS)';
  END IF;
  IF NEW.CompletedDate IS NOT NULL AND NEW.ProposedDate IS NOT NULL AND NEW.CompletedDate < NEW.ProposedDate THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Completed date must be after proposed date';
  END IF;
END$$

CREATE TRIGGER trg_validate_exchange_dates_update
BEFORE UPDATE ON Exchanges
FOR EACH ROW
BEGIN
  IF NEW.ConfirmedDate IS NOT NULL AND NEW.ConfirmedDate < NEW.ProposedDate THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Confirmed date must be after or equal to proposed date';
  END IF;
  IF NEW.CompletedDate IS NOT NULL AND NEW.ConfirmedDate IS NOT NULL AND NEW.CompletedDate <= NEW.ConfirmedDate THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Completed date must be greater than confirmed date (format: YYYY-MM-DD HH:MM:SS)';
  END IF;
  IF NEW.CompletedDate IS NOT NULL AND NEW.ProposedDate IS NOT NULL AND NEW.CompletedDate < NEW.ProposedDate THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Completed date must be after proposed date';
  END IF;
END$$

CREATE TRIGGER trg_validate_post_location
BEFORE INSERT ON Posts
FOR EACH ROW
BEGIN
  IF NEW.PostType = 'in-person' AND (NEW.MeetLocation IS NULL OR NEW.MeetLocation = '') THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'In-person posts must have a MeetLocation';
  END IF;
  IF NEW.PaymentMethod = 'credit' AND NEW.RequiredCredits <= 0 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Credit payment method requires RequiredCredits > 0';
  END IF;
  IF NEW.AvailableDate IS NOT NULL AND NEW.AvailableDate < NOW() THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Available date cannot be in the past (format: YYYY-MM-DD HH:MM:SS)';
  END IF;
END$$

CREATE TRIGGER trg_validate_post_location_update
BEFORE UPDATE ON Posts
FOR EACH ROW
BEGIN
  IF NEW.PostType = 'in-person' AND (NEW.MeetLocation IS NULL OR NEW.MeetLocation = '') THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'In-person posts must have a MeetLocation';
  END IF;
  IF NEW.PaymentMethod = 'credit' AND NEW.RequiredCredits <= 0 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Credit payment method requires RequiredCredits > 0';
  END IF;
  IF NEW.AvailableDate IS NOT NULL AND NEW.AvailableDate < NOW() THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Available date cannot be in the past (format: YYYY-MM-DD HH:MM:SS)';
  END IF;
END$$

CREATE TRIGGER trg_validate_attendee_dates
BEFORE INSERT ON EventsAttendees
FOR EACH ROW
BEGIN
  IF NEW.ConfirmedAt IS NOT NULL AND NEW.ConfirmedAt < NEW.RegisteredAt THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Confirmed date must be after or equal to registered date (format: YYYY-MM-DD HH:MM:SS)';
  END IF;
END$$

CREATE TRIGGER trg_validate_attendee_dates_update
BEFORE UPDATE ON EventsAttendees
FOR EACH ROW
BEGIN
  IF NEW.ConfirmedAt IS NOT NULL AND NEW.ConfirmedAt < NEW.RegisteredAt THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Confirmed date must be after or equal to registered date (format: YYYY-MM-DD HH:MM:SS)';
  END IF;
END$$

-- ============================================
-- STORED FUNCTION FOR ALGERIAN DATE FORMATTING
-- ============================================
-- Format: Year-Month-Day Hour:Minute (e.g., 2025-12-15 14:30)

CREATE FUNCTION FormatDateAlgerian(dateInput DATETIME) 
RETURNS VARCHAR(20) 
DETERMINISTIC
READS SQL DATA
BEGIN
  IF dateInput IS NULL THEN
    RETURN NULL;
  END IF;
  RETURN DATE_FORMAT(dateInput, '%Y-%m-%d %H:%i');
END$$

-- Alternative format with day name: Example "Monday 15-12-2025 14:30"
CREATE FUNCTION FormatDateAlgerianWithDay(dateInput DATETIME) 
RETURNS VARCHAR(35) 
DETERMINISTIC
READS SQL DATA
BEGIN
  IF dateInput IS NULL THEN
    RETURN NULL;
  END IF;
  RETURN CONCAT(
    DATE_FORMAT(dateInput, '%W %d-%m-%Y %H:%i')
  );
END$$

DELIMITER ;

-- ============================================
-- SAMPLE DATA INSERTION
-- ============================================
-- Date Format in Database: YYYY-MM-DD HH:MM:SS (MySQL Standard)
-- Display Format in Application: YYYY-MM-DD HH:MM (Algerian Format)
-- Use FormatDateAlgerian() function when displaying dates to users

-- Insert Users (10 users with Algerian phone/location formats)
INSERT INTO Users (UserName, FullName, Email, Password, Description, ProfessionalTitle, Location, PhoneNumber, BirthDate, Gender, CreditBalance) VALUES
('youssef_dev', 'Youssef Benhadj', 'youssef@outlook.dz', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Experienced web developer passionate about teaching and mentoring', 'Senior Developer', 'Algiers, Hydra', '+213 05 12 345 678', '1990-05-15', 'M', 250),
('fatima_design', 'Fatima Debbache', 'fatima@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Creative art director with 8 years of experience in digital design', 'UI/UX Designer', 'Oran, Downtown', '+213 06 23 456 789', '1988-08-22', 'F', 300),
('ahmed_photo', 'Ahmed Medjahed', 'ahmed.photo@outlook.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Professional photographer and certified trainer in visual media', 'Photographer', 'Constantine, Belkaid', '+213 07 34 567 890', '1985-03-10', 'M', 180),
('leila_writer', 'Leila Saidane', 'leila.writer@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Content writer and expert in copywriting and SEO optimization', 'Content Writer', 'Annaba, Sidi Salem', '+213 05 45 678 901', '1992-11-30', 'F', 220),
('karim_music', 'Karim Bouchikhi', 'karim.music@outlook.dz', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Music teacher specialized in guitar instruction for all levels', 'Music Instructor', 'Tlemcen', '+213 06 56 789 012', '1987-07-18', 'M', 190),
('amina_chef', 'Amina Hadj-Aissa', 'amina.chef@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Professional chef trained in culinary arts and traditional cooking', 'Chef', 'Blida, Downtown', '+213 07 67 890 123', '1991-04-25', 'F', 280),
('ali_fitness', 'Ali Bouchta', 'ali.fitness@outlook.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Certified personal trainer and nutritionist with 12 years experience', 'Fitness Trainer', 'Setif, Haouchias', '+213 05 78 901 234', '1989-09-12', 'M', 210),
('samira_language', 'Samira Kebaili', 'samira.lang@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Polyglot fluent in 6 languages with international teaching credentials', 'Language Teacher', 'Medea', '+213 06 89 012 345', '1993-06-08', 'F', 260),
('moussa_mechanic', 'Moussa Aidel', 'moussa.mechanic@outlook.dz', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Auto mechanic with 15 years experience in vehicle diagnostics', 'Mechanic', 'Tipaza, Chenoua', '+213 07 90 123 456', '1982-11-20', 'M', 230),
('zainab_garden', 'Zainab Benkhalifa', 'zainab.garden@gmail.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Master horticulturist and expert in sustainable landscaping design', 'Horticulturist', 'Boumerdes, Baya', '+213 05 01 234 567', '1986-02-14', 'F', 270);

-- Insert 16 Categories
INSERT INTO Category (CategoryName, CategoryDescription) VALUES
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

-- Insert Skills (10+ per category = 160+ total skills)
INSERT INTO Skills (SkillName, CategoryId, SkillDescription) VALUES
-- Technology & Programming (CategoryId: 1)
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

-- Design & Creative (CategoryId: 2)
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

-- Photography & Video (CategoryId: 3)
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

-- Writing & Content (CategoryId: 4)
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

-- Music & Performance (CategoryId: 5)
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

-- Cooking & Culinary (CategoryId: 6)
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

-- Health & Fitness (CategoryId: 7)
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

-- Languages (CategoryId: 8)
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

-- Automotive & Mechanics (CategoryId: 9)
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

-- Gardening & Nature (CategoryId: 10)
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

-- Business & Finance (CategoryId: 11)
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

-- Arts & Crafts (CategoryId: 12)
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

-- Home Improvement (CategoryId: 13)
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

-- Cleaning & Organization (CategoryId: 14)
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

-- Science & Education (CategoryId: 15)
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

-- Fashion & Beauty (CategoryId: 16)
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
-- Insert UserSkills (Linking users to skills)
-- ============================================
INSERT INTO UserSkills (UserId, SkillId, SkillType, ProficiencyLevel) VALUES
(1, 1, 'teach', 'expert'),
(1, 2, 'teach', 'advanced'),
(1, 7, 'teach', 'expert'),
(2, 13, 'teach', 'expert'),
(2, 14, 'teach', 'advanced'),
(2, 20, 'teach', 'advanced'),
(3, 25, 'teach', 'expert'),
(3, 26, 'teach', 'advanced'),
(3, 28, 'teach', 'advanced'),
(4, 37, 'teach', 'expert'),
(4, 38, 'teach', 'advanced'),
(4, 40, 'teach', 'intermediate'),
(5, 49, 'teach', 'expert'),
(5, 51, 'teach', 'advanced'),
(5, 57, 'teach', 'intermediate'),
(6, 61, 'teach', 'expert'),
(6, 62, 'teach', 'advanced'),
(6, 63, 'teach', 'intermediate'),
(7, 71, 'teach', 'expert'),
(7, 73, 'teach', 'advanced'),
(8, 85, 'teach', 'expert'),
(8, 86, 'teach', 'expert'),
(9, 93, 'teach', 'expert'),
(9, 94, 'teach', 'advanced'),
(10, 110, 'teach', 'expert'),
(10, 111, 'teach', 'advanced');

-- ============================================
-- Insert Events (10 events)
-- ============================================
INSERT INTO Events (OrganizerId, EventTitle, EventDescription, EventLocation, EventType, EventStartDate, EventEndDate, MaxAttendees, CurrentAttendeesNumber, EventCost, EventStatus) VALUES
(1, 'Web Development Workshop', 'Practical workshop on React and modern JavaScript. Complete training from beginner to intermediate level.', 'Algiers, Kouba - Room 101', 'in-person', '2025-12-15 09:00:00', '2025-12-15 16:00:00', 20, 12, 50, 'upcoming'),
(2, 'UI/UX Design Masterclass', 'Intensive session on Figma and modern design. Practical exercises and direct feedback.', 'Oran, Downtown - Design Studio', 'in-person', '2025-12-20 10:00:00', '2025-12-20 14:00:00', 15, 8, 60, 'upcoming'),
(3, 'Product Photography', 'Professional photography techniques for e-commerce. Including lighting and post-production.', 'Constantine, Online', 'online', '2025-12-22 18:00:00', '2025-12-22 20:00:00', 25, 10, 30, 'upcoming'),
(4, 'Algerian Literary Week', 'Conference on creative writing and copywriting. Discussion with local authors.', 'Annaba, Library - Main Hall', 'in-person', '2026-01-05 14:00:00', '2026-01-05 18:00:00', 50, 22, 0, 'upcoming'),
(5, 'Intensive Guitar Course', 'Intensive training on acoustic and electric guitar. All levels welcome.', 'Tlemcen, Music Academy', 'in-person', '2026-01-10 11:00:00', '2026-01-10 15:00:00', 12, 6, 40, 'upcoming'),
(6, 'Algerian Cooking Workshop', 'Preparation of traditional Algerian dishes. Couscous, tajines and pastries.', 'Blida, Pro Kitchen - Kitchen 1', 'in-person', '2026-01-15 17:00:00', '2026-01-15 20:00:00', 18, 14, 35, 'upcoming'),
(7, 'Fitness & Nutrition Seminar', 'Conference on personal training and sports nutrition. Certification included.', 'Setif, Online', 'online', '2026-01-18 19:00:00', '2026-01-18 21:00:00', 30, 18, 25, 'upcoming'),
(8, 'Applied Languages Training', 'Intensive English and French course for professionals. Professional vocabulary included.', 'Medea, Training Center', 'in-person', '2026-01-25 09:00:00', '2026-01-25 17:00:00', 20, 9, 45, 'upcoming'),
(9, 'Auto Mechanics - Diagnostics', 'OBD diagnostics and automotive electrical systems training. Practical on vehicle.', 'Tipaza, Pro Garage', 'in-person', '2026-02-01 08:00:00', '2026-02-01 16:00:00', 10, 5, 80, 'upcoming'),
(10, 'Sustainable & Organic Gardening', 'Permaculture and ecological gardening techniques. Creating productive vegetable gardens.', 'Boumerdes, Green Center', 'in-person', '2026-02-08 10:00:00', '2026-02-08 13:00:00', 25, 11, 20, 'upcoming');

-- ============================================
-- Insert Posts (10 service posts)
-- ============================================
INSERT INTO Posts (UserId, CategoryId, Title, Description, PostType, MeetLocation, AvailableDate, Duration, PaymentMethod, RequiredCredits, PostStatus, LikeCount) VALUES
(1, 1, 'Private JavaScript Lessons', 'Private lessons in JavaScript for beginners to intermediate. Fast learning guaranteed.', 'in-person', 'Algiers, Hydra - Cultural Center', '2025-12-20 10:00:00', 120, 'credit', 50, 'active', 8),
(2, 2, 'Custom Graphic Design', 'Creation of custom designs for your brand. Logos, brochures, and marketing materials.', 'online', NULL, '2025-12-18 14:30:00', 180, 'exchange', 0, 'active', 12),
(3, 3, 'Professional Photo Session', 'Professional photography for portraits, products, and events. Retouching included.', 'in-person', 'Constantine, Belkaid - Photo Studio', '2025-12-25 09:00:00', 90, 'credit', 60, 'active', 15),
(4, 4, 'Blog Content Writing', 'Writing SEO-optimized articles for your blog. Engaging and relevant content.', 'online', NULL, '2025-12-22 11:00:00', 240, 'exchange', 0, 'active', 6),
(5, 5, 'Guitar Lessons All Levels', 'Teaching acoustic and electric guitar. Structured and progressive method.', 'in-person', 'Tlemcen - Music Studio', '2026-01-01 15:00:00', 60, 'credit', 45, 'active', 10),
(6, 6, 'Algerian Cooking Coaching', 'Traditional Algerian cuisine training. Learning authentic recipes.', 'in-person', 'Blida, Downtown - Kitchen', '2026-01-05 18:00:00', 150, 'credit', 55, 'active', 9),
(7, 7, 'Personal Fitness Coaching', 'Personal training adapted to your goals. With nutrition and regular follow-up.', 'in-person', 'Setif, Haouchias - ProFit Gym', '2025-12-30 06:00:00', 120, 'exchange', 0, 'active', 14),
(8, 8, 'Intensive English Tutoring', 'Intensive English conversation and grammar course. Rapid improvement guaranteed.', 'online', NULL, '2025-12-27 13:00:00', 90, 'credit', 40, 'active', 7),
(9, 9, 'Auto Mechanics Diagnostics', 'Complete vehicle diagnostics with detailed report. Repair quote included.', 'in-person', 'Tipaza, Chenoua - Garage', '2026-01-08 08:30:00', 60, 'exchange', 0, 'active', 5),
(10, 10, 'Organic Gardening Consultation', 'Personalized advice for ecological garden. Design and landscape arrangement.', 'in-person', 'Boumerdes, Baya - Green Center', '2026-01-12 10:00:00', 180, 'credit', 50, 'active', 11);

-- ============================================
-- Insert EventsAttendees (Event attendance)
-- ============================================
INSERT INTO EventsAttendees (EventId, UserId, Status, RegisteredAt, ConfirmedAt) VALUES
(1, 2, 'confirmed', '2025-12-10 10:00:00', '2025-12-11 14:30:00'),
(1, 3, 'registered', '2025-12-11 09:15:00', NULL),
(1, 4, 'confirmed', '2025-12-09 16:45:00', '2025-12-10 11:20:00'),
(2, 5, 'confirmed', '2025-12-12 13:30:00', '2025-12-13 08:00:00'),
(2, 6, 'registered', '2025-12-13 10:00:00', NULL),
(3, 7, 'confirmed', '2025-12-14 14:20:00', '2025-12-15 09:30:00'),
(3, 8, 'confirmed', '2025-12-13 11:45:00', '2025-12-14 15:00:00'),
(4, 9, 'registered', '2025-12-30 16:15:00', NULL),
(5, 10, 'confirmed', '2026-01-02 12:00:00', '2026-01-03 10:30:00'),
(6, 1, 'registered', '2026-01-10 09:00:00', NULL);

-- ============================================
-- Insert Exchanges (10 skill exchanges)
-- ============================================
INSERT INTO Exchanges (PostId, OfferedByUserId, RequestedByUserId, Status, ProposedDate, ConfirmedDate, CompletedDate, MeetingLocation, CreditsCost) VALUES
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
-- Insert Ratings (10 reviews from exchanges)
-- ============================================
INSERT INTO Rating (ExchangeId, ReviewerId, ReviewedUserId, Rating, ReviewText) VALUES
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
-- Insert UserComments (10 comments on posts)
-- ============================================
INSERT INTO UserComments (PostId, UserId, CommentText, LikeCount) VALUES
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
-- Insert UserNotifications (10 notifications)
-- ============================================
INSERT INTO UserNotifications (UserId, NotificationSection, NotificationType, Title, Message, IsRead, CreatedAt) VALUES
(1, 'Exchange', 'accepted', 'Exchange Accepted', 'Your exchange request has been accepted by Fatima Debbache', 'no', '2025-12-20 10:30:00'),
(2, 'Reviews', 'rating', 'New Rating Received', 'Youssef Benhadj left you a 5-star rating: Excellent teacher!', 'no', '2025-12-16 14:45:00'),
(3, 'events', 'acceptedInEvent', 'Event Acceptance', 'You have been accepted to the event UI/UX Design Masterclass', 'yes', '2025-12-13 11:20:00'),
(4, 'credits', 'earned', 'Credits Earned', 'You earned 50 credits for the completed exchange with Ahmed', 'yes', '2025-12-20 18:15:00'),
(5, 'Exchange', 'booking', 'Exchange Request', 'Amina Hadj-Aissa is requesting to exchange for your guitar course', 'no', '2026-01-02 09:30:00'),
(6, 'Reviews', 'comment', 'New Comment Posted', 'Ali Bouchta commented on your post: Perfect cuisine!', 'yes', '2026-01-05 16:45:00'),
(7, 'events', 'RejectedFromEvent', 'Event Rejection', 'Your application to the Fitness Workshop event was declined', 'yes', '2025-12-28 13:20:00'),
(8, 'credits', 'spent', 'Credits Spent', 'You spent 40 credits for intensive English tutoring', 'yes', '2025-12-27 20:00:00'),
(9, 'Exchange', 'completing', 'Exchange Completion', 'Zainab Benkhalifa marked your exchange as completed', 'no', '2026-01-09 17:30:00'),
(10, 'Reviews', 'like', 'Comment Liked', 'Your comment on the Gardening post received 5 likes!', 'no', '2026-01-13 12:45:00');

-- ============================================
-- Insert CreditTransactions (10 transactions)
-- ============================================
INSERT INTO CreditTransactions (UserId, TransactionType, Amount, BalanceAfter, RelatedEntityType, RelatedEntityId, Description) VALUES
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
-- Insert PostSkills (Linking posts to skills)
-- ============================================
INSERT INTO PostSkills (PostId, SkillId) VALUES
(1, 1),
(1, 2),
(1, 7),
(2, 13),
(2, 15),
(2, 20),
(3, 25),
(3, 26),
(3, 28),
(4, 37),
(4, 38),
(4, 40),
(5, 49),
(5, 51),
(5, 57),
(6, 61),
(6, 62),
(6, 63),
(7, 71),
(7, 73),
(8, 85),
(8, 86),
(9, 93),
(9, 94),
(10, 110),
(10, 111);

-- ============================================
-- Insert EventSkills (Linking events to skills)
-- ============================================
INSERT INTO EventSkills (EventId, SkillId, IsRequired) VALUES
(1, 1, 'no'),
(1, 2, 'no'),
(1, 7, 'yes'),
(2, 13, 'yes'),
(2, 15, 'yes'),
(3, 25, 'no'),
(3, 26, 'yes'),
(4, 37, 'no'),
(4, 38, 'no'),
(5, 49, 'yes'),
(5, 51, 'no'),
(6, 61, 'yes'),
(6, 62, 'yes'),
(7, 71, 'yes'),
(7, 73, 'no'),
(8, 85, 'yes'),
(8, 86, 'yes'),
(9, 93, 'yes'),
(9, 94, 'no'),
(10, 110, 'yes'),
(10, 111, 'no');