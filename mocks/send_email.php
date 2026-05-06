<?php
// Auth::requireRole('admin');
 require_once __DIR__ . '/../config/config.php';

// 1. Enviar Email
Messenger::sendEmail('magil@h-h.com.mx', 'Seguridad Admindash', 'password_reset', [
    'nombre' => 'Juan Pérez',
    'codigo' => '445522'
]);

// 2. Enviar SMS
Messenger::sendSMS('+525512345678', 'Admindash: Tu codigo de seguridad es 445522');

echo json_encode(['status' => 'success']);

?>