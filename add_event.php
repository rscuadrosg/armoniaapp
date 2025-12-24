<?php
require_once 'db_config.php';

// Si venimos del Paso 1 (Crear el evento base)
if (isset($_POST['create_base_event'])) {
    $stmt = $pdo->prepare("INSERT INTO events (event_date, description) VALUES (?, ?)");
    $stmt->execute([$_POST['event_date'], $_POST['description']]);
    $event_id = $pdo->lastInsertId();
    
    // Redirigimos al Paso 2: Seleccionar canciones para este ID
    header("Location: view_event.php?id=" . $event_id);
    exit;
}

include 'header.php';
?>

<div class="min-h-screen bg-slate-50 flex items-center justify-center">
    <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl w-full max-w-md border border-slate-100">
        <h2 class="text-3xl font-black text-slate-800 text-center mb-8">Nuevo Servicio</h2>
        
        <form method="POST">
            <div class="mb-6">
                <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">Nombre del Evento</label>
                <input type="text" name="description" placeholder="Ej: Servicio Dominical" required 
                       class="w-full p-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 transition-all">
            </div>

            <div class="mb-8">
                <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">Fecha</label>
                <input type="date" name="event_date" required 
                       class="w-full p-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-700">
            </div>

            <button type="submit" name="create_base_event" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all uppercase tracking-widest text-xs">
                Crear y Continuar
            </button>
        </form>
    </div>
</div>