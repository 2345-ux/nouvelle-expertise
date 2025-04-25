<?php
header('Content-Type: application/json');

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Debug: Afficher les données reçues
    error_log("Données reçues: " . print_r($_POST, true));

    // Récupération des données requises
    $medecin_id = $_POST['medecin_id'] ?? null;
    $nom_victime = $_POST['nom_victime'] ?? null;
    $age_victime = isset($_POST['age_victime']) ? intval($_POST['age_victime']) : null;
    $provenance = $_POST['provenance'] ?? null;
    $type_requisition = $_POST['type_requisition'] ?? null;

    // Validation des données requises
    if (!$medecin_id || !$nom_victime || !$age_victime || !$provenance || !$type_requisition) {
        throw new Exception("Tous les champs obligatoires doivent être remplis");
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

    // Générer le code expertise
    $code_expertise = 'EXP-' . date('Ymd') . '-' . sprintf('%03d', rand(1, 999));

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
            victime_de,
            consultation_examen,
            date_heure
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $code_expertise,
        $medecin_id,
        $nom_victime,
        $age_victime,
        $provenance,
        $type_requisition,
        $_POST['numero_requisition'] ?? null,
        $_POST['date_requisition'] ?? null,
        $_POST['victime_de'] ?? null,
        $_POST['consultation_examen'] ?? null
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Expertise ajoutée avec succès',
        'code' => $code_expertise
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    // Réponse en cas d'erreur
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}

/**
 * Génère un code unique basé sur la date/heure actuelle et un nombre aléatoire
 * Format: YYYYMMDDHHMMSSxxxx (où xxxx est un nombre aléatoire)
 * 
 * @return string Le code unique généré
 */
function generateUniqueCode()
{
    // Obtenir la date et l'heure actuelles
    $date = new DateTime();

    // Formater la date et l'heure selon le format souhaité
    $uniqueCode = 'EXP-' . $date->format('YmdHis') . sprintf('%04d', rand(0, 9999));

    // S'assurer que le code est une chaîne de caractères
    return strval($uniqueCode);
}
?>
