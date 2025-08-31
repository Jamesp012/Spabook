<?php
/**
 * Performance Monitoring Utility
 * 
 * This utility provides functions to monitor and log performance metrics
 * for database queries and API requests.
 */

class Performance {
    private static $startTimes = [];
    private static $queryCount = 0;
    private static $queryTimes = [];
    private static $logEnabled = false;
    private static $logFile = __DIR__ . '/../logs/performance.log';
    
    /**
     * Initialize performance monitoring
     * 
     * @param bool $enableLogging Whether to enable logging
     */
    public static function init($enableLogging = false) {
        self::$logEnabled = $enableLogging;
        self::$queryCount = 0;
        self::$queryTimes = [];
        self::$startTimes = [];
        
        // Start timing the request
        self::startTimer('request');
        
        // Create log directory if it doesn't exist
        if ($enableLogging) {
            $logDir = dirname(self::$logFile);
            if (!file_exists($logDir)) {
                mkdir($logDir, 0755, true);
            }
        }
    }
    
    /**
     * Start a timer
     * 
     * @param string $name Timer name
     */
    public static function startTimer($name) {
        self::$startTimes[$name] = microtime(true);
    }
    
    /**
     * End a timer and get the elapsed time
     * 
     * @param string $name Timer name
     * @return float Elapsed time in seconds
     */
    public static function endTimer($name) {
        if (!isset(self::$startTimes[$name])) {
            return 0;
        }
        
        $elapsed = microtime(true) - self::$startTimes[$name];
        unset(self::$startTimes[$name]);
        
        return $elapsed;
    }
    
    /**
     * Log a database query
     * 
     * @param string $query SQL query
     * @param float $time Query execution time
     */
    public static function logQuery($query, $time) {
        self::$queryCount++;
        self::$queryTimes[] = $time;
        
        if (self::$logEnabled) {
            $logEntry = date('Y-m-d H:i:s') . " | Query #" . self::$queryCount . " | " . 
                        number_format($time * 1000, 2) . "ms | " . $query . "\n";
            file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
        }
    }
    
    /**
     * Get performance metrics for the current request
     * 
     * @return array Performance metrics
     */
    public static function getMetrics() {
        $requestTime = self::endTimer('request');
        
        $metrics = [
            'request_time' => $requestTime,
            'query_count' => self::$queryCount,
            'total_query_time' => array_sum(self::$queryTimes),
            'avg_query_time' => self::$queryCount > 0 ? array_sum(self::$queryTimes) / self::$queryCount : 0,
            'max_query_time' => self::$queryCount > 0 ? max(self::$queryTimes) : 0,
            'memory_usage' => memory_get_usage(true) / 1024 / 1024, // MB
            'peak_memory_usage' => memory_get_peak_usage(true) / 1024 / 1024 // MB
        ];
        
        if (self::$logEnabled) {
            $logEntry = date('Y-m-d H:i:s') . " | Request completed | " . 
                        "Time: " . number_format($requestTime * 1000, 2) . "ms | " .
                        "Queries: " . self::$queryCount . " | " .
                        "Memory: " . number_format($metrics['memory_usage'], 2) . "MB\n";
            file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
        }
        
        return $metrics;
    }
    
    /**
     * Include performance metrics in the response
     * 
     * @param array $response Response array
     * @return array Response with metrics
     */
    public static function includeMetricsInResponse($response) {
        $metrics = self::getMetrics();
        
        if (is_array($response)) {
            $response['_performance'] = $metrics;
        }
        
        return $response;
    }
}