<?php
require_once 'db_config.php';

try {
    // 1. Crear tabla de Etiquetas (Tags)
    $pdo->exec("CREATE TABLE IF NOT EXISTS tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        color_class VARCHAR(100) DEFAULT 'bg-slate-100 text-slate-600'
    )");

    // 2. Crear tabla pivote (Relación Muchos a Muchos)
    $pdo->exec("CREATE TABLE IF NOT EXISTS song_tags (
        song_id INT,
        tag_id INT,
        PRIMARY KEY(song_id, tag_id),
        FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
    )");

    // 3. Insertar etiquetas por defecto (Basadas en tus prioridades actuales)
    // Verificamos si ya existen para no duplicar
    $stmt = $pdo->query("SELECT COUNT(*) FROM tags");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO tags (name, color_class) VALUES 
            ('Alta', 'bg-red-100 text-red-600 border-red-200'),
            ('Media', 'bg-orange-100 text-orange-600 border-orange-200'),
            ('Baja', 'bg-blue-100 text-blue-600 border-blue-200'),
            ('Adoración', 'bg-violet-100 text-violet-600 border-violet-200'),
            ('Alabanza', 'bg-emerald-100 text-emerald-600 border-emerald-200')
        ");
        echo "<p>✅ Etiquetas creadas.</p>";
    }

    // 4. Migrar datos existentes (Priority -> Tags)
    // Obtenemos las canciones y su prioridad antigua
    $songs = $pdo->query("SELECT id, priority FROM songs")->fetchAll();
    
    // Mapeo de prioridad a ID de etiqueta (Asumiendo los IDs insertados arriba: 1=Alta, 2=Media, 3=Baja)
    $map = ['High' => 1, 'Medium' => 2, 'Low' => 3];
    
    $count = 0;
    foreach($songs as $s) {
        if(isset($map[$s['priority']])) {
            $tag_id = $map[$s['priority']];
            // Insertar ignorando si ya existe
            $sql = "INSERT IGNORE INTO song_tags (song_id, tag_id) VALUES (?, ?)";
            $pdo->prepare($sql)->execute([$s['id'], $tag_id]);
            $count++;
        }
    }
    
    echo "<p>✅ Migración completada: $count canciones etiquetadas.</p>";
    echo "<a href='index.php'>Volver al Inicio</a>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>