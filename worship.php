<?php
require_once 'db_config.php';
require_once 'auth.php';
include 'header.php';

if ($isAdmin) {
    // --- VISTA ADMIN: MÉTRICAS Y ESTADÍSTICAS ---
    $totalSongs = $pdo->query("SELECT COUNT(*) FROM songs")->fetchColumn();
    $noPdf = $pdo->query("SELECT COUNT(*) FROM songs WHERE has_lyrics IS NULL OR has_lyrics = '' OR has_lyrics = '0'")->fetchColumn();
    $noMultitrack = $pdo->query("SELECT COUNT(*) FROM songs WHERE has_multitrack = 0")->fetchColumn();

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

    $proximosServicios = $pdo->query("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

} else {
    // --- VISTA MÚSICO: MI AGENDA ---
    $my_id = $currentUserId;
    $showHistory = isset($_GET['history']) && $_GET['history'] == '1';
    $dateCondition = $showHistory ? "e.event_date < CURDATE()" : "e.event_date >= CURDATE()";
    $orderDirection = $showHistory ? "DESC" : "ASC";

    $my_events = $pdo->prepare("
        SELECT 
            e.id, 
            e.description,
            e.event_date, 
            ea.instrument,
            ec.status as confirmation_status
        FROM events e
        JOIN event_assignments ea ON e.id = ea.event_id
        LEFT JOIN event_confirmations ec ON (e.id = ec.event_id AND ec.member_id = ?)
        WHERE ea.member_id = ? AND $dateCondition
        ORDER BY e.event_date $orderDirection
    ");
    $my_events->execute([$my_id, $my_id]);
}
?>

<div class="container mx-auto px-4 max-w-7xl pb-20 pt-6">
    <header class="mb-8">
        <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase italic">
            <?php echo $isAdmin ? 'Panel de Alabanza' : 'Mi Agenda'; ?>
        </h1>
        <p class="text-slate-400 font-bold text-xs uppercase tracking-widest">Módulo de Gestión Musical</p>
    </header>

    <?php if ($isAdmin): ?>
    <!-- ================= VISTA ADMIN ================= -->

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
                <?php
                    $date_ts = strtotime($evento['event_date']);
                    $days_es = ['Sun'=>'DOM','Mon'=>'LUN','Tue'=>'MAR','Wed'=>'MIE','Thu'=>'JUE','Fri'=>'VIE','Sat'=>'SAB'];
                    $day_name = $days_es[date('D', $date_ts)];
                ?>
                <div class="p-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="bg-slate-100 text-slate-600 w-10 h-10 rounded-xl flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-[7px] font-black uppercase leading-none text-blue-500 mb-0.5"><?php echo $day_name; ?></span>
                            <span class="text-sm font-black leading-none"><?php echo date('d', $date_ts); ?></span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-black text-slate-800 text-xs uppercase truncate">
                                <?php echo htmlspecialchars($evento['description']); ?>
                            </h4>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">
                                <?php echo date('h:i A', $date_ts); ?> • <?php echo date('Y', $date_ts); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 flex-shrink-0">
                        <?php if ($isAdmin || $isLeader): ?>
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

    <?php else: ?>
    <!-- ================= VISTA MÚSICO ================= -->
    
    <div class="grid gap-4">
        <?php if($my_events->rowCount() == 0): ?>
            <div class="bg-white p-10 rounded-[2.5rem] text-center border-2 border-dashed border-slate-200">
                <p class="text-slate-400 font-bold italic">
                    <?php echo $showHistory ? 'No tienes servicios pasados.' : 'No tienes servicios asignados próximamente.'; ?>
                </p>
            </div>
        <?php endif; ?>

        <?php while($ev = $my_events->fetch(PDO::FETCH_ASSOC)): 
            $status = $ev['confirmation_status'];
        ?>
            <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm flex flex-col md:flex-row justify-between items-center gap-6 group hover:border-blue-200 transition-all">
                
                <div class="flex items-center gap-6 w-full md:w-auto">
                    <div class="bg-slate-900 text-white p-4 rounded-3xl text-center min-w-[75px] shadow-lg shadow-slate-200">
                        <span class="block text-[10px] font-black uppercase opacity-50"><?php echo date('M', strtotime($ev['event_date'])); ?></span>
                        <span class="text-2xl font-black leading-none"><?php echo date('d', strtotime($ev['event_date'])); ?></span>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-black text-slate-800 leading-tight mb-1">
                            <?php echo htmlspecialchars($ev['description']); ?>
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="bg-blue-100 text-blue-700 text-[9px] font-black px-2 py-0.5 rounded-md uppercase tracking-widest">
                                <?php echo htmlspecialchars($ev['instrument']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                    
                    <?php if($status == 'confirmado'): ?>
                        <div class="bg-green-50 text-green-600 px-5 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest border border-green-100">
                            ✓ Confirmado
                        </div>
                    <?php elseif($status == 'rechazado'): ?>
                        <div class="bg-red-50 text-red-600 px-5 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest border border-red-100">
                            No Asistiré
                        </div>
                    <?php else: ?>
                        <a href="process_confirmation.php?event_id=<?php echo $ev['id']; ?>&member_id=<?php echo $my_id; ?>&status=rechazado" 
                           class="bg-slate-50 text-slate-400 px-5 py-3 rounded-2xl font-black text-[10px] uppercase hover:bg-red-50 hover:text-red-500 transition border border-slate-100">
                            Declinar
                        </a>
                        <a href="process_confirmation.php?event_id=<?php echo $ev['id']; ?>&member_id=<?php echo $my_id; ?>&status=confirmado" 
                           class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase shadow-lg shadow-blue-100 hover:scale-105 transition tracking-widest">
                            Confirmar
                        </a>
                    <?php endif; ?>

                    <a href="view_event_musico.php?id=<?php echo $ev['id']; ?>" 
                       class="bg-slate-900 text-white w-12 h-12 flex items-center justify-center rounded-2xl hover:bg-blue-600 transition shadow-lg shadow-slate-200">
                        🎵
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>

<?php 
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>