<?php
require_once 'db_config.php';
$query = $pdo->query("SELECT * FROM members ORDER BY full_name ASC");
$members = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'header.php'; ?>
<body class="bg-gray-50">

    <main class="container mx-auto mt-8 p-4">
        <h2 class="text-2xl font-semibold mb-6 text-gray-700">Integrantes del Grupo</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach ($members as $m): ?>
            <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 <?php echo $m['is_available'] ? 'border-green-500' : 'border-red-500'; ?>">
                <div class="font-bold text-lg text-gray-800"><?php echo $m['full_name']; ?></div>
                <div class="text-sm text-gray-500"><?php echo $m['member_type'] == 'Internal' ? '🏠 Interno' : '🌍 Externo'; ?></div>
                <div class="mt-2 text-xs font-semibold <?php echo $m['is_available'] ? 'text-green-600' : 'text-red-600'; ?>">
                    <?php echo $m['is_available'] ? '● Disponible' : '● No disponible'; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>