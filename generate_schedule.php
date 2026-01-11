<?php
require_once 'db_config.php';
require_once 'auth.php';

if (!$isAdmin) {
    header("Location: index.php");
    exit;
}

$message = "";
$generated_events = [];
$tags = $pdo->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $base_name = $_POST['base_name'];
    $start_date = new DateTime($_POST['start_date']);
    $end_date = new DateTime($_POST['end_date']);
    
    // Arrays de configuración
    $selected_days = $_POST['days'] ?? []; // Indices 0-6
    $day_times = $_POST['times'] ?? []; // Hora por día
    
    // Nueva lógica: Etiquetas y Cantidades
    $selected_tag_ids = $_POST['tags_selected'] ?? [];
    $tag_counts = $_POST['tag_counts'] ?? [];
    
    // Filtrar configuración válida (Etiqueta seleccionada y cantidad > 0)
    $config_tags = [];
    foreach($selected_tag_ids as $tid) {
        $qty = (int)($tag_counts[$tid] ?? 0);
        if($qty > 0) $config_tags[$tid] = $qty;
    }

    if (empty($selected_days)) {
        $message = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6'>⚠️ Debes seleccionar al menos un día de la semana.</div>";
    } elseif (empty($config_tags)) {
        $message = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6'>⚠️ Debes seleccionar al menos una etiqueta y definir una cantidad mayor a 0.</div>";
    } else {
    
    // 1. OBTENER REPERTORIO INTELIGENTE
    // Construir placeholders para etiquetas
    $inQuery = implode(',', array_fill(0, count($config_tags), '?'));
    $params = array_keys($config_tags);
    
    $sql = "SELECT s.id, s.title, MAX(e.event_date) as last_played, GROUP_CONCAT(st.tag_id) as tag_ids 
            FROM songs s 
            JOIN song_tags st ON s.id = st.song_id
            LEFT JOIN event_songs es ON s.id = es.song_id 
            LEFT JOIN events e ON es.event_id = e.id 
            WHERE st.tag_id IN ($inQuery)
            GROUP BY s.id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $raw_songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Crear Pool de Canciones con historial simulado
    $song_pool = [];
    foreach($raw_songs as $r) {
        $song_pool[$r['id']] = [
            'id' => $r['id'],
            'title' => $r['title'],
            'last_played_ts' => $r['last_played'] ? strtotime($r['last_played']) : 0,
            'tags' => explode(',', $r['tag_ids']) // Array de etiquetas de la canción
        ];
    }
    
    $total_repertoire = count($song_pool);
    
    $current_date = clone $start_date;
    $songs_assigned_count = 0;
    
    if ($total_repertoire > 0) {
        try {
            $pdo->beginTransaction();
            
            while ($current_date <= $end_date) {
                $day_idx = $current_date->format('w'); // 0 (Dom) - 6 (Sab)
                
                // Si este día está seleccionado
                if (in_array($day_idx, $selected_days)) {
                    $date_str = $current_date->format('Y-m-d');
                    $time_str = $day_times[$day_idx] ?? '09:00';

                    // Generar título dinámico (Ej: Servicio Dom-01-feb-2026)
                    $day_en = $current_date->format('D');
                    $month_en = $current_date->format('M');
                    $days_es = ['Sun'=>'Dom','Mon'=>'Lun','Tue'=>'Mar','Wed'=>'Mie','Thu'=>'Jue','Fri'=>'Vie','Sat'=>'Sab'];
                    $months_es = ['Jan'=>'ene','Feb'=>'feb','Mar'=>'mar','Apr'=>'abr','May'=>'may','Jun'=>'jun','Jul'=>'jul','Aug'=>'ago','Sep'=>'sep','Oct'=>'oct','Nov'=>'nov','Dec'=>'dic'];
                    $final_title = $base_name . " " . $days_es[$day_en] . "-" . $current_date->format('d') . "-" . $months_es[$month_en] . "-" . $current_date->format('Y');
                
                // A. Crear Evento
                $stmt = $pdo->prepare("INSERT INTO events (description, event_date) VALUES (?, ?)");
                    $stmt->execute([$final_title, "$date_str $time_str:00"]);
                $event_id = $pdo->lastInsertId();
                
                // B. Asignar Canciones (Rotación)
                $event_songs_preview = [];
                $current_event_song_ids = []; // Para evitar duplicados en el mismo evento
                $position = 1;

                foreach($config_tags as $tid => $qty) {
                    // 1. Filtrar candidatos: Tienen la etiqueta Y NO están ya en este evento
                    $candidates = [];
                    foreach($song_pool as $sid => $data) {
                        if(in_array($tid, $data['tags']) && !in_array($sid, $current_event_song_ids)) {
                            $candidates[] = $data;
                        }
                    }

                    // 2. Mezclar para variedad (Shuffle) y luego Ordenar por uso (Smart Sort)
                    shuffle($candidates); // Evita orden alfabético en empates
                    usort($candidates, function($a, $b) {
                        return $a['last_played_ts'] <=> $b['last_played_ts'];
                    });

                    // 3. Seleccionar los Top N
                    for($k=0; $k<$qty; $k++) {
                        if(isset($candidates[$k])) {
                            $picked = $candidates[$k];
                    
                    $stmt_s = $pdo->prepare("INSERT INTO event_songs (event_id, song_id, position) VALUES (?, ?, ?)");
                            $stmt_s->execute([$event_id, $picked['id'], $position++]);
                    
                            $event_songs_preview[] = $picked['title'];
                            $current_event_song_ids[] = $picked['id'];
                    $songs_assigned_count++;
                            
                            // ACTUALIZAR HISTORIAL SIMULADO: Para el próximo evento, esta canción parecerá recién tocada hoy
                            $song_pool[$picked['id']]['last_played_ts'] = strtotime($date_str . ' ' . $time_str);
                        }
                    }
                }
                
                $generated_events[] = [
                    'date' => $date_str,
                        'time' => $time_str,
                    'songs' => $event_songs_preview
                ];
                }
                
                $current_date->modify('+1 day');
            }
            
            $pdo->commit();
            
            // Estadísticas
            $unique_songs_used = min($songs_assigned_count, $total_repertoire);
            $unused = $total_repertoire - $unique_songs_used;
            $unused = max(0, $unused); // Evitar negativos
            
            $message = "<div class='bg-green-100 text-green-800 p-6 rounded-[2rem] mb-8 shadow-lg border border-green-200'>";
            $message .= "<h3 class='text-xl font-black uppercase italic'>✅ Programación Exitosa</h3>";
            $message .= "<p class='mt-2 text-sm'>Se generaron <b>" . count($generated_events) . "</b> servicios automáticamente.</p>";
            
            if ($unused > 0) {
                $message .= "<div class='mt-4 bg-white/50 p-4 rounded-xl text-xs font-bold text-green-900'>⚠️ Nota: Aún quedan <b>$unused</b> canciones (con las etiquetas seleccionadas) que no alcanzaron a sonar.</div>";
            } else {
                $message .= "<div class='mt-4 bg-white/50 p-4 rounded-xl text-xs font-bold text-green-900'>🎉 ¡Excelente! Se ha rotado todo el repertorio disponible al menos una vez.</div>";
            }
            $message .= "</div>";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6'>Error: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='bg-orange-100 text-orange-700 p-4 rounded-xl mb-6'>⚠️ No se encontraron canciones con las etiquetas seleccionadas.</div>";
    }
    }
}

