<div class="container-fluid">
    <div class="row">

        <div class="col-md-12 mb-1">

            <div class="container-fluid h-100" style="max-height: calc(100vh - 310px); overflow-y: auto;">
                <div class="row g-3">
                    <div class="row g-3">
                        <div class="responsive-col p-2">
                            <div id="services_container">
                                <div class="card flex-md-column flex-row-reverse">
                                    <!-- Image -->
                                    <div class="service-img-wrapper">
                                        <img src="../vendor/images/headMassage.png" class="img-fluid service-img" alt="Service Image">
                                    </div>
                                    <!-- Card Body -->
                                    <div class="card-body p-3">
                                        <h5 class="card-title mb-2">Service 1</h5>
                                        <p class="card-text small">This is the description for Service 1 Relax and enjoy!</p>
                                        <p class="card-text fw-bold text-primary mb-0">₱ 305 / 30 min</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
            <div class="card mt-2">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Recent Services</h5>
                </div>
                <div class="recent-services-scroll">
                    <ul class="list-group list-group-flush" style="overflow-y: auto; max-height: 180px;">
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li>
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Shiatsu - June 2, 2025</li>
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li>
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Shiatsu - June 2, 2025</li>
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li>
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Shiatsu - June 2, 2025</li>
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li>
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Shiatsu - June 2, 2025</li>
                        <!-- Add more to test scroll -->
                    </ul>
                </div>
            </div>
        </div>

        <!-- Appointment Form (right side) -->
        <!-- <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Book an Appointment</h5>
                </div> -->

        <!-- Add style to limit height and enable scroll -->
        <!-- <div class="card-body p-3" style="max-height: 75vh; overflow-y: auto;">
                    <form id="appointment-form">
                        <div class="mb-3">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstname" placeholder="Enter first name" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastname" placeholder="Enter last name" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contact" placeholder="Enter contact number" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter email address" required>
                        </div>
                        <div class="mb-3">
                            <label for="age" class="form-label">Age</label>
                            <input type="number" class="form-control" id="age" placeholder="Enter your age" required>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" data-content="user_booking-view_calendar.php" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="time" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Complete Address</label>
                            <textarea class="form-control" id="address" rows="2" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Book Now</button>
                    </form>
                </div>
            </div>
        </div> -->
    </div>
</div>


<script>
    loadServices();
    // Views Script
    function loadServices() {
        $.ajax({
            url: '../controller/booking_services_contr.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fetch_services'
            },
            success: result => {
                console.log(result);
                const response = result === 'nodata' ? '<p>No services available</p>' : result;
                // $('#services_container').html(response);
            },
        });
    }
</script>

<style>
    .responsive-col {
        flex: 0 0 100%;
    }

    .service-img-wrapper {
        width: 100%;
        height: 150px;
        overflow: hidden;
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .service-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    @media (min-width: 576px) {
        .responsive-col {
            flex: 0 0 50%;
        }

        /* .service-img-wrapper {
        height: 100%;
    } */
    }


    @media (min-width: 768px) {
        .responsive-col {
            flex: 0 0 33.3333%;
        }

        /* .service-img-wrapper {
        height: 100%
    } */
    }


    @media (min-width: 992px) {
        .responsive-col {
            flex: 0 0 33.3333%;
        }
    }

    @media (min-width: 1200px) {
        .responsive-col {
            flex: 0 0 33.3333%;
        }
    }
</style>