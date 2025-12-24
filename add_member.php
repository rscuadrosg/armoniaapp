<?php
require_once 'db_config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("INSERT INTO members (full_name, member_type) VALUES (?, ?)");
    if ($stmt->execute([$_POST['full_name'], $_POST['member_type']])) {
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
        <select name="member_type" class="w-full p-3 border rounded-xl mb-6 bg-white">
            <option value="Internal">Interno</option>
            <option value="External">Externo (EXT)</option>
        </select>
        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition">
            Registrar Integrante
        </button>
    </form>
</body>
</html>