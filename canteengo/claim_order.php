<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'student') {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    
    // Verify order belongs to student and is claimable
    $stmt = $conn->prepare("UPDATE orders SET status = 'complete' 
                          WHERE id = ? 
                          AND student_username = ?
                          AND status = 'claimable'");
    $stmt->bind_param("is", $order_id, $_SESSION['username']);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "Order claimed successfully!";
        }
    }
    $stmt->close();
}

header("Location: student.php");
exit();