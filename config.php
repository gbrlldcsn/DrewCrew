<?php
$host = "localhost";  // usually: localhost
$user = "root";       // your MySQL username
$pass = "";            // your MySQL password (XAMPP default is empty)
$dbname = "clothing_db"; // ✅ name ng database mo

$conn = new mysqli($host, $user, $pass, $dbname);

// ✅ Check database connection
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}
?>
