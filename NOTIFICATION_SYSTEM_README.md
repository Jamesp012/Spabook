# 🔔 SpaBook Notification System

## Overview
The SpaBook notification system provides comprehensive user notifications for booking status changes and appointment reminders. The system automatically notifies users when:

1. **Booking Created** - User creates a new booking
2. **Booking Status Changes** - Admin confirms/rejects/cancels bookings
3. **Appointment Reminders** - 24 hours and 1 hour before appointments

## ✨ Features

### 📋 Booking Status Notifications
- ✅ **Pending**: When user submits a booking request
- 🎉 **Confirmed**: When admin approves the booking
- ⚠️ **Rejected**: When admin declines the booking
- 🚫 **Cancelled**: When booking is cancelled
- ✨ **Completed**: When spa session is finished

### ⏰ Appointment Reminders
- 🌸 **24-hour reminder**: "Spa Appointment Tomorrow!"
- ⏰ **1-hour reminder**: "Spa Appointment in 1 Hour!"

### 🎨 Visual Features
- Color-coded notification types (info, success, warning, error)
- Special booking badges and reminder badges
- Animated reminder notifications
- Responsive design for mobile devices

## 🚀 Implementation

### Backend Components

#### 1. Database Table
```sql
-- notification table (already exists)
CREATE TABLE notification (
    notificationid SERIAL PRIMARY KEY,
    user_id UUID REFERENCES users(user_id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    type VARCHAR(50), -- 'info' | 'warning' | 'error' | 'success'
    is_read BOOLEAN DEFAULT false,
    created_at TIMESTAMPTZ DEFAULT current_timestamp,
    read_at TIMESTAMPTZ,
    metadata JSONB
);
```

#### 2. PHP Functions
- `createBookingStatusNotification()` - Creates booking status notifications
- `createAdminBookingNotification()` - Notifies admins of new bookings
- `createAppointmentReminders()` - Creates appointment reminders

#### 3. API Endpoints
- `POST booking_contr.php?action=send_appointment_reminders` - Trigger reminders manually
- `POST booking_contr.php?action=cancel_booking_user` - User cancels booking with notification

### Frontend Components

#### 1. User Notification Page
- `/views/user/user_notification.php` - Complete notification interface
- Filter by type (all, unread, info, success, warning, error)
- Mark as read functionality
- Delete notifications
- Load more pagination

#### 2. Notification Integration
- Notifications are automatically triggered on booking status changes
- Real-time updates when admin actions occur
- User-friendly display with metadata formatting

## 📅 Appointment Reminder Setup

### Option 1: Cron Job (Linux/Mac)
```bash
# Run every hour
0 * * * * php /path/to/Spabook/cron_appointment_reminders.php
```

### Option 2: Windows Task Scheduler
1. Open Task Scheduler
2. Create Basic Task
3. Set to run hourly
4. Action: Start a program
5. Program: `php.exe`
6. Arguments: `c:\xampp\htdocs\Spabook\cron_appointment_reminders.php`

### Option 3: Manual API Call
```bash
curl -X POST http://your-domain/Spabook/controller/booking_contr.php \
     -d "action=send_appointment_reminders"
```

## 🧪 Testing

### Run Test Script
```bash
php test_notifications.php
```
This will:
- Check if notification table exists
- Create test notifications for all booking statuses
- Create test reminder notifications
- Verify notifications were created
- Show integration instructions

### Manual Testing
1. Login as a user
2. Create a booking
3. Check notifications page for "Pending" notification
4. Have admin approve/reject the booking
5. Check for status change notification
6. Set up a booking for tomorrow to test 24-hour reminder
7. Set up a booking for next hour to test 1-hour reminder

## 🔧 Configuration

### Timezone Settings
The system uses `Asia/Manila` timezone. To change:
```php
// In cron_appointment_reminders.php and booking_contr.php
date_default_timezone_set('Your/Timezone');
```

### Reminder Timing
Reminders are sent when:
- **24-hour reminder**: 23-25 hours before appointment
- **1-hour reminder**: 50-70 minutes before appointment

To modify timing, edit the conditions in `createAppointmentReminders()` function.

### Notification Messages
Customize notification messages in the `$titles` and `$messages` arrays in `createBookingStatusNotification()` function.

## 📊 Monitoring

### Log Files
- `/logs/appointment_reminders.log` - Reminder processing logs
- Check for successful reminder sends and errors

### Database Monitoring
```sql
-- Check notification counts
SELECT type, COUNT(*) FROM notification GROUP BY type;

-- Check recent notifications
SELECT * FROM notification ORDER BY created_at DESC LIMIT 10;

-- Check unread notifications
SELECT COUNT(*) FROM notification WHERE is_read = false;
```

## 🚨 Troubleshooting

### Common Issues

1. **Notifications not sending**
   - Check if notification table exists
   - Verify `$notificationTableExists` is true
   - Check error logs

2. **Reminders not working**
   - Ensure cron job is running
   - Check booking data has proper dates/times
   - Verify timezone settings

3. **UI not displaying notifications**
   - Check browser console for JavaScript errors
   - Verify API endpoints are accessible
   - Check user authentication

### Debug Commands
```php
// Test notification table
$result = $php_fetch("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'notification') as exists");

// Test user session
var_dump($_SESSION['user_id']);

// Test notification creation
createBookingStatusNotification('user-id', 123, 'Confirmed');
```

## 🔄 Integration Points

### Booking Creation
- `controller/booking_contr.php` → `case 'create_booking'`
- Automatically creates "Pending" notification

### Admin Actions
- `case 'accept_booking'` → Creates "Confirmed" notification
- `case 'decline_booking'` → Creates "Rejected" notification
- `case 'complete_booking'` → Creates "Completed" notification

### User Actions
- `case 'cancel_booking_user'` → Creates "Cancelled" notification

### Cache Integration
- Notifications invalidate user booking caches
- Ensures real-time data consistency

## 🎯 Future Enhancements

### Potential Improvements
1. **Email Notifications** - Send email copies of important notifications
2. **Push Notifications** - Browser push notifications for real-time alerts
3. **SMS Reminders** - Text message reminders for appointments
4. **Admin Notifications** - Separate notification system for admins
5. **Notification Preferences** - User settings to control notification types
6. **Read Receipts** - Track when notifications are actually viewed
7. **Batch Processing** - Optimize reminder processing for large datasets

### Performance Optimizations
1. **Database Indexing** - Add indexes on user_id and created_at
2. **Caching** - Cache notification counts and recent notifications
3. **Pagination** - Implement server-side pagination for large notification lists
4. **Background Processing** - Use queue system for notification sending

---

*Last Updated: 2024*
*System Version: 1.0*