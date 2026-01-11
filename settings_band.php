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

// --- AJAX: Reordenamiento ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_action']) && $_POST['ajax_action'] == 'reorder') {
    $order = json_decode($_POST['order'], true);
    if (is_array($order)) {
        $stmt = $pdo->prepare("UPDATE band_roles SET sort_order = ? WHERE id = ?");
        foreach ($order as $position => $id) {
            $stmt->execute([$position + 1, $id]);
        }
    }
    echo "OK";
    exit;
}

// 4. Lógica para Agregar/Editar Rol
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_role'])) {
    $role_name = $_POST['role_name'];
    $role_id = $_POST['role_id'] ?? '';

    if (!empty($role_name)) {
        if (!empty($role_id)) {
            // Editar
            $stmt = $pdo->prepare("UPDATE band_roles SET role_name = ? WHERE id = ?");
            $stmt->execute([$role_name, $role_id]);
        } else {
            // Agregar (Obtener último orden)
            $max = $pdo->query("SELECT MAX(sort_order) FROM band_roles")->fetchColumn();
            $sort_order = $max ? $max + 1 : 1;
            
            $stmt = $pdo->prepare("INSERT INTO band_roles (role_name, sort_order) VALUES (?, ?)");
            $stmt->execute([$role_name, $sort_order]);
        }
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

<main class="container mx-auto max-w-2xl p-4 pb-20">
    <!-- Header Compacto -->
    <div class="flex justify-between items-center mb-6 mt-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight italic uppercase leading-none">Estructura de Banda</h2>
            <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.3em] mt-1">Roles y Plantilla del Equipo</p>
        </div>
        
        <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-black shadow-lg shadow-blue-200 transition-all flex items-center gap-2 text-[10px] uppercase tracking-widest">
            <span class="text-lg">+</span> <span class="hidden md:inline">Nuevo Rol</span>
        </button>
    </div>

    <section class="space-y-3 pb-20" id="rolesList">
        <p class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-4 ml-2 italic">Arrastra para ordenar</p>
        
        <?php foreach($roles as $r): ?>
            <div class="role-item flex justify-between items-center bg-white p-3 rounded-xl shadow-sm border border-slate-100 hover:border-blue-200 transition-all cursor-move" 
                 draggable="true" 
                 data-id="<?php echo $r['id']; ?>">
                
                <div class="flex items-center gap-4">
                    <span class="w-6 h-6 bg-slate-100 text-slate-400 rounded-md flex items-center justify-center text-[10px] font-black cursor-grab">
                        ☰
                    </span>
                    <span class="font-black text-slate-700 text-sm uppercase tracking-tight italic"><?php echo htmlspecialchars($r['role_name']); ?></span>
                </div>
                
                <div class="flex gap-2">
                    <button onclick='openModal(<?php echo json_encode($r); ?>)' class="w-8 h-8 flex items-center justify-center bg-slate-50 text-slate-400 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all">
                        ✎
                    </button>
                    <a href="?delete_role=<?php echo $r['id']; ?>" 
                       onclick="return confirm('¿Seguro que deseas eliminar este rol?')" 
                       class="w-8 h-8 flex items-center justify-center bg-slate-50 text-slate-300 rounded-lg hover:bg-red-50 hover:text-red-500 transition-all font-bold">
                       ✕
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</main>

<!-- Modal Rol -->
<div id="roleModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-6">
        <h3 id="modalTitle" class="text-xl font-black text-slate-800 uppercase italic mb-1">Nuevo Rol</h3>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Configuración de instrumento</p>
        
        <form method="POST" class="space-y-4">
            <input type="hidden" name="role_id" id="role_id">
            
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-2 tracking-widest">Nombre del Instrumento</label>
                <input type="text" name="role_name" id="role_name" placeholder="Ej: Saxofón" required class="w-full p-3 bg-slate-50 rounded-xl font-bold text-xs text-slate-700 outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('roleModal').classList.add('hidden')" class="flex-1 py-3 rounded-xl font-black uppercase text-[10px] text-slate-400 hover:bg-slate-50 transition-colors">Cancelar</button>
                <button type="submit" name="save_role" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-black uppercase text-[10px] tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(role = null) {
    const modal = document.getElementById('roleModal');
    const title = document.getElementById('modalTitle');
    const inputId = document.getElementById('role_id');
    const inputName = document.getElementById('role_name');

    if (role) {
        title.innerText = "Editar Rol";
        inputId.value = role.id;
        inputName.value = role.role_name;
    } else {
        title.innerText = "Nuevo Rol";
        inputId.value = "";
        inputName.value = "";
    }
    modal.classList.remove('hidden');
}

// Drag and Drop Logic
const list = document.getElementById('rolesList');
let draggedItem = null;

list.addEventListener('dragstart', (e) => {
    draggedItem = e.target;
    e.target.classList.add('opacity-50');
});

list.addEventListener('dragend', (e) => {
    e.target.classList.remove('opacity-50');
    saveOrder();
});

list.addEventListener('dragover', (e) => {
    e.preventDefault();
    const afterElement = getDragAfterElement(list, e.clientY);
    const draggable = document.querySelector('.opacity-50');
    if (afterElement == null) {
        list.appendChild(draggable);
    } else {
        list.insertBefore(draggable, afterElement);
    }
});

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.role-item:not(.opacity-50)')];

    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function saveOrder() {
    const items = list.querySelectorAll('.role-item');
    const order = Array.from(items).map(item => item.dataset.id);
    
    const formData = new FormData();
    formData.append('ajax_action', 'reorder');
    formData.append('order', JSON.stringify(order));

    fetch('settings_band.php', {
        method: 'POST',
        body: formData
    }).then(response => {
        if(response.ok) {
            console.log('Orden guardado');
        }
    });
}
</script>

<?php 
// Validación del footer para evitar errores visuales
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>