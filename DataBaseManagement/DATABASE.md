# 🎓 Skill Service Exchange Database - Complete Documentation

**Status:** ✅ **PRODUCTION READY**  
**Last Updated:** December 11, 2025  
**Database Engine:** MySQL/InnoDB  
**Character Set:** UTF8MB4 Unicode

---

## 📑 Quick Navigation

- [Database Overview](#database-overview)
- [Quick Start (5 Minutes)](#quick-start-5-minutes)
- [Table Structures](#table-structures)
- [Date Validation System](#date-validation-system)
- [Algerian Date Formatting](#algerian-date-formatting)
- [Sample Data](#sample-data)
- [Common Tasks](#common-tasks)
- [Error Handling](#error-handling)
- [Testing Guide](#testing-guide)

---

## 🎯 Database Overview

### Purpose
The Skill Service Exchange database powers a peer-to-peer skill and service exchange platform where users can:
- Post skills/services they offer
- Request skills/services from others
- Attend community events
- Exchange skills or exchange skills for credits
- Rate and review exchanges
- Manage user profiles and notifications

### Key Features
✅ **User Management** - 10 users with Algerian phone numbers and locations  
✅ **Service Posts** - 10 service posts across 16 categories  
✅ **Events** - 10 community events with attendee tracking  
✅ **Exchanges** - 10 skill exchanges with comprehensive date validation  
✅ **Ratings & Reviews** - Review system for completed exchanges  
✅ **Credit System** - Credit transactions and balance tracking  
✅ **Notifications** - Real-time notification system  
✅ **Date Validation** - Database-level validation for all dates  
✅ **Algerian Formatting** - Dates display in Algerian format (YYYY-MM-DD HH:MM)

---

## ⚡ Quick Start (5 Minutes)

### Step 1: Import Database
```bash
# Method 1: phpMyAdmin
1. Go to Import tab
2. Select swapdb.sql file
3. Click Import

# Method 2: MySQL Command Line
mysql -u username -p password < swapdb.sql

# Method 3: PHP Script
$mysqli = new mysqli("localhost", "user", "pass", "database_name");
$mysqli->multi_query(file_get_contents('swapdb.sql'));
```

### Step 2: Verify Installation
```sql
-- Check if all tables exist
SHOW TABLES;
-- Should return: 16 tables

-- Test formatting functions
SELECT FormatDateAlgerian(NOW());
-- Output: 2025-12-11 14:30

SELECT FormatDateAlgerianWithDay(NOW());
-- Output: Thursday 11-12-2025 14:30

-- Count sample data
SELECT COUNT(*) FROM Users;           -- Should return 10
SELECT COUNT(*) FROM Posts;           -- Should return 10
SELECT COUNT(*) FROM Events;          -- Should return 10
SELECT COUNT(*) FROM Exchanges;       -- Should return 10
```

### Step 3: View Sample Data
```sql
-- Display all users
SELECT UserId, UserName, FullName, Location FROM Users;

-- Display all active posts
SELECT PostId, Title, Description, AvailableDate FROM Posts WHERE PostStatus = 'active';

-- Display upcoming events
SELECT EventId, EventTitle, EventStartDate, MaxAttendees FROM Events;

-- Display exchanges with formatted dates
SELECT 
  ExchangeId,
  FormatDateAlgerian(ProposedDate) as ProposedDate,
  FormatDateAlgerian(CompletedDate) as CompletedDate,
  Status
FROM Exchanges;
```

---

## 📊 Table Structures

### 1. Users Table
**Purpose:** Store user profiles and account information

```sql
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
  Rating DECIMAL(3,2) DEFAULT 0.00,           -- 0.00 to 5.00
  RatingCount INT DEFAULT 0,
  ExchangeCount INT DEFAULT 0,
  CreditBalance INT DEFAULT 0
)
```

**Key Fields:**
- `UserId` - Unique identifier
- `PhoneNumber` - Algerian format (+213 05/06/07 XXXXXXXX)
- `BirthDate` - Must be 13+ years old
- `Rating` - Average rating from exchanges (0.00 to 5.00)
- `CreditBalance` - Available credits for purchasing

**Sample Data:** 10 users with diverse skills and locations

---

### 2. Category Table
**Purpose:** Organize services into categories

```sql
CREATE TABLE Category (
  CategoryId INT AUTO_INCREMENT PRIMARY KEY,
  CategoryName VARCHAR(100) UNIQUE NOT NULL,
  CategoryDescription TEXT,
  PostCount INT DEFAULT 0
)
```

**Categories (16 Total):**
1. Technology & Programming
2. Design & Creative
3. Photography & Video
4. Writing & Content
5. Music & Performance
6. Cooking & Culinary
7. Health & Fitness
8. Languages
9. Automotive & Mechanics
10. Gardening & Nature
11. Business & Finance
12. Arts & Crafts
13. Home Improvement
14. Cleaning & Organization
15. Science & Education
16. Fashion & Beauty

---

### 3. Posts Table
**Purpose:** Service/skill posts offered by users

```sql
CREATE TABLE Posts (
  PostId INT AUTO_INCREMENT PRIMARY KEY,
  UserId INT NOT NULL,                        -- Post creator
  Title VARCHAR(150) NOT NULL,
  Description TEXT NOT NULL,
  PostType ENUM('in-person','online') NOT NULL,
  PostStatus ENUM('active','disabled') DEFAULT 'active',
  CategoryId INT NOT NULL,
  Duration INT,                               -- Minutes (1-480)
  MeetLocation VARCHAR(150),                  -- Required if in-person
  AvailableDate DATETIME NOT NULL,            -- Must be future date
  Prerequisites TEXT,
  Requirements TEXT,
  PaymentMethod ENUM('exchange','credit') NOT NULL,
  RequiredCredits INT DEFAULT 0,              -- Required if payment='credit'
  LikeCount INT DEFAULT 0,
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (UserId) REFERENCES Users(UserId),
  FOREIGN KEY (CategoryId) REFERENCES Category(CategoryId)
)
```

**Constraints:**
- ✅ `in-person` posts MUST have MeetLocation
- ✅ `credit` payment posts MUST have RequiredCredits > 0
- ✅ AvailableDate MUST be in future

**Sample Data:** 10 posts across all categories

---

### 4. Events Table
**Purpose:** Organize community events and workshops

```sql
CREATE TABLE Events (
  EventId INT AUTO_INCREMENT PRIMARY KEY,
  OrganizerId INT NOT NULL,
  EventTitle VARCHAR(150) NOT NULL,
  EventDescription TEXT NOT NULL,
  EventLocation VARCHAR(150),
  EventType ENUM('online','in-person') NOT NULL,
  EventStartDate DATETIME NOT NULL,
  EventEndDate DATETIME NOT NULL,
  MaxAttendees INT NOT NULL,                  -- 1-1000
  CurrentAttendeesNumber INT DEFAULT 0,
  EventCost INT DEFAULT 0,
  EventStatus ENUM('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  UpdatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (OrganizerId) REFERENCES Users(UserId)
)
```

**Constraints:**
- ✅ EventEndDate > EventStartDate (strictly greater)
- ✅ EventStartDate NOT in past
- ✅ Duration ≤ 30 days
- ✅ CurrentAttendeesNumber ≤ MaxAttendees

**Sample Data:** 10 events with various start/end dates

---

### 5. EventsAttendees Table
**Purpose:** Track event registration and attendance

```sql
CREATE TABLE EventsAttendees (
  AttendanceId INT AUTO_INCREMENT PRIMARY KEY,
  EventId INT NOT NULL,
  UserId INT NOT NULL,
  Status ENUM('registered','confirmed','attended','cancelled') DEFAULT 'registered',
  RegisteredAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  ConfirmedAt DATETIME,
  
  FOREIGN KEY (EventId) REFERENCES Events(EventId),
  FOREIGN KEY (UserId) REFERENCES Users(UserId),
  
  UNIQUE KEY unique_event_user (EventId, UserId)
)
```

**Constraints:**
- ✅ ConfirmedAt ≥ RegisteredAt (if ConfirmedAt is set)
- ✅ Each user can only register once per event

**Sample Data:** 10 attendee records

---

### 6. Exchanges Table ⭐ **KEY TABLE**
**Purpose:** Track skill exchanges between users

```sql
CREATE TABLE Exchanges (
  ExchangeId INT AUTO_INCREMENT PRIMARY KEY,
  PostId INT NOT NULL,
  OfferedByUserId INT NOT NULL,              -- Service provider
  RequestedByUserId INT NOT NULL,            -- Service receiver
  Status ENUM('pending','accepted','rejected','completed','cancelled') DEFAULT 'pending',
  ProposedDate DATETIME NOT NULL,            -- When exchange proposed
  ConfirmedDate DATETIME,                    -- When both agreed
  CompletedDate DATETIME,                    -- When exchange happened
  MeetingLink VARCHAR(255),                  -- For online exchanges
  MeetingLocation VARCHAR(150),              -- For in-person exchanges
  CreditsCost INT DEFAULT 0,
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (PostId) REFERENCES Posts(PostId),
  FOREIGN KEY (OfferedByUserId) REFERENCES Users(UserId),
  FOREIGN KEY (RequestedByUserId) REFERENCES Users(UserId)
)
```

**⭐ KEY VALIDATIONS:**
- ✅ **ProposedDate NOT in past** (must be >= NOW())
- ✅ **ProposedDate < ConfirmedDate** (if ConfirmedDate set)
- ✅ **CompletedDate > ConfirmedDate** (strictly greater, NOT equal!)
- ✅ OfferedByUserId ≠ RequestedByUserId (cannot exchange with self)

**Date Sequence (Must Follow):**
```
ProposedDate → ConfirmedDate → CompletedDate
   (future)    (after proposed) (after confirmed)
```

**Sample Data:** 10 exchanges with proper date sequences

---

### 7. Rating Table
**Purpose:** Store reviews from completed exchanges

```sql
CREATE TABLE Rating (
  ReviewId INT AUTO_INCREMENT PRIMARY KEY,
  ExchangeId INT NOT NULL,                   -- UNIQUE - only 1 rating per exchange
  ReviewerId INT NOT NULL,                   -- Who wrote review
  ReviewedUserId INT NOT NULL,               -- Who is being reviewed
  Rating INT NOT NULL,                       -- 1-5 stars
  ReviewText TEXT,
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  UpdatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (ExchangeId) REFERENCES Exchanges(ExchangeId),
  FOREIGN KEY (ReviewerId) REFERENCES Users(UserId),
  FOREIGN KEY (ReviewedUserId) REFERENCES Users(UserId),
  
  UNIQUE KEY unique_exchange_reviewer (ExchangeId, ReviewerId)
)
```

**Constraints:**
- ✅ Rating must be 1-5
- ✅ Only one rating per exchange per reviewer

**Sample Data:** 10 reviews from exchanges

---

### 8. Other Key Tables

**UserNotifications**
- User notifications for exchanges, events, ratings, credits
- Title (required, 5+ chars), Message, IsRead, NotificationSection, CreatedAt

**UserComments**
- Comments on service posts
- Links to Posts and Users

**PostLikes**
- Tracks likes on service posts
- Unique constraint: One like per user per post

**Skills**
- Available skills in the system (170+ skills across 16 categories)
- Each has CategoryId reference

**UserSkills**
- Links users to skills they teach/learn
- ProficiencyLevel: beginner, intermediate, advanced, expert

**CreditTransactions**
- Tracks credit earned/spent
- Used for audit and balance tracking

**Reports**
- User reporting system for inappropriate content

---

## 🕐 Date Validation System

### Complete Validation Rules

| Table | Field | Rule | Trigger |
|-------|-------|------|---------|
| **Users** | BirthDate | Age ≥ 13 years | INSERT/UPDATE |
| **Posts** | AvailableDate | NOT in past | INSERT/UPDATE |
| **Events** | EventStartDate | NOT in past | INSERT |
| **Events** | EventEndDate | > EventStartDate | INSERT/UPDATE |
| **Events** | Duration | ≤ 30 days | INSERT/UPDATE |
| **EventsAttendees** | ConfirmedAt | ≥ RegisteredAt | INSERT/UPDATE |
| **Exchanges** | ProposedDate | NOT in past ⭐ | INSERT |
| **Exchanges** | ConfirmedDate | ≥ ProposedDate | INSERT/UPDATE |
| **Exchanges** | CompletedDate | > ConfirmedDate ⭐ | INSERT/UPDATE |

### Validation Trigger Examples

#### Exchange Date Validation (Most Important)
```sql
CREATE TRIGGER trg_validate_exchange_dates_insert
BEFORE INSERT ON Exchanges
FOR EACH ROW
BEGIN
  -- ProposedDate cannot be in past
  IF NEW.ProposedDate < NOW() THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Proposed date cannot be in the past';
  END IF;
  
  -- Confirmed must be after proposed
  IF NEW.ConfirmedDate IS NOT NULL AND NEW.ConfirmedDate < NEW.ProposedDate THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Confirmed date must be after proposed date';
  END IF;
  
  -- Completed MUST be GREATER than confirmed
  IF NEW.CompletedDate IS NOT NULL AND NEW.ConfirmedDate IS NOT NULL 
     AND NEW.CompletedDate <= NEW.ConfirmedDate THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Completed date must be GREATER than confirmed date';
  END IF;
END$$
```

### All Validation Triggers Included
- `trg_validate_user_age` - Birth date age check
- `trg_validate_event_dates` - Event date sequence validation
- `trg_validate_event_dates_update` - Event update validation
- `trg_validate_exchange_users` - Proposed date check
- `trg_validate_exchange_dates_insert` - Complete exchange sequence
- `trg_validate_exchange_dates_update` - Update validation
- `trg_validate_post_location` - Location and payment validation
- `trg_validate_post_location_update` - Post update validation
- `trg_validate_attendee_dates` - Attendee date validation
- `trg_validate_attendee_dates_update` - Update validation
- `trg_validate_notification_section` - Notification type matching

---

## 🇩🇿 Algerian Date Formatting

### Two Built-in Functions

#### Function 1: FormatDateAlgerian()
**Format:** `YYYY-MM-DD HH:MM`  
**Example:** `2025-12-20 14:30`

```sql
SELECT FormatDateAlgerian(NOW());
-- Output: 2025-12-11 14:30

SELECT FormatDateAlgerian(ProposedDate) FROM Exchanges;
-- Output: 2025-12-12 10:00
```

#### Function 2: FormatDateAlgerianWithDay()
**Format:** `Day DD-MM-YYYY HH:MM`  
**Example:** `Saturday 20-12-2025 14:30`

```sql
SELECT FormatDateAlgerianWithDay(NOW());
-- Output: Thursday 11-12-2025 14:30

SELECT FormatDateAlgerianWithDay(CompletedDate) FROM Exchanges;
-- Output: Sunday 20-12-2025 18:30
```

### Usage in Queries

```sql
-- Display exchanges with formatted dates
SELECT 
  ExchangeId,
  FormatDateAlgerian(ProposedDate) as ProposedDate,
  FormatDateAlgerian(ConfirmedDate) as ConfirmedDate,
  FormatDateAlgerian(CompletedDate) as CompletedDate,
  Status
FROM Exchanges
ORDER BY CompletedDate DESC;

-- Display posts with availability
SELECT 
  PostId,
  Title,
  FormatDateAlgerianWithDay(AvailableDate) as AvailableDate,
  Duration,
  RequiredCredits
FROM Posts
WHERE PostStatus = 'active';

-- Display events with full date names
SELECT 
  EventId,
  EventTitle,
  FormatDateAlgerianWithDay(EventStartDate) as EventStartDate,
  FormatDateAlgerianWithDay(EventEndDate) as EventEndDate,
  EventStatus
FROM Events;
```

### Display in PHP Applications

```php
<?php
// Method 1: Use MySQL function (Recommended)
$query = "SELECT 
  ExchangeId,
  FormatDateAlgerian(ProposedDate) as ProposedDate,
  FormatDateAlgerian(CompletedDate) as CompletedDate
FROM Exchanges";
$result = mysqli_query($connection, $query);
while($row = mysqli_fetch_assoc($result)) {
  echo $row['ProposedDate'];  // Already formatted: 2025-12-12 10:00
}

// Method 2: Format in PHP
$query = "SELECT ProposedDate FROM Exchanges";
$result = mysqli_query($connection, $query);
while($row = mysqli_fetch_assoc($result)) {
  $formatted = date('Y-m-d H:i', strtotime($row['ProposedDate']));
  echo $formatted;  // 2025-12-12 10:00
}

// Method 3: Format with day name (PHP)
$formatted = date('l d-m-Y H:i', strtotime($dbDate));
echo $formatted;  // Thursday 11-12-2025 14:30
?>
```

### Timezone Support

```php
<?php
// Set timezone to Africa/Algiers
date_default_timezone_set('Africa/Algiers');

// Now all dates display in Algerian timezone
echo date('Y-m-d H:i');  // Current time in Algiers
?>
```

---

## 📋 Sample Data

### Users (10 Sample Users)
| UserId | UserName | FullName | Location | PhoneNumber |
|--------|----------|----------|----------|-------------|
| 1 | youssef_dev | Youssef Benhadj | Algiers, Hydra | +213 05 12 345 678 |
| 2 | fatima_design | Fatima Debbache | Oran, Downtown | +213 06 23 456 789 |
| 3 | ahmed_photo | Ahmed Medjahed | Constantine, Belkaid | +213 07 34 567 890 |
| 4 | leila_writer | Leila Saidane | Annaba, Sidi Salem | +213 05 45 678 901 |
| 5 | karim_music | Karim Bouchkhi | Tlemcen | +213 06 56 789 012 |
| 6 | amina_chef | Amina Hadj-Aissa | Blida, Downtown | +213 07 67 890 123 |
| 7 | ali_fitness | Ali Bouchta | Setif, Haouchias | +213 05 78 901 234 |
| 8 | samira_language | Samira Kebaili | Medea | +213 06 89 012 345 |
| 9 | moussa_mechanic | Moussa Aidel | Tipaza, Chenoua | +213 07 90 123 456 |
| 10 | zainab_garden | Zainab Benkhalifa | Boumerdes, Baya | +213 05 01 234 567 |

### Posts (10 Sample Service Posts)
| PostId | Title | Category | PaymentMethod | AvailableDate |
|--------|-------|----------|----------------|---------------|
| 1 | Private JavaScript Lessons | Technology | Credit (50) | 2025-12-20 10:00 |
| 2 | Custom Graphic Design | Design | Exchange | 2025-12-18 14:30 |
| 3 | Professional Photo Session | Photography | Credit (60) | 2025-12-25 09:00 |
| 4 | Blog Content Writing | Writing | Exchange | 2025-12-22 11:00 |
| 5 | Guitar Lessons All Levels | Music | Credit (45) | 2026-01-01 15:00 |
| 6 | Algerian Cooking Coaching | Cooking | Credit (55) | 2026-01-05 18:00 |
| 7 | Personal Fitness Coaching | Fitness | Exchange | 2025-12-30 06:00 |
| 8 | Intensive English Tutoring | Languages | Credit (40) | 2025-12-27 13:00 |
| 9 | Auto Mechanics Diagnostics | Automotive | Exchange | 2026-01-08 08:30 |
| 10 | Organic Gardening Consultation | Gardening | Credit (50) | 2026-01-12 10:00 |

### Events (10 Sample Events)
| EventId | EventTitle | EventStartDate | EventEndDate | MaxAttendees |
|---------|------------|----------------|--------------|--------------|
| 1 | Web Development Workshop | 2025-12-15 09:00 | 2025-12-15 16:00 | 20 |
| 2 | UI/UX Design Masterclass | 2025-12-20 10:00 | 2025-12-20 14:00 | 15 |
| 3 | Product Photography | 2025-12-22 18:00 | 2025-12-22 20:00 | 25 |
| 4 | Algerian Literary Week | 2026-01-05 14:00 | 2026-01-05 18:00 | 50 |
| 5 | Intensive Guitar Course | 2026-01-10 11:00 | 2026-01-10 15:00 | 12 |

### Exchanges (Sample - All with Valid Date Sequences)
| ExchangeId | PostId | OfferedBy | RequestedBy | Status | ProposedDate | ConfirmedDate | CompletedDate |
|------------|--------|-----------|-------------|--------|-------------|---------------|---------------|
| 1 | 1 | 1 | 2 | completed | 2025-12-12 10:00 | 2025-12-13 14:30 | 2025-12-15 18:00 |
| 2 | 2 | 2 | 3 | accepted | 2025-12-13 09:00 | 2025-12-14 11:20 | NULL |
| 3 | 3 | 3 | 4 | completed | 2025-12-14 15:00 | 2025-12-15 16:45 | 2025-12-20 18:30 |
| 4 | 4 | 4 | 5 | pending | 2025-12-16 10:30 | NULL | NULL |

---

## 🔧 Common Tasks

### Task 1: Create a New User
```php
<?php
$mysqli = new mysqli("localhost", "user", "password", "skill_exchange_db");

$query = "INSERT INTO Users 
  (UserName, FullName, Email, Password, Location, PhoneNumber, BirthDate, Gender)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $mysqli->prepare($query);
$stmt->bind_param(
  "ssssssss",
  $username,
  $fullname,
  $email,
  password_hash($password, PASSWORD_BCRYPT),
  $location,
  $phone,  // Format: +213 05 12 345 678
  $birthdate,  // Format: YYYY-MM-DD
  $gender
);
$stmt->execute();
?>
```

### Task 2: Create a Service Post
```php
<?php
$query = "INSERT INTO Posts 
  (UserId, CategoryId, Title, Description, PostType, MeetLocation, 
   AvailableDate, Duration, PaymentMethod, RequiredCredits)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $mysqli->prepare($query);
$stmt->bind_param(
  "iissssssii",
  $user_id,  // Existing user
  $category_id,
  $title,
  $description,
  $post_type,  // 'in-person' or 'online'
  $location,
  $available_date,  // Must be future: YYYY-MM-DD HH:MM:SS
  $duration,  // Minutes: 1-480
  $payment_method,  // 'exchange' or 'credit'
  $credits  // Required if payment='credit', else 0
);
$stmt->execute();
?>
```

### Task 3: Propose an Exchange
```php
<?php
// All future dates, proper sequence
$proposed = '2025-12-20 10:00:00';  // Future date
$confirmed = NULL;  // Will be set when accepted
$completed = NULL;  // Will be set when done

$query = "INSERT INTO Exchanges 
  (PostId, OfferedByUserId, RequestedByUserId, Status, 
   ProposedDate, ConfirmedDate, CompletedDate, MeetingLocation)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $mysqli->prepare($query);
$stmt->bind_param(
  "iiisssss",
  $post_id,
  $offered_by,  // Service provider user
  $requested_by,  // Service receiver user
  $status,  // 'pending'
  $proposed,
  $confirmed,
  $completed,
  $location
);
$stmt->execute();
?>
```

### Task 4: Complete an Exchange
```php
<?php
// IMPORTANT: CompletedDate MUST be > ConfirmedDate (strictly greater)
$completed_date = '2025-12-20 18:00:00';  // Later than ConfirmedDate

$query = "UPDATE Exchanges 
SET 
  Status = 'completed',
  CompletedDate = ?
WHERE ExchangeId = ? AND Status = 'accepted'";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("si", $completed_date, $exchange_id);
$stmt->execute();
?>
```

### Task 5: Display Dates in Algerian Format
```php
<?php
// Method 1: Using MySQL function (Best Performance)
$query = "SELECT 
  ExchangeId,
  FormatDateAlgerian(ProposedDate) as ProposedDate,
  FormatDateAlgerian(CompletedDate) as CompletedDate,
  Status
FROM Exchanges
ORDER BY ExchangeId";

$result = $mysqli->query($query);
while($row = $result->fetch_assoc()) {
  echo "Exchange " . $row['ExchangeId'] . 
       " proposed: " . $row['ProposedDate'] . 
       " completed: " . $row['CompletedDate'];
}

// Method 2: Format in PHP
$query = "SELECT ProposedDate, CompletedDate FROM Exchanges WHERE ExchangeId = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $exchange_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

$proposed = date('Y-m-d H:i', strtotime($row['ProposedDate']));
$completed = date('l d-m-Y H:i', strtotime($row['CompletedDate']));
echo "Proposed: $proposed (Thursday 20-12-2025 10:00)";
?>
```

### Task 6: Validate Date Before Insert (PHP)
```php
<?php
function validate_exchange_dates($proposed, $confirmed, $completed) {
  $now = new DateTime('now', new DateTimeZone('Africa/Algiers'));
  $proposed_dt = DateTime::createFromFormat('Y-m-d H:i:s', $proposed);
  $confirmed_dt = $confirmed ? DateTime::createFromFormat('Y-m-d H:i:s', $confirmed) : null;
  $completed_dt = $completed ? DateTime::createFromFormat('Y-m-d H:i:s', $completed) : null;
  
  // ProposedDate must be in future
  if($proposed_dt <= $now) {
    return "ERROR: Proposed date must be in future";
  }
  
  // Sequence validation
  if($confirmed_dt && $confirmed_dt < $proposed_dt) {
    return "ERROR: Confirmed date must be after proposed date";
  }
  
  if($completed_dt && $confirmed_dt && $completed_dt <= $confirmed_dt) {
    return "ERROR: Completed date must be GREATER than confirmed date";
  }
  
  return "VALID";
}

// Test
echo validate_exchange_dates(
  '2025-12-20 10:00:00',
  '2025-12-21 14:30:00',
  '2025-12-25 18:00:00'
);  // Returns: VALID
?>
```

---

## ❌ Error Handling

### Common Errors and Solutions

#### Error #1: "Proposed date cannot be in the past"
```
Problem: ProposedDate is before TODAY (2025-12-11)
Solution: Use future dates only
Wrong: ProposedDate = '2025-12-01 10:00:00'
Right: ProposedDate = '2025-12-20 10:00:00'
```

#### Error #2: "Completed date must be greater than confirmed date"
```
Problem: CompletedDate <= ConfirmedDate
Solution: Ensure CompletedDate is AFTER ConfirmedDate
Wrong: ConfirmedDate = '2025-12-20 14:30:00' 
       CompletedDate = '2025-12-20 14:30:00' (SAME TIME!)
Right: ConfirmedDate = '2025-12-20 14:30:00'
       CompletedDate = '2025-12-25 18:00:00' (LATER!)
```

#### Error #3: "User must be at least 13 years old"
```
Problem: BirthDate is too recent
Solution: Set BirthDate before 2012-12-11
Wrong: BirthDate = '2015-05-10'  (Age 10)
Right: BirthDate = '1995-05-10'  (Age 30)
```

#### Error #4: "In-person posts must have a MeetLocation"
```
Problem: PostType='in-person' but MeetLocation is empty
Solution: Always provide location for in-person posts
Wrong: PostType='in-person', MeetLocation=NULL
Right: PostType='in-person', MeetLocation='Algiers, Hydra'
```

#### Error #5: "Available date cannot be in the past"
```
Problem: AvailableDate is before NOW()
Solution: Set future dates
Wrong: AvailableDate = '2025-12-01 10:00:00'
Right: AvailableDate = '2025-12-20 10:00:00'
```

#### Error #6: "Event duration cannot exceed 30 days"
```
Problem: EventEndDate - EventStartDate > 30 days
Solution: Limit events to 30 days maximum
Wrong: START='2025-12-01' END='2026-02-01' (62 days)
Right: START='2025-12-15' END='2025-12-20' (5 days)
```

---

## 🧪 Testing Guide

### Unit Tests - Date Validation

#### Test 1: Future ProposedDate (PASS)
```sql
INSERT INTO Exchanges 
(PostId, OfferedByUserId, RequestedByUserId, Status, ProposedDate)
VALUES (1, 1, 2, 'pending', '2025-12-20 10:00:00');
-- Result: ✅ SUCCESS
```

#### Test 2: Past ProposedDate (FAIL)
```sql
INSERT INTO Exchanges 
(PostId, OfferedByUserId, RequestedByUserId, Status, ProposedDate)
VALUES (1, 1, 2, 'pending', '2025-12-01 10:00:00');
-- Result: ❌ ERROR #1644 - Proposed date cannot be in the past
```

#### Test 3: CompletedDate > ConfirmedDate (PASS)
```sql
INSERT INTO Exchanges 
(PostId, OfferedByUserId, RequestedByUserId, Status, 
 ProposedDate, ConfirmedDate, CompletedDate)
VALUES (1, 1, 2, 'completed', 
        '2025-12-12 10:00:00',
        '2025-12-13 14:30:00',
        '2025-12-15 18:00:00');
-- Result: ✅ SUCCESS (12→13→15 proper sequence)
```

#### Test 4: CompletedDate = ConfirmedDate (FAIL)
```sql
INSERT INTO Exchanges 
(PostId, OfferedByUserId, RequestedByUserId, Status, 
 ProposedDate, ConfirmedDate, CompletedDate)
VALUES (1, 1, 2, 'completed',
        '2025-12-12 10:00:00',
        '2025-12-15 14:30:00',
        '2025-12-15 14:30:00');
-- Result: ❌ ERROR - Completed date must be GREATER than confirmed date
```

#### Test 5: Formatting Functions
```sql
-- Test FormatDateAlgerian
SELECT FormatDateAlgerian(NOW());
-- Expected: 2025-12-11 14:30 (current time)

-- Test FormatDateAlgerianWithDay
SELECT FormatDateAlgerianWithDay(NOW());
-- Expected: Thursday 11-12-2025 14:30 (with day name)

-- Test with real exchange data
SELECT 
  ExchangeId,
  FormatDateAlgerian(ProposedDate) as Proposed,
  FormatDateAlgerian(CompletedDate) as Completed
FROM Exchanges WHERE ExchangeId = 1;
-- Expected: Shows dates in YYYY-MM-DD HH:MM format
```

### Integration Tests

#### Test 6: Complete Exchange Workflow
```sql
-- Step 1: Create post
INSERT INTO Posts (UserId, CategoryId, Title, Description, PostType, 
                   AvailableDate, Duration, PaymentMethod, RequiredCredits, PostStatus)
VALUES (1, 1, 'Test Course', 'Description', 'online', '2025-12-20 10:00:00', 
        120, 'exchange', 0, 'active');
-- Expected: ✅ PostId = 11

-- Step 2: Create exchange
INSERT INTO Exchanges (PostId, OfferedByUserId, RequestedByUserId, Status, ProposedDate)
VALUES (11, 1, 2, 'pending', '2025-12-20 10:00:00');
-- Expected: ✅ ExchangeId = 11

-- Step 3: Accept exchange
UPDATE Exchanges SET Status = 'accepted', ConfirmedDate = '2025-12-21 14:30:00' 
WHERE ExchangeId = 11;
-- Expected: ✅ Updated 1 row

-- Step 4: Complete exchange
UPDATE Exchanges SET Status = 'completed', CompletedDate = '2025-12-25 18:00:00' 
WHERE ExchangeId = 11;
-- Expected: ✅ Updated 1 row

-- Step 5: Add review
INSERT INTO Rating (ExchangeId, ReviewerId, ReviewedUserId, Rating, ReviewText)
VALUES (11, 2, 1, 5, 'Excellent!');
-- Expected: ✅ ReviewId = 11

-- Step 6: Verify with formatted dates
SELECT 
  ExchangeId,
  FormatDateAlgerian(ProposedDate) as Proposed,
  FormatDateAlgerian(ConfirmedDate) as Confirmed,
  FormatDateAlgerian(CompletedDate) as Completed,
  Status
FROM Exchanges WHERE ExchangeId = 11;
-- Expected: All dates in YYYY-MM-DD HH:MM format
```

### Checklist for Team

- [ ] Database imports without errors
- [ ] All 16 tables created successfully
- [ ] FormatDateAlgerian() function works
- [ ] FormatDateAlgerianWithDay() function works
- [ ] Sample data loads (10 users, 10 posts, 10 events, 10 exchanges)
- [ ] Past ProposedDate is rejected
- [ ] CompletedDate > ConfirmedDate validation works
- [ ] In-person posts without location are rejected
- [ ] All date displays show in Algerian format
- [ ] Notifications have proper Title field (5+ chars)
- [ ] All foreign keys work (no orphaned records)
- [ ] Credit transactions balance correctly

---

## 📚 Reference Guide

### Quick SQL Commands

```sql
-- Count records
SELECT COUNT(*) as total_users FROM Users;
SELECT COUNT(*) as total_posts FROM Posts WHERE PostStatus = 'active';
SELECT COUNT(*) as total_exchanges FROM Exchanges WHERE Status = 'completed';

-- Top rated users
SELECT UserId, FullName, Rating, RatingCount 
FROM Users 
ORDER BY Rating DESC 
LIMIT 5;

-- Active posts by category
SELECT c.CategoryName, COUNT(*) as post_count
FROM Posts p
JOIN Category c ON p.CategoryId = c.CategoryId
WHERE p.PostStatus = 'active'
GROUP BY c.CategoryName;

-- Upcoming events
SELECT EventTitle, EventStartDate, MaxAttendees, CurrentAttendeesNumber
FROM Events
WHERE EventStatus = 'upcoming'
ORDER BY EventStartDate ASC;

-- Exchange summary
SELECT 
  Status,
  COUNT(*) as count,
  AVG(DATEDIFF(CompletedDate, ProposedDate)) as avg_days
FROM Exchanges
GROUP BY Status;

-- User activity
SELECT 
  u.UserId,
  u.FullName,
  COUNT(DISTINCT e.ExchangeId) as exchange_count,
  COUNT(DISTINCT ev.EventId) as event_attendance,
  u.CreditBalance
FROM Users u
LEFT JOIN Exchanges e ON u.UserId IN (e.OfferedByUserId, e.RequestedByUserId)
LEFT JOIN EventsAttendees ea ON u.UserId = ea.UserId
LEFT JOIN Events ev ON ea.EventId = ev.EventId
GROUP BY u.UserId
ORDER BY exchange_count DESC;
```

### Key Constraints Summary

| Constraint | Check | Action |
|-----------|-------|--------|
| BirthDate | Age ≥ 13 | Reject INSERT/UPDATE |
| ProposedDate | NOT past | Reject INSERT |
| ConfirmedDate | ≥ ProposedDate | Reject INSERT/UPDATE |
| CompletedDate | > ConfirmedDate | Reject INSERT/UPDATE |
| EventStartDate | NOT past | Reject INSERT |
| EventEndDate | > EventStartDate | Reject INSERT/UPDATE |
| EventDuration | ≤ 30 days | Reject INSERT/UPDATE |
| PostType | 'in-person' requires location | Reject INSERT/UPDATE |
| PaymentMethod | 'credit' requires RequiredCredits > 0 | Reject INSERT/UPDATE |
| AvailableDate | NOT past | Reject INSERT/UPDATE |

---

## 📞 Support & FAQ

### Q: What do I do if import fails?
**A:** 
1. Check if database `skill_service_exchange_db` exists
2. Verify MySQL is running
3. Ensure you have proper permissions
4. Try importing in small chunks if file is large
5. Check for syntax errors using MySQL command: `mysql -u user -p < swapdb.sql`

### Q: Can I modify the date validation rules?
**A:** Yes! The triggers are modifiable. Edit the relevant trigger in swapdb.sql before importing, or drop and recreate triggers after import:
```sql
DROP TRIGGER IF EXISTS trg_validate_exchange_dates_insert;
-- Then recreate with modified logic
```

### Q: How do I add new users?
**A:** Use the INSERT statement shown in "Common Tasks" section with proper format for Algerian phone numbers (+213 05/06/07 XXXXXXXX).

### Q: How do I display dates in my application?
**A:** Use the FormatDateAlgerian() function in your SELECT queries, or format in PHP using date('Y-m-d H:i', strtotime($date)).

### Q: What if I need to modify past dates?
**A:** The triggers only apply to future operations. To modify existing data, you can use UPDATE statements (which still go through validation triggers).

### Q: Can I run queries with the database?
**A:** Yes! Use phpMyAdmin or MySQL command line. Sample queries are provided in the "Reference Guide" section.

---

## ✅ Deployment Checklist

- [ ] Database backed up
- [ ] swapdb.sql file available
- [ ] MySQL/phpMyAdmin access ready
- [ ] Import swapdb.sql without errors
- [ ] Verify all 16 tables exist
- [ ] Test formatting functions
- [ ] Load sample data successfully
- [ ] Test date validation (past dates rejected)
- [ ] Verify CompletedDate > ConfirmedDate rule
- [ ] Test with actual user data
- [ ] Performance test with large datasets
- [ ] Document any custom modifications
- [ ] Train team on date format standards
- [ ] Set up regular backups

---

## 📝 Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2025-12-11 | Initial release with 8 date validation triggers, 2 formatting functions, comprehensive documentation |

---

**Last Updated:** December 11, 2025  
**Status:** ✅ Production Ready  
**Support:** Contact your development team

---

*This comprehensive documentation covers all aspects of the Skill Service Exchange database. For questions, refer to the relevant section or contact your team lead.*
