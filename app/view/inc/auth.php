<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION["id"])) {
    header("Location: /voltmap/app/view/content/login.php");
    exit();
}
?>