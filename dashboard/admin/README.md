# Admin Panel Documentation

## Overview

The Admin Panel is a comprehensive management system for the Skill Service Exchange platform. It allows administrators to manage users, moderate content, oversee transactions, and maintain the integrity of the platform.

## Access & Authentication

### How to Access
- **URL**: `/dashboard/admin/index.php`
- **Requirements**: You must be logged in AND have admin privileges
- **Default Admin**: User ID 1 (Youssef Benhadj) - created during database setup

### Authentication Flow
All admin pages include `auth_admin.php` which:
1. Checks if user is logged in
2. Verifies user is not banned
3. Confirms user has admin privileges (`IsAdmin = 'yes'`)
4. Redirects to login page if any check fails

```php
require_once __DIR__ . '/auth_admin.php';
```

## Admin Panel Structure

```
/dashboard/admin/
├── index.php                  # Dashboard home with statistics
├── users.php                  # User management
├── posts.php                  # Post moderation
├── events.php                 # Event management
├── categories.php             # Categories & skills management
├── exchanges.php              # Exchange monitoring
├── reports.php                # Report management
├── transactions.php           # Credit/transaction management
├── auth_admin.php             # Authentication middleware
├── logout.php                 # Admin logout
└── admin.css                  # Admin styling
```

## Feature Overview

### 1. Dashboard (index.php)
**Statistics at a Glance**
- Total Users
- Active Posts
- Active Events
- Pending Exchanges
- Pending Reports
- Total Transactions
- Banned Users

**Recent Activity Feeds**
- Latest 5 registered users
- Latest 5 reports with status indicators
- Quick links to manage each section

### 2. Users Management (users.php)
**Capabilities**
- View all users with details (username, email, full name, join date, rating, credits)
- Filter users: All, Banned, Admins
- Search by username, email, or full name
- **Actions**:
  - Ban/Unban users
  - Make/Remove admin privileges (with safeguard against removing last admin)

**Database Fields Accessed**
- UserId, UserName, Email, FullName, UserSince
- Rating, RatingCount, CreditBalance
- IsBanned, IsAdmin, ExchangeCount

### 3. Posts Moderation (posts.php)
**Capabilities**
- View all posts with creation info
- Filter by status: All, Active, Disabled
- Search posts by title or creator username

**Actions**
- Disable posts (hide from platform)
- Enable posts (restore visibility)
- Delete posts permanently

**Display Info**
- Post title, creator, creation date
- Like count, current status
- Timestamp of last modification

### 4. Events Management (events.php)
**Capabilities**
- Monitor all platform events
- Filter by status: All, Upcoming, Ongoing, Cancelled
- Search by event title or organizer name

**Actions**
- Cancel events
- Delete events permanently

**Information Shown**
- Event title, organizer name
- Start date & time
- Attendance: Current/Maximum attendees
- Event status with color coding

### 5. Categories & Skills (categories.php)
**Capabilities**
- Add new categories to the platform
- View all categories
- Delete categories
- View all available skills organized by category

**Form Fields**
- Category Name (required)
- Category Description (optional)

**Display**
- Full list of skills with their categories
- Post count for each category

### 6. Exchanges Management (exchanges.php)
**Capabilities**
- Monitor all skill exchanges
- Filter by status: All, Pending, Accepted, Completed, Rejected
- Search by post title or participant username

**Information Tracked**
- Post being exchanged
- User who offered service
- User who requested service
- Current exchange status
- Key dates (proposed, confirmed, completed)

### 7. Reports Management (reports.php)
**Capabilities**
- Review user reports
- Filter by status: All, Pending, Reviewing, Resolved
- Take action on reported content

**Actions**
- Mark reports as "Resolved"
- Dismiss reports
- View reason for report
- See reported entity type (user, post, comment, event)

**Status Tracking**
- Pending → Initial submission
- Reviewing → Under admin review
- Resolved → Issue addressed
- Dismissed → No action needed

### 8. Transactions Management (transactions.php)
**Capabilities**
- View all credit transactions
- Filter: All, Earned, Spent
- Search transactions by username

