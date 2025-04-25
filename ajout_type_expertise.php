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

    // Valider les données requises
    $required_fields = ['nom_type'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Le champ $field est requis");
        }
    }

    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Génération d'un code unique pour le type d'expertise
    $code_type = generateUniqueTypeCode();
    
    // Récupération des données du formulaire
    $nom_type = $_POST['nom_type'] ?? '';
    $description = $_POST['description_type'] ?? '';
    $categorie = $_POST['categorie_type'] ?? '';
    $statut = $_POST['statut_type'] ?? 'actif';

    // Préparation de la requête d'insertion
    $stmt = $pdo->prepare("
        INSERT INTO t_types_expertise (
            code_type,
            nom_type,
            description,
            categorie,
            statut
        ) VALUES (
            :code_type,
            :nom_type,
            :description,
            :categorie,
            :statut
        )
    ");

    // Exécution de la requête avec les paramètres
    $stmt->execute([
        ':code_type' => $code_type,
        ':nom_type' => $nom_type,
        ':description' => $description,
        ':categorie' => $categorie,
        ':statut' => $statut
    ]);

    // Réponse en cas de succès
    echo json_encode([
        'status' => 'success',
        'message' => 'Type d\'expertise ajouté avec succès !',
        'code' => $code_type
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

/**
 * Génère un code unique pour le type d'expertise
 * Format: EXP-T-XXX (où XXX est un nombre incrémental)
 * 
 * @return string Le code unique généré
 */
function generateUniqueTypeCode()
{
    $date = new DateTime();
    return 'EXP-T-' . $date->format('ymd') . sprintf('%03d', rand(1, 999));
}
?>
