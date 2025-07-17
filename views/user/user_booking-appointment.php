<?php
    $services = [];

    for ($i = 1; $i <= 12; $i++) {
        $services[] = [
            'title' => "Service $i",
            'description' => "This is the description for Service $i. Relax and enjoy!",
            'price' => "₱" . (300 + $i * 5) . " / 30 min",
            'image' => "../vendor/images/headMassage.png"
        ];
    }
?>

<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-12 mb-1">
            <div class="card " id="cardView">
                
            </div>

            <div class="container-fluid h-100" style="max-height: calc(100vh - 310px); overflow-y: auto;">
                <div class="row g-3">

                    <div class="row g-3">
                        <?php foreach ($services as $service): ?>
                            <div class="responsive-col p-2">
                                <div class="card flex-md-column flex-row-reverse">
                                    <!-- Image -->
                                    <div class="service-img-wrapper">
                                        <img src="<?= $service['image']; ?>" class="img-fluid service-img" alt="Service Image">
                                    </div>


                                    <!-- Card Body -->
                                    <div class="card-body p-3">
                                        <h5 class="card-title mb-2"><?= $service['title']; ?></h5>
                                        <p class="card-text small"><?= $service['description']; ?></p>
                                        <p class="card-text fw-bold text-primary mb-0"><?= $service['price']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>


                </div>
            </div>
            <div class="card mt-2">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Recent Services</h5>
                </div>
                <div class="recent-services-scroll">
                    <ul class="list-group list-group-flush" style="overflow-y: auto; max-height: 120px;">
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
    </div>
</div>

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
