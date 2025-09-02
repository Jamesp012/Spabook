<!-- Product Management Modal -->
<div class="modal fade" id="manageProductModal" tabindex="-1" aria-labelledby="manageProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="manageProductModalLabel">
                    <i class="bi bi-box-seam me-2"></i>
                    <span id="modalTitle">Add New Product</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="productForm" enctype="multipart/form-data">
                    <input type="hidden" id="productId" name="productid">
                    <input type="hidden" id="actionType" name="action" value="add_product">
                    
                    <div class="row g-3">
                        <!-- Product Name -->
                        <div class="col-md-8">
                            <label for="productName" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="productName" name="product_name" required>
                        </div>
                        
                        <!-- Stock Quantity -->
                        <div class="col-md-4">
                            <label for="stockQuantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stockQuantity" name="stock_quantity" min="0" required>
                        </div>
                        
                        <!-- Product Description -->
                        <div class="col-12">
                            <label for="productDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="productDescription" name="product_description" rows="3" placeholder="Enter product description..."></textarea>
                        </div>
                        
                        <!-- Price and Category -->
                        <div class="col-md-6">
                            <label for="productPrice" class="form-label">Price (₱) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="productPrice" name="product_price" step="0.01" min="0" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="productCategory" class="form-label">Category</label>
                            <select class="form-select" id="productCategory" name="product_category">
                                <option value="General">General</option>
                                <option value="Skincare">Skincare</option>
                                <option value="Supplements">Supplements</option>
                                <option value="Accessories">Accessories</option>
                                <option value="Gift Items">Gift Items</option>
                                <option value="Essential Oils">Essential Oils</option>
                            </select>
                        </div>
                        
                        <!-- Product Image -->
                        <div class="col-12">
                            <label for="productImage" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="productImage" accept="image/*">
                            <small class="text-muted">Supported formats: JPG, PNG, GIF (Max 2MB)</small>
                            
                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <img id="previewImage" src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                                <button type="button" class="btn btn-outline-danger btn-sm ms-2" onclick="removeImage()">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="saveProduct()">
                    <i class="bi bi-check-circle me-1"></i>
                    <span id="saveButtonText">Add Product</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Detect product ID from either query string or global modalData (when loaded via AJAX)
    let productId = null;
    try {
        const urlParams = new URLSearchParams(window.location.search || '');
        productId = urlParams.get('productid');
    } catch (e) { /* ignore */ }

    if (!productId && window.modalData && window.modalData.productid) {
        productId = window.modalData.productid;
    }

    if (productId) {
        loadProductForEdit(productId);
    }
    
    // Image upload preview
    $('#productImage').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) { // 2MB limit
                Swal.fire('Error', 'File size must be less than 2MB', 'error');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImage').attr('src', e.target.result);
                $('#imagePreview').show();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Load product categories
    loadProductCategories();
});

function loadProductCategories() {
    $.ajax({
        url: '../controller/product_contr.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'fetch_product_categories' },
        success: function(result) {
            if (result.status === 'success' && result.data.length > 0) {
                let options = '<option value="General">General</option>';
                result.data.forEach(category => {
                    options += `<option value="${category.category_name}">${category.category_name}</option>`;
                });
                $('#productCategory').html(options);
            }
        },
        error: function() {
            console.log('Failed to load product categories');
        }
    });
}

