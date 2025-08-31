<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Multiple Services for Therapists</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .results { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px; }
        .service-checkbox { width: auto; margin-right: 10px; }
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 15px; }
        .checkbox-item { display: flex; align-items: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧘‍♀️ Test Multiple Services for Therapists</h1>
        
        <div style="background: #e7f3ff; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            <strong>📋 How it works:</strong>
            <ul>
                <li>Uses existing database schema - no new tables needed!</li>
                <li>Stores multiple service IDs as comma-separated values in the existing <code>service_id</code> text column</li>
                <li>Example: service_id = "1,3,5" means therapist can perform services 1, 3, and 5</li>
                <li>Backwards compatible with single service assignments</li>
            </ul>
        </div>

        <!-- Add Therapist Form -->
        <div class="form-section">
            <h2>➕ Add New Therapist</h2>
            <form id="addTherapistForm">
                <div class="form-group">
                    <label>Therapist Name:</label>
                    <input type="text" id="therapist_name" required>
                </div>
                
                <div class="form-group">
                    <label>Description:</label>
                    <textarea id="therapist_desc" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Rate (per hour):</label>
                    <input type="number" id="rate" min="0" value="0">
                </div>
                
                <div class="form-group">
                    <label>Services (Select multiple):</label>
                    <div id="servicesCheckboxes" class="checkbox-group">
                        <!-- Services will be loaded here -->
                    </div>
                </div>
                
                <button type="submit">Add Therapist</button>
            </form>
        </div>

        <!-- Search Therapists by Service -->
        <div class="form-section" style="margin-top: 40px;">
            <h2>🔍 Find Therapists by Service</h2>
            <div class="form-group">
                <label>Select Service:</label>
                <select id="serviceSearch">
                    <option value="">-- Select a Service --</option>
                    <!-- Services will be loaded here -->
                </select>
            </div>
            <button onclick="searchTherapists()">Search Therapists</button>
        </div>

        <!-- Results -->
        <div id="results" class="results" style="display: none;"></div>

        <!-- All Therapists -->
        <div style="margin-top: 40px;">
            <h2>👥 All Therapists</h2>
            <button onclick="loadAllTherapists()">Load All Therapists</button>
            <div id="allTherapists" class="results" style="display: none;"></div>
        </div>
    </div>

    <script src="vendor/js/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            loadServices();
            loadAllTherapists();
        });

        function loadServices() {
            $.post('controller/therapist_contr.php', {
                action: 'test_services'
            }, function(response) {
                console.log('Services response:', response);
                
                if (response.status === 'success' && response.services) {
                    let checkboxHtml = '';
                    let selectHtml = '<option value="">-- Select a Service --</option>';
                    
                    response.services.forEach(function(service) {
                        checkboxHtml += `
                            <div class="checkbox-item">
                                <input type="checkbox" class="service-checkbox" value="${service.id}" id="service_${service.id}">
                                <label for="service_${service.id}">${service.service_name} (₱${service.price || 0})</label>
                            </div>
                        `;
                        
                        selectHtml += `<option value="${service.id}">${service.service_name}</option>`;
                    });
                    
                    $('#servicesCheckboxes').html(checkboxHtml);
                    $('#serviceSearch').html(selectHtml);
                } else {
                    $('#servicesCheckboxes').html('<p>⚠️ Could not load services. Please check your database.</p>');
                }
            }).fail(function(xhr, status, error) {
                console.error('Failed to load services:', error);
                $('#servicesCheckboxes').html('<p>❌ Failed to load services: ' + error + '</p>');
            });
        }

        $('#addTherapistForm').on('submit', function(e) {
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
            
            console.log('Submitting:', formData);
            
            $.post('controller/therapist_contr.php', formData, function(response) {
                console.log('Add therapist response:', response);
                showResult('Add Therapist Result', response);
                
                if (response.status === 'success') {
                    // Reset form
                    $('#addTherapistForm')[0].reset();
                    loadAllTherapists(); // Refresh the list
                }
            }).fail(function(xhr, status, error) {
                showResult('Error', {status: 'error', message: 'Request failed: ' + error});
            });
        });

        function searchTherapists() {
            const serviceId = $('#serviceSearch').val();
            if (!serviceId) {
                alert('Please select a service');
                return;
            }
            
            $.post('controller/therapist_contr.php', {
                action: 'get_therapists_by_service',
                service_id: serviceId
            }, function(response) {
                console.log('Search response:', response);
                showResult('Therapists for Selected Service', response);
            }).fail(function(xhr, status, error) {
                showResult('Search Error', {status: 'error', message: 'Search failed: ' + error});
            });
        }

        function loadAllTherapists() {
            $.post('controller/therapist_contr.php', {
                action: 'get_all_therapists_admin'
            }, function(response) {
                console.log('All therapists response:', response);
                
                let html = '<h3>All Therapists:</h3>';
                
                if (Array.isArray(response) && response.length > 0) {
                    html += '<table border="1" style="width: 100%; border-collapse: collapse;">';
                    html += '<tr><th>ID</th><th>Name</th><th>Services</th><th>Description</th><th>Rate</th></tr>';
                    
                    response.forEach(function(therapist) {
                        html += `<tr>
                            <td>${therapist.therapistid}</td>
                            <td>${therapist.therapist_name}</td>
                            <td>${therapist.services_display || therapist.service_id || 'No services'}</td>
                            <td>${therapist.therapist_desc || 'No description'}</td>
                            <td>₱${therapist.rate || 0}</td>
                        </tr>`;
                    });
                    
                    html += '</table>';
                } else if (response === 'nodata') {
                    html += '<p>No therapists found.</p>';
                } else {
                    html += '<p>Response: ' + JSON.stringify(response) + '</p>';
                }
                
                $('#allTherapists').html(html).show();
            }).fail(function(xhr, status, error) {
                $('#allTherapists').html('<p>❌ Failed to load therapists: ' + error + '</p>').show();
            });
        }

        function showResult(title, data) {
            let html = '<h3>' + title + '</h3>';
            html += '<pre style="background: white; padding: 10px; border: 1px solid #ddd; border-radius: 4px; overflow-x: auto;">';
            html += JSON.stringify(data, null, 2);
            html += '</pre>';
            
            $('#results').html(html).show();
        }
    </script>
</body>
</html>