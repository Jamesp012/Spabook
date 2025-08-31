<?php
/**
 * Simple caching system for database queries
 */

class Cache {
    private static $cacheDir = __DIR__ . '/../cache/';
    private static $defaultExpiry = 300; // 5 minutes in seconds
    
    /**
     * Initialize the cache system
     */
    public static function init() {
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
        
        // Create .htaccess to protect cache directory
        $htaccess = self::$cacheDir . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all");
        }
    }
    
    /**
     * Get data from cache
     * 
     * @param string $key Cache key
     * @return mixed|null Cached data or null if not found/expired
     */
    public static function get($key) {
        $filename = self::getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $content = file_get_contents($filename);
        $data = json_decode($content, true);
        
        // Check if cache has expired
        if ($data['expires'] < time()) {
            self::delete($key);
            return null;
        }
        
        return $data['data'];
    }
    
    /**
     * Store data in cache
     * 
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int $expiry Cache expiry time in seconds
     * @return bool Success status
     */
    public static function set($key, $data, $expiry = null) {
        self::init();
        
        if ($expiry === null) {
            $expiry = self::$defaultExpiry;
        }
        
        $cacheData = [
            'expires' => time() + $expiry,
            'data' => $data
        ];
        
        $filename = self::getCacheFilename($key);
        return file_put_contents($filename, json_encode($cacheData)) !== false;
    }
    
    /**
     * Delete a cache entry
     * 
     * @param string $key Cache key
     * @return bool Success status
     */
    public static function delete($key) {
        $filename = self::getCacheFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    /**
     * Clear all cache entries
     * 
     * @return bool Success status
     */
    public static function clear() {
        self::init();
        
        $files = glob(self::$cacheDir . '*.cache');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    /**
     * Get cache filename for a key
     * 
     * @param string $key Cache key
     * @return string Cache filename
     */
    private static function getCacheFilename($key) {
        return self::$cacheDir . md5($key) . '.cache';
    }
    
    /**
     * Get or set cached data with callback
     * 
     * @param string $key Cache key
     * @param callable $callback Function to generate data if not in cache
     * @param int $expiry Cache expiry time in seconds
     * @return mixed Cached or generated data
     */
    public static function remember($key, $callback, $expiry = null) {
        $data = self::get($key);
        
        if ($data !== null) {
            return $data;
        }
        
        $data = call_user_func($callback);
        self::set($key, $data, $expiry);
        
        return $data;
    }
}