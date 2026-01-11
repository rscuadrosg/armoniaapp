<?php
require_once 'db_config.php';
require_once 'auth.php';

// Validar ID
$event_id = $_GET['id'] ?? null;
if (!$event_id) {
    header("Location: index.php");
    exit;
}

// --- AJAX: Reordenamiento ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_action']) && $_POST['ajax_action'] == 'reorder' && $isAdmin) {
    $order = json_decode($_POST['order'], true);
    if (is_array($order)) {
        $stmt = $pdo->prepare("UPDATE event_songs SET position = ? WHERE event_id = ? AND song_id = ?");
        foreach ($order as $position => $song_id) {
            $stmt->execute([$position + 1, $event_id, $song_id]);
        }
    }
    echo "OK";
    exit;
}

// Obtener Evento
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) die("Evento no encontrado");

// Obtener Canciones (Ordenadas por posición)
$songs_stmt = $pdo->prepare("
    SELECT s.* 
    FROM songs s 
    JOIN event_songs es ON s.id = es.song_id 
    WHERE es.event_id = ?
    ORDER BY es.position ASC
");
$songs_stmt->execute([$event_id]);
$songs = $songs_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Live View - <?php echo htmlspecialchars($event['description']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0f172a; color: white; }
        .drag-handle { touch-action: none; } /* Importante para móviles */
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Header Flotante -->
    <header class="bg-slate-900/90 backdrop-blur-md p-4 sticky top-0 z-50 border-b border-slate-800 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black uppercase italic tracking-tighter leading-none text-white">
                <?php echo htmlspecialchars($event['description']); ?>
            </h1>
            <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mt-1">
                Vista en Vivo • <span id="wakeStatus">Pantalla Activa</span>
            </p>
        </div>
        <a href="view_event_musico.php?id=<?php echo $event_id; ?>" class="bg-slate-800 text-slate-400 px-4 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-700">
            Salir
        </a>
    </header>

    <!-- Lista de Canciones -->
    <main class="flex-1 p-4 pb-20">
        <div id="setlist" class="space-y-2">
            <?php foreach($songs as $s): ?>
                <div class="song-item bg-slate-800 p-3 rounded-xl border border-slate-700 flex items-center gap-3 select-none" data-id="<?php echo $s['id']; ?>">
                    
                    <!-- Columna Izquierda: Handle/Numero -->
                    <div class="flex flex-col justify-center">
                        <?php if($isAdmin): ?>
                            <div class="drag-handle text-slate-600 cursor-grab active:cursor-grabbing p-1">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                            </div>
                        <?php else: ?>
                            <div class="text-slate-600 font-black text-lg w-6 text-center">
                                <?php echo array_search($s, $songs) + 1; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Centro: Título y Artista -->
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg md:text-xl font-black text-white leading-tight mb-0.5 break-words"><?php echo htmlspecialchars($s['title']); ?></h2>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wide truncate"><?php echo htmlspecialchars($s['artist']); ?></p>
                    </div>

                    <!-- Derecha: Key y BPM -->
                    <div class="flex gap-2 shrink-0">
                        <div class="bg-slate-700 px-2 py-1 rounded-lg text-center min-w-[45px]">
                            <span class="block text-[8px] font-bold text-slate-400 uppercase">Key</span>
                            <span class="block text-xl font-black text-white leading-none"><?php echo $s['musical_key']; ?></span>
                        </div>
                        <div class="bg-slate-700 px-2 py-1 rounded-lg text-center min-w-[45px]">
                            <span class="block text-[8px] font-bold text-slate-400 uppercase">BPM</span>
                            <span class="block text-xl font-black text-slate-300 leading-none"><?php echo $s['bpm'] ?: '-'; ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Modal Recursos -->
    <div id="resModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-[60] flex items-end md:items-center justify-center p-4">
        <div class="bg-slate-900 w-full max-w-sm rounded-3xl border border-slate-700 shadow-2xl overflow-hidden animate-in slide-in-from-bottom duration-300">
            <div class="p-6">
                <h3 id="m_title" class="text-2xl font-black text-white mb-1">Título</h3>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-6">Recursos Disponibles</p>
                
                <div id="m_links" class="grid gap-3"></div>
            </div>
            <div class="p-4 bg-slate-800 border-t border-slate-700">
                <button onclick="document.getElementById('resModal').classList.add('hidden')" class="w-full bg-slate-700 text-white py-4 rounded-xl font-black uppercase tracking-widest">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        // 1. Wake Lock (Mantener pantalla encendida)
        let wakeLock = null;
        const requestWakeLock = async () => {
            try {
                if ('wakeLock' in navigator) {
                    wakeLock = await navigator.wakeLock.request('screen');
                    document.getElementById('wakeStatus').innerText = "Pantalla Activa ⚡";
                    document.getElementById('wakeStatus').classList.add('text-green-400');
                }
            } catch (err) {
                console.log(`${err.name}, ${err.message}`);
                document.getElementById('wakeStatus').innerText = "Modo Normal";
            }
        };
        
        // Reactivar si se minimiza y vuelve
        document.addEventListener('visibilitychange', async () => {
            if (wakeLock !== null && document.visibilityState === 'visible') {
                requestWakeLock();
            }
        });
        requestWakeLock();

        // 2. Drag & Drop (Solo si es Admin)
        <?php if($isAdmin): ?>
        const el = document.getElementById('setlist');
        Sortable.create(el, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'opacity-50',
            onEnd: function (evt) {
                saveOrder();
            }
        });

        function saveOrder() {
            const items = document.querySelectorAll('.song-item');
            const order = Array.from(items).map(item => item.dataset.id);
            
            const formData = new FormData();
            formData.append('ajax_action', 'reorder');
            formData.append('order', JSON.stringify(order));

            fetch('live_view.php?id=<?php echo $event_id; ?>', {
                method: 'POST',
                body: formData
            }).then(res => {
                if(res.ok) console.log('Orden guardado');
            });
        }
        <?php endif; ?>

        // 3. Modal Recursos
        function openResources(song) {
            document.getElementById('m_title').innerText = song.title;
            const container = document.getElementById('m_links');
            container.innerHTML = '';

            const links = [
                { url: song.youtube_link, label: 'YouTube', icon: '▶', color: 'bg-red-600' },
                { url: song.has_lyrics, label: 'PDF / Letra', icon: '📄', color: 'bg-blue-600' },
                { url: song.propresenter_path, label: 'ProPresenter', icon: '📺', color: 'bg-orange-600' },
                { url: song.midi_path, label: 'Secuencia MIDI', icon: '🎹', color: 'bg-indigo-600' }
            ];

            let hasLinks = false;
            links.forEach(l => {
                if(l.url && l.url.trim() !== '') {
                    hasLinks = true;
                    const a = document.createElement('a');
                    a.href = l.url;
                    a.target = '_blank';
                    a.className = `flex items-center gap-4 p-4 rounded-2xl ${l.color} text-white font-bold uppercase tracking-widest shadow-lg`;
                    a.innerHTML = `<span class="text-2xl">${l.icon}</span> ${l.label}`;
                    container.appendChild(a);
                }
            });

            if(!hasLinks) {
                container.innerHTML = '<div class="text-center text-slate-500 font-bold italic p-4">No hay recursos vinculados</div>';
            }

            document.getElementById('resModal').classList.remove('hidden');
        }
    </script>
</body>
</html>