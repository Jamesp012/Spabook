<div class="container-fluid" style="max-height: calc(100vh - 130px); overflow-y: auto;">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-spa me-2"></i>Services & Products</h4>
        <button class="btn btn-outline-primary btn-sm" onclick="refreshData()">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
        </button>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="servicesProductsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="user-services-tab" data-bs-toggle="tab" data-bs-target="#user-services" type="button" role="tab" aria-controls="user-services" aria-selected="true">
                <i class="bi bi-leaf me-2"></i>Services
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="user-products-tab" data-bs-toggle="tab" data-bs-target="#user-products" type="button" role="tab" aria-controls="user-products" aria-selected="false">
                <i class="bi bi-box-seam me-2"></i>Products
            </button>
        </li>
    </ul>

    <div class="tab-content" id="servicesProductsTabContent">
        <!-- SERVICES TAB -->
        <div class="tab-pane fade show active" id="user-services" role="tabpanel" aria-labelledby="user-services-tab">
            <!-- Loading State -->
            <div id="loadingServices" class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-muted">Loading services...</div>
            </div>
            
            <!-- Services Grid -->
            <div class="row g-3" id="servicesContainer" style="display: none;">
                <!-- Services will be loaded here -->
            </div>
            
            <!-- No Services State -->
            <div id="noServices" class="text-center py-5" style="display: none;">
                <i class="bi bi-leaf fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">No Services Available</h5>
                <p class="text-muted">Services will appear here when they become available.</p>
            </div>
        </div>

        <!-- PRODUCTS TAB -->
        <div class="tab-pane fade" id="user-products" role="tabpanel" aria-labelledby="user-products-tab">
            <!-- Loading State -->
            <div id="loadingProducts" class="text-center py-5">
                <div class="spinner-border text-success mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-muted">Loading products...</div>
            </div>
            
            <!-- Products Grid -->
            <div class="row g-3" id="productsContainer" style="display: none;">
                <!-- Products will be loaded here -->
            </div>
            
            <!-- No Products State -->
            <div id="noProducts" class="text-center py-5" style="display: none;">
                <i class="bi bi-box-seam fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">No Products Available</h5>
                <p class="text-muted">Products will appear here when they become available.</p>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadServices();
    
    // Load products when tab is clicked for the first time
    $('#user-products-tab').one('click', function() {
        loadProducts();
    });
});

function refreshData() {
    const activeTab = $('.nav-link.active').attr('id');
    if (activeTab === 'user-services-tab') {
        loadServices();
    } else {
        loadProducts();
    }
}

// ============= SERVICES FUNCTIONS =============
function loadServices() {
    $('#loadingServices').show();
    $('#servicesContainer').hide();
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
            } else {
                renderServices(result);
                $('#servicesContainer').show();
            }
        },
        error: function() {
            $('#loadingServices').hide();
            $('#noServices').show();
            console.error('Failed to load services');
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
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100 service-card" data-service-id="${service.id}">
                    <img src="${imageSrc}" 
                         class="card-img-top" 
                         style="height: 180px; object-fit: cover;" 
                         alt="${service.service_name}"
                         onerror="this.src='../vendor/images/headMassage.png'">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2">${service.service_name}</h5>
                        <p class="card-text small text-muted flex-grow-1" style="line-height: 1.4;">
                            ${service.description.length > 100 ? service.description.substring(0, 100) + '...' : service.description}
                        </p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-primary h6 mb-0">₱${parseFloat(service.price).toLocaleString()}</span>
                                    <div class="small text-muted">${service.per_minute} minutes</div>
                                </div>
                                <button class="btn btn-primary btn-sm" onclick="bookService(${service.id})">
                                    <i class="bi bi-calendar-plus me-1"></i>Book
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    $('#servicesContainer').html(html);
}

// ============= PRODUCTS FUNCTIONS =============
function loadProducts() {
    $('#loadingProducts').show();
    $('#productsContainer').hide();
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
            
            if (result.status === 'success' && result.data && result.data.length > 0) {
                renderProducts(result.data);
                $('#productsContainer').show();
            } else {
                $('#noProducts').show();
            }
        },
        error: function() {
            $('#loadingProducts').hide();
            $('#noProducts').show();
            console.error('Failed to load products');
        }
    });
}

