<?php
// Test script for performance and cache utilities

// Include utilities
require_once 'utils/performance.php';
require_once 'utils/cache.php';

// Initialize performance monitoring
Performance::init(true);

// Test performance monitoring
echo "<h1>Testing Performance Monitoring</h1>";

// Test timer
Performance::startTimer('test_operation');
sleep(1); // Simulate work
$time = Performance::endTimer('test_operation');
echo "<p>Test operation took " . ($time * 1000) . " ms</p>";

// Test query logging
Performance::logQuery("SELECT * FROM test_table WHERE id = 1", 0.05);
Performance::logQuery("INSERT INTO test_table (name) VALUES ('test')", 0.02);

// Get metrics
$metrics = Performance::getMetrics();
echo "<h2>Performance Metrics</h2>";
echo "<pre>" . print_r($metrics, true) . "</pre>";

// Test cache
echo "<h1>Testing Cache</h1>";

// Initialize cache
Cache::init();

// Test setting cache
$testData = ['name' => 'Test User', 'email' => 'test@example.com'];
$cacheResult = Cache::set('test_key', $testData, 60);
echo "<p>Cache set result: " . ($cacheResult ? 'Success' : 'Failed') . "</p>";

// Test getting cache
$cachedData = Cache::get('test_key');
echo "<h2>Cached Data</h2>";
echo "<pre>" . print_r($cachedData, true) . "</pre>";

// Test remember function
$rememberedData = Cache::remember('remembered_key', function() {
    return ['generated' => 'data', 'timestamp' => time()];
}, 60);
echo "<h2>Remembered Data</h2>";
echo "<pre>" . print_r($rememberedData, true) . "</pre>";

// Check cache directory
echo "<h2>Cache Directory</h2>";
$cacheFiles = glob('cache/*.cache');
echo "<p>Cache files: " . count($cacheFiles) . "</p>";
foreach ($cacheFiles as $file) {
    echo "<p>" . basename($file) . " - " . round(filesize($file) / 1024, 2) . " KB</p>";
}

// Check logs directory
echo "<h2>Logs Directory</h2>";
$logFiles = glob('logs/*.log');
echo "<p>Log files: " . count($logFiles) . "</p>";
foreach ($logFiles as $file) {
    echo "<p>" . basename($file) . " - " . round(filesize($file) / 1024, 2) . " KB</p>";
    
    // Show last few lines of log
    $log = file_get_contents($file);
    $lines = explode("\n", $log);
    $lastLines = array_slice($lines, -5);
    echo "<pre>" . implode("\n", $lastLines) . "</pre>";
}