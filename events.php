<?php
require_once 'db_config.php';

// --- LÓGICA DE BORRADO SEGURO (TRANSACCIONAL) ---
if (isset($_GET['delete_event'])) {
    $id_a_borrar = $_GET['delete_event'];
    
    try {
        $pdo->beginTransaction();

        // 1. Limpiar todas las relaciones del evento antes de borrarlo
        $stmt1 = $pdo->prepare("DELETE FROM event_songs WHERE event_id = ?");
        $stmt1->execute([$id_a_borrar]);

        $stmt2 = $pdo->prepare("DELETE FROM event_assignments WHERE event_id = ?");
        $stmt2->execute([$id_a_borrar]);

        $stmt4 = $pdo->prepare("DELETE FROM event_confirmations WHERE event_id = ?");
        $stmt4->execute([$id_a_borrar]);

        // 2. Eliminar el evento de la tabla principal
        $stmt3 = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt3->execute([$id_a_borrar]);

        $pdo->commit();
        header("Location: events.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error al eliminar: " . $e->getMessage());
    }
}

include 'header.php'; 
?>

<main class="container mx-auto p-4 max-w-6xl pb-20">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 mt-10 gap-6">
        <div>
            <h2 class="text-4xl font-black text-slate-800 tracking-tighter italic">PRÓXIMOS SERVICIOS</h2>
            <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.3em]">Gestión de Calendario Litúrgico</p>
        </div>
        <a href="add_event.php" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-2xl font-black shadow-lg shadow-blue-200 transition-all flex items-center gap-2 text-xs uppercase tracking-widest">
            <span>+</span> Programar Servicio
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
        // Consulta corregida usando 'description'
        $stmt = $pdo->query("SELECT id, description, event_date FROM events ORDER BY event_date DESC");
        
        if ($stmt->rowCount() == 0): ?>
            <div class="col-span-full py-20 bg-white rounded-[3rem] border-2 border-dashed border-slate-200 text-center">
                <p class="text-slate-400 font-bold italic">No hay servicios programados aún.</p>
            </div>
        <?php endif; ?>

        <?php while ($row = $stmt->fetch()) : 
            $fecha = strtotime($row['event_date']);
            $dia = date('d', $fecha);
            $mes = date('M', $fecha);
            $año = date('Y', $fecha);
        ?>
            <div class="bg-white p-2 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 relative group transition-all hover:shadow-2xl hover:-translate-y-1 overflow-hidden">
                
                <a href="?delete_event=<?php echo $row['id']; ?>" 
                   onclick="return confirm('¿Estás seguro de eliminar este servicio? Se borrará el setlist y los músicos asignados.')" 
                   class="absolute top-6 right-6 text-slate-200 hover:text-red-500 font-bold transition-all z-10 text-xl">
                   ✕
                </a>

                <div class="p-6">
                    <div class="flex items-center gap-6 mb-8">
                        <div class="flex flex-col items-center justify-center bg-slate-900 text-white min-w-[85px] h-[95px] rounded-[1.8rem] shadow-lg">
                            <span class="text-[11px] font-black uppercase tracking-tighter opacity-50"><?php echo $mes; ?></span>
                            <span class="text-4xl font-black leading-none my-1"><?php echo $dia; ?></span>
                            <span class="text-[11px] font-bold opacity-40"><?php echo $año; ?></span>
                        </div>

                        <div class="flex-1">
                            <h3 class="text-xl font-black text-slate-800 uppercase leading-tight tracking-tighter">
                                <?php echo htmlspecialchars($row['description']); ?>
                            </h3>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-blue-600">Activo</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <a href="view_event.php?id=<?php echo $row['id']; ?>" 
                           class="flex items-center justify-center py-4 bg-slate-50 text-slate-600 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-all">
                            Configurar
                        </a>
                        <a href="resumen_evento.php?id=<?php echo $row['id']; ?>" 
                           class="flex items-center justify-center py-4 bg-blue-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all">
                            Ver Resumen
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<footer class="mt-20 py-10 text-center text-slate-300 text-[10px] font-black uppercase tracking-[0.5em]">
    ArmoníaApp • Sistema de Gestión de Alabanza
</footer>

</body>
</html>