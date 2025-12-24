<?php
require_once 'db_config.php';

// --- CONFIGURACIÓN DE PRIVILEGIOS (A futuro vendrá de la sesión) ---
$isAdmin = true; // Cambiar a false para modo lectura
// -----------------------------------------------------------------

// 1. Procesamiento de nueva canción
if ($isAdmin && isset($_POST['add_song'])) {
    $stmt = $pdo->prepare("INSERT INTO songs (title, artist, musical_key, youtube_link, bpm, has_multitrack, has_lyrics, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['title'], $_POST['artist'], $_POST['musical_key'], 
        $_POST['youtube_link'], $_POST['bpm'], 
        isset($_POST['has_multitrack']) ? 1 : 0, 
        $_POST['has_lyrics'], $_POST['priority']
    ]);
    header("Location: repertorio_lista.php"); 
    exit;
}

// 2. Procesamiento de EDICIÓN
if ($isAdmin && isset($_POST['update_song'])) {
    $stmt = $pdo->prepare("UPDATE songs SET title=?, artist=?, musical_key=?, youtube_link=?, bpm=?, has_multitrack=?, has_lyrics=?, priority=? WHERE id=?");
    $stmt->execute([
        $_POST['title'], $_POST['artist'], $_POST['musical_key'], 
        $_POST['youtube_link'], $_POST['bpm'], 
        isset($_POST['has_multitrack']) ? 1 : 0, 
        $_POST['has_lyrics'], $_POST['priority'],
        $_POST['song_id']
    ]);
    header("Location: repertorio_lista.php");
    exit;
}

// 3. Procesamiento de ELIMINAR
if ($isAdmin && isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM songs WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: repertorio_lista.php");
    exit;
}

