<?php
require_once 'db_config.php';
require_once 'auth.php';
include 'header.php';
?>

<div class="container mx-auto px-4 max-w-7xl py-12">
    <header class="mb-12 text-center">
        <h1 class="text-4xl font-black text-slate-900 tracking-tighter uppercase italic">Panel Principal</h1>
        <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mt-2">Selecciona un módulo para comenzar</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Módulo Alabanza -->
        <a href="worship.php" class="group bg-white p-8 rounded-[2.5rem] shadow-xl border border-slate-100 hover:border-blue-200 hover:shadow-2xl transition-all relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-10 -mt-10 opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <div class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-lg shadow-blue-200">
                    🎵
                </div>
                <h2 class="text-2xl font-black text-slate-800 uppercase italic tracking-tight mb-2">Alabanza</h2>
                <p class="text-sm text-slate-500 font-medium leading-relaxed">Gestión de repertorio, programación de servicios y equipo musical.</p>
            </div>
        </a>

        <!-- Módulo Ujieres (Placeholder) -->
        <div class="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-200 opacity-60 cursor-not-allowed relative overflow-hidden">
            <div class="w-16 h-16 bg-slate-200 text-slate-400 rounded-2xl flex items-center justify-center text-3xl mb-6">
                🤝
            </div>
            <h2 class="text-2xl font-black text-slate-400 uppercase italic tracking-tight mb-2">Ujieres</h2>
            <p class="text-sm text-slate-400 font-medium leading-relaxed">Próximamente: Gestión de bienvenida y orden.</p>
        </div>

        <!-- Módulo Niños (Placeholder) -->
        <div class="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-200 opacity-60 cursor-not-allowed relative overflow-hidden">
            <div class="w-16 h-16 bg-slate-200 text-slate-400 rounded-2xl flex items-center justify-center text-3xl mb-6">
                🎈
            </div>
            <h2 class="text-2xl font-black text-slate-400 uppercase italic tracking-tight mb-2">Niños</h2>
            <p class="text-sm text-slate-400 font-medium leading-relaxed">Próximamente: Maestros y clases infantiles.</p>
        </div>
    </div>
</div>

<?php 
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>
</body>
</html>