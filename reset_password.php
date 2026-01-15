<?php
require_once 'db_config.php';

$token = $_GET['token'] ?? '';
$message = "";
$validToken = false;

if ($token) {
    $stmt = $pdo->prepare("SELECT id FROM members WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user) $validToken = true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $validToken) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE members SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
    if ($stmt->execute([$password, $token])) {
        $message = "<div class='bg-green-100 text-green-700 p-4 rounded-xl mb-4 text-sm font-bold'>✅ ¡Contraseña actualizada! <a href='login.php' class='underline'>Iniciar Sesión</a></div>";
        $validToken = false; // Ocultar formulario
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl w-full max-w-md border border-slate-100 text-center">
        <h2 class="text-2xl font-black text-slate-900 uppercase italic mb-2">Nueva Contraseña</h2>
        
        <?php echo $message; ?>

        <?php if ($validToken): ?>
        <form method="POST" class="space-y-4 text-left mt-6">
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">Ingresa tu nueva clave</label>
                <input type="password" name="password" required class="w-full p-4 bg-slate-50 border-2 border-transparent rounded-2xl font-bold outline-none focus:border-blue-500/20 transition-all text-sm">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-black py-4 rounded-2xl shadow-lg hover:bg-blue-700 transition-all uppercase tracking-widest text-xs">Guardar Cambios</button>
        </form>
        <?php elseif (empty($message)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-4 text-sm font-bold">⚠️ Enlace inválido o expirado.</div>
            <a href="forgot_password.php" class="text-blue-600 font-bold text-xs uppercase tracking-widest">Solicitar nuevo enlace</a>
        <?php endif; ?>
    </div>
</body>
</html>