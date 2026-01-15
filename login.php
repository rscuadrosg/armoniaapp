<?php
require_once 'db_config.php';
session_start();

// Si ya está logueado, al index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Buscamos al usuario por email
    $stmt = $pdo->prepare("SELECT id, full_name, password, role FROM members WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verificamos contraseña (asumiendo que usaste password_hash anteriormente)
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role']; // 'admin' o 'musico'
        header("Location: index.php");
        exit;
    } else {
        $error = "El correo o la contraseña son incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ArmoniaApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-10 md:p-14 rounded-[3.5rem] shadow-2xl w-full max-w-md border border-slate-100 text-center">
        <h2 class="text-4xl font-black text-slate-900 tracking-tighter italic uppercase mb-2">Armonia<span class="text-blue-600">App</span></h2>
        <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest mb-10 text-center">Acceso al Sistema</p>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-xs font-bold uppercase"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="text-left space-y-6">
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">Correo Electrónico</label>
                <input type="email" name="email" required class="w-full p-5 bg-slate-50 border-2 border-transparent rounded-[1.8rem] font-bold outline-none focus:border-blue-500/20 transition-all">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">Contraseña</label>
                <input type="password" name="password" required class="w-full p-5 bg-slate-50 border-2 border-transparent rounded-[1.8rem] font-bold outline-none focus:border-blue-500/20 transition-all">
            </div>
            <div class="text-right">
                <a href="forgot_password.php" class="text-[10px] font-bold text-blue-500 hover:text-blue-700 uppercase tracking-widest">¿Olvidaste tu contraseña?</a>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-black py-5 rounded-[1.8rem] shadow-xl hover:bg-blue-700 transition-all uppercase tracking-widest text-[11px] mt-4">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>