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

// Get search and filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status_filter']) ? mysqli_real_escape_string($conn, $_GET['status_filter']) : '';

// Modify the query to use transactions table and get only the latest transaction per order
$transactions_query = "SELECT t.*, o.shipping_name, o.shipping_address, o.shipping_city, 
                             o.shipping_phone, o.special_instructions, u.username, u.email 
                      FROM transactions t 
                      JOIN (
                          SELECT order_id, MAX(transaction_id) as latest_transaction
                          FROM transactions
                          GROUP BY order_id
                      ) latest ON t.order_id = latest.order_id 
                          AND t.transaction_id = latest.latest_transaction
                      JOIN orders o ON t.order_id = o.id 
                      JOIN users u ON t.user_id = u.id 
                      WHERE (o.shipping_name LIKE '%$search%' 
                            OR t.transaction_id LIKE '%$search%'
                            OR u.email LIKE '%$search%')";

if ($status_filter !== '') {
    $transactions_query .= " AND t.status = '$status_filter'";
}

$transactions_query .= " ORDER BY t.transaction_date DESC";
$transactions_result = mysqli_query($conn, $transactions_query);

// Get current page for active link styling
$current_page = 'transaction.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - Admin Dashboard - Martina's Indulgence</title>
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
                <h2 class="text-xl font-semibold text-pink-600">Transaction History</h2>
            </div>

            <!-- Page content -->
            <div class="p-6">
                <div class="mb-6">
                    <form method="GET">
                        <div class="flex gap-4 items-end max-w-4xl">
                            <!-- Search input -->
                            <div class="flex-1">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" 
                                       name="search" 
                                       id="search" 
                                       placeholder="Search by customer name, transaction ID, or email"
                                       value="<?= htmlspecialchars($search) ?>"
                                       class="w-full h-12 rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
                            </div>

                            <!-- Status filter -->
                            <div class="w-48">
                                <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status_filter" 
                                        id="status_filter" 
                                        class="w-full h-12 rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
                                    <option value="">All Statuses</option>
                                    <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="failed" <?= $status_filter === 'failed' ? 'selected' : '' ?>>Failed</option>
                                </select>
                            </div>

                            <!-- Search button -->
                            <button type="submit" 
                                    class="h-12 px-6 bg-pink-500 text-white rounded-md hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>

                            <!-- Reset button - only show if there are active filters -->
                            <?php if ($search || $status_filter): ?>
                                <a href="transaction.php" 
                                   class="h-12 px-6 bg-gray-500 text-white flex items-center rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    <i class="fas fa-times mr-2"></i>Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Search results count -->
                <?php if ($search || $status_filter): ?>
                    <div class="mt-4 text-sm text-gray-600">
                        <?php
                        $result_count = mysqli_num_rows($transactions_result);
                        echo "Showing $result_count transaction" . ($result_count != 1 ? 's' : '');
                        if ($search) echo " for \"" . htmlspecialchars($search) . "\"";
                        if ($status_filter) echo " with status \"" . ucfirst($status_filter) . "\"";
                        ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($transaction = mysqli_fetch_assoc($transactions_result)): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            #<?= $transaction['transaction_id'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            #<?= $transaction['order_id'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?= htmlspecialchars($transaction['shipping_name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($transaction['email']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('M j, Y g:i A', strtotime($transaction['transaction_date'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ₱<?= number_format($transaction['amount'], 2) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($transaction['payment_method']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <?= ucfirst($transaction['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="view_transaction.php?id=<?= $transaction['order_id'] ?>" class="text-pink-600 hover:text-pink-900">View Details</a>
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
</body>
</html>
