<!DOCTYPE html>
<html>
<head>
    <title>🧪 Simple Service Add Test</title>
    <link href="vendor/Bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>🧪 Simple Service Add Test</h2>
        
        <div class="card">
            <div class="card-header">
                <h4>Manual Service Addition Form</h4>
            </div>
            <div class="card-body">
                <form id="serviceForm">
                    <div class="mb-3">
                        <label class="form-label">Service Name</label>
                        <input type="text" class="form-control" id="serviceName" value="Simple Test Service" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="serviceDescription" required>Simple test description</textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" id="servicePrice" value="1000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" id="serviceDuration" value="60" required>
                        </div>
                    </div>
                    
                    <div class="mb-3 mt-3">
                        <label class="form-label">Service Image (optional)</label>
                        <input type="file" class="form-control" id="serviceImage" accept="image/*">
                        <small class="text-muted">Leave empty to use default image</small>
                    </div>
                    
                    <button type="button" class="btn btn-primary" onclick="addService()">Add Service</button>
                    <button type="button" class="btn btn-secondary" onclick="testWithoutImage()">Test Without Image</button>
                </form>
                
                <div id="result" class="mt-3"></div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h4>Current Services</h4>
            </div>
            <div class="card-body">
                <button class="btn btn-info btn-sm" onclick="loadServices()">Refresh Services List</button>
                <div id="servicesList" class="mt-3"></div>
            </div>
        </div>
    </div>

    <script src="vendor/js/jquery.min.js"></script>
    <script src="vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/SweetAlert/sweetalert2.all.min.js"></script>
    
    <script>
        let imageData = null;
        
        $('#serviceImage').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imageData = e.target.result;
                    $('#result').html('<div class="alert alert-info">Image loaded: ' + file.name + ' (' + (file.size/1024).toFixed(2) + ' KB)</div>');
                };
                reader.readAsDataURL(file);
            }
        });
        
        function addService() {
            const name = $('#serviceName').val();
            const description = $('#serviceDescription').val();
            const price = $('#servicePrice').val();
            const duration = $('#serviceDuration').val();
            
            if (!name || !description || !price || !duration) {
                alert('Please fill all required fields');
                return;
            }
            
            const data = {
                action: 'add_service',
                name: name,
                description: description,
                price: price,
                duration: duration,
                image: imageData || 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==' // 1x1 transparent PNG
            };
            
            $('#result').html('<div class="alert alert-info">Adding service...</div>');
            
            $.ajax({
                url: 'controller/booking_services_contr.php',
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(response) {
                    console.log('Success response:', response);
                    
                    if (response === 'success') {
                        $('#result').html('<div class="alert alert-success">✅ Service added successfully!</div>');
                        loadServices();
                        
                        // Reset form
                        $('#serviceName').val('Simple Test Service ' + new Date().getTime());
                        $('#serviceDescription').val('Simple test description');
                        imageData = null;
                        $('#serviceImage').val('');
                    } else if (response === 'exists') {
                        $('#result').html('<div class="alert alert-warning">⚠️ Service with this name already exists</div>');
                    } else if (response === 'error') {
                        $('#result').html('<div class="alert alert-danger">❌ Database error occurred</div>');
                    } else {
                        $('#result').html('<div class="alert alert-warning">❓ Unexpected response: ' + JSON.stringify(response) + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', {xhr, status, error});
                    $('#result').html('<div class="alert alert-danger">❌ AJAX Error: ' + error + '<br>Response: ' + xhr.responseText + '</div>');
                }
            });
        }
        
        function testWithoutImage() {
            imageData = null;
            addService();
        }
        
        function loadServices() {
            $.ajax({
                url: 'controller/booking_services_contr.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'fetch_services' },
                success: function(response) {
                    if (response === 'nodata' || !response || response.length === 0) {
                        $('#servicesList').html('<div class="alert alert-info">No services found</div>');
                    } else {
                        let html = '<div class="row">';
                        response.forEach(service => {
                            html += `
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">${service.service_name}</h6>
                                            <p class="card-text small">${service.description}</p>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-success">₱${service.price}</span>
                                                <span class="text-muted">${service.per_minute}min</span>
                                            </div>
                                            <small class="text-muted">ID: ${service.id}</small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        $('#servicesList').html(html);
                    }
                },
                error: function(xhr, status, error) {
                    $('#servicesList').html('<div class="alert alert-danger">Error loading services: ' + error + '</div>');
                }
            });
        }
        
        // Load services on page load
        $(document).ready(function() {
            loadServices();
        });
    </script>
</body>
</html>