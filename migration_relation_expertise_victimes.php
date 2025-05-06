<?php
header('Content-Type: text/html; charset=utf-8');

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

    echo "<h1>Migration de la relation Types d'expertise - Types de victimes</h1>";
    
    // 1. Vérifier si la table des types de victimes existe
    $tableExists = false;
    $stmt = $pdo->query("SHOW TABLES LIKE 't_types_victimes'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<p>❌ La table t_types_victimes n'existe pas. Veuillez exécuter d'abord le script migration_types_victimes.php</p>";
        exit;
    } else {
        echo "<p>✅ La table t_types_victimes existe</p>";
    }
    
    // 2. Créer la table de relation entre types d'expertise et types de victimes
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS t_types_expertise_victimes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            type_expertise_id INT NOT NULL,
            type_victime_id INT NOT NULL,
            FOREIGN KEY (type_expertise_id) REFERENCES t_types_expertise(id),
            FOREIGN KEY (type_victime_id) REFERENCES t_types_victimes(id),
            UNIQUE (type_expertise_id, type_victime_id)
        )
    ");
    echo "<p>✅ Table t_types_expertise_victimes créée</p>";
    
    // 3. Vérifier si la table contient déjà des données
    $stmt = $pdo->query("SELECT COUNT(*) FROM t_types_expertise_victimes");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "<p>💡 La table est vide, ajout des relations par défaut...</p>";
        
        // 4. Récupérer les types d'expertise existants
        $stmt = $pdo->query("SELECT id, nom_type FROM t_types_expertise");
        $typesExpertise = $stmt->fetchAll();
        
        // 5. Récupérer les types de victimes existants
        $stmt = $pdo->query("SELECT id, nom_type_victime FROM t_types_victimes");
        $typesVictimes = $stmt->fetchAll();
        
        // Associations par défaut (à adapter selon vos besoins réels)
        $associations = [
            'Traumatologique' => ['Accident de la route', 'Accident du travail', 'Accident domestique'],
            'Psychiatrique' => ['Agression', 'Violence conjugale', 'Maltraitance'],
            'Neurologique' => ['Accident de la route', 'Accident du travail'],
            'Cardiologique' => ['Accident de la route', 'Accident du travail'],
            'Orthopédique' => ['Accident de la route', 'Accident du travail', 'Accident domestique']
        ];
        
        // Compteur d'associations créées
        $associationsCount = 0;
        
        echo "<ul>";
        foreach ($associations as $expertiseNom => $victimesNoms) {
            // Trouver l'ID du type d'expertise
            $expertiseId = null;
            foreach ($typesExpertise as $typeExpertise) {
                if ($typeExpertise['nom_type'] == $expertiseNom) {
                    $expertiseId = $typeExpertise['id'];
                    break;
                }
            }
            
            if (!$expertiseId) {
                echo "<li>⚠️ Type d'expertise '$expertiseNom' non trouvé, association ignorée</li>";
                continue;
            }
            
            foreach ($victimesNoms as $victimeNom) {
                // Trouver l'ID du type de victime
                $victimeId = null;
                foreach ($typesVictimes as $typeVictime) {
                    if ($typeVictime['nom_type_victime'] == $victimeNom) {
                        $victimeId = $typeVictime['id'];
                        break;
                    }
                }
                
                if (!$victimeId) {
                    echo "<li>⚠️ Type de victime '$victimeNom' non trouvé, association ignorée</li>";
                    continue;
                }
                
                // Insérer l'association
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO t_types_expertise_victimes (type_expertise_id, type_victime_id)
                        VALUES (:expertise_id, :victime_id)
                    ");
                    $stmt->execute([
                        'expertise_id' => $expertiseId,
                        'victime_id' => $victimeId
                    ]);
                    $associationsCount++;
                    echo "<li>✅ Association créée : $expertiseNom → $victimeNom</li>";
                } catch (PDOException $e) {
                    // Ignorer les erreurs de duplication (violation de contrainte UNIQUE)
                    if ($e->getCode() != 23000) { // Code d'erreur pour violation de contrainte UNIQUE
                        throw $e;
                    }
                    echo "<li>ℹ️ Association déjà existante : $expertiseNom → $victimeNom</li>";
                }
            }
        }
        echo "</ul>";
        
        echo "<p>✅ $associationsCount associations créées avec succès</p>";
    } else {
        echo "<p>ℹ️ La table contient déjà des données ($count associations), pas d'insertion automatique</p>";
    }
    
    echo "<p>✅ Migration terminée avec succès!</p>";
    echo "<p><a href='nouvelle-expertise.html'>Retour à la page des expertises</a></p>";
    
} catch (Exception $e) {
    echo "<h1>Erreur</h1>";
    echo "<p>Une erreur est survenue : " . $e->getMessage() . "</p>";
}
?> 