<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function require_login() {
if (!isset($_SESSION['user'])) {
header('Location: /DrewCrew/login.php');
exit();
}
}
function require_admin() {
if (!isset($_SESSION['user']) || ($_SESSION['role'] ?? '') !== 'admin') {
header('Location: /DrewCrew/login.php');
exit();
}
}
?>