<?php
/**
 * Products Migration Script for SpaBook
 * 
 * This script shows the SQL statements you need to run in your Supabase dashboard
 * Go to your Supabase project > SQL Editor and run these statements
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Products Migration SQL</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f8f9fa; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .sql-block { background: #f8f9fa; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0; border-radius: 5px; }
        .instructions { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #007bff; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; color: #155724; border-left: 4px solid #28a745; }
        pre { white-space: pre-wrap; font-size: 14px; font-family: 'Courier New', monospace; }
        .badge { background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        h1 { color: #28a745; }
        h3 { color: #333; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🛍️ SpaBook Products & Services Migration</h1>";

echo "<div class='instructions'>
<h3>📋 Instructions:</h3>
<ol>
    <li>Go to your <strong>Supabase Dashboard</strong></li>
    <li>Navigate to <strong>SQL Editor</strong></li>
    <li>Copy and paste the SQL below</li>
    <li>Click <strong>Run</strong> to execute</li>
</ol>
</div>";

echo "<div class='sql-block'>
<h3>💾 SQL Migration Script:</h3>
<pre>";

$migration_sql = "-- ==========================================
-- SpaBook Products & Services Migration
-- ==========================================

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    productid BIGSERIAL PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    product_description TEXT,
    product_price NUMERIC(12,2) NOT NULL DEFAULT 0,
    product_image TEXT NULL,
    product_category VARCHAR(100) DEFAULT 'General',
    stock_quantity INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW()
);

-- Create product_categories table for better organization
CREATE TABLE IF NOT EXISTS product_categories (
    categoryid BIGSERIAL PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    category_description TEXT,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW()
);

-- Insert default product categories
INSERT INTO product_categories (category_name, category_description) VALUES
('Skincare', 'Skincare products and cosmetics'),
('Supplements', 'Health and wellness supplements'),
('Accessories', 'Spa accessories and tools'),
('Gift Items', 'Gift certificates and packages'),
('Essential Oils', 'Aromatherapy and essential oils')
ON CONFLICT (category_name) DO NOTHING;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_products_active ON products(is_active);
CREATE INDEX IF NOT EXISTS idx_products_category ON products(product_category);
CREATE INDEX IF NOT EXISTS idx_products_name ON products(product_name);
CREATE INDEX IF NOT EXISTS idx_product_categories_active ON product_categories(is_active);

-- Insert sample products
INSERT INTO products (product_name, product_description, product_price, product_category, stock_quantity) VALUES
('Relaxation Essential Oil', 'Premium lavender essential oil for relaxation and stress relief', 450.00, 'Essential Oils', 50),
('Anti-Aging Face Cream', 'Advanced anti-aging cream with natural ingredients', 1200.00, 'Skincare', 25),
('Collagen Supplements', 'High-quality collagen capsules for skin health', 850.00, 'Supplements', 100),
('Jade Facial Roller', 'Natural jade stone facial massage roller', 380.00, 'Accessories', 30),
('Spa Gift Certificate', '1-hour spa treatment gift certificate', 1500.00, 'Gift Items', 999),
('Vitamin C Serum', 'Brightening vitamin C serum for all skin types', 680.00, 'Skincare', 40),
('Aromatherapy Diffuser', 'Ultrasonic essential oil diffuser', 920.00, 'Accessories', 15),
('Detox Tea Blend', 'Herbal detox tea for cleansing', 320.00, 'Supplements', 75)
ON CONFLICT DO NOTHING;

-- ==========================================
-- Migration Complete!
-- ==========================================";

echo htmlspecialchars($migration_sql);

echo "</pre>
</div>";

echo "<div class='success'>
<h3>✅ After Migration You Will Have:</h3>
<ul>
    <li><span class='badge'>NEW</span> <strong>Products Management:</strong> Admin can add/edit/delete products</li>
    <li><span class='badge'>UPDATED</span> <strong>Services & Products:</strong> Users can view both services and products</li>
    <li><span class='badge'>NEW</span> <strong>Product Categories:</strong> Skincare, Supplements, Accessories, Gift Items, Essential Oils</li>
    <li><span class='badge'>NEW</span> <strong>Stock Management:</strong> Track product inventory levels</li>
    <li><span class='badge'>NEW</span> <strong>Sample Data:</strong> 8 sample products to get started</li>
</ul>
</div>";

echo "<div class='instructions'>
<h3>🎯 Navigation Updates Made:</h3>
<ul>
    <li><strong>Admin Panel:</strong> 'Manage Services' → 'Manage Services & Products'</li>
    <li><strong>User Panel:</strong> 'Services' → 'Services & Products'</li>
    <li><strong>New Tabs:</strong> Services and Products are now in separate tabs</li>
    <li><strong>New Button:</strong> 'Add New Product' button in admin panel</li>
</ul>
</div>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; color: #856404; border-left: 4px solid #ffc107;'>
<h3>🔧 Files Modified:</h3>
<ul>
    <li><strong>Navigation:</strong> Updated admin and user sidebar labels</li>
    <li><strong>Admin Panel:</strong> Manage Services page now has tabs for Services & Products</li>
    <li><strong>User Interface:</strong> Services page now shows both Services & Products</li>
    <li><strong>New Files:</strong> Product controller, model, and management modal created</li>
</ul>
</div>";

echo "</div></body></html>";
?>