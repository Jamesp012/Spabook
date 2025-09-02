<?php
class Product {
    
    public function fetchProducts($php_fetch) {
        try {
            $result = $php_fetch('products', '*', ['is_active' => true]);
            
            if ($result && count($result) > 0) {
                return json_encode([
                    'status' => 'success',
                    'data' => $result
                ]);
            } else {
                return json_encode([
                    'status' => 'success',
                    'data' => []
                ]);
            }
        } catch (Exception $e) {
            error_log("Error in fetchProducts: " . $e->getMessage());
            return json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch products'
            ]);
        }
    }
    
    public function addProduct($php_insert, $product_name, $product_description, $product_price, $product_category, $stock_quantity, $product_image) {
        try {
            $data = [
                'product_name' => $product_name,
                'product_description' => $product_description,
                'product_price' => $product_price,
                'product_category' => $product_category,
                'stock_quantity' => $stock_quantity,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($product_image) {
                $data['product_image'] = $product_image;
            }
            
            $result = $php_insert('products', $data);
            
            if ($result) {
                return json_encode([
                    'status' => 'success',
                    'message' => 'Product added successfully'
                ]);
            } else {
                return json_encode([
                    'status' => 'error',
                    'message' => 'Failed to add product'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error in addProduct: " . $e->getMessage());
            return json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function updateProduct($php_update, $productid, $product_name, $product_description, $product_price, $product_category, $stock_quantity, $product_image) {
        try {
            $data = [
                'product_name' => $product_name,
                'product_description' => $product_description,
                'product_price' => $product_price,
                'product_category' => $product_category,
                'stock_quantity' => $stock_quantity,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($product_image) {
                $data['product_image'] = $product_image;
            }
            
            $result = $php_update('products', $data, ['productid' => $productid]);
            
            if ($result) {
                return json_encode([
                    'status' => 'success',
                    'message' => 'Product updated successfully'
                ]);
            } else {
                return json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update product'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error in updateProduct: " . $e->getMessage());
            return json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function deleteProduct($php_update, $productid) {
        try {
            // Soft delete by setting is_active to false
            $data = [
                'is_active' => false,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $php_update('products', $data, ['productid' => $productid]);
            
            if ($result) {
                return json_encode([
                    'status' => 'success',
                    'message' => 'Product deleted successfully'
                ]);
            } else {
                return json_encode([
                    'status' => 'error',
                    'message' => 'Failed to delete product'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error in deleteProduct: " . $e->getMessage());
            return json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getProductById($php_fetch, $productid) {
        try {
            $result = $php_fetch('products', '*', ['productid' => $productid, 'is_active' => true]);
            
            if ($result && count($result) > 0) {
                return json_encode([
                    'status' => 'success',
                    'data' => $result[0]
                ]);
            } else {
                return json_encode([
                    'status' => 'error',
                    'message' => 'Product not found'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error in getProductById: " . $e->getMessage());
            return json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch product'
            ]);
        }
    }
    
    public function updateProductStock($php_update, $productid, $new_quantity) {
        try {
            $data = [
                'stock_quantity' => $new_quantity,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $php_update('products', $data, ['productid' => $productid]);
            
            if ($result) {
                return json_encode([
                    'status' => 'success',
                    'message' => 'Stock updated successfully'
                ]);
            } else {
                return json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update stock'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error in updateProductStock: " . $e->getMessage());
            return json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
}
?>