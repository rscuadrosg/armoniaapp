<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <nav class="bg-blue-900 text-white shadow-2xl mb-8 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-xl font-black italic tracking-tighter">ArmoníaApp</a>
            
            <div class="hidden md:flex bg-blue-950/50 p-1 rounded-xl border border-white/10 gap-1 items-center">
                <span class="text-[9px] font-black uppercase tracking-widest px-2 text-blue-300">Prueba:</span>
                <a href="?set_role=admin" class="px-3 py-1 rounded-lg text-[9px] font-black <?php echo $isAdmin ? 'bg-blue-600 text-white' : 'text-blue-400'; ?>">ADMIN</a>
                <a href="?set_role=musico" class="px-3 py-1 rounded-lg text-[9px] font-black <?php echo !$isAdmin ? 'bg-green-600 text-white' : 'text-blue-400'; ?>">MUSICO</a>
            </div>

            <div class="flex gap-4 overflow-x-auto no-scrollbar items-center text-[10px] font-black uppercase tracking-widest">
                <a href="index.php" class="hover:text-blue-300">Home</a>
                <a href="repertorio_lista.php" class="hover:text-blue-300">Repertorio</a>
                <a href="members.php" class="hover:text-blue-300">Equipo</a>
                <a href="events.php" class="hover:text-blue-300">Servicios</a>
                <a href="settings_band.php" class="hover:text-blue-300 text-sm">&#9881;</a>
                
                <a href="dashboard.php" class="bg-blue-600 px-4 py-2 rounded-xl shadow-lg shadow-blue-900/50 flex items-center gap-2 border border-blue-400/30">
                    <span class="text-sm">&#128100;</span> MI PANEL
                </a>
            </div>
        </div>
    </nav>