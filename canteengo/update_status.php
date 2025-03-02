<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'staff') {
    try {
        // Validate inputs
        $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        
        // Add 'claimable' to the allowed statuses
        if(!$order_id || !in_array($status, ['pending', 'processing', 'claimable', 'complete'])) {
            throw new Exception('Invalid input');
        }

        // Update database
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        
        if(!$stmt->execute()) {
            throw new Exception('Database update failed');
        }

        header("Location: staff.php");
        exit();
        
    } catch(Exception $e) {
        error_log("Status update error: " . $e->getMessage());
        header("Location: staff.php");
        exit();
    }
}
?>