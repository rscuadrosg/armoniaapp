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
    SELECT s.title, s.musical_key, s.youtube_link 
    FROM songs s 
    JOIN event_songs es ON s.id = es.song_id 
    WHERE es.event_id = ?
");
$songs_stmt->execute([$event_id]);
$songs = $songs_stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; 
?>

<div class="max-w-md mx-auto p-4 pb-20">
    <header class="mb-10 text-center mt-6">
        <div class="inline-block bg-blue-600 text-white px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest mb-4 shadow-lg shadow-blue-500/30">
            Resumen del Servicio
        </div>
        <h1 class="text-4xl font-black text-slate-900 tracking-tighter leading-none mb-2 italic uppercase">
            <?php echo htmlspecialchars($event['description'] ?? 'Servicio'); ?>
        </h1>
        <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">
            <?php echo date('d M, Y', strtotime($event['event_date'])); ?>
        </p>
    </header>

    <section class="mb-12">
        <div class="flex items-center justify-between mb-4 px-2">
            <h2 class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Equipo del Servicio</h2>
            <span class="bg-slate-100 text-slate-500 text-[9px] font-black px-2 py-1 rounded-md">
                <?php echo count($assigned_members); ?> INTEGRANTES
            </span>
        </div>
        
        <div class="grid grid-cols-1 gap-3">
            <?php foreach($assigned_members as $m): ?>
                <?php 
                    // Lógica de estado visual
                    $status = $m['status'] ?? 'pendiente';
                    $s_color = 'bg-slate-50 text-slate-300 border-slate-100';
                    $s_icon = '?';
                    
                    if($status === 'confirmado') { $s_color = 'bg-green-50 text-green-600 border-green-100'; $s_icon = '✓'; }
                    if($status === 'rechazado')  { $s_color = 'bg-red-50 text-red-500 border-red-100'; $s_icon = '✕'; }
                ?>
                <div class="flex items-center justify-between bg-white p-3 rounded-3xl shadow-sm border border-slate-100 hover:border-blue-100 transition-all">
                    <div class="flex items-center gap-4">
                    <?php 
                    $foto_path = 'uploads/profile_pics/' . ($m['profile_photo'] ?? '');
                    if (!empty($m['profile_photo']) && file_exists($foto_path)): ?>
                        <img src="<?php echo $foto_path; ?>" class="w-10 h-10 rounded-2xl object-cover shadow-sm">
                    <?php else: ?>
                        <div class="w-10 h-10 bg-slate-900 rounded-2xl flex items-center justify-center text-white text-xs font-black italic">
                            <?php echo strtoupper(substr($m['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>

                    <div>
                        <p class="text-sm font-black text-slate-800 leading-tight uppercase italic tracking-tight">
                            <?php echo htmlspecialchars($m['full_name']); ?>
                        </p>
                        <p class="text-[9px] font-bold text-blue-600 uppercase tracking-widest mt-0.5">
                            <?php echo htmlspecialchars($m['instrument'] ?: 'Asignado'); ?>
                        </p>
                    </div>
                    </div>
                    
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center border <?php echo $s_color; ?> font-black text-sm" title="<?php echo ucfirst($status); ?>">
                        <?php echo $s_icon; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if(empty($assigned_members)): ?>
                <div class="text-center p-8 bg-slate-50 rounded-[2rem] border border-dashed border-slate-200">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">No hay músicos asignados</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section>
        <h2 class="text-[10px] font-black uppercase text-slate-400 mb-4 ml-2 tracking-[0.2em] italic px-2">Repertorio Seleccionado</h2>
        <div class="space-y-4">
            <?php foreach($songs as $s): ?>
                <div class="bg-white border border-slate-100 p-6 rounded-[2.5rem] shadow-xl shadow-slate-200/50 group hover:scale-[1.02] transition-transform">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-2xl font-black text-slate-800 leading-tight mb-1 uppercase italic tracking-tighter">
                                <?php echo htmlspecialchars($s['title']); ?>
                            </h3>
                            <div class="flex items-center gap-2">
                                <span class="bg-blue-50 text-blue-600 font-black text-[10px] tracking-widest px-2 py-0.5 rounded-lg">
                                    Tono: <?php echo $s['musical_key']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-xl shadow-inner group-hover:bg-blue-50 transition-colors">
                            🎵
                        </div>
                    </div>

                    <?php if(!empty($s['youtube_link'])): ?>
                        <a href="<?php echo $s['youtube_link']; ?>" target="_blank" 
                           class="flex items-center justify-center gap-3 w-full bg-[#FF0000] hover:bg-[#CC0000] text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all active:scale-95 shadow-lg shadow-red-100">
                            <span class="text-lg">▶</span> Escuchar en YouTube
                        </a>
                    <?php else: ?>
                        <div class="text-center p-4 bg-slate-50 rounded-2xl text-slate-300 text-[9px] font-black uppercase tracking-widest border border-dashed border-slate-200">
                            Link no disponible
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <?php if(empty($songs)): ?>
                <div class="text-center p-12">
                    <p class="text-slate-300 font-black italic uppercase text-xs">El repertorio está vacío</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="mt-16 text-center opacity-30">
        <p class="text-[10px] font-black uppercase tracking-[0.5em] text-slate-500 italic">"La alabanza abre puertas"</p>
    </footer>
</div>

<?php 
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>