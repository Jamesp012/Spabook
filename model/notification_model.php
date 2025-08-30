<?php
require_once '../config/connection.php';

class NotificationModel {
    /**
     * Create a new notification
     * 
     * @param callable $php_insert The insert function from connection.php
     * @param string $table The table name (notification)
     * @param array $data The notification data
     * @return array The result of the insert operation
     */
    public function createNotification($php_insert, $table, $data) {
        return $php_insert($table, $data);
    }
    
    /**
     * Get notifications for a specific user
     * 
     * @param callable $php_fetch The fetch function from connection.php
     * @param string $table The table name (notification)
     * @param string $user_id The user ID
     * @param int $limit Optional limit of notifications to return
     * @param bool $unread_only Whether to return only unread notifications
     * @return array The notifications
     */
    public function getUserNotifications($php_fetch, $table, $user_id, $limit = 20, $unread_only = false) {
        // Use a more efficient query that selects only needed columns
        $query = "SELECT 
            notificationid, 
            user_id, 
            title, 
            message, 
            type, 
            is_read, 
            created_at, 
            read_at, 
            metadata,
            to_char(created_at, 'Mon DD, YYYY at HH12:MI AM') as formatted_time,
            CASE
                WHEN NOW() - created_at < INTERVAL '1 minute' THEN 'Just now'
                WHEN NOW() - created_at < INTERVAL '1 hour' THEN CONCAT(EXTRACT(MINUTE FROM NOW() - created_at)::INTEGER, ' minute(s) ago')
                WHEN NOW() - created_at < INTERVAL '1 day' THEN CONCAT(EXTRACT(HOUR FROM NOW() - created_at)::INTEGER, ' hour(s) ago')
                WHEN NOW() - created_at < INTERVAL '7 days' THEN CONCAT(EXTRACT(DAY FROM NOW() - created_at)::INTEGER, ' day(s) ago')
                WHEN NOW() - created_at < INTERVAL '30 days' THEN CONCAT(EXTRACT(WEEK FROM NOW() - created_at)::INTEGER, ' week(s) ago')
                WHEN NOW() - created_at < INTERVAL '365 days' THEN CONCAT(EXTRACT(MONTH FROM NOW() - created_at)::INTEGER, ' month(s) ago')
                ELSE CONCAT(EXTRACT(YEAR FROM NOW() - created_at)::INTEGER, ' year(s) ago')
            END as relative_time
        FROM $table 
        WHERE user_id = '$user_id'";
        
        if ($unread_only) {
            $query .= " AND is_read = false";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        if ($limit > 0) {
            $query .= " LIMIT $limit";
        }
        
        return $php_fetch($query);
    }
    
    /**
     * Mark a notification as read
     * 
     * @param callable $php_update The update function from connection.php
     * @param string $table The table name (notification)
     * @param int $notification_id The notification ID
     * @return array The result of the update operation
     */
    public function markAsRead($php_update, $table, $notification_id) {
        // The correct format for filters is an array of key-value pairs
        return $php_update(
            $table,
            ['is_read' => true, 'read_at' => 'NOW()'],
            ['notificationid' => $notification_id]
        );
    }
    
    /**
     * Mark all notifications as read for a user
     * 
     * @param callable $php_update The update function from connection.php
     * @param string $table The table name (notification)
     * @param string $user_id The user ID
     * @return array The result of the update operation
     */
    public function markAllAsRead($php_update, $table, $user_id) {
        // For complex filters like this, we need to modify the connection.php approach
        // or use a custom query. For now, let's use a workaround:
        
        // First, get all unread notifications for the user
        global $php_fetch;
        $unreadNotifications = $php_fetch($table, '*', ['user_id' => $user_id, 'is_read' => 'false']);
        
        $results = [];
        
        // Then update each notification individually
        if (!empty($unreadNotifications)) {
            foreach ($unreadNotifications as $notification) {
                $result = $php_update(
                    $table,
                    ['is_read' => true, 'read_at' => 'NOW()'],
                    ['notificationid' => $notification['notificationid']]
                );
                $results[] = $result;
            }
        }
        
        return $results;
    }
    
    /**
     * Delete a notification
     * 
     * @param callable $php_delete The delete function from connection.php
     * @param string $table The table name (notification)
     * @param int $notification_id The notification ID
     * @return array The result of the delete operation
     */
    public function deleteNotification($php_delete, $table, $notification_id) {
        // The correct format for filters is an array of key-value pairs
        return $php_delete($table, ['notificationid' => $notification_id]);
    }
    
    /**
     * Get unread notification count for a user
     * 
     * @param callable $php_fetch The fetch function from connection.php
     * @param string $table The table name (notification)
     * @param string $user_id The user ID
     * @return int The number of unread notifications
     */
    public function getUnreadCount($php_fetch, $table, $user_id) {
        $query = "SELECT COUNT(*) as count FROM $table WHERE user_id = '$user_id' AND is_read = false";
        $result = $php_fetch($query);
        
        if (isset($result[0]['count'])) {
            return $result[0]['count'];
        }
        
        return 0;
    }
    
    /**
     * Get all admin notifications (system-wide notifications)
     * 
     * @param callable $php_fetch The fetch function from connection.php
     * @param string $table The table name (notification)
     * @param int $limit Optional limit of notifications to return
     * @return array The admin notifications
     */
    public function getAdminNotifications($php_fetch, $table, $limit = 50) {
        // Use a more efficient query with calculated fields
        $query = "SELECT 
            n.notificationid, 
            n.user_id, 
            n.title, 
            n.message, 
            n.type, 
            n.is_read, 
            n.created_at, 
            n.read_at, 
            n.metadata,
            u.first_name, 
            u.last_name, 
            u.email,
            to_char(n.created_at, 'Mon DD, YYYY at HH12:MI AM') as formatted_time,
            CASE
                WHEN NOW() - n.created_at < INTERVAL '1 minute' THEN 'Just now'
                WHEN NOW() - n.created_at < INTERVAL '1 hour' THEN CONCAT(EXTRACT(MINUTE FROM NOW() - n.created_at)::INTEGER, ' minute(s) ago')
                WHEN NOW() - n.created_at < INTERVAL '1 day' THEN CONCAT(EXTRACT(HOUR FROM NOW() - n.created_at)::INTEGER, ' hour(s) ago')
                WHEN NOW() - n.created_at < INTERVAL '7 days' THEN CONCAT(EXTRACT(DAY FROM NOW() - n.created_at)::INTEGER, ' day(s) ago')
                WHEN NOW() - n.created_at < INTERVAL '30 days' THEN CONCAT(EXTRACT(WEEK FROM NOW() - n.created_at)::INTEGER, ' week(s) ago')
                WHEN NOW() - n.created_at < INTERVAL '365 days' THEN CONCAT(EXTRACT(MONTH FROM NOW() - n.created_at)::INTEGER, ' month(s) ago')
                ELSE CONCAT(EXTRACT(YEAR FROM NOW() - n.created_at)::INTEGER, ' year(s) ago')
            END as relative_time
        FROM $table n 
        LEFT JOIN users u ON n.user_id = u.user_id 
        ORDER BY n.created_at DESC";
        
        if ($limit > 0) {
            $query .= " LIMIT $limit";
        }
        
        return $php_fetch($query);
    }
}
?>