<?php
session_start();
require_once "../dbconn.php";

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

if (isset($_GET["id"])) {
    $id = mysqli_real_escape_string($conn, $_GET["id"]);
    $sql = "SELECT * FROM products WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            header('Content-Type: application/json');
            echo json_encode($row);
        } else {
            header("HTTP/1.1 404 Not Found");
            echo "Product not found";
        }
    }
}
?> 