function renderProducts(products) {
    let html = '';
    products.forEach(product => {
        let imageSrc = product.product_image || '../vendor/images/default_product.png';
        if (product.product_image && !product.product_image.startsWith('http') && !product.product_image.startsWith('data:image')) {
            imageSrc = `data:image/png;base64,${product.product_image}`;
        }

        // Stock status
        let stockStatus = '';
        let stockClass = 'text-success';
        if (product.stock_quantity <= 0) {
            stockStatus = 'Out of Stock';
            stockClass = 'text-danger';
        } else if (product.stock_quantity < 10) {
            stockStatus = 'Low Stock';
            stockClass = 'text-warning';
        } else {
            stockStatus = 'In Stock';
            stockClass = 'text-success';
        }

        html += `
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100 product-card" data-product-id="${product.productid}">
                    <div class="position-relative">
                        <img src="${imageSrc}" 
                             class="card-img-top" 
                             style="height: 180px; object-fit: cover;" 
                             alt="${product.product_name}"
                             onerror="this.src='../vendor/images/default_product.png'">
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-success">${product.product_category}</span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2">${product.product_name}</h5>
                        <p class="card-text small text-muted flex-grow-1" style="line-height: 1.4;">
                            ${product.product_description ? (product.product_description.length > 100 ? product.product_description.substring(0, 100) + '...' : product.product_description) : 'No description available'}
                        </p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-success h6 mb-0">₱${parseFloat(product.product_price).toLocaleString()}</span>
                                <small class="${stockClass} fw-semibold">${stockStatus}</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Stock: ${product.stock_quantity}</small>
                                <button class="btn btn-success btn-sm ${product.stock_quantity <= 0 ? 'disabled' : ''}" 
                                        onclick="purchaseProduct(${product.productid})"
                                        ${product.stock_quantity <= 0 ? 'disabled' : ''}>
                                    <i class="bi bi-cart-plus me-1"></i>
                                    ${product.stock_quantity <= 0 ? 'Sold Out' : 'Add to Cart'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    $('#productsContainer').html(html);
}

// ============= INTERACTION FUNCTIONS =============
function bookService(serviceId) {
    // This should integrate with your existing booking modal
    // For now, we'll show a simple message
    Swal.fire({
        title: 'Book Service',
        text: 'This will open the booking modal for service ID: ' + serviceId,
        icon: 'info',
        confirmButtonText: 'OK'
    });
    
    // Uncomment and modify based on your existing booking system:
    // showGlobalModal('modal/user_modal-booking.php', { service_id: serviceId });
}

function purchaseProduct(productId) {
    // This should integrate with your cart/checkout system
    // For now, we'll show a simple message
    Swal.fire({
        title: 'Add to Cart',
        text: 'This will add product ID ' + productId + ' to your cart.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Add to Cart',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Here you would integrate with your cart system
            Swal.fire('Added!', 'Product has been added to your cart.', 'success');
        }
    });
}
</script>

<style>
.service-card, .product-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border: 1px solid rgba(0,0,0,0.125);
}

.service-card:hover, .product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.service-card:hover {
    border-color: #0d6efd;
}

.product-card:hover {
    border-color: #198754;
}

.nav-tabs .nav-link {
    border: none;
    border-radius: 0;
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    background-color: transparent;
    border-bottom: 3px solid #0d6efd;
    font-weight: 600;
    color: #0d6efd;
}

.nav-tabs .nav-link:hover {
    border-color: transparent;
    background-color: rgba(0,0,0,0.05);
}

.card-img-top {
    transition: transform 0.3s ease;
}

.service-card:hover .card-img-top,
.product-card:hover .card-img-top {
    transform: scale(1.05);
}

@media (max-width: 576px) {
    .col-12.col-sm-6.col-md-4.col-lg-3 {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .card-title {
        font-size: 1rem;
    }
    
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
}
</style>