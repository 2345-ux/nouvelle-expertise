<?php
header('Content-Type: application/json');

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Vérifier si la requête est en POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    // Récupération et validation des données du formulaire
    $code_type = $_POST['code_type'] ?? '';
    $nom_type = $_POST['nom_type'] ?? '';
    $description = $_POST['description_type'] ?? '';
    $categorie = $_POST['categorie_type'] ?? '';
    $statut = $_POST['statut_type'] ?? 'actif';

    // Validation des données requises
    if (empty($code_type)) {
        throw new Exception("Le code du type d'expertise est obligatoire");
    }
    if (empty($nom_type)) {
        throw new Exception("Le nom du type d'expertise est obligatoire");
    }

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

    // Vérifier si le type existe
    $check = $pdo->prepare("SELECT code_type FROM t_types_expertise WHERE code_type = ?");
    $check->execute([$code_type]);
    if ($check->rowCount() === 0) {
        throw new Exception("Le type d'expertise n'existe pas");
    }

    // Préparation de la requête de mise à jour
    $sql = "UPDATE t_types_expertise SET 
            nom_type = :nom_type,
            description = :description,
            categorie = :categorie,
            statut = :statut,
            date_modification = NOW()
            WHERE code_type = :code_type";

    $stmt = $pdo->prepare($sql);
    
    // Exécution de la requête avec les paramètres
    $success = $stmt->execute([
        ':code_type' => $code_type,
        ':nom_type' => $nom_type,
        ':description' => $description,
        ':categorie' => $categorie,
        ':statut' => $statut
    ]);

    if (!$success) {
        throw new Exception("Erreur lors de la mise à jour");
    }

    // Réponse en cas de succès
    echo json_encode([
        'status' => 'success',
        'message' => 'Type d\'expertise modifié avec succès !'
    ]);

} catch (PDOException $e) {
    error_log("Erreur PDO: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
