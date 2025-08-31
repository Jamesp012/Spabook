# Performance Optimization Guide

This document provides an overview of the performance optimizations implemented in the Spabook system and instructions for maintaining optimal performance.

## Database Optimizations

### Indexes

We've added several indexes to improve query performance:

1. **Run the SQL scripts:**
   - `notification_table.sql` - Creates the notification table with proper indexes
   - `database_optimization.sql` - Adds indexes to all major tables

2. **Key indexes added:**
   - Booking status indexes for faster filtering
   - User ID indexes for faster user-related queries
   - Created date indexes for faster sorting
   - Combined indexes for common query patterns

### Query Optimizations

1. **Reduced N+1 Query Problems:**
   - Replaced multiple separate queries with JOINs
   - Implemented batch fetching for related data

2. **Optimized SELECT Statements:**
   - Only selecting needed columns instead of using `SELECT *`
   - Using database functions for calculations instead of PHP

3. **Improved Query Structure:**
   - Added proper WHERE clauses to limit result sets
   - Using ORDER BY with indexed columns

## Caching System

A comprehensive caching system has been implemented:

### Cache Configuration

- Cache files are stored in the `/cache` directory
- Default cache expiration is 5 minutes
- Cache keys are based on query parameters

### Using the Cache

```php
// Simple usage
$data = Cache::get('my_cache_key');
if ($data === null) {
    $data = // ... expensive operation ...
    Cache::set('my_cache_key', $data, 300); // 5 minutes
}

// Using the remember function
$data = Cache::remember('my_cache_key', function() {
    return // ... expensive operation ...
}, 300);
```

### Cache Invalidation

The system automatically invalidates relevant caches when data changes:

- When a booking status changes, related caches are cleared
- When notifications are marked as read, user notification caches are cleared
- When new bookings are created, admin booking list caches are cleared

### Manual Cache Management

Administrators can manage the cache through:
- `/admin/analyze_performance.php` - View cache statistics
- `/admin/clear_cache.php` - Clear all cache files

## Database Connection Optimization

The `DBOptimizer` class provides optimized versions of database functions:

```php
// Instead of using $php_fetch directly
$result = DBOptimizer::fetch('booking', '*', ['user_id' => $user_id], 30);

// Batch fetching multiple items
$users = DBOptimizer::batchFetch('users', 'user_id', $user_ids);
```

## Performance Monitoring

The system includes tools for monitoring performance:

### Enabling Performance Monitoring

```php
require_once '../utils/performance.php';
Performance::init(true); // true enables logging
```

### Measuring Specific Operations

```php
Performance::startTimer('my_operation');
// ... code to measure ...
$time = Performance::endTimer('my_operation');
echo "Operation took " . ($time * 1000) . " ms";
```

### Performance Analysis

Administrators can view detailed performance metrics at:
- `/admin/analyze_performance.php`

This page shows:
- Table sizes and index usage
- Slow queries
- Cache statistics
- Memory usage

## Best Practices

1. **Use JOINs instead of multiple queries**
   - When fetching related data, use JOINs instead of separate queries

2. **Implement pagination**
   - Always use LIMIT and OFFSET for large result sets
   - Implement "load more" or pagination UI

3. **Use the caching system**
   - Cache expensive queries
   - Use shorter cache times for frequently changing data
   - Invalidate caches when data changes

4. **Monitor performance**
   - Regularly check the performance analysis page
   - Look for slow queries and optimize them
   - Add indexes for frequently queried columns

5. **Optimize images and assets**
   - Compress images before uploading
   - Use appropriate image formats (JPEG for photos, PNG for graphics)
   - Consider implementing lazy loading for images

## Troubleshooting

If you encounter performance issues:

1. **Check the logs**
   - Review `/logs/performance.log` for slow queries

2. **Clear the cache**
   - Use `/admin/clear_cache.php` to clear all caches

3. **Analyze database performance**
   - Use `/admin/analyze_performance.php` to identify bottlenecks

4. **Check for missing indexes**
   - Look for tables with high sequential scan counts
   - Add indexes for frequently queried columns

5. **Optimize large queries**
   - Break down complex queries
   - Use JOINs instead of subqueries when possible
   - Only select needed columns