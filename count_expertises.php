<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Compte total des expertises
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM t_expertises");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Compte par type de réquisition
    $stmt = $pdo->query("
        SELECT 
            type_requisition,
            COUNT(*) as count
        FROM 
            t_expertises
        GROUP BY 
            type_requisition
    ");
    $par_type = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Compte des expertises du mois en cours
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM t_expertises 
        WHERE MONTH(date_heure) = MONTH(CURRENT_DATE())
        AND YEAR(date_heure) = YEAR(CURRENT_DATE())
    ");
    $ce_mois = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total' => $total,
            'par_type' => $par_type,
            'ce_mois' => $ce_mois
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
