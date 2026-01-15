<?php
require_once 'db_config.php';
require_once 'auth.php';

if (!$isAdmin) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $app_name = $_POST['app_name'];
    $pdo->prepare("UPDATE app_settings SET setting_value = ? WHERE setting_key = 'app_name'")->execute([$app_name]);

    // Subir Logo
    if (!empty($_FILES['logo']['name'])) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $filename = "logo_" . time() . "." . $ext;
        move_uploaded_file($_FILES['logo']['tmp_name'], "uploads/" . $filename);
        $pdo->prepare("UPDATE app_settings SET setting_value = ? WHERE setting_key = 'logo_path'")->execute([$filename]);
    }

    // Subir Favicon
    if (!empty($_FILES['favicon']['name'])) {
        $ext = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
        $filename = "favicon_" . time() . "." . $ext;
        move_uploaded_file($_FILES['favicon']['tmp_name'], "uploads/" . $filename);
        $pdo->prepare("UPDATE app_settings SET setting_value = ? WHERE setting_key = 'favicon_path'")->execute([$filename]);
    }
    
    header("Location: settings_general.php");
    exit;
}

// Cargar configuración
$settings = [];
$stmt = $pdo->query("SELECT * FROM app_settings");
while ($row = $stmt->fetch()) $settings[$row['setting_key']] = $row['setting_value'];

include 'header.php';
?>

<div class="container mx-auto max-w-2xl p-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-800 mb-1">Configuración General</h1>
            <p class="text-slate-400 text-sm font-bold uppercase tracking-widest">Personaliza la identidad</p>
        </div>
        <a href="index.php" class="bg-white border border-slate-200 text-slate-500 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm">
            ← Volver
        </a>
    </div>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-[2rem] shadow-xl border border-slate-100 space-y-6">
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nombre de la Aplicación</label>
            <input type="text" name="app_name" value="<?php echo htmlspecialchars($settings['app_name'] ?? 'ArmoniaApp'); ?>" class="w-full p-4 bg-slate-50 rounded-xl font-bold text-slate-700 outline-none">
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Logo (PNG/JPG)</label>
                <?php if(!empty($settings['logo_path'])): ?>
                    <img src="uploads/<?php echo $settings['logo_path']; ?>" class="h-20 mb-2 object-contain bg-slate-100 rounded-lg p-2">
                <?php endif; ?>
                <input type="file" name="logo" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Favicon (ICO/PNG)</label>
                <?php if(!empty($settings['favicon_path'])): ?>
                    <img src="uploads/<?php echo $settings['favicon_path']; ?>" class="h-8 w-8 mb-2 object-contain">
                <?php endif; ?>
                <input type="file" name="favicon" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
        </div>

        <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all">
            Guardar Cambios
        </button>
    </form>
</div>

<?php 
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>