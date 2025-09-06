    <div class="container-fluid">
        <div class="row" style="max-height: 80vh; overflow-y: auto;">
            <!-- Main Content -->
            <div class="col-md-12 px-md-4">
                <!-- Services and Products Tab Header -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <ul class="nav nav-tabs card-header-tabs" id="servicesProductsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab" aria-controls="services" aria-selected="true">
                                    <i class="bi bi-leaf me-2"></i>Services
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="products-view-tab" data-bs-toggle="tab" data-bs-target="#products-view" type="button" role="tab" aria-controls="products-view" aria-selected="false">
                                    <i class="bi bi-box-seam me-2"></i>Products
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="servicesProductsTabContent">
                            <!-- SERVICES TAB -->
                            <div class="tab-pane fade show active" id="services" role="tabpanel" aria-labelledby="services-tab">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="bi bi-grid me-1"></i>Services Collection
                                    </h6>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="addServices()">
                                            <i class="bi bi-plus-circle me-1"></i>Add New Service
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadServices()">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                        </button>
                                    </div>
                                </div>

                                <!-- Loading State -->
                                <div id="loadingServices" class="text-center py-5">
                                    <div class="spinner-border text-primary mb-3" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <div class="text-muted">Loading services...</div>
                                </div>

                                <!-- Services Container -->
                                <div class="row g-4" id="services_container" style="display: none;">
                                    <!-- Services will be loaded here -->
                                </div>

                                <!-- No Data State -->
                                <div id="noServices" class="text-center py-5" style="display: none;">
                                    <i class="bi bi-leaf fs-1 text-muted mb-3"></i>
                                    <h5 class="text-muted">No Services Found</h5>
                                    <p class="text-muted">Start by adding your first service to the collection.</p>
                                </div>
                            </div>

                            <!-- VIEW PRODUCTS TAB -->
                            <div class="tab-pane fade" id="products-view" role="tabpanel" aria-labelledby="products-view-tab">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="bi bi-box-seam me-1"></i>Products Collection
                                    </h6>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success btn-sm" onclick="addProducts()">
                                            <i class="bi bi-plus-circle me-1"></i>Add Products
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadProducts()">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                        </button>
                                    </div>
                                </div>

                                <!-- Loading State -->
                                <div id="loadingProducts" class="text-center py-5">
                                    <div class="spinner-border text-success mb-3" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <div class="text-muted">Loading products...</div>
                                </div>

                                <!-- Products Container -->
                                <div class="row g-4" id="products_container" style="display: none;">
                                    <!-- Products will be loaded here -->
                                </div>

                                <!-- No Data State -->
                                <div id="noProducts" class="text-center py-5" style="display: none;">
                                    <i class="bi bi-box-seam fs-1 text-muted mb-3"></i>
                                    <h5 class="text-muted">No Products Found</h5>
                                    <p class="text-muted">Start by adding your first product to the collection.</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Use window to avoid redeclaration errors when modal reloads
        window.servicesData = window.servicesData || [];
        window.productsData = window.productsData || [];

        // Prevent multiple initialization
        if (!window.servicesProductsPageInitialized) {
            window.servicesProductsPageInitialized = true;

            $(document).ready(function() {
                loadServices();

                // Load products when view products tab is clicked
                $('#products-view-tab').on('click', function() {
                    if (window.productsData.length === 0) {
                        loadProducts();
                    }
                });
            });
        }

        // ============= SERVICES FUNCTIONS =============
        function loadServices() {
            // Show loading state
            $('#loadingServices').show();
            $('#services_container').hide();
            $('#noServices').hide();

            $.ajax({
                url: '../controller/booking_services_contr.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'fetch_services'
                },
                success: function(result) {
                    $('#loadingServices').hide();

                    if (result === 'nodata' || !result || result.length === 0) {
                        $('#noServices').show();
                        window.servicesData = [];
                    } else {
                        window.servicesData = result;
                        renderServices(result);
                        $('#services_container').show();
                    }
                },
                error: function() {
                    $('#loadingServices').hide();
                    $('#noServices').show();
                    Swal.fire('Error!', 'Failed to load services.', 'error');
                }
            });
        }

        function renderServices(services) {
            let html = '';
            services.forEach(service => {
                let imageSrc;
                if (service.service_picture.startsWith('http')) {
                    imageSrc = service.service_picture;
                } else if (service.service_picture.startsWith('data:image')) {
                    imageSrc = service.service_picture;
                } else {
                    imageSrc = `data:image/png;base64,${service.service_picture}`;
                }

                html += `
                <div class="col-xl-4 col-lg-6 col-md-6 mb-4" data-service-id="${service.id}">
                    <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden position-relative service-card border-primary">
                        <div class="position-relative">
                            <img src="${imageSrc}" 
                                class="card-img-top" 
                                style="height: 220px; object-fit: cover;" 
                                alt="${service.service_name}"
                                onerror="this.src='../vendor/images/headMassage.png'">
                            <div class="position-absolute bottom-0 start-0 end-0 bg-gradient-dark p-2">
                                <span class="badge bg-primary">${service.per_minute} minutes</span>
                            </div>
                            <!-- Action Buttons - Top Right -->
                            <div class="position-absolute top-0 end-0 p-2">
                                <div class="d-flex flex-column gap-2">
                                    <button type="button" class="btn btn-light btn-action" onclick="editService(${service.id})" title="Edit Service">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </button>
                                    <button type="button" class="btn btn-light btn-action" onclick="deleteService(${service.id})" title="Delete Service">
                                        <i class="bi bi-trash3 text-danger"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title mb-2 text-dark">${service.service_name}</h5>
                            <p class="card-text text-muted small mb-3" style="line-height: 1.4;">
                                ${service.description.length > 80 ? service.description.substring(0, 80) + '...' : service.description}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="h5 text-primary fw-bold mb-0">₱${parseFloat(service.price).toLocaleString()}</span>
                                    <small class="text-muted d-block">per session</small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">Duration</small>
                                    <div class="fw-semibold">${service.per_minute} min</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            });
            $('#services_container').html(html);
        }

        function addServices() {
            showGlobalModal('modal/admin_modal-manage-services.php');
        }

        function editService(id) {
            showGlobalModal('modal/admin_modal-manage-services.php?serviceid=' + id);
        }

        function deleteService(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This service will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../controller/booking_services_contr.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'delete_service',
                            serviceid: id
                        },
                        success: function(response) {
                            if (response === 'success') {
                                Swal.fire('Deleted!', 'The service has been deleted.', 'success');
                                loadServices();
                            } else {
                                Swal.fire('Error!', 'Failed to delete the service.', 'error');
                            }
                        }
                    });
                }
            });
        }

        // ============= PRODUCTS FUNCTIONS =============
        // Track loading state to prevent multiple simultaneous requests
        if (typeof window.isLoadingProducts === 'undefined') {
            window.isLoadingProducts = false;
        }

        function loadProducts() {
            // Prevent multiple simultaneous requests
            if (window.isLoadingProducts) {
                return;
            }

            window.isLoadingProducts = true;

            // Show loading state
            $('#loadingProducts').show();
            $('#products_container').hide();
            $('#noProducts').hide();

            $.ajax({
                url: '../controller/product_contr.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'fetch_products'
                },
                success: function(result) {
                    $('#loadingProducts').hide();
                    window.isLoadingProducts = false;

                    if (result.status === 'success' && result.data && result.data.length > 0) {
                        window.productsData = result.data;
                        renderProducts(result.data);
                        $('#products_container').show();
                    } else {
                        $('#noProducts').show();
                        window.productsData = [];
                    }
                },
                error: function() {
                    $('#loadingProducts').hide();
                    $('#noProducts').show();
                    window.isLoadingProducts = false;
                    Swal.fire('Error!', 'Failed to load products.', 'error');
                }
            });
        }

        function renderProducts(products) {
            console.log('Rendering products:', products);
            let html = '';
            products.forEach(product => {
                let imageSrc = product.product_image == null ? '../vendor/images/default_product.jpg' : product.product_image;
                // if (product.product_image && !product.product_image.startsWith('http') && !product.product_image.startsWith('data:image')) {
                //     imageSrc = `data:image/png;base64,${product.product_image}`;
                // }

                html += `
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4" data-product-id="${product.productid}">
                        <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden position-relative product-card border-success">
                            <div class="position-relative">
                                <img src="${imageSrc}" 
                                    class="card-img-top" 
                                    style="height: 220px; object-fit: cover; transition: opacity 0.3s ease;" 
                                    alt="${product.product_name}"
                                    loading="lazy"
                                    onload="this.style.opacity='1'"
                                  
                                    onloadstart="this.style.opacity='0.7'">
                                <div class="position-absolute bottom-0 start-0 end-0 bg-gradient-dark p-2">
                                    <span class="badge bg-success">${product.category || 'General'}</span>
                                    <span class="badge bg-info ms-1">Stock: ${product.stock_quantity}</span>
                                </div>
                                <!-- Action Buttons - Top Right -->
                                <div class="position-absolute top-0 end-0 p-2">
                                    <div class="d-flex flex-column gap-2">
                                        <button type="button" class="btn btn-light btn-action" onclick="editProduct(${product.productid})" title="Edit Product">
                                            <i class="bi bi-pencil-square text-success"></i>
                                        </button>
                                        <button type="button" class="btn btn-light btn-action" onclick="deleteProduct(${product.productid})" title="Delete Product">
                                            <i class="bi bi-trash3 text-danger"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title mb-2 text-dark" style="min-height: 1.5em;">${product.product_name}</h5>
                                <p class="card-text text-muted small mb-3" style="line-height: 1.4; min-height: 3.6em; overflow: hidden;">
                                    ${(product.product_description || product.description) ? ((product.product_description || product.description).length > 80 ? (product.product_description || product.description).substring(0, 80) + '...' : (product.product_description || product.description)) : 'No description available'}
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="h5 text-success fw-bold mb-0">₱${parseFloat(product.product_price || product.price || 0).toLocaleString()}</span>
                                        <small class="text-muted d-block">per item</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">Stock</small>
                                        <div class="fw-semibold ${product.stock_quantity < 10 ? 'text-warning' : 'text-success'}">${product.stock_quantity || 0}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            // Use fade - in animation to prevent jarring content replacement
            const $container = $('#products_container');
            $container.fadeOut(150, function() {
                $container.html(html).fadeIn(300);
            });
        }

        function addProducts() {
            showGlobalModal('modal/admin_modal-manage-products-global.php');
        }

        function editProduct(id) {
            showGlobalModal('modal/admin_modal-manage-products-global.php', {
                productid: id
            });
        }

        function deleteProduct(id) {
            // First get product details to show in confirmation
            $.ajax({
                url: '../controller/product_contr.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_product_by_id',
                    productid: id
                },
                success: function(result) {
                    let productName = 'this product';
                    if (result.status === 'success' && result.data && result.data.product_name) {
                        productName = result.data.product_name;
                    }

                    // Show confirmation dialog with product name
                    Swal.fire({
                        title: 'Delete Product?',
                        html: `Are you sure you want to delete <strong>"${productName}"</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bi bi-trash me-1"></i>Delete Product',
                        cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
                        reverseButtons: true,
                        focusCancel: true
                    }).then((confirmResult) => {
                        if (confirmResult.isConfirmed) {
                            performDelete(id, productName);
                        }
                    });
                },
                error: function() {
                    // If we can't get product details, still allow deletion
                    Swal.fire({
                        title: 'Delete Product?',
                        text: 'Are you sure you want to delete this product? This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bi bi-trash me-1"></i>Delete Product',
                        cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
                        reverseButtons: true,
                        focusCancel: true
                    }).then((confirmResult) => {
                        if (confirmResult.isConfirmed) {
                            performDelete(id, 'product');
                        }
                    });
                }
            });
        }

        function performDelete(id, productName) {
            // Show loading state
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while we delete the product.',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Perform the deletion
            $.ajax({
                url: '../controller/product_contr.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'delete_product',
                    productid: id
                },
                success: function(result) {
                    // Handle different response formats
                    let isSuccess = false;
                    let message = '';

                    if (typeof result === 'string') {
                        isSuccess = result === 'success';
                        message = isSuccess ? 'Product deleted successfully' : 'Failed to delete product';
                    } else if (typeof result === 'object' && result !== null) {
                        isSuccess = result.status === 'success';
                        message = result.message || (isSuccess ? 'Product deleted successfully' : 'Failed to delete product');
                    }

                    if (isSuccess) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: `"${productName}" has been successfully deleted.`,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Refresh the products list
                            loadProducts();
                        });
                    } else {
                        Swal.fire({
                            title: 'Delete Failed',
                            text: message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete product error:', {
                        xhr,
                        status,
                        error
                    });
                    Swal.fire({
                        title: 'Connection Error',
                        text: 'Failed to delete the product due to a connection error. Please check your internet connection and try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    </script>

    <style>
        .service-card,
        .product-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border-width: 2px !important;
        }

        .service-card:hover,
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .product-card:hover {
            box-shadow: 0 8px 25px rgba(25, 135, 84, 0.2) !important;
        }

        .border-success {
            border-color: #198754 !important;
        }

        .border-primary {
            border-color: #0d6efd !important;
        }

        .bg-gradient-dark {
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.4), transparent);
        }

        .card-img-top {
            transition: transform 0.3s ease, opacity 0.3s ease;
            background-color: #f8f9fa;
        }

        /* Prevent layout shifts during image loading */
        .product-card .position-relative {
            min-height: 220px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Smooth fade-in for products container */
        #products_container {
            transition: opacity 0.3s ease;
        }

        .service-card:hover .card-img-top,
        .product-card:hover .card-img-top {
            transform: scale(1.05);
        }

        .btn-action {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.9;
        }

        .btn-action:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .nav-tabs .nav-link {
            border: none;
            border-radius: 0;
        }

        .nav-tabs .nav-link.active {
            background-color: transparent;
            border-bottom: 3px solid #0d6efd;
            font-weight: 600;
        }

        .nav-tabs .nav-link:hover {
            border-color: transparent;
            background-color: rgba(0, 0, 0, 0.05);
        }

        /* Custom scrollbar for containers */
        .card-body {
            scrollbar-width: thin;
            scrollbar-color: #dee2e6 transparent;
        }

        .card-body::-webkit-scrollbar {
            width: 6px;
        }

        .card-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .card-body::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 3px;
        }

        .card-body::-webkit-scrollbar-thumb:hover {
            background: #adb5bd;
        }

        @media (max-width: 768px) {
            .btn-group {
                flex-direction: column;
            }

            .btn-group .btn {
                border-radius: 0.375rem !important;
                margin-bottom: 0.25rem;
            }
        }
    </style>