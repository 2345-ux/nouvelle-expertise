<?php
// modifier_expertise.php
header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Récupération des données du formulaire
    $code_expertise = $_POST['code_expertise'] ?? '';
    $medecin_id = $_POST['medecin_id'] ?? '';
    $nom_victime = $_POST['nom_victime'] ?? '';
    $age_victime = $_POST['age_victime'] ?? '';
    $provenance = $_POST['provenance'] ?? '';
    $numero_requisition = $_POST['numero_requisition'] ?? '';
    $date_requisition = $_POST['date_requisition'] ?? '';
    $type_requisition = $_POST['type_requisition'] ?? '';
    $victime_de = $_POST['victime_de'] ?? '';
    $consultation_examen = $_POST['consultation_examen'] ?? '';
    
    // Vérification des données obligatoires
    if (empty($code_expertise) || empty($medecin_id) || empty($nom_victime)) {
        throw new Exception("Le code d'expertise, le médecin et le nom de la victime sont obligatoires.");
    }
    
    // Vérification de l'existence de l'expertise
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM t_expertises WHERE code_expertise = :code_expertise");
    $stmt->execute([':code_expertise' => $code_expertise]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception("Cette expertise n'existe pas.");
    }
    
    // Préparation de la requête de mise à jour
    $stmt = $pdo->prepare("
        UPDATE t_expertises SET 
            medecin_id = :medecin_id,
            nom_victime = :nom_victime,
            age_victime = :age_victime,
            provenance = :provenance,
            numero_requisition = :numero_requisition,
            date_requisition = :date_requisition,
            type_requisition = :type_requisition,
            victime_de = :victime_de,
            consultation_examen = :consultation_examen
        WHERE code_expertise = :code_expertise
    ");
    
    // Exécution de la requête avec les paramètres
    $stmt->execute([
        ':code_expertise' => $code_expertise,
        ':medecin_id' => $medecin_id,
        ':nom_victime' => $nom_victime,
        ':age_victime' => $age_victime,
        ':provenance' => $provenance,
        ':numero_requisition' => $numero_requisition,
        ':date_requisition' => $date_requisition,
        ':type_requisition' => $type_requisition,
        ':victime_de' => $victime_de,
        ':consultation_examen' => $consultation_examen
    ]);
    
    // Réponse en cas de succès
    echo json_encode([
        'status' => 'success',
        'message' => 'Expertise médicale modifiée avec succès !'
    ]);
    
} catch (PDOException $e) {
    // Réponse en cas d'erreur de base de données
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Réponse en cas d'erreur générale
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
