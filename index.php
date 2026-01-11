<?php
require_once 'db_config.php';
require_once 'auth.php';
include 'header.php';

// --- 2. MÉTRICAS GENERALES ---
$totalSongs = $pdo->query("SELECT COUNT(*) FROM songs")->fetchColumn();
$noPdf = $pdo->query("SELECT COUNT(*) FROM songs WHERE has_lyrics IS NULL OR has_lyrics = '' OR has_lyrics = '0'")->fetchColumn();
$noMultitrack = $pdo->query("SELECT COUNT(*) FROM songs WHERE has_multitrack = 0")->fetchColumn();

// --- 3. TOP 5 MÁS/MENOS TOCADAS ---
$sqlMost = "SELECT s.title, COUNT(es.song_id) as total 
            FROM songs s
            JOIN event_songs es ON s.id = es.song_id
            GROUP BY s.id ORDER BY total DESC LIMIT 5";
$mostPlayed = $pdo->query($sqlMost)->fetchAll(PDO::FETCH_ASSOC);

$sqlLeast = "SELECT s.title, COUNT(es.song_id) as total 
             FROM songs s
             LEFT JOIN event_songs es ON s.id = es.song_id
             GROUP BY s.id ORDER BY total ASC LIMIT 5";
$leastPlayed = $pdo->query($sqlLeast)->fetchAll(PDO::FETCH_ASSOC);

// --- 4. PRÓXIMOS SERVICIOS ---
$proximosServicios = $pdo->query("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mx-auto px-4 max-w-7xl pb-20">
    <!-- Header Compacto -->
    <header class="py-4 flex flex-col md:flex-row justify-between items-center gap-4 mb-2">
        <div class="text-center md:text-left">
            <h1 class="text-2xl md:text-4xl font-black text-slate-900 tracking-tighter italic uppercase">DASHBOARD</h1>
            <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.3em]">Gestión de Alabanza</p>
        </div>
        
        <div class="flex flex-wrap justify-center gap-2">
            <a href="repertorio_lista.php" class="bg-slate-900 text-white px-4 py-2 rounded-xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-300 flex items-center gap-2 h-10 transform active:scale-95" title="Repertorio">
                <span class="text-lg italic">♫</span>
                <span class="text-[10px] font-black uppercase tracking-widest">Repertorio</span>
            </a>
            
            <?php if ($isAdmin): ?>
                <a href="members.php" class="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 flex items-center gap-2 h-10 transform active:scale-95" title="Equipo">
                    <span class="text-lg">&#127928;</span>
                    <span class="text-[10px] font-black uppercase tracking-widest">Equipo</span>
                </a>
                
                <a href="repertorio_borrar.php" class="bg-rose-600 text-white px-4 py-2 rounded-xl hover:bg-rose-700 transition-all shadow-lg shadow-rose-200 flex items-center gap-2 h-10 transform active:scale-95" title="Limpieza">
                    <span class="text-lg">&#9881;</span>
                    <span class="text-[10px] font-black uppercase tracking-widest">Limpieza</span>
                </a>

                <div class="h-6 w-px bg-slate-200 mx-2 hidden md:block"></div>

                <a href="add_event.php" class="bg-blue-600 text-white px-5 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all flex items-center gap-2 h-10 transform active:scale-95">
                    <span>+</span> <span>Programar</span>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- 1. Próximos Servicios (Prioridad Alta) -->
    <div class="mb-6">
        <h3 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-3 ml-1">Próximos Servicios</h3>
        
        <?php if(empty($proximosServicios)): ?>
            <div class="p-6 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 text-center text-slate-400 text-xs font-bold">
                No hay servicios programados
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 divide-y divide-slate-50">
                <?php foreach($proximosServicios as $evento): ?>
                <div class="p-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="bg-slate-100 text-slate-600 w-10 h-10 rounded-xl flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-[8px] font-black uppercase leading-none"><?php echo date('M', strtotime($evento['event_date'])); ?></span>
                            <span class="text-sm font-black leading-none"><?php echo date('d', strtotime($evento['event_date'])); ?></span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-black text-slate-800 text-xs uppercase truncate">
                                <?php echo htmlspecialchars($evento['description']); ?>
                            </h4>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">
                                <?php echo date('Y', strtotime($evento['event_date'])); ?> • Activo
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 flex-shrink-0">
                        <?php if ($isAdmin): ?>
                        <a href="view_event.php?id=<?php echo $evento['id']; ?>" 
                           class="w-8 h-8 flex items-center justify-center bg-slate-50 text-slate-400 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all" title="Configurar">
                            ⚙️
                        </a>
                        <?php endif; ?>
                        
                        <a href="view_event_musico.php?id=<?php echo $evento['id']; ?>" 
                           class="w-8 h-8 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all" title="Ver Resumen">
                            👁️
                        </a>

                        <?php if ($isAdmin): ?>
                        <a href="delete_event.php?id=<?php echo $evento['id']; ?>" 
                           class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-400 rounded-lg hover:bg-red-500 hover:text-white transition-all"
                           onclick="return confirm('¿Eliminar servicio?')">
                            ✕
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 2. Métricas Compactas (Fila única) -->
    <div class="grid grid-cols-3 gap-3 mb-8">
        <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm text-center">
            <div class="text-xl font-black text-blue-600"><?php echo $totalSongs; ?></div>
            <p class="text-[7px] font-bold text-slate-400 uppercase tracking-tighter">Canciones</p>
        </div>

        <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm text-center">
            <div class="text-xl font-black text-orange-500"><?php echo $noPdf; ?></div>
            <p class="text-[7px] font-bold text-slate-400 uppercase tracking-tighter">Sin PDF</p>
        </div>

        <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm text-center">
            <div class="text-xl font-black text-indigo-500"><?php echo $noMultitrack; ?></div>
            <p class="text-[7px] font-bold text-slate-400 uppercase tracking-tighter">Acústicas</p>
        </div>
    </div>

    <!-- 4. Estadísticas (2 Columnas Compactas) -->
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-50">
            <h3 class="text-xs font-black uppercase text-slate-400 tracking-[0.2em] mb-8 flex items-center gap-3">
                <span class="text-green-500 text-xl">🔥</span> Las Más Tocadas
            </h3>
            <div class="space-y-4">
                <?php foreach($mostPlayed as $song): ?>
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-700 truncate mr-2"><?php echo htmlspecialchars($song['title']); ?></span>
                    <span class="bg-green-50 text-green-600 px-2 py-0.5 rounded-md font-black text-[9px] whitespace-nowrap"><?php echo $song['total']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-50">
            <h3 class="text-xs font-black uppercase text-slate-400 tracking-[0.2em] mb-8 flex items-center gap-3">
                <span class="text-blue-400 text-xl">❄️</span> En el olvido / Nuevas
            </h3>
            <div class="space-y-4">
                <?php foreach($leastPlayed as $song): ?>
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-700 truncate mr-2"><?php echo htmlspecialchars($song['title']); ?></span>
                    <span class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md font-black text-[9px] whitespace-nowrap"><?php echo $song['total']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>