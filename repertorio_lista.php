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
                $stmt = $pdo->prepare("UPDATE songs SET id=?, title=?, artist=?, musical_key=?, youtube_link=?, bpm=?, has_multitrack=?, has_lyrics=?, priority=?, midi_path=?, propresenter_path=? WHERE id=?");
                $stmt->execute([$new_id, $_POST['title'], $_POST['artist'], $_POST['musical_key'], $_POST['youtube_link'], $_POST['bpm'], $has_multitrack, $_POST['has_lyrics'], $_POST['priority'], $_POST['midi_path'], $_POST['propresenter_path'], $old_id]);
                $message = "Actualizado.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO songs (id, title, artist, musical_key, youtube_link, bpm, has_multitrack, has_lyrics, priority, midi_path, propresenter_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$new_id, $_POST['title'], $_POST['artist'], $_POST['musical_key'], $_POST['youtube_link'], $_POST['bpm'], $has_multitrack, $_POST['has_lyrics'], $_POST['priority'], $_POST['midi_path'], $_POST['propresenter_path']]);
                $message = "Añadida.";
            }
        } catch (PDOException $e) {
            $error = ($e->getCode() == 23000) ? "El ID ya existe." : "Error: " . $e->getMessage();
        }
    }
}

// --- 2. CONSULTAS DASHBOARD ---
$totalSongs = $pdo->query("SELECT COUNT(*) FROM songs")->fetchColumn();
$withMultitracks = $pdo->query("SELECT COUNT(*) FROM songs WHERE has_multitrack = 1")->fetchColumn();
$withMidis = $pdo->query("SELECT COUNT(*) FROM songs WHERE midi_path IS NOT NULL AND midi_path != ''")->fetchColumn();
$withPro = $pdo->query("SELECT COUNT(*) FROM songs WHERE propresenter_path IS NOT NULL AND propresenter_path != ''")->fetchColumn();
$noYoutube = $pdo->query("SELECT COUNT(*) FROM songs WHERE youtube_link IS NULL OR TRIM(youtube_link) = ''")->fetchColumn();
$noLyrics = $pdo->query("SELECT COUNT(*) FROM songs WHERE has_lyrics IS NULL OR TRIM(has_lyrics) = ''")->fetchColumn();
$priorityCounts = $pdo->query("SELECT priority, COUNT(*) as total FROM songs GROUP BY priority")->fetchAll(PDO::FETCH_ASSOC);

$songs = $pdo->query("SELECT * FROM songs ORDER BY artist ASC, title ASC")->fetchAll(PDO::FETCH_ASSOC);
include 'header.php'; 
?>

