<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test User Booking Modal</title>
    <link href="vendor/Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-icons-1.13.1/bootstrap-icons.css" rel="stylesheet">
    <script src="vendor/js/jquery.min.js"></script>
    <script src="vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/js/modal.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>🧪 User Booking Modal Test</h2>
        <p>This tests the therapist loading in user booking modal.</p>
        
        <div class="row">
            <div class="col-md-6">
                <h3>Available Services</h3>
                <div id="servicesContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border" role="status"></div>
                        <div>Loading services...</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h3>Test Results</h3>
                <div id="testResults"></div>
            </div>
        </div>
    </div>

    <!-- Global Modal -->
    <div class="modal fade" id="globalModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div id="globalModalContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            loadServices();
        });

        function loadServices() {
            $('#testResults').append('<p>🔄 Loading services...</p>');
            
            $.ajax({
                url: 'controller/booking_services_contr.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'fetch_services'
                },
                success: function(result) {
                    if (result === 'nodata' || !result || result.length === 0) {
                        $('#servicesContainer').html('<div class="alert alert-warning">No services found</div>');
                        $('#testResults').append('<p style="color: orange;">⚠️ No services available for testing</p>');
                    } else {
                        $('#testResults').append('<p style="color: green;">✅ Loaded ' + result.length + ' services</p>');
                        renderServices(result);
                        
                        // Test therapist loading for first service
                        if (result.length > 0) {
                            testTherapistLoading(result[0].id, result[0].service_name);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    $('#servicesContainer').html('<div class="alert alert-danger">Failed to load services</div>');
                    $('#testResults').append('<p style="color: red;">❌ Error loading services: ' + error + '</p>');
                }
            });
        }

        function renderServices(services) {
            let html = '';
            services.forEach(service => {
                let imageSrc = service.service_picture?.startsWith('http') ? 
                    service.service_picture : 
                    (service.service_picture?.startsWith('data:') ? 
                        service.service_picture : 
                        `data:image/png;base64,${service.service_picture}`
                    );

                html += `
                    <div class="card mb-3">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <img src="${imageSrc}" class="img-fluid rounded-start" style="height: 100px; object-fit: cover;" alt="${service.service_name}" onerror="this.src='vendor/images/headMassage.png'">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">${service.service_name}</h5>
                                    <p class="card-text">₱${service.price} • ${service.per_minute || 0} min</p>
                                    <button class="btn btn-primary btn-sm" onclick="testBookService(${service.id}, '${service.service_name}')">
                                        Test Book Service
                                    </button>
                                    <button class="btn btn-info btn-sm ms-2" onclick="testTherapistLoading(${service.id}, '${service.service_name}')">
                                        Test Therapist Loading
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#servicesContainer').html(html);
        }

        function testBookService(serviceId, serviceName) {
            $('#testResults').append(`<p>🔄 Testing booking modal for service: ${serviceName} (ID: ${serviceId})</p>`);
            
            try {
                // Set up service data that the modal expects
                window.selectedService = {
                    id: serviceId,
                    service_name: serviceName,
                    price: 500,
                    per_minute: 60
                };
                
                showGlobalModal('views/modal/user_modal-booking.php', {}, function() {
                    $('#testResults').append(`<p style="color: green;">✅ Booking modal opened for ${serviceName}</p>`);
                    
                    // Auto-test therapist loading in the modal
                    setTimeout(() => {
                        if (typeof loadTherapists === 'function') {
                            $('#testResults').append('<p>🔄 Auto-testing therapist loading in modal...</p>');
                            loadTherapists(serviceId);
                        } else {
                            $('#testResults').append('<p style="color: red;">❌ loadTherapists function not found in modal</p>');
                        }
                    }, 1000);
                });
            } catch (error) {
                $('#testResults').append(`<p style="color: red;">❌ Error opening booking modal: ${error.message}</p>`);
            }
        }

        function testTherapistLoading(serviceId, serviceName) {
            $('#testResults').append(`<p>🔄 Testing direct therapist loading for service: ${serviceName} (ID: ${serviceId})</p>`);
            
            $.ajax({
                url: 'controller/therapist_contr.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_therapists_by_service',
                    service_id: serviceId
                },
                success: function(result) {
                    if (result === 'nodata' || !result || result.length === 0) {
                        $('#testResults').append(`<p style="color: orange;">⚠️ No therapists found for ${serviceName}</p>`);
                    } else {
                        $('#testResults').append(`<p style="color: green;">✅ Found ${result.length} therapist(s) for ${serviceName}:</p>`);
                        result.forEach(therapist => {
                            $('#testResults').append(`<div style="margin-left: 20px;">- ${therapist.therapist_name}</div>`);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $('#testResults').append(`<p style="color: red;">❌ AJAX error loading therapists for ${serviceName}: ${error}</p>`);
                    $('#testResults').append(`<div style="margin-left: 20px; color: red;">Status: ${status}</div>`);
                    $('#testResults').append(`<div style="margin-left: 20px; color: red;">Response: ${xhr.responseText}</div>`);
                }
            });
        }

        // Test all therapists endpoint
        function testAllTherapists() {
            $('#testResults').append('<p>🔄 Testing get all therapists...</p>');
            
            $.ajax({
                url: 'controller/therapist_contr.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_all_therapists'
                },
                success: function(result) {
                    if (result === 'nodata' || !result || result.length === 0) {
                        $('#testResults').append('<p style="color: orange;">⚠️ No therapists found in system</p>');
                    } else {
                        $('#testResults').append(`<p style="color: green;">✅ Found ${result.length} total therapist(s) in system</p>`);
                    }
                },
                error: function(xhr, status, error) {
                    $('#testResults').append(`<p style="color: red;">❌ Error loading all therapists: ${error}</p>`);
                }
            });
        }

        // Auto-run some tests
        setTimeout(() => {
            testAllTherapists();
        }, 2000);
    </script>

    <style>
        body { font-family: Arial, sans-serif; }
        #testResults { 
            max-height: 400px; 
            overflow-y: auto; 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 5px; 
            border: 1px solid #dee2e6;
        }
        #testResults p { margin-bottom: 5px; }
    </style>
</body>
</html>