<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Compter le nombre d'expertises par type
    $sql = "SELECT 
                COALESCE(te.nom_type, e.type_requisition) as type_expertise,
                COUNT(*) as nombre,
                (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM t_expertises)) as pourcentage
            FROM 
                t_expertises e
            LEFT JOIN 
                t_types_expertise te ON e.victime_de_id = te.code_type
            GROUP BY 
                COALESCE(te.nom_type, e.type_requisition)
            ORDER BY 
                nombre DESC";

    $stmt = $pdo->query($sql);
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $stats
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
?>
