# SWAP

A modern web platform enabling users to trade expertise and skills without monetary exchange. Built with HTML, CSS, bootstrap CSS, Javascript, php, this platform connects people who want to learn with those willing to teach, creating a community-driven skill-sharing ecosystem.

## 🌟 Overview

SWAP is designed to facilitate the exchange of knowledge and services between users based on their skills and needs. Whether you're a graphic designer looking to learn Spanish, or a chef wanting to improve your photography skills, this platform helps you find the perfect skill-exchange partner completely for free.

## ✨ Key Features

### Core Functionality
- **Skill-Based Trading System**: Exchange skills and services without monetary transactions
- **Comprehensive User Profiles**: Showcase your skills, experience, and what you're looking to learn
- **Rating & Review System**: Build trust through community feedback and verified ratings
- **Event Management**: Create and join community workshops and skill-sharing events
- **Real-Time Notifications**: Stay updated on matches, messages, and event invitations
- **Advanced Search & Filtering**: Find exactly what you need with category-based browsing

### User Experience
- **Fully Responsive Design**: Optimized for desktop, tablet, and mobile devices
- **Smooth Animations**: Enhanced hover effects and transitions throughout
- **Inter Typography**: Clean, modern font family for optimal readability

## 🎨 Design System

### Color Palette
```css
Background:      #424769  /* Main app background */
Primary Buttons: #f9b17a  /* Call-to-action elements */
Post Cards:      #676f9d  /* Content cards */
Emphasis:        #2d3250  /* Sidebar and important elements */
Text:            #FFFFFF  /* Primary text */
                 #E0E0E0  /* Secondary text */
```

### Typography
- **Font Family**: Inter
- **Base Size**: 16px
- **Weights**: 400 (normal), 500 (medium)

## 📄 Pages & Routes

### Public Pages
1. **Landing Page** (`/`)
   - Hero section with compelling value proposition
   - Features grid highlighting platform benefits
   - Testimonials from active users
   - Call-to-action for sign-up

2. **Login Page** (`/login`)
   - Email and password authentication
   - "Remember me" functionality
   - Links to sign-up and password recovery

3. **Sign-Up Page** (`/signup`)
   - User registration with skill tagging
   - Categorized skill selection
   - Profile setup wizard

4. **About Us** (`/about`)
   - Platform mission and values
   - Team information
   - Community statistics

5. **Contact Us** (`/contact`)
   - Contact form for inquiries
   - Support information

### Authenticated Pages

6.**Home Posts** (`/home`)
   - Service listing with filtering
   - Category-based search
   - Skill-based filtering
   - Location filtering

7. **add Post** (`/addpost`)
   - Create skill offering or request
   - Skill selection with categorized dropdowns
   - Description and requirements

10. **Profile Page** (`/profile`)
    - User profile display
    - Skills showcase with ratings
    - Reviews and testimonials
    - Availability calender

11. **Edit Profile** (`/edit-profile`)
    - Update personal information
    - Manage skills (add/remove)
    - Upload profile picture
    - Update availability

12. **Events Page** (`/events`)
    - Community workshops and meetups
    - Filter by category and date
    - RSVP functionality
    - Event creation


14. **Notifications** (`/notifications`)
    - Real-time updates
    - Match notifications
    - Message alerts
    - Event reminders

## 🛠️ Technical Stack

### Frontend
- **java script**
- **bootstrap CSS**



## 🎯 Skills System

### Architecture
The platform implements a comprehensive, centralized skills management system to ensure data consistency across all features.

#### Central Skills Data 
- **10 Categories**: Technology, Creative Arts, Languages, Business, Home Services, Fitness, Music, Cooking, Education, Crafts
- **15-19 Skills per Category**: Curated list of common skills
- **"Other" Option**: Custom skill input for flexibility
- **Type-Safe**: Full TypeScript support


#### Features:
- Category-based dropdown navigation
- Multi-skill selection with badges
- Remove functionality for selected skills
- Custom "Other" skill input
- Prevents duplicate selection
- Responsive design

