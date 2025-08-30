<?php
// Simple debug script to test the manage services functionality
?>
<!DOCTYPE html>
<html>
<head>
    <title>🐛 Debug Manage Services</title>
    <link href="vendor/Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-icons-1.13.1/bootstrap-icons.css" rel="stylesheet">
    <script src="vendor/js/jquery.min.js"></script>
    <script src="vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/js/modal.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2>🐛 Debug Manage Services Button</h2>
        
        <div class="alert alert-info">
            <h5>Test the exact same flow as your admin page:</h5>
            <ol>
                <li>Click the test button below</li>
                <li>Check browser console (F12 → Console)</li>
                <li>See what errors appear</li>
            </ol>
        </div>

        <!-- Test button that mimics the real one -->
        <button class="btn btn-primary btn-manage-services" data-bookingid="1">
            <i class="bi bi-list-check me-1"></i>
            Test Manage Services (Booking #1)
        </button>

        <div class="mt-3">
            <h5>Console Output:</h5>
            <div id="console-output" class="bg-light p-3 border" style="font-family: monospace; font-size: 0.9rem; max-height: 400px; overflow-y: auto;">
                <!-- Console messages will appear here -->
            </div>
        </div>
    </div>

    <!-- Global Modal (same as in your main layout) -->
    <div class="modal fade" id="globalModal" tabindex="-1" aria-labelledby="globalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="globalModalContent">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Override console.log to display in our debug area
        const originalLog = console.log;
        const originalError = console.error;
        
        function addToDebugConsole(type, message) {
            const timestamp = new Date().toLocaleTimeString();
            const color = type === 'error' ? 'red' : type === 'warn' ? 'orange' : 'black';
            $('#console-output').append(`<div style="color: ${color};">[${timestamp}] ${type.toUpperCase()}: ${message}</div>`);
            $('#console-output').scrollTop($('#console-output')[0].scrollHeight);
        }
        
        console.log = function(...args) {
            originalLog.apply(console, args);
            addToDebugConsole('log', args.join(' '));
        };
        
        console.error = function(...args) {
            originalError.apply(console, args);
            addToDebugConsole('error', args.join(' '));
        };

        $(document).ready(function() {
            console.log('🔧 Debug page loaded');
            
            // Handle Manage Services button click (same as admin page)
            $(document).on('click', '.btn-manage-services', function (e) {
                e.preventDefault();
                const bookingid = $(this).data('bookingid');
                console.log('🔧 Opening service management for booking:', bookingid);
                
                // Test the exact same call
                showGlobalModal('views/modal/admin_modal-booking-services.php', { bookingid: bookingid });
            });
            
            console.log('✅ Event handlers attached');
        });
    </script>
</body>
</html>