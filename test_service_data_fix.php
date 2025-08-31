<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Service Data Fix</title>
    <link href="vendor/Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="vendor/js/jquery.min.js"></script>
    <script src="vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/js/modal.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>🔧 Service Data Fix Test</h2>
        <p>This tests if the service ID is now being passed correctly to the modal.</p>
        
        <div class="row">
            <div class="col-md-8">
                <h3>Services from Database</h3>
                <div id="testServicesContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border" role="status"></div>
                        <div>Loading services...</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <h3>Debug Output</h3>
                <div id="debugOutput" style="background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; height: 400px; overflow-y: auto;"></div>
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
        let debugLog = [];
        
        function addDebug(message) {
            debugLog.push(new Date().toLocaleTimeString() + ': ' + message);
            $('#debugOutput').html(debugLog.join('<br>'));
            $('#debugOutput').scrollTop($('#debugOutput')[0].scrollHeight);
            console.log(message);
        }

        $(document).ready(function() {
            addDebug('🔄 Loading services from database...');
            loadTestServices();
        });

        function loadTestServices() {
            $.ajax({
                url: 'controller/booking_services_contr.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'fetch_services'
                },
                success: function(result) {
                    addDebug('✅ Services loaded: ' + (result ? result.length : 0) + ' services');
                    
                    if (result === 'nodata' || !result || result.length === 0) {
                        $('#testServicesContainer').html('<div class="alert alert-warning">No services found</div>');
                        return;
                    }

                    // Debug the actual data structure
                    addDebug('📋 First service structure:');
                    addDebug(JSON.stringify(result[0], null, 2));

                    let html = '';
                    result.forEach((service, index) => {
                        // Test both id and service_id fields
                        const serviceId = service.id || service.service_id || 'UNDEFINED';
                        const serviceName = service.service_name || 'Unknown Service';
                        const servicePrice = service.price || 0;
                        
                        addDebug(`Service ${index + 1}: ID=${serviceId}, Name=${serviceName}`);

                        let imageSrc = '../vendor/images/headMassage.png'; // Default fallback
                        if (service.service_picture) {
                            if (service.service_picture.startsWith('http')) {
                                imageSrc = service.service_picture;
                            } else if (service.service_picture.startsWith('data:image')) {
                                imageSrc = service.service_picture;
                            } else {
                                imageSrc = `data:image/png;base64,${service.service_picture}`;
                            }
                        }

                        html += `
                            <div class="col-12 col-md-6 mb-3">
                                <div class="card h-100" style="cursor: pointer;" onclick="testServiceModal(${serviceId}, '${serviceName}', ${servicePrice}, '${service.description || ''}', '${imageSrc}')">
                                    <img src="${imageSrc}" class="card-img-top" style="height: 120px; object-fit: cover;" alt="${serviceName}" onerror="this.src='vendor/images/headMassage.png'">
                                    <div class="card-body">
                                        <h5 class="card-title">${serviceName}</h5>
                                        <p class="card-text small">${service.description || 'No description'}</p>
                                        <p class="card-text"><strong>₱${servicePrice}</strong> • ${service.per_minute || 0} min</p>
                                        <p class="card-text"><small class="text-muted">Service ID: <strong>${serviceId}</strong></small></p>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    $('#testServicesContainer').html('<div class="row">' + html + '</div>');
                    addDebug('✅ Services rendered with correct IDs');
                },
                error: function(xhr, status, error) {
                    addDebug('❌ Error loading services: ' + error);
                    $('#testServicesContainer').html('<div class="alert alert-danger">Error loading services</div>');
                }
            });
        }

        function testServiceModal(serviceId, serviceName, servicePrice, serviceDescription, serviceImage) {
            addDebug('🔄 Testing modal with service ID: ' + serviceId);
            addDebug('📋 Service data: {id: ' + serviceId + ', name: "' + serviceName + '", price: ' + servicePrice + '}');

            if (serviceId === 'UNDEFINED' || serviceId === undefined) {
                addDebug('❌ SERVICE ID IS UNDEFINED! This will cause therapist loading to fail.');
                alert('⚠️ Service ID is undefined! This is the bug that prevents therapists from loading.');
                return;
            }

            try {
                showGlobalModal('views/modal/user_modal-booking.php', {
                    id: serviceId,
                    name: serviceName,
                    price: servicePrice,
                    description: serviceDescription,
                    image: serviceImage
                }, function() {
                    addDebug('✅ Modal opened successfully');
                    
                    // Test if therapist loading is triggered
                    setTimeout(() => {
                        if (typeof loadTherapists === 'function') {
                            addDebug('🔄 Modal should now be loading therapists for service ID: ' + serviceId);
                        } else {
                            addDebug('❌ loadTherapists function not found in modal');
                        }
                    }, 1000);
                });
            } catch (error) {
                addDebug('❌ Error opening modal: ' + error.message);
            }
        }
    </script>

    <style>
        body { font-family: Arial, sans-serif; }
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    </style>
</body>
</html>