$songs = $pdo->query("SELECT * FROM songs ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
include 'header.php'; 
?>

<div class="container mx-auto px-4 max-w-7xl pb-10">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tighter uppercase">Biblioteca de Repertorio</h1>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.3em]">Control total de canciones</p>
        </div>
        <div class="relative w-full md:w-96">
            <input type="text" id="songSearch" placeholder="Buscar por título o artista..." 
                   class="w-full p-3 pl-10 bg-white border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm transition-all">
            <span class="absolute left-3 top-3.5 text-slate-400 text-xs">🔍</span>
        </div>
    </div>

    <?php if ($isAdmin): ?>
    <form method="POST" class="bg-[#13192b] p-4 rounded-[2rem] mb-8 shadow-xl">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <input type="text" name="title" placeholder="Título" class="md:col-span-2 p-3 rounded-xl text-xs font-bold outline-none" required>
            <input type="text" name="artist" placeholder="Artista" class="md:col-span-2 p-3 rounded-xl text-xs font-bold outline-none" required>
            <input type="text" name="musical_key" placeholder="Tono" class="md:col-span-1 p-3 rounded-xl text-xs text-center font-black uppercase outline-none" required>
            <input type="number" name="bpm" placeholder="BPM" class="md:col-span-1 p-3 rounded-xl text-xs text-center font-bold outline-none" required>
            <input type="url" name="has_lyrics" placeholder="Link PDF" class="md:col-span-1 p-3 rounded-xl text-[10px] outline-none">
            <input type="url" name="youtube_link" placeholder="Link YT" class="md:col-span-1 p-3 rounded-xl text-[10px] outline-none">
            <select name="priority" class="md:col-span-1 p-3 rounded-xl text-[10px] font-black uppercase cursor-pointer outline-none" required>
                <option value="" disabled selected>Prioridad</option>
                <option value="High">Alta</option>
                <option value="Medium">Media</option>
                <option value="Low">Baja</option>
            </select>
            <div class="md:col-span-1 flex flex-col items-center justify-center bg-slate-800/50 py-1 rounded-xl border border-slate-700">
                <span class="text-[7px] font-black text-slate-500 uppercase tracking-widest mb-1">Multitrack</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="has_multitrack" value="1" class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-700 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            <button name="add_song" class="md:col-span-1 bg-blue-600 text-white p-3 rounded-xl text-lg font-black hover:bg-blue-500 transition-all shadow-lg shadow-blue-900/40">＋</button>
        </div>
    </form>
    <?php endif; ?>

    <div class="bg-white rounded-[2rem] shadow-2xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <table class="w-full text-left border-collapse table-auto" id="songTable">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black uppercase text-slate-400">
                    <th class="p-4 w-16 text-center border-r border-slate-100">Tono</th>
                    <th class="p-4">Canción / Artista</th>
                    <th class="p-4 text-center">BPM</th>
                    <th class="p-4 text-center">Track</th>
                    <th class="p-4 text-center">PDF</th>
                    <th class="p-4 text-center">YT</th>
                    <th class="p-4 text-center">Prioridad</th>
                    <?php if ($isAdmin): ?> <th class="p-4 text-center w-24">Acciones</th> <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach ($songs as $s): 
                    $priText = ($s['priority'] == 'High') ? "Alta" : (($s['priority'] == 'Low') ? "Baja" : "Media");
                ?>
                <tr class="song-row hover:bg-blue-50/30 transition-all group">
                    <td class="p-4 text-center font-black text-blue-600 text-sm border-r border-slate-50/50"><?php echo $s['musical_key']; ?></td>
                    <td class="p-4">
                        <div class="text-sm font-black text-slate-800 leading-tight"><?php echo htmlspecialchars($s['title']); ?></div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter mt-0.5"><?php echo htmlspecialchars($s['artist']); ?></div>
                    </td>
                    <td class="p-4 text-center text-[11px] font-mono font-bold text-slate-500"><?php echo $s['bpm'] ?: '-'; ?></td>
                    <td class="p-4 text-center">
                        <span class="<?php echo $s['has_multitrack'] ? 'text-green-500 bg-green-50' : 'text-slate-200'; ?> font-black text-[9px] px-2 py-1 rounded-md">
                            <?php echo $s['has_multitrack'] ? 'SI' : 'NO'; ?>
                        </span>
                    </td>
                    <td class="p-4 text-center">
                        <?php if($s['has_lyrics']): ?> <a href="<?php echo $s['has_lyrics']; ?>" target="_blank" class="text-blue-500 font-black text-[10px] uppercase hover:underline">PDF</a>
                        <?php else: ?> <span class="opacity-10 text-slate-400">---</span> <?php endif; ?>
                    </td>
                    <td class="p-4 text-center">
                        <?php if($s['youtube_link']): ?> <a href="<?php echo $s['youtube_link']; ?>" target="_blank" class="text-xs hover:scale-125 transition-transform inline-block">🎬</a>
                        <?php else: ?> <span class="opacity-10 text-slate-400">---</span> <?php endif; ?>
                    </td>
                    <td class="p-4 text-center">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500"><?php echo $priText; ?></span>
                    </td>
                    <?php if ($isAdmin): ?>
                    <td class="p-4 text-center flex justify-center gap-3">
                        <button onclick='openEditModal(<?php echo json_encode($s); ?>)' class="text-slate-300 hover:text-blue-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2003/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </button>
                        <a href="?delete=<?php echo $s['id']; ?>" onclick="return confirm('¿Seguro que quieres eliminar esta canción?')" class="text-slate-300 hover:text-red-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2003/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] w-full max-w-lg shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-slate-50 p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter">Editar Canción</h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-red-500 font-black text-xl">✕</button>
        </div>
        <form method="POST" class="p-8 space-y-4">
            <input type="hidden" name="song_id" id="edit_id">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Título</label>
                    <input type="text" name="title" id="edit_title" class="w-full p-3 bg-slate-50 rounded-xl font-bold border-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Artista</label>
                    <input type="text" name="artist" id="edit_artist" class="w-full p-3 bg-slate-50 rounded-xl font-bold border-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Tono</label>
                        <input type="text" name="musical_key" id="edit_key" class="w-full p-3 bg-slate-50 rounded-xl font-black text-center uppercase border-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-400 ml-1">BPM</label>
                        <input type="number" name="bpm" id="edit_bpm" class="w-full p-3 bg-slate-50 rounded-xl font-bold text-center border-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                <div class="col-span-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Link PDF</label>
                    <input type="url" name="has_lyrics" id="edit_lyrics" class="w-full p-3 bg-slate-50 rounded-xl text-sm border-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-1">YouTube</label>
                    <input type="url" name="youtube_link" id="edit_yt" class="w-full p-3 bg-slate-50 rounded-xl text-sm border-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Prioridad</label>
                    <select name="priority" id="edit_priority" class="w-full p-3 bg-slate-50 rounded-xl font-black uppercase text-[10px] border-none focus:ring-2 focus:ring-blue-500">
                        <option value="High">Alta</option>
                        <option value="Medium">Media</option>
                        <option value="Low">Baja</option>
                    </select>
                </div>
                <div class="flex items-center justify-center bg-slate-50 rounded-xl">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <span class="text-[10px] font-black uppercase text-slate-500">Multitrack</span>
                        <input type="checkbox" name="has_multitrack" id="edit_track" value="1" class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </label>
                </div>
            </div>
            <button name="update_song" class="w-full bg-blue-600 text-white p-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-200 mt-4">Guardar Cambios</button>
        </form>
    </div>
</div>

<script>
// Buscador
document.getElementById('songSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#songTable .song-row');
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// Modal de edición
function openEditModal(song) {
    document.getElementById('edit_id').value = song.id;
    document.getElementById('edit_title').value = song.title;
    document.getElementById('edit_artist').value = song.artist;
    document.getElementById('edit_key').value = song.musical_key;
    document.getElementById('edit_bpm').value = song.bpm;
    document.getElementById('edit_lyrics').value = song.has_lyrics;
    document.getElementById('edit_yt').value = song.youtube_link;
    document.getElementById('edit_priority').value = song.priority;
    document.getElementById('edit_track').checked = (song.has_multitrack == 1);
    
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>