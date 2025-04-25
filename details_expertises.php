<?php
// details_expertise.php
header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Récupération du code de l'expertise
    $code_expertise = $_GET['code_expertise'] ?? '';
    
    // Vérification du code
    if (empty($code_expertise)) {
        throw new Exception("Le code d'expertise est obligatoire.");
    }
    
    // Requête pour récupérer les détails de l'expertise
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
    
    $stmt->execute([':code_expertise' => $code_expertise]);
    $expertise = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Vérification si l'expertise existe
    if (!$expertise) {
        throw new Exception("Expertise non trouvée avec le code $code_expertise.");
    }
    
    // Formatage des dates pour un affichage plus convivial
    if (isset($expertise['date_heure'])) {
        $date = new DateTime($expertise['date_heure']);
        $expertise['date_heure_formatee'] = $date->format('d/m/Y H:i');
    }
    
    if (isset($expertise['date_requisition']) && !empty($expertise['date_requisition'])) {
        $date = new DateTime($expertise['date_requisition']);
        $expertise['date_requisition_formatee'] = $date->format('d/m/Y');
    } else {
        $expertise['date_requisition_formatee'] = '';
    }
    
    // Réponse en cas de succès
    echo json_encode([
        'status' => 'success',
        'expertise' => $expertise
    ]);
    
} catch (PDOException $e) {
    // Réponse en cas d'erreur de base de données
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Réponse en cas d'erreur générale
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
