<?php
require_once 'db_config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $new_password = $_POST['password'];

    // 1. Generar el hash seguro
    $hash = password_hash($new_password, PASSWORD_DEFAULT);

    // 2. Actualizar en la base de datos
    $stmt = $pdo->prepare("UPDATE members SET password = ? WHERE email = ?");
    if ($stmt->execute([$hash, $email])) {
        if ($stmt->rowCount() > 0) {
            $message = "<div class='bg-green-100 text-green-700 p-4 rounded-xl mb-4'>✅ Contraseña actualizada. <a href='login.php' class='font-bold underline'>Ir al Login</a></div>";
        } else {
            $message = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-4'>⚠️ No se encontró el email: <b>" . htmlspecialchars($email) . "</b></div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Fix Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md">
        <h2 class="text-2xl font-black text-slate-800 mb-4">Generador de Contraseña</h2>
        <?php echo $message; ?>
        <form method="POST" class="space-y-4">
            <input type="email" name="email" placeholder="Correo del usuario" required class="w-full p-3 border rounded-xl">
            <input type="text" name="password" placeholder="Nueva Contraseña" required class="w-full p-3 border rounded-xl">
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700">Actualizar Contraseña</button>
        </form>
    </div>
</body>
</html>
