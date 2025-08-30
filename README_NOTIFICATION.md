# Notification System Documentation

## Overview
This document provides an overview of the notification system implemented for Spabook. The system allows for real-time notifications to both users and administrators, particularly for booking status changes and other important events.

## Database Structure
The notification system uses the following table:

```sql
CREATE TABLE notification (
    notificationid SERIAL PRIMARY KEY,
    user_id uuid NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    type VARCHAR(50), -- 'info', 'warning', 'error', 'success'
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMPTZ,
    metadata JSONB,
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

## Files Created/Modified

### New Files
1. `model/notification_model.php` - Database operations for notifications
2. `controller/notification_contr.php` - API endpoints and business logic for notifications
3. `components/notification_badge.php` - Reusable notification badge component
4. `views/admin/admin_notification.php` - Admin interface for notification management

### Modified Files
1. `controller/booking_contr.php` - Added notification creation on booking status changes
2. `views/user/user_notification.php` - Updated to use the new notification system

## Features

### For Users
- Real-time notification badge showing unread notification count
- Notification list with filtering options (all, unread, by type)
- Mark notifications as read individually or all at once
- Delete notifications
- Relative time display (e.g., "2 hours ago")
- Color-coded notifications by type (info, success, warning, error)

### For Admins
- View all notifications in the system
- Create and send notifications to specific users or all users
- Filter and search notifications
- View notification details including metadata
- Delete notifications

## Time Formatting Functions
The notification system includes two main time formatting functions:

1. `formatRelativeTime($timestamp)` - Converts a timestamp to a human-readable relative time (e.g., "2 hours ago")
2. `formatDateTime($timestamp)` - Formats a timestamp to a readable date and time (e.g., "January 1, 2023 at 2:30 PM")

## Integration with Booking System
The notification system is integrated with the booking system to automatically create notifications when:

1. A user creates a new booking (notifications for both user and admin)
2. A booking status changes (e.g., from "Pending" to "Confirmed" or "Rejected")

## How to Use

### Including the Notification Badge
To include the notification badge in any page:

```php
<?php 
$isAdmin = true; // Set to true for admin pages, false for user pages
include_once '../components/notification_badge.php'; 
?>
```

### Creating a Notification Programmatically
To create a notification from any controller:

```php
require_once '../controller/notification_contr.php';

// For a simple notification
createNotification(
    $user_id,
    'Notification Title',
    'Notification message goes here',
    'info', // Type: 'info', 'success', 'warning', 'error'
    [] // Optional metadata
);

// For a booking status notification
createBookingStatusNotification($user_id, $booking_id, 'Confirmed');
```

## API Endpoints

### User Notifications
- `get_user_notifications` - Get notifications for a specific user
- `mark_as_read` - Mark a notification as read
- `mark_all_as_read` - Mark all notifications as read for a user
- `delete_notification` - Delete a notification
- `get_unread_count` - Get unread notification count for a user

### Admin Notifications
- `get_admin_notifications` - Get all notifications in the system
- `create_notification` - Create a new notification
- `create_booking_notification` - Create a booking status notification

## Styling
The notification system uses Bootstrap 5 for styling, with custom CSS for the notification items. Notifications are color-coded by type:

- Info: Blue
- Success: Green
- Warning: Yellow
- Error: Red

## Future Enhancements
Potential future enhancements for the notification system:

1. Real-time push notifications using WebSockets
2. Email notifications for important events
3. Mobile push notifications
4. Notification preferences for users
5. Scheduled/automated notifications