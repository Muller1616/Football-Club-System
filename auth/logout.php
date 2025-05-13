<?php
include_once '../includes/functions.php';
session_start_if_not_started();

// Destroy all session data
session_destroy();

// Redirect to login page
header("Location: ../index.php");
exit();
?>
