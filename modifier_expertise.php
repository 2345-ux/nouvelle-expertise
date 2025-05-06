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

    // Récupération du code d'expertise (clé pour la mise à jour)
    $code_expertise = $_POST['code_expertise'] ?? null;
    
    if (!$code_expertise) {
        throw new Exception("Le code d'expertise est obligatoire pour la modification");
    }

    // Récupération des données requises
    $medecin_id = $_POST['medecin_id'] ?? null;
    $nom_victime = $_POST['nom_victime'] ?? null;
    $age_victime = isset($_POST['age_victime']) ? intval($_POST['age_victime']) : null;
    $provenance = $_POST['provenance'] ?? null;
    $type_requisition = $_POST['type_requisition'] ?? null;
    $victime_de_id = $_POST['victime_de_id'] ?? null;

    // Validation des données requises
    if (!$medecin_id || !$nom_victime || $age_victime === null || !$provenance) {
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

    // Récupérer les champs optionnels
    $numero_requisition = !empty($_POST['numero_requisition']) ? $_POST['numero_requisition'] : null;
    $date_requisition = !empty($_POST['date_requisition']) ? $_POST['date_requisition'] : null;
    $consultation_examen = !empty($_POST['consultation_examen']) ? $_POST['consultation_examen'] : null;

    // Vérifier si victime_de_id est valide s'il est fourni
    if (!empty($victime_de_id)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM t_types_expertise WHERE id = :id");
        $stmt->execute([':id' => $victime_de_id]);
        if ($stmt->fetchColumn() == 0) {
            // Si l'ID n'existe pas, on le met à NULL
            error_log("victime_de_id invalide: $victime_de_id - sera défini à NULL");
            $victime_de_id = null;
        }
    }

    // Préparation et exécution de la requête de mise à jour
    $stmt = $pdo->prepare("
        UPDATE t_expertises SET
            medecin_id = ?,
            nom_victime = ?,
            age_victime = ?,
            provenance = ?,
            numero_requisition = ?,
            date_requisition = ?,
            type_requisition = ?,
            victime_de_id = ?,
            consultation_examen = ?
        WHERE code_expertise = ?
    ");

    $result = $stmt->execute([
        $medecin_id,
        $nom_victime,
        $age_victime,
        $provenance,
        $numero_requisition,
        $date_requisition,
        $type_requisition,
        $victime_de_id,
        $consultation_examen,
        $code_expertise // Condition WHERE
    ]);

    if (!$result) {
        error_log("Erreur SQL: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Erreur de mise à jour en base de données");
    }

    // Vérifier si l'expertise a été trouvée et mise à jour
    if ($stmt->rowCount() === 0) {
        throw new Exception("Aucune expertise trouvée avec le code: $code_expertise");
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Expertise modifiée avec succès',
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
?>