function loadProductForEdit(productId) {
    $('#modalTitle').text('Edit Product');
    $('#saveButtonText').text('Update Product');
    $('#actionType').val('update_product');
    $('#productId').val(productId);
    
    $.ajax({
        url: '../controller/product_contr.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'get_product_by_id',
            productid: productId
        },
        success: function(result) {
            if (result.status === 'success') {
                const product = result.data;
                $('#productName').val(product.product_name);
                $('#productDescription').val(product.product_description);
                $('#productPrice').val(product.product_price);
                $('#productCategory').val(product.product_category);
                $('#stockQuantity').val(product.stock_quantity);
                
                // Show existing image if available
                if (product.product_image) {
                    let imageSrc = product.product_image;
                    if (!imageSrc.startsWith('http') && !imageSrc.startsWith('data:image')) {
                        imageSrc = `data:image/png;base64,${imageSrc}`;
                    }
                    $('#previewImage').attr('src', imageSrc);
                    $('#imagePreview').show();
                }
            } else {
                Swal.fire('Error', 'Failed to load product data', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Network error loading product', 'error');
        }
    });
}

function saveProduct() {
    // Validation
    if (!$('#productName').val().trim()) {
        Swal.fire('Validation Error', 'Product name is required', 'error');
        $('#productName').focus();
        return;
    }
    
    if (!$('#productPrice').val() || parseFloat($('#productPrice').val()) < 0) {
        Swal.fire('Validation Error', 'Valid price is required', 'error');
        $('#productPrice').focus();
        return;
    }
    
    if (!$('#stockQuantity').val() || parseInt($('#stockQuantity').val()) < 0) {
        Swal.fire('Validation Error', 'Valid stock quantity is required', 'error');
        $('#stockQuantity').focus();
        return;
    }
    
    // Prepare form data
    let formData = {
        action: $('#actionType').val(),
        product_name: $('#productName').val().trim(),
        product_description: $('#productDescription').val().trim(),
        product_price: parseFloat($('#productPrice').val()),
        product_category: $('#productCategory').val(),
        stock_quantity: parseInt($('#stockQuantity').val())
    };
    
    if ($('#actionType').val() === 'update_product') {
        formData.productid = $('#productId').val();
    }
    
    // Handle image upload
    const imageFile = $('#productImage')[0].files[0];
    if (imageFile) {
        const reader = new FileReader();
        reader.onload = function(e) {
            formData.product_image = e.target.result;
            submitProductData(formData);
        };
        reader.readAsDataURL(imageFile);
    } else {
        submitProductData(formData);
    }
}

function submitProductData(formData) {
    $.ajax({
        url: '../controller/product_contr.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        beforeSend: function() {
            $('#saveButtonText').text('Saving...');
            $('button').prop('disabled', true);
        },
        success: function(result) {
            if (result.status === 'success') {
                Swal.fire('Success!', result.message, 'success').then(() => {
                    // Hide the appropriate modal (global or standalone)
                    if ($('#globalModal').length > 0 && $('#globalModal').hasClass('show')) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('globalModal'));
                        if (modal) modal.hide();
                    } else if ($('#manageProductModal').length > 0) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('manageProductModal'));
                        if (modal) modal.hide();
                    }
                    
                    // Refresh products list if function exists
                    if (typeof loadProducts === 'function') {
                        loadProducts();
                    } else if (window.parent && typeof window.parent.loadProducts === 'function') {
                        window.parent.loadProducts();
                    }
                });
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Network error occurred', 'error');
        },
        complete: function() {
            $('#saveButtonText').text($('#actionType').val() === 'update_product' ? 'Update Product' : 'Add Product');
            $('button').prop('disabled', false);
        }
    });
}

function removeImage() {
    $('#productImage').val('');
    $('#imagePreview').hide();
    $('#previewImage').attr('src', '');
}

// Clean up when modal is hidden - handle both global and standalone modals
function cleanupProductModal() {
    try {
        const form = document.getElementById('productForm');
        if (form) form.reset();
        
        const imagePreview = $('#imagePreview');
        if (imagePreview.length) imagePreview.hide();
        
        const previewImage = $('#previewImage');
        if (previewImage.length) previewImage.attr('src', '');
        
        const modalTitle = $('#modalTitle');
        if (modalTitle.length) modalTitle.text('Add New Product');
        
        const saveButtonText = $('#saveButtonText');
        if (saveButtonText.length) saveButtonText.text('Add Product');
        
        const actionType = $('#actionType');
        if (actionType.length) actionType.val('add_product');
        
        const productId = $('#productId');
        if (productId.length) productId.val('');
    } catch (e) {
        console.warn('Modal cleanup warning:', e);
    }
}

// Attach cleanup to both possible modals
$(document).on('hidden.bs.modal', '#manageProductModal', cleanupProductModal);
$(document).on('hidden.bs.modal', '#globalModal', cleanupProductModal);
</script>