<div class="container mx-auto px-4 max-w-7xl pb-10">
    
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 mb-8 text-center">
        <div onclick="filterTable('all')" class="cursor-pointer bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 hover:border-blue-400 transition-all">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total</p>
            <h3 class="text-3xl font-black text-blue-600"><?php echo $totalSongs; ?></h3>
        </div>
        <div onclick="filterTable('multitrack')" class="cursor-pointer bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 hover:border-green-400 transition-all">
            <p class="text-[10px] font-black text-green-500 uppercase tracking-widest">multitrack</p>
            <h3 class="text-3xl font-black text-slate-800"><?php echo $withMultitracks; ?></h3>
        </div>
        <div onclick="filterTable('has-midi')" class="cursor-pointer bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 hover:border-indigo-400 transition-all">
            <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Midi</p>
            <h3 class="text-3xl font-black text-slate-800"><?php echo $withMidis; ?></h3>
        </div>
        <div onclick="filterTable('has-pro')" class="cursor-pointer bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 hover:border-orange-400 transition-all">
            <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest">ProPresenter</p>
            <h3 class="text-3xl font-black text-slate-800"><?php echo $withPro; ?></h3>
        </div>
        <div onclick="filterTable('no-yt')" class="cursor-pointer bg-white p-5 rounded-[2rem] shadow-sm border-l-4 border-red-500">
            <p class="text-[10px] font-black text-red-500 uppercase tracking-widest">Sin Youtube</p>
            <h3 class="text-3xl font-black text-slate-800"><?php echo $noYoutube; ?></h3>
        </div>
        <div onclick="filterTable('no-pdf')" class="cursor-pointer bg-white p-5 rounded-[2rem] shadow-sm border-l-4 border-orange-500">
            <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest">Sin PDF</p>
            <h3 class="text-3xl font-black text-slate-800"><?php echo $noLyrics; ?></h3>
        </div>
        <?php foreach ($priorityCounts as $pc): 
            $pColor = ($pc['priority'] == 'High') ? 'text-red-500' : (($pc['priority'] == 'Medium') ? 'text-orange-500' : 'text-blue-400');
        ?>
        <div onclick="filterTable('<?php echo strtolower($pc['priority']); ?>')" class="cursor-pointer bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100">
            <p class="text-[10px] font-black <?php echo $pColor; ?> uppercase tracking-widest"><?php echo $pc['priority']; ?></p>
            <h3 class="text-3xl font-black text-slate-800"><?php echo $pc['total']; ?></h3>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tighter">Repertorio</h1>
        <div class="flex gap-4">
            <div class="relative w-64">
                <input type="text" id="songSearch" placeholder="Buscar..." class="w-full p-4 bg-white border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 pr-10">
                <button onclick="document.getElementById('songSearch').value=''; filterTable('all');" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300">✕</button>
            </div>
            <button onclick="openModal()" class="bg-blue-600 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase shadow-lg shadow-blue-200">+ Nueva</button>
        </div>
    </div>

    <div class="bg-white rounded-[3rem] shadow-xl border border-slate-100 overflow-hidden">
        <table class="w-full text-left" id="songsTable">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black uppercase text-slate-400 tracking-widest">
                    <th onclick="sortTable(0)" class="p-6 text-center cursor-pointer">ID ↕</th>
                    <th onclick="sortTable(1)" class="p-6 cursor-pointer">Artista / Canción ↕</th>
                    <th onclick="sortTable(2)" class="p-6 text-center cursor-pointer">Tono ↕</th>
                    <th class="p-6 text-center">multitrack</th>
                    <th class="p-6 text-center">Recursos</th>
                    <th class="p-6 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach ($songs as $s): ?>
                <tr class="song-row hover:bg-slate-50/50 transition-all group" 
                    data-priority="<?php echo strtolower($s['priority']); ?>" 
                    data-multitrack="<?php echo $s['has_multitrack']; ?>"
                    data-midi="<?php echo !empty(trim($s['midi_path'] ?? '')) ? '1' : '0'; ?>"
                    data-pro="<?php echo !empty(trim($s['propresenter_path'] ?? '')) ? '1' : '0'; ?>"
                    data-yt="<?php echo !empty(trim($s['youtube_link'] ?? '')) ? '1' : '0'; ?>"
                    data-pdf="<?php echo !empty(trim($s['has_lyrics'] ?? '')) ? '1' : '0'; ?>">
                    
                    <td class="p-6 text-center font-black text-slate-300 text-xs">#<?php echo $s['id']; ?></td>
                    <td class="p-6">
                        <div onclick="filterByArtist('<?php echo addslashes($s['artist']); ?>')" class="text-[10px] text-blue-600 font-black uppercase cursor-pointer hover:underline mb-1">
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
                            <?php if(!empty($s['midi_path'])): ?><a href="<?php echo $s['midi_path']; ?>" target="_blank" title="MIDI">🎹</a><?php endif; ?>
                            <?php if(!empty($s['propresenter_path'])): ?><a href="<?php echo $s['propresenter_path']; ?>" target="_blank" title="ProPresenter">📺</a><?php endif; ?>
                            <?php if(!empty($s['has_lyrics'])): ?><a href="<?php echo $s['has_lyrics']; ?>" target="_blank">📄</a><?php endif; ?>
                            <?php if(!empty($s['youtube_link'])): ?><a href="<?php echo $s['youtube_link']; ?>" target="_blank">🎬</a><?php endif; ?>
                        </div>
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
                        <label class="text-[10px] font-black uppercase text-slate-400">Recursos (Links Drive/Web)</label>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <input type="url" name="midi_path" id="m_midi" placeholder="Midi Link" class="p-3 bg-slate-50 rounded-xl text-[10px] outline-none">
                            <input type="url" name="propresenter_path" id="m_pro" placeholder="ProPresenter file" class="p-3 bg-slate-50 rounded-xl text-[10px] outline-none">
                            <input type="url" name="youtube_link" id="m_yt" placeholder="YouTube Link" class="p-3 bg-slate-50 rounded-xl text-[10px] outline-none">
                            <input type="url" name="has_lyrics" id="m_lyrics" placeholder="PDF Link" class="p-3 bg-slate-50 rounded-xl text-[10px] outline-none">
                        </div>
                    </div>
                    <div class="col-span-1">
                        <label class="text-[10px] font-black uppercase text-slate-400">Tono</label>
                        <input type="text" name="musical_key" id="m_key" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-black text-center uppercase outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-1">
                        <label class="text-[10px] font-black uppercase text-slate-400">BPM</label>
                        <input type="number" name="bpm" id="m_bpm" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-bold text-center outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-1">
                        <label class="text-[10px] font-black uppercase text-slate-400">Prioridad</label>
                        <select name="priority" id="m_priority" class="w-full p-4 bg-slate-50 rounded-2xl border-none font-black text-[10px] uppercase outline-none">
                            <option value="High">High</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="Low">Low</option>
                        </select>
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

