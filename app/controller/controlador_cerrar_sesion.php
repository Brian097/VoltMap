<?php
session_start();

session_unset();

session_destroy();

header("Location: /app/view/content/login.php");
exit();
?>