<?php
require 'config.php';

header('Content-Type: application/json');

try {
    // Get all orders with proper ordering
    $result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $orders = [];
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    echo json_encode($orders);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>