<script>
function filterByArtist(name) {
    const input = document.getElementById('songSearch');
    input.value = name;
    input.dispatchEvent(new Event('keyup'));
}

function sortTable(n) {
    let table = document.getElementById("songsTable");
    let rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    switching = true; dir = "asc"; 
    while (switching) {
        switching = false; rows = table.querySelectorAll(".song-row");
        for (i = 0; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i+1].getElementsByTagName("TD")[n];
            let xV = x.innerText.toLowerCase(); let yV = y.innerText.toLowerCase();
            if (n === 0) { xV = parseInt(xV.replace('#', '')) || 0; yV = parseInt(yV.replace('#', '')) || 0; }
            if (dir == "asc" ? xV > yV : xV < yV) { shouldSwitch = true; break; }
        }
        if (shouldSwitch) { rows[i].parentNode.insertBefore(rows[i + 1], rows[i]); switching = true; switchcount ++; }
        else if (switchcount == 0 && dir == "asc") { dir = "desc"; switching = true; }
    }
}

function filterTable(type) {
    const rows = document.querySelectorAll('.song-row');
    rows.forEach(row => {
        if (type === 'all') row.style.display = '';
        else if (type === 'multitrack') row.style.display = row.dataset.multitrack === '1' ? '' : 'none';
        else if (type === 'has-midi') row.style.display = row.dataset.midi === '1' ? '' : 'none';
        else if (type === 'has-pro') row.style.display = row.dataset.pro === '1' ? '' : 'none';
        else if (type === 'no-yt') row.style.display = row.dataset.yt === '0' ? '' : 'none';
        else if (type === 'no-pdf') row.style.display = row.dataset.pdf === '0' ? '' : 'none';
        else row.style.display = row.dataset.priority === type ? '' : 'none';
    });
}

document.getElementById('songSearch').addEventListener('keyup', function() {
    let f = this.value.toLowerCase();
    document.querySelectorAll('.song-row').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(f) ? '' : 'none';
    });
});

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
        document.getElementById('m_priority').value = song.priority;
        document.getElementById('m_multitrack').checked = (song.has_multitrack == 1);
        document.getElementById('modalTitle').innerText = "Editar Canción";
    } else {
        document.querySelector('#songModal form').reset();
        document.getElementById('m_old_id').value = "";
        document.getElementById('modalTitle').innerText = "Nueva Canción";
    }
    m.classList.remove('hidden');
}
function closeModal() { document.getElementById('songModal').classList.add('hidden'); }
</script>