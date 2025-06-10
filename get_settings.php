<?php
header('Content-Type: application/json');
$settingsFile = __DIR__ . '/settings.json';
if (file_exists($settingsFile)) {
    $json = file_get_contents($settingsFile);
    $settings = json_decode($json, true);
    if ($settings) {
        echo json_encode(['success' => true, 'settings' => $settings]);
        exit;
    }
}
// Defaults if not found
$defaults = [
    'general' => [
        'libraryName' => 'Central Public Library',
        'libraryCode' => 'CPL001',
        'libraryAddress' => '123 Main Street, City Center, Metro Manila, Philippines',
        'contactPhone' => '+63 2 1234 5678',
        'contactEmail' => 'info@centrallibrary.ph'
    ],
    'operatingHours' => [
        'openTime' => '08:00',
        'closeTime' => '20:00',
        'operatingDays' => [
            'monday' => true,
            'tuesday' => true,
            'wednesday' => true,
            'thursday' => true,
            'friday' => true,
            'saturday' => true,
            'sunday' => false
        ]
    ],
    'attendance' => [
        'enableAttendance' => true,
        'attendanceMethod' => 'barcode',
        'autoCheckout' => 8,
        'guestTracking' => true,
        'attendanceReports' => false
    ],
    'circulation' => [
        'loanPeriod' => 14,
        'renewalLimit' => 2,
        'maxBooks' => 5,
        'finePerDay' => 5.00,
        'gracePeriod' => 3,
        'maxFine' => 500.00,
        'autoRenewal' => true,
        'overdueNotifications' => true
    ],
    'notifications' => [
        'notificationMethod' => 'email',
        'dueReminder' => 3,
        'overdueReminder' => 7,
        'checkoutNotification' => true,
        'returnNotification' => false,
        'holdNotification' => true
    ],
    'security' => [
        'sessionTimeout' => 30,
        'backupFrequency' => 'daily',
        'auditLog' => true,
        'loginAttempts' => false,
        'maxAttempts' => 3
    ],
    'system' => [
        'timezone' => 'Asia/Manila',
        'dateFormat' => 'DD/MM/YYYY',
        'language' => 'en',
        'currency' => 'PHP',
        'maintenanceMode' => false,
        'debugMode' => false
    ]
];
echo json_encode(['success' => true, 'settings' => $defaults]); 