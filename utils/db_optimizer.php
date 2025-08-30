<?php
/**
 * Database Query Optimizer
 * 
 * This utility provides optimized versions of database functions that use caching
 * and batching to improve performance.
 */

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/cache.php';

class DBOptimizer {
    /**
     * Cached version of php_fetch
     * 
     * @param string $table Table name or SQL query
     * @param string|array $select Columns to select or additional parameters
     * @param array $filters Filter conditions
     * @param int $cache_time Cache time in seconds (0 to disable caching)
     * @return array Query results
     */
    public static function fetch($table, $select = '*', $filters = [], $cache_time = 30) {
        global $php_fetch;
        
        // Generate cache key
        $cache_key = 'db_fetch_' . md5($table . json_encode($select) . json_encode($filters));
        
        // Use caching if enabled
        if ($cache_time > 0) {
            return Cache::remember($cache_key, function() use ($php_fetch, $table, $select, $filters) {
                return $php_fetch($table, $select, $filters);
            }, $cache_time);
        } else {
            return $php_fetch($table, $select, $filters);
        }
    }
    
    /**
     * Batch fetch multiple items at once
     * 
     * @param string $table Table name
     * @param string $id_column ID column name
     * @param array $ids Array of IDs to fetch
     * @param string $select Columns to select
     * @param int $cache_time Cache time in seconds
     * @return array Results indexed by ID
     */
    public static function batchFetch($table, $id_column, $ids, $select = '*', $cache_time = 30) {
        global $php_fetch;
        
        // Remove duplicates
        $ids = array_unique($ids);
        
        if (empty($ids)) {
            return [];
        }
        
        // Generate cache key
        $cache_key = 'db_batch_' . md5($table . $id_column . json_encode($ids) . $select);
        
        $fetchFunction = function() use ($php_fetch, $table, $id_column, $ids, $select) {
            // Convert IDs to string for SQL
            $ids_str = "'" . implode("','", $ids) . "'";
            
            // Build query
            $query = "SELECT $select FROM $table WHERE $id_column IN ($ids_str)";
            
            // Execute query
            $results = $php_fetch($query);
            
            // Index results by ID
            $indexed = [];
            foreach ($results as $row) {
                $indexed[$row[$id_column]] = $row;
            }
            
            return $indexed;
        };
        
        // Use caching if enabled
        if ($cache_time > 0) {
            return Cache::remember($cache_key, $fetchFunction, $cache_time);
        } else {
            return $fetchFunction();
        }
    }
    
    /**
     * Optimized version of php_insert that invalidates relevant caches
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @return array Insert result
     */
    public static function insert($table, $data) {
        global $php_insert;
        
        $result = $php_insert($table, $data);
        
        // Invalidate caches related to this table
        self::invalidateTableCaches($table);
        
        return $result;
    }
    
    /**
     * Optimized version of php_update that invalidates relevant caches
     * 
     * @param string $table Table name
     * @param array $data Data to update
     * @param array $filters Filter conditions
     * @return array Update result
     */
    public static function update($table, $data, $filters) {
        global $php_update;
        
        $result = $php_update($table, $data, $filters);
        
        // Invalidate caches related to this table
        self::invalidateTableCaches($table);
        
        return $result;
    }
    
    /**
     * Optimized version of php_delete that invalidates relevant caches
     * 
     * @param string $table Table name
     * @param array $filters Filter conditions
     * @return array Delete result
     */
    public static function delete($table, $filters) {
        global $php_delete;
        
        $result = $php_delete($table, $filters);
        
        // Invalidate caches related to this table
        self::invalidateTableCaches($table);
        
        return $result;
    }
    
    /**
     * Invalidate all caches related to a specific table
     * 
     * @param string $table Table name
     */
    private static function invalidateTableCaches($table) {
        // Get all cache keys
        $cache_dir = __DIR__ . '/../cache/';
        $files = glob($cache_dir . '*.cache');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            
            // If the cache contains data for this table, delete it
            if (strpos($content, $table) !== false) {
                unlink($file);
            }
        }
    }
}