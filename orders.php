<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle GET requests (get all orders or by user ID)
if ($method === 'GET') {
    $userId = isset($_GET['userId']) ? $_GET['userId'] : null;
    
    if ($userId && $userId !== 'guest') {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE userId = ? ORDER BY createdAt DESC");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query("SELECT * FROM orders ORDER BY createdAt DESC");
    }
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $row['items'] = json_decode($row['items'], true);
        $orders[] = $row;
    }
    
    echo json_encode([
        "success" => true,
        "data" => $orders,
        "count" => count($orders)
    ]);
}

// Handle POST requests (create new order)
else if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data || !isset($data['orderId']) || !isset($data['customerName']) || !isset($data['phone']) || !isset($data['items']) || empty($data['items'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Missing required fields"]);
        exit();
    }
    
    $itemsJson = json_encode($data['items']);
    
    $stmt = $conn->prepare("INSERT INTO orders (orderId, userId, customerName, email, phone, address, city, state, pinCode, items, subtotal, shipping, total, paymentMethod, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed')");
    
    $userId = isset($data['userId']) ? $data['userId'] : 'guest';
    $email = isset($data['email']) ? $data['email'] : 'not-provided@email.com';
    $address = isset($data['address']) ? $data['address'] : 'Not provided';
    $city = isset($data['city']) ? $data['city'] : 'N/A';
    $state = isset($data['state']) ? $data['state'] : 'N/A';
    $pinCode = isset($data['pinCode']) ? $data['pinCode'] : 'N/A';
    $subtotal = isset($data['subtotal']) ? $data['subtotal'] : 0;
    $shipping = isset($data['shipping']) ? $data['shipping'] : 0;
    $total = isset($data['total']) ? $data['total'] : ($subtotal + $shipping);
    $paymentMethod = isset($data['paymentMethod']) ? $data['paymentMethod'] : 'Cash on Delivery';
    
    $stmt->bind_param("ssssssssssdddss", 
        $data['orderId'], 
        $userId, 
        $data['customerName'], 
        $email, 
        $data['phone'], 
        $address, 
        $city, 
        $state, 
        $pinCode, 
        $itemsJson, 
        $subtotal, 
        $shipping, 
        $total, 
        $paymentMethod
    );
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "Order created successfully",
            "data" => array_merge($data, ["id" => $conn->insert_id, "status" => "Confirmed"])
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
}
?>
