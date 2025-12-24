<?php
require_once 'db_config.php';

$isAdmin = true; 
$message = ""; 
$error = "";

// --- 1. PROCESAMIENTO DE DATOS ---
if ($isAdmin) {
    if (isset($_POST['save_song'])) {
        $has_multitrack = isset($_POST['has_multitrack']) ? 1 : 0;
        $new_id = $_POST['song_id_manual']; 
        $old_id = $_POST['old_id']; 

        try {
            if (!empty($old_id)) {
                $stmt = $pdo->prepare("UPDATE songs SET id=?, title=?, artist=?, musical_key=?, youtube_link=?, bpm=?, has_multitrack=?, has_lyrics=?, priority=? WHERE id=?");
                $stmt->execute([$new_id, $_POST['title'], $_POST['artist'], $_POST['musical_key'], $_POST['youtube_link'], $_POST['bpm'], $has_multitrack, $_POST['has_lyrics'], $_POST['priority'], $old_id]);
                $message = "Canción #$new_id actualizada.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO songs (id, title, artist, musical_key, youtube_link, bpm, has_multitrack, has_lyrics, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$new_id, $_POST['title'], $_POST['artist'], $_POST['musical_key'], $_POST['youtube_link'], $_POST['bpm'], $has_multitrack, $_POST['has_lyrics'], $_POST['priority']]);
                $message = "Canción #$new_id añadida.";
            }
        } catch (PDOException $e) {
            $error = ($e->getCode() == 23000) ? "El ID #$new_id ya existe." : "Error: " . $e->getMessage();
        }
    }
    if (isset($_GET['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM songs WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        header("Location: repertorio_lista.php?deleted=1"); exit;
    }
}

// --- 2. CONSULTAS DASHBOARD ---
$totalSongs = $pdo->query("SELECT COUNT(*) FROM songs")->fetchColumn();
$withMultitracks = $pdo->query("SELECT COUNT(*) FROM songs WHERE has_multitrack = 1")->fetchColumn();
$noYoutube = $pdo->query("SELECT COUNT(*) FROM songs WHERE youtube_link IS NULL OR TRIM(youtube_link) = ''")->fetchColumn();
$noLyrics = $pdo->query("SELECT COUNT(*) FROM songs WHERE has_lyrics IS NULL OR TRIM(has_lyrics) = ''")->fetchColumn();
$priorityCounts = $pdo->query("SELECT priority, COUNT(*) as total FROM songs GROUP BY priority")->fetchAll(PDO::FETCH_ASSOC);

$songs = $pdo->query("SELECT * FROM songs ORDER BY artist ASC, title ASC")->fetchAll(PDO::FETCH_ASSOC);
include 'header.php'; 
?>

<?php if ($message || $error): ?>
<div id="toast" class="fixed top-5 right-5 z-[100] <?php echo $error ? 'bg-red-600' : 'bg-slate-900'; ?> text-white px-6 py-4 rounded-3xl shadow-2xl flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-300">
    <span class="text-xs font-black uppercase tracking-wider"><?php echo $error ? $error : $message; ?></span>
</div>
<script>setTimeout(() => { const t = document.getElementById('toast'); if(t){ t.style.opacity='0'; setTimeout(()=>t.remove(),500); } }, 4000);</script>
<?php endif; ?>

<div class="container mx-auto px-4 max-w-7xl pb-10">
    
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-8 text-center">
        <div onclick="filterTable('all')" class="cursor-pointer bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 hover:border-blue-400 transition-all">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total</p>
            <h3 class="text-3xl font-black text-blue-600"><?php echo $totalSongs; ?></h3>
        </div>
        <div onclick="filterTable('multitrack')" class="cursor-pointer bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 hover:border-green-400 transition-all">
            <p class="text-[10px] font-black text-green-500 uppercase tracking-widest">Multitrack</p>
            <h3 class="text-3xl font-black text-slate-800"><?php echo $withMultitracks; ?></h3>
        </div>
        <div onclick="filterTable('no-yt')" class="cursor-pointer bg-white p-5 rounded-[2rem] shadow-sm border-l-4 border-red-500 hover:shadow-md transition-all">
            <p class="text-[10px] font-black text-red-500 uppercase tracking-widest">Sin Youtube</p>
            <h3 class="text-3xl font-black text-slate-800"><?php echo $noYoutube; ?></h3>
        </div>
        <div onclick="filterTable('no-pdf')" class="cursor-pointer bg-white p-5 rounded-[2rem] shadow-sm border-l-4 border-orange-500 hover:shadow-md transition-all">
            <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest">Sin PDF</p>
            <h3 class="text-3xl font-black text-slate-800"><?php echo $noLyrics; ?></h3>
        </div>
        <?php foreach ($priorityCounts as $pc): 
            $color = ($pc['priority'] == 'High') ? 'text-red-500' : (($pc['priority'] == 'Medium') ? 'text-orange-500' : 'text-blue-400');
        ?>
        <div onclick="filterTable('<?php echo strtolower($pc['priority']); ?>')" class="cursor-pointer bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-md transition-all">
            <p class="text-[10px] font-black <?php echo $color; ?> uppercase tracking-widest">Prio. <?php echo $pc['priority']; ?></p>
            <h3 class="text-3xl font-black text-slate-800"><?php echo $pc['total']; ?></h3>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div class="flex items-center gap-4">
            <h1 class="text-3xl font-black text-slate-800 tracking-tighter uppercase">Biblioteca</h1>
            <button onclick="openModal()" class="bg-blue-600 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-200">+ Nueva</button>
        </div>
        <div class="relative w-full md:w-80">
            <input type="text" id="songSearch" placeholder="Buscar artista o canción..." class="w-full p-4 bg-white border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 pr-10">
            <button onclick="document.getElementById('songSearch').value=''; filterTable('all');" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500 font-bold">✕</button>
        </div>
    </div>

    <div class="bg-white rounded-[3rem] shadow-xl border border-slate-100 overflow-hidden">
        <table class="w-full text-left" id="songsTable">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black uppercase text-slate-400 tracking-widest">
                    <th onclick="sortTable(0)" class="p-6 text-center cursor-pointer hover:text-blue-600 transition-colors">ID ↕</th>
                    <th onclick="sortTable(1)" class="p-6 cursor-pointer hover:text-blue-600 transition-colors">Artista / Canción ↕</th>
                    <th onclick="sortTable(2)" class="p-6 text-center cursor-pointer hover:text-blue-600 transition-colors">Tono ↕</th>
                    <th class="p-6 text-center">Multitrack</th>
                    <th class="p-6 text-center">Recursos</th>
                    <th onclick="sortTable(5)" class="p-6 text-center cursor-pointer hover:text-blue-600 transition-colors">Prioridad ↕</th>
                    <th class="p-6 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach ($songs as $s): 
                    $hasYt = (!empty(trim($s['youtube_link']))) ? '1' : '0';
                    $hasPdf = (!empty(trim($s['has_lyrics']))) ? '1' : '0';
                ?>
                <tr class="song-row hover:bg-slate-50/50 transition-all group" 
                    data-priority="<?php echo strtolower($s['priority']); ?>" 
                    data-multitrack="<?php echo $s['has_multitrack']; ?>"
                    data-yt="<?php echo $hasYt; ?>"
                    data-pdf="<?php echo $hasPdf; ?>">
                    
                    <td class="p-6 text-center font-black text-slate-300 text-xs">#<?php echo $s['id']; ?></td>
                    <td class="p-6">
                        <div onclick="filterByArtist('<?php echo addslashes($s['artist']); ?>')" 
                             class="text-[10px] text-blue-600 font-black uppercase tracking-tighter cursor-pointer hover:underline inline-block mb-1">
                             <?php echo htmlspecialchars($s['artist']); ?>
                        </div>
                        <div class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($s['title']); ?></div>
                    </td>
                    <td class="p-6 text-center font-black text-slate-700 uppercase"><?php echo $s['musical_key']; ?></td>
                    <td class="p-6 text-center">
                        <span class="text-[9px] font-black <?php echo $s['has_multitrack'] ? 'text-green-500' : 'text-slate-200'; ?>">
                            <?php echo $s['has_multitrack'] ? '● MULTITRACK' : 'NO'; ?>
                        </span>
                    </td>
                    <td class="p-6 text-center">
                        <div class="flex justify-center gap-3 text-lg">
                            <?php if($hasPdf === '1'): ?><a href="<?php echo $s['has_lyrics']; ?>" target="_blank" title="Ver PDF">📄</a><?php endif; ?>
                            <?php if($hasYt === '1'): ?><a href="<?php echo $s['youtube_link']; ?>" target="_blank" title="Ver Video">🎬</a><?php endif; ?>
                        </div>
                    </td>
                    <td class="p-6 text-center">
                        <span class="text-[9px] font-black px-3 py-1 rounded-full border border-slate-100 uppercase text-slate-500"><?php echo $s['priority']; ?></span>
                    </td>
                    <td class="p-6 text-center">
                        <div class="flex justify-center gap-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick='openModal(<?php echo json_encode($s); ?>)' class="text-slate-400 hover:text-blue-600">✎</button>
                            <a href="?delete=<?php echo $s['id']; ?>" onclick="return confirm('¿Eliminar?')" class="text-slate-400 hover:text-red-500">✕</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="songModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-md z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[3rem] w-full max-w-lg shadow-2xl overflow-hidden animate-in zoom-in duration-200">
        <div class="p-8">
            <h3 id="modalTitle" class="text-2xl font-black text-slate-800 uppercase tracking-tighter mb-6">Nueva Canción</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="old_id" id="m_old_id">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">ID Manual</label>
                        <input type="number" name="song_id_manual" id="m_id_manual" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-black text-blue-600 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="col-span-1">
                        <label class="text-[10px] font-black uppercase text-slate-400">Artista</label>
                        <input type="text" name="artist" id="m_artist" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-bold outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="col-span-1">
                        <label class="text-[10px] font-black uppercase text-slate-400">Título</label>
                        <input type="text" name="title" id="m_title" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-bold outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="col-span-1">
                        <label class="text-[10px] font-black uppercase text-slate-400">Tono</label>
                        <input type="text" name="musical_key" id="m_key" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-black text-center uppercase outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="col-span-1">
                        <label class="text-[10px] font-black uppercase text-slate-400">BPM</label>
                        <input type="number" name="bpm" id="m_bpm" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-bold text-center outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="col-span-2">
                        <label class="text-[10px] font-black uppercase text-slate-400">YouTube Link</label>
                        <input type="url" name="youtube_link" id="m_yt" class="w-full p-4 bg-slate-50 rounded-2xl border-none text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label class="text-[10px] font-black uppercase text-slate-400">PDF Link</label>
                        <input type="url" name="has_lyrics" id="m_lyrics" class="w-full p-4 bg-slate-50 rounded-2xl border-none text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-400">Prioridad</label>
                        <select name="priority" id="m_priority" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-black text-[10px] uppercase">
                            <option value="High">Alta</option>
                            <option value="Medium" selected>Media</option>
                            <option value="Low">Baja</option>
                        </select>
                    </div>
                    <div class="flex flex-col items-center justify-center bg-slate-50 rounded-2xl">
                        <span class="text-[10px] font-black uppercase text-slate-400 mb-1">Multitrack</span>
                        <input type="checkbox" name="has_multitrack" id="m_multitrack" class="w-5 h-5 accent-blue-600">
                    </div>
                </div>
                <button name="save_song" class="w-full bg-blue-600 text-white p-5 rounded-3xl font-black uppercase tracking-widest hover:bg-blue-700 transition-all mt-4">Guardar Cambios</button>
                <button type="button" onclick="closeModal()" class="w-full text-slate-400 text-[10px] font-black uppercase mt-2">Cancelar</button>
            </form>
        </div>
    </div>
</div>

<script>
// --- FILTRAR POR ARTISTA AL HACER CLIC ---
function filterByArtist(artistName) {
    const searchInput = document.getElementById('songSearch');
    searchInput.value = artistName;
    
    const rows = document.querySelectorAll('.song-row');
    const filter = artistName.toLowerCase();
    
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
    });
}

