<?php
$servername = "localhost";  // Change if necessary
$username = "root";         // Change to your DB username
$password = "";             // Change to your DB password
$dbname = "martinas_indulgence";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
