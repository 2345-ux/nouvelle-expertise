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

    echo "<h1>Migration du champ 'victime de'</h1>";
    
    // 1. Vérifier la structure actuelle de la table t_expertises
    $stmt = $pdo->query("SHOW COLUMNS FROM t_expertises LIKE 'type_victime_id'");
    $typeVictimeExists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM t_expertises LIKE 'victime_de_id'");
    $victimeDeIdExists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM t_expertises LIKE 'victime_de'");
    $victimeDeExists = $stmt->rowCount() > 0;
    
    echo "<h2>Étape 1: Analyse de la structure</h2>";
    echo "<ul>";
    echo "<li>Colonne 'type_victime_id': " . ($typeVictimeExists ? '✅ Existe' : '❌ N\'existe pas') . "</li>";
    echo "<li>Colonne 'victime_de_id': " . ($victimeDeIdExists ? '✅ Existe' : '❌ N\'existe pas') . "</li>";
    echo "<li>Colonne 'victime_de': " . ($victimeDeExists ? '✅ Existe' : '❌ N\'existe pas') . "</li>";
    echo "</ul>";
    
    echo "<h2>Étape 2: Migration de la structure</h2>";
    
    // 2. Migration de la structure
    if ($typeVictimeExists) {
        // 2.1 Supprimer la contrainte de clé étrangère si elle existe
        try {
            $pdo->exec("ALTER TABLE t_expertises DROP FOREIGN KEY fk_type_victime");
            echo "<p>✅ Contrainte 'fk_type_victime' supprimée</p>";
        } catch (PDOException $e) {
            echo "<p>ℹ️ La contrainte 'fk_type_victime' n'existe pas ou a déjà été supprimée</p>";
        }
        
        // 2.2 Renommer la colonne
        if (!$victimeDeIdExists) {
            $pdo->exec("ALTER TABLE t_expertises CHANGE COLUMN type_victime_id victime_de_id INT NULL");
            echo "<p>✅ Colonne 'type_victime_id' renommée en 'victime_de_id'</p>";
        } else {
            echo "<p>ℹ️ La colonne 'victime_de_id' existe déjà</p>";
        }
    } else if (!$victimeDeIdExists) {
        // 2.3 Créer la colonne si elle n'existe pas
        $pdo->exec("ALTER TABLE t_expertises ADD COLUMN victime_de_id INT NULL");
        echo "<p>✅ Colonne 'victime_de_id' créée</p>";
    }
    
    // 2.4 Ajouter la contrainte de clé étrangère vers t_types_expertise
    try {
        $pdo->exec("ALTER TABLE t_expertises ADD CONSTRAINT fk_victime_de FOREIGN KEY (victime_de_id) REFERENCES t_types_expertise(id)");
        echo "<p>✅ Contrainte 'fk_victime_de' ajoutée</p>";
    } catch (PDOException $e) {
        if ($e->getCode() == '42000') { // Code d'erreur pour contrainte déjà existante
            echo "<p>ℹ️ La contrainte 'fk_victime_de' existe déjà</p>";
        } else {
            throw $e;
        }
    }
    
    echo "<h2>Étape 3: Migration des données</h2>";
    
    // 3.1 Si nous avons un ancien champ texte 'victime_de'
    if ($victimeDeExists) {
        $stmt = $pdo->query("SELECT DISTINCT victime_de FROM t_expertises WHERE victime_de IS NOT NULL AND victime_de != ''");
        $uniqueValues = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Trouvé " . count($uniqueValues) . " valeurs distinctes pour 'victime_de'</p>";
        
        if (count($uniqueValues) > 0) {
            echo "<ul>";
            foreach ($uniqueValues as $value) {
                // Trouver un type d'expertise similaire ou créer un nouveau si nécessaire
                $stmt = $pdo->prepare("SELECT id FROM t_types_expertise WHERE nom_type LIKE :value");
                $stmt->execute(['value' => "%$value%"]);
                $typeId = $stmt->fetchColumn();
                
                if (!$typeId) {
                    // Créer un nouveau type d'expertise basé sur l'ancienne valeur
                    $stmt = $pdo->prepare("INSERT INTO t_types_expertise (nom_type) VALUES (:value)");
                    $stmt->execute(['value' => $value]);
                    $typeId = $pdo->lastInsertId();
                    echo "<li>✅ Nouveau type d'expertise créé: '$value' (ID: $typeId)</li>";
                }
                
                // Mettre à jour les expertises
                $stmt = $pdo->prepare("UPDATE t_expertises SET victime_de_id = :typeId WHERE victime_de = :value");
                $stmt->execute(['typeId' => $typeId, 'value' => $value]);
                $rowCount = $stmt->rowCount();
                echo "<li>✅ $rowCount expertises mises à jour pour '$value' → ID: $typeId</li>";
            }
            echo "</ul>";
        }
    }
    
    // 3.2 Si nous avions une ancienne colonne type_victime_id avec des données
    if ($typeVictimeExists && !empty($victimeDeIdExists)) {
        echo "<p>Transfert des données de 'type_victime_id' vers 'victime_de_id'...</p>";
        
        // Récupérer les associations entre types de victimes et types d'expertise
        $stmt = $pdo->query("SELECT tv.id as tv_id, te.id as te_id 
                             FROM t_types_victimes tv 
                             JOIN t_types_expertise_victimes tev ON tv.id = tev.type_victime_id
                             JOIN t_types_expertise te ON tev.type_expertise_id = te.id");
        $associations = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        if (count($associations) > 0) {
            echo "<ul>";
            foreach ($associations as $tvId => $teId) {
                $stmt = $pdo->prepare("UPDATE t_expertises SET victime_de_id = :teId WHERE type_victime_id = :tvId");
                $stmt->execute(['teId' => $teId, 'tvId' => $tvId]);
                $rowCount = $stmt->rowCount();
                echo "<li>✅ $rowCount expertises mises à jour: type_victime_id $tvId → victime_de_id $teId</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>ℹ️ Aucune association trouvée entre types de victimes et types d'expertise</p>";
        }
    }
    
    echo "<h2>Étape 4: Nettoyage (optionnel)</h2>";
    echo "<p>⚠️ Une fois que vous avez vérifié que la migration s'est bien déroulée, vous pouvez supprimer l'ancienne colonne 'victime_de':</p>";
    echo "<code>ALTER TABLE t_expertises DROP COLUMN victime_de;</code>";
    
    if ($typeVictimeExists && $victimeDeIdExists) {
        echo "<p>⚠️ Et également supprimer l'ancienne colonne 'type_victime_id' si elle existe encore:</p>";
        echo "<code>ALTER TABLE t_expertises DROP COLUMN type_victime_id;</code>";
    }
    
    echo "<p>✅ Migration terminée avec succès!</p>";
    echo "<p><a href='nouvelle-expertise.html' class='btn btn-primary'>Retour à la page des expertises</a></p>";
    
} catch (Exception $e) {
    echo "<h1>Erreur</h1>";
    echo "<p>Une erreur est survenue : " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 