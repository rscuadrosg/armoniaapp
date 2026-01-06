<?php
// 1. Configuración de errores y base de datos
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_config.php';
require_once 'auth.php';

if (!$isAdmin) {
    // Si no es admin, lo mandamos al index por seguridad
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

// 4. Lógica para Agregar un nuevo Rol
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_role'])) {
    $role_name = $_POST['role_name'];
    $sort_order = $_POST['sort_order'];

    if (!empty($role_name)) {
        $stmt = $pdo->prepare("INSERT INTO band_roles (role_name, sort_order) VALUES (?, ?)");
        $stmt->execute([$role_name, $sort_order]);
        // Redirección segura para evitar error de "headers sent"
        echo "<script>window.location.href='settings_band.php';</script>";
        exit;
    }
}

// 5. Lógica para Borrar un Rol
if (isset($_GET['delete_role'])) {
    $id_a_borrar = $_GET['delete_role'];
    $stmt = $pdo->prepare("DELETE FROM band_roles WHERE id = ?");
    $stmt->execute([$id_a_borrar]);
    echo "<script>window.location.href='settings_band.php';</script>";
    exit;
}

// 6. Obtener roles actuales
$roles = $pdo->query("SELECT * FROM band_roles ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

// 7. Cargar el header después de toda la lógica
include 'header.php'; 
?>

<main class="container mx-auto max-w-2xl p-4">
    <header class="mb-10 mt-10">
        <h2 class="text-4xl font-black text-slate-800 tracking-tight italic uppercase leading-none">Estructura de Banda</h2>
        <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.3em] mt-2">Roles y Plantilla del Equipo</p>
    </header>

    <section class="bg-white p-6 rounded-[2rem] shadow-xl border border-slate-100 mb-10">
        <form method="POST" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-2 tracking-widest">Nombre del Instrumento</label>
                <input type="text" name="role_name" placeholder="Ej: Piano / Teclados" class="w-full p-4 bg-slate-50 rounded-2xl border border-slate-100 outline-none focus:ring-2 focus:ring-blue-500 font-bold text-slate-700" required>
            </div>
            <div class="w-full md:w-24">
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-2 tracking-widest">Orden</label>
                <input type="number" name="sort_order" value="0" class="w-full p-4 bg-slate-50 rounded-2xl border border-slate-100 outline-none text-center font-bold text-slate-700">
            </div>
            <div class="flex items-end">
                <button name="add_role" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-2xl font-black shadow-lg shadow-blue-100 transition-all uppercase text-[10px] tracking-widest">
                    Añadir
                </button>
            </div>
        </form>
    </section>

    <section class="space-y-4 pb-20">
        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-4 ml-2 italic">Roles en la Plantilla</h3>
        
        <?php foreach($roles as $r): ?>
            <div class="flex justify-between items-center bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:border-blue-200 transition-all">
                <div class="flex items-center gap-4">
                    <span class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center text-[10px] font-black italic">
                        <?php echo $r['sort_order']; ?>
                    </span>
                    <span class="font-black text-slate-700 text-lg uppercase tracking-tight italic"><?php echo htmlspecialchars($r['role_name']); ?></span>
                </div>
                
                <a href="?delete_role=<?php echo $r['id']; ?>" 
                   onclick="return confirm('¿Seguro que deseas eliminar este rol de la plantilla?')" 
                   class="w-10 h-10 flex items-center justify-center rounded-full text-slate-200 hover:bg-red-50 hover:text-red-500 transition-all font-bold">
                   ✕
                </a>
            </div>
        <?php endforeach; ?>
    </section>
</main>

<?php 
// Validación del footer para evitar errores visuales
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>