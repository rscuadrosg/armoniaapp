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

$error_message = "";

// Lógica para Agregar o Editar Miembro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_member'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    
    // Procesar array de instrumentos seleccionados (Líder)
    $leader_instrument = null;
    if (isset($_POST['leader_instruments']) && is_array($_POST['leader_instruments'])) {
        $leader_instrument = implode(',', $_POST['leader_instruments']);
    }

    // Procesar array de instrumentos que TOCA (Habilidades)
    $playable_instruments = null;
    if (isset($_POST['playable_instruments']) && is_array($_POST['playable_instruments'])) {
        $playable_instruments = implode(',', $_POST['playable_instruments']);
    }
    
    // Disponibilidad
    $max_services = $_POST['max_services_per_month'] ?? 10;
    $days = isset($_POST['available_days']) ? implode(',', $_POST['available_days']) : '0,1,2,3,4,5,6';
    
    try {
        if (!empty($_POST['member_id'])) {
            // EDICIÓN
            $id = $_POST['member_id'];
            $sql = "UPDATE members SET full_name=?, email=?, role=?, leader_instrument=?, playable_instruments=?, max_services_per_month=?, available_days=?";
            $params = [$full_name, $email, $role, $leader_instrument, $playable_instruments, $max_services, $days];
            
            if (!empty($_POST['password'])) {
                $sql .= ", password=?";
                $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            $sql .= " WHERE id=?";
            $params[] = $id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            // CREACIÓN
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO members (full_name, email, password, role, leader_instrument, playable_instruments, max_services_per_month, available_days) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $password, $role, $leader_instrument, $playable_instruments, $max_services, $days]);
        }
        
        echo "<script>window.location.href='members.php';</script>";
        exit;
    } catch (PDOException $e) {
        $error_message = "Error al guardar: " . $e->getMessage();
    }
}

// Lógica para Borrar
if (isset($_GET['delete_member'])) {
    $id_a_borrar = $_GET['delete_member'];
    $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
    $stmt->execute([$id_a_borrar]);
    echo "<script>window.location.href='members.php';</script>";
    exit;
}

// Agrupar miembros por rol
$stmt = $pdo->query("SELECT id, full_name, email, role, profile_photo, leader_instrument, playable_instruments FROM members ORDER BY role ASC, full_name ASC");
$members_by_role = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $members_by_role[$row['role']][] = $row;
}

// Etiquetas amigables para los roles
$role_labels = [
    'admin' => 'Liderazgo / Admin',
    'musico' => 'Músicos / Equipo',
    'director' => 'Directores Musicales',
    'lider' => 'Líderes de Instrumento'
];

// Obtener roles de banda disponibles para el selector
$band_roles = $pdo->query("SELECT * FROM band_roles ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; 
?>

<main class="container mx-auto max-w-4xl p-4 pb-20">
    <!-- Header Compacto -->
    <div class="flex justify-between items-center mb-6 mt-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight italic uppercase leading-none">Equipo</h2>
            <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.3em] mt-1">Gestión de integrantes</p>
        </div>
        
        <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-black shadow-lg shadow-blue-200 transition-all flex items-center gap-2 text-[10px] uppercase tracking-widest">
            <span class="text-lg">+</span> <span class="hidden md:inline">Nuevo</span>
        </button>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 text-sm font-bold"><?php echo $error_message; ?></div>
    <?php endif; ?>

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
                        
                        <?php if($m['role'] === 'lider' && !empty($m['leader_instrument'])): ?>
                            <div class="flex flex-wrap gap-1 mt-1">
                                <?php foreach(explode(',', $m['leader_instrument']) as $inst): ?>
                                    <span class="bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded text-[7px] font-black uppercase tracking-wider">
                                        <?php echo htmlspecialchars($inst); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($m['playable_instruments'])): ?>
                            <div class="flex flex-wrap gap-1 mt-1">
                                <?php foreach(explode(',', $m['playable_instruments']) as $inst): ?>
                                    <span class="bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded text-[7px] font-bold uppercase tracking-wider border border-slate-200">
                                        <?php echo htmlspecialchars($inst); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <button onclick='openModal(<?php echo json_encode($m); ?>)' class="w-8 h-8 flex items-center justify-center bg-slate-50 text-slate-400 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all">
                        ✎
                    </button>
                    <a href="?delete_member=<?php echo $m['id']; ?>" 
                       onclick="return confirm('¿Eliminar a este integrante?')" 
                       class="w-8 h-8 flex items-center justify-center bg-slate-50 text-slate-300 rounded-lg hover:bg-red-50 hover:text-red-500 transition-all font-bold">
                       ✕
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>
</main>

