<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Definimos variables globales para usar en el resto de la app
$currentUserId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userRole = $_SESSION['user_role'];
$isAdmin = ($userRole === 'admin');
$isLeader = ($userRole === 'lider');
?>