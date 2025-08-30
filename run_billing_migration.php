<?php
require_once './config/supabase.php';

$migration_sql = "
-- Billing & Sales Migration
-- Tables: invoices, invoice_items, therapist_commissions

-- invoices: one per booking
CREATE TABLE IF NOT EXISTS invoices (
    invoice_id BIGSERIAL PRIMARY KEY,
    booking_id BIGINT NOT NULL UNIQUE,
    user_id BIGINT NOT NULL,
    subtotal NUMERIC(12,2) NOT NULL DEFAULT 0,
    discount NUMERIC(12,2) NOT NULL DEFAULT 0,
    total NUMERIC(12,2) NOT NULL DEFAULT 0,
    payment_status TEXT NOT NULL DEFAULT 'Unpaid', -- Unpaid | Down Payment | Paid | Refunded
    payment_method TEXT NULL,
    issued_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

-- invoice_items: line items per service/booking_detail
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

-- therapist_commissions: computed from actual hours logged
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

-- Helpful indexes
CREATE INDEX IF NOT EXISTS idx_invoices_booking ON invoices(booking_id);
CREATE INDEX IF NOT EXISTS idx_invoice_items_invoice ON invoice_items(invoice_id);
CREATE INDEX IF NOT EXISTS idx_commissions_detail ON therapist_commissions(booking_detail_id);
";

try {
    // Split by semicolons and execute each statement
    $statements = explode(';', $migration_sql);
    $success_count = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        $result = executeQuery($statement);
        if ($result !== false) {
            $success_count++;
            echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
        } else {
            echo "✗ Failed: " . substr($statement, 0, 50) . "...\n";
        }
    }
    
    echo "\n✅ Migration completed! Executed {$success_count} statements.\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
}

function executeQuery($sql) {
    global $supabase_url, $supabase_key;
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $supabase_url . '/rest/v1/rpc/exec_sql',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['sql' => $sql]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'apikey: ' . $supabase_key,
            'Authorization: Bearer ' . $supabase_key
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode >= 200 && $httpCode < 300;
}
?>