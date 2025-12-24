<?php
require_once 'db_config.php';
$event_id = $_GET['id'];

// Obtener detalles del evento
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

// Obtener las canciones con sus detalles y links
$songs_stmt = $pdo->prepare("
    SELECT s.title, s.musical_key, s.youtube_link 
    FROM songs s 
    JOIN event_songs es ON s.id = es.song_id 
    WHERE es.event_id = ?
");
$songs_stmt->execute([$event_id]);

include 'header.php'; // Usamos el header para mantener el estilo
?>

<div class="max-w-md mx-auto p-4 pb-20">
    <header class="mb-10 text-center">
        <div class="inline-block bg-blue-600 text-white px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest mb-4 shadow-lg shadow-blue-500/30">
            Setlist de Ensayo
        </div>
        <h1 class="text-4xl font-black text-slate-900 tracking-tighter leading-none mb-2">
            <?php echo htmlspecialchars($event['event_title']); ?>
        </h1>
        <p class="text-slate-400 font-bold"><?php echo date('d M, Y', strtotime($event['event_date'])); ?></p>
    </header>

    <div class="space-y-6">
        <?php while($s = $songs_stmt->fetch()): ?>
            <div class="bg-white border border-slate-100 p-6 rounded-[2.5rem] shadow-xl shadow-slate-200/50 group">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800 leading-tight mb-1">
                            <?php echo htmlspecialchars($s['title']); ?>
                        </h3>
                        <span class="text-blue-600 font-black text-xs uppercase tracking-widest">Tono: <?php echo $s['musical_key']; ?></span>
                    </div>
                    <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                        🎵
                    </div>
                </div>

                <?php if(!empty($s['youtube_link'])): ?>
                    <a href="<?php echo $s['youtube_link']; ?>" target="_blank" 
                       class="flex items-center justify-center gap-3 w-full bg-[#FF0000] hover:bg-[#CC0000] text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-transform active:scale-95 shadow-lg shadow-red-200">
                        <span class="text-lg">▶</span> Escuchar en YouTube
                    </a>
                <?php else: ?>
                    <div class="text-center p-4 bg-slate-50 rounded-2xl text-slate-400 text-[10px] font-black uppercase tracking-widest border border-dashed border-slate-200">
                        Link no disponible
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

    <footer class="mt-12 text-center opacity-30">
        <p class="text-[10px] font-black uppercase tracking-[0.5em] text-slate-500 italic">"La alabanza abre puertas"</p>
    </footer>
</div>

</body>
</html>