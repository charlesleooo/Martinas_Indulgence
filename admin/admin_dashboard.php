<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../dbconn.php";

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Get current page for active link styling
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Martina's Indulgence</title>
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
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'admin/admin_dashboard.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fas fa-tachometer-alt w-5 text-center"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                
                <a href="orders.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'admin/orders.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fas fa-shopping-cart w-5 text-center"></i>
                    <span class="ml-3">Orders</span>
                </a>
                
                <a href="products.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'admin/products.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fas fa-birthday-cake w-5 text-center"></i>
                    <span class="ml-3">Products</span>
                </a>
                
                <div class="text-xs uppercase tracking-wider text-pink-400 font-semibold mb-2 mt-6 px-3">Management</div>
                
                <a href="customers.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'admin/customers.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fas fa-users w-5 text-center"></i>
                    <span class="ml-3">Customers</span>
                </a>
                
                <a href="transaction.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'admin/transaction.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fa-solid fa-timeline w-5 text-center"></i>
                    <span class="ml-3">Transaction History</span>
                </a>
                
                <a href="reports.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'admin/reports.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fas fa-chart-bar w-5 text-center"></i>
                    <span class="ml-3">Reports</span>
                </a>
                
                <div class="text-xs uppercase tracking-wider text-pink-400 font-semibold mb-2 mt-6 px-3">Settings</div>
                
                <a href="admin/profile.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'admin/profile.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
                    <i class="fas fa-user-cog w-5 text-center"></i>
                    <span class="ml-3">Profile</span>
                </a>
                
                <a href="admin/site_settings.php" 
                   class="flex items-center px-3 py-2 mb-1 rounded-md <?= $current_page == 'admin/site_settings.php' ? 'bg-pink-500 text-white' : 'text-gray-700 hover:bg-pink-100' ?>">
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
                <h2 class="text-xl font-semibold text-pink-600">Admin Dashboard</h2>
                
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
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <!-- Products Stats Box -->
                    <a href="products.php" class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-pink-100 text-pink-600">
                                <i class="fas fa-birthday-cake text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-2xl font-semibold text-gray-800">
                                    <?php
                                    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
                                    $data = mysqli_fetch_assoc($result);
                                    echo $data['total'];
                                    ?>
                                </h4>
                                <p class="text-gray-600">Total Products</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- Orders Stats Box -->
                    <a href="orders.php" class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-pink-100 text-pink-600">
                                <i class="fas fa-shopping-cart text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-2xl font-semibold text-gray-800">
                                    <?php
                                    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status != 'delivered'");
                                    $data = mysqli_fetch_assoc($result);
                                    echo $data['total'];
                                    ?>
                                </h4>
                                <p class="text-gray-600">Current Orders</p>
                            </div>
                        </div>
                    </a>

                    <!-- Transaction History Stats Box -->
                    <a href="transaction.php" class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-pink-100 text-pink-600">
                                <i class="fa-solid fa-timeline text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-2xl font-semibold text-gray-800">
                                    <?php
                                    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions");
                                    $data = mysqli_fetch_assoc($result);
                                    echo $data['total'];
                                    ?>
                                </h4>
                                <p class="text-gray-600">Transactions</p>
                            </div>
                        </div>
                    </a>

                    <!-- Reports Stats Box -->
                    <a href="reports.php" class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-pink-100 text-pink-600">
                                <i class="fas fa-chart-bar text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-2xl font-semibold text-gray-800">
                                    <?php
                                    // Temporarily set to 0 until reports table is created
                                    echo "0";
                                    // Remove or comment out the failing query
                                    // $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM reports");
                                    // $data = mysqli_fetch_assoc($result);
                                    // echo $data['total'];
                                    ?>
                                </h4>
                                <p class="text-gray-600">Reports</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/admin/admin_dashboard.js"></script>
</body>
</html>