<?php
// 1. Reporte de errores para identificar por qué sale el Error 500
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Conexión a la base de datos
require_once 'db_config.php';

// 3. Lógica para Agregar un nuevo Rol
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_role'])) {
    $role_name = $_POST['role_name'];
    $sort_order = $_POST['sort_order'];

    if (!empty($role_name)) {
        $stmt = $pdo->prepare("INSERT INTO band_roles (role_name, sort_order) VALUES (?, ?)");
        $stmt->execute([$role_name, $sort_order]);
        header("Location: settings_band.php");
        exit;
    }
}

// 4. Lógica para Borrar un Rol
if (isset($_GET['delete_role'])) {
    $id_a_borrar = $_GET['delete_role'];
    $stmt = $pdo->prepare("DELETE FROM band_roles WHERE id = ?");
    $stmt->execute([$id_a_borrar]);
    header("Location: settings_band.php");
    exit;
}

// 5. Obtener los roles actuales de la base de datos
// Si esto falla, el error 500 es porque la tabla no existe en la DB
$roles = $pdo->query("SELECT * FROM band_roles ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'header.php'; ?>
<body class="bg-slate-50 min-h-screen">

    <main class="container mx-auto max-w-2xl p-4">
        <header class="mb-10">
            <h2 class="text-4xl font-black text-slate-800 tracking-tight">Estructura de Banda</h2>
            <p class="text-slate-500 font-medium">Define los instrumentos fijos que conforman tu equipo de alabanza.</p>
        </header>

        <section class="bg-white p-6 rounded-[2rem] shadow-xl border border-slate-100 mb-10">
            <form method="POST" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-2 tracking-widest">Nombre del Instrumento / Rol</label>
                    <input type="text" name="role_name" placeholder="Ej: Guitarra Eléctrica" class="w-full p-4 bg-slate-50 rounded-2xl border border-slate-100 outline-none focus:ring-2 focus:ring-blue-500 font-bold text-slate-700" required>
                </div>
                <div class="w-full md:w-24">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-2 tracking-widest">Orden</label>
                    <input type="number" name="sort_order" value="0" class="w-full p-4 bg-slate-50 rounded-2xl border border-slate-100 outline-none text-center font-bold text-slate-700">
                </div>
                <div class="flex items-end">
                    <button name="add_role" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-2xl font-black shadow-lg shadow-blue-100 transition-all uppercase text-xs tracking-widest">
                        Añadir
                    </button>
                </div>
            </form>
        </section>

        <section class="space-y-4">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-4 ml-2">Roles en la Plantilla</h3>
            
            <?php if (empty($roles)): ?>
                <div class="text-center p-10 bg-slate-100 rounded-[2rem] border-2 border-dashed border-slate-200 text-slate-400 font-medium italic">
                    Aún no has definido instrumentos. ¡Comienza agregando uno arriba!
                </div>
            <?php endif; ?>

            <?php foreach($roles as $r): ?>
                <div class="flex justify-between items-center bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:border-blue-200 transition-all group">
                    <div class="flex items-center gap-4">
                        <span class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-[10px] font-black text-slate-400">
                            <?php echo $r['sort_order']; ?>
                        </span>
                        <span class="font-bold text-slate-700 text-lg uppercase tracking-tight"><?php echo htmlspecialchars($r['role_name']); ?></span>
                    </div>
                    
                    <a href="?delete_role=<?php echo $r['id']; ?>" 
                       onclick="return confirm('¿Eliminar este instrumento de la plantilla?')" 
                       class="w-10 h-10 flex items-center justify-center rounded-full text-slate-200 hover:bg-red-50 hover:text-red-500 transition-all font-bold">
                       ✕
                    </a>
                </div>
            <?php endforeach; ?>
        </section>
    </main>

</body>
</html>