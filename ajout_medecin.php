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
    $nom = $_POST['nom'] ?? '';
    $sexe = $_POST['sexe'] ?? '';
    $specialite = $_POST['specialite'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // Vérification des données requises
    if (empty($nom) || empty($sexe)) {
        throw new Exception("Le nom et le sexe sont obligatoires");
    }
    
    // Génération du code médecin
    $code_medecin = generateUniqueCode();
    
    // Préparation de la requête d'insertion
    $stmt = $pdo->prepare("
        INSERT INTO t_medecins (
            code, 
            nom, 
            sexe, 
            specialite, 
            telephone, 
            email
        ) VALUES (
            :code,
            :nom,
            :sexe,
            :specialite,
            :telephone,
            :email
        )
    ");
    
    // Exécution de la requête
    $stmt->execute([
        ':code' => $code_medecin,
        ':nom' => $nom,
        ':sexe' => $sexe,
        ':specialite' => $specialite,
        ':telephone' => $telephone,
        ':email' => $email
    ]);
    
    // Réponse en cas de succès
    echo json_encode([
        'status' => 'success',
        'message' => 'Médecin ajouté avec succès',
        'code' => $code_medecin
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

function generateUniqueCode() {
    $date = new DateTime();
    return 'MED-' . $date->format('Ymd') . sprintf('%03d', rand(1, 999));
}
?>
