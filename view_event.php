<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_config.php';

// --- 1. CONFIGURACIÓN INICIAL ---
require_once 'auth.php';

$event_id = $_GET['id'] ?? null;
if (!$event_id) {
    echo "<script>window.location.href='events.php';</script>";
    exit;
}

// SEGURIDAD: Redirigir si no es admin ANTES de procesar cualquier cambio
if (!$isAdmin) {
    echo "<script>window.location.href='view_event_musico.php?id=$event_id';</script>";
    exit;
}

// --- 2. LÓGICA DE PROCESAMIENTO (Antes del Header para mayor limpieza) ---

// Agregar/Actualizar Músico
if (isset($_POST['add_member'])) {
    $m_id = $_POST['member_id'];
    $inst = $_POST['instrument'];
    
    if (!empty($m_id)) {
        $stmt = $pdo->prepare("INSERT INTO event_assignments (event_id, member_id, instrument) 
                               VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE member_id = VALUES(member_id)");
        $stmt->execute([$event_id, $m_id, $inst]);
    }
    echo "<script>window.location.href='view_event.php?id=$event_id';</script>";
    exit;
}

// Agregar Canción
if (isset($_POST['add_song']) && !empty($_POST['song_id'])) {
    $check = $pdo->prepare("SELECT id FROM event_songs WHERE event_id = ? AND song_id = ?");
    $check->execute([$event_id, $_POST['song_id']]);
    if ($check->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO event_songs (event_id, song_id) VALUES (?, ?)");
        $stmt->execute([$event_id, $_POST['song_id']]);
    }
    echo "<script>window.location.href='view_event.php?id=$event_id';</script>";
    exit;
}

// Borrados
if (isset($_GET['del_song'])) {
    $pdo->prepare("DELETE FROM event_songs WHERE event_id = ? AND song_id = ?")->execute([$event_id, $_GET['del_song']]);
    echo "<script>window.location.href='view_event.php?id=$event_id';</script>";
    exit;
}
if (isset($_GET['del_assignment'])) {
    $pdo->prepare("DELETE FROM event_assignments WHERE id = ?")->execute([$_GET['del_assignment']]);
    echo "<script>window.location.href='view_event.php?id=$event_id';</script>";
    exit;
}

// --- GENERADOR AUTOMÁTICO (MAGIC FILL) ---
if (isset($_POST['auto_generate_setlist'])) {
    $selected_tag_ids = $_POST['tags_selected'] ?? [];
    $tag_counts = $_POST['tag_counts'] ?? [];
    
    // Filtrar configuración
    $config_tags = [];
    foreach($selected_tag_ids as $tid) {
        $qty = (int)($tag_counts[$tid] ?? 0);
        if($qty > 0) $config_tags[$tid] = $qty;
    }
    
    if (!empty($config_tags)) {
        // 1. Limpiar canciones actuales
        $pdo->prepare("DELETE FROM event_songs WHERE event_id = ?")->execute([$event_id]);
        
        // 2. Obtener Pool de canciones (Smart Shuffle Logic)
        $inQuery = implode(',', array_fill(0, count($config_tags), '?'));
        $params = array_keys($config_tags);
        
        $sql = "SELECT s.id, MAX(e.event_date) as last_played, GROUP_CONCAT(st.tag_id) as tag_ids 
                FROM songs s 
                JOIN song_tags st ON s.id = st.song_id
                LEFT JOIN event_songs es ON s.id = es.song_id 
                LEFT JOIN events e ON es.event_id = e.id 
                WHERE st.tag_id IN ($inQuery)
                GROUP BY s.id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $raw_songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar en PHP para evitar duplicados y respetar cantidades
        $current_event_song_ids = [];
        $position = 1;
        $stmt_ins = $pdo->prepare("INSERT INTO event_songs (event_id, song_id, position) VALUES (?, ?, ?)");
        
        foreach($config_tags as $tid => $qty) {
            $candidates = [];
            foreach($raw_songs as $r) {
                $song_tags = explode(',', $r['tag_ids']);
                if(in_array($tid, $song_tags) && !in_array($r['id'], $current_event_song_ids)) {
                    $r['ts'] = $r['last_played'] ? strtotime($r['last_played']) : 0;
                    $candidates[] = $r;
                }
            }
            
            shuffle($candidates);
            usort($candidates, function($a, $b) { return $a['ts'] <=> $b['ts']; });
            
            for($k=0; $k<$qty; $k++) {
                if(isset($candidates[$k])) {
                    $stmt_ins->execute([$event_id, $candidates[$k]['id'], $position++]);
                    $current_event_song_ids[] = $candidates[$k]['id'];
                }
            }
        }
    }
    
    echo "<script>window.location.href='view_event.php?id=$event_id';</script>";
    exit;
}

// --- 3. INCLUSIÓN DE INTERFAZ ---
include 'header.php'; // Define $isAdmin [cite: 2025-12-21]

// --- 4. CONSULTAS PARA LA VISTA ---
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

$all_songs = $pdo->prepare("
    SELECT id, title FROM songs 
    WHERE id NOT IN (SELECT song_id FROM event_songs WHERE event_id = ?)
    ORDER BY title ASC
");
$all_songs->execute([$event_id]);
$all_songs = $all_songs->fetchAll();
$all_members = $pdo->query("SELECT id, full_name FROM members ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$template_roles = $pdo->query("SELECT * FROM band_roles ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

$current_songs = $pdo->prepare("SELECT s.id, s.title, s.musical_key FROM songs s JOIN event_songs es ON s.id = es.song_id WHERE es.event_id = ?");
$current_songs->execute([$event_id]);

$tags = $pdo->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$assigned_stmt = $pdo->prepare("
    SELECT ea.id as assign_id, ea.instrument, m.full_name, m.id as member_id, ec.status as confirmation_status
    FROM event_assignments ea 
    JOIN members m ON ea.member_id = m.id 
    LEFT JOIN event_confirmations ec ON (ea.event_id = ec.event_id AND ea.member_id = ec.member_id)
    WHERE ea.event_id = ?
");
$assigned_stmt->execute([$event_id]);
$assignments = [];
while($row = $assigned_stmt->fetch(PDO::FETCH_ASSOC)) {
    $assignments[$row['instrument']] = $row;
}
?>

<div class="container mx-auto px-4 max-w-6xl pb-20">
    <header class="mb-12 mt-10">
        <div class="inline-block bg-blue-100 text-blue-700 px-4 py-1 rounded-full text-xs font-black uppercase mb-4 tracking-widest">Panel Administrativo</div>
        <h1 class="text-5xl md:text-6xl font-extrabold text-slate-900 tracking-tighter leading-none mb-2">
            <?php echo htmlspecialchars($event['description'] ?? ($event['event_title'] ?? 'Servicio')); ?>
        </h1>
        <p class="text-xl text-slate-400 font-semibold"><?php echo date('d \d\e F, Y', strtotime($event['event_date'])); ?></p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <section>
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight italic uppercase">Setlist Musical</h2>
                <button onclick="document.getElementById('magicModal').classList.remove('hidden')" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">✨ Auto-Generar</button>
            </div>
            <form method="POST" class="flex gap-3 mb-8 bg-white p-3 rounded-[2rem] shadow-sm border border-slate-100">
                <select name="song_id" class="flex-1 bg-transparent px-4 font-bold text-slate-600 outline-none text-sm cursor-pointer">
                    <option value="">Buscar canción...</option>
                    <?php foreach($all_songs as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['title']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button name="add_song" class="bg-blue-600 hover:bg-blue-700 text-white w-12 h-12 rounded-2xl flex items-center justify-center font-black transition-all shadow-lg shadow-blue-100">+</button>
            </form>

            <div class="space-y-4">
                <?php while($s = $current_songs->fetch()): ?>
                    <div class="flex justify-between items-center p-5 bg-white border border-slate-100 rounded-[2rem] shadow-sm group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 font-black text-[10px]"><?php echo $s['musical_key']; ?></div>
                            <span class="font-bold text-slate-700 text-lg uppercase tracking-tight"><?php echo htmlspecialchars($s['title']); ?></span>
                        </div>
                        <a href="?id=<?php echo $event_id; ?>&del_song=<?php echo $s['id']; ?>" class="w-8 h-8 flex items-center justify-center text-slate-200 hover:text-red-500 rounded-full transition-all">✕</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight mb-8 italic uppercase">Equipo y Estados</h2>
            <div class="space-y-4">
                <?php foreach($template_roles as $role): 
                    $r_name = $role['role_name'];
                    $info = $assignments[$r_name] ?? null;
                    
                    $dot_color = 'bg-slate-300'; 
                    $text_status = 'Pendiente';
                    if ($info) {
                        if ($info['confirmation_status'] == 'confirmado') { $dot_color = 'bg-green-500'; $text_status = 'Confirmado'; }
                        elseif ($info['confirmation_status'] == 'rechazado') { $dot_color = 'bg-red-500'; $text_status = 'No asiste'; }
                    }
                ?>
                    <div class="flex justify-between items-center p-4 <?php echo $info ? 'bg-white shadow-sm' : 'bg-slate-50 border-dashed'; ?> border border-slate-200 rounded-[2rem] relative group transition-all">
                        
                        <?php if($info): ?>
                            <div class="absolute top-4 left-4 w-4 h-4 <?php echo $dot_color; ?> rounded-full border-4 border-white z-10 shadow-sm" title="<?php echo $text_status; ?>"></div>
                        <?php endif; ?>

                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-[1.4rem] flex items-center justify-center text-white font-black shadow-lg <?php echo $info ? 'bg-gradient-to-br from-blue-600 to-indigo-700 shadow-blue-100' : 'bg-slate-200 text-slate-400 shadow-none'; ?>">
                                <?php 
                                if ($info) {
                                    $words = explode(" ", $info['full_name']);
                                    echo strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ""));
                                } else { echo "?"; }
                                ?>
                            </div>

                            <div class="flex flex-col">
                                <span class="text-[10px] font-black uppercase text-blue-500 tracking-widest mb-1"><?php echo htmlspecialchars($r_name); ?></span>
                                <span class="font-bold text-lg leading-tight <?php echo $info ? 'text-slate-800' : 'text-slate-300'; ?>">
                                    <?php echo $info ? htmlspecialchars($info['full_name']) : 'Vacante'; ?>
                                </span>
                            </div>
                        </div>

                        <form method="POST" class="flex gap-2">
                            <input type="hidden" name="instrument" value="<?php echo htmlspecialchars($r_name); ?>">
                            <select name="member_id" onchange="this.form.submit()" class="text-[10px] font-bold p-2 rounded-xl border-none bg-slate-100 text-slate-500 outline-none cursor-pointer">
                                <option value="">Asignar...</option>
                                <?php foreach($all_members as $m): ?>
                                    <option value="<?php echo $m['id']; ?>" <?php echo ($info && $info['member_id'] == $m['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($m['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="add_member" value="1">
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-8 p-6 bg-slate-900 rounded-[2.5rem] text-white shadow-2xl shadow-slate-300 border border-white/5">
                <form method="POST" class="flex flex-col gap-3">
                    <div class="flex gap-2">
                        <input type="text" name="instrument" placeholder="Instrumento Extra" class="flex-1 bg-white/10 p-4 rounded-xl text-xs border border-white/10 outline-none focus:ring-2 focus:ring-blue-500" required>
                        <select name="member_id" class="flex-1 bg-white/10 p-4 rounded-xl text-xs border border-white/10 outline-none cursor-pointer focus:ring-2 focus:ring-blue-500" required>
                            <option value="" class="text-slate-500">¿Quién?</option>
                            <?php foreach($all_members as $m): ?>
                                <option value="<?php echo $m['id']; ?>" style="color: #0f172a; background-color: white;">
                                    <?php echo htmlspecialchars($m['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button name="add_member" class="bg-blue-600 hover:bg-blue-500 px-6 rounded-xl font-black text-[10px] uppercase transition-all shadow-lg shadow-blue-900/20">OK</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<!-- Modal Generador Mágico -->
<div id="magicModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] w-full max-w-md shadow-2xl p-8">
        <h3 class="text-2xl font-black text-slate-800 uppercase italic mb-2">Generador Automático</h3>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Reemplazará el setlist actual</p>
        
        <form method="POST">
            <div class="mb-8">
                <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block tracking-widest">Estructura del Setlist</label>
                <div class="grid grid-cols-1 gap-2 bg-slate-50 p-4 rounded-2xl border border-slate-100 max-h-60 overflow-y-auto">
                    <?php foreach($tags as $t): ?>
                        <div class="flex items-center justify-between p-2 border border-slate-200 rounded-xl bg-white">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="tags_selected[]" value="<?php echo $t['id']; ?>" class="w-4 h-4 accent-blue-600 rounded" onchange="toggleTagCount(this, <?php echo $t['id']; ?>)">
                                <span class="text-[10px] font-black uppercase <?php echo $t['color_class']; ?> px-2 py-0.5 rounded"><?php echo $t['name']; ?></span>
                            </label>
                            <input type="number" name="tag_counts[<?php echo $t['id']; ?>]" id="count_<?php echo $t['id']; ?>" value="0" min="1" max="10" class="w-12 p-1 bg-slate-50 rounded-lg text-center font-bold text-xs outline-none border border-slate-100 focus:border-blue-500 disabled:opacity-30" disabled>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('magicModal').classList.add('hidden')" class="flex-1 py-4 rounded-2xl font-black uppercase text-xs text-slate-400 hover:bg-slate-50">Cancelar</button>
                <button type="submit" name="auto_generate_setlist" class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-indigo-700 shadow-lg shadow-indigo-200">Generar</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleTagCount(cb, id) {
    const input = document.getElementById('count_' + id);
    input.disabled = !cb.checked;
    if(cb.checked) { input.value = 1; input.focus(); } else { input.value = 0; }
}
</script>

<footer class="text-center py-10 text-slate-300 text-[10px] font-black uppercase tracking-[0.4em]">
    ArmoníaApp • Panel de Control
</footer>

</body>
</html>