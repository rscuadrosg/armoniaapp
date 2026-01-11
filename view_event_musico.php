<?php
// 1. Configuración y Conexión
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_config.php';
require_once 'auth.php';

// 2. Validar ID del evento
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$event_id = $_GET['id'];

// 3. Obtener detalles del evento
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    die("El evento no existe.");
}

// 4. Obtener el Equipo y el Instrumento específico asignado
$members_stmt = $pdo->prepare("
    SELECT m.full_name, m.profile_photo, ea.instrument, ec.status 
    FROM members m
    JOIN event_assignments ea ON m.id = ea.member_id 
    LEFT JOIN event_confirmations ec ON (ea.event_id = ec.event_id AND ea.member_id = ec.member_id)
    WHERE ea.event_id = ?
    ORDER BY ea.id ASC
");
$members_stmt->execute([$event_id]);
$assigned_members = $members_stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Obtener las canciones del repertorio
$songs_stmt = $pdo->prepare("
    SELECT s.* 
    FROM songs s 
    JOIN event_songs es ON s.id = es.song_id 
    WHERE es.event_id = ?
    ORDER BY es.position ASC
");
$songs_stmt->execute([$event_id]);
$songs = $songs_stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; 
?>

<div class="max-w-md mx-auto p-4 pb-20">
    <header class="mb-6 text-center mt-4">
        <div class="inline-block bg-blue-600 text-white px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest mb-2 shadow-md shadow-blue-500/30">
            Resumen del Servicio
        </div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tighter leading-none mb-1 italic uppercase">
            <?php echo htmlspecialchars($event['description'] ?? 'Servicio'); ?>
        </h1>
        <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">
            <?php echo date('d M, Y', strtotime($event['event_date'])); ?>
        </p>

        <a href="live_view.php?id=<?php echo $event_id; ?>" class="inline-flex items-center gap-2 bg-slate-900 text-white px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest mt-4 shadow-lg shadow-slate-300 hover:bg-slate-800 transition-all">
            <span>⚡</span> Vista en Vivo
        </a>
    </header>

    <!-- 1. REPERTORIO (Prioridad Alta) -->
    <section class="mb-8">
        <h2 class="text-[10px] font-black uppercase text-slate-400 mb-4 ml-2 tracking-[0.2em] italic px-2">Repertorio Seleccionado</h2>
        <div class="space-y-2">
            <?php foreach($songs as $i => $s): ?>
                <div class="bg-white p-3 rounded-xl border border-slate-100 flex items-center gap-3 shadow-sm">
                    <!-- Number -->
                    <div class="text-slate-300 font-black text-sm w-6 text-center"><?php echo $i + 1; ?></div>
                    
                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-black text-slate-800 leading-tight mb-0.5 truncate"><?php echo htmlspecialchars($s['title']); ?></h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide truncate"><?php echo htmlspecialchars($s['artist']); ?></p>
                    </div>

                    <!-- Metadata & Resources -->
                    <div class="flex gap-2 shrink-0">
                        <div class="bg-slate-50 px-2 py-1 rounded-lg text-center min-w-[35px] border border-slate-100">
                            <span class="block text-[7px] font-bold text-slate-400 uppercase">Key</span>
                            <span class="block text-xs font-black text-blue-600 leading-none"><?php echo $s['musical_key']; ?></span>
                        </div>
                        <div class="bg-slate-50 px-2 py-1 rounded-lg text-center min-w-[35px] border border-slate-100">
                            <span class="block text-[7px] font-bold text-slate-400 uppercase">BPM</span>
                            <span class="block text-xs font-black text-slate-600 leading-none"><?php echo $s['bpm'] ?: '-'; ?></span>
                        </div>
                        <button onclick='openResources(<?php echo json_encode($s); ?>)' class="bg-blue-600 text-white w-9 h-9 rounded-lg flex items-center justify-center shadow-md active:scale-95 transition-transform">
                            📂
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if(empty($songs)): ?>
                <div class="text-center p-8 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    <p class="text-slate-300 font-black italic uppercase text-xs">El repertorio está vacío</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 2. EQUIPO (Lista 3 Columnas) -->
    <section class="mb-8">
        <div class="flex items-center justify-between mb-4 px-2">
            <h2 class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Equipo del Servicio</h2>
            <span class="bg-slate-100 text-slate-500 text-[9px] font-black px-2 py-1 rounded-md">
                <?php echo count($assigned_members); ?>
            </span>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <!-- Header Tabla -->
            <div class="grid grid-cols-12 bg-slate-50 p-3 text-[8px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                <div class="col-span-4">Rol</div>
                <div class="col-span-5">Músico</div>
                <div class="col-span-3 text-right">Estado</div>
            </div>
            
            <!-- Filas -->
            <div class="divide-y divide-slate-50">
                <?php foreach($assigned_members as $m): 
                    $status = $m['status'] ?? 'pendiente';
                    $s_color = 'bg-slate-100 text-slate-300';
                    $s_icon = '?';
                    
                    if($status === 'confirmado') { $s_color = 'bg-green-100 text-green-600'; $s_icon = '✓'; }
                    if($status === 'rechazado')  { $s_color = 'bg-red-100 text-red-500'; $s_icon = '✕'; }
                ?>
                <div class="grid grid-cols-12 p-3 items-center hover:bg-slate-50/50 transition-colors">
                    <!-- Col 1: Rol -->
                    <div class="col-span-4 pr-2">
                        <p class="text-[10px] font-black text-blue-600 uppercase tracking-tight truncate">
                            <?php echo htmlspecialchars($m['instrument'] ?: 'Asignado'); ?>
                        </p>
                    </div>
                    
                    <!-- Col 2: Músico -->
                    <div class="col-span-5 flex items-center gap-2 min-w-0">
                        <?php 
                        $foto_path = 'uploads/profile_pics/' . ($m['profile_photo'] ?? '');
                        if (!empty($m['profile_photo']) && file_exists($foto_path)): ?>
                            <img src="<?php echo $foto_path; ?>" class="w-6 h-6 rounded-md object-cover shadow-sm flex-shrink-0">
                        <?php else: ?>
                            <div class="w-6 h-6 bg-slate-900 rounded-md flex items-center justify-center text-white text-[8px] font-black italic flex-shrink-0">
                                <?php echo strtoupper(substr($m['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <p class="text-[10px] font-bold text-slate-700 truncate">
                            <?php echo htmlspecialchars($m['full_name']); ?>
                        </p>
                    </div>
                    
                    <!-- Col 3: Estado -->
                    <div class="col-span-3 flex justify-end">
                        <div class="w-6 h-6 rounded-md flex items-center justify-center <?php echo $s_color; ?> font-black text-[10px]" title="<?php echo ucfirst($status); ?>">
                            <?php echo $s_icon; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if(empty($assigned_members)): ?>
                    <div class="p-6 text-center">
                        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest italic">Sin asignaciones</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer class="mt-16 text-center opacity-30">
        <p class="text-[10px] font-black uppercase tracking-[0.5em] text-slate-500 italic">"La alabanza abre puertas"</p>
    </footer>
</div>

<!-- Modal Recursos (Igual que Live View) -->
<div id="resModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-end md:items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden animate-in slide-in-from-bottom duration-300">
        <div class="p-6">
            <h3 id="m_title" class="text-xl font-black text-slate-800 mb-1">Título</h3>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-6">Recursos Disponibles</p>
            
            <div id="m_links" class="grid gap-3"></div>
        </div>
        <div class="p-4 bg-slate-50 border-t border-slate-100">
            <button onclick="document.getElementById('resModal').classList.add('hidden')" class="w-full bg-slate-200 text-slate-600 py-3 rounded-xl font-black uppercase tracking-widest text-xs hover:bg-slate-300 transition-colors">Cerrar</button>
        </div>
    </div>
</div>

<script>
function openResources(song) {
    document.getElementById('m_title').innerText = song.title;
    const container = document.getElementById('m_links');
    container.innerHTML = '';

    const links = [
        { url: song.youtube_link, label: 'YouTube', icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>', color: 'bg-red-50 text-red-600 border-red-100' },
        { url: song.has_lyrics, label: 'PDF / Letra', icon: '📄', color: 'bg-blue-50 text-blue-600 border-blue-100' },
        { url: song.propresenter_path, label: 'ProPresenter', icon: '📺', color: 'bg-orange-50 text-orange-600 border-orange-100' },
        { url: song.midi_path, label: 'Secuencia MIDI', icon: '🎹', color: 'bg-indigo-50 text-indigo-600 border-indigo-100' }
    ];

    let hasLinks = false;
    links.forEach(l => {
        if(l.url && l.url.trim() !== '') {
            hasLinks = true;
            const a = document.createElement('a');
            a.href = l.url;
            a.target = '_blank';
            a.className = `flex items-center gap-3 p-3 rounded-xl border ${l.color} font-bold text-xs uppercase tracking-widest transition-transform active:scale-95`;
            a.innerHTML = `<span class="text-lg">${l.icon}</span> ${l.label}`;
            container.appendChild(a);
        }
    });

    if(!hasLinks) {
        container.innerHTML = '<div class="text-center text-slate-400 font-bold italic p-4 text-xs">No hay recursos vinculados</div>';
    }
    document.getElementById('resModal').classList.remove('hidden');
}
</script>

<?php 
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>