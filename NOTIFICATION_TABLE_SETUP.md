# 📋 Create Notification Table - Step by Step

## 🎯 Current Status
- ✅ Database connection working
- ❌ Notification table missing
- 🔧 Need to create table with your exact structure

## 📋 Your Table Structure
```sql
notification
- notificationid: serial PRIMARY KEY
- user_id: uuid REFERENCES users(user_id) ON DELETE CASCADE
- title: varchar(255) NOT NULL
- message: text
- type: varchar(50) -- 'info' | 'warning' | 'error' | 'success'
- is_read: boolean DEFAULT false
- created_at: timestamptz DEFAULT current_timestamp
- read_at: timestamptz
- metadata: jsonb
```

## 🚀 Method 1: Supabase Dashboard (Recommended)

### Step 1: Open Supabase
1. Go to: https://supabase.com/dashboard/
2. Select your **SpaBook project**
3. Click **"SQL Editor"** in the left sidebar

### Step 2: Create Table
1. **Copy** the entire SQL from `create_notification_table.sql` file
2. **Paste** it in the SQL Editor
3. Click **"Run"** button

### Step 3: Verify Creation
1. Check if query runs without errors
2. Go to **"Table Editor"** tab
3. Look for **"notification"** table in the list

## 🚀 Method 2: Quick Copy-Paste SQL

If you can't find the file, copy this SQL directly:

```sql
-- Create notification table
CREATE TABLE notification (
    notificationid SERIAL PRIMARY KEY,
    user_id UUID REFERENCES users(user_id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    type VARCHAR(50),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMPTZ,
    metadata JSONB
);

-- Create indexes for performance
CREATE INDEX idx_notification_user_id ON notification(user_id);
CREATE INDEX idx_notification_is_read ON notification(is_read);
CREATE INDEX idx_notification_created_at ON notification(created_at DESC);
CREATE INDEX idx_notification_combined ON notification(user_id, is_read, created_at DESC);

-- Enable Row Level Security
ALTER TABLE notification ENABLE ROW LEVEL SECURITY;

-- Policies for security
CREATE POLICY "Users can view own notifications" ON notification
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Service role full access" ON notification
    FOR ALL USING (auth.role() = 'service_role');

-- Grant permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON notification TO authenticated;
GRANT USAGE, SELECT ON SEQUENCE notification_notificationid_seq TO authenticated;
```

## 🧪 Test After Creation

### Method 1: Quick Test
Run: `http://localhost/Spabook/test_table_creation.php`

Should show:
- ✅ Database connection successful
- ✅ Notification table exists!
- ✅ Successfully inserted test notification

### Method 2: Full Test
Run: `http://localhost/Spabook/debug_notification.php`

Should show:
- ✅ Notification table exists
- ✅ Notification model loaded successfully
- ✅ AJAX call successful

## 🎉 After Table is Created

### Test the Notification System:
1. **Login as a user**
2. **Go to notifications page** - should load without errors
3. **Create a booking** - should get "Booking Request Submitted" notification
4. **Login as admin** and approve booking - user gets "Booking Confirmed!" notification

### Enable Automatic Reminders:
1. **Set up cron job** or **manual API calls** for appointment reminders
2. **Test with bookings** scheduled for tomorrow and next hour

## 🚨 Troubleshooting

### If SQL fails:
- Check if `users` table exists and has `user_id` column
- Verify you have admin permissions in Supabase
- Try creating without policies first, add them later

### If notifications still don't work:
- Clear browser cache
- Check browser console for JavaScript errors
- Verify user is logged in with valid session

### If AJAX calls fail:
- Check file paths in `notification_contr.php`
- Verify `utils/cache.php` exists
- Test with simplified controller

## 📞 Need Help?

If you're still having issues:
1. **Check Supabase logs** for any error messages
2. **Run test_table_creation.php** for detailed diagnostics
3. **Share the exact error message** from browser console or PHP logs