# Notification System Setup

## Database Setup

Before using the notification system, you need to create the notification table in your Supabase database.

1. Log in to your Supabase dashboard
2. Go to the SQL Editor
3. Copy and paste the SQL from `notification_table.sql` file
4. Run the SQL to create the notification table and set up the necessary permissions

## Troubleshooting

If you encounter the error "Failed to load booking request" or similar JSON parsing errors, it's likely because the notification table doesn't exist in your database yet.

### Common Issues:

1. **Missing notification table**: The most common issue is that the notification table doesn't exist in your database. Follow the setup instructions above to create it.

2. **Permission issues**: Make sure the Row Level Security (RLS) policies are correctly set up as specified in the SQL file.

3. **Circular dependencies**: If you see PHP errors related to circular dependencies, make sure you're not requiring notification_contr.php in files that are already required by notification_contr.php.

### Testing the Notification System

After setting up the notification table, you can test if it's working correctly:

1. Create a new booking
2. Check if notifications appear in the user's notification panel
3. Accept or reject a booking as an admin and verify that the user receives a notification

## Notification Types

The system supports four types of notifications:

- `info`: General information (blue)
- `success`: Success messages (green)
- `warning`: Warning messages (yellow)
- `error`: Error messages (red)

## Customizing Notifications

You can customize the notification messages by editing the `createBookingStatusNotification` function in `controller/notification_contr.php`.

## Adding Notifications to New Pages

To add the notification badge to any page:

```php
<?php 
$isAdmin = true; // Set to true for admin pages, false for user pages
include_once '../components/notification_badge.php'; 
?>
```

## Creating Custom Notifications

To create a custom notification from any controller:

```php
// Make sure notification table exists and controller is included
if ($notificationTableExists) {
    require_once '../controller/notification_contr.php';
    
    // Create a notification
    createNotification(
        $user_id,
        'Notification Title',
        'Notification message goes here',
        'info', // Type: 'info', 'success', 'warning', 'error'
        [] // Optional metadata
    );
}
```