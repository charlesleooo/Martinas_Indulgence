<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Martina's Indulgence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add modern font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('https://images.unsplash.com/photo-1486427944299-d1955d23e34d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .input-effect:focus {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="flex justify-center items-center min-h-screen">
    <div class="glass-effect p-8 rounded-3xl shadow-2xl w-96 mx-4 border border-white/20">
        <form id="loginForm" action="login.php" method="POST" class="space-y-6">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Welcome Back</h2>
                <p class="text-sm text-gray-500">Please sign in to continue</p>
            </div>
            
            <div class="space-y-5">
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" placeholder="Enter your username" required 
                        class="input-effect w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-pink-400 focus:ring-4 focus:ring-pink-100 transition-all duration-300 bg-white/50">
                </div>
                
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required 
                        class="input-effect w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-pink-400 focus:ring-4 focus:ring-pink-100 transition-all duration-300 bg-white/50">
                </div>
            </div>

            <div class="flex items-center justify-between text-sm mt-8">
                <a href="forgot_password.php" class="text-pink-600 hover:text-pink-500 font-medium transition-all duration-300 hover:scale-105">
                    Forgot Password?
                </a>
                <a href="register.php" class="text-pink-600 hover:text-pink-500 font-medium transition-all duration-300 hover:scale-105">
                    Create Account
                </a>
            </div>

            <button type="submit" 
                class="w-full bg-gradient-to-r from-pink-500 to-pink-600 text-white py-3.5 rounded-xl font-medium hover:opacity-90 focus:outline-none focus:ring-4 focus:ring-pink-300 transform transition-all duration-300 hover:scale-[1.02] hover:shadow-lg mt-6">
                Sign In
            </button>
        </form>
    </div>
    <script src="main.js"></script>
</body>
</html>

<?php
session_start();
require 'dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;

            // Redirect based on role
            if ($role == 'admin') {
                header("Location: admin/admin_dashboard.php");
            } elseif ($role == 'customer') {
                header("Location: customer/customer_dashboard.php");
            } else {
                echo "<script>alert('Invalid role assigned. Contact support.'); window.location.href='login.php';</script>";
            }
            exit();
        } else {
            echo "<script>alert('Invalid password'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('User not found'); window.location.href='login.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>