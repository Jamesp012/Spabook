<?php
// Ensure session is started for all product operations
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Product controller
if (isset($_POST['action'])) {
    date_default_timezone_set('Asia/Manila');
    require_once '../config/connection.php';
    require_once '../model/product_model.php';
    $Product = new Product();
    $action = trim($_POST['action']);
    $current_date = date('Y-m-d');
    $timestamp = new DateTime('now');
    $current_datetimestamp = $timestamp->format('Y-m-d H:i:s');
    
    switch ($action) {
        case 'fetch_products':
            echo $Product->fetchProducts($php_fetch, 'products');
            break;

        case 'add_product':
            $product_name = trim($_POST['product_name']);
            $product_description = trim($_POST['product_description'] ?? '');
            $product_price = floatval($_POST['product_price']);
            $product_category = trim($_POST['product_category'] ?? 'General');
            $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
            $product_image = $_POST['product_image'] ?? null;
            
            echo $Product->addProduct($php_insert, 'products', $product_name, $product_description, $product_price, $product_category, $stock_quantity, $product_image);
            break;

        case 'update_product':
            $productid = intval($_POST['productid']);
            $product_name = trim($_POST['product_name']);
            $product_description = trim($_POST['product_description'] ?? '');
            $product_price = floatval($_POST['product_price']);
            $product_category = trim($_POST['product_category'] ?? 'General');
            $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
            $product_image = $_POST['product_image'] ?? null;
            
            echo $Product->updateProduct($php_update, 'products', $productid, $product_name, $product_description, $product_price, $product_category, $stock_quantity, $product_image);
            break;

        case 'delete_product':
            $productid = intval($_POST['productid']);
            echo $Product->deleteProduct($php_update, 'products', $productid);
            break;

        case 'get_product_by_id':
            $productid = intval($_POST['productid']);
            echo $Product->getProductById($php_fetch, 'products', $productid);
            break;

        case 'fetch_product_categories':
            echo $Product->fetchProductCategories($php_fetch, 'product_categories');
            break;

        case 'update_product_stock':
            $productid = intval($_POST['productid']);
            $new_quantity = intval($_POST['stock_quantity']);
            echo $Product->updateProductStock($php_update, 'products', $productid, $new_quantity);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
}
?>