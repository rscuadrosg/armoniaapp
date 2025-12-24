<?php
require_once 'db_config.php';

if (isset($_GET['event_id']) && isset($_GET['member_id']) && isset($_GET['status'])) {
    $event_id = $_GET['event_id'];
    $member_id = $_GET['member_id'];
    $status = $_GET['status'];

    // Insertar o actualizar la confirmación
    $stmt = $pdo->prepare("INSERT INTO event_confirmations (event_id, member_id, status) 
                           VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE status = ?");
    $stmt->execute([$event_id, $member_id, $status, $status]);

    header("Location: dashboard.php");
    exit;
}