# 🚨 NOTIFICATION SYSTEM FIX

## Problem
Getting error: "Error connecting to the server. Please check your connection and try again."

## Root Cause
❌ **Notification table doesn't exist in database**

## ✅ SOLUTION

### Step 1: Create Notification Table
1. **Open Supabase Dashboard**: https://supabase.com/dashboard/
2. **Go to your SpaBook project**
3. **Click "SQL Editor"** in left sidebar
4. **Copy & paste** the SQL from `notification_table.sql` file
5. **Click "Run"** to execute

### Step 2: Verify Fix
1. **Refresh the notification page** in your SpaBook app
2. **Run test**: http://localhost/Spabook/test_notifications.php
3. **Should now see**: ✅ Notification table exists

### Step 3: Test Notifications
1. **Create a booking** as a user
2. **Check notifications page** - should see "Booking Request Submitted"
3. **Login as admin** and approve/reject the booking
4. **Check user notifications** - should see status update

## Alternative: Quick SQL Execution
```sql
-- Copy this SQL and run in Supabase SQL Editor
CREATE TABLE notification (
    notificationid SERIAL PRIMARY KEY,
    user_id uuid NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    type VARCHAR(50),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMPTZ,
    metadata JSONB,
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Create indexes
CREATE INDEX idx_notification_user_id ON notification(user_id);
CREATE INDEX idx_notification_is_read ON notification(is_read);
CREATE INDEX idx_notification_created_at ON notification(created_at DESC);
CREATE INDEX idx_notification_combined ON notification(user_id, is_read, created_at DESC);

-- Enable RLS
ALTER TABLE notification ENABLE ROW LEVEL SECURITY;

-- Policy: Users can view their own notifications
CREATE POLICY "Users can view their own notifications" 
ON notification FOR SELECT 
USING (auth.uid() = user_id);

-- Policy: Service role can do everything
CREATE POLICY "Service role can do everything" 
ON notification 
USING (auth.role() = 'service_role');

-- Grant permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON notification TO authenticated;
GRANT USAGE, SELECT ON SEQUENCE notification_notificationid_seq TO authenticated;
```

## 🧪 Test After Fix
Run: `http://localhost/Spabook/debug_notification.php`

Should show:
- ✅ Database connection loaded successfully
- ✅ Notification table exists
- ✅ Notification model loaded successfully
- ✅ AJAX call successful

## Need Help?
If still having issues:
1. Check Supabase project connection in `config/credentials.php`
2. Verify user permissions in Supabase
3. Check browser console for JavaScript errors