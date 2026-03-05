<?php
// Email Configuration
return [
    // Set to true to enable real email sending (false = log only)
    'enable_sending' => true,
    
    // SMTP Configuration
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 465,
        'encryption' => 'ssl', // ssl for port 465
        'username' => 'diffindocakes@gmail.com', // Your Gmail address
        'password' => 'jqbpzwighkzvgldd',    // Gmail App Password (not regular password)
    ],
    
    // Email Settings
    'from_email' => 'diffindocakes@gmail.com',
    'from_name' => 'Diffindo Cakes & Bakes',
    'reply_to' => 'diffindocakes@gmail.com',
];
?>