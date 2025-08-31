<!DOCTYPE html>
<html>
<head>
    <title>Test Controller Add Therapist</title>
    <script src="vendor/js/jquery.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .results { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px; }
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 15px; }
        .checkbox-item { display: flex; align-items: center; }
        .service-checkbox { width: auto; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Controller Add Therapist</h1>
        
        <div style="background: #e7f3ff; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            <strong>📋 This tests:</strong>
            <ul>
                <li>Direct AJAX call to therapist controller</li>
                <li>Service loading from services controller</li>
                <li>Multiple service selection</li>
                <li>Database insertion</li>
            </ul>
        </div>

        <!-- Test Form -->
        <div class="form-section">
            <h2>➕ Test Add Therapist</h2>
            <form id="testAddForm">
                <div class="form-group">
                    <label>Therapist Name:</label>
                    <input type="text" id="therapist_name" value="Test Therapist Ajax" required>
                </div>
                
                <div class="form-group">
                    <label>Description:</label>
                    <textarea id="therapist_desc" rows="3">Test therapist added via AJAX</textarea>
                </div>
                
                <div class="form-group">
                    <label>Rate (per hour):</label>
                    <input type="number" id="rate" min="0" value="1500">
                </div>
                
                <div class="form-group">
                    <label>Services:</label>
                    <div id="servicesCheckboxes" class="checkbox-group">
                        <p>Loading services...</p>
                    </div>
                </div>
                
                <button type="submit">Test Add Therapist</button>
                <button type="button" onclick="testDirectController()">Direct Controller Test</button>
                <button type="button" onclick="loadServices()">Reload Services</button>
            </form>
        </div>

        <!-- Results -->
        <div id="results" class="results" style="display: none;"></div>
    </div>

    <script>
        $(document).ready(function() {
            loadServices();
        });

        function loadServices() {
            console.log('Loading services...');
            
            $.post('controller/booking_services_contr.php', {
                action: 'get_services'
            }, function(response) {
                console.log('Services response:', response);
                
                if (response && Array.isArray(response) && response.length > 0) {
                    let checkboxHtml = '';
                    
                    response.forEach(function(service) {
                        checkboxHtml += `
                            <div class="checkbox-item">
                                <input type="checkbox" class="service-checkbox" value="${service.id}" id="service_${service.id}">
                                <label for="service_${service.id}">${service.service_name} (₱${service.price || 0})</label>
                            </div>
                        `;
                    });
                    
                    $('#servicesCheckboxes').html(checkboxHtml);
                } else {
                    $('#servicesCheckboxes').html('<p>❌ No services found or error loading services</p>');
                }
            }).fail(function(xhr, status, error) {
                console.error('Failed to load services:', error);
                $('#servicesCheckboxes').html('<p>❌ Failed to load services: ' + error + '</p>');
            });
        }

        $('#testAddForm').on('submit', function(e) {
            e.preventDefault();
            
            // Get selected services
            let selectedServices = [];
            $('.service-checkbox:checked').each(function() {
                selectedServices.push($(this).val());
            });
            
            if (selectedServices.length === 0) {
                alert('Please select at least one service');
                return;
            }
            
            const formData = {
                action: 'add_therapist',
                therapist_name: $('#therapist_name').val(),
                therapist_desc: $('#therapist_desc').val(),
                rate: $('#rate').val(),
                service_ids: selectedServices
            };
            
            console.log('Submitting to controller:', formData);
            showResult('Form Submission', 'Sending request...', 'info');
            
            $.post('controller/therapist_contr.php', formData, function(response) {
                console.log('Controller response:', response);
                showResult('Controller Response - Success', response, 'success');
            }).fail(function(xhr, status, error) {
                console.error('Controller request failed:', xhr.responseText);
                showResult('Controller Response - Error', {
                    status: 'error',
                    message: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                }, 'error');
            });
        });

        function testDirectController() {
            const testData = {
                action: 'add_therapist',
                therapist_name: 'Direct Test ' + new Date().getTime(),
                therapist_desc: 'Direct controller test',
                rate: 2000,
                service_ids: ['1', '2'] // Fixed service IDs
            };
            
            console.log('Direct controller test:', testData);
            showResult('Direct Test', 'Sending direct request...', 'info');
            
            $.ajax({
                url: 'controller/therapist_contr.php',
                type: 'POST',
                data: testData,
                dataType: 'json',
                success: function(response) {
                    console.log('Direct test response:', response);
                    showResult('Direct Test - Success', response, 'success');
                },
                error: function(xhr, status, error) {
                    console.error('Direct test failed:', xhr);
                    showResult('Direct Test - Error', {
                        status: 'error',
                        error: error,
                        responseText: xhr.responseText,
                        statusCode: xhr.status
                    }, 'error');
                }
            });
        }

        function showResult(title, data, type) {
            const colors = {
                info: '#d1ecf1',
                success: '#d4edda', 
                error: '#f8d7da'
            };
            
            let html = '<h3>' + title + '</h3>';
            html += '<pre style="background: ' + (colors[type] || '#f8f9fa') + '; padding: 10px; border-radius: 4px; overflow-x: auto;">';
            
            if (typeof data === 'object') {
                html += JSON.stringify(data, null, 2);
            } else {
                html += data;
            }
            
            html += '</pre>';
            html += '<hr>';
            
            $('#results').html($('#results').html() + html).show();
        }
    </script>
</body>
</html>