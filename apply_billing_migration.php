<?php
/**
 * Simple Migration Script for Billing & Sales Tables
 * 
 * This script shows the SQL statements you need to run in your Supabase dashboard
 * Go to your Supabase project > SQL Editor and run these statements
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Billing Migration SQL</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .sql-block { background: #f5f5f5; padding: 20px; border-left: 4px solid #007bff; margin: 20px 0; }
        .instructions { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
        pre { white-space: pre-wrap; font-size: 14px; }
    </style>
</head>
<body>";

echo "<h1>🏥 SpaBook Billing & Sales Migration</h1>";

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
-- SpaBook Billing & Sales Migration
-- ==========================================

-- Create invoices table (one per booking)
CREATE TABLE IF NOT EXISTS invoices (
    invoice_id BIGSERIAL PRIMARY KEY,
    booking_id BIGINT NOT NULL UNIQUE,
    user_id BIGINT NOT NULL,
    subtotal NUMERIC(12,2) NOT NULL DEFAULT 0,
    discount NUMERIC(12,2) NOT NULL DEFAULT 0,
    total NUMERIC(12,2) NOT NULL DEFAULT 0,
    payment_status TEXT NOT NULL DEFAULT 'Unpaid',
    payment_method TEXT NULL,
    issued_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

-- Create invoice_items table (line items per service)
CREATE TABLE IF NOT EXISTS invoice_items (
    item_id BIGSERIAL PRIMARY KEY,
    invoice_id BIGINT NOT NULL REFERENCES invoices(invoice_id) ON DELETE CASCADE,
    booking_detail_id BIGINT NULL,
    service_id BIGINT NOT NULL,
    description TEXT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price NUMERIC(12,2) NOT NULL DEFAULT 0,
    line_total NUMERIC(12,2) NOT NULL DEFAULT 0
);

-- Create therapist_commissions table (₱50/hour from actual logged hours)
CREATE TABLE IF NOT EXISTS therapist_commissions (
    commission_id BIGSERIAL PRIMARY KEY,
    booking_detail_id BIGINT NOT NULL,
    therapist_id BIGINT NOT NULL,
    hours NUMERIC(10,2) NOT NULL DEFAULT 0,
    rate_per_hour NUMERIC(12,2) NOT NULL DEFAULT 50.00,
    commission_amount NUMERIC(12,2) NOT NULL DEFAULT 0,
    computed_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    UNIQUE (booking_detail_id, therapist_id)
);

-- Create helpful indexes for performance
CREATE INDEX IF NOT EXISTS idx_invoices_booking ON invoices(booking_id);
CREATE INDEX IF NOT EXISTS idx_invoice_items_invoice ON invoice_items(invoice_id);
CREATE INDEX IF NOT EXISTS idx_commissions_detail ON therapist_commissions(booking_detail_id);
CREATE INDEX IF NOT EXISTS idx_invoices_user ON invoices(user_id);
CREATE INDEX IF NOT EXISTS idx_invoices_status ON invoices(payment_status);
CREATE INDEX IF NOT EXISTS idx_commissions_therapist ON therapist_commissions(therapist_id);

-- ==========================================
-- Migration Complete!
-- ==========================================";

echo htmlspecialchars($migration_sql);

echo "</pre>
</div>";

echo "<div class='instructions'>
<h3>✅ After Migration:</h3>
<ul>
    <li><strong>Sales Report:</strong> Available in Admin Panel → Sales Report</li>
    <li><strong>Billing:</strong> Available in Admin Panel → Billing</li>
    <li><strong>User Invoices:</strong> Automatically shown in User History</li>
    <li><strong>Auto-Generation:</strong> Invoices created when bookings are Confirmed</li>
    <li><strong>Commission:</strong> Calculated at ₱50/hour from therapist time logs</li>
</ul>
</div>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; color: #155724;'>
<h3>🎉 Features Added:</h3>
<ul>
    <li>📊 <strong>Sales Dashboard</strong> - Daily/Weekly/Monthly reports</li>
    <li>💰 <strong>Commission Tracking</strong> - ₱50/hour from logged time</li>
    <li>🧾 <strong>Invoice Management</strong> - Auto-generated on booking confirmation</li>
    <li>💳 <strong>Payment Status</strong> - Unpaid, Down Payment, Paid, Refunded</li>
    <li>📱 <strong>User History</strong> - Invoice display in user booking history</li>
</ul>
</div>";

echo "</body></html>";
?>