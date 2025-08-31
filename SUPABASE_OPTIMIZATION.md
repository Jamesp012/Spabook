# Supabase Performance Optimization Guide

This guide provides specific techniques for optimizing performance when using Supabase as your backend service.

## Understanding Supabase Architecture

Supabase is built on PostgreSQL and provides a REST API for database operations. When optimizing performance, it's important to understand:

1. **HTTP Overhead**: Each database operation requires a complete HTTP request/response cycle
2. **PostgreSQL Performance**: Underlying database performance still matters
3. **API Rate Limits**: Supabase may have rate limits that affect high-volume applications

## Database Query Optimization

### 1. Use Efficient Filters

```php
// INEFFICIENT: Filtering in PHP after fetching all records
$allUsers = $php_fetch('users');
$activeUsers = array_filter($allUsers, function($user) {
    return $user['status'] === 'active';
});

// EFFICIENT: Filtering at the database level
$activeUsers = $php_fetch('users', '*', ['status' => 'active']);
```

### 2. Select Only Needed Columns

```php
// INEFFICIENT: Fetching all columns
$users = $php_fetch('users');

// EFFICIENT: Selecting only needed columns
$users = $php_fetch('users', 'id, name, email');
```

### 3. Use PostgreSQL Functions

Supabase allows you to use PostgreSQL functions in your queries:

```php
// INEFFICIENT: Formatting dates in PHP
$bookings = $php_fetch('bookings');
foreach ($bookings as &$booking) {
    $booking['formatted_date'] = date('Y-m-d', strtotime($booking['created_at']));
}

// EFFICIENT: Using PostgreSQL functions
$query = "SELECT *, to_char(created_at, 'YYYY-MM-DD') as formatted_date FROM bookings";
$bookings = $php_fetch($query);
```

### 4. Implement Pagination

```php
// INEFFICIENT: Fetching all records
$allBookings = $php_fetch('bookings');

// EFFICIENT: Using pagination
$page = 1;
$pageSize = 20;
$offset = ($page - 1) * $pageSize;
$query = "SELECT * FROM bookings ORDER BY created_at DESC LIMIT $pageSize OFFSET $offset";
$bookings = $php_fetch($query);
```

### 5. Use JOINs Instead of Multiple Queries

```php
// INEFFICIENT: Multiple separate queries
$booking = $php_fetch('bookings', '*', ['id' => $bookingId])[0];
$user = $php_fetch('users', '*', ['id' => $booking['user_id']])[0];
$details = $php_fetch('booking_details', '*', ['booking_id' => $bookingId]);

// EFFICIENT: Using JOINs
$query = "
    SELECT b.*, u.name, u.email, bd.service_id, bd.price
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN booking_details bd ON b.id = bd.booking_id
    WHERE b.id = '$bookingId'
";
$bookingData = $php_fetch($query);
```

## Reducing HTTP Requests

### 1. Batch Operations

```php
// INEFFICIENT: Multiple separate requests
foreach ($userIds as $userId) {
    $user = $php_fetch('users', '*', ['id' => $userId]);
    // Process user...
}

// EFFICIENT: Single batch request
$query = "SELECT * FROM users WHERE id IN (" . implode(',', $userIds) . ")";
$users = $php_fetch($query);
foreach ($users as $user) {
    // Process user...
}
```

### 2. Implement Client-Side Caching

```php
// Check cache first
$cacheKey = "user_$userId";
$userData = Cache::get($cacheKey);

if ($userData === null) {
    // Cache miss - fetch from Supabase
    $userData = $php_fetch('users', '*', ['id' => $userId]);
    Cache::set($cacheKey, $userData, 300); // Cache for 5 minutes
}
```

### 3. Use Connection Pooling

Our optimized connection.php implements connection pooling for cURL handles:

```php
// Connection pooling - store and reuse curl handles
$curlHandles = [];

// In supabaseRequest function:
$handleKey = md5($url);
if (isset($curlHandles[$handleKey])) {
    $ch = $curlHandles[$handleKey];
    curl_setopt($ch, CURLOPT_URL, $url);
} else {
    $ch = curl_init($url);
    $curlHandles[$handleKey] = $ch;
}
```

