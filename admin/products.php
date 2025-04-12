<?php
session_start();
require_once "../dbconn.php";

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Create product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create"])) {
    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $description = mysqli_real_escape_string($conn, $_POST["description"]);
    $price = mysqli_real_escape_string($conn, $_POST["price"]);
    $category = mysqli_real_escape_string($conn, $_POST["category"]);
    
    // Handle file upload
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $target_file = $target_dir . uniqid() . '.' . $imageFileType;
    
    // Check if image file is actual image
    if(isset($_FILES["image"])) {
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // File uploaded successfully, now save to database
                $sql = "INSERT INTO products (name, description, price, category, image_url) VALUES (?, ?, ?, ?, ?)";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ssdss", $name, $description, $price, $category, $target_file);
                    mysqli_stmt_execute($stmt);
                    header("location: products.php");
                    exit();
                }
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "File is not an image.";
        }
    }
}

// Update product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $id = mysqli_real_escape_string($conn, $_POST["id"]);
    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $description = mysqli_real_escape_string($conn, $_POST["description"]);
    $price = mysqli_real_escape_string($conn, $_POST["price"]);
    $category = mysqli_real_escape_string($conn, $_POST["category"]);
    
    // Handle new image upload if provided
    if (isset($_FILES["new_image"]) && $_FILES["new_image"]["size"] > 0) {
        $target_dir = "uploads/";
        $imageFileType = strtolower(pathinfo($_FILES["new_image"]["name"], PATHINFO_EXTENSION));
        $target_file = $target_dir . uniqid() . '.' . $imageFileType;
        
        if (move_uploaded_file($_FILES["new_image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        } else {
            echo "Sorry, there was an error uploading your file.";
            exit();
        }
    } else {
        $image = $_POST["current_image"];
    }
    
    $sql = "UPDATE products SET name=?, description=?, price=?, category=?, image=? WHERE id=?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssdssi", $name, $description, $price, $category, $image, $id);
        mysqli_stmt_execute($stmt);
        header("location: products.php");
        exit();
    }
}

// Delete product
if (isset($_GET["delete"]) && !empty($_GET["delete"])) {
    // First check if product is referenced in order_items
    $check_sql = "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?";
    if ($check_stmt = mysqli_prepare($conn, $check_sql)) {
        mysqli_stmt_bind_param($check_stmt, "i", $_GET["delete"]);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $row = mysqli_fetch_assoc($check_result);
        
        if ($row['count'] > 0) {
            // Product is referenced in orders, cannot delete
            echo "<script>alert('Cannot delete this product as it is associated with existing orders.'); window.location.href='products.php';</script>";
            exit();
        } else {
            // Safe to delete the product
            $sql = "DELETE FROM products WHERE id = ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $_GET["delete"]);
                mysqli_stmt_execute($stmt);
                header("location: products.php");
                exit();
            }
        }
    }
}

// Get current page for active link styling
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Martina's Indulgence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-pink-50">
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
                <h2 class="text-xl font-semibold text-pink-600">Products Management</h2>
                
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

            <div class="p-10">
                <div class="bg-white rounded-lg shadow-sm p-6 w-full">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-lg font-semibold text-gray-800">Products Management</h3>
                        <button onclick="document.getElementById('addProductModal').classList.remove('hidden')" 
                                class="bg-pink-500 text-white px-4 py-2 rounded-md hover:bg-pink-600">
                            Add New Product
                        </button>
                    </div>

                    <!-- Products Table -->
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM products";
                            $result = mysqli_query($conn, $sql);
                            
                            while($row = mysqli_fetch_assoc($result)) {
                                echo '<tr>';
                                echo '<td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><img src="' . htmlspecialchars($row["image_url"]) . '" class="w-16 h-16 object-cover rounded-md"></td>';
                                echo '<td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">' . htmlspecialchars($row["name"]) . '</td>';
                                echo '<td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><span class="px-2 py-1 text-xs rounded-full ' . 
                                    (htmlspecialchars($row["category"]) == 'Cake' ? 'bg-blue-100 text-blue-800' : 
                                    (htmlspecialchars($row["category"]) == 'Cupcake' ? 'bg-green-100 text-green-800' : 
                                    'bg-purple-100 text-purple-800')) . '">' . 
                                    htmlspecialchars($row["category"]) . '</span></td>';
                                echo '<td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">' . htmlspecialchars($row["description"]) . '</td>';
                                echo '<td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">₱' . htmlspecialchars($row["price"]) . '</td>';
                                echo '<td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">';
                                echo '<button onclick="editProduct(' . $row["id"] . ')" class="text-blue-500 hover:text-blue-700 mr-3"><i class="fas fa-edit"></i></button>';
                                echo '<button onclick="deleteProduct(' . $row["id"] . ')" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-[800px] shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Add New Product</h3>
                <form method="POST" enctype="multipart/form-data" class="mt-4">
                    <div class="flex gap-6">
                        <!-- Left Column -->
                        <div class="w-1/2">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                                <input type="text" name="name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Price</label>
                                <input type="number" step="0.01" name="price" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                                <select name="category" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <option value="">Select a category</option>
                                    <option value="Cake">Cake</option>
                                    <option value="Cupcake">Cupcake</option>
                                    <option value="Pastry">Pastry</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="w-1/2">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                                <textarea name="description" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="4"></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="imageInput">Upload Image</label>
                                <input type="file" id="imageInput" name="image" accept="image/*" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-4 pt-4 border-t border-gray-200">
                        <button type="button" onclick="document.getElementById('addProductModal').classList.add('hidden')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 mr-2">
                            Cancel
                        </button>
                        <button type="submit" name="create" class="bg-pink-500 text-white px-4 py-2 rounded-md hover:bg-pink-600">
                            Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-[800px] shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Edit Product</h3>
                <form method="POST" enctype="multipart/form-data" class="mt-4">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="current_image" id="edit_current_image">
                    
                    <div class="flex gap-6">
                        <div class="w-1/3">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Current Image</label>
                                <div class="border rounded-md p-2 bg-gray-50">
                                    <img id="edit_image_preview" class="w-full aspect-square object-contain rounded-md mb-2">
                                </div>
                                <input type="file" name="new_image" id="new_image" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 mt-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" onchange="previewNewImage(this)">
                            </div>
                        </div>

                        <div class="w-2/3">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                                <input type="text" name="name" id="edit_name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                                <textarea name="description" id="edit_description" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="3"></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                                <select name="category" id="edit_category" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <option value="">Select a category</option>
                                    <option value="Cake">Cake</option>
                                    <option value="Cupcake">Cupcake</option>
                                    <option value="Pastry">Pastry</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Price</label>
                                <input type="number" step="0.01" name="price" id="edit_price" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-4 border-t pt-4">
                        <button type="button" onclick="document.getElementById('editProductModal').classList.add('hidden')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 mr-2">Cancel</button>
                        <button type="submit" name="update" class="bg-pink-500 text-white px-4 py-2 rounded-md hover:bg-pink-600">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/admin/products.js"></script>
</body>
</html> 