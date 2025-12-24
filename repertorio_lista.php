<?php
require_once 'db_config.php';

// Procesamiento de nueva canción
if (isset($_POST['add_song'])) {
    $stmt = $pdo->prepare("INSERT INTO songs (title, artist, musical_key, youtube_link, bpm, has_multitrack, has_lyrics, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['title'], 
        $_POST['artist'], 
        $_POST['musical_key'], 
        $_POST['youtube_link'], 
        $_POST['bpm'], 
        isset($_POST['has_multitrack']) ? 1 : 0, 
        $_POST['has_lyrics'], 
        $_POST['priority']
    ]);
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

    <form method="POST" class="bg-[#13192b] p-4 rounded-[2rem] mb-8 shadow-xl">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            
            <input type="text" name="title" placeholder="Título" class="md:col-span-2 p-3 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none" required>
            <input type="text" name="artist" placeholder="Artista" class="md:col-span-2 p-3 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none" required>
            <input type="text" name="musical_key" placeholder="Tono" class="md:col-span-1 p-3 rounded-xl text-xs text-center font-black uppercase focus:ring-2 focus:ring-blue-500 outline-none" required>
            <input type="number" name="bpm" placeholder="BPM" class="md:col-span-1 p-3 rounded-xl text-xs text-center font-bold focus:ring-2 focus:ring-blue-500 outline-none" required>
            
            <input type="url" name="has_lyrics" placeholder="Link PDF" class="md:col-span-1 p-3 rounded-xl text-[10px] focus:ring-2 focus:ring-blue-500 outline-none">
            <input type="url" name="youtube_link" placeholder="Link YT" class="md:col-span-1 p-3 rounded-xl text-[10px] focus:ring-2 focus:ring-blue-500 outline-none">
            
            <select name="priority" class="md:col-span-1 p-3 rounded-xl text-[10px] font-black uppercase outline-none cursor-pointer" required>
                <option value="" disabled selected>Prioridad</option>
                <option value="High">Alta</option>
                <option value="Medium">Media</option>
                <option value="Low">Baja</option>
            </select>

            <div class="md:col-span-1 flex flex-col items-center justify-center bg-slate-800/50 py-1 rounded-xl border border-slate-700">
                <span class="text-[7px] font-black text-slate-500 uppercase tracking-widest mb-1">Multitrack</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="has_multitrack" value="1" class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <button name="add_song" class="md:col-span-1 bg-blue-600 text-white p-3 rounded-xl text-lg font-black hover:bg-blue-500 transition-all shadow-lg shadow-blue-900/40">
                ＋
            </button>
        </div>
    </form>

    <div class="bg-white rounded-[2rem] shadow-2xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <table class="w-full text-left border-collapse table-auto" id="songTable">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black uppercase text-slate-400">
                    <th class="p-4 w-16 text-center border-r border-slate-100">Tono</th>
                    <th class="p-4">Canción / Artista</th>
                    <th class="p-4 text-center">BPM</th>
                    <th class="p-4 text-center">Multitrack</th>
                    <th class="p-4 text-center">PDF</th>
                    <th class="p-4 text-center">YT</th>
                    <th class="p-4 text-center">Prioridad</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach ($songs as $s): 
                    $priText = "Media";
                    if($s['priority'] == 'High') $priText = "Alta";
                    if($s['priority'] == 'Low') $priText = "Baja";
                ?>
                <tr class="song-row hover:bg-blue-50/30 transition-all group">
                    <td class="p-4 text-center font-black text-blue-600 text-sm border-r border-slate-50/50"><?php echo $s['musical_key']; ?></td>
                    <td class="p-4">
                        <div class="text-sm font-black text-slate-800 leading-tight"><?php echo htmlspecialchars($s['title']); ?></div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter mt-0.5"><?php echo htmlspecialchars($s['artist']); ?></div>
                    </td>
                    <td class="p-4 text-center text-[11px] font-mono font-bold text-slate-500"><?php echo $s['bpm'] ?: '-'; ?></td>
                    <td class="p-4 text-center">
                        <?php if($s['has_multitrack']): ?>
                            <span class="text-green-500 font-black text-[9px] bg-green-50 px-2 py-1 rounded-md">SI</span>
                        <?php else: ?>
                            <span class="text-slate-200 font-black text-[9px]">NO</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-center">
                        <?php if($s['has_lyrics']): ?>
                            <a href="<?php echo $s['has_lyrics']; ?>" target="_blank" class="text-blue-500 font-black text-[10px] uppercase hover:underline">PDF</a>
                        <?php else: ?>
                            <span class="opacity-10 text-slate-400">---</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-center">
                        <?php if($s['youtube_link']): ?>
                            <a href="<?php echo $s['youtube_link']; ?>" target="_blank" class="text-xs hover:scale-125 transition-transform inline-block">🎬</a>
                        <?php else: ?>
                            <span class="opacity-10 text-slate-400">---</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-center">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                            <?php echo $priText; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('songSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#songTable .song-row');
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>