<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Récupérer les types d'expertise uniques
    $stmt = $pdo->query("
        SELECT DISTINCT 
            COALESCE(te.nom_type, e.type_requisition) as type_expertise
        FROM t_expertises e
        LEFT JOIN t_types_expertise te ON e.victime_de_id = te.code_type
        WHERE COALESCE(te.nom_type, e.type_requisition) IS NOT NULL
        ORDER BY type_expertise
    ");
    $types = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Récupérer les médecins
    $stmt = $pdo->query("
        SELECT 
            m.code,
            CONCAT('Dr. ', m.nom) as nom_complet
        FROM t_medecins m
        ORDER BY m.nom
    ");
    $medecins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'types' => $types,
            'medecins' => $medecins
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
