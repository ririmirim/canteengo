<?php
require 'config.php';

if (isset($_SESSION['user_id'])) {
    $session_id = session_id();
    $stmt = $conn->prepare("UPDATE active_sessions 
                          SET last_activity = NOW() 
                          WHERE session_id = ?");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $stmt->close();
}
exit();
?>