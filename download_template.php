<?php
require_once 'auth.php';

if (!$isAdmin) {
    header("Location: index.php");
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=plantilla_repertorio.csv');

$output = fopen('php://output', 'w');

// Agregar BOM para que Excel reconozca caracteres latinos (tildes, ñ) correctamente
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 1. Encabezados
fputcsv($output, array('ID', 'Titulo', 'Artista', 'Tono', 'BPM', 'Etiquetas', 'Youtube', 'PDF', 'Multitrack'));

// 2. Fila de Ejemplo (Opcional, ayuda al usuario a entender el formato)
fputcsv($output, array('105', 'Tu Fidelidad', 'Marcos Witt', 'D', '70', 'Adoración, Piano', 'https://youtube.com/...', 'https://drive.google.com/...', 'Si'));

fclose($output);
exit;
?>