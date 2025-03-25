<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Martina's Indulgence</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        <form action="register.php" method="POST" class="space-y-6">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Create Account</h2>
                <p class="text-sm text-gray-500">Join Martina's Indulgence today</p>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
                    <input type="email" name="email" placeholder="Enter your email" required 
                        class="input-effect w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-pink-400 focus:ring-4 focus:ring-pink-100 transition-all duration-300 bg-white/50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" placeholder="Choose a username" required 
                        class="input-effect w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-pink-400 focus:ring-4 focus:ring-pink-100 transition-all duration-300 bg-white/50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" placeholder="Create a password" required 
                        class="input-effect w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:outline-none focus:border-pink-400 focus:ring-4 focus:ring-pink-100 transition-all duration-300 bg-white/50">
                </div>
            </div>

            <button type="submit" 
                class="w-full bg-gradient-to-r from-pink-500 to-pink-600 text-white py-3.5 rounded-xl font-medium hover:opacity-90 focus:outline-none focus:ring-4 focus:ring-pink-300 transform transition-all duration-300 hover:scale-[1.02] hover:shadow-lg mt-6">
                Create Account
            </button>

            <p class="text-center text-sm text-gray-600 mt-6">
                Already have an account? 
                <a href="login.php" class="text-pink-600 hover:text-pink-500 font-medium transition-all duration-300 hover:scale-105 inline-block">
                    Sign in here
                </a>
            </p>
        </form>
    </div>
</body>
</html>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'dbconn.php';
require 'vendor/autoload.php'; // If using Composer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $role = "customer"; // Default role for new users

    // 🔍 CHECK IF EMAIL OR USERNAME ALREADY EXISTS
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check_stmt->bind_param("ss", $email, $username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<script>alert('Error: Username or email already exists!'); window.location.href='register.html';</script>";
        exit();
    }
    $check_stmt->close();

    // ✅ INSERT USER INTO DATABASE
    $stmt = $conn->prepare("INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $username, $hashed_password, $role);

    if ($stmt->execute()) {
        // 📧 SEND CONFIRMATION EMAIL
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@gmail.com'; // Your email
            $mail->Password = 'your-app-password'; // Use App Password for Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('your-email@gmail.com', 'Martina\'s Indulgence');
            $mail->addAddress($email, $username);
            $mail->Subject = 'Welcome to Martina\'s Indulgence!';
            $mail->isHTML(true);
            $mail->Body = "
                <h2>Welcome to Martina's Indulgence!</h2>
                <p>Dear $username,</p>
                <p>Your account has been successfully created.</p>
                <p>Happy shopping!</p>
                <p>Best Regards,<br>Martina's Indulgence Team</p>
            ";

            $mail->send();
            echo "<script>alert('Registration successful! Check your email for confirmation.'); window.location.href='login.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Registration successful, but email sending failed: {$mail->ErrorInfo}'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('Registration failed. Please try again.'); window.location.href='register.html';</script>";
    }

    $stmt->close();
}

$conn->close();
?>
