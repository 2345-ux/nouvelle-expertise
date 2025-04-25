<?php
header('Content-Type: application/json');

try {
    if (!isset($_GET['code_expertise'])) {
        throw new Exception("Code d'expertise manquant");
    }

    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare("
        SELECT 
            e.*,
            m.nom AS nom_medecin,
            m.sexe AS sexe_medecin
        FROM 
            t_expertises e
        LEFT JOIN 
            t_medecins m ON e.medecin_id = m.code
        WHERE 
            e.code_expertise = :code_expertise
    ");

    $stmt->execute([':code_expertise' => $_GET['code_expertise']]);
    $expertise = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expertise) {
        throw new Exception("Expertise non trouvée");
    }

    echo json_encode([
        'status' => 'success',
        'expertise' => $expertise
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