<!-- Modal Nuevo Integrante -->
<div id="memberModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-6">
        <h3 id="modalTitle" class="text-xl font-black text-slate-800 uppercase italic mb-1">Nuevo Integrante</h3>
        <p id="modalDesc" class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Registrar en el sistema</p>
        
        <form method="POST" class="space-y-4">
            <input type="hidden" name="member_id" id="member_id">
            <input type="text" name="full_name" id="full_name" placeholder="Nombre completo" required class="w-full p-3 bg-slate-50 rounded-xl font-bold text-xs text-slate-700 outline-none focus:ring-2 focus:ring-blue-500">
            <input type="email" name="email" id="email" placeholder="Correo Electrónico" required class="w-full p-3 bg-slate-50 rounded-xl font-bold text-xs text-slate-700 outline-none focus:ring-2 focus:ring-blue-500">
            <input type="password" name="password" id="password" placeholder="Contraseña Provisional" required class="w-full p-3 bg-slate-50 rounded-xl font-bold text-xs text-slate-700 outline-none focus:ring-2 focus:ring-blue-500">
            
            <div class="relative">
                <select name="role" id="roleSelect" onchange="toggleLeaderInput()" class="w-full p-3 bg-slate-50 rounded-xl font-bold text-xs text-slate-700 outline-none appearance-none cursor-pointer">
                    <option value="musico">Músico</option>
                    <option value="lider">Líder de Instrumento</option>
                    <option value="admin">Administrador</option>
                </select>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-xs">▼</div>
            </div>

            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block tracking-widest">Habilidades (¿Qué toca?)</label>
                <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <?php foreach($band_roles as $r): ?>
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-white p-1 rounded-lg transition-colors">
                            <input type="checkbox" name="playable_instruments[]" value="<?php echo htmlspecialchars($r['role_name']); ?>" class="w-4 h-4 accent-green-600 rounded border-slate-300">
                            <span class="text-[10px] font-bold text-slate-600 uppercase"><?php echo htmlspecialchars($r['role_name']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block tracking-widest">Disponibilidad</label>
                <div class="flex justify-between mb-3">
                    <?php $days_labels = ['D','L','M','M','J','V','S']; 
                    foreach($days_labels as $idx => $d): ?>
                        <label class="flex flex-col items-center cursor-pointer">
                            <input type="checkbox" name="available_days[]" value="<?php echo $idx; ?>" class="w-4 h-4 accent-blue-600 rounded" checked>
                            <span class="text-[9px] font-bold text-slate-500 mt-1"><?php echo $d; ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div>
                    <label class="text-[9px] font-bold text-slate-500">Máximo servicios al mes:</label>
                    <input type="number" name="max_services_per_month" id="max_services" value="4" min="1" max="30" class="w-16 p-1 text-center text-xs font-bold rounded border border-slate-200 ml-2">
                </div>
            </div>

            <div id="leaderInputDiv" class="hidden">
                <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block tracking-widest">Asignar Instrumentos a Liderar</label>
                <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <?php foreach($band_roles as $r): ?>
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-white p-1 rounded-lg transition-colors">
                            <input type="checkbox" name="leader_instruments[]" value="<?php echo htmlspecialchars($r['role_name']); ?>" class="w-4 h-4 accent-blue-600 rounded border-slate-300">
                            <span class="text-[10px] font-bold text-slate-600 uppercase"><?php echo htmlspecialchars($r['role_name']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="text-[9px] text-slate-400 mt-2 italic">Selecciona los roles que este líder podrá gestionar.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal()" class="flex-1 py-3 rounded-xl font-black uppercase text-[10px] text-slate-400 hover:bg-slate-50 transition-colors">Cancelar</button>
                <button type="submit" name="save_member" id="submitBtn" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-black uppercase text-[10px] tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">Registrar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(member = null) {
    const modal = document.getElementById('memberModal');
    const title = document.getElementById('modalTitle');
    const desc = document.getElementById('modalDesc');
    const btn = document.getElementById('submitBtn');
    const passInput = document.getElementById('password');
    
    // Limpiar checkboxes
    document.querySelectorAll('input[name="leader_instruments[]"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[name="playable_instruments[]"]').forEach(cb => cb.checked = false);

    if (member) {
        // Edit Mode
        title.innerText = "Editar Integrante";
        desc.innerText = "Modificar datos del usuario";
        btn.innerText = "Guardar Cambios";
        
        document.getElementById('member_id').value = member.id;
        document.getElementById('full_name').value = member.full_name;
        document.getElementById('email').value = member.email;
        document.getElementById('roleSelect').value = member.role;
        
        // Marcar checkboxes de Líder
        if (member.leader_instrument) {
            const roles = member.leader_instrument.split(',');
            roles.forEach(r => {
                const cb = document.querySelector(`input[name="leader_instruments[]"][value="${r.replace(/"/g, '\\"')}"]`);
                if (cb) cb.checked = true;
            });
        }

        // Marcar checkboxes de Habilidades
        if (member.playable_instruments) {
            const plays = member.playable_instruments.split(',');
            plays.forEach(r => {
                const cb = document.querySelector(`input[name="playable_instruments[]"][value="${r.replace(/"/g, '\\"')}"]`);
                if (cb) cb.checked = true;
            });
        }

        // Disponibilidad
        document.getElementById('max_services').value = member.max_services_per_month || 4;
        // Resetear días
        document.querySelectorAll('input[name="available_days[]"]').forEach(cb => cb.checked = false);
        if (member.available_days) {
            member.available_days.split(',').forEach(d => {
                const cb = document.querySelector(`input[name="available_days[]"][value="${d}"]`);
                if(cb) cb.checked = true;
            });
        } else {
            // Si es nuevo o null, marcar todos
            document.querySelectorAll('input[name="available_days[]"]').forEach(cb => cb.checked = true);
        }
        
        passInput.required = false;
        passInput.placeholder = "Dejar vacío para mantener actual";
        
    } else {
        // Add Mode
        title.innerText = "Nuevo Integrante";
        desc.innerText = "Registrar en el sistema";
        btn.innerText = "Registrar";
        
        document.getElementById('member_id').value = "";
        document.getElementById('full_name').value = "";
        document.getElementById('email').value = "";
        document.getElementById('roleSelect').value = "musico";
        
        document.getElementById('max_services').value = 4;
        document.querySelectorAll('input[name="available_days[]"]').forEach(cb => cb.checked = true);

        passInput.required = true;
        passInput.placeholder = "Contraseña Provisional";
    }
    
    toggleLeaderInput();
    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('memberModal').classList.add('hidden');
}

function toggleLeaderInput() {
    const role = document.getElementById('roleSelect').value;
    const div = document.getElementById('leaderInputDiv');
    if (role === 'lider') {
        div.classList.remove('hidden');
    } else {
        div.classList.add('hidden');
    }
}
</script>

<?php 
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>
