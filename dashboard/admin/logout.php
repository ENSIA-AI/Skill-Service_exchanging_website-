<?php
/**
 * Admin Logout
 */

session_start();
session_destroy();
header('Location: ../../auth/login.php?logout=true');
exit;
?>
