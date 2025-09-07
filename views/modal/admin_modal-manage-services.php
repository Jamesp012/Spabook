<div class="modal-header border-0 pb-0">
    <h5 class="modal-title fw-semibold" id="addServiceModalLabel">Add New Service</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body pt-0">
    <!-- Custom Image Preview + Upload -->
    <div class="mb-4">
        <label class="form-label fw-semibold">Service Image</label>
        <div class="position-relative rounded-3 overflow-hidden border border-2 shadow-sm" style="height: 200px; cursor: pointer;" onclick="document.getElementById('serviceImage').click();">
            <img id="service_image" src="../vendor/images/default_product.png" alt="Service Image" class="w-100 h-100" style="object-fit: cover;">
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-flex justify-content-center align-items-center text-white fw-semibold" style="opacity: 0; transition: opacity 0.3s;" id="uploadOverlay">
                Click to select image
            </div>
        </div>
        <input type="file" class="form-control mt-2 d-none" id="serviceImage" name="image" accept="image/*" required>
    </div>

    <!-- Service Name -->
    <div class="mb-3">
        <label for="serviceName" class="form-label fw-semibold">Service Name</label>
        <input type="text" class="form-control" id="serviceName" name="name" placeholder="e.g., Body Massage" required>
        <div class="invalid-feedback"></div>
    </div>

    <!-- Description -->
    <div class="mb-3">
        <label for="serviceDescription" class="form-label fw-semibold">Description</label>
        <textarea class="form-control" id="serviceDescription" name="description" rows="3" placeholder="Enter short description..." required></textarea>
        <div class="invalid-feedback"></div>
    </div>

    <div class="row g-3">
        <!-- Price -->
        <div class="col-md-6">
            <label for="servicePrice" class="form-label fw-semibold">Price (₱)</label>
            <input type="number" class="form-control" id="servicePrice" name="price" min="0" placeholder="e.g., 500" required>
            <div class="invalid-feedback"></div>
        </div>

        <!-- Duration -->
        <div class="col-md-6">
            <label for="serviceDuration" class="form-label fw-semibold">Duration (minutes)</label>
            <input type="number" class="form-control" id="serviceDuration" name="duration" min="1" placeholder="e.g., 60" required>
            <div class="invalid-feedback"></div>
        </div>

        <!-- Commission  -->
        <div class="col-md-6">
            <label for="serviceCommission" class="form-label fw-semibold">Commission (₱)</label>
            <input type="number" class="form-control" id="serviceCommission" name="commission" min="1" placeholder="e.g., 30" required>
            <div class="invalid-feedback"></div>
        </div>
    </div>
</div>

<div class="modal-footer border-0 pt-3">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary addServicesBtn" onclick="addNewServices();">Add Service</button>
    <button type="button" class="btn btn-success updateServicesBtn" onclick="updateServices(this.value);">Update Service</button>
</div>

