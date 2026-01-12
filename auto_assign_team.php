<?php
require_once 'db_config.php';
require_once 'auth.php';

if (!$isAdmin && !$isLeader) {
    header("Location: index.php");
    exit;
}

$message = "";

// --- LÓGICA DE LÍDER ---
$my_leader_instruments = [];
if ($isLeader) {
    $stmt_lider = $pdo->prepare("SELECT leader_instrument FROM members WHERE id = ?");
    $stmt_lider->execute([$currentUserId]);
    $str = $stmt_lider->fetchColumn();
    if ($str) $my_leader_instruments = explode(',', $str);
}

// --- HELPER: Filtro Inteligente (Misma lógica que view_event) ---
function is_eligible($member, $role_name) {
    $instruments = $member['playable_instruments'];
    if (empty($instruments)) return false;
    $member_insts = explode(',', $instruments);
    $keywords = ['guitarra', 'coro', 'voz', 'saxo', 'trompeta', 'violin', 'flauta', 'chelo'];
    foreach ($member_insts as $inst) {
        if (stripos($role_name, $inst) !== false || stripos($inst, $role_name) !== false) return true;
        foreach ($keywords as $kw) {
            if (stripos($role_name, $kw) !== false && stripos($inst, $kw) !== false) return true;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // 1. Obtener Eventos en el rango
    $events = $pdo->prepare("SELECT id, event_date FROM events WHERE event_date BETWEEN ? AND ? ORDER BY event_date ASC");
    $events->execute([$start_date, $end_date]);
    $events_list = $events->fetchAll(PDO::FETCH_ASSOC);

    // 2. Obtener Roles de Banda
    $roles = $pdo->query("SELECT * FROM band_roles ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

    // 3. Obtener Miembros con sus reglas
    $members = $pdo->query("SELECT id, full_name, playable_instruments, available_days, max_services_per_month FROM members WHERE is_available = 1")->fetchAll(PDO::FETCH_ASSOC);

    // 4. Pre-calcular conteo de servicios por mes para cada miembro (para respetar límites)
    // Esto es complejo porque abarca múltiples meses. Haremos un array $usage[mes][member_id]
    $usage = [];
    
    // Cargar uso actual de la base de datos
    $sql_usage = "SELECT member_id, MONTH(e.event_date) as m, YEAR(e.event_date) as y, COUNT(*) as c 
                  FROM event_assignments ea 
                  JOIN events e ON ea.event_id = e.id 
                  WHERE e.event_date BETWEEN ? AND ? 
                  GROUP BY member_id, m, y";
    $stmt_usage = $pdo->prepare($sql_usage);
    $stmt_usage->execute([$start_date, $end_date]);
    while($row = $stmt_usage->fetch(PDO::FETCH_ASSOC)) {
        $key = $row['y'] . '-' . $row['m'];
        $usage[$key][$row['member_id']] = $row['c'];
    }

    $assigned_count = 0;

    foreach ($events_list as $ev) {
        $event_id = $ev['id'];
        $date_ts = strtotime($ev['event_date']);
        $day_of_week = date('w', $date_ts); // 0-6
        $month_key = date('Y-n', $date_ts);

        // Obtener asignaciones actuales para no sobrescribir
        $current_assignments = $pdo->prepare("SELECT instrument FROM event_assignments WHERE event_id = ?");
        $current_assignments->execute([$event_id]);
        $filled_roles = $current_assignments->fetchAll(PDO::FETCH_COLUMN);

        foreach ($roles as $role) {
            $role_name = $role['role_name'];

            // Si ya está asignado, saltar
            if (in_array($role_name, $filled_roles)) continue;

            // Si soy líder, solo proceso mis instrumentos
            if ($isLeader && !in_array($role_name, $my_leader_instruments)) continue;

            // Buscar candidatos
            $candidates = [];
            foreach ($members as $m) {
                // Regla 1: ¿Toca el instrumento?
                if (!is_eligible($m, $role_name)) continue;

                // Regla 2: ¿Está disponible este día de la semana?
                $avail_days = explode(',', $m['available_days']);
                if (!in_array($day_of_week, $avail_days)) continue;

                // Regla 3: ¿Límite mensual?
                $current_usage = $usage[$month_key][$m['id']] ?? 0;
                if ($current_usage >= $m['max_services_per_month']) continue;

                // Regla 4: ¿Ya está asignado a OTRO rol en este mismo evento?
                // (Esto requeriría consultar DB o llevar track en memoria. Por simplicidad, asumimos que DB constraint lo bloquea o lo permitimos si toca dos cosas)
                // Mejor verificamos en DB rápido
                $check = $pdo->prepare("SELECT id FROM event_assignments WHERE event_id = ? AND member_id = ?");
                $check->execute([$event_id, $m['id']]);
                if ($check->rowCount() > 0) continue;

                $candidates[] = $m;
            }

            if (!empty($candidates)) {
                // Selección: Priorizar al que menos ha tocado este mes para balancear
                usort($candidates, function($a, $b) use ($usage, $month_key) {
                    $ua = $usage[$month_key][$a['id']] ?? 0;
                    $ub = $usage[$month_key][$b['id']] ?? 0;
                    return $ua <=> $ub;
                });

                // Tomar el primero (el menos usado)
                $chosen = $candidates[0];

                // Asignar
                $stmt_ins = $pdo->prepare("INSERT INTO event_assignments (event_id, member_id, instrument) VALUES (?, ?, ?)");
                $stmt_ins->execute([$event_id, $chosen['id'], $role_name]);

                // Actualizar uso en memoria
                if (!isset($usage[$month_key][$chosen['id']])) $usage[$month_key][$chosen['id']] = 0;
                $usage[$month_key][$chosen['id']]++;
                
                $assigned_count++;
            }
        }
    }
    
    $message = "<div class='bg-green-100 text-green-800 p-4 rounded-xl mb-6 font-bold'>✅ Asignación completada. Se generaron $assigned_count asignaciones nuevas respetando las reglas.</div>";
}

include 'header.php';
?>

<div class="container mx-auto px-4 max-w-2xl py-12">
    <header class="mb-10 text-center">
        <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase italic">Asignación Automática de Equipo</h1>
        <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mt-2">Rellena roles vacíos según disponibilidad</p>
    </header>

    <?php echo $message; ?>

    <div class="bg-white p-8 rounded-[2rem] shadow-xl border border-slate-100">
        <form method="POST" class="space-y-6">
            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 text-blue-800 text-xs mb-4">
                <strong>Cómo funciona:</strong>
                <ul class="list-disc ml-4 mt-2 space-y-1">
                    <li>Solo llena los roles que están <b>vacíos</b>.</li>
                    <li>Respeta los días disponibles de cada músico (ej: Solo Domingos).</li>
                    <li>Respeta el límite mensual de servicios de cada uno.</li>
                    <li>Si eres Líder, solo asignará tus instrumentos.</li>
                </ul>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block tracking-widest">Desde</label>
                    <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full p-4 bg-slate-50 rounded-2xl font-bold text-slate-700 outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block tracking-widest">Hasta</label>
                    <input type="date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required class="w-full p-4 bg-slate-50 rounded-2xl font-bold text-slate-700 outline-none">
                </div>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-5 rounded-[2rem] font-black uppercase tracking-widest shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition-all transform hover:-translate-y-1">
                🚀 Ejecutar Asignación
            </button>
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