<?php
require_once 'db_config.php';
require_once 'auth.php';

if (!$isAdmin) {
    header("Location: index.php");
    exit;
}

$message = "";
$error = "";
$imported_count = 0;

if (isset($_POST['import'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $filename = $_FILES['csv_file']['tmp_name'];
        
        if (($handle = fopen($filename, "r")) !== FALSE) {
            // Saltar la fila de encabezados
            fgetcsv($handle, 1000, ",");
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Estructura esperada del CSV:
                // 0: ID, 1: Título, 2: Artista, 3: Tono, 4: BPM, 5: Etiquetas (coma), 6: YouTube, 7: PDF, 8: Multitrack (Si/No)
                
                $id_manual = trim($data[0] ?? '');
                $title = trim($data[1] ?? '');
                if (empty($title)) continue; // Saltar filas vacías

                $artist = trim($data[2] ?? '');
                $key = trim($data[3] ?? '');
                $bpm = (int)($data[4] ?? 0);
                $tags_input = $data[5] ?? '';
                $yt = trim($data[6] ?? '');
                $pdf = trim($data[7] ?? '');
                $mt_raw = mb_strtolower(trim($data[8] ?? ''), 'UTF-8');
                $has_mt = (in_array($mt_raw, ['si', 'sí', 'yes', '1', 's', 'true', 'y'])) ? 1 : 0;

                try {
                    // 1. Insertar Canción
                    if (!empty($id_manual) && is_numeric($id_manual)) {
                        // Si viene ID manual, lo usamos
                        $stmt = $pdo->prepare("INSERT INTO songs (id, title, artist, musical_key, bpm, youtube_link, has_lyrics, has_multitrack) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$id_manual, $title, $artist, $key, $bpm, $yt, $pdf, $has_mt]);
                        $song_id = $id_manual;
                    } else {
                        // Si no, dejamos que sea automático
                        $stmt = $pdo->prepare("INSERT INTO songs (title, artist, musical_key, bpm, youtube_link, has_lyrics, has_multitrack) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $artist, $key, $bpm, $yt, $pdf, $has_mt]);
                        $song_id = $pdo->lastInsertId();
                    }
                    $imported_count++;

                    // 2. Procesar Etiquetas (Lógica Sencilla)
                    if (!empty($tags_input)) {
                        $tags = explode(',', $tags_input);
                        foreach($tags as $t_name) {
                            $t_name = trim($t_name);
                            if(empty($t_name)) continue;

                            // Buscar si la etiqueta ya existe
                            $stmt_find = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
                            $stmt_find->execute([$t_name]);
                            $tag_id = $stmt_find->fetchColumn();

                            if (!$tag_id) {
                                // Si no existe, CREARLA automáticamente (Color gris por defecto)
                                $stmt_new = $pdo->prepare("INSERT INTO tags (name, color_class) VALUES (?, 'bg-slate-100 text-slate-600 border-slate-200')");
                                $stmt_new->execute([$t_name]);
                                $tag_id = $pdo->lastInsertId();
                            }

                            // Asignar etiqueta a la canción
                            $pdo->prepare("INSERT IGNORE INTO song_tags (song_id, tag_id) VALUES (?, ?)")->execute([$song_id, $tag_id]);
                        }
                    }
                } catch (Exception $e) {
                    // Si hay error en una fila, continuamos con la siguiente (o podrías guardar un log)
                }
            }
            fclose($handle);
            $message = "✅ Proceso finalizado. Se importaron <b>$imported_count</b> canciones.";
        } else {
            $error = "No se pudo leer el archivo.";
        }
    } else {
        $error = "Por favor selecciona un archivo CSV válido.";
    }
}

include 'header.php';
?>

<div class="container mx-auto px-4 max-w-xl py-20">
    <div class="bg-white p-10 rounded-[3rem] shadow-2xl border border-slate-100 text-center">
        <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter mb-2">Importar Repertorio</h1>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-8">Carga masiva desde Excel / CSV</p>

        <?php if($message): ?><div class="bg-green-100 text-green-700 p-4 rounded-2xl mb-6 text-sm font-bold"><?php echo $message; ?></div><?php endif; ?>
        <?php if($error): ?><div class="bg-red-100 text-red-700 p-4 rounded-2xl mb-6 text-sm font-bold"><?php echo $error; ?></div><?php endif; ?>

        <div class="mb-8">
            <a href="download_template.php" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all border border-slate-200">
                ⬇ Descargar Plantilla CSV
            </a>
            <p class="text-[9px] text-slate-400 mt-2 font-bold">Usa esta plantilla para evitar errores de formato.</p>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="border-2 border-dashed border-slate-200 rounded-3xl p-8 hover:bg-slate-50 transition-colors cursor-pointer relative">
                <input type="file" name="csv_file" accept=".csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                <div class="text-4xl mb-2">📂</div>
                <span class="text-sm font-bold text-slate-500">Arrastra tu archivo CSV aquí o haz clic</span>
            </div>

            <div class="text-left bg-slate-50 p-4 rounded-2xl text-[10px] text-slate-500 font-mono">
                <p class="font-bold mb-2 uppercase">Formato de columnas requerido (Sin tildes en cabecera):</p>
                <p>ID, Titulo, Artista, Tono, BPM, Etiquetas, Youtube, PDF, Multitrack</p>
                <p class="mt-2 text-blue-500">Ejemplo: "105", "Tu Fidelidad", "Marcos Witt", "D", "70", "Adoración, Piano", "http...", "http...", "Si"</p>
            </div>

            <button type="submit" name="import" class="w-full bg-emerald-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all">Comenzar Importación</button>
        </form>
        <a href="repertorio_lista.php" class="block mt-6 text-xs font-bold text-slate-400 hover:text-slate-600">Cancelar y Volver</a>
    </div>
</div>