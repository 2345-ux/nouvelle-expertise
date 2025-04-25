<?php
// supprimer_medecin.php
header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Récupération du code du médecin à supprimer
    $code = $_POST['code'] ?? '';
    
    // Vérification du code
    if (empty($code)) {
        throw new Exception("Le code du médecin est obligatoire.");
    }
    
    // Vérification si le médecin est utilisé dans des expertises
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM t_expertises WHERE medecin_id = :code");
    $stmt->execute([':code' => $code]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] > 0) {
        throw new Exception("Ce médecin ne peut pas être supprimé car il est associé à " . $result['total'] . " expertise(s).");
    }
    
    // Préparation de la requête de suppression
    $stmt = $pdo->prepare("DELETE FROM t_medecins WHERE code = :code");
    
    // Exécution de la requête avec le paramètre
    $stmt->execute([':code' => $code]);
    
    // Vérification si une ligne a été supprimée
    if ($stmt->rowCount() === 0) {
        throw new Exception("Aucun médecin trouvé avec ce code.");
    }
    
    // Réponse en cas de succès
    echo json_encode([
        'status' => 'success',
        'message' => 'Médecin supprimé avec succès !'
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