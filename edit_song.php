<?php
require_once 'db_config.php';
require_once 'auth.php';

if (!$isAdmin) {
    header("Location: index.php");
    exit;
}

// 1. Obtener el ID de la canción desde la URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// 2. Consultar los datos actuales de esa canción
$stmt = $pdo->prepare("SELECT * FROM songs WHERE id = ?");
$stmt->execute([$id]);
$song = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$song) {
    die("Canción no encontrada.");
}

// 3. Procesar el formulario cuando se le da a "Guardar Cambios"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "UPDATE songs SET 
            title = ?, 
            artist = ?, 
            musical_key = ?, 
            bpm = ?, 
            has_multitrack = ?, 
            priority = ? 
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['title'],
        $_POST['artist'],
        $_POST['musical_key'],
        $_POST['bpm'],
        isset($_POST['has_multitrack']) ? 1 : 0,
        $_POST['priority'],
        $id
    ]);
    
    // Regresar al listado principal
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Canción - ArmoniaApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Editar Canción</h2>
            <a href="index.php" class="text-gray-400 hover:text-gray-600 text-sm">Cancelar</a>
        </div>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Título</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($song['title']); ?>" required 
                       class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Artista</label>
                <input type="text" name="artist" value="<?php echo htmlspecialchars($song['artist']); ?>" 
                       class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div class="flex gap-4">
                <div class="w-1/2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Key</label>
                    <input type="text" name="musical_key" value="<?php echo htmlspecialchars($song['musical_key']); ?>" 
                           class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-blue-600 font-bold">
                </div>
                <div class="w-1/2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">BPM</label>
                    <input type="number" name="bpm" value="<?php echo $song['bpm']; ?>" 
                           class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <div class="flex items-center space-x-2 bg-blue-50 p-4 rounded-xl border border-blue-100">
                <input type="checkbox" name="has_multitrack" id="mt" class="w-5 h-5" <?php echo $song['has_multitrack'] ? 'checked' : ''; ?>>
                <label for="mt" class="text-sm text-gray-700 font-medium">¿Tiene Secuencia / Multitrack?</label>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Prioridad</label>
                <select name="priority" class="w-full p-3 border rounded-xl bg-white outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Low" <?php echo $song['priority'] == 'Low' ? 'selected' : ''; ?>>Baja</option>
                    <option value="Medium" <?php echo $song['priority'] == 'Medium' ? 'selected' : ''; ?>>Media</option>
                    <option value="High" <?php echo $song['priority'] == 'High' ? 'selected' : ''; ?>>Alta</option>
                </select>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 shadow-lg transition duration-300">
                Actualizar Datos
            </button>
        </form>
    </div>
</body>
</html>