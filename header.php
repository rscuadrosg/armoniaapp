<?php
// Forzar codificación UTF-8 en el navegador
header('Content-Type: text/html; charset=utf-8');

// Forzar codificación UTF-8 en la conexión a base de datos (si existe $pdo)
if (isset($pdo)) {
    $pdo->exec("SET NAMES 'utf8mb4'");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definimos la variable globalmente para que view_event.php la reconozca
$currentRole = $_SESSION['user_role'] ?? 'musico';
$isAdmin = ($currentRole === 'admin');
$isLeader = ($currentRole === 'lider');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
</head>
<body class="bg-slate-50 min-h-screen">

    <nav class="bg-[#1e293b] text-white shadow-lg mb-6 sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="index.php" class="text-xl font-black italic tracking-tighter uppercase">
                    Armonia<span class="text-blue-500">App</span>
                </a>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden text-slate-300 hover:text-white focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-6 text-[10px] font-black uppercase tracking-widest">
                    <a href="index.php" class="hover:text-blue-300 transition-colors">Home</a>
                    <a href="repertorio_lista.php" class="hover:text-blue-300 transition-colors">Repertorio</a>
                    
                    <?php if ($isAdmin): ?>
                        <a href="members.php" class="hover:text-blue-300 transition-colors">Equipo</a>
                        <a href="events.php" class="hover:text-blue-300 transition-colors">Servicios</a>
                        <a href="settings_band.php" class="hover:text-blue-300 transition-colors" title="Configuracion de Banda">Banda</a>
                        <a href="settings_tags.php" class="hover:text-blue-300 transition-colors" title="Gestionar Etiquetas">Etiquetas</a>
                        <a href="generate_schedule.php" class="hover:text-blue-300 text-emerald-400 transition-colors" title="Generador Automático">Autogenerador</a>
                        <a href="auto_assign_team.php" class="hover:text-blue-300 text-indigo-400 transition-colors" title="Asignar Equipo">Auto-Equipo</a>
                    <?php elseif ($isLeader): ?>
                        <a href="events.php" class="hover:text-blue-300 transition-colors">Servicios</a>
                        <a href="members.php" class="hover:text-blue-300 transition-colors">Equipo</a>
                        <a href="auto_assign_team.php" class="hover:text-blue-300 text-indigo-400 transition-colors" title="Asignar Equipo">Auto-Equipo</a>
                    <?php else: ?>
                        <a href="events.php" class="hover:text-blue-300 transition-colors">Servicios</a>
                    <?php endif; ?>
                    
                    <div class="flex items-center gap-3 ml-4 border-l border-slate-700 pl-4">
                        <a href="dashboard.php" class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-xl shadow-lg shadow-blue-900/50 flex items-center gap-2 border border-blue-400/30 transition-all">
                            <span class="text-sm">&#128100;</span> MI PANEL
                        </a>
                        <a href="logout.php" class="text-slate-400 hover:text-red-400 transition-colors" title="Cerrar Sesión">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu (Hidden by default) -->
        <div id="mobile-menu" class="hidden md:hidden bg-slate-800 border-t border-slate-700">
            <div class="px-4 pt-2 pb-6 space-y-1 flex flex-col text-xs font-bold uppercase tracking-widest">
                <a href="index.php" class="block px-3 py-3 rounded-md hover:bg-slate-700 text-slate-300 hover:text-white">Home</a>
                
                <a href="repertorio_lista.php" class="block px-3 py-3 rounded-md hover:bg-slate-700 text-slate-300 hover:text-white">Repertorio</a>
                
                <?php if ($isAdmin): ?>
                    <a href="members.php" class="block px-3 py-3 rounded-md hover:bg-slate-700 text-slate-300 hover:text-white">Equipo</a>
                    <a href="events.php" class="block px-3 py-3 rounded-md hover:bg-slate-700 text-slate-300 hover:text-white">Servicios</a>
                    <a href="settings_band.php" class="block px-3 py-3 rounded-md hover:bg-slate-700 text-slate-300 hover:text-white">Banda</a>
                    <a href="settings_tags.php" class="block px-3 py-3 rounded-md hover:bg-slate-700 text-slate-300 hover:text-white">Etiquetas</a>
                    <a href="generate_schedule.php" class="block px-3 py-3 rounded-md hover:bg-slate-700 text-emerald-400 hover:text-emerald-300">Autogenerador</a>
                <?php else: ?>
                    <a href="events.php" class="block px-3 py-3 rounded-md hover:bg-slate-700 text-slate-300 hover:text-white">Servicios</a>
                <?php endif; ?>
                
                <div class="border-t border-slate-700 mt-4 pt-4 flex gap-2">
                    <a href="dashboard.php" class="flex-1 bg-blue-600 text-center py-3 rounded-xl shadow-lg font-black">MI PANEL</a>
                    <a href="logout.php" class="bg-red-600/20 text-red-400 px-4 py-3 rounded-xl text-center font-black">SALIR</a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Lógica del Menú Móvil
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');

        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });
    </script>