// --- ORDENAR TABLA ---
function sortTable(n) {
    let table = document.getElementById("songsTable");
    let rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    switching = true;
    dir = "asc"; 
    while (switching) {
        switching = false;
        rows = table.querySelectorAll(".song-row");
        for (i = 0; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i+1].getElementsByTagName("TD")[n];
            let xVal = x.innerText.toLowerCase();
            let yVal = y.innerText.toLowerCase();
            if (n === 0) {
                xVal = parseInt(xVal.replace('#', '')) || 0;
                yVal = parseInt(yVal.replace('#', '')) || 0;
            }
            if (dir == "asc") {
                if (xVal > yVal) { shouldSwitch = true; break; }
            } else if (dir == "desc") {
                if (xVal < yVal) { shouldSwitch = true; break; }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount ++;      
        } else {
            if (switchcount == 0 && dir == "asc") { dir = "desc"; switching = true; }
        }
    }
}

// --- FILTROS DASHBOARD ---
function filterTable(type) {
    const searchInput = document.getElementById('songSearch');
    if(type === 'all') searchInput.value = ''; // Limpiar buscador al ver todos

    const rows = document.querySelectorAll('.song-row');
    rows.forEach(row => {
        const hasYt = row.getAttribute('data-yt');
        const hasPdf = row.getAttribute('data-pdf');
        const priority = row.getAttribute('data-priority');
        const isMultitrack = row.getAttribute('data-multitrack');

        if (type === 'all') row.style.display = '';
        else if (type === 'multitrack') row.style.display = (isMultitrack === '1') ? '' : 'none';
        else if (type === 'no-yt') row.style.display = (hasYt === '0') ? '' : 'none';
        else if (type === 'no-pdf') row.style.display = (hasPdf === '0') ? '' : 'none';
        else row.style.display = (priority === type) ? '' : 'none';
    });
}

