<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Compter le nombre total de types d'expertise
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM t_types_expertise");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Obtenir les détails de chaque type avec leur utilisation
    $stmt = $pdo->query("
        SELECT 
            te.id,
            te.nom_type,
            COUNT(e.code_expertise) as nombre_expertises
        FROM 
            t_types_expertise te
        LEFT JOIN 
            t_expertises e ON te.id = e.type_expertise_id
        GROUP BY 
            te.id, te.nom_type
        ORDER BY 
            nombre_expertises DESC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total' => $total,
            'categories' => $categories
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
