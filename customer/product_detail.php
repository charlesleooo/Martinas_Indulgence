<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../dbconn.php";

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: customer_dashboard.php");
    exit();
}

$product_id = $_GET['id'];

// Fetch product details
$query = "SELECT * FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    // Product not found
    header("Location: customer_dashboard.php");
    exit();
}

$product = mysqli_fetch_assoc($result);

// Handle add to cart action
if (isset($_POST['add_to_cart'])) {
    $quantity = $_POST['quantity'];
    
    // Initialize cart array in session if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    // Add or update product in cart
    $_SESSION['cart'][$product_id] = $quantity;
    
    // Redirect to same page to prevent form resubmission
    header("Location: product_detail.php?id=$product_id&added=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Martina's Indulgence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-pink-50 to-white font-sans min-h-screen">
    <!-- Navigation Bar (same as customer_dashboard.php) -->
    <nav class="bg-white/80 backdrop-blur-md shadow-sm fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <img src="logo.png" alt="Martina's" class="h-20 w-auto">
                    <div>
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-pink-500 to-purple-500 bg-clip-text text-transparent">Martina's</h1>
                        <p class="text-xl text-pink-500 text-font-bold">Indulgence</p>
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="cart.php" class="relative group">
                        <i class="fas fa-shopping-cart text-gray-700 text-xl group-hover:text-pink-500 transition-colors"></i>
                        <?php 
                        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): 
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
                    
                    <!-- User dropdown menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=f9a8d4&color=fff" 
                                 class="h-8 w-8 rounded-full">
                            <span class="text-gray-700"><?= htmlspecialchars($_SESSION['username']) ?></span>
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
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 pt-32 pb-12">
        <!-- Success Message for Cart Addition -->
        <?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded mb-6 flex items-center justify-between" id="successAlert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>Product added to your cart successfully!</span>
            </div>
            <button onclick="document.getElementById('successAlert').style.display='none'" class="text-green-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php endif; ?>

        <!-- Breadcrumb Navigation -->
        <div class="mb-6 flex items-center text-sm text-gray-500">
            <a href="customer_dashboard.php" class="hover:text-pink-500">Home</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <a href="customer_dashboard.php" class="hover:text-pink-500">Products</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <span class="text-gray-700"><?= htmlspecialchars($product['name']) ?></span>
        </div>

        <!-- Product Detail Section -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Product Image -->
                <div class="p-6">
                    <?php if (!empty($product['image_url'])): ?>
                        <div class="rounded-lg overflow-hidden">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 class="w-full h-auto object-cover hover:scale-105 transition-transform duration-300">
                        </div>
                    <?php else: ?>
                        <div class="w-full h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-birthday-cake text-gray-300 text-4xl"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Product Details -->
                <div class="p-6">
                    <div class="mb-6">
                        <span class="inline-block px-3 py-1 text-sm rounded-full 
                            <?= htmlspecialchars($product['category']) == 'Cake' ? 'bg-blue-100 text-blue-800' : 
                               (htmlspecialchars($product['category']) == 'Cupcake' ? 'bg-green-100 text-green-800' : 
                               'bg-purple-100 text-purple-800') ?>">
                            <?= htmlspecialchars($product['category']) ?>
                        </span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($product['name']) ?></h1>
                    <p class="text-2xl text-pink-600 font-bold mb-4">₱<?= number_format($product['price'], 2) ?></p>
                    
                    <div class="my-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-2">Description</h2>
                        <p class="text-gray-600 leading-relaxed">
                            <?= htmlspecialchars($product['description']) ?>
                        </p>
                    </div>
                    
                    <!-- Add to Cart Form -->
                    <form method="POST" class="mt-8">
                        <div class="flex items-center mb-4">
                            <label for="quantity" class="mr-4 text-gray-700">Quantity:</label>
                            <div class="custom-number-input h-10 w-32">
                                <div class="flex flex-row h-10 w-full rounded-lg relative bg-transparent mt-1">
                                    <button type="button" onclick="decrementQuantity()" class="bg-gray-100 text-gray-600 hover:text-gray-700 hover:bg-gray-200 h-full w-10 rounded-l-lg cursor-pointer outline-none">
                                        <span class="m-auto text-xl font-thin">−</span>
                                    </button>
                                    <input type="number" id="quantity" name="quantity" min="1" value="1" 
                                           class="outline-none focus:outline-none text-center w-full bg-gray-50 font-semibold text-md hover:text-black focus:text-black md:text-base cursor-default flex items-center text-gray-700">
                                    <button type="button" onclick="incrementQuantity()" class="bg-gray-100 text-gray-600 hover:text-gray-700 hover:bg-gray-200 h-full w-10 rounded-r-lg cursor-pointer">
                                        <span class="m-auto text-xl font-thin">+</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="add_to_cart" 
                                class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Add to Cart
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function incrementQuantity() {
            const input = document.getElementById('quantity');
            input.value = parseInt(input.value) + 1;
        }
        
        function decrementQuantity() {
            const input = document.getElementById('quantity');
            const value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
            }
        }
    </script>
</body>
</html> 