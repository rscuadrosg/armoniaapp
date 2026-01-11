<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_config.php';
require_once 'auth.php';

// Bloqueo de seguridad para Admin
if (!$isAdmin) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

// Lógica para Agregar Miembro (Ahora integrada aquí)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_member'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO members (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$_POST['full_name'], $_POST['email'], $password, $_POST['role']])) {
        echo "<script>window.location.href='members.php';</script>";
        exit;
    }
}

// Lógica para Borrar (usando la tabla 'members')
if (isset($_GET['delete_member'])) {
    $id_a_borrar = $_GET['delete_member'];
    $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
    $stmt->execute([$id_a_borrar]);
    echo "<script>window.location.href='members.php';</script>";
    exit;
}

// Agrupar miembros por rol
$stmt = $pdo->query("SELECT id, full_name, email, role, profile_photo FROM members ORDER BY role ASC, full_name ASC");
$members_by_role = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $members_by_role[$row['role']][] = $row;
}

// Etiquetas amigables para los roles
$role_labels = [
    'admin' => 'Liderazgo / Admin',
    'musico' => 'Músicos / Equipo',
    'director' => 'Directores Musicales'
];

include 'header.php'; 
?>

<main class="container mx-auto max-w-4xl p-4 pb-20">
    <!-- Header Compacto -->
    <div class="flex justify-between items-center mb-6 mt-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight italic uppercase leading-none">Equipo</h2>
            <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.3em] mt-1">Gestión de integrantes</p>
        </div>
        
        <button onclick="document.getElementById('memberModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-black shadow-lg shadow-blue-200 transition-all flex items-center gap-2 text-[10px] uppercase tracking-widest">
            <span class="text-lg">+</span> <span class="hidden md:inline">Nuevo</span>
        </button>
    </div>

    <?php foreach($members_by_role as $role => $group_members): 
        $label = $role_labels[$role] ?? ucfirst($role);
    ?>
    <section class="mb-8">
        <h3 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-3 ml-1 border-b border-slate-100 pb-2"><?php echo htmlspecialchars($label); ?></h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <?php foreach($group_members as $m): ?>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center hover:shadow-md transition-all">
                <div class="flex items-center gap-3">
                    <?php if (!empty($m['profile_photo'])): ?>
                        <img src="uploads/profile_pics/<?php echo $m['profile_photo']; ?>" class="w-10 h-10 rounded-xl object-cover shadow-sm">
                    <?php else: ?>
                        <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white font-black italic text-xs">
                            <?php echo strtoupper(substr($m['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <h3 class="font-black text-slate-800 uppercase text-xs tracking-tight"><?php echo htmlspecialchars($m['full_name']); ?></h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest"><?php echo htmlspecialchars($m['role']); ?></p>
                    </div>
                </div>
                
                <a href="?delete_member=<?php echo $m['id']; ?>" 
                   onclick="return confirm('¿Eliminar a este integrante?')" 
                   class="w-8 h-8 flex items-center justify-center bg-slate-50 text-slate-300 rounded-lg hover:bg-red-50 hover:text-red-500 transition-all font-bold">
                   ✕
                </a>
            </div>
        <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>
</main>

<!-- Modal Nuevo Integrante -->
<div id="memberModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-6">
        <h3 class="text-xl font-black text-slate-800 uppercase italic mb-1">Nuevo Integrante</h3>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Registrar en el sistema</p>
        
        <form method="POST" class="space-y-4">
            <input type="text" name="full_name" placeholder="Nombre completo" required class="w-full p-3 bg-slate-50 rounded-xl font-bold text-xs text-slate-700 outline-none focus:ring-2 focus:ring-blue-500">
            <input type="email" name="email" placeholder="Correo Electrónico" required class="w-full p-3 bg-slate-50 rounded-xl font-bold text-xs text-slate-700 outline-none focus:ring-2 focus:ring-blue-500">
            <input type="password" name="password" placeholder="Contraseña Provisional" required class="w-full p-3 bg-slate-50 rounded-xl font-bold text-xs text-slate-700 outline-none focus:ring-2 focus:ring-blue-500">
            
            <div class="relative">
                <select name="role" class="w-full p-3 bg-slate-50 rounded-xl font-bold text-xs text-slate-700 outline-none appearance-none cursor-pointer">
                    <option value="musico">Músico</option>
                    <option value="admin">Administrador</option>
                </select>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-xs">▼</div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('memberModal').classList.add('hidden')" class="flex-1 py-3 rounded-xl font-black uppercase text-[10px] text-slate-400 hover:bg-slate-50 transition-colors">Cancelar</button>
                <button type="submit" name="add_member" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-black uppercase text-[10px] tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">Registrar</button>
            </div>
        </form>
    </div>
</div>

<?php 
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>