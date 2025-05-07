<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Compte uniquement le total des expertises
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM t_expertises");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total' => $total,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
