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

// Handle add to cart action
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    // Initialize cart array in session if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    // Add or update product in cart
    $_SESSION['cart'][$product_id] = $quantity;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Martina's Indulgence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Add animation library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Add Alpine.js for dropdown functionality -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-pink-50 to-white font-sans min-h-screen">
    <!-- Modern Navigation Bar -->
    <nav class="bg-white/80 backdrop-blur-md shadow-md fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 py-2">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <img src="../logo.png" alt="Martina's" class="h-20 w-auto transition-transform hover:scale-105 duration-300">
                    <div class="border-l-2 border-pink-300 pl-3">
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-pink-500 to-purple-500 bg-clip-text text-transparent">Martina's</h1>
                        <p class="text-xl text-pink-500 font-medium italic">Indulgence</p>
                    </div>
                </div>
                
                <!-- Main navigation links -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="customer_dashboard.php" class="text-gray-700 hover:text-pink-500 font-medium transition-colors">Home</a>
                    <a href="#" class="text-gray-700 hover:text-pink-500 font-medium transition-colors">Best Sellers</a>
                    <a href="#" class="text-gray-700 hover:text-pink-500 font-medium transition-colors">About Us</a>
                    <a href="contact.php" class="text-gray-700 hover:text-pink-500 font-medium transition-colors">Contact</a>
                </div>
                
                <div class="flex items-center space-x-6">
                    <!-- Integrated Search Bar -->
                    <div class="flex items-center">
                        <form action="customer_dashboard.php" method="GET" class="flex items-center">
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       placeholder="Search products..."
                                       class="w-48 pl-10 pr-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-pink-500"
                                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            </div>
                            
                            <select name="search_category" 
                                    class="ml-2 py-2 px-3 border border-gray-200 rounded-lg focus:outline-none focus:border-pink-500 text-sm">
                                <option value="">All</option>
                                <option value="Cake" <?= (isset($_GET['search_category']) && $_GET['search_category'] == 'Cake') ? 'selected' : '' ?>>Cakes</option>
                                <option value="Cupcake" <?= (isset($_GET['search_category']) && $_GET['search_category'] == 'Cupcake') ? 'selected' : '' ?>>Cupcakes</option>
                                <option value="Pastry" <?= (isset($_GET['search_category']) && $_GET['search_category'] == 'Pastry') ? 'selected' : '' ?>>Pastries</option>
                            </select>
                            
                            <button type="submit" 
                                    class="ml-2 bg-pink-500 text-white px-4 py-2 rounded-lg hover:bg-pink-600 transition-colors">
                                Search
                            </button>
                        </form>
                    </div>

                    <!-- Cart Icon -->
                    <a href="cart.php" class="relative group">
                        <i class="fas fa-shopping-cart text-gray-700 text-xl group-hover:text-pink-500 transition-colors"></i>
                        <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): 
                            $itemCount = 0;
                            foreach($_SESSION['cart'] as $qty) {
                                if ($qty > 0) $itemCount++;
                            }
                            if ($itemCount > 0):
                        ?>
                        <span class="absolute -top-2 -right-2 bg-pink-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center animate-pulse">
                            <?= $itemCount ?>
                        </span>
                        <?php 
                            endif;
                        endif; 
                        ?>
                    </a>
                    
                    <!-- User Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none p-1 rounded-full hover:bg-pink-50 transition-colors">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=f9a8d4&color=fff" 
                                 class="h-8 w-8 rounded-full border-2 border-pink-300">
                            <span class="text-gray-700 font-medium"><?= htmlspecialchars($_SESSION['username']) ?></span>
                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                        </button>
                        
                        <!-- Dropdown menu -->
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            
                            <a href="my_profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50">
                                <i class="fas fa-user mr-2 text-pink-500"></i> My Profile
                            </a>
                            <a href="my_orders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50">
                                <i class="fas fa-box mr-2 text-pink-500"></i> My Orders
                            </a>
                            <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50">
                                <i class="fas fa-cog mr-2 text-pink-500"></i> Settings
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Decorative bottom border with gradient -->
        <div class="h-1 w-full bg-gradient-to-r from-pink-300 via-purple-400 to-pink-300"></div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 pt-24 pb-12">
        <!-- Category Filter -->
        <?php
        // Get current category filter from URL parameter
        $current_category = isset($_GET['category']) ? $_GET['category'] : 'all';
        ?>
        <div class="mb-8 flex items-center space-x-4 overflow-x-auto py-4">
            <a href="customer_dashboard.php" 
               class="px-4 py-2 rounded-full <?= $current_category == 'all' ? 'bg-pink-500 text-white' : 'bg-white text-gray-700 hover:bg-pink-100' ?> transition-colors">
                All Products
            </a>
            <a href="customer_dashboard.php?category=Cake" 
               class="px-4 py-2 rounded-full <?= $current_category == 'Cake' ? 'bg-pink-500 text-white' : 'bg-white text-gray-700 hover:bg-pink-100' ?> transition-colors">
                Cakes
            </a>
            <a href="customer_dashboard.php?category=Cupcake" 
               class="px-4 py-2 rounded-full <?= $current_category == 'Cupcake' ? 'bg-pink-500 text-white' : 'bg-white text-gray-700 hover:bg-pink-100' ?> transition-colors">
                Cupcakes
            </a>
            <a href="customer_dashboard.php?category=Pastry" 
               class="px-4 py-2 rounded-full <?= $current_category == 'Pastry' ? 'bg-pink-500 text-white' : 'bg-white text-gray-700 hover:bg-pink-100' ?> transition-colors">
                Pastries
            </a>
        </div>
        
        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php
            // Modify query based on search and category filters
            $query = "SELECT * FROM products WHERE 1=1";

            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = mysqli_real_escape_string($conn, $_GET['search']);
                $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
            }

            if (isset($_GET['search_category']) && !empty($_GET['search_category'])) {
                $category = mysqli_real_escape_string($conn, $_GET['search_category']);
                $query .= " AND category = '$category'";
            } elseif (isset($_GET['category']) && $_GET['category'] != 'all') {
                $category = mysqli_real_escape_string($conn, $_GET['category']);
                $query .= " AND category = '$category'";
            }
            
            $result = mysqli_query($conn, $query);
            
            if (!$result) {
                die("Query failed: " . mysqli_error($conn));
            }

            if (mysqli_num_rows($result) > 0) {
                while ($product = mysqli_fetch_assoc($result)) {
            ?>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-300" 
                     data-aos="fade-up">
                    <?php if (!empty($product['image_url'])): ?>
                        <div class="relative group">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <a href="product_detail.php?id=<?= $product['id'] ?>" class="bg-white/90 px-4 py-2 rounded-full text-sm font-medium text-gray-800 hover:bg-white transition-colors">
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-birthday-cake text-gray-300 text-4xl"></i>
                        </div>
                    <?php endif; ?>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($product['name']) ?></h3>
                            <span class="inline-block px-2 py-1 text-xs rounded-full 
                                <?= $product['category'] == 'Cake' ? 'bg-blue-100 text-blue-800' : 
                                   ($product['category'] == 'Cupcake' ? 'bg-green-100 text-green-800' : 
                                   'bg-purple-100 text-purple-800') ?>">
                                <?= htmlspecialchars($product['category']) ?>
                            </span>
                        </div>
                        <p class="text-gray-500 text-sm mb-3 line-clamp-2"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="flex items-center justify-between">
                            <p class="text-pink-600 font-bold text-lg">₱<?= number_format($product['price'], 2) ?></p>
                            <div class="flex items-center space-x-2">
                                <a href="product_detail.php?id=<?= $product['id'] ?>" class="text-pink-500 hover:text-pink-600">
                                    <i class="fas fa-eye mr-1"></i> Details
                                </a>
                                <form method="POST" class="flex items-center">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" name="add_to_cart" 
                                            class="bg-pink-500 text-white px-4 py-2 rounded-lg hover:bg-pink-600 transition-colors">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo '<div class="col-span-full text-center py-12">
                        <div class="text-gray-400 text-lg">No products available in this category</div>
                        <a href="customer_dashboard.php" class="text-pink-500 hover:text-pink-600 mt-2 inline-block">
                            <i class="fas fa-arrow-left mr-1"></i> View all products
                        </a>
                      </div>';
            }   
            ?>
        </div>
    </div>

   <script src="assets/js/customer/customer_dashboard.js"></script>
</body>
</html>
