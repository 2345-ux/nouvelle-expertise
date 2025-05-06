<?php
header('Content-Type: application/json');

// Activer l'affichage détaillé des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log des données reçues
$raw_data = file_get_contents('php://input');
error_log('POST data received (raw): ' . $raw_data);
error_log('$_POST contents: ' . print_r($_POST, true));

try {
    // Debug: Afficher les données reçues
    error_log("Données reçues: " . print_r($_POST, true));
    
    // Récupération et validation des données requises
    $medecin_id = $_POST['medecin_id'] ?? null;
    $nom_victime = $_POST['nom_victime'] ?? null;
    $age_victime = isset($_POST['age_victime']) ? intval($_POST['age_victime']) : null;
    $provenance = $_POST['provenance'] ?? null;
    $type_requisition = $_POST['type_requisition'] ?? null;
    
    // Utiliser victime_de_id tel quel sans conversion
    $victime_de_id = $_POST['victime_de_id'] ?? null;
    // Si la valeur est vide ou "null", la mettre à NULL
    if (empty($victime_de_id) || $victime_de_id === "null") {
        $victime_de_id = null;
    }
    
    error_log("Reçu victime_de_id: " . var_export($victime_de_id, true));
    
    $code_expertise = $_POST['code_expertise'] ?? ('EXP-' . date('Ymd') . '-' . sprintf('%03d', rand(1, 999)));
    
    // Debug des valeurs extraites
    error_log("medecin_id: " . var_export($medecin_id, true));
    error_log("nom_victime: " . var_export($nom_victime, true));
    error_log("age_victime: " . var_export($age_victime, true));
    error_log("provenance: " . var_export($provenance, true));
    error_log("victime_de_id: " . var_export($victime_de_id, true));
    error_log("code_expertise: " . var_export($code_expertise, true));

    // Validation des données requises
    if (!$medecin_id || !$nom_victime || !$age_victime || !$provenance) {
        error_log("Validation échouée: medecin_id={$medecin_id}, nom_victime={$nom_victime}, age_victime={$age_victime}, provenance={$provenance}");
        throw new Exception("Les champs médecin, nom de la victime, âge et provenance sont obligatoires");
    }

    // Validation de l'âge
    if ($age_victime <= 0 || $age_victime > 120) {
        throw new Exception("L'âge doit être compris entre 1 et 120 ans");
    }

    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Debug de la requête avant exécution
    $params = [
        $code_expertise,
        $medecin_id,
        $nom_victime,
        $age_victime,
        $provenance,
        $type_requisition,
        $_POST['numero_requisition'] ?? null,
        $_POST['date_requisition'] ?? null,
        $victime_de_id,
        $_POST['consultation_examen'] ?? null
    ];
    error_log("Paramètres requête SQL: " . print_r($params, true));

    // Préparation et exécution de la requête
    $stmt = $pdo->prepare("
        INSERT INTO t_expertises (
            code_expertise, 
            medecin_id, 
            nom_victime, 
            age_victime, 
            provenance,
            type_requisition,
            numero_requisition,
            date_requisition,
            victime_de_id,
            consultation_examen,
            date_heure
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $result = $stmt->execute($params);

    if (!$result) {
        error_log("Erreur SQL: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Erreur d'insertion en base de données");
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Expertise ajoutée avec succès',
        'code' => $code_expertise
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

/**
 * Génère un code unique basé sur la date/heure actuelle et un nombre aléatoire
 * Format: EXP-YYYYMMDD-xxx (où xxx est un nombre aléatoire)
 * 
 * @return string Le code unique généré
 */
function generateUniqueCode()
{
    // Obtenir la date actuelle
    $date = new DateTime();
    
    // Formater la date selon le format souhaité
    $uniqueCode = 'EXP-' . $date->format('Ymd') . '-' . sprintf('%03d', rand(1, 999));
    
    return $uniqueCode;
}
?>