// --- BUSCADOR GENERAL ---
document.getElementById('songSearch').addEventListener('keyup', function() {
    let f = this.value.toLowerCase();
    document.querySelectorAll('.song-row').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(f) ? '' : 'none';
    });
});

// --- MODAL ---
function openModal(song = null) {
    const modal = document.getElementById('songModal');
    if(song) {
        document.getElementById('m_old_id').value = song.id;
        document.getElementById('m_id_manual').value = song.id;
        document.getElementById('m_artist').value = song.artist;
        document.getElementById('m_title').value = song.title;
        document.getElementById('m_key').value = song.musical_key;
        document.getElementById('m_bpm').value = song.bpm;
        document.getElementById('m_yt').value = song.youtube_link;
        document.getElementById('m_lyrics').value = song.has_lyrics;
        document.getElementById('m_priority').value = song.priority;
        document.getElementById('m_multitrack').checked = (song.has_multitrack == 1);
        document.getElementById('modalTitle').innerText = "Editar Canción";
    } else {
        document.querySelector('#songModal form').reset();
        document.getElementById('m_old_id').value = "";
        document.getElementById('modalTitle').innerText = "Nueva Canción";
    }
    modal.classList.remove('hidden');
}
function closeModal() { document.getElementById('songModal').classList.add('hidden'); }
</script>