### Skill Categories

1. **Technology** (19 skills)
   - Web Development, Mobile Development, Data Science, UI/UX Design, etc.

2. **Creative Arts** (18 skills)
   - Graphic Design, Photography, Video Editing, Drawing, etc.

3. **Languages** (19 skills)
   - English, Spanish, French, Mandarin, Arabic, etc.

4. **Business** (17 skills)
   - Marketing, Accounting, Project Management, Sales, etc.

5. **Home Services** (15 skills)
   - Plumbing, Carpentry, Electrical Work, Gardening, etc.

6. **Fitness** (15 skills)
   - Personal Training, Yoga, Pilates, CrossFit, etc.

7. **Music** (16 skills)
   - Guitar, Piano, Singing, Music Production, etc.

8. **Cooking** (17 skills)
   - Baking, Italian Cuisine, Vegan Cooking, BBQ, etc.

9. **Education** (16 skills)
   - Tutoring, Test Prep, Career Coaching, Study Skills, etc.

10. **Crafts** (18 skills)
    - Knitting, Woodworking, Jewelry Making, Pottery, etc.

## 📁 Project Structure

assets/
│ ├── css/
│ ├── icons/
│ ├── images/
│ └── js/
auth/
│ ├── login.html
│ ├── login.php
│ ├── signup.html
│ └── signup.php
components/
│ ├── footer.html
│ ├── footer.php
│ ├── header.html
│ ├── header.php
│ └── sidebar.html
│ └── sidebar.php
dashboard/
│ ├── events/
│ │ ├── addevent.html
│ │ ├── addevent.php
│ │ └── events.html
│ │ └── events.php
│ ├── notification/
│ │ └── notifications.html
│ │ └── notifications.php
post/
│ ├── addpost.html
│ ├── addpost.php
│ └── postdetails.html
│ └── postdetails.php
profile/
│ ├── home.html
│ └── home.php
php/
README.md
about.html
about.php
contact.html
contact.php
index.html
index.php





## 🎯 Key Components

### DashboardLayout
Reusable layout component providing:
- Sidebar navigation
- Responsive mobile menu
- Consistent header
- Page container

### SkillSelector
Centralized skill selection with:
- Category-based organization
- Multi-select capability
- Custom skill input
- Badge display with removal

### PostCard
Service listing display:
- Skill badges
- User information
- Quick actions
- Rating display

### ProfileCard
User profile preview:
- Avatar display
- Skill showcase
- Rating summary
- Contact options

## 🔐 Authentication Flow

1. User lands on HomePage
2. Clicks "Sign Up" or "Login"
3. **Sign Up**: Selects initial skills during registration
4. **Login**: Enters credentials
5. Redirects to Dashboard upon success
6. Full access to authenticated features

## 🎨 Design Principles

### Trustworthy
- Rating systems
- User reviews
- Transparent profiles

### Modern
- Clean interface
- Smooth animations
- Intuitive navigation
- Consistent design language

### Accessible
- WCAG compliant
- Keyboard navigation
- Screen reader support
- High contrast ratios

### Responsive
- Mobile-first approach
- Tablet optimization
- Desktop enhancement
- Flexible layouts

## 📊 Feature Highlights

### Trust & Safety

- Rating and review mechanism
- Community guidelines enforcement

### Community Events
- Workshop creation and hosting
- Skill-sharing meetups
- Category-based filtering
- RSVP and attendance tracking
- Event notifications

## 🔄 Data Consistency

All skill-related data throughout the platform uses the centralized `skillsData.ts` file, ensuring:
- No duplicate skill entries
- Consistent skill naming
- Easy maintenance and updates
- Type-safe skill references
- Standardized categorization

## 🎯 Future Enhancements

- [ ] Video call integration
- [ ] Advanced scheduling calendar
- [ ] Skill verification through tests
- [ ] Reputation score algorithm
- [ ] Mobile native apps
- [ ] Social media integration
- [ ] Progress tracking for learning paths

