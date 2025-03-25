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

$user_id = $_SESSION["user_id"];

// Get orders for the current user
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);

// Get current page for active link styling
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Martina's Indulgence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-pink-50 to-white font-sans min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-white/80 backdrop-blur-md shadow-sm w-full">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="customer_dashboard.php" class="flex items-center space-x-2">
                    <img src="../logo.png" alt="Martina's" class="h-12 w-auto">
                    <div>
                        <h1 class="text-xl font-bold bg-gradient-to-r from-pink-500 to-purple-500 bg-clip-text text-transparent">Martina's</h1>
                        <p class="text-sm text-pink-500">Indulgence</p>
                    </div>
                </a>
                <a href="customer_dashboard.php" class="text-gray-700 hover:text-pink-500 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Products
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-5xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">My Orders</h1>
        
        <?php if (mysqli_num_rows($orders_result) > 0): ?>
            <div class="space-y-6">
                <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <!-- Order Header -->
                        <div class="px-6 py-4 flex flex-wrap items-center justify-between bg-gray-50 border-b border-gray-200">
                            <div>
                                <p class="text-sm text-gray-500">Order #<?= $order['id'] ?></p>
                                <p class="text-xs text-gray-400"><?= date('F j, Y \a\t g:i A', strtotime($order['order_date'])) ?></p>
                            </div>
                            
                            <?php
                            $status_class = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'shipped' => 'bg-purple-100 text-purple-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                            $status = $order['status'];
                            $class = $status_class[$status] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?= $class ?>">
                                <?= ucfirst($status) ?>
                            </span>
                        </div>
                        
                        <!-- Order Items -->
                        <div class="px-6 py-4">
                            <?php
                            // Get items for this order
                            $items_query = "SELECT oi.*, p.name, p.image_url FROM order_items oi 
                                           JOIN products p ON oi.product_id = p.id 
                                           WHERE oi.order_id = " . $order['id'];
                            $items_result = mysqli_query($conn, $items_query);
                            ?>
                            
                            <div class="space-y-4">
                                <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                                    <div class="flex items-center">
                                        <?php if (!empty($item['image_url'])): ?>
                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                                 onerror="this.onerror=null; this.src='uploads/<?= htmlspecialchars($item['image_url']) ?>'; this.onerror=function(){this.src='images/no-image.png'; this.onerror=null;}"
                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                 class="h-16 w-16 object-cover rounded-md mr-4">
                                        <?php else: ?>
                                            <div class="h-16 w-16 bg-gray-100 rounded-md flex items-center justify-center mr-4">
                                                <i class="fas fa-birthday-cake text-gray-300 text-xl"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="flex-1">
                                            <h3 class="text-gray-800 font-medium"><?= htmlspecialchars($item['name']) ?></h3>
                                            <p class="text-gray-500 text-sm">Qty: <?= $item['quantity'] ?> × $<?= number_format($item['price'], 2) ?></p>
                                        </div>
                                        
                                        <div class="text-right">
                                            <p class="text-gray-800 font-medium">$<?= number_format($item['item_total'], 2) ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        
                        <!-- Order Footer -->
                        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500"><?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></p>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($order['shipping_address']) ?>, <?= htmlspecialchars($order['shipping_city']) ?></p>
                            </div>
                            
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Total</p>
                                <p class="text-xl font-bold text-pink-600">$<?= number_format($order['total_amount'], 2) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16 bg-white rounded-xl shadow-sm">
                <div class="mb-4 text-pink-400">
                    <i class="fas fa-shopping-bag text-6xl"></i>
                </div>
                <h2 class="text-xl font-medium text-gray-800 mb-2">No orders yet</h2>
                <p class="text-gray-500 mb-6">You haven't placed any orders yet</p>
                <a href="customer_dashboard.php" class="px-6 py-3 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors inline-block">
                    Browse Products
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 