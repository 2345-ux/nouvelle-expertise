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

    echo "<h1>Migration des types de victimes</h1>";
    
    // 1. Créer la table des types de victimes
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS t_types_victimes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nom_type_victime VARCHAR(100) NOT NULL,
            description TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p>✅ Table t_types_victimes créée</p>";
    
    // 2. Vérifier si la table contient déjà des données
    $stmt = $pdo->query("SELECT COUNT(*) FROM t_types_victimes");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // 3. Insérer les types de victimes par défaut
        $pdo->exec("
            INSERT INTO t_types_victimes (nom_type_victime, description) VALUES
            ('Accident de la route', 'Victimes d\'accidents sur la voie publique'),
            ('Agression', 'Victimes d\'agressions physiques'),
            ('Accident domestique', 'Accidents au domicile'),
            ('Accident du travail', 'Accidents en milieu professionnel'),
            ('Violence conjugale', 'Violences entre conjoints'),
            ('Maltraitance', 'Maltraitance sur personne vulnérable')
        ");
        echo "<p>✅ Types de victimes par défaut ajoutés</p>";
    } else {
        echo "<p>ℹ️ La table contient déjà des données, pas d'insertion automatique</p>";
    }
    
    // 4. Vérifier si la colonne type_victime_id existe déjà dans t_expertises
    $stmt = $pdo->query("SHOW COLUMNS FROM t_expertises LIKE 'type_victime_id'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        // 5. Ajouter la colonne
        $pdo->exec("ALTER TABLE t_expertises ADD COLUMN type_victime_id INT");
        echo "<p>✅ Colonne type_victime_id ajoutée à la table t_expertises</p>";
        
        // 6. Ajouter la contrainte de clé étrangère
        $pdo->exec("ALTER TABLE t_expertises ADD CONSTRAINT fk_type_victime FOREIGN KEY (type_victime_id) REFERENCES t_types_victimes(id)");
        echo "<p>✅ Contrainte de clé étrangère ajoutée</p>";
    } else {
        echo "<p>ℹ️ La colonne type_victime_id existe déjà</p>";
    }
    
    // 7. Vérifier si l'ancienne colonne victime_de existe
    $stmt = $pdo->query("SHOW COLUMNS FROM t_expertises LIKE 'victime_de'");
    $oldColumnExists = $stmt->rowCount() > 0;
    
    if ($oldColumnExists) {
        echo "<p>⚠️ Migration des données : Migration des valeurs de l'ancienne colonne 'victime_de' vers la nouvelle structure</p>";
        
        // 8. Récupérer les valeurs distinctes de l'ancienne colonne
        $stmt = $pdo->query("SELECT DISTINCT victime_de FROM t_expertises WHERE victime_de IS NOT NULL AND victime_de != ''");
        $uniqueValues = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<ul>";
        foreach ($uniqueValues as $value) {
            // 9. Pour chaque valeur, vérifier si un type similaire existe déjà
            $stmt = $pdo->prepare("SELECT id FROM t_types_victimes WHERE nom_type_victime = :value");
            $stmt->execute(['value' => $value]);
            $typeId = $stmt->fetchColumn();
            
            if (!$typeId) {
                // 10. Si le type n'existe pas, l'ajouter
                $stmt = $pdo->prepare("INSERT INTO t_types_victimes (nom_type_victime) VALUES (:value)");
                $stmt->execute(['value' => $value]);
                $typeId = $pdo->lastInsertId();
                echo "<li>Nouveau type ajouté : '$value' (ID: $typeId)</li>";
            }
            
            // 11. Mettre à jour les expertises avec cette valeur
            $stmt = $pdo->prepare("UPDATE t_expertises SET type_victime_id = :typeId WHERE victime_de = :value");
            $stmt->execute(['typeId' => $typeId, 'value' => $value]);
            $rowCount = $stmt->rowCount();
            echo "<li>$rowCount expertises mises à jour pour le type '$value'</li>";
        }
        echo "</ul>";
        
        echo "<p>⚠️ L'ancienne colonne 'victime_de' a été conservée pour référence. Vous pourrez la supprimer manuellement après avoir vérifié la migration.</p>";
    } else {
        echo "<p>ℹ️ L'ancienne colonne 'victime_de' n'existe pas ou a déjà été supprimée</p>";
    }
    
    echo "<p>✅ Migration terminée avec succès!</p>";
    echo "<p><a href='nouvelle-expertise.html'>Retour à la page des expertises</a></p>";
    
} catch (Exception $e) {
    echo "<h1>Erreur</h1>";
    echo "<p>Une erreur est survenue : " . $e->getMessage() . "</p>";
}
?> 