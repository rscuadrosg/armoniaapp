<?php
require_once 'db_config.php';
require_once 'auth.php';

$message = ""; 
$error = "";

// --- 2. PROCESAMIENTO DE DATOS (Solo si es Admin) ---
if ($isAdmin) {
    // Eliminar canción
    if (isset($_GET['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM songs WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Canción eliminada.";
    }

    // Guardar o Editar canción
    if (isset($_POST['save_song'])) {
        $has_multitrack = isset($_POST['has_multitrack']) ? 1 : 0;
        $new_id = $_POST['song_id_manual']; 
        $old_id = $_POST['old_id']; 

        try {
            if (!empty($old_id)) {
                $stmt = $pdo->prepare("UPDATE songs SET id=?, title=?, artist=?, musical_key=?, youtube_link=?, bpm=?, has_multitrack=?, has_lyrics=?, midi_path=?, propresenter_path=? WHERE id=?");
                $stmt->execute([$new_id, $_POST['title'], $_POST['artist'], $_POST['musical_key'], $_POST['youtube_link'], $_POST['bpm'], $has_multitrack, $_POST['has_lyrics'], $_POST['midi_path'], $_POST['propresenter_path'], $old_id]);
                
                // Actualizar Etiquetas: Borrar anteriores e insertar nuevas
                $pdo->prepare("DELETE FROM song_tags WHERE song_id = ?")->execute([$new_id]);
                $message = "Actualizado con éxito.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO songs (id, title, artist, musical_key, youtube_link, bpm, has_multitrack, has_lyrics, midi_path, propresenter_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$new_id, $_POST['title'], $_POST['artist'], $_POST['musical_key'], $_POST['youtube_link'], $_POST['bpm'], $has_multitrack, $_POST['has_lyrics'], $_POST['midi_path'], $_POST['propresenter_path']]);
                $message = "Añadida con éxito.";
            }

            // Insertar nuevas etiquetas seleccionadas
            if (isset($_POST['tags']) && is_array($_POST['tags'])) {
                $stmt_tag = $pdo->prepare("INSERT INTO song_tags (song_id, tag_id) VALUES (?, ?)");
                foreach ($_POST['tags'] as $tag_id) {
                    $stmt_tag->execute([$new_id, $tag_id]);
                }
            }

        } catch (PDOException $e) {
            $error = ($e->getCode() == 23000) ? "El ID ya existe." : "Error: " . $e->getMessage();
        }
    }
}

// --- 3. CONSULTAS DASHBOARD ---
$totalSongs = $pdo->query("SELECT COUNT(*) FROM songs")->fetchColumn();
$withMultitracks = $pdo->query("SELECT COUNT(*) FROM songs WHERE has_multitrack = 1")->fetchColumn();
$withMidis = $pdo->query("SELECT COUNT(*) FROM songs WHERE midi_path IS NOT NULL AND midi_path != ''")->fetchColumn();
$withPro = $pdo->query("SELECT COUNT(*) FROM songs WHERE propresenter_path IS NOT NULL AND propresenter_path != ''")->fetchColumn();
$noYoutube = $pdo->query("SELECT COUNT(*) FROM songs WHERE youtube_link IS NULL OR TRIM(youtube_link) = ''")->fetchColumn();
$noLyrics = $pdo->query("SELECT COUNT(*) FROM songs WHERE has_lyrics IS NULL OR TRIM(has_lyrics) = ''")->fetchColumn();

// Obtener todas las etiquetas disponibles
$all_tags = $pdo->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Contar canciones por etiqueta
$tagCounts = $pdo->query("SELECT t.id, t.name, t.color_class, COUNT(st.song_id) as total 
                          FROM tags t 
                          LEFT JOIN song_tags st ON t.id = st.tag_id 
                          GROUP BY t.id")->fetchAll(PDO::FETCH_ASSOC);

// --- LÓGICA DE ORDENAMIENTO ---
$sort = $_GET['sort'] ?? 'artist';
$order = $_GET['order'] ?? 'ASC';

$valid_columns = ['id', 'title', 'artist', 'musical_key', 'bpm'];
if (!in_array($sort, $valid_columns)) $sort = 'artist';
$order = (strtoupper($order) === 'DESC') ? 'DESC' : 'ASC';

// Helper para iconos de ordenamiento
function getSortIcon($col, $current_sort, $current_order) {
    if ($col !== $current_sort) return '<span class="text-slate-200 text-[9px] ml-1">▼</span>';
    return ($current_order === 'ASC') 
        ? '<span class="text-blue-600 text-[9px] ml-1">▲</span>' 
        : '<span class="text-blue-600 text-[9px] ml-1">▼</span>';
}

// Obtener canciones con sus etiquetas concatenadas (QUERY DINÁMICA)
$sql = "
    SELECT s.*, GROUP_CONCAT(t.id) as tag_ids, GROUP_CONCAT(t.name SEPARATOR '||') as tag_names, GROUP_CONCAT(t.color_class SEPARATOR '||') as tag_colors
    FROM songs s
    LEFT JOIN song_tags st ON s.id = st.song_id
    LEFT JOIN tags t ON st.tag_id = t.id
    GROUP BY s.id
    ORDER BY s.$sort $order
";
// Orden secundario para estabilidad
if ($sort !== 'title') $sql .= ", s.title ASC";

$songs = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; 
?>

<div class="container mx-auto px-4 max-w-7xl pb-20">
    
    <div class="grid grid-cols-3 md:grid-cols-6 gap-2 mb-4 text-center">
        <div class="bg-white p-2 rounded-xl shadow-sm border border-slate-100">
            <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest truncate">Total</p>
            <h3 class="text-lg font-black text-blue-600 leading-tight"><?php echo $totalSongs; ?></h3>
        </div>
        <div class="bg-white p-2 rounded-xl shadow-sm border border-slate-100">
            <p class="text-[7px] font-black text-green-500 uppercase tracking-widest truncate">Multi</p>
            <h3 class="text-lg font-black text-slate-800 leading-tight"><?php echo $withMultitracks; ?></h3>
        </div>
        <div class="bg-white p-2 rounded-xl shadow-sm border border-slate-100">
            <p class="text-[7px] font-black text-indigo-500 uppercase tracking-widest truncate">Midi</p>
            <h3 class="text-lg font-black text-slate-800 leading-tight"><?php echo $withMidis; ?></h3>
        </div>
        <div class="bg-white p-2 rounded-xl shadow-sm border border-slate-100">
            <p class="text-[7px] font-black text-orange-500 uppercase tracking-widest truncate">Pro</p>
            <h3 class="text-lg font-black text-slate-800 leading-tight"><?php echo $withPro; ?></h3>
        </div>
        <div class="bg-white p-2 rounded-xl shadow-sm border-l-2 border-red-500">
            <p class="text-[7px] font-black text-red-500 uppercase tracking-widest truncate">No YT</p>
            <h3 class="text-lg font-black text-slate-800 leading-tight"><?php echo $noYoutube; ?></h3>
        </div>
        <div class="bg-white p-2 rounded-xl shadow-sm border-l-2 border-orange-500">
            <p class="text-[7px] font-black text-orange-500 uppercase tracking-widest truncate">No PDF</p>
            <h3 class="text-lg font-black text-slate-800 leading-tight"><?php echo $noLyrics; ?></h3>
        </div>
    </div>

    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 mb-6">
        <div class="flex flex-col gap-4 mb-4">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <!-- Search -->
            <div class="relative w-full md:flex-1">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
                <input type="text" id="songSearch" placeholder="Buscar canción o artista..." class="w-full pl-10 p-3 bg-slate-50 border-none rounded-xl font-bold text-slate-600 outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Resource Filter -->
            <select id="resourceFilter" class="w-full md:w-auto p-3 bg-slate-50 rounded-xl font-bold text-xs text-slate-600 outline-none cursor-pointer border-r-8 border-transparent flex-shrink-0">
                <option value="">Todos los Recursos</option>
                <option value="multitrack">Con Multitrack</option>
                <option value="has-midi">Con MIDI</option>
                <option value="has-pro">Con ProPresenter</option>
                <option value="no-yt">Falta YouTube</option>
                <option value="no-pdf">Falta PDF</option>
            </select>
            </div>

            <?php if ($isAdmin): ?>
            <div class="flex flex-wrap gap-2 justify-end border-t border-slate-50 pt-4">
                <a href="export_songs.php" class="flex-1 md:flex-none justify-center bg-slate-700 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase shadow-md hover:bg-slate-800 transition-all whitespace-nowrap flex items-center gap-2 transform active:scale-95">⬇ Exportar</a>
                <a href="import_songs.php" class="flex-1 md:flex-none justify-center bg-emerald-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase shadow-md hover:bg-emerald-700 transition-all whitespace-nowrap flex items-center gap-2 transform active:scale-95">📂 Importar</a>
                <button onclick="openModal()" class="flex-1 md:flex-none justify-center bg-blue-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase shadow-md hover:bg-blue-700 transition-all whitespace-nowrap transform active:scale-95">+ Nueva</button>
            </div>
            <?php endif; ?>
        </div>

        <!-- Multi-select Tags Row -->
        <div>
            <p class="text-[9px] font-black uppercase text-slate-300 mb-2 ml-1 tracking-widest">Filtrar por etiquetas (Selección múltiple)</p>
            <div class="flex flex-wrap gap-2" id="tagFiltersContainer">
                <?php foreach ($all_tags as $t): ?>
                    <button type="button" 
                            onclick="toggleTagFilter(this, '<?php echo $t['id']; ?>')" 
                            class="tag-filter-btn px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest border border-slate-100 text-slate-400 hover:border-blue-300 transition-all bg-white"
                            data-active-class="<?php echo $t['color_class']; ?>">
                        <?php echo $t['name']; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- VISTA DE ESCRITORIO (Tabla) -->
    <div class="hidden md:block bg-white rounded-[3rem] shadow-xl border border-slate-100 overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black uppercase text-slate-400 tracking-widest">
                    <th class="p-6 text-center cursor-pointer hover:bg-slate-50 transition-colors" onclick="window.location='?sort=id&order=<?php echo ($sort=='id' && $order=='ASC')?'DESC':'ASC'; ?>'">
                        <div class="flex items-center justify-center">ID <?php echo getSortIcon('id', $sort, $order); ?></div>
                    </th>
                    <th class="p-6">
                        <div class="flex items-center gap-1">
                            <a href="?sort=artist&order=<?php echo ($sort=='artist' && $order=='ASC')?'DESC':'ASC'; ?>" class="hover:text-blue-600 flex items-center">
                                ARTISTA <?php echo getSortIcon('artist', $sort, $order); ?>
                            </a>
                            <span class="text-slate-300 mx-1">/</span>
                            <a href="?sort=title&order=<?php echo ($sort=='title' && $order=='ASC')?'DESC':'ASC'; ?>" class="hover:text-blue-600 flex items-center">
                                CANCIÓN <?php echo getSortIcon('title', $sort, $order); ?>
                            </a>
                        </div>
                    </th>
                    <th class="p-6 text-center cursor-pointer hover:bg-slate-50 transition-colors" onclick="window.location='?sort=musical_key&order=<?php echo ($sort=='musical_key' && $order=='ASC')?'DESC':'ASC'; ?>'">
                        <div class="flex items-center justify-center">TONO <?php echo getSortIcon('musical_key', $sort, $order); ?></div>
                    </th>
                    <th class="p-6 text-center">multitrack</th>
                    <th class="p-6 text-center">Recursos</th>
                    <th class="p-6 text-center">Etiquetas</th>
                    <?php if ($isAdmin): ?>
                        <th class="p-6 text-center">Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach ($songs as $s): ?>
                <tr class="song-row hover:bg-slate-50/50 transition-all group" 
                    data-tags="<?php echo $s['tag_ids'] ? ',' . $s['tag_ids'] . ',' : ''; ?>"
                    data-multitrack="<?php echo $s['has_multitrack']; ?>"
                    data-midi="<?php echo !empty(trim($s['midi_path'] ?? '')) ? '1' : '0'; ?>"
                    data-pro="<?php echo !empty(trim($s['propresenter_path'] ?? '')) ? '1' : '0'; ?>"
                    data-yt="<?php echo !empty(trim($s['youtube_link'] ?? '')) ? '1' : '0'; ?>"
                    data-pdf="<?php echo !empty(trim($s['has_lyrics'] ?? '')) ? '1' : '0'; ?>">
                    
                    <td class="p-6 text-center font-black text-slate-300 text-xs">#<?php echo $s['id']; ?></td>
                    <td class="p-6">
                        <div class="text-[10px] text-blue-600 font-black uppercase mb-1"><?php echo htmlspecialchars($s['artist']); ?></div>
                        <div class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($s['title']); ?></div>
                    </td>
                    <td class="p-6 text-center font-black text-slate-700"><?php echo $s['musical_key']; ?></td>
                    <td class="p-6 text-center">
                        <span class="text-[9px] font-black <?php echo $s['has_multitrack'] ? 'text-green-500' : 'text-slate-200'; ?>">
                            <?php echo $s['has_multitrack'] ? '● MULTITRACK' : 'NO'; ?>
                        </span>
                    </td>
                    <td class="p-6 text-center">
                        <div class="flex justify-center gap-3 text-lg">
                            <?php if(!empty($s['midi_path'])): ?><a href="<?php echo $s['midi_path']; ?>" target="_blank" title="MIDI">🎹</a><?php endif; ?>
                            <?php if(!empty($s['propresenter_path'])): ?><a href="<?php echo $s['propresenter_path']; ?>" target="_blank" title="propresenter lyrics">📺</a><?php endif; ?>
                            <?php if(!empty($s['has_lyrics'])): ?><a href="<?php echo $s['has_lyrics']; ?>" target="_blank">📄</a><?php endif; ?>
                            <?php if(!empty($s['youtube_link'])): ?><a href="<?php echo $s['youtube_link']; ?>" target="_blank" class="text-red-600 hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                            </a><?php endif; ?>
                        </div>
                    </td>
                    <td class="p-6 text-center">
                        <div class="flex flex-wrap justify-center gap-1">
                            <?php 
                            if($s['tag_names']):
                                $names = explode('||', $s['tag_names']);
                                $colors = explode('||', $s['tag_colors']);
                                for($i=0; $i<count($names); $i++): ?>
                                    <span class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border <?php echo $colors[$i] ?? 'bg-slate-100'; ?>">
                                        <?php echo $names[$i]; ?>
                                    </span>
                            <?php endfor; endif; ?>
                        </div>
                    </td>
                    <?php if ($isAdmin): ?>
                    <td class="p-6 text-center">
                        <div class="flex justify-center gap-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick='openModal(<?php echo json_encode($s); ?>)' class="text-slate-400 hover:text-blue-600">✎</button>
                            <a href="?delete=<?php echo $s['id']; ?>" onclick="return confirm('¿Eliminar?')" class="text-slate-400 hover:text-red-500">✕</a>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- VISTA MÓVIL (Lista Compacta) -->
    <div class="md:hidden bg-white rounded-2xl shadow-sm border border-slate-100 divide-y divide-slate-50">
        <?php foreach ($songs as $s): ?>
        <div class="song-row p-2 hover:bg-slate-50 transition-colors cursor-pointer active:bg-blue-50"
             onclick='openDetailModal(<?php echo json_encode($s); ?>)'
             data-tags="<?php echo $s['tag_ids'] ? ',' . $s['tag_ids'] . ',' : ''; ?>"
             data-multitrack="<?php echo $s['has_multitrack']; ?>"
             data-midi="<?php echo !empty(trim($s['midi_path'] ?? '')) ? '1' : '0'; ?>"
             data-pro="<?php echo !empty(trim($s['propresenter_path'] ?? '')) ? '1' : '0'; ?>"
             data-yt="<?php echo !empty(trim($s['youtube_link'] ?? '')) ? '1' : '0'; ?>"
             data-pdf="<?php echo !empty(trim($s['has_lyrics'] ?? '')) ? '1' : '0'; ?>">
            
            <div class="flex items-center justify-between gap-2">
                <!-- ID Box (Left) -->
                <div class="bg-slate-50 text-slate-400 w-8 h-8 rounded-lg flex flex-col items-center justify-center flex-shrink-0 border border-slate-200">
                    <span class="text-[6px] font-black uppercase leading-none opacity-60">ID</span>
                    <span class="text-[10px] font-black leading-none text-slate-600"><?php echo $s['id']; ?></span>
                </div>
                
                <!-- Info (Middle) -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1">
                        <h3 class="font-black text-slate-800 text-xs uppercase truncate leading-tight">
                            <?php echo htmlspecialchars($s['title']); ?>
                        </h3>
                        <?php if($s['has_multitrack']): ?>
                            <span class="w-1 h-1 rounded-full bg-green-500 flex-shrink-0" title="Multitrack Disponible"></span>
                        <?php endif; ?>
                    </div>
                    <p class="text-[8px] text-blue-600 font-bold uppercase tracking-widest mt-0.5 truncate">
                        <?php echo htmlspecialchars($s['artist']); ?>
                    </p>
                </div>

                <!-- Key & BPM Boxes (Right) -->
                <div class="flex gap-1 flex-shrink-0">
                    <div class="bg-slate-50 text-slate-600 w-8 h-8 rounded-lg flex flex-col items-center justify-center border border-slate-200">
                        <span class="text-[6px] font-black uppercase leading-none opacity-60">Key</span>
                        <span class="text-[10px] font-black leading-none text-slate-800"><?php echo $s['musical_key']; ?></span>
                    </div>
                    <div class="bg-slate-50 text-slate-600 w-8 h-8 rounded-lg flex flex-col items-center justify-center border border-slate-200">
                        <span class="text-[6px] font-black uppercase leading-none opacity-60">BPM</span>
                        <span class="text-[10px] font-black leading-none text-slate-800"><?php echo $s['bpm'] ?: '-'; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- Modal Detalle Canción (Móvil) -->
<div id="viewSongModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-in zoom-in-95 duration-300">
        
        <!-- Header con Imagen/Gradiente -->
        <div class="bg-slate-900 p-5 text-white relative overflow-hidden shrink-0">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500 rounded-full -mr-10 -mt-10 opacity-20 blur-2xl"></div>
            
            <div class="flex justify-between items-start relative z-10">
                <div class="flex-1 mr-4 min-w-0">
                    <h3 id="v_title" class="text-xl font-black uppercase italic tracking-tighter leading-tight mb-1 break-words">Título</h3>
                    <p id="v_artist" class="text-blue-400 font-bold text-xs uppercase tracking-widest">Artista</p>
                </div>
            </div>
        </div>

        <!-- Cuerpo Scrollable -->
        <div class="p-5 overflow-y-auto space-y-5">
            <!-- Grid Info -->
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-slate-50 p-2 rounded-xl text-center border border-slate-100">
                    <span class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">ID</span>
                    <span id="v_id" class="text-lg font-black text-slate-700">000</span>
                </div>
                <div class="bg-slate-50 p-2 rounded-xl text-center border border-slate-100">
                    <span class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Tono</span>
                    <span id="v_key" class="text-lg font-black text-blue-600">C</span>
                </div>
                <div class="bg-slate-50 p-2 rounded-xl text-center border border-slate-100">
                    <span class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">BPM</span>
                    <span id="v_bpm" class="text-lg font-black text-slate-700">0</span>
                </div>
            </div>

            <!-- Etiquetas -->
            <div id="v_tags_container" class="flex flex-wrap gap-2"></div>

            <!-- Recursos -->
            <div>
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-100 pb-1">Recursos</h4>
                <div class="grid grid-cols-1 gap-3" id="v_resources">
                    <!-- Botones generados por JS -->
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-slate-100 bg-white shrink-0 flex flex-col gap-3">
            <?php if ($isAdmin): ?>
            <button id="btn_edit_song" class="w-full bg-blue-600 text-white py-3 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                Editar Canción
            </button>
            <?php endif; ?>
            <button onclick="document.getElementById('viewSongModal').classList.add('hidden')" class="w-full bg-slate-100 text-slate-500 py-3 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-slate-200 transition-all">
                Volver
            </button>
        </div>
    </div>
</div>

<?php if ($isAdmin): ?>
<div id="songModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-md z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[3rem] w-full max-w-lg shadow-2xl overflow-hidden">
        <div class="p-8">
            <h3 id="modalTitle" class="text-2xl font-black text-slate-800 uppercase mb-6">Detalles Canción</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="old_id" id="m_old_id">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="text-[10px] font-black uppercase text-slate-400">ID Manual</label>
                        <input type="number" name="song_id_manual" id="m_id_manual" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-black text-blue-600 outline-none" required>
                    </div>
                    <div class="col-span-1">
                        <label class="text-[10px] font-black uppercase text-slate-400">Artista</label>
                        <input type="text" name="artist" id="m_artist" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-bold outline-none" required>
                    </div>
                    <div class="col-span-1">
                        <label class="text-[10px] font-black uppercase text-slate-400">Título</label>
                        <input type="text" name="title" id="m_title" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-bold outline-none" required>
                    </div>
                    <div class="col-span-2">
                        <label class="text-[10px] font-black uppercase text-slate-400">Recursos (Links Drive)</label>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <input type="url" name="midi_path" id="m_midi" placeholder="Midi Link" class="p-3 bg-slate-50 rounded-xl text-[10px] outline-none">
                            <input type="url" name="propresenter_path" id="m_pro" placeholder="propresenter lyrics Link" class="p-3 bg-slate-50 rounded-xl text-[10px] outline-none">
                            <input type="url" name="youtube_link" id="m_yt" placeholder="YouTube Link" class="p-3 bg-slate-50 rounded-xl text-[10px] outline-none">
                            <input type="url" name="has_lyrics" id="m_lyrics" placeholder="PDF/Lyrics Link" class="p-3 bg-slate-50 rounded-xl text-[10px] outline-none">
                        </div>
                    </div>
                    <div class="col-span-1">
                        <label class="text-[10px] font-black uppercase text-slate-400">Tono</label>
                        <input type="text" name="musical_key" id="m_key" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-black text-center outline-none">
                    </div>
                    <div class="col-span-1">
                        <label class="text-[10px] font-black uppercase text-slate-400">BPM</label>
                        <input type="number" name="bpm" id="m_bpm" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-bold text-center outline-none">
                    </div>
                    <div class="col-span-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block">Etiquetas</label>
                        <div class="flex flex-wrap gap-2 bg-slate-50 p-4 rounded-2xl">
                            <?php foreach($all_tags as $t): ?>
                                <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:border-blue-300 transition-all">
                                    <input type="checkbox" name="tags[]" value="<?php echo $t['id']; ?>" class="tag-checkbox w-4 h-4 accent-blue-600">
                                    <span class="text-[10px] font-black uppercase <?php echo $t['color_class']; ?> bg-transparent border-none p-0"><?php echo $t['name']; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="flex items-center justify-center bg-slate-50 rounded-2xl p-2 gap-3">
                        <span class="text-[10px] font-black uppercase text-slate-400">multitrack</span>
                        <input type="checkbox" name="has_multitrack" id="m_multitrack" class="w-6 h-6 accent-blue-600">
                    </div>
                </div>
                <button name="save_song" class="w-full bg-blue-600 text-white p-5 rounded-3xl font-black uppercase tracking-widest hover:bg-blue-700 mt-4 transition-all">Guardar</button>
                <button type="button" onclick="closeModal()" class="w-full text-slate-400 text-[10px] font-black uppercase mt-2">Cerrar</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Filtro Unificado
const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
let selectedTags = new Set();

function toggleTagFilter(btn, id) {
    const activeClass = btn.dataset.activeClass; // Clase de color guardada en data-attribute
    
    if (selectedTags.has(id)) {
        selectedTags.delete(id);
        // Desactivar estilo visual
        btn.className = "tag-filter-btn px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest border border-slate-100 text-slate-400 hover:border-blue-300 transition-all bg-white";
    } else {
        selectedTags.add(id);
        // Activar estilo visual (usando el color de la etiqueta)
        btn.className = "tag-filter-btn px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest border border-transparent transition-all ring-1 ring-offset-1 ring-slate-200 " + activeClass;
    }
    applyFilters();
}

function applyFilters() {
    const searchText = document.getElementById('songSearch').value.toLowerCase();
    const resourceValue = document.getElementById('resourceFilter').value;
    
    const rows = document.querySelectorAll('.song-row');
    
    rows.forEach(row => {
        let show = true;
        
        // 1. Search Text
        if (searchText && !row.innerText.toLowerCase().includes(searchText)) {
            show = false;
        }
        
        // 2. Tag Filter (Multi)
        if (show && selectedTags.size > 0) {
            // La canción debe tener TODAS las etiquetas seleccionadas (Lógica AND)
            for (let tagId of selectedTags) {
                if (!row.dataset.tags.includes(',' + tagId + ',')) {
                    show = false;
                    break;
                }
            }
        }
        
        // 3. Resource Filter
        if (show && resourceValue) {
            if (resourceValue === 'multitrack' && row.dataset.multitrack !== '1') show = false;
            else if (resourceValue === 'has-midi' && row.dataset.midi !== '1') show = false;
            else if (resourceValue === 'has-pro' && row.dataset.pro !== '1') show = false;
            else if (resourceValue === 'no-yt' && row.dataset.yt !== '0') show = false;
            else if (resourceValue === 'no-pdf' && row.dataset.pdf !== '0') show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

// Event Listeners
document.getElementById('songSearch').addEventListener('keyup', applyFilters);
document.getElementById('resourceFilter').addEventListener('change', applyFilters);

// Modal de Detalle (Móvil)
function openDetailModal(song) {
    // Poblar datos básicos
    document.getElementById('v_title').innerText = song.title;
    document.getElementById('v_artist').innerText = song.artist;
    document.getElementById('v_id').innerText = song.id;
    document.getElementById('v_key').innerText = song.musical_key;
    document.getElementById('v_bpm').innerText = song.bpm || '-';

    // Poblar Etiquetas
    const tagsContainer = document.getElementById('v_tags_container');
    tagsContainer.innerHTML = '';
    if (song.tag_names) {
        const names = song.tag_names.split('||');
        const colors = song.tag_colors ? song.tag_colors.split('||') : [];
        names.forEach((name, i) => {
            const span = document.createElement('span');
            span.className = `px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border ${colors[i] || 'bg-slate-100'}`;
            span.innerText = name;
            tagsContainer.appendChild(span);
        });
    }

    // Poblar Recursos
    const resContainer = document.getElementById('v_resources');
    resContainer.innerHTML = '';
    
    const resources = [
        { type: 'youtube', url: song.youtube_link, label: 'Ver en YouTube', icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>', color: 'bg-red-50 text-red-600 border-red-100' },
        { type: 'pdf', url: song.has_lyrics, label: 'Abrir PDF / Letra', icon: '📄', color: 'bg-blue-50 text-blue-600 border-blue-100' },
        { type: 'midi', url: song.midi_path, label: 'Descargar MIDI', icon: '🎹', color: 'bg-indigo-50 text-indigo-600 border-indigo-100' },
        { type: 'pro', url: song.propresenter_path, label: 'ProPresenter File', icon: '📺', color: 'bg-orange-50 text-orange-600 border-orange-100' }
    ];

    resources.forEach(res => {
        if (res.url && res.url.trim() !== '') {
            const a = document.createElement('a');
            a.href = res.url;
            a.target = '_blank';
            a.className = `flex items-center gap-3 p-3 rounded-xl border ${res.color} font-bold text-xs uppercase tracking-widest transition-transform active:scale-95`;
            a.innerHTML = `<span class="text-lg">${res.icon}</span> ${res.label}`;
            resContainer.appendChild(a);
        }
    });

    // Configurar botón editar (si es admin)
    if (isAdmin) {
        const btnEdit = document.getElementById('btn_edit_song');
        if(btnEdit) btnEdit.onclick = function() { openModal(song); };
    }

    document.getElementById('viewSongModal').classList.remove('hidden');
}

<?php if ($isAdmin): ?>
function openModal(song = null) {
    const m = document.getElementById('songModal');
    if(song) {
        document.getElementById('m_old_id').value = song.id;
        document.getElementById('m_id_manual').value = song.id;
        document.getElementById('m_artist').value = song.artist;
        document.getElementById('m_title').value = song.title;
        document.getElementById('m_key').value = song.musical_key;
        document.getElementById('m_bpm').value = song.bpm;
        document.getElementById('m_yt').value = song.youtube_link;
        document.getElementById('m_lyrics').value = song.has_lyrics;
        document.getElementById('m_midi').value = song.midi_path;
        document.getElementById('m_pro').value = song.propresenter_path;
        document.getElementById('m_multitrack').checked = (song.has_multitrack == 1);
        
        // Resetear y marcar checkboxes
        document.querySelectorAll('.tag-checkbox').forEach(cb => cb.checked = false);
        if(song.tag_ids) {
            let ids = song.tag_ids.split(',');
            ids.forEach(id => {
                let cb = document.querySelector(`.tag-checkbox[value="${id}"]`);
                if(cb) cb.checked = true;
            });
        }
        
        document.getElementById('modalTitle').innerText = "Editar Canción";
    } else {
        document.querySelector('#songModal form').reset();
        document.getElementById('m_old_id').value = "";
        document.getElementById('modalTitle').innerText = "Nueva Canción";
    }
    m.classList.remove('hidden');
}
function closeModal() { document.getElementById('songModal').classList.add('hidden'); }
<?php endif; ?>
</script>