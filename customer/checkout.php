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

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Process checkout
if (isset($_POST['place_order'])) {
    // Get form data
    $shipping_name = mysqli_real_escape_string($conn, $_POST['shipping_name']);
    $shipping_address = mysqli_real_escape_string($conn, $_POST['shipping_address']);
    $shipping_city = mysqli_real_escape_string($conn, $_POST['shipping_city']);
    $shipping_phone = mysqli_real_escape_string($conn, $_POST['shipping_phone']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $special_instructions = isset($_POST['special_instructions']) ? mysqli_real_escape_string($conn, $_POST['special_instructions']) : '';
    
    // Calculate total order amount
    $total_amount = 0;
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $query = "SELECT price FROM products WHERE id = $product_id";
        $result = mysqli_query($conn, $query);
        if ($product = mysqli_fetch_assoc($result)) {
            $item_total = $product['price'] * $quantity;
            $total_amount += $item_total;
        }
    }
    
    // Create order in database
    $order_date = date('Y-m-d H:i:s');
    $status = 'pending'; // Initial status
    
    $order_query = "INSERT INTO orders (user_id, total_amount, order_date, status, shipping_name, shipping_address, 
                   shipping_city, shipping_phone, payment_method, special_instructions) 
                   VALUES ($user_id, $total_amount, '$order_date', '$status', '$shipping_name', '$shipping_address', 
                   '$shipping_city', '$shipping_phone', '$payment_method', '$special_instructions')";
    
    if (mysqli_query($conn, $order_query)) {
        $order_id = mysqli_insert_id($conn);
        
        // Add order items
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $product_query = "SELECT price FROM products WHERE id = $product_id";
            $result = mysqli_query($conn, $product_query);
            if ($product = mysqli_fetch_assoc($result)) {
                $item_price = $product['price'];
                $item_total = $item_price * $quantity;
                
                $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price, item_total) 
                              VALUES ($order_id, $product_id, $quantity, $item_price, $item_total)";
                mysqli_query($conn, $item_query);
            }
        }
        
        // Clear the cart
        unset($_SESSION['cart']);
        
        // Redirect to order confirmation
        $_SESSION['order_success'] = true;
        $_SESSION['order_id'] = $order_id;
        header("Location: order_confirmation.php");
        exit();
    } else {
        $error = "Error processing your order: " . mysqli_error($conn);
    }
}

// Calculate cart total
$total = 0;
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $query = "SELECT price FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $query);
    if ($product = mysqli_fetch_assoc($result)) {
        $item_total = $product['price'] * $quantity;
        $total += $item_total;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Martina's Indulgence</title>
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
                <a href="cart.php" class="text-gray-700 hover:text-pink-500 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Cart
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Checkout</h1>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-8">
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-medium text-gray-800 mb-4">Shipping Information</h2>
                    
                    <form method="POST" action="">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="shipping_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="shipping_name" name="shipping_name" required 
                                       value="<?= htmlspecialchars($user['username'] ?? '') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500">
                            </div>
                            
                            <div>
                                <label for="shipping_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" id="shipping_phone" name="shipping_phone" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500">
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input type="text" id="shipping_address" name="shipping_address" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500">
                        </div>
                        
                        <div class="mb-6">
                            <label for="shipping_city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input type="text" id="shipping_city" name="shipping_city" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500">
                        </div>
                        
                        <div class="mb-6">
                            <label for="special_instructions" class="block text-sm font-medium text-gray-700 mb-1">Special Instructions (Optional)</label>
                            <textarea id="special_instructions" name="special_instructions" rows="3" 
                                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500"></textarea>
                        </div>
                        
                        <h2 class="text-xl font-medium text-gray-800 mb-4">Payment Method</h2>
                        
                        <div class="mb-6">
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="radio" id="cod" name="payment_method" value="cash_on_delivery" checked
                                           class="h-4 w-4 text-pink-600 focus:ring-pink-500">
                                    <label for="cod" class="ml-2 block text-sm text-gray-700">Cash on Delivery</label>
                                </div>
                                
                                <div class="flex items-center">
                                    <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer"
                                           class="h-4 w-4 text-pink-600 focus:ring-pink-500">
                                    <label for="bank_transfer" class="ml-2 block text-sm text-gray-700">Bank Transfer</label>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" name="place_order" class="w-full py-3 px-4 bg-pink-500 hover:bg-pink-600 text-white font-medium rounded-lg transition-colors">
                                Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-6">
                    <h2 class="text-xl font-medium text-gray-800 mb-4">Order Summary</h2>
                    
                    <div class="mb-4">
                        <?php
                        $item_count = 0;
                        foreach ($_SESSION['cart'] as $product_id => $quantity) {
                            $item_count += $quantity;
                            $query = "SELECT name, price FROM products WHERE id = $product_id";
                            $result = mysqli_query($conn, $query);
                            
                            if ($product = mysqli_fetch_assoc($result)):
                                $item_total = $product['price'] * $quantity;
                        ?>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <div>
                                <p class="text-gray-800"><?= htmlspecialchars($product['name']) ?> × <?= $quantity ?></p>
                            </div>
                            <p class="text-gray-600">$<?= number_format($item_total, 2) ?></p>
                        </div>
                        <?php 
                            endif; 
                        } 
                        ?>
                    </div>
                    
                    <div class="py-2 border-b border-gray-200">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Subtotal (<?= $item_count ?> items)</span>
                            <span class="text-gray-800 font-medium">$<?= number_format($total, 2) ?></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Shipping</span>
                            <span class="text-gray-800 font-medium">Free</span>
                        </div>
                    </div>
                    
                    <div class="flex justify-between py-3 font-bold">
                        <span class="text-lg text-gray-800">Total</span>
                        <span class="text-lg text-pink-600">$<?= number_format($total, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 