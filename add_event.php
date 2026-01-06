<?php
// 1. Activar reporte de errores para ver exactamente qué falla
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_config.php';
require_once 'auth.php';

if (!$isAdmin) {
    echo "<script>window.location.href='events.php';</script>";
    exit;
}

// 3. Lógica de procesamiento de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_base_event'])) {
    $description = $_POST['description'] ?? '';
    $event_date = $_POST['event_date'] ?? '';

    if (!empty($description) && !empty($event_date)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO events (event_date, description) VALUES (?, ?)");
            $stmt->execute([$event_date, $description]);
            $event_id = $pdo->lastInsertId();
            
            // REDIRECCIÓN SEGURA POR JAVASCRIPT
            echo "<script>window.location.href='add_event_songs.php?id=$event_id';</script>";
            exit;
        } catch (Exception $e) {
            $error_db = $e->getMessage();
        }
    }
}

// 4. Cargar el header solo después de procesar la lógica pesada
include 'header.php';
?>

<div class="min-h-[70vh] flex items-center justify-center p-4">
    <div class="bg-white p-10 md:p-14 rounded-[3.5rem] shadow-2xl shadow-slate-200 w-full max-w-lg border border-slate-100 relative overflow-hidden">
        
        <header class="text-center mb-10">
            <span class="bg-blue-100 text-blue-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-[0.2em]">Paso 1</span>
            <h2 class="text-4xl font-black text-slate-900 mt-6 tracking-tighter italic uppercase">Nuevo <span class="text-blue-600">Servicio</span></h2>
        </header>
        
        <?php if(isset($error_db)): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-xs font-bold uppercase text-center">
                Error de Base de Datos: <?php echo $error_db; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block tracking-widest">Nombre del Evento</label>
                <input type="text" name="description" placeholder="Ej: Servicio Dominical" required 
                       class="w-full p-5 bg-slate-50 border-2 border-transparent rounded-[1.8rem] font-bold text-slate-700 focus:bg-white focus:border-blue-500/20 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none">
            </div>

            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block tracking-widest">Fecha</label>
                <input type="date" name="event_date" required 
                       class="w-full p-5 bg-slate-50 border-2 border-transparent rounded-[1.8rem] font-bold text-slate-700 focus:bg-white focus:border-blue-500/20 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none">
            </div>

            <button type="submit" name="create_base_event" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-5 rounded-[1.8rem] shadow-xl shadow-blue-200 transition-all uppercase tracking-[0.2em] text-[11px] flex items-center justify-center gap-3 group">
                <span>Siguiente: Elegir Canciones</span>
                <span class="group-hover:translate-x-1 transition-transform">→</span>
            </button>
        </form>
    </div>
</div>

<?php 
// Solo incluir si el archivo existe, si no, cerrar etiquetas manualmente
if (file_exists('footer.php')) {
    include 'footer.php';
} else {
    echo "</body></html>";
}
?>