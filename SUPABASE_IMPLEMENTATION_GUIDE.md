# Supabase Implementation Guide for Performance Optimizations

This guide provides step-by-step instructions for implementing the performance optimizations in your Supabase environment.

## 1. Database Indexes

### Step 1: Use the Safe Version of the Database Optimization Script

1. Log in to your Supabase dashboard
2. Navigate to the SQL Editor
3. Open the file `database_optimization_safe.sql` from your project
4. Copy and paste the entire content into the SQL Editor
5. Click "Run" to execute the script

This script will:
- Check if each column exists before creating an index
- Only create indexes for tables and columns that actually exist in your database
- Avoid errors from attempting to create indexes on non-existent columns

### Step 2: Verify Index Creation

1. In the Supabase dashboard, go to "Database" → "Tables"
2. Select a table (e.g., "booking")
3. Click on the "Indexes" tab
4. Verify that the indexes were created successfully

## 2. Connection Optimization

The connection optimization is implemented on the PHP side, not in Supabase directly. To use it:

1. Log in to your admin panel
2. Navigate to "Toggle Performance Optimizations"
3. Click "Enable Optimizations"

This will:
- Switch to the optimized connection handler
- Enable connection pooling
- Implement request caching
- Add performance monitoring

## 3. Query Optimization

### Step 1: Identify Slow Queries

1. In your admin panel, go to "Performance Dashboard"
2. Look at the "Slow Queries" section to identify problematic queries

### Step 2: Optimize Identified Queries

For each slow query:

1. Use JOINs instead of multiple separate queries
2. Select only needed columns instead of using `SELECT *`
3. Add appropriate WHERE clauses to limit result sets
4. Use ORDER BY with indexed columns

Example:

```php
// Before
$bookings = $php_fetch('booking', '*', []);
foreach ($bookings as $booking) {
    $user = $php_fetch('users', '*', ['user_id' => $booking['user_id']]);
    $details = $php_fetch('booking_details', '*', ['booking_id' => $booking['bookingid']]);
    // Process data...
}

// After
$query = "
    SELECT b.bookingid, b.booking_status, b.date_created, 
           u.user_id, u.first_name, u.last_name, u.email,
           bd.service_id, bd.price
    FROM booking b
    JOIN users u ON b.user_id = u.user_id
    JOIN booking_details bd ON b.bookingid = bd.booking_id
    WHERE b.booking_status = 'Pending'
    ORDER BY b.date_created DESC
";
$results = $php_fetch($query);
```

## 4. Implement Caching for Expensive Operations

### Step 1: Identify Expensive Operations

Look for:
- Operations that are performed frequently
- Operations that return the same data for multiple users
- Operations that don't need real-time data

### Step 2: Add Caching

For each expensive operation:

```php
// Before
$result = $BookingModel->getAdminBookingRequests($php_fetch, 'booking', 'users', 'booking_details', 'services');

// After
$cacheKey = "admin_booking_requests";
$result = Cache::remember($cacheKey, function() use ($BookingModel, $php_fetch) {
    return $BookingModel->getAdminBookingRequests($php_fetch, 'booking', 'users', 'booking_details', 'services');
}, 30); // Cache for 30 seconds
```

### Step 3: Invalidate Cache When Data Changes

```php
// When a booking status changes
Cache::delete("admin_booking_requests");
Cache::delete("admin_booking_accepted");
Cache::delete("booking_details_admin_$bookingId");
```

## 5. Optimize Assets

### Step 1: Optimize Images

1. In your admin panel, go to "Image Optimization"
2. Set the quality level (85 is a good balance)
3. Click "Optimize All Images"

### Step 2: Optimize JavaScript and CSS

1. In your admin panel, go to "JavaScript Optimization" or "CSS Optimization"
2. Click "Analyze JavaScript Files" or "Analyze CSS Files"
3. Select the files you want to optimize
4. Click "Optimize Selected Files"

## 6. Monitor Performance

1. In your admin panel, go to "Performance Dashboard"
2. Review the performance metrics regularly
3. Look for:
   - Slow queries
   - High cache miss rates
   - Long request times

## 7. Supabase-Specific Optimizations

### Use PostgreSQL Functions for Complex Operations

If you have complex operations that are performed frequently, consider creating PostgreSQL functions:

1. In the Supabase SQL Editor, create a function:

```sql
CREATE OR REPLACE FUNCTION get_user_bookings(user_id UUID)
RETURNS TABLE (
    booking_id UUID,
    created_at TIMESTAMP,
    status TEXT,
    service_name TEXT,
    price NUMERIC
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        b.bookingid as booking_id,
        b.date_created as created_at,
        b.booking_status as status,
        s.name as service_name,
        bd.price
    FROM 
        booking b
    JOIN 
        booking_details bd ON b.bookingid = bd.booking_id
    JOIN 
        services s ON bd.service_id = s.id
    WHERE 
        b.user_id = get_user_bookings.user_id
    ORDER BY 
        b.date_created DESC;
END;
$$ LANGUAGE plpgsql;
```

2. Call the function from PHP:

```php
$query = "SELECT * FROM get_user_bookings('$userId')";
$bookings = $php_fetch($query);
```

### Use Materialized Views for Reports

If you have complex reports that don't need real-time data:

1. Create a materialized view:

```sql
CREATE MATERIALIZED VIEW booking_stats AS
SELECT 
    date_trunc('day', date_created) as booking_date,
    count(*) as total_bookings,
    sum(total_price) as total_revenue
FROM 
    booking
GROUP BY 
    date_trunc('day', date_created);
```

2. Schedule a refresh:

```sql
-- Install pg_cron extension (requires Supabase Pro)
CREATE EXTENSION pg_cron;

-- Schedule a job to refresh materialized views
SELECT cron.schedule('0 * * * *', 'REFRESH MATERIALIZED VIEW booking_stats');
```

3. Query the materialized view:

```php
$stats = $php_fetch('booking_stats');
```

## Conclusion

By implementing these optimizations, you should see significant improvements in your application's performance. Remember to:

1. Monitor performance regularly
2. Identify and fix slow queries
3. Use caching for expensive operations
4. Optimize assets for faster loading
5. Use PostgreSQL features for complex operations

If you encounter any issues or have questions, refer to the `SUPABASE_OPTIMIZATION.md` document for more detailed information.