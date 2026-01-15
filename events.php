<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_config.php';

// --- 0. LÓGICA DE CREACIÓN RÁPIDA (Modal) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_base_event'])) {
    $description = $_POST['description'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '09:00';

    if (!empty($description) && !empty($event_date)) {
        try {
            $full_date = $event_date . ' ' . $event_time . ':00';
            $stmt = $pdo->prepare("INSERT INTO events (event_date, description) VALUES (?, ?)");
            $stmt->execute([$full_date, $description]);
            $event_id = $pdo->lastInsertId();
            
            // Redirección al paso 2 (Canciones)
            echo "<script>window.location.href='add_event_songs.php?id=$event_id';</script>";
            exit;
        } catch (Exception $e) {
            $error_db = $e->getMessage();
        }
    }
}

// --- 1. LÓGICA DE ELIMINACIÓN (Antes de incluir el header para evitar conflictos) ---
if (isset($_GET['delete_event'])) {
    $id_to_delete = $_GET['delete_event'];
    try {
        $pdo->beginTransaction();

        // Limpieza de tablas relacionadas (Orden de jerarquía)
        $pdo->prepare("DELETE FROM event_confirmations WHERE event_id = ?")->execute([$id_to_delete]);
        $pdo->prepare("DELETE FROM event_assignments WHERE event_id = ?")->execute([$id_to_delete]);
        $pdo->prepare("DELETE FROM event_songs WHERE event_id = ?")->execute([$id_to_delete]);
        
        // Eliminación del evento principal
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$id_to_delete]);

        $pdo->commit();
        
        // Redirección segura por JS para evitar el error 503
        echo "<script>window.location.href='events.php';</script>";
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error al eliminar el servicio: " . $e->getMessage());
    }
}

include 'header.php'; // Aquí se define $isAdmin y $currentRole
?>

<?php
// Lógica de Historial vs Próximos
$showHistory = isset($_GET['history']) && $_GET['history'] == '1';
$whereClause = $showHistory ? "event_date < CURDATE()" : "event_date >= CURDATE()";
$orderDirection = $showHistory ? "DESC" : "ASC"; // Historial: Más reciente primero. Próximos: Más cercano primero.
$titleText = $showHistory ? "Historial de Servicios" : "Próximos Servicios";
$subTitle = $showHistory ? "Eventos Pasados" : "Calendario Activo";

$stmt = $pdo->query("SELECT id, description, event_date FROM events WHERE $whereClause ORDER BY event_date $orderDirection");
?>

