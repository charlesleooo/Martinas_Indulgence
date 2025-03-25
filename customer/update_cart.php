<?php
session_start();
require_once "../dbconn.php";

header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Please log in first"]);
    exit();
}

if (!isset($_POST['cart_id']) || !isset($_POST['quantity'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing required parameters"]);
    exit();
}

$cart_id = intval($_POST['cart_id']);
$quantity = intval($_POST['quantity']);
$user_id = $_SESSION["user_id"];

try {
    // Verify cart item belongs to user
    $stmt = $conn->prepare("SELECT c.*, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Cart item not found");
    }

    $cart_item = $result->fetch_assoc();

    // Update quantity
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
    
    if ($stmt->execute()) {
        // Calculate new subtotal and total
        $subtotal = number_format($quantity * $cart_item['price'], 2);
        
        // Get new cart total
        $stmt = $conn->prepare("SELECT SUM(c.quantity * p.price) as total FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $total_result = $stmt->get_result();
        $total = number_format($total_result->fetch_assoc()['total'], 2);

        echo json_encode([
            "success" => true,
            "subtotal" => $subtotal,
            "total" => $total
        ]);
    } else {
        throw new Exception("Failed to update cart");
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