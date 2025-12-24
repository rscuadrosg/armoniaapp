<?php
require_once 'db_config.php';

if (isset($_GET['confirm_delete'])) {
    $stmt = $pdo->prepare("DELETE FROM songs WHERE id = ?");
    $stmt->execute([$_GET['confirm_delete']]);
    header("Location: repertorio_borrar.php?msg=deleted"); exit;
}

$songs = $pdo->query("SELECT * FROM songs ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
include 'header.php';
?>

<div class="container mx-auto px-4 max-w-4xl pb-20">
    <header class="mb-10 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-red-600 tracking-tighter uppercase">Zona de Peligro</h1>
            <p class="text-slate-400 text-xs font-bold uppercase">Eliminación definitiva de canciones</p>
        </div>
        <a href="index.php" class="bg-slate-900 text-white px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest">
            ← Volver al Panel
        </a>
    </header>

    <?php if(isset($_GET['msg'])): ?>
        <div class="bg-green-100 text-green-700 p-4 rounded-2xl mb-6 text-center font-bold text-sm">
            Canción eliminada correctamente del sistema.
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-[2.5rem] shadow-2xl border-2 border-red-50 overflow-hidden">
        <div class="p-6 bg-red-50 border-b border-red-100">
            <p class="text-red-700 text-xs font-bold italic">⚠️ Nota: Las canciones eliminadas aquí desaparecerán de todos los eventos pasados y futuros.</p>
        </div>
        
        <div class="divide-y divide-slate-100">
            <?php foreach ($songs as $s): ?>
                <div class="p-6 flex justify-between items-center hover:bg-red-50/30 transition-all group">
                    <div>
                        <h3 class="font-black text-slate-800"><?php echo htmlspecialchars($s['title']); ?></h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase"><?php echo htmlspecialchars($s['artist']); ?></p>
                    </div>
                    
                    <a href="?confirm_delete=<?php echo $s['id']; ?>" 
                       onclick="return confirm('¿ESTÁS ABSOLUTAMENTE SEGURO? Esta acción no se puede deshacer.')"
                       class="bg-white text-red-600 border border-red-200 px-4 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-red-600 hover:text-white transition-all shadow-sm">
                        Eliminar Definitivamente
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>