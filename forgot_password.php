<?php
require_once 'db_config.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
        
        $update = $pdo->prepare("UPDATE members SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $update->execute([$token, $expires, $email]);

        // Enviar Email
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
        $subject = "Recuperar Contraseña - ArmoniaApp";
        
        $msg = "
        <html>
        <body style='font-family: sans-serif; background-color: #f8fafc; padding: 20px;'>
          <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;'>
            <h2 style='color: #1e293b; text-align: center; margin-bottom: 30px; font-style: italic; text-transform: uppercase; letter-spacing: -1px;'>Armonia<span style='color: #2563eb;'>App</span></h2>
            
            <p style='color: #475569; font-size: 16px; line-height: 1.5; text-align: center;'>Hemos recibido una solicitud para restablecer tu contraseña.</p>
            
            <div style='text-align: center; margin: 40px 0;'>
              <a href='$resetLink' style='display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 12px; font-weight: bold; text-transform: uppercase; font-size: 12px; letter-spacing: 1px;'>Restablecer Contraseña</a>
            </div>
            
            <p style='color: #64748b; font-size: 14px; text-align: center;'>Si no solicitaste este cambio, puedes ignorar este correo.</p>
            <p style='color: #94a3b8; font-size: 12px; text-align: center; margin-top: 20px;'>Este enlace expirará en 1 hora.</p>
          </div>
        </body>
        </html>";
        
        // Usar el dominio actual para evitar caer en SPAM
        $domain = $_SERVER['SERVER_NAME'];
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: ArmoniaApp <no-reply@$domain>" . "\r\n";
        $headers .= "Reply-To: no-reply@$domain" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Nota: mail() requiere un servidor SMTP configurado. 
        // Si estás en local, verás el link de prueba en el mensaje de error/éxito.
        if(@mail($email, $subject, $msg, $headers)) {
            $message = "<div class='bg-green-100 text-green-700 p-4 rounded-xl mb-4 text-sm font-bold'>✅ Se ha enviado un enlace a tu correo.</div>";
        } else {
            // Fallback para desarrollo local
            $message = "<div class='bg-orange-100 text-orange-700 p-4 rounded-xl mb-4 text-sm font-bold'>⚠️ Correo simulado (Localhost):<br><a href='$resetLink' class='underline'>Click aquí para resetear</a></div>";
        }
    } else {
        $message = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-4 text-sm font-bold'>⚠️ No encontramos ese correo electrónico.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl w-full max-w-md border border-slate-100 text-center">
        <h2 class="text-2xl font-black text-slate-900 uppercase italic mb-2">Recuperar Acceso</h2>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-8">Ingresa tu correo registrado</p>

        <?php echo $message; ?>

        <form method="POST" class="space-y-4 text-left">
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">Correo Electrónico</label>
                <input type="email" name="email" required class="w-full p-4 bg-slate-50 border-2 border-transparent rounded-2xl font-bold outline-none focus:border-blue-500/20 transition-all text-sm">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-black py-4 rounded-2xl shadow-lg hover:bg-blue-700 transition-all uppercase tracking-widest text-xs">Enviar Enlace</button>
        </form>
        
        <div class="mt-8 pt-6 border-t border-slate-100">
            <a href="login.php" class="text-slate-400 hover:text-slate-600 text-xs font-bold uppercase tracking-widest">Volver al Login</a>
        </div>
    </div>
</body>
</html>