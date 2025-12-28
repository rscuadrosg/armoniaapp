<?php
require_once 'db_config.php';

if (isset($_POST['upload']) && isset($_FILES['photo'])) {
    $member_id = $_POST['member_id'];
    $file = $_FILES['photo'];
    
    // Validar extensi贸n
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png'];
    
    if (in_array(strtolower($ext), $allowed)) {
        $new_name = "profile_" . $member_id . "_" . time() . "." . $ext;
        $path = "uploads/profile_pics/" . $new_name;
        
        if (move_uploaded_file($file['tmp_name'], $path)) {
            // Guardar en DB
            $stmt = $pdo->prepare("UPDATE members SET profile_photo = ? WHERE id = ?");
            $stmt->execute([$new_name, $member_id]);
        }
    }
    header("Location: dashboard.php");
    exit;
}