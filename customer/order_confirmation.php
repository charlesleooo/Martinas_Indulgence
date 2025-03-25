<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../dbconn.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Check if there's an order success message
if (!isset($_SESSION['order_success']) || !isset($_SESSION['order_id'])) {
    header("Location: customer_dashboard.php");
    exit();
}

$order_id = $_SESSION['order_id'];

// Get order details
$order_query = "SELECT * FROM orders WHERE id = $order_id AND user_id = " . $_SESSION['user_id'];
$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header("Location: customer_dashboard.php");
    exit();
}

// Clear the session variables
unset($_SESSION['order_success']);
unset($_SESSION['order_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Martina's Indulgence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-pink-50 to-white font-sans min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-white/80 backdrop-blur-md shadow-sm w-full">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="customer_dashboard.php" class="flex items-center space-x-2">
                    <img src="logo.png" alt="Martina's" class="h-12 w-auto">
                    <div>
                        <h1 class="text-xl font-bold bg-gradient-to-r from-pink-500 to-purple-500 bg-clip-text text-transparent">Martina's</h1>
                        <p class="text-sm text-pink-500">Indulgence</p>
                    </div>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-3xl mx-auto px-4 py-12">
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-8 text-center">
                <div class="mb-6 inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full">
                    <i class="fas fa-check text-2xl text-green-600"></i>
                </div>
                
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Order Confirmed!</h1>
                <p class="text-gray-600 mb-6">Thank you for your order. We've received your request and are working on it.</p>
                
                <div class="bg-gray-50 rounded-lg p-4 mb-6 inline-block">
                    <p class="text-gray-700">Order #<?= $order_id ?></p>
                    <p class="text-sm text-gray-500">Placed on <?= date('F j, Y', strtotime($order['order_date'])) ?></p>
                </div>
                
                <div class="mb-8">
                    <h3 class="font-medium text-gray-800 mb-2 text-left">Order Details:</h3>
                    <div class="border border-gray-200 rounded-md overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-gray-600">Total Amount:</span>
                                <span class="font-bold text-pink-600">$<?= number_format($order['total_amount'], 2) ?></span>
                            </div>
                        </div>
                        <div class="p-4 text-left">
                            <p class="mb-2"><span class="font-medium text-gray-700">Shipping Address:</span> <?= htmlspecialchars($order['shipping_address']) ?>, <?= htmlspecialchars($order['shipping_city']) ?></p>
                            <p class="mb-2"><span class="font-medium text-gray-700">Contact:</span> <?= htmlspecialchars($order['shipping_name']) ?>, <?= htmlspecialchars($order['shipping_phone']) ?></p>
                            <p><span class="font-medium text-gray-700">Payment Method:</span> <?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-center space-x-4">
                    <a href="customer_dashboard.php" class="px-6 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors">
                        Continue Shopping
                    </a>
                    <a href="my_orders.php" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        View My Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 