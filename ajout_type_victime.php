<?php
header('Content-Type: application/json');

// Vérifier si la méthode est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Méthode non autorisée. Utilisez POST'
    ]);
    exit;
}

// Vérifier si les données sont présentes
$inputData = json_decode(file_get_contents('php://input'), true);
if (!$inputData) {
    $inputData = $_POST;
}

if (empty($inputData['nom_type_victime'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Le nom du type de victime est requis'
    ]);
    exit;
}

// Récupérer et nettoyer les données
$nom_type_victime = trim($inputData['nom_type_victime']);
$description = isset($inputData['description']) ? trim($inputData['description']) : null;

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

    // Vérifier si le type de victime existe déjà
    $check_sql = "SELECT COUNT(*) FROM t_types_victimes WHERE nom_type_victime = :nom_type_victime";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(['nom_type_victime' => $nom_type_victime]);
    
    if ($check_stmt->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode([
            'status' => 'error',
            'message' => 'Ce type de victime existe déjà'
        ]);
        exit;
    }

    // Insérer le nouveau type de victime
    $sql = "INSERT INTO t_types_victimes (nom_type_victime, description) VALUES (:nom_type_victime, :description)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nom_type_victime' => $nom_type_victime,
        'description' => $description
    ]);

    $type_id = $pdo->lastInsertId();

    echo json_encode([
        'status' => 'success',
        'message' => 'Type de victime ajouté avec succès',
        'type_id' => $type_id
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur de base de données',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur serveur',
        'details' => $e->getMessage()
    ]);
}
?> 