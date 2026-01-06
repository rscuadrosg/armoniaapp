<?php
require_once 'db_config.php';
require_once 'auth.php';

if (!$isAdmin) { header("Location: index.php"); exit; }

// Agregar Etiqueta
if (isset($_POST['add_tag'])) {
    $stmt = $pdo->prepare("INSERT INTO tags (name, color_class) VALUES (?, ?)");
    $stmt->execute([$_POST['name'], $_POST['color_class']]);
    header("Location: settings_tags.php"); exit;
}

// Eliminar Etiqueta
if (isset($_GET['del'])) {
    $pdo->prepare("DELETE FROM tags WHERE id = ?")->execute([$_GET['del']]);
    header("Location: settings_tags.php"); exit;
}

$tags = $pdo->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Preajustes de colores Tailwind
$colors = [
    'bg-slate-100 text-slate-600 border-slate-200' => 'Gris (Neutro)',
    'bg-red-100 text-red-600 border-red-200' => 'Rojo (Urgente)',
    'bg-orange-100 text-orange-600 border-orange-200' => 'Naranja (Atención)',
    'bg-amber-100 text-amber-600 border-amber-200' => 'Amarillo (Advertencia)',
    'bg-green-100 text-green-600 border-green-200' => 'Verde (Aprobado)',
    'bg-emerald-100 text-emerald-600 border-emerald-200' => 'Esmeralda (Fresco)',
    'bg-teal-100 text-teal-600 border-teal-200' => 'Turquesa (Calma)',
    'bg-cyan-100 text-cyan-600 border-cyan-200' => 'Cian (Agua)',
    'bg-blue-100 text-blue-600 border-blue-200' => 'Azul (Info)',
    'bg-indigo-100 text-indigo-600 border-indigo-200' => 'Índigo (Profundo)',
    'bg-violet-100 text-violet-600 border-violet-200' => 'Violeta (Creativo)',
    'bg-purple-100 text-purple-600 border-purple-200' => 'Púrpura (Real)',
    'bg-fuchsia-100 text-fuchsia-600 border-fuchsia-200' => 'Fucsia (Vibrante)',
    'bg-pink-100 text-pink-600 border-pink-200' => 'Rosa (Suave)',
    'bg-rose-100 text-rose-600 border-rose-200' => 'Rosa (Intenso)',
];

include 'header.php';
?>

<div class="container mx-auto max-w-2xl p-4 pb-20">
    <header class="mb-10 mt-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Gestión de Etiquetas</h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Categorías dinámicas</p>
        </div>
        <a href="repertorio_lista.php" class="bg-slate-100 text-slate-500 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200">Volver</a>
    </header>

    <div class="bg-white p-6 rounded-[2rem] shadow-xl border border-slate-100 mb-8">
        <h2 class="text-xs font-black uppercase text-slate-400 mb-4 tracking-widest">Nueva Etiqueta</h2>
        <form method="POST" class="flex flex-col md:flex-row gap-3">
            <input type="text" name="name" placeholder="Nombre (Ej: Navidad)" required class="flex-1 p-3 bg-slate-50 rounded-xl font-bold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500">
            
            <select name="color_class" class="flex-1 p-3 bg-slate-50 rounded-xl font-bold text-slate-600 outline-none cursor-pointer">
                <?php foreach($colors as $class => $name): ?>
                    <option value="<?php echo $class; ?>"><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>
            
            <button name="add_tag" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
                Crear
            </button>
        </form>
    </div>

    <div class="grid gap-3">
        <?php foreach($tags as $t): ?>
            <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border <?php echo $t['color_class']; ?>">
                        <?php echo htmlspecialchars($t['name']); ?>
                    </span>
                    <span class="text-xs font-bold text-slate-400">ID: <?php echo $t['id']; ?></span>
                </div>
                
                <a href="?del=<?php echo $t['id']; ?>" onclick="return confirm('¿Eliminar esta etiqueta? Se quitará de todas las canciones.')" class="text-slate-300 hover:text-red-500 font-bold px-2">✕</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>