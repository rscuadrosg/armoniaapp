<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bloqueo de seguridad para Admin
$currentRole = $_SESSION['user_role'] ?? 'musico';
if ($currentRole !== 'admin') {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

// Lógica para Borrar (usando la tabla 'members')
if (isset($_GET['delete_member'])) {
    $id_a_borrar = $_GET['delete_member'];
    $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
    $stmt->execute([$id_a_borrar]);
    echo "<script>window.location.href='members.php';</script>";
    exit;
}

// CONSULTA CORREGIDA: Usando 'full_name' según tu base de datos
$members = $pdo->query("SELECT id, full_name, email, role, profile_photo FROM members ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; 
?>

<main class="container mx-auto max-w-4xl p-4">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 mt-10 gap-6">
        <div>
            <h2 class="text-4xl font-black text-slate-800 tracking-tight italic uppercase leading-none">Equipo</h2>
            <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.3em] mt-2">Gestión de integrantes</p>
        </div>
        
        <a href="add_member.php" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-2xl font-black shadow-lg shadow-blue-200 transition-all flex items-center gap-2 text-[10px] uppercase tracking-widest">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            Nuevo Integrante
        </a>
    </div>

    <section class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-20">
        <?php foreach($members as $m): ?>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex justify-between items-center hover:shadow-md transition-all">
                <div class="flex items-center gap-4">
                    <?php if (!empty($m['profile_photo'])): ?>
                        <img src="uploads/profile_pics/<?php echo $m['profile_photo']; ?>" class="w-12 h-12 rounded-2xl object-cover shadow-sm">
                    <?php else: ?>
                        <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white font-black italic">
                            <?php echo strtoupper(substr($m['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <h3 class="font-black text-slate-800 uppercase text-sm tracking-tight"><?php echo htmlspecialchars($m['full_name']); ?></h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest"><?php echo htmlspecialchars($m['role']); ?></p>
                    </div>
                </div>
                
                <a href="?delete_member=<?php echo $m['id']; ?>" 
                   onclick="return confirm('¿Eliminar a este integrante?')" 
                   class="text-slate-200 hover:text-red-500 transition-colors font-bold text-xl px-2">
                   ✕
                </a>
            </div>
        <?php endforeach; ?>
    </section>
</main>

<?php 
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>