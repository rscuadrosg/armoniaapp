<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definimos la variable globalmente para que view_event.php la reconozca
$currentRole = $_SESSION['user_role'] ?? 'musico';
$isAdmin = ($currentRole === 'admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <nav class="bg-[#1e293b] text-white shadow-2xl mb-8 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-6">
                <a href="index.php" class="text-xl font-black italic tracking-tighter uppercase">
                    Armonia<span class="text-blue-500">App</span>
                </a>
            </div>

            <div class="flex gap-4 overflow-x-auto no-scrollbar items-center text-[10px] font-black uppercase tracking-widest">
                <a href="index.php" class="hover:text-blue-300">Home</a>
                
                <a href="repertorio_lista.php" class="hover:text-blue-300">Repertorio</a>
                
                <?php if ($isAdmin): ?>
                    <a href="members.php" class="hover:text-blue-300">Equipo</a>
                    <a href="events.php" class="hover:text-blue-300">Servicios</a>
                    <a href="settings_band.php" class="hover:text-blue-300" title="Configuracion de Banda">Banda</a>
                    <a href="settings_tags.php" class="hover:text-blue-300" title="Gestionar Etiquetas">Etiquetas</a>
                <?php else: ?>
                    <a href="events.php" class="hover:text-blue-300">Servicios</a>
                <?php endif; ?>
                
                <a href="dashboard.php" class="bg-blue-600 px-4 py-2 rounded-xl shadow-lg shadow-blue-900/50 flex items-center gap-2 border border-blue-400/30">
                    <span class="text-sm">&#128100;</span> MI PANEL
                </a>
                <a href="logout.php" class="bg-slate-700 px-4 py-2 rounded-xl hover:bg-red-600 transition-colors border border-slate-600 text-white" title="Cerrar Sesión">
                    SALIR
                </a>
            </div>
        </div>
    </nav>