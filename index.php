<?php
require_once 'db_config.php';
include 'header.php';

// --- 1. MÉTRICAS GENERALES ---
$totalSongs = $pdo->query("SELECT COUNT(*) FROM songs")->fetchColumn();
$noPdf = $pdo->query("SELECT COUNT(*) FROM songs WHERE has_lyrics IS NULL OR has_lyrics = '' OR has_lyrics = '0'")->fetchColumn();
$noTrack = $pdo->query("SELECT COUNT(*) FROM songs WHERE has_multitrack = 0")->fetchColumn();

// --- 2. TOP 5 MÁS TOCADAS ---
$sqlMost = "SELECT s.title, COUNT(es.song_id) as total 
            FROM songs s
            JOIN event_songs es ON s.id = es.song_id
            GROUP BY s.id 
            ORDER BY total DESC 
            LIMIT 5";
$mostPlayed = $pdo->query($sqlMost)->fetchAll(PDO::FETCH_ASSOC);

// --- 3. TOP 5 MENOS TOCADAS ---
$sqlLeast = "SELECT s.title, COUNT(es.song_id) as total 
             FROM songs s
             LEFT JOIN event_songs es ON s.id = es.song_id
             GROUP BY s.id 
             ORDER BY total ASC 
             LIMIT 5";
$leastPlayed = $pdo->query($sqlLeast)->fetchAll(PDO::FETCH_ASSOC);

