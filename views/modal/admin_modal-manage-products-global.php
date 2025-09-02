<!-- Product Management Modal Content for Global Modal -->
<div class="modal-header bg-success text-white">
    <h5 class="modal-title">
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

<script>
$(document).ready(function() {
    // Initialize the modal for global modal system
    initializeProductModal();
});

function initializeProductModal() {
    // Detect product ID from URL parameters or modal data
    let productId = null;
    
    // Check URL parameters first
    try {
        const urlParams = new URLSearchParams(window.location.search || '');
        productId = urlParams.get('productid');
    } catch (e) { /* ignore */ }

    // Check global modal data
    if (!productId && window.modalData && window.modalData.productid) {
        productId = window.modalData.productid;
    }

    // If we have a product ID, load it for editing
    if (productId) {
        loadProductForEdit(productId);
    }
    
    // Setup image preview functionality
    setupImagePreview();
}

function setupImagePreview() {
    $('#productImage').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (2MB limit)
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire('Error', 'File size must be less than 2MB', 'error');
                $(this).val('');
                return;
            }
            
            // Validate file type
            if (!file.type.match('image.*')) {
                Swal.fire('Error', 'Please select a valid image file', 'error');
                $(this).val('');
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
}

function loadProductForEdit(productId) {
    // Update modal title and button text for editing
    $('#modalTitle').text('Edit Product');
    $('#saveButtonText').text('Update Product');
    $('#actionType').val('update_product');
    $('#productId').val(productId);
    
    // Load product data
    $.ajax({
        url: '../controller/product_contr.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'get_product_by_id',
            productid: productId
        },
        success: function(result) {
            if (result.status === 'success' && result.data) {
                const product = result.data;
                
                // Populate form fields
                $('#productName').val(product.product_name || '');
                $('#stockQuantity').val(product.stock_quantity || '');
                $('#productDescription').val(product.product_description || '');
                $('#productPrice').val(product.product_price || '');
                $('#productCategory').val(product.product_category || 'General');
                
                // Handle product image
                if (product.product_image) {
                    let imageSrc = product.product_image;
                    if (!imageSrc.startsWith('http') && !imageSrc.startsWith('data:image')) {
                        imageSrc = `data:image/png;base64,${product.product_image}`;
                    }
                    $('#previewImage').attr('src', imageSrc);
                    $('#imagePreview').show();
                }
            } else {
                Swal.fire('Error', 'Failed to load product data', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to load product data', 'error');
        }
    });
}

function removeImage() {
    $('#productImage').val('');
    $('#imagePreview').hide();
    $('#previewImage').attr('src', '');
}

function saveProduct() {
    // Validate required fields
    if (!$('#productName').val().trim()) {
        Swal.fire('Error', 'Product name is required', 'error');
        $('#productName').focus();
        return;
    }
    
    if (!$('#stockQuantity').val() || $('#stockQuantity').val() < 0) {
        Swal.fire('Error', 'Valid stock quantity is required', 'error');
        $('#stockQuantity').focus();
        return;
    }
    
    if (!$('#productPrice').val() || $('#productPrice').val() <= 0) {
        Swal.fire('Error', 'Valid product price is required', 'error');
        $('#productPrice').focus();
        return;
    }
    
    // Show loading state
    const saveButton = $('button[onclick="saveProduct()"]');
    const originalText = saveButton.html();
    saveButton.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>Saving...');
    
    // Handle image conversion if file is selected
    const imageFile = $('#productImage')[0].files[0];
    
    if (imageFile) {
        // Convert image to base64
        const reader = new FileReader();
        reader.onload = function(e) {
            const base64String = e.target.result.split(',')[1]; // Remove data:image/...;base64, prefix
            submitProductData(base64String);
        };
        reader.onerror = function() {
            saveButton.prop('disabled', false).html(originalText);
            Swal.fire('Error', 'Failed to process image file', 'error');
        };
        reader.readAsDataURL(imageFile);
    } else {
        // No image file, submit without image
        submitProductData(null);
    }
    
    function submitProductData(imageBase64) {
        // Prepare form data
        const formData = {
            action: $('#actionType').val(),
            product_name: $('#productName').val().trim(),
            stock_quantity: $('#stockQuantity').val(),
            product_description: $('#productDescription').val().trim(),
            product_price: $('#productPrice').val(),
            product_category: $('#productCategory').val()
        };
        
        // Add product ID if editing
        if ($('#actionType').val() === 'update_product') {
            formData.productid = $('#productId').val();
        }
        
        // Add image if provided
        if (imageBase64) {
            formData.product_image = imageBase64;
        }
        
        // Submit form
        $.ajax({
            url: '../controller/product_contr.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(result) {
                saveButton.prop('disabled', false).html(originalText);
                
                if (result.status === 'success') {
                    Swal.fire('Success!', result.message, 'success').then(() => {
                        // Hide the global modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('globalModal'));
                        if (modal) modal.hide();
                        
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
            error: function(xhr, status, error) {
                saveButton.prop('disabled', false).html(originalText);
                console.error('Save product error:', {xhr, status, error});
                Swal.fire('Error', 'Failed to save product. Please try again.', 'error');
            }
        });
    }
}

// Clean up when modal is hidden
$(document).on('hidden.bs.modal', '#globalModal', function() {
    try {
        const form = document.getElementById('productForm');
        if (form) form.reset();
        
        $('#imagePreview').hide();
        $('#previewImage').attr('src', '');
        $('#modalTitle').text('Add New Product');
        $('#saveButtonText').text('Add Product');
        $('#actionType').val('add_product');
        $('#productId').val('');
    } catch (e) {
        console.warn('Modal cleanup warning:', e);
    }
});
</script>