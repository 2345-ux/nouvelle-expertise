<?php
header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Requête SQL simple pour commencer
    $sql = "SELECT * FROM t_types_expertise ORDER BY nom_type ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Récupérer les résultats
    $types = $stmt->fetchAll();
    
    // Log des données pour debug
    error_log("Types d'expertise récupérés: " . print_r($types, true));
    
    // Note importante: On utilise directement le champ code_type sans créer de champ 'id' supplémentaire
    // Cela garantit la compatibilité avec le formulaire qui envoie cette valeur à ajout_expertise.php

    // Envoyer la réponse
    echo json_encode([
        'status' => 'success',
        'types' => $types
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur de base de données',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur serveur',
        'details' => $e->getMessage()
    ]);
}
?>
