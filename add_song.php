<?php
require_once 'db_config.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("INSERT INTO songs (title, artist, musical_key, bpm, has_multitrack, has_lyrics, priority) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $success = $stmt->execute([
        $_POST['title'], $_POST['artist'], $_POST['musical_key'], 
        $_POST['bpm'], isset($_POST['has_multitrack']) ? 1 : 0, 
        isset($_POST['has_lyrics']) ? 1 : 0, $_POST['priority']
    ]);
    if ($success) header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Canción - ArmoniaApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md">
        <a href="index.php" class="text-blue-600 text-sm mb-4 inline-block hover:underline">← Volver al listado</a>
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Nueva Canción</h2>
        <form method="POST" class="space-y-4">
            <input type="text" name="title" placeholder="Título de la canción" required class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
            <input type="text" name="artist" placeholder="Artista" class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
            <div class="flex gap-4">
                <input type="text" name="musical_key" placeholder="Key (Ej: G#m)" class="w-1/2 p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                <input type="number" name="bpm" placeholder="BPM" class="w-1/2 p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex items-center space-x-2 bg-gray-50 p-3 rounded-xl border">
                <input type="checkbox" name="has_multitrack" id="mt" class="w-5 h-5 text-blue-600">
                <label for="mt" class="text-sm text-gray-700 font-medium">¿Tiene Multitrack / Secuencia?</label>
            </div>
            <select name="priority" class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-white font-medium text-gray-700">
                <option value="Low">Prioridad: Baja</option>
                <option value="Medium" selected>Prioridad: Media</option>
                <option value="High">Prioridad: Alta</option>
            </select>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 shadow-lg transition duration-300">
                Guardar en Repertorio
            </button>
        </form>
    </div>
</body>
</html>