// --- 4. OBTENER PRÓXIMOS SERVICIOS ---
// Ajustado para obtener el setlist en la misma consulta o posterior si es necesario
$proximosServicios = $pdo->query("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mx-auto px-4 max-w-7xl pb-20">
    <header class="py-10 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tighter italic uppercase">DASHBOARD</h1>
            <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.3em]">Gestión de Alabanza</p>
        </div>
        <a href="add_event.php" class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest shadow-[0_10px_20px_-5px_rgba(37,99,235,0.4)] hover:scale-105 transition-all">
            + Programar Servicio
        </a>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-blue-600 p-8 rounded-[2.5rem] text-white shadow-xl shadow-blue-100">
            <span class="text-[10px] font-black uppercase opacity-80 tracking-widest">Total Repertorio</span>
            <div class="text-6xl font-black my-2"><?php echo $totalSongs; ?></div>
            <p class="text-[10px] font-bold uppercase tracking-tighter">Canciones en biblioteca</p>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
            <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Faltan PDFs</span>
            <div class="text-5xl font-black text-orange-500 my-2"><?php echo $noPdf; ?></div>
            <p class="text-[10px] font-bold text-slate-400 italic">Pendientes de subir link</p>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
            <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Sin Multitracks</span>
            <div class="text-5xl font-black text-indigo-500 my-2"><?php echo $noTrack; ?></div>
            <p class="text-[10px] font-bold text-slate-400 italic">Ejecución solo acústica</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
        
        <div class="lg:col-span-8">
            <h3 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-6 ml-4">Próximos Servicios</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if(empty($proximosServicios)): ?>
                    <div class="md:col-span-2 p-10 bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-200 text-center text-slate-400 text-xs font-bold">
                        No hay servicios programados
                    </div>
                <?php endif; ?>

                <?php foreach($proximosServicios as $evento): ?>
                <div class="bg-white p-8 rounded-[3rem] shadow-2xl shadow-slate-200/40 border border-slate-50 relative group transition-all hover:border-blue-100">
                    
                    <div class="absolute top-8 right-10 text-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>

                    <div class="flex items-center gap-5 mb-8">
                        <div class="bg-[#13192b] text-white w-[90px] h-[105px] rounded-[2.2rem] flex flex-col items-center justify-center shadow-xl shadow-slate-900/20">
                            <span class="text-[10px] font-black uppercase tracking-widest opacity-60"><?php echo date('M', strtotime($evento['event_date'])); ?></span>
                            <span class="text-4xl font-black leading-none my-1"><?php echo date('d', strtotime($evento['event_date'])); ?></span>
                            <span class="text-[9px] font-bold opacity-30"><?php echo date('Y', strtotime($evento['event_date'])); ?></span>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-black text-[#1e293b] uppercase text-lg leading-[1.1] tracking-tighter mb-1">
                                <?php echo htmlspecialchars($evento['description']); ?>
                            </h4>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]"></span>
                                <p class="text-[10px] text-slate-400 font-bold tracking-widest uppercase">Activo</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-3">
                        <a href="view_event.php?id=<?php echo $evento['id']; ?>" 
                           class="flex-1 bg-[#f8fafc] text-[#64748b] py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest text-center border border-slate-100 hover:bg-slate-100 transition-all">
                            Configurar
                        </a>
                        <a href="view_event.php?id=<?php echo $evento['id']; ?>" 
                           class="flex-1 bg-[#3b82f6] text-white py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest text-center shadow-[0_10px_20px_-5px_rgba(59,130,246,0.3)] hover:bg-blue-600 transition-all">
                            Ver Resumen
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="lg:col-span-4">
            <h3 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-6 ml-4">Herramientas</h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="repertorio_lista.php" class="p-6 bg-slate-900 text-white rounded-[2rem] text-center hover:bg-blue-600 transition-all shadow-xl shadow-slate-200">
                    <div class="text-2xl mb-2">🎵</div>
                    <span class="text-[9px] font-black uppercase tracking-widest">Repertorio</span>
                </a>
                <a href="equipo.php" class="p-6 bg-white border border-slate-100 rounded-[2rem] text-center hover:shadow-md transition-all">
                    <div class="text-2xl mb-2">🎸</div>
                    <span class="text-[9px] font-black uppercase text-slate-600 tracking-widest">Equipo</span>
                </a>
                <a href="repertorio_borrar.php" class="p-6 bg-red-50 text-red-600 rounded-[2rem] text-center hover:bg-red-600 hover:text-white transition-all border border-red-100 col-span-2">
                    <div class="text-2xl mb-2">⚙️</div>
                    <span class="text-[9px] font-black uppercase tracking-widest">Limpieza de Base de Datos</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white p-10 rounded-[3rem] shadow-sm border border-slate-50">
            <h3 class="text-xs font-black uppercase text-slate-400 tracking-[0.2em] mb-8 flex items-center gap-3">
                <span class="text-green-500 text-xl">🔥</span> Las Más Tocadas
            </h3>
            <div class="space-y-4">
                <?php if(empty($mostPlayed)) echo '<p class="text-slate-300 text-xs italic">Aún no hay registros</p>'; ?>
                <?php foreach($mostPlayed as $song): ?>
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-transparent hover:border-green-100 transition-all">
                    <span class="font-bold text-slate-700 text-sm"><?php echo htmlspecialchars($song['title']); ?></span>
                    <span class="bg-green-100 text-green-700 px-4 py-1 rounded-full text-[10px] font-black"><?php echo $song['total']; ?> Veces</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white p-10 rounded-[3rem] shadow-sm border border-slate-50">
            <h3 class="text-xs font-black uppercase text-slate-400 tracking-[0.2em] mb-8 flex items-center gap-3">
                <span class="text-blue-400 text-xl">❄️</span> En el olvido / Nuevas
            </h3>
            <div class="space-y-4">
                <?php if(empty($leastPlayed)) echo '<p class="text-slate-300 text-xs italic">Aún no hay registros</p>'; ?>
                <?php foreach($leastPlayed as $song): ?>
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-transparent hover:border-blue-100 transition-all">
                    <span class="font-bold text-slate-700 text-sm"><?php echo htmlspecialchars($song['title']); ?></span>
                    <span class="bg-slate-200 text-slate-500 px-4 py-1 rounded-full text-[10px] font-black"><?php echo $song['total']; ?> Veces</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>