<?php
// liste_medecins.php
header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Récupération du paramètre de recherche (facultatif)
    $recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
    
    // Construction de la requête SQL de base
    $sql = "SELECT 
                code, 
                nom, 
                sexe, 
                specialite,
                telephone,
                email
            FROM t_medecins";
    
    // Ajout de la condition de recherche si nécessaire
    $params = [];
    if (!empty($recherche)) {
        $sql .= " WHERE nom LIKE :recherche OR code LIKE :recherche";
        $params[':recherche'] = "%$recherche%";
    }
    
    // Tri par nom
    $sql .= " ORDER BY nom ASC";
    
    // Préparation et exécution de la requête
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Récupération de tous les résultats
    $medecins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Envoi de la réponse JSON
    echo json_encode([
        'status' => 'success',
        'medecins' => $medecins,
        'total' => count($medecins)
    ]);
    
} catch (PDOException $e) {
    // Gestion des erreurs de base de données
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Gestion des autres erreurs
    echo json_encode([
        'status' => 'error',
        'message' => 'Exception: ' . $e->getMessage()
    ]);
}
?>