// Helper para nombres de días
$days_labels = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

include 'header.php';
?>

<div class="container mx-auto px-4 max-w-3xl py-12">
    <header class="mb-10 text-center">
        <h1 class="text-4xl font-black text-slate-900 tracking-tighter uppercase italic">Generador de Temporada</h1>
        <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mt-2">Planificación Automática de Servicios</p>
    </header>

    <?php echo $message; ?>

    <?php if(empty($generated_events)): ?>
    <div class="bg-white p-8 md:p-12 rounded-[3rem] shadow-2xl border border-slate-100">
        <form method="POST" class="space-y-8">
            
            <!-- 1. Configuración Básica -->
            <div class="space-y-4">
                <h3 class="text-xs font-black uppercase text-blue-600 tracking-widest border-b border-blue-100 pb-2">1. Configuración General</h3>
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block tracking-widest">Nombre Base del Evento</label>
                    <input type="text" name="base_name" value="Servicio General" required class="w-full p-4 bg-slate-50 rounded-2xl font-bold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block tracking-widest">Desde</label>
                        <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full p-4 bg-slate-50 rounded-2xl font-bold text-slate-700 outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block tracking-widest">Hasta</label>
                        <input type="date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+3 months')); ?>" required class="w-full p-4 bg-slate-50 rounded-2xl font-bold text-slate-700 outline-none">
                    </div>
                </div>
            </div>

            <!-- 2. Días y Horarios -->
            <div class="space-y-4">
                <h3 class="text-xs font-black uppercase text-blue-600 tracking-widest border-b border-blue-100 pb-2">2. Días y Horarios</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach($days_labels as $idx => $label): ?>
                    <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <input type="checkbox" name="days[]" value="<?php echo $idx; ?>" id="day_<?php echo $idx; ?>" class="w-5 h-5 accent-blue-600 cursor-pointer" onchange="toggleTimeInput(<?php echo $idx; ?>)">
                        <label for="day_<?php echo $idx; ?>" class="flex-1 font-bold text-slate-700 text-sm cursor-pointer select-none"><?php echo $label; ?></label>
                        <input type="time" name="times[<?php echo $idx; ?>]" id="time_<?php echo $idx; ?>" value="09:00" disabled class="p-2 rounded-xl border border-slate-200 text-xs font-bold bg-white text-slate-500 disabled:opacity-50">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 3. Repertorio -->
            <div class="space-y-4">
                <h3 class="text-xs font-black uppercase text-blue-600 tracking-widest border-b border-blue-100 pb-2">3. Repertorio</h3>
                
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block tracking-widest">Estructura del Servicio (Cantidad por Etiqueta)</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <?php foreach($tags as $t): ?>
                            <div class="flex items-center justify-between p-3 border border-slate-200 rounded-xl bg-white">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" name="tags_selected[]" value="<?php echo $t['id']; ?>" class="w-5 h-5 accent-blue-600 rounded-md" onchange="toggleTagCount(this, <?php echo $t['id']; ?>)">
                                    <span class="text-[10px] font-black uppercase <?php echo $t['color_class']; ?> px-2 py-1 rounded"><?php echo $t['name']; ?></span>
                                </label>
                                <input type="number" name="tag_counts[<?php echo $t['id']; ?>]" id="count_<?php echo $t['id']; ?>" value="0" min="1" max="10" class="w-16 p-2 bg-slate-50 rounded-lg text-center font-bold text-xs outline-none border border-slate-100 focus:border-blue-500 transition-all disabled:opacity-30 disabled:bg-slate-100" disabled>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-slate-900 text-white py-6 rounded-[2rem] font-black uppercase tracking-widest shadow-2xl hover:bg-blue-600 transition-all transform hover:-translate-y-1 text-sm">
                ✨ Generar Programación
            </button>
        </form>
    </div>
    <?php else: ?>
        <div class="grid gap-4">
            <h3 class="text-center text-xs font-black uppercase text-slate-400 tracking-widest mb-4">Vista Previa de lo Generado</h3>
            <?php foreach($generated_events as $ev): ?>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h4 class="font-black text-slate-800 text-lg"><?php echo date('d M, Y', strtotime($ev['date'])); ?></h4>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?php echo $ev['time']; ?> hrs</span>
                        </div>
                        <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">Creado</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach($ev['songs'] as $song): ?>
                            <span class="bg-slate-50 text-slate-600 px-3 py-1 rounded-lg text-xs font-bold border border-slate-100">
                                🎵 <?php echo htmlspecialchars($song); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <a href="events.php" class="block text-center bg-slate-900 text-white py-4 rounded-2xl font-black uppercase tracking-widest mt-6 hover:bg-slate-800 transition-all">
                Ir a Servicios
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleTimeInput(idx) {
    const cb = document.getElementById('day_' + idx);
    const input = document.getElementById('time_' + idx);
    input.disabled = !cb.checked;
    if(!input.disabled) input.focus();
}

function toggleTagCount(cb, id) {
    const input = document.getElementById('count_' + id);
    input.disabled = !cb.checked;
    if(cb.checked) { input.value = 1; input.focus(); } else { input.value = 0; }
}
</script>

<?php 
if (file_exists('footer.php')) {
    include 'footer.php'; 
} else {
    echo "</body></html>";
}
?>
