<?php
require_once 'db_config.php';
require_once 'auth.php';

if (!$isAdmin) {
    header("Location: index.php");
    exit;
}

// Nombre del archivo con fecha
$filename = "repertorio_backup_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Agregar BOM para compatibilidad con Excel (tildes, ñ)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 1. Encabezados (Misma estructura que la plantilla de importación)
fputcsv($output, array('ID', 'Titulo', 'Artista', 'Tono', 'BPM', 'Etiquetas', 'Youtube', 'PDF', 'Multitrack'));

// 2. Obtener datos
// Usamos GROUP_CONCAT para unir las etiquetas separadas por coma
$sql = "
    SELECT s.*, GROUP_CONCAT(t.name SEPARATOR ', ') as tag_names
    FROM songs s
    LEFT JOIN song_tags st ON s.id = st.song_id
    LEFT JOIN tags t ON st.tag_id = t.id
    GROUP BY s.id
    ORDER BY s.id ASC
";

$stmt = $pdo->query($sql);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['id'],
        $row['title'],
        $row['artist'],
        $row['musical_key'],
        $row['bpm'],
        $row['tag_names'], // Etiquetas separadas por coma
        $row['youtube_link'],
        $row['has_lyrics'], // En DB guardamos el link en has_lyrics
        $row['has_multitrack'] ? 'Si' : 'No'
    ]);
}

fclose($output);
exit;
?>