<main class="container mx-auto p-4 max-w-5xl pb-20">
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'saved'): ?>
        <div class="bg-green-100 text-green-700 p-4 rounded-2xl mb-6 text-center font-bold text-sm shadow-sm">
            ✅ Cambios guardados correctamente.
        </div>
    <?php endif; ?>

    <!-- Header Compacto -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 mt-4 gap-4">
        <div class="text-center md:text-left">
            <h2 class="text-2xl font-black text-slate-900 tracking-tighter italic uppercase leading-none"><?php echo $titleText; ?></h2>
            <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.3em] mt-1"><?php echo $subTitle; ?></p>
        </div>
        
        <div class="flex flex-wrap justify-center gap-2">
            <a href="?history=<?php echo $showHistory ? '0' : '1'; ?>" class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl hover:shadow-md transition-all flex items-center gap-2 h-10 font-black text-[10px] uppercase tracking-widest">
                <?php echo $showHistory ? 'Ver Próximos' : 'Ver Historial'; ?>
            </a>

            <?php if ($isAdmin): ?>
            <button onclick="document.getElementById('addEventModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-black shadow-lg shadow-blue-200 transition-all flex items-center gap-2 text-[10px] uppercase tracking-widest h-10">
                <span>+</span> Programar
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($stmt->rowCount() == 0): ?>
        <div class="py-12 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 text-center">
            <p class="text-slate-400 font-black uppercase text-xs tracking-widest">No hay eventos en esta lista.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 divide-y divide-slate-50">
            <?php while ($row = $stmt->fetch()) : 
                $fecha = strtotime($row['event_date']);
                $dia = date('d', $fecha);
                $mes = strtoupper(date('M', $fecha));
                $año = date('Y', $fecha);
                $hora = date('h:i A', $fecha);
                
                $days_es = ['Sun'=>'DOM','Mon'=>'LUN','Tue'=>'MAR','Wed'=>'MIE','Thu'=>'JUE','Fri'=>'VIE','Sat'=>'SAB'];
                $day_name = $days_es[date('D', $fecha)];
            ?>
            <div class="p-3 md:p-4 flex items-center justify-between gap-3 hover:bg-slate-50 transition-colors group">
                <div class="flex items-center gap-3 overflow-hidden flex-1">
                    <div class="bg-slate-100 text-slate-600 w-10 h-10 md:w-12 md:h-12 rounded-xl flex flex-col items-center justify-center flex-shrink-0 border border-slate-200">
                        <span class="text-[7px] md:text-[8px] font-black uppercase leading-none text-blue-500 mb-0.5"><?php echo $day_name; ?></span>
                        <span class="text-sm md:text-base font-black leading-none text-slate-800"><?php echo $dia; ?></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-black text-slate-800 text-xs md:text-sm uppercase leading-tight break-words">
                            <?php echo htmlspecialchars($row['description']); ?>
                        </h3>
                        <p class="text-[8px] md:text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">
                            <?php echo $año; ?> • <?php echo $hora; ?>
                        </p>
                    </div>
                </div>

                <div class="flex gap-2 flex-shrink-0">
                    <?php if ($isAdmin || $isLeader): ?>
                    <a href="view_event.php?id=<?php echo $row['id']; ?>" class="w-9 h-9 flex items-center justify-center bg-slate-50 text-slate-400 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all" title="Configurar">
                        ⚙️
                    </a>
                    <?php endif; ?>
                    
                    <a href="view_event_musico.php?id=<?php echo $row['id']; ?>" class="w-9 h-9 flex items-center justify-center bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all" title="Ver Resumen">
                        👁️
                    </a>

                    <?php if ($isAdmin): ?>
                    <a href="?delete_event=<?php echo $row['id']; ?>" onclick="return confirm('¿Eliminar este servicio?')" class="w-9 h-9 flex items-center justify-center bg-red-50 text-red-400 rounded-xl hover:bg-red-500 hover:text-white transition-all" title="Eliminar">
                        ✕
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</main>

<!-- Modal Nuevo Servicio -->
<div id="addEventModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-6">
        <header class="text-center mb-6">
            <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-[0.2em]">Paso 1</span>
            <h2 class="text-2xl font-black text-slate-900 mt-4 tracking-tighter italic uppercase">Nuevo <span class="text-blue-600">Servicio</span></h2>
        </header>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block tracking-widest">Nombre del Evento</label>
                <input type="text" name="description" placeholder="Ej: Servicio Dominical" required 
                       class="w-full p-3 bg-slate-50 border-2 border-transparent rounded-xl font-bold text-slate-700 focus:bg-white focus:border-blue-500/20 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block tracking-widest">Fecha</label>
                    <input type="date" name="event_date" required 
                           class="w-full p-3 bg-slate-50 border-2 border-transparent rounded-xl font-bold text-slate-700 focus:bg-white focus:border-blue-500/20 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block tracking-widest">Hora</label>
                    <input type="time" name="event_time" value="09:00" required 
                           class="w-full p-3 bg-slate-50 border-2 border-transparent rounded-xl font-bold text-slate-700 focus:bg-white focus:border-blue-500/20 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none">
                </div>
            </div>

            <button type="submit" name="create_base_event" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-xl shadow-lg shadow-blue-200 transition-all uppercase tracking-[0.2em] text-[10px] flex items-center justify-center gap-3 group">
                <span>Siguiente: Elegir Canciones</span> <span class="group-hover:translate-x-1 transition-transform">→</span>
            </button>
            <button type="button" onclick="document.getElementById('addEventModal').classList.add('hidden')" class="w-full py-3 rounded-xl font-black uppercase text-[10px] text-slate-400 hover:bg-slate-50 transition-colors">Cancelar</button>
        </form>
    </div>
</div>

<?php 
// --- LIMPIEZA DE ERRORES AL FINAL (Footer Seguro) ---
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>