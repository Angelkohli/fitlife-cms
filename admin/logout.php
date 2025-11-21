<?php
// Admin Logout
session_start();
session_destroy();
header('Location: ../public/index.php');
exit;
?>