**Admin Credit Control**
- Adjust user credits (add or deduct)
- Log reason for adjustment
- Automatic transaction record creation
- Real-time balance updates

**Transaction Details**
- User, transaction type, amount
- Balance before/after
- Description (reason)
- Timestamp

## Making a User Admin

### Method 1: Via Users Management Page
1. Go to `/dashboard/admin/users.php`
2. Find the user you want to promote
3. Click "Make Admin" button
4. Confirm the action

### Method 2: Via Database
```php
UPDATE Users SET IsAdmin = 'yes' WHERE UserId = [user_id];
```

### Important Notes
- Only users with `IsAdmin = 'yes'` can access admin pages
- Cannot remove admin status from the last admin
- Making someone admin also prevents auto-logout on permissions pages

## Database Schema Extensions

### Users Table Addition
```sql
ALTER TABLE Users ADD COLUMN IsAdmin ENUM('yes','no') DEFAULT 'no';
CREATE INDEX idx_users_is_admin ON Users(IsAdmin);
```

### Initial Admin Setup
```sql
UPDATE Users SET IsAdmin = 'yes' WHERE UserId = 1;
```

## Security Features

1. **Session Management**
   - Session-based authentication
   - `session_regenerate_id(true)` after successful login
   - Auto-logout on permission check failure

2. **Input Validation**
   - All user inputs sanitized with `htmlspecialchars()`
   - Prepared statements for all SQL queries
   - Type binding to prevent SQL injection

3. **Authorization Checks**
   - Every page requires `auth_admin.php` inclusion
   - Ban status verification
   - Admin status verification
   - Confirmation dialogs for destructive actions

4. **Data Protection**
   - Admin actions logged in database
   - Transaction history maintained
   - Cannot delete last admin

## Styling & UI

### Design System
- **Color Scheme**: Indigo primary (#6366f1), with secondary colors
- **Layout**: Fixed sidebar + main content area
- **Responsive**: Fully responsive for mobile/tablet
- **Components**: Statistics cards, tables, forms, buttons, alerts

### CSS Classes
- `.admin-container` - Main layout wrapper
- `.admin-sidebar` - Navigation sidebar
- `.admin-main` - Main content area
- `.stat-card` - Statistics cards
- `.admin-table` - Data tables
- `.btn` - Button styles (primary, secondary, success, danger, warning)
- `.alert` - Alert messages (success, error)

## Usage Examples

### Banning a User
```php
// POST to users.php
POST data:
  user_id: 5
  action: ban
```

### Adjusting Credits
```php
// POST to transactions.php
POST data:
  adjust_credits: true
  user_id: 3
  amount: -50
  description: "Refund for cancelled exchange"
```

### Disabling a Post
```php
// POST to posts.php
POST data:
  post_id: 12
  action: disable
```

## Error Handling

All admin pages include:
- Try-catch blocks for database operations
- User-friendly error messages
- Success confirmation messages
- Form validation before submission

## Performance Considerations

- Pagination: All queries limited to 50-100 records
- Indexes: Added on commonly searched fields
- Prepared statements: Prevent SQL injection and improve performance
- Lazy loading: Statistics calculated on-demand

## Future Enhancements

Potential features to add:
- Bulk user actions (ban multiple users)
- Advanced analytics & charts
- Email notifications for key events
- Audit log of admin actions
- Content moderation queue
- Automated flagging system
- User analytics & behavior tracking
- Revenue reports for event costs
- Skill recommendations based on demand

## Troubleshooting

### Can't Access Admin Panel
- Verify you're logged in
- Check that your user ID is 1 or has been set as admin
- Verify `IsBanned` is set to 'no'
- Clear browser cache and cookies

### Database Operations Failing
- Verify MySQL connection on port 3307
- Check .env.local credentials
- Ensure database user has proper permissions
- Verify foreign key relationships

### Styling Issues
- Clear browser cache
- Verify admin.css is being loaded
- Check browser console for errors
- Test in different browser

## Contact & Support

For issues or feature requests, contact the development team.
