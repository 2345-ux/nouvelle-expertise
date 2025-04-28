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

    // Modification de la requête SQL pour ne sélectionner que les champs nécessaires
    $sql = "SELECT id, nom_type FROM t_types_expertise WHERE actif = 1 ORDER BY nom_type ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Récupérer les résultats
    $types = $stmt->fetchAll();

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
