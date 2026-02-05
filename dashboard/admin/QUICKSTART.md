# Admin Panel Quick Start Guide

## 🚀 Getting Started

### Step 1: Access the Admin Panel
1. Open your browser and go to: `http://localhost/Skill-Service_exchanging_website-/dashboard/admin/`
2. Or navigate directly to: `http://localhost/Skill-Service_exchanging_website-/dashboard/admin/index.php`

### Step 2: Login (if not already logged in)
- The system will automatically redirect you to the login page if needed
- Use any registered account credentials to log in first
- Only accounts with admin privileges can access the panel

### Step 3: Default Admin Account
The first user in the system is automatically set as an admin:
- **Username**: `youssef_dev`
- **Email**: `youssef@outlook.dz`
- **Password**: Use the login form (encrypted in database)

## 📊 Dashboard Overview

Once logged in, you'll see:
1. **Sidebar Navigation** - Quick access to all admin features
2. **Statistics Cards** - Key metrics at a glance
3. **Recent Activity** - Latest users and reports

## 🎯 Common Tasks

### Ban a User
1. Click "👥 Users Management" in sidebar
2. Find the user in the list
3. Click "Ban" button
4. Confirm the action
5. User account is now banned (they cannot log in)

### Disable Inappropriate Post
1. Click "📝 Posts Moderation" in sidebar
2. Search or find the problematic post
3. Click "Disable" button
4. Post is hidden from the platform

### Adjust User Credits
1. Click "💳 Transactions" in sidebar
2. Select user from dropdown
3. Enter amount: 
   - Positive number = Add credits
   - Negative number = Deduct credits
4. (Optional) Add description
5. Click "Adjust"

### Manage Events
1. Click "📅 Events Management" in sidebar
2. View upcoming, ongoing, or cancelled events
3. Cancel event: Click "Cancel" button
4. Delete event: Click "Delete" button (permanent)

### Handle User Reports
1. Click "⚠️ Reports" in sidebar
2. See pending reports
3. Review each report
4. Click "Resolve" (take action) or "Dismiss" (no action)

### Add New Category
1. Click "🏷️ Categories & Skills" in sidebar
2. Scroll to "Add New Category" form
3. Enter category name
4. (Optional) Add description
5. Click "Add Category"

## 🔐 Security Tips

1. **Keep Admin Account Safe**
   - Use a strong password
   - Don't share admin login
   - Log out when done

2. **Confirm Destructive Actions**
   - Deleting is permanent
   - Banning affects user access
   - Review before confirming

3. **Document Major Actions**
   - Keep notes of why you banned users
   - Log the reason when adjusting credits
   - Mark reports with resolution notes

## 📱 Mobile Access

The admin panel is fully responsive and works on:
- Tablets (iPad, Android tablets)
- Mobile devices (with responsive menu)
- Desktop computers

## ⚙️ Admin Page Structure

| Page | Icon | Purpose |
|------|------|---------|
| Dashboard | 📊 | View statistics & recent activity |
| Users | 👥 | Ban/unban users, manage admin status |
| Posts | 📝 | Disable/enable/delete posts |
| Events | 📅 | Cancel/delete events |
| Categories | 🏷️ | Manage categories and skills |
| Exchanges | 🔄 | Monitor skill exchanges |
| Reports | ⚠️ | Handle user reports |
| Transactions | 💳 | Manage user credits |

## 🆘 Troubleshooting

### "Access denied" error
→ You don't have admin privileges
→ Contact system administrator to promote your account

### Can't find a user/post/event
→ Try using the search feature
→ Adjust filters (banned, active, etc.)
→ Results are limited to 50 records per page

### Database error
→ Verify MySQL is running on port 3307
→ Check database connection in `.env.local`
→ Ensure database credentials are correct

## 💡 Best Practices

1. **Before Banning**: Review user history and violations
2. **Before Deleting**: Confirm it's the right content
3. **Credit Adjustments**: Always add a description
4. **Regular Checks**: Review reports weekly
5. **Keep Backups**: Maintain database backups
6. **Document Decisions**: Log major actions for audit trail

## 📞 Need Help?

- Read the full documentation in `README.md`
- Check database schema in `/DataBaseManagement/swapdb.sql`
- Review error messages carefully
- Check browser console for technical errors

---

**Last Updated**: February 2026
**Admin Panel Version**: 1.0
