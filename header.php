<?php
// Evitar doble inclusión y errores de headers
if (defined('HEADER_LOADED')) return;
define('HEADER_LOADED', true);

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

// Cargar Configuración (Logo/Favicon)
$app_settings = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM app_settings");
        while($row = $stmt->fetch()) $app_settings[$row['setting_key']] = $row['setting_value'];
    } catch (Exception $e) { /* Ignorar si tabla no existe aun */ }
}

// Detectar Módulo Activo
$current_page = basename($_SERVER['PHP_SELF']);
$worship_pages = ['worship.php', 'repertorio_lista.php', 'events.php', 'members.php', 'view_event.php', 'view_event_musico.php', 'settings_band.php', 'settings_tags.php', 'generate_schedule.php', 'auto_assign_team.php', 'repertorio_borrar.php', 'add_event.php', 'add_event_songs.php', 'edit_song.php', 'import_songs.php', 'settings_general.php', 'dashboard.php'];
$is_worship_module = in_array($current_page, $worship_pages);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
    <?php if(!empty($app_settings['favicon_path'])): ?>
        <link rel="icon" href="uploads/<?php echo $app_settings['favicon_path']; ?>">
    <?php endif; ?>
    <title><?php echo htmlspecialchars($app_settings['app_name'] ?? 'ArmoniaApp'); ?></title>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col md:flex-row">

    <!-- SIDEBAR (Solo visible en escritorio y si estamos en un módulo) -->
    <?php if($is_worship_module): ?>
    <aside class="hidden md:flex flex-col w-64 bg-[#1e293b] text-white h-screen sticky top-0 overflow-y-auto shrink-0">
        <div class="p-6">
            <a href="worship.php" class="flex items-center gap-3 text-xl font-black italic tracking-tighter uppercase">
                <?php if(!empty($app_settings['logo_path'])): ?>
                    <img src="uploads/<?php echo $app_settings['logo_path']; ?>" class="h-14 w-auto">
                <?php endif; ?>
                <span class="<?php echo !empty($app_settings['logo_path']) ? 'hidden' : ''; ?>"><?php echo htmlspecialchars($app_settings['app_name'] ?? 'ArmoniaApp'); ?></span>
            </a>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">Módulo de Alabanza</p>
        </div>

        <nav class="flex-1 px-4 space-y-2">
            <a href="worship.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-colors <?php echo $current_page == 'worship.php' ? 'bg-blue-600 shadow-lg shadow-blue-900/50' : 'text-slate-400'; ?>">
                <span>🏠</span> <span class="text-xs font-bold uppercase tracking-widest">Inicio</span>
            </a>
            <a href="repertorio_lista.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-colors <?php echo $current_page == 'repertorio_lista.php' ? 'bg-blue-600 shadow-lg shadow-blue-900/50' : 'text-slate-400'; ?>">
                <span>🎵</span> <span class="text-xs font-bold uppercase tracking-widest">Repertorio</span>
            </a>
            <a href="events.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-colors <?php echo $current_page == 'events.php' ? 'bg-blue-600 shadow-lg shadow-blue-900/50' : 'text-slate-400'; ?>">
                <span>📅</span> <span class="text-xs font-bold uppercase tracking-widest">Servicios</span>
            </a>
            
            <?php if ($isAdmin || $isLeader): ?>
            <div class="pt-4 pb-2 px-4 text-[9px] font-black text-slate-600 uppercase tracking-widest">Gestión</div>
            <a href="members.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-colors <?php echo $current_page == 'members.php' ? 'bg-blue-600 shadow-lg shadow-blue-900/50' : 'text-slate-400'; ?>">
                <span>👥</span> <span class="text-xs font-bold uppercase tracking-widest">Equipo</span>
            </a>
            <a href="auto_assign_team.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-colors <?php echo $current_page == 'auto_assign_team.php' ? 'bg-blue-600 shadow-lg shadow-blue-900/50' : 'text-slate-400'; ?>">
                <span>🤖</span> <span class="text-xs font-bold uppercase tracking-widest">Auto-Asignar</span>
            </a>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
            <div class="pt-4 pb-2 px-4 text-[9px] font-black text-slate-600 uppercase tracking-widest">Configuración</div>
            <a href="settings_band.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-colors <?php echo $current_page == 'settings_band.php' ? 'bg-blue-600 shadow-lg shadow-blue-900/50' : 'text-slate-400'; ?>">
                <span>🎸</span> <span class="text-xs font-bold uppercase tracking-widest">Banda</span>
            </a>
            <a href="settings_tags.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-colors <?php echo $current_page == 'settings_tags.php' ? 'bg-blue-600 shadow-lg shadow-blue-900/50' : 'text-slate-400'; ?>">
                <span>🏷️</span> <span class="text-xs font-bold uppercase tracking-widest">Etiquetas</span>
            </a>
            <a href="generate_schedule.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-colors <?php echo $current_page == 'generate_schedule.php' ? 'bg-blue-600 shadow-lg shadow-blue-900/50' : 'text-slate-400'; ?>">
                <span>✨</span> <span class="text-xs font-bold uppercase tracking-widest">Generador</span>
            </a>
            <?php endif; ?>
        </nav>

        <div class="p-4 border-t border-slate-700">
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors text-slate-300">
                <span>⬅</span> <span class="text-xs font-bold uppercase tracking-widest">Cambiar Módulo</span>
            </a>
        </div>
    </aside>
    <?php endif; ?>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- TOP BAR (Móvil y Escritorio) -->
        <nav class="bg-[#1e293b] border-b border-slate-700 px-6 py-3 flex justify-between items-center shrink-0 z-40 shadow-md">
            <div class="flex items-center gap-4">
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden text-slate-400 hover:text-white focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                
                <!-- Logo (Solo visible en móvil o si no hay sidebar) -->
                <div class="<?php echo $is_worship_module ? 'md:hidden' : ''; ?> flex items-center gap-2">
                    <?php if(!empty($app_settings['logo_path'])): ?>
                        <img src="uploads/<?php echo $app_settings['logo_path']; ?>" class="h-10 w-auto">
                    <?php endif; ?>
                    <span class="<?php echo !empty($app_settings['logo_path']) ? 'hidden md:block' : ''; ?> text-lg font-black italic tracking-tighter uppercase text-white"><?php echo htmlspecialchars($app_settings['app_name'] ?? 'ArmoniaApp'); ?></span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-xl transition-all text-[10px] font-black uppercase tracking-widest border border-slate-700">
                    <span>👤</span> <span class="hidden sm:inline">Mi Perfil</span>
                </a>

                <?php if($isAdmin): ?>
                    <a href="settings_general.php" class="flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-xl transition-all text-[10px] font-black uppercase tracking-widest border border-slate-700">
                        <span>⚙️</span> <span class="hidden sm:inline">Configuración</span>
                    </a>
                <?php endif; ?>
                <div class="h-6 w-px bg-slate-700"></div>
                <a href="logout.php" class="text-xs font-bold text-red-400 hover:text-red-300 uppercase tracking-widest">Salir</a>
            </div>
        </nav>

        <!-- SCROLLABLE CONTENT -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 relative">
            
            <!-- Mobile Menu Overlay (Solo para móvil) -->
            <div id="mobile-menu" class="hidden fixed inset-0 bg-slate-900/95 z-50 p-6 flex flex-col overflow-y-auto">
                <div class="flex justify-between items-center mb-8 shrink-0">
                    <span class="text-white text-xl font-black italic uppercase">Menú</span>
                    <button id="close-mobile-menu" class="text-white text-2xl">✕</button>
                </div>
                
                <div class="space-y-6 text-center flex-1">
                    <div>
                        <a href="index.php" class="block text-white text-2xl font-black italic uppercase mb-2">Hub Principal</a>
                        <a href="dashboard.php" class="block text-slate-400 text-sm font-bold uppercase tracking-widest mb-4">👤 Mi Perfil</a>
                        <a href="worship.php" class="block text-blue-400 text-lg font-bold">Panel de Alabanza</a>
                    </div>

                    <div class="space-y-4 border-t border-slate-700 pt-6">
                        <a href="repertorio_lista.php" class="block text-slate-300 text-xl font-bold hover:text-white">🎵 Repertorio</a>
                        <a href="events.php" class="block text-slate-300 text-xl font-bold hover:text-white">📅 Servicios</a>
                        
                        <?php if ($isAdmin || $isLeader): ?>
                            <a href="members.php" class="block text-slate-300 text-xl font-bold hover:text-white">👥 Equipo</a>
                            <a href="auto_assign_team.php" class="block text-slate-300 text-xl font-bold hover:text-white">🤖 Auto-Asignar</a>
                        <?php endif; ?>
                    </div>

                    <?php if($isAdmin): ?>
                    <div class="space-y-3 border-t border-slate-700 pt-6">
                        <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Configuración</p>
                        <a href="settings_band.php" class="block text-slate-400 text-sm font-bold hover:text-white">🎸 Banda</a>
                        <a href="settings_tags.php" class="block text-slate-400 text-sm font-bold hover:text-white">🏷️ Etiquetas</a>
                        <a href="generate_schedule.php" class="block text-slate-400 text-sm font-bold hover:text-white">✨ Generador</a>
                        <a href="settings_general.php" class="block text-slate-400 text-sm font-bold hover:text-white">⚙️ General</a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-8 pt-6 border-t border-slate-700 shrink-0">
                    <a href="logout.php" class="block bg-red-600 text-white py-4 rounded-xl font-black uppercase tracking-widest text-center">Cerrar Sesión</a>
                </div>
            </div>

            <script>
                const btn = document.getElementById('mobile-menu-btn');
                const closeBtn = document.getElementById('close-mobile-menu');
                const menu = document.getElementById('mobile-menu');
                
                if(btn) {
                    btn.addEventListener('click', () => menu.classList.remove('hidden'));
                    closeBtn.addEventListener('click', () => menu.classList.add('hidden'));
                }
            </script>