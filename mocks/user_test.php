<?php
 Auth::requireRole('admin');
 require_once __DIR__ . '/../config/config.php';
 $users = Dashboard::searchUsers("%", 1 );

?>



