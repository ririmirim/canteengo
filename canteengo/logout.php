<?php
require 'config.php';

if (isset($_SESSION['user_id'])) {
    $session_id = session_id();
    
    // Delete from active sessions
    $stmt = $conn->prepare("DELETE FROM active_sessions WHERE session_id = ?");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $stmt->close();
    
    // Destroy session
    session_unset();
    session_destroy();
}

header("Location: index.php");
exit();
?>