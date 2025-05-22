<?php
require_once 'classes/Session.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$session = new Session();

// Destroy the session
$session->destroy();

// Force clear browser cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Redirect to home page with a cache-busting parameter
header("Location: index.php?t=" . time());
exit();
?> 