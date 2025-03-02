<?php
require 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

$stmt = $conn->prepare("SELECT id, status, order_details, created_at FROM orders 
                       WHERE student_username = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = [
        'id' => $row['id'],
        'status' => $row['status'],
        'order_details' => $row['order_details'],
        'created_at' => $row['created_at']
    ];
}

header('Content-Type: application/json');
echo json_encode($orders);
exit();
?>