<script>
    // Input validation functions
    function inputValidation(...args) {
        let isValidated = true;
        $.each(args, function(i, e) {
            let element = $(`#${e}`);
            let reqfield = ($(`label[for='${e}']`).text()).replace(/[^a-zA-Z0-9\s]/g, '');
            if (element.val().trim() == '' || element.val().trim() == '-') {
                invalidField(e, `${reqfield} is required.`);
                isValidated = false;
            } else {
                validField(e);
            }
        });
        return isValidated;
    }

    function invalidField(field, msg) {
        $('#' + field).addClass('is-invalid').removeClass('is-valid');
        $('#' + field).next().html(msg);
    }

    function validField(field) {
        $('#' + field).addClass('is-valid').removeClass('is-invalid');
        $('#' + field).next().html();
    }

    // Modal specific code
    var serviceid = '<?= isset($_GET['serviceid']) ? $_GET['serviceid'] : '' ?>';

    // Define global functions
    function addNewServices() {
        if (inputValidation('serviceName', 'serviceDescription', 'servicePrice', 'serviceDuration', 'serviceCommission')) {
            // Get the image data
            let imageData = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
            let currentSrc = $('#service_image').attr('src');

            // Only use the image if it's not the default image or a relative path
            if (currentSrc && !currentSrc.includes('default_product.png') && currentSrc.startsWith('data:')) {
                imageData = currentSrc;
                console.log('📸 Using uploaded image, length:', imageData.length);
            } else {
                console.log('📷 Using default image (no custom image uploaded)');
            }

            // Log the data being sent
            const formData = {
                action: 'add_service',
                image: imageData,
                name: $('#serviceName').val(),
                description: $('#serviceDescription').val(),
                price: $('#servicePrice').val(),
                duration: $('#serviceDuration').val(),
                commission: $('#serviceCommission').val()
            };

            console.log('📤 Sending service data:', {
                action: formData.action,
                name: formData.name,
                description: formData.description,
                price: formData.price,
                duration: formData.duration,
                imageLength: formData.image ? formData.image.length : 0,
                imageType: formData.image ? (formData.image.startsWith('data:') ? 'base64' : 'other') : 'none'
            });

            $.ajax({
                url: '../controller/booking_services_contr.php',
                type: 'POST',
                dataType: 'json',
                data: formData,
                beforeSend: function() {
                    Swal.fire({
                        title: 'Adding Service...',
                        html: `
                            <div class="d-flex justify-content-center align-items-center" style="min-width:220px; min-height:220px;">
                                <img src="../vendor/images/SpaBook.png" alt="Loading..." class="custom-spinner-glow" style="width: 120px; height: 120px;">
                            </div>
                            <style>
                                .custom-spinner-glow {
                                    animation: spin 1.2s linear infinite, glow 1.2s ease-in-out infinite alternate;
                                    filter: drop-shadow(0 0 16px #a1623f);
                                }
                                @keyframes spin {
                                    100% { transform: rotate(360deg); }
                                }
                                @keyframes glow {
                                    0% { filter: drop-shadow(0 0 8px #a1623f); }
                                    100% { filter: drop-shadow(0 0 32px #a1623f); }
                                }
                            </style>
                        `,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        backdrop: true,
                    });
                },
                success: function(response) {
                    console.log('Raw response:', response);
                    console.log('Response type:', typeof response);

                    // Handle both JSON string and direct string responses
                    let result = response;
                    if (typeof response === 'string') {
                        try {
                            result = JSON.parse(response);
                        } catch (e) {
                            result = response;
                        }
                    }

                    console.log('Parsed result:', result);

                    if (result === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Service Added Successfully',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        $('#globalModal').modal('hide');
                        if (typeof loadServices === 'function') {
                            loadServices();
                        }
                    } else if (result === 'exists') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Service Already Exists',
                            text: 'A service with this name already exists. Please choose a different name.'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Adding Service',
                            html: '<strong>Response:</strong> ' + JSON.stringify(result) + '<br><br><small>Check browser console for more details</small>'
                        });
                        console.log('Service addition failed:', result);
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        html: '<strong>Status:</strong> ' + status + '<br><strong>Error:</strong> ' + error + '<br><strong>Response:</strong> ' + xhr.responseText
                    });
                    console.log('AJAX Error:', {
                        xhr,
                        status,
                        error
                    });
                }
            });
        }
    }

    function updateServices() {
        if (inputValidation('serviceName', 'serviceDescription', 'servicePrice', 'serviceDuration', 'serviceCommission')) {
            // Get the image data (same logic as add function)
            let imageData = $('#service_image').attr('src');
            let currentSrc = $('#service_image').attr('src');

            console.log('🔄 Update mode - current image src:', currentSrc);
            console.log('🔄 Is base64?', currentSrc && currentSrc.startsWith('data:'));

            // Use the current src, whether it's a URL or base64
            if (!currentSrc) {
                imageData = '../vendor/images/default_product.png';
            }

            // Log the data being sent for update
            const formData = {
                action: 'update_service',
                serviceid: serviceid,
                image: imageData,
                name: $('#serviceName').val(),
                description: $('#serviceDescription').val(),
                price: $('#servicePrice').val(),
                duration: $('#serviceDuration').val(),
                commission: $('#serviceCommission').val()
            };

            $.ajax({
                url: '../controller/booking_services_contr.php',
                type: 'POST',
                dataType: 'json',
                data: formData,
                beforeSend: function() {
                    Swal.fire({
                        title: 'Updating Service...',
                        html: `
                            <div class="d-flex justify-content-center align-items-center" style="min-width:220px; min-height:220px;">
                                <img src="../vendor/images/SpaBook.png" alt="Loading..." class="custom-spinner-glow" style="width: 120px; height: 120px;">
                            </div>
                            <style>
                                .custom-spinner-glow {
                                    animation: spin 1.2s linear infinite, glow 1.2s ease-in-out infinite alternate;
                                    filter: drop-shadow(0 0 16px #a1623f);
                                }
                                @keyframes spin {
                                    100% { transform: rotate(360deg); }
                                }
                                @keyframes glow {
                                    0% { filter: drop-shadow(0 0 8px #a1623f); }
                                    100% { filter: drop-shadow(0 0 32px #a1623f); }
                                }
                            </style>
                        `,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        backdrop: true,
                    });
                },
                success: function(response) {
                    // Handle both JSON string and direct string responses
                    let result = response;
                    if (typeof response === 'string') {
                        try {
                            result = JSON.parse(response);
                        } catch (e) {
                            result = response;
                        }
                    }

                    console.log('Parsed result:', result);

                    if (result.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Service Updated Successfully',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        $('#globalModal').modal('hide');
                        if (typeof loadServices === 'function') {
                            loadServices();
                        }
                    } else if (result.status === 'duplicate') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Duplicate Service Name',
                            text: 'A service with this name already exists. Please choose a different name.'
                        });
                    } else if (result.status === 'notfound') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Service Not Found',
                            text: 'The service you are trying to update no longer exists.'
                        });
                    } else if (result.status === 'image_upload_failed') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Image Upload Failed',
                            text: 'Failed to upload the new image. Please try again or use a different image.'
                        });
                    } else if (result.status === 'database_error') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Database Error',
                            text: 'There was a problem updating the service in the database. Please check the server logs.'
                        });
                    } else if (result.status === 'exception_error') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'An unexpected server error occurred. Please try again or contact support.'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Updating Service',
                            html: '<strong>Response:</strong> ' + JSON.stringify(result) + '<br><br><small>Check browser console for more details</small>'
                        });
                        console.log('Service update failed:', result);
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        html: '<strong>Status:</strong> ' + status + '<br><strong>Error:</strong> ' + error + '<br><strong>Response:</strong> ' + xhr.responseText
                    });
                    console.log('Update AJAX Error:', {
                        xhr,
                        status,
                        error
                    });
                }
            });
        }
    }

    // Initialize based on serviceid
    if (serviceid != '') {
        // Update mode - change title and button visibility
        $('#addServiceModalLabel').text('Update Service');
        $('.addServicesBtn').css('display', 'none');
        $('.updateServicesBtn').css('display', 'block');

        $.ajax({
            url: '../controller/booking_services_contr.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'get_service_by_id',
                serviceid: serviceid
            },
            success: function(result) {
                if (result) {
                    $('#serviceName').val(result.service_name);
                    $('#serviceDescription').val(result.description);
                    $('#servicePrice').val(result.price);
                    $('#serviceDuration').val(result.per_minute);
                    $('#service_image').attr('src', result.service_picture);
                    $('#serviceCommission').val(result.commission);
                }
            }
        });
    } else {
        // Add mode - ensure correct title and button visibility
        $('#addServiceModalLabel').text('Add New Service');
        $('.addServicesBtn').css('display', 'block');
        $('.updateServicesBtn').css('display', 'none');
        $('#service_image').attr('src', '../vendor/images/default_product.png');
    }

    // Image upload handling
    $('#serviceImage').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Show loading while processing image
            $('#uploadOverlay').html('<div class="spinner-border spinner-border-sm text-white me-2"></div>Processing...').css('opacity', '1');

            console.log('📁 Image file selected:', {
                name: file.name,
                size: (file.size / 1024).toFixed(2) + ' KB',
                type: file.type
            });

            var reader = new FileReader();
            reader.onload = function(e) {
                $('#service_image').attr('src', e.target.result);
                $('#uploadOverlay').html('Image uploaded successfully! Click to change').css('opacity', '0');

                console.log('✅ Image loaded as base64, length:', e.target.result.length);

                // Show success feedback briefly
                $('#uploadOverlay').css('opacity', '1');
                setTimeout(() => {
                    $('#uploadOverlay').html('Click to select image').css('opacity', '0');
                }, 2000);
            }
            reader.readAsDataURL(file);
        }
    });

    // Hover effects for image upload  
    $('.position-relative.rounded-3').hover(
        function() {
            $('#uploadOverlay').css('opacity', '1');
        },
        function() {
            $('#uploadOverlay').css('opacity', '0');
        }
    );
</script>