<?php
require_once 'db_config.php';
require_once 'auth.php';

if (!$isAdmin) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Se asume que la columna en DB es 'role' y no 'member_type' basado en login.php
    $stmt = $pdo->prepare("INSERT INTO members (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$_POST['full_name'], $_POST['email'], $password, $_POST['role']])) {
        header("Location: members.php");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Añadir Integrante</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <form method="POST" class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-6">Nuevo Integrante</h2>
        <input type="text" name="full_name" placeholder="Nombre completo" required class="w-full p-3 border rounded-xl mb-4">
        <input type="email" name="email" placeholder="Correo Electrónico" required class="w-full p-3 border rounded-xl mb-4">
        <input type="password" name="password" placeholder="Contraseña Provisional" required class="w-full p-3 border rounded-xl mb-4">
        <select name="role" class="w-full p-3 border rounded-xl mb-6 bg-white">
            <option value="musico">Músico</option>
            <option value="admin">Administrador</option>
        </select>
        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition">
            Registrar Integrante
        </button>
    </form>
</body>
</html>