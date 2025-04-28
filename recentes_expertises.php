<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Récupérer les 5 expertises les plus récentes
    $stmt = $pdo->query("
        SELECT 
            e.code_expertise,
            e.date_heure,
            e.nom_victime,
            e.type_requisition,
            m.nom AS nom_medecin,
            m.sexe AS sexe_medecin,
            CASE 
                WHEN e.date_heure > NOW() THEN 'Planifié'
                WHEN e.consultation_examen IS NOT NULL THEN 'Terminé'
                ELSE 'En cours'
            END AS statut
        FROM 
            t_expertises e
        LEFT JOIN 
            t_medecins m ON e.medecin_id = m.code
        ORDER BY 
            e.date_heure DESC
        LIMIT 5
    ");

    $expertises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formater les données
    foreach ($expertises as &$expertise) {
        $date = new DateTime($expertise['date_heure']);
        $expertise['date_formatee'] = $date->format('d/m/Y');
        $expertise['medecin_formate'] = 'Dr. ' . $expertise['nom_medecin'];
    }

    echo json_encode([
        'status' => 'success',
        'expertises' => $expertises
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
