<?php
header('Content-Type: application/json');

try {
    // Vérifier si la requête est en POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Récupération des données du formulaire
    $code = $_POST['code'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $sexe = $_POST['sexe'] ?? '';
    $specialite = $_POST['specialite'] ?? null;
    $telephone = $_POST['telephone'] ?? null;
    $email = $_POST['email'] ?? null;
    
    // Vérification des données requises
    if (empty($code) || empty($nom) || empty($sexe)) {
        throw new Exception("Le code, le nom et le sexe du médecin sont obligatoires.");
    }
    
    // Préparation de la requête de mise à jour
    $stmt = $pdo->prepare("
        UPDATE t_medecins 
        SET nom = :nom, 
            sexe = :sexe, 
            specialite = NULLIF(:specialite, ''),
            telephone = NULLIF(:telephone, ''),
            email = NULLIF(:email, '')
        WHERE code = :code
    ");
    
    // Exécution de la requête
    $result = $stmt->execute([
        ':code' => $code,
        ':nom' => $nom,
        ':sexe' => $sexe,
        ':specialite' => $specialite,
        ':telephone' => $telephone,
        ':email' => $email
    ]);
    
    // Vérification si une ligne a été modifiée
    if ($stmt->rowCount() === 0) {
        throw new Exception("Aucun médecin trouvé avec ce code ou aucune modification effectuée.");
    }
    
    // Réponse en cas de succès
    echo json_encode([
        'status' => 'success',
        'message' => 'Médecin modifié avec succès !'
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
?>
