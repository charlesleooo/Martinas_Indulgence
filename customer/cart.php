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

// Handle cart updates
if (isset($_POST['update_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    if ($quantity > 0) {
        $_SESSION['cart'][$product_id] = $quantity;
    } else {
        // Remove item if quantity is 0
        unset($_SESSION['cart'][$product_id]);
    }
}

// Handle removing item from cart
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
}

// Handle clearing the entire cart
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
}

// Get current page for active link styling
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Martina's Indulgence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-pink-50 to-white font-sans min-h-screen">
    <!-- Navigation Bar - Simplified version -->
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
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Shopping Cart</h1>
        
        <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-left">
                            <tr>
                                <th class="px-6 py-3 text-gray-500 text-xs font-medium uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-gray-500 text-xs font-medium uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-gray-500 text-xs font-medium uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-gray-500 text-xs font-medium uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-gray-500 text-xs font-medium uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            $total = 0;
                            foreach ($_SESSION['cart'] as $product_id => $quantity):
                                // Get product details from database
                                $query = "SELECT * FROM products WHERE id = $product_id";
                                $result = mysqli_query($conn, $query);
                                
                                if ($result && $product = mysqli_fetch_assoc($result)):
                                    $item_total = $product['price'] * $quantity;
                                    $total += $item_total;
                            ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <?php if (!empty($product['image_url'])): ?>
                                            <!-- Try multiple possible image paths -->
                                            <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                                 onerror="this.onerror=null; this.src='uploads/<?= htmlspecialchars($product['image_url']) ?>'; this.onerror=function(){this.src='images/no-image.png'; this.onerror=null;}"
                                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                                 class="h-16 w-16 object-cover rounded-md mr-4">
                                        <?php else: ?>
                                            <div class="h-16 w-16 bg-gray-100 rounded-md flex items-center justify-center mr-4">
                                                <i class="fas fa-birthday-cake text-gray-300 text-xl"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h3 class="text-gray-800 font-medium"><?= htmlspecialchars($product['name']) ?></h3>
                                            <?php 
                                            // Remove all debug info about the image
                                            // Uncomment this block for debugging if needed
                                            /*
                                            if(!empty($product['image_url'])) {
                                                echo '<p class="text-xs text-gray-500">Image: ' . htmlspecialchars($product['image_url']) . '</p>';
                                                
                                                // Check directory existence
                                                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/test/uploads';
                                                if(!is_dir($upload_dir)) {
                                                    echo '<p class="text-xs text-red-500">Uploads directory not found!</p>';
                                                }
                                            } else {
                                                echo '<p class="text-xs text-gray-500">No image</p>';
                                            }
                                            */
                                            ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    $<?= number_format($product['price'], 2) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" class="flex items-center">
                                        <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                        <input type="number" name="quantity" value="<?= $quantity ?>" min="1" 
                                               class="w-16 border border-gray-200 rounded-lg px-2 py-1 text-center">
                                        <button type="submit" name="update_cart" class="ml-2 text-sm text-pink-500 hover:text-pink-700">
                                            Update
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 font-medium text-pink-600">
                                    $<?= number_format($item_total, 2) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="?remove=<?= $product_id ?>" class="text-gray-400 hover:text-red-500">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endif; 
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Cart Summary -->
                <div class="border-t border-gray-200 p-6 bg-gray-50">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="text-lg font-medium text-gray-800">$<?= number_format($total, 2) ?></span>
                    </div>
                    <div class="flex justify-between space-x-4">
                        <a href="?clear=1" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                            Clear Cart
                        </a>
                        <div class="flex-1"></div>
                        <a href="checkout.php" class="px-6 py-3 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-16 bg-white rounded-xl shadow-sm">
                <div class="mb-4 text-pink-400">
                    <i class="fas fa-shopping-cart text-6xl"></i>
                </div>
                <h2 class="text-xl font-medium text-gray-800 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-6">Looks like you haven't added any products to your cart yet</p>
                <a href="customer_dashboard.php" class="px-6 py-3 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors inline-block">
                    Continue Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 