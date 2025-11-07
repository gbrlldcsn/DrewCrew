<?php
session_start();
function require_login() {
if (!isset($_SESSION['user'])) {
header('Location: /drewcrew/login.php');
exit();
}
}
function require_admin() {
if (!isset($_SESSION['user']) || ($_SESSION['role'] ?? '') !== 'admin') {
header('Location: /drewcrew/login.php');
exit();
}
}
?>