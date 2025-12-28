<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_config.php';

// --- SIMULACIÃ“N DE LOGIN ---
$my_id = 1; 

// 1. Obtener datos del perfil del mÃºsico
$stmt_me = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt_me->execute([$my_id]);
$user = $stmt_me->fetch(PDO::FETCH_ASSOC);

// 2. OBTENER SERVICIOS (Consulta Corregida)
$my_events = $pdo->prepare("
    SELECT 
        e.id, 
        e.description,  -- USAMOS 'description' QUE ES LA COLUMNA REAL
        e.event_date, 
        ea.instrument,
        ec.status as confirmation_status
    FROM events e
    JOIN event_assignments ea ON e.id = ea.event_id
    LEFT JOIN event_confirmations ec ON (e.id = ec.event_id AND ec.member_id = ?)
    WHERE ea.member_id = ? AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC
");
$my_events->execute([$my_id, $my_id]);

include 'header.php';
?>

<div class="max-w-4xl mx-auto p-4 pb-20">
    <header class="bg-white rounded-[3rem] p-8 shadow-xl shadow-slate-200/50 flex flex-col md:flex-row items-center gap-8 mb-10 border border-slate-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        
        <div class="relative group">
            <div class="w-32 h-32 rounded-[2.5rem] overflow-hidden bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white text-4xl font-black shadow-2xl border-4 border-white">
                <?php if(!empty($user['profile_photo'])): ?>
                    <img src="uploads/profile_pics/<?php echo $user['profile_photo']; ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            
            <form action="upload_photo.php" method="POST" enctype="multipart/form-data" class="absolute -bottom-2 -right-2">
                <label class="bg-white w-10 h-10 rounded-xl shadow-lg flex items-center justify-center text-blue-600 hover:scale-110 transition cursor-pointer border border-slate-100">
                    <span class="text-lg">ðŸ“·</span>
                    <input type="file" name="photo" class="hidden" onchange="this.form.submit()">
                    <input type="hidden" name="member_id" value="<?php echo $my_id; ?>">
                    <input type="hidden" name="upload" value="1">
                </label>
            </form>
        </div>

        <div class="text-center md:text-left flex-1 z-10">
            <h1 class="text-4xl font-black text-slate-800 tracking-tighter leading-none mb-2">
                <?php echo htmlspecialchars($user['full_name']); ?>
            </h1>
            <p class="text-blue-500 font-bold uppercase text-[10px] tracking-[0.3em]">Integrante del Ministerio</p>
            
            <div class="flex gap-3 mt-6 justify-center md:justify-start">
                <div class="bg-slate-50 px-4 py-2 rounded-2xl border border-slate-100">
                    <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">PrÃ³ximos</span>
                    <span class="text-lg font-black text-slate-700"><?php echo $my_events->rowCount(); ?> Servicios</span>
                </div>
            </div>
        </div>
    </header>

    <h2 class="text-2xl font-black text-slate-800 mb-6 px-2 tracking-tight">Mi Agenda de Servicio</h2>
    
    <div class="grid gap-4">
        <?php if($my_events->rowCount() == 0): ?>
            <div class="bg-white p-10 rounded-[2.5rem] text-center border-2 border-dashed border-slate-200">
                <p class="text-slate-400 font-bold italic">No tienes servicios asignados prÃ³ximamente.</p>
            </div>
        <?php endif; ?>

        <?php while($ev = $my_events->fetch(PDO::FETCH_ASSOC)): 
            $status = $ev['confirmation_status'];
        ?>
            <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col md:flex-row justify-between items-center gap-6 group hover:border-blue-200 transition-all">
                
                <div class="flex items-center gap-6 w-full md:w-auto">
                    <div class="bg-slate-900 text-white p-4 rounded-3xl text-center min-w-[75px] shadow-lg shadow-slate-200">
                        <span class="block text-[10px] font-black uppercase opacity-50"><?php echo date('M', strtotime($ev['event_date'])); ?></span>
                        <span class="text-2xl font-black leading-none"><?php echo date('d', strtotime($ev['event_date'])); ?></span>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-black text-slate-800 leading-tight mb-1">
                            <?php echo htmlspecialchars($ev['description']); ?>
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="bg-blue-100 text-blue-700 text-[9px] font-black px-2 py-0.5 rounded-md uppercase tracking-widest">
                                <?php echo htmlspecialchars($ev['instrument']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                    
                    <?php if($status == 'confirmado'): ?>
                        <div class="bg-green-50 text-green-600 px-5 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest border border-green-100">
                            âœ“ Confirmado
                        </div>
                    <?php elseif($status == 'rechazado'): ?>
                        <div class="bg-red-50 text-red-600 px-5 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest border border-red-100">
                            No AsistirÃ©
                        </div>
                    <?php else: ?>
                        <a href="process_confirmation.php?event_id=<?php echo $ev['id']; ?>&member_id=<?php echo $my_id; ?>&status=rechazado" 
                           class="bg-slate-50 text-slate-400 px-5 py-3 rounded-2xl font-black text-[10px] uppercase hover:bg-red-50 hover:text-red-500 transition border border-slate-100">
                            Declinar
                        </a>
                        <a href="process_confirmation.php?event_id=<?php echo $ev['id']; ?>&member_id=<?php echo $my_id; ?>&status=confirmado" 
                           class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase shadow-lg shadow-blue-100 hover:scale-105 transition tracking-widest">
                            Confirmar
                        </a>
                    <?php endif; ?>

                    <a href="view_event_musico.php?id=<?php echo $ev['id']; ?>" 
                       class="bg-slate-900 text-white w-12 h-12 flex items-center justify-center rounded-2xl hover:bg-blue-600 transition shadow-lg shadow-slate-200">
                        ðŸŽµ
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>