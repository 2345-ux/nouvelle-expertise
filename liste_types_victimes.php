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

    // Requête SQL pour récupérer les types de victimes
    $sql = "SELECT * FROM t_types_victimes ORDER BY nom_type_victime ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Récupérer les résultats
    $types_victimes = $stmt->fetchAll();

    // Envoyer la réponse
    echo json_encode([
        'status' => 'success',
        'types_victimes' => $types_victimes
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
