<?php
// supprimer_expertise.php
header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Récupération du code de l'expertise à supprimer
    $code_expertise = $_POST['code_expertise'] ?? '';
    
    // Vérification du code
    if (empty($code_expertise)) {
        throw new Exception("Le code d'expertise est obligatoire.");
    }
    
    // Vérification de l'existence de l'expertise
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM t_expertises WHERE code_expertise = :code_expertise");
    $stmt->execute([':code_expertise' => $code_expertise]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception("Cette expertise n'existe pas.");
    }
    
    // Début d'une transaction pour garantir l'intégrité des données
    $pdo->beginTransaction();
    
    // Suppression des relations éventuelles (si vous avez des tables liées)
    // Exemple: $stmt = $pdo->prepare("DELETE FROM t_details_expertise WHERE expertise_id = :code_expertise");
    // $stmt->execute([':code_expertise' => $code_expertise]);
    
    // Suppression de l'expertise
    $stmt = $pdo->prepare("DELETE FROM t_expertises WHERE code_expertise = :code_expertise");
    $stmt->execute([':code_expertise' => $code_expertise]);
    
    // Validation de la transaction
    $pdo->commit();
    
    // Réponse en cas de succès
    echo json_encode([
        'status' => 'success',
        'message' => 'Expertise médicale supprimée avec succès !'
    ]);
    
} catch (PDOException $e) {
    // Annulation de la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    // Réponse en cas d'erreur de base de données
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Annulation de la transaction en cas d'erreur
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    // Réponse en cas d'erreur générale
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
