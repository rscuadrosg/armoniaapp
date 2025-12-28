<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_config.php';

// Iniciamos sesión para validar el rol antes de cargar el header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que tenemos un ID de evento (viniendo de id o event_id)
$event_id = $_GET['id'] ?? $_GET['event_id'] ?? null;

if (!$event_id) {
    die("Error: No se especificó un ID de evento.");
}

// --- PROCESAR GUARDADO ---
if (isset($_POST['save_setlist'])) {
    try {
        // 1. Limpiar setlist anterior para evitar duplicados
        $stmt_del = $pdo->prepare("DELETE FROM event_songs WHERE event_id = ?");
        $stmt_del->execute([$event_id]);
        
        // 2. Insertar nuevas canciones
        if (!empty($_POST['selected_songs'])) {
            $stmt_ins = $pdo->prepare("INSERT INTO event_songs (event_id, song_id, position) VALUES (?, ?, ?)");
            foreach ($_POST['selected_songs'] as $index => $song_id) {
                $stmt_ins->execute([$event_id, $song_id, $index + 1]);
            }
        }
        
        // REDIRECCIÓN SEGURA: Usamos JS para evitar errores de cabecera y 503
        echo "<script>window.location.href='view_event.php?id=" . $event_id . "';</script>";
        exit;
    } catch (PDOException $e) {
        die("Error en la base de datos: " . $e->getMessage());
    }
}

// Obtener info del evento y canciones
$evento = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$evento->execute([$event_id]);
$info_evento = $evento->fetch();

$songs = $pdo->query("SELECT id, title, artist, musical_key FROM songs ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; // Aquí se define $isAdmin y el diseño del nav
?>

<div class="container mx-auto px-4 max-w-2xl py-12">
    <div class="mb-10 text-center">
        <span class="bg-blue-100 text-blue-600 px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">Paso 2: Selección de Repertorio</span>
        <h1 class="text-4xl font-black text-slate-900 tracking-tighter uppercase mt-4 italic">Planificar Setlist</h1>
        <p class="text-slate-400 font-bold text-sm uppercase tracking-widest mt-2">
            Para: <span class="text-slate-800"><?php echo htmlspecialchars($info_evento['description'] ?? 'Evento'); ?></span>
        </p>
    </div>

    <div class="relative mb-8">
        <select id="songPicker" class="w-full p-5 bg-white border border-slate-200 rounded-[2rem] shadow-sm font-bold text-slate-700 outline-none focus:ring-4 focus:ring-blue-500/10 appearance-none cursor-pointer">
            <option value="">+ Agregar canción al setlist...</option>
            <?php foreach($songs as $s): ?>
                <option value="<?php echo $s['id']; ?>" 
                        data-title="<?php echo htmlspecialchars($s['title']); ?>" 
                        data-artist="<?php echo htmlspecialchars($s['artist'] ?? 'Autor Desconocido'); ?>"
                        data-key="<?php echo $s['musical_key']; ?>">
                    <?php echo htmlspecialchars($s['title']); ?> (<?php echo $s['musical_key']; ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">▼</div>
    </div>

    <form method="POST">
        <div id="setlistContainer" class="space-y-3 mb-10">
            </div>

        <button type="submit" name="save_setlist" class="w-full bg-slate-900 text-white py-6 rounded-[2.5rem] font-black text-xs uppercase tracking-[0.2em] shadow-2xl hover:bg-blue-600 transition-all transform hover:-translate-y-1">
            Confirmar y Asignar Músicos
        </button>
    </form>
</div>

<script>
const songPicker = document.getElementById('songPicker');
const container = document.getElementById('setlistContainer');

songPicker.addEventListener('change', function() {
    if (this.value === "") return;

    const option = this.options[this.selectedIndex];
    const id = this.value;
    const title = option.getAttribute('data-title');
    const artist = option.getAttribute('data-artist');
    const key = option.getAttribute('data-key');

    const row = document.createElement('div');
    row.className = "flex items-center justify-between p-5 bg-white rounded-[2rem] border border-slate-100 shadow-sm animate-in fade-in slide-in-from-bottom-2 duration-300";
    row.innerHTML = `
        <input type="hidden" name="selected_songs[]" value="${id}">
        <div class="flex items-center gap-4">
            <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-[10px] font-black">
                ${container.children.length + 1}
            </div>
            <div>
                <p class="font-black text-slate-800 text-sm uppercase">${title}</p>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter">${artist}</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="px-3 py-1 bg-slate-100 rounded-lg text-slate-600 font-black text-[10px] uppercase">${key}</div>
            <button type="button" onclick="this.parentElement.parentElement.remove(); updateNumbers();" class="text-slate-200 hover:text-red-500 font-bold px-2 transition-colors">✕</button>
        </div>
    `;

    container.appendChild(row);
    this.value = ""; 
});

function updateNumbers() {
    const rows = container.querySelectorAll('.w-8.h-8');
    rows.forEach((div, index) => {
        div.innerText = index + 1;
    });
}
</script>

<?php 
// Validación para evitar el error de archivo no encontrado visto en la captura
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>