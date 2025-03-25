<?php
session_start();
require_once "../dbconn.php";

if (!isset($_SESSION["user_id"]) || !isset($_POST['product_id'])) {
    header("Location: customer/customer_dashboard.php");
    exit();
}

$product_id = $_POST['product_id'];

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Add product to cart
if (!isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] = 1;
} else {
    $_SESSION['cart'][$product_id]++;
}

// Redirect back to the previous page
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?> 