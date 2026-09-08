<?php
session_start();

session_unset();

session_destroy();

header("Location: /voltmap/app/view/content/login.php");
exit();
?>