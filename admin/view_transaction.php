<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../dbconn.php";

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Get order id from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: transaction.php");
    exit();
}

$order_id = (int)$_GET['id'];

// Get order details
$order_query = "SELECT o.*, u.username, u.email,
                COALESCE(o.order_date, NOW()) as order_date 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = $order_id";
$order_result = mysqli_query($conn, $order_query);

if (!$order_result || mysqli_num_rows($order_result) == 0) {
    header("Location: transaction.php");
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Get order items
$items_query = "SELECT oi.*, p.name, p.image_url FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);

// Get current page for active link styling
$current_page = 'transaction.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction #<?= $order_id ?> - Admin Dashboard - Martina's Indulgence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-pink-50 font-sans">
    <div class="flex">
        <!-- Sidebar -->
        <div class="bg-white shadow-lg w-64 min-h-screen flex flex-col transition-all duration-300" id="sidebar">
            <!-- Logo and brand -->
            <div class="px-6 py-4 border-b border-pink-100">
                <h1 class="text-2xl font-bold text-pink-600">Martina's</h1>
                <p class="text-sm text-pink-400">Admin Dashboard</p>
            </div>
            
            <!-- Admin profile -->
            <div class="flex items-center px-6 py-3 border-b border-pink-100">
                <div class="w-10 h-10 rounded-full bg-pink-200 flex items-center justify-center">
                    <span class="text-pink-600 font-bold"><?= substr($_SESSION["username"], 0, 1) ?></span>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($_SESSION["username"]) ?></p>
                    <p class="text-xs text-pink-500">Administrator</p>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4">
                <!-- Include your sidebar navigation here -->
                <div class="text-xs uppercase tracking-wider text-pink-400 font-semibold mb-2 px-3">Main</div>
                
                <a href="admin_dashboard.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'admin_dashboard.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fas fa-tachometer-alt w-5 text-center"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                
                <a href="orders.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'orders.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fas fa-shopping-cart w-5 text-center"></i>
                    <span class="ml-3">Orders</span>
                </a>
                
                <a href="products.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'products.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fas fa-birthday-cake w-5 text-center"></i>
                    <span class="ml-3">Products</span>
                </a>
                
                <div class="text-xs uppercase tracking-wider text-pink-400 font-semibold mb-2 mt-6 px-3">Management</div>
                
                <a href="customers.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'customers.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fas fa-users w-5 text-center"></i>
                    <span class="ml-3">Customers</span>
                </a>
                
                <a href="transaction.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'transaction.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fa-solid fa-timeline w-5 text-center"></i>
                    <span class="ml-3">Transaction History</span>
                </a>
                
                <a href="reports.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'reports.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fas fa-chart-bar w-5 text-center"></i>
                    <span class="ml-3">Reports</span>
                </a>
                
                <div class="text-xs uppercase tracking-wider text-pink-400 font-semibold mb-2 mt-6 px-3">Settings</div>
                
                <a href="profile.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'profile.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fas fa-user-cog w-5 text-center"></i>
                    <span class="ml-3">Profile</span>
                </a>
                
                <a href="site_settings.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'site_settings.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fas fa-cog w-5 text-center"></i>
                    <span class="ml-3">Site Settings</span>
                </a>
            </nav>
            
            <!-- Logout button -->
            <div class="px-6 py-4 border-t border-pink-100">
                <a href="../logout.php" class="flex items-center text-red-500 hover:text-red-600">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="ml-3">Logout</span>
                </a>
            </div>
        </div>

        <!-- Main content area -->
        <div class="flex-1">
            <!-- Top navbar -->
            <div class="bg-white shadow-sm py-3 px-6 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-pink-600">Transaction #<?= $order_id ?> Details</h2>
                <a href="transaction.php" class="text-gray-600 hover:text-pink-500">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Transactions
                </a>
            </div>

            <!-- Page content -->
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Order Info -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-800">Order Information</h3>
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
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-500 mb-1">Order Date</h4>
                                        <p class="text-gray-800"><?= date('F j, Y \a\t g:i A') ?></p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-500 mb-1">Order Total</h4>
                                        <p class="text-gray-800 font-medium">₱<?= number_format($order['total_amount'], 2) ?></p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 mb-1">Payment Method</h4>
                                        <p class="text-gray-800"><?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></p>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-500 mb-1">Customer</h4>
                                        <p class="text-gray-800 font-medium"><?= htmlspecialchars($order['shipping_name']) ?></p>
                                        <p class="text-gray-600"><?= htmlspecialchars($order['email']) ?></p>
                                        <p class="text-gray-600"><?= htmlspecialchars($order['shipping_phone']) ?></p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 mb-1">Shipping Address</h4>
                                        <p class="text-gray-800"><?= htmlspecialchars($order['shipping_address']) ?></p>
                                        <p class="text-gray-800"><?= htmlspecialchars($order['shipping_city']) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($order['special_instructions'])): ?>
                                <div class="mt-4 border-t pt-4">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Special Instructions</h4>
                                    <p class="text-gray-800"><?= nl2br(htmlspecialchars($order['special_instructions'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Order Items -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Order Items</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                                            <tr>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center">
                                                        <?php if (!empty($item['image_url'])): ?>
                                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                                                 onerror="this.onerror=null; this.src='uploads/<?= htmlspecialchars($item['image_url']) ?>'; this.onerror=function(){this.src='images/no-image.png'; this.onerror=null;}"
                                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                                 class="h-40 w-40 object-cover rounded-md mr-4">
                                                        <?php else: ?>
                                                            <div class="h-40 w-40 bg-gray-100 rounded-md flex items-center justify-center mr-4">
                                                            <div class="h-20 w-20 bg-gray-100 rounded-md flex items-center justify-center mr-4">
                                                                <i class="fas fa-birthday-cake text-gray-300 text-xl"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($item['name']) ?></div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    ₱<?= number_format($item['price'], 2) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?= $item['quantity'] ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    ₱<?= number_format($item['item_total'], 2) ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-500">
                                                Order Total:
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-pink-600">
                                                ₱<?= number_format($order['total_amount'], 2) ?>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Actions</h3>
                            
                            <div class="space-y-2">
                                <a href="#" class="flex items-center text-gray-700 hover:text-pink-500">
                                    <i class="fas fa-print w-5 text-center"></i>
                                    <span class="ml-2">Print Invoice</span>
                                </a>
                                
                                <a href="mailto:<?= htmlspecialchars($order['email']) ?>" class="flex items-center text-gray-700 hover:text-pink-500">
                                    <i class="fas fa-envelope w-5 text-center"></i>
                                    <span class="ml-2">Email Customer</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 