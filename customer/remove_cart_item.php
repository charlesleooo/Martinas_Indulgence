<?php
session_start();
require_once "../dbconn.php";

header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Please log in first"]);
    exit();
}

if (!isset($_POST['cart_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Cart ID is required"]);
    exit();
}

$cart_id = intval($_POST['cart_id']);
$user_id = $_SESSION["user_id"];

try {
    // Delete cart item
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    
    if ($stmt->execute()) {
        // Get new cart total
        $stmt = $conn->prepare("SELECT SUM(c.quantity * p.price) as total FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $total_result = $stmt->get_result();
        $total_row = $total_result->fetch_assoc();
        $total = $total_row['total'] ? number_format($total_row['total'], 2) : '0.00';

        // Check if cart is empty
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $count_result = $stmt->get_result();
        $isEmpty = $count_result->fetch_assoc()['count'] === 0;

        echo json_encode([
            "success" => true,
            "total" => $total,
            "isEmpty" => $isEmpty
        ]);
    } else {
        throw new Exception("Failed to remove item from cart");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

$stmt->close();
$conn->close(); 