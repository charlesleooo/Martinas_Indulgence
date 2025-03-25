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

// Handle order status updates
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // If the new status is 'delivered', move it to transactions first
    if ($new_status === 'delivered') {
        // Insert into transactions table with correct field mapping
        $insert_transaction = "INSERT INTO transactions (
            order_id, 
            user_id, 
            amount,
            transaction_date
        ) 
        SELECT 
            id, 
            user_id, 
            total_amount, 
            order_date
        FROM orders 
        WHERE id = $order_id";
            
        if (mysqli_query($conn, $insert_transaction)) {
            // Update the order status
            $update_query = "UPDATE orders SET status = '$new_status', archived = 1 WHERE id = $order_id";
            if (mysqli_query($conn, $update_query)) {
                $success_message = "Order marked as delivered and moved to transaction history.";
            } else {
                $error_message = "Error updating order status: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Error moving order to transactions: " . mysqli_error($conn);
        }
    } else {
        // Regular status update for non-delivered status
        $update_query = "UPDATE orders SET status = '$new_status' WHERE id = $order_id";
        if (mysqli_query($conn, $update_query)) {
            $success_message = "Order status updated successfully.";
        } else {
            $error_message = "Error updating order status: " . mysqli_error($conn);
        }
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status_filter']) ? mysqli_real_escape_string($conn, $_GET['status_filter']) : '';

// Modify orders query to include search and filter
$orders_query = "SELECT o.*, u.username, u.email FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE (o.shipping_name LIKE '%$search%' 
                      OR o.id LIKE '%$search%'
                      OR u.email LIKE '%$search%')
                AND o.status != 'delivered'";  // Explicitly exclude delivered orders

if ($status_filter !== '') {
    // Only add status filter if it's not 'delivered'
    if ($status_filter !== 'delivered') {
        $orders_query .= " AND o.status = '$status_filter'";
    }
}

$orders_query .= " ORDER BY o.order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);

// Get current page for active link styling
$current_page = 'orders.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin Dashboard - Martina's Indulgence</title>
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
                <!-- Page title -->
                <h2 class="text-xl font-semibold text-pink-600">Order Management</h2>
                
                <!-- Right side elements -->
                <div class="flex items-center">
                    <button class="text-gray-500 hover:text-pink-500 mr-4">
                        <i class="fas fa-bell"></i>
                    </button>
                    <div class="relative">
                        <button class="flex items-center text-gray-700 focus:outline-none">
                            <span class="mr-2"><?= htmlspecialchars($_SESSION["username"]) ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Page content -->
            <div class="p-6">
                <?php if (isset($success_message)): ?>
                    <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <div class="mb-6">
                    <form method="GET">
                        <div class="flex gap-4 items-end max-w-4xl">
                            <!-- Search input -->
                            <div class="flex-1">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" 
                                       name="search" 
                                       id="search" 
                                       placeholder="Search by customer name, order ID, or email"
                                       value="<?= htmlspecialchars($search) ?>"
                                       class="w-full h-12 rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
                            </div>

                            <!-- Status filter -->
                            <div class="w-48">
                                <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status_filter" 
                                        id="status_filter" 
                                        class="w-full h-12 rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
                                    <option value="">All Active Orders</option>
                                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>

                            <!-- Search button -->
                            <button type="submit" 
                                    class="h-12 px-6 bg-pink-500 text-white rounded-md hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>

                            <!-- Reset button - only show if there are active filters -->
                            <?php if ($search || $status_filter): ?>
                                <a href="orders.php" 
                                   class="h-12 px-6 bg-gray-500 text-white flex items-center rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    <i class="fas fa-times mr-2"></i>Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- Search results count -->
                    <?php if ($search || $status_filter): ?>
                        <div class="mt-4 text-sm text-gray-600">
                            <?php
                            $result_count = mysqli_num_rows($orders_result);
                            echo "Showing $result_count order" . ($result_count != 1 ? 's' : '');
                            if ($search) echo " for \"" . htmlspecialchars($search) . "\"";
                            if ($status_filter) echo " with status \"" . ucfirst($status_filter) . "\"";
                            ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            #<?= $order['id'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?= htmlspecialchars($order['shipping_name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($order['email']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('M j, Y g:i A', strtotime($order['order_date'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ₱<?= number_format($order['total_amount'], 2) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form method="POST" action="" class="flex items-center space-x-2">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <span class="<?php
                                                    switch($order['status']) {
                                                        case 'pending':
                                                            echo 'bg-yellow-100 text-yellow-800';
                                                            break;
                                                        case 'delivered':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'processing':
                                                            echo 'bg-blue-100 text-blue-800';
                                                            break;
                                                        case 'cancelled':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                    }
                                                ?> px-2 py-1 rounded-full text-xs font-medium inline-block mb-2">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                                <select name="status" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500" <?= $order['status'] === 'delivered' ? 'disabled' : '' ?>>
                                                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                                    <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                    <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                </select>
                                                <?php if ($order['status'] !== 'delivered'): ?>
                                                    <button type="submit" name="update_status" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                                                        Update
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Confirm before marking as delivered
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const status = this.querySelector('select[name="status"]').value;
                if (status === 'delivered') {
                    if (!confirm('Are you sure you want to mark this order as delivered? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
</body>
</html>