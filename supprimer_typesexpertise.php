<?php
header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Récupération du code du type à supprimer
    $code_type = $_POST['code_type'] ?? '';
    
    // Vérification du code
    if (empty($code_type)) {
        throw new Exception("Le code du type d'expertise est obligatoire.");
    }
    
    // Vérification si le type est utilisé dans des expertises en utilisant le bon nom de colonne
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM t_expertises WHERE type_requisition = :code_type");
    $stmt->execute([':code_type' => $code_type]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] > 0) {
        throw new Exception("Ce type d'expertise ne peut pas être supprimé car il est associé à " . $result['total'] . " expertise(s).");
    }
    
    // Préparation de la requête de suppression
    $stmt = $pdo->prepare("DELETE FROM t_types_expertise WHERE code_type = :code_type");
    
    // Exécution de la requête
    $stmt->execute([':code_type' => $code_type]);
    
    // Vérification si une ligne a été supprimée
    if ($stmt->rowCount() === 0) {
        throw new Exception("Aucun type d'expertise trouvé avec ce code.");
    }
    
    // Réponse en cas de succès
    echo json_encode([
        'status' => 'success',
        'message' => 'Type d\'expertise supprimé avec succès !'
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
