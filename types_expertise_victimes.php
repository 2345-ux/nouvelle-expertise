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

    // Récupérer l'ID du type d'expertise depuis la requête
    $type_expertise_id = isset($_GET['type_expertise_id']) ? intval($_GET['type_expertise_id']) : null;

    if ($type_expertise_id) {
        // Si un type d'expertise est spécifié, récupérer les types de victimes associés
        $sql = "
            SELECT tv.* 
            FROM t_types_victimes tv
            JOIN t_types_expertise_victimes tev ON tv.id = tev.type_victime_id
            WHERE tev.type_expertise_id = :type_expertise_id
            ORDER BY tv.nom_type_victime ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['type_expertise_id' => $type_expertise_id]);
    } else {
        // Si aucun type d'expertise n'est spécifié, récupérer tous les types de victimes
        $sql = "SELECT * FROM t_types_victimes ORDER BY nom_type_victime ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

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