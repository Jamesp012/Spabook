<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Modal Variable Fix</title>
    <link href="../vendor/Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../vendor/js/jquery.min.js"></script>
    <script src="../vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/js/modal.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Test Modal Fix</h2>
        <p>This tests if the variable declaration fix resolves the modal loading issues.</p>
        
        <button class="btn btn-primary me-2" onclick="testBookingModal()">Test Booking Modal</button>
        <button class="btn btn-success me-2" onclick="testCheckoutModal()">Test Checkout Modal</button>
        <button class="btn btn-warning me-2" onclick="testPaymentModal()">Test Payment Modal</button>
        
        <div id="testResults" class="mt-4"></div>
    </div>

    <!-- Global Modal -->
    <div class="modal fade" id="globalModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div id="globalModalContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function testBookingModal() {
            $('#testResults').append('<p>🔄 Testing booking modal...</p>');
            
            try {
                showGlobalModal('modal/user_modal-booking.php', {}, function() {
                    $('#testResults').append('<p style="color: green;">✅ Booking modal loaded successfully!</p>');
                });
            } catch (error) {
                $('#testResults').append(`<p style="color: red;">❌ Booking modal error: ${error.message}</p>`);
            }
        }

        function testCheckoutModal() {
            $('#testResults').append('<p>🔄 Testing checkout modal...</p>');
            
            try {
                showGlobalModal('modal/user_modal-checkout.php', {}, function() {
                    $('#testResults').append('<p style="color: green;">✅ Checkout modal loaded successfully!</p>');
                });
            } catch (error) {
                $('#testResults').append(`<p style="color: red;">❌ Checkout modal error: ${error.message}</p>`);
            }
        }

        function testPaymentModal() {
            $('#testResults').append('<p>🔄 Testing payment modal...</p>');
            
            try {
                showGlobalModal('modal/user_modal-payment.php', {}, function() {
                    $('#testResults').append('<p style="color: green;">✅ Payment modal loaded successfully!</p>');
                });
            } catch (error) {
                $('#testResults').append(`<p style="color: red;">❌ Payment modal error: ${error.message}</p>`);
            }
        }

        function testMultipleLoads() {
            $('#testResults').append('<p>🔄 Testing multiple modal loads...</p>');
            
            // Load booking modal 3 times in sequence
            setTimeout(() => {
                showGlobalModal('modal/user_modal-booking.php');
                setTimeout(() => {
                    showGlobalModal('modal/user_modal-booking.php');
                    setTimeout(() => {
                        showGlobalModal('modal/user_modal-booking.php', {}, function() {
                            $('#testResults').append('<p style="color: green;">✅ Multiple loads test passed!</p>');
                        });
                    }, 500);
                }, 500);
            }, 500);
        }

        // Auto-test multiple loads
        $(document).ready(function() {
            setTimeout(testMultipleLoads, 2000);
        });
    </script>
</body>
</html>