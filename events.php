<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_config.php';

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

<main class="container mx-auto p-4 max-w-7xl pb-20">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 mt-10 gap-6">
        <div>
            <h2 class="text-4xl font-black text-slate-900 tracking-tighter italic uppercase leading-none">Próximos Servicios</h2>
            <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.3em] mt-2">Calendario de Alabanza</p>
        </div>
        
        <?php if ($isAdmin): ?>
        <a href="add_event.php" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-2xl font-black shadow-lg shadow-blue-200 transition-all flex items-center gap-2 text-[10px] uppercase tracking-widest">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
            </svg>
            Programar Servicio
        </a>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
        $stmt = $pdo->query("SELECT id, description, event_date FROM events ORDER BY event_date ASC");
        
        if ($stmt->rowCount() == 0): ?>
            <div class="col-span-full py-20 bg-white rounded-[3rem] border-2 border-dashed border-slate-100 text-center">
                <p class="text-slate-300 font-black uppercase text-xs tracking-widest">No hay servicios programados aún.</p>
            </div>
        <?php endif; ?>

        <?php while ($row = $stmt->fetch()) : 
            $fecha = strtotime($row['event_date']);
            $dia = date('d', $fecha);
            $mes = strtoupper(date('M', $fecha));
            $año = date('Y', $fecha);
            $esPasado = ($fecha < strtotime('today'));
        ?>
            <div class="bg-white p-2 rounded-[2.8rem] shadow-xl shadow-slate-200/40 border border-slate-50 relative group transition-all hover:shadow-2xl hover:-translate-y-1 <?php echo $esPasado ? 'opacity-60' : ''; ?>">
                
                <?php if ($isAdmin): ?>
                <a href="?delete_event=<?php echo $row['id']; ?>" 
                   onclick="return confirm('¿Eliminar este servicio y todas sus asignaciones?')" 
                   class="absolute top-6 right-8 text-slate-100 hover:text-red-500 font-bold transition-all z-10 text-xl">
                    ✕
                </a>
                <?php endif; ?>

                <div class="p-6">
                    <div class="flex items-center gap-6 mb-8">
                        <div class="flex flex-col items-center justify-center bg-slate-900 text-white min-w-[85px] h-[100px] rounded-[2rem] shadow-lg shadow-slate-300">
                            <span class="text-[10px] font-black uppercase tracking-widest opacity-40"><?php echo $mes; ?></span>
                            <span class="text-4xl font-black leading-none my-1 italic"><?php echo $dia; ?></span>
                            <span class="text-[9px] font-bold opacity-30"><?php echo $año; ?></span>
                        </div>

                        <div class="flex-1">
                            <h3 class="text-xl font-black text-slate-800 uppercase leading-[1.1] tracking-tighter">
                                <?php echo htmlspecialchars($row['description']); ?>
                            </h3>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="w-2 h-2 rounded-full <?php echo $esPasado ? 'bg-slate-300' : 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.4)]'; ?>"></span>
                                <p class="text-[9px] font-black uppercase tracking-[0.2em] <?php echo $esPasado ? 'text-slate-400' : 'text-blue-600'; ?>">
                                    <?php echo $esPasado ? 'Finalizado' : 'Activo'; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid <?php echo $isAdmin ? 'grid-cols-2' : 'grid-cols-1'; ?> gap-3">
                        <?php if ($isAdmin): ?>
                        <a href="view_event.php?id=<?php echo $row['id']; ?>" 
                           class="flex items-center justify-center py-4 bg-slate-50 text-slate-500 rounded-2xl font-black text-[9px] uppercase tracking-widest hover:bg-slate-100 transition-all border border-slate-100">
                            Configurar
                        </a>
                        <?php endif; ?>
                        
                        <a href="view_event_musico.php?id=<?php echo $row['id']; ?>" 
                           class="flex items-center justify-center py-4 bg-blue-600 text-white rounded-2xl font-black text-[9px] uppercase tracking-widest shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all">
                            Ver Resumen
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<?php 
// --- LIMPIEZA DE ERRORES AL FINAL (Footer Seguro) ---
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>