## Supabase-Specific Optimizations

### 1. Use Appropriate Indexes

```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_bookings_user_id ON bookings(user_id);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_bookings_created_at ON bookings(created_at DESC);
```

### 2. Use RLS (Row Level Security) Policies

Supabase uses RLS policies to filter data at the database level:

```sql
-- Example RLS policy for bookings
CREATE POLICY "Users can view their own bookings" ON bookings
    FOR SELECT
    USING (auth.uid() = user_id);
```

### 3. Use Supabase Functions for Complex Operations

For complex operations, consider using Supabase Functions (PostgreSQL functions):

```sql
-- Create a function to get user bookings with details
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
        b.id as booking_id,
        b.created_at,
        b.status,
        s.name as service_name,
        bd.price
    FROM 
        bookings b
    JOIN 
        booking_details bd ON b.id = bd.booking_id
    JOIN 
        services s ON bd.service_id = s.id
    WHERE 
        b.user_id = get_user_bookings.user_id
    ORDER BY 
        b.created_at DESC;
END;
$$ LANGUAGE plpgsql;
```

Then call it from PHP:

```php
$query = "SELECT * FROM get_user_bookings('$userId')";
$bookings = $php_fetch($query);
```

### 4. Use Supabase Realtime for Live Updates

Instead of polling for updates, use Supabase Realtime:

```javascript
// JavaScript client-side code
const channel = supabase
  .channel('table-db-changes')
  .on(
    'postgres_changes',
    {
      event: '*',
      schema: 'public',
      table: 'bookings',
      filter: `user_id=eq.${userId}`
    },
    (payload) => {
      console.log('Change received!', payload)
      updateUI(payload.new);
    }
  )
  .subscribe()
```

## Monitoring and Debugging

### 1. Enable Query Logging

```php
// Log queries to a file
function logQuery($query, $params, $result) {
    $logFile = '../logs/query.log';
    $logEntry = date('Y-m-d H:i:s') . " | " . $query . " | " . json_encode($params) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
```

### 2. Use Supabase Dashboard

The Supabase dashboard provides:
- Query performance metrics
- API usage statistics
- Database health monitoring

### 3. Implement Client-Side Performance Monitoring

```javascript
// Measure API request times
const startTime = performance.now();
const response = await fetch('https://your-supabase-url.supabase.co/rest/v1/users', {
    headers: {
        'apikey': 'your-api-key',
        'Authorization': 'Bearer your-api-key'
    }
});
const endTime = performance.now();
console.log(`API call took ${endTime - startTime}ms`);
```

## Advanced Techniques

### 1. Implement Edge Functions

For frequently accessed data, consider using Supabase Edge Functions to move computation closer to users:

```javascript
// Example Edge Function
export async function handler(event, context) {
  const { data, error } = await supabase
    .from('popular_services')
    .select('*')
    .limit(10)
  
  return {
    statusCode: 200,
    body: JSON.stringify(data)
  }
}
```

### 2. Use Materialized Views for Complex Reports

```sql
CREATE MATERIALIZED VIEW booking_stats AS
SELECT 
    date_trunc('day', created_at) as booking_date,
    count(*) as total_bookings,
    sum(total_price) as total_revenue
FROM 
    bookings
GROUP BY 
    date_trunc('day', created_at);

-- Refresh the materialized view
REFRESH MATERIALIZED VIEW booking_stats;
```

### 3. Implement Database-Side Caching with pg_cron

```sql
-- Install pg_cron extension
CREATE EXTENSION pg_cron;

-- Schedule a job to refresh materialized views
SELECT cron.schedule('0 * * * *', 'REFRESH MATERIALIZED VIEW booking_stats');
```

## Conclusion

Optimizing Supabase performance requires a combination of:
1. Efficient database queries
2. Minimizing HTTP requests
3. Implementing caching
4. Using appropriate indexes
5. Leveraging PostgreSQL features

By applying these techniques, you can significantly improve the performance of your Supabase-powered application.