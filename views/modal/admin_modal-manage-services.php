<div class="modal-header border-0 pb-0">
    <h5 class="modal-title fw-semibold" id="addServiceModalLabel">Add New Service</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body pt-0">
    <!-- Custom Image Preview + Upload -->
    <div class="mb-4">
        <label class="form-label fw-semibold">Service Image</label>
        <div class="position-relative rounded-3 overflow-hidden border border-2 shadow-sm" style="height: 200px; cursor: pointer;" onclick="document.getElementById('serviceImage').click();">
            <div class="service_image_container"></div>
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
    </div>
</div>

<div class="modal-footer border-0 pt-3">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary addServicesBtn" onclick="addNewServices();">Add Service</button>
    <button type="button" class="btn btn-success updateServicesBtn" onclick="updateServices(this.value);">Update Service</button>
</div>

<?php include_once '../../helper/input_validation.php'; ?>
<script>
    var serviceid = '<?= isset($_GET['serviceid']) ? $_GET['serviceid'] : '' ?>';
    if (serviceid != '') {
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
                    $('.service_image_container').html('<img src="data:image/png;base64,' + result.service_picture + '" value="' + result.service_picture + '" class="service_image img-fluid rounded-3 border" id="service_image" style="height: 200px; object-fit: cover;" alt="Preview">');
                }
            }
        });


        function updateServices() {
            if (inputValidation('serviceName', 'serviceDescription', 'servicePrice', 'serviceDuration')) {
                $.ajax({
                    url: '../controller/booking_services_contr.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'update_service',
                        serviceid: serviceid,
                        image: $('#service_image').attr('value'),
                        name: $('#serviceName').val(),
                        description: $('#serviceDescription').val(),
                        price: $('#servicePrice').val(),
                        duration: $('#serviceDuration').val()
                    },
                    success: function(response) {
                        if (response === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Service Updated Successfully',
                                showConfirmButton: false,
                                timer: 1500
                            });

                            $('#globalModal').modal('hide');
                            loadServices();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error Updating Service',
                                text: response
                            });
                        }
                    }
                });
            }
        }
    } else {
        $('.addServicesBtn').css('display', 'block');
        $('.updateServicesBtn').css('display', 'none');

        $('.service_image_container').html('<img src="../vendor/images/headMassage.png" class="service_image img-fluid rounded-3 border" id="service_image" style="height: 200px; object-fit: cover;" alt="Preview">');

        function addNewServices() {
            if (inputValidation('serviceImage', 'serviceName', 'serviceDescription', 'servicePrice', 'serviceDuration')) {
                $.ajax({
                    url: '../controller/booking_services_contr.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'add_service',
                        image: $('#service_image').attr('value'),
                        name: $('#serviceName').val(),
                        description: $('#serviceDescription').val(),
                        price: $('#servicePrice').val(),
                        duration: $('#serviceDuration').val()
                    },
                    success: function(response) {
                        if (response === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Service Added Successfully',
                                showConfirmButton: false,
                                timer: 1500
                            });

                            $('#globalModal').modal('hide');
                            loadServices();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error Adding Service',
                                text: response
                            });
                        }
                    }
                });
            }
        }
    }


    $('#serviceImage').on('change', function() {
        var reader = new FileReader();
        reader.onload = function(e) {
            $.ajax({
                url: '../controller/booking_services_contr.php',
                type: 'POST',
                data: {
                    action: 'load_image_base64',
                    image: e.target.result
                },
                success: function(result) {
                    $('.service_image_container').html('<img src="' + e.target.result + '" value="' + result + '" class="service_image img-fluid rounded-3 border" id="service_image" style="height: 200px; object-fit: cover;" alt="Preview">');
                }
            });
        }
        reader.readAsDataURL(this.files[0]);
    });
</script>