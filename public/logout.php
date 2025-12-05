<?php
// Logout (7.4)
session_start();
session_unset();
session_destroy();

// Redirect to homepage
header('Location: index.php');
exit;
?>