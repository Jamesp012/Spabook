<?php
// Sample script to add therapist data for testing
require_once 'config/connection.php';
require_once 'model/therapist_model.php';

$TherapistModel = new TherapistModel();

// Sample therapists data with enhanced fields
$therapists = [
    [
        'therapist_name' => 'Maria Santos',
        'service_id' => 1, // Assuming service ID 1 exists
        'therapist_desc' => 'Certified massage therapist with 5+ years experience in Swedish and deep tissue massage.',
        'specialties' => 'Swedish Massage, Deep Tissue, Prenatal Massage',
        'experience_years' => 5,
        'certification' => 'Licensed Massage Therapist (LMT)',
        'phone' => '+63 912 345 6789',
        'active' => true
    ],
    [
        'therapist_name' => 'Juan Cruz',
        'service_id' => 1, // Same service
        'therapist_desc' => 'Licensed therapeutic massage specialist, expert in sports massage and injury recovery.',
        'specialties' => 'Sports Massage, Injury Recovery, Trigger Point Therapy',
        'experience_years' => 7,
        'certification' => 'Certified Sports Massage Therapist',
        'phone' => '+63 923 456 7890',
        'active' => true
    ],
    [
        'therapist_name' => 'Ana Rodriguez',
        'service_id' => 2, // Different service
        'therapist_desc' => 'Professional spa therapist specializing in relaxation and aromatherapy treatments.',
        'specialties' => 'Aromatherapy, Relaxation Therapy, Essential Oils',
        'experience_years' => 4,
        'certification' => 'Aromatherapy Certification',
        'phone' => '+63 934 567 8901',
        'active' => true
    ],
    [
        'therapist_name' => 'Carlos Mendoza',
        'service_id' => 2,
        'therapist_desc' => 'Experienced wellness practitioner focused on holistic healing and stress relief.',
        'specialties' => 'Holistic Therapy, Stress Management, Wellness Coaching',
        'experience_years' => 6,
        'certification' => 'Certified Wellness Practitioner',
        'phone' => '+63 945 678 9012',
        'active' => true
    ],
    [
        'therapist_name' => 'Sofia Garcia',
        'service_id' => 3,
        'therapist_desc' => 'Senior therapist with expertise in hot stone massage and reflexology techniques.',
        'specialties' => 'Hot Stone Massage, Reflexology, Energy Healing',
        'experience_years' => 8,
        'certification' => 'Advanced Hot Stone Therapy Certificate',
        'phone' => '+63 956 789 0123',
        'active' => true
    ],
    [
        'therapist_name' => 'Miguel Torres',
        'service_id' => 3,
        'therapist_desc' => 'Certified in multiple massage modalities including Thai massage and trigger point therapy.',
        'specialties' => 'Thai Massage, Trigger Point, Myofascial Release',
        'experience_years' => 9,
        'certification' => 'Thai Massage Certification, Trigger Point Specialist',
        'phone' => '+63 967 890 1234',
        'active' => true
    ],
    [
        'therapist_name' => 'Isabella Luna',
        'service_id' => 4,
        'therapist_desc' => 'Skincare specialist and facial therapist with advanced certification in anti-aging treatments.',
        'specialties' => 'Anti-aging Facials, Skincare Analysis, Chemical Peels',
        'experience_years' => 6,
        'certification' => 'Licensed Esthetician, Anti-aging Specialist',
        'phone' => '+63 978 901 2345',
        'active' => true
    ],
    [
        'therapist_name' => 'Rafael Villanueva',
        'service_id' => 4,
        'therapist_desc' => 'Licensed esthetician focusing on deep cleansing facials and acne treatment.',
        'specialties' => 'Acne Treatment, Deep Cleansing, Microdermabrasion',
        'experience_years' => 4,
        'certification' => 'Licensed Esthetician, Acne Specialist',
        'phone' => '+63 989 012 3456',
        'active' => true
    ],
    [
        'therapist_name' => 'Carmen Reyes',
        'service_id' => 5,
        'therapist_desc' => 'Experienced body treatment specialist skilled in detoxification and skin rejuvenation.',
        'specialties' => 'Body Detox, Skin Rejuvenation, Body Wraps',
        'experience_years' => 5,
        'certification' => 'Body Treatment Specialist',
        'phone' => '+63 990 123 4567',
        'active' => true
    ],
    [
        'therapist_name' => 'Diego Morales',
        'service_id' => 5,
        'therapist_desc' => 'Wellness therapist with background in alternative healing and natural treatments.',
        'specialties' => 'Alternative Healing, Natural Treatments, Holistic Wellness',
        'experience_years' => 10,
        'certification' => 'Alternative Healing Practitioner',
        'phone' => '+63 901 234 5678',
        'active' => true
    ]
];

echo "<h2>Adding Sample Therapists</h2>\n";

foreach ($therapists as $therapist) {
    try {
        $result = $TherapistModel->addTherapist($php_insert, 'therapist', $therapist);
        $decoded = json_decode($result, true);
        
        if ($decoded['status'] === 'success') {
            echo "<p>✅ Added: {$therapist['therapist_name']} for Service ID {$therapist['service_id']}</p>\n";
        } else {
            echo "<p>❌ Failed to add: {$therapist['therapist_name']}</p>\n";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error adding {$therapist['therapist_name']}: " . $e->getMessage() . "</p>\n";
    }
}

echo "<h3>Sample therapists have been added to the database!</h3>";
echo "<p><strong>Note:</strong> Make sure the service IDs (1-5) exist in your services table, or update the service_id values above.</p>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sample Therapists Added</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h2 { color: #007bff; }
        p { margin: 10px 0; }
    </style>
</head>
<body>
    <h2>Therapist Selection Feature</h2>
    <p>The therapist selection feature has been successfully implemented with the following features:</p>
    <ul>
        <li>✅ <strong>Service-specific therapists</strong> - Only therapists qualified for the selected service are shown</li>
        <li>✅ <strong>Multiple people support</strong> - Select different therapists for each person in the booking</li>
        <li>✅ <strong>Visual therapist cards</strong> - Click-friendly interface with therapist information</li>
        <li>✅ <strong>Optional selection</strong> - Users can proceed without selecting therapists</li>
        <li>✅ <strong>Cart integration</strong> - Therapist assignments are saved with the booking</li>
        <li>✅ <strong>Checkout display</strong> - Shows selected therapists in the cart summary</li>
    </ul>
    
    <h3>How to Test:</h3>
    <ol>
        <li>Navigate to the user booking appointment page</li>
        <li>Click on any service card</li>
        <li>Adjust the number of people (1-10)</li>
        <li>Select therapists for each person (or leave unselected)</li>
        <li>Add to cart and view in checkout</li>
    </ol>
    
    <p><strong>Database Tables Used:</strong></p>
    <ul>
        <li><code>therapist</code> - Stores therapist information</li>
        <li><code>booking_details</code> - Now includes therapist_id and person_number fields</li>
    </ul>
</body>
</html>