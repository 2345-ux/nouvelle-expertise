<?php
header('Content-Type: application/json');

// Activer l'affichage détaillé des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Récupération du code d'expertise à supprimer
    $code_expertise = $_POST['code_expertise'] ?? null;
    
    if (!$code_expertise) {
        throw new Exception("Le code d'expertise est obligatoire pour la suppression");
    }

    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Préparation et exécution de la requête de suppression
    $stmt = $pdo->prepare("DELETE FROM t_expertises WHERE code_expertise = ?");
    $result = $stmt->execute([$code_expertise]);

    if (!$result) {
        error_log("Erreur SQL: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Erreur de suppression en base de données");
    }

    // Vérifier si l'expertise a été trouvée et supprimée
    if ($stmt->rowCount() === 0) {
        throw new Exception("Aucune expertise trouvée avec le code: $code_expertise");
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Expertise supprimée avec succès'
    ]);

} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    // Réponse en cas d'erreur de base de données
    error_log("PDOException: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
}
?>