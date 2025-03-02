<?php
require 'config.php';

$result = $conn->query("SELECT COUNT(DISTINCT user_id) AS count 
                       FROM active_sessions 
                       WHERE last_activity > NOW() - INTERVAL 5 MINUTE");
$data = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode(['count' => $data['count']]);
exit();
?>