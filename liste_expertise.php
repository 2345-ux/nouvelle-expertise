<?php
// liste_expertises.php
header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Récupération des paramètres de recherche et de filtrage (facultatif)
    $recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
    $date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
    $date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';
    $type_expertise = isset($_GET['type_expertise']) ? $_GET['type_expertise'] : '';
    $code_expertise = isset($_GET['code_expertise']) ? $_GET['code_expertise'] : '';

    // Modification de la construction de la requête SQL de base
    $sql = "SELECT 
            e.code_expertise,
            e.date_heure,
            e.nom_victime,
            e.age_victime,
            e.provenance,
            e.numero_requisition,
            e.date_requisition,
            e.type_requisition,
            e.victime_de_id,
            CASE 
                WHEN e.consultation_examen IS NOT NULL AND e.consultation_examen != '' 
                THEN 'Consultation disponible'
                ELSE 'Aucune consultation'
            END as consultation_status,
            e.medecin_id,
            m.nom AS nom_medecin,
            m.sexe AS sexe_medecin,
            te.nom_type AS nom_victime_de";

    // Si un code_expertise spécifique est demandé, on récupère aussi consultation_examen
    if (!empty($_GET['code_expertise'])) {
        $sql = str_replace('END as consultation_status', 'END as consultation_status, e.consultation_examen', $sql);
    }

    $sql .= " FROM t_expertises e
        LEFT JOIN t_medecins m ON e.medecin_id = m.code
        LEFT JOIN t_types_expertise te ON e.victime_de_id = te.code_type";

    // Si un code_expertise spécifique est demandé
    if (!empty($_GET['code_expertise'])) {
        $sql .= " WHERE e.code_expertise = :code_expertise";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':code_expertise' => $_GET['code_expertise']]);
        $expertises = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($expertises)) {
            echo json_encode([
                'status' => 'success',
                'expertises' => $expertises
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Expertise non trouvée'
            ]);
        }
        exit;
    }

    // Ajout des conditions de filtrage si nécessaire
    $conditions = [];
    $params = [];

    if (!empty($code_expertise)) {
        // Si un code d'expertise spécifique est demandé
        $conditions[] = "e.code_expertise = :code_expertise";
        $params[':code_expertise'] = $code_expertise;
    }

    if (!empty($recherche)) {
        $conditions[] = "(e.code_expertise LIKE :recherche 
                        OR e.nom_victime LIKE :recherche 
                        OR m.nom LIKE :recherche 
                        OR e.numero_requisition LIKE :recherche)";
        $params[':recherche'] = "%$recherche%";
    }

    if (!empty($date_debut)) {
        $conditions[] = "e.date_heure >= :date_debut";
        $params[':date_debut'] = $date_debut . ' 00:00:00';
    }

    if (!empty($date_fin)) {
        $conditions[] = "e.date_heure <= :date_fin";
        $params[':date_fin'] = $date_fin . ' 23:59:59';
    }

    if (!empty($type_expertise)) {
        $conditions[] = "e.type_requisition = :type_expertise";
        $params[':type_expertise'] = $type_expertise;
    }

    // Ajout des conditions à la requête SQL si elles existent
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // Tri par date (le plus récent en premier)
    $sql .= " ORDER BY e.date_heure DESC";

    // Préparation et exécution de la requête
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Récupération de tous les résultats
    $expertises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatage des dates pour un affichage plus convivial
    foreach ($expertises as &$expertise) {
        // Formatage de la date et heure
        if (isset($expertise['date_heure'])) {
            $date = new DateTime($expertise['date_heure']);
            $expertise['date_heure_formatee'] = $date->format('d/m/Y H:i');
        }
        
        // Formatage de la date de réquisition
        if (isset($expertise['date_requisition']) && !empty($expertise['date_requisition'])) {
            $date = new DateTime($expertise['date_requisition']);
            $expertise['date_requisition_formatee'] = $date->format('d/m/Y');
        } else {
            $expertise['date_requisition_formatee'] = '';
        }
        
        // Calcul de l'âge en années si l'âge est fourni en nombre
        if (isset($expertise['age_victime']) && is_numeric($expertise['age_victime'])) {
            $expertise['age_victime_formatee'] = $expertise['age_victime'] . ' ans';
        } else {
            $expertise['age_victime_formatee'] = $expertise['age_victime'];
        }
        
        // Si nom_victime_de est NULL, afficher une valeur par défaut ou le code
        if (empty($expertise['nom_victime_de']) && !empty($expertise['victime_de_id'])) {
            // Essai de récupérer le nom du type d'expertise directement
            $stmt = $pdo->prepare("SELECT nom_type FROM t_types_expertise WHERE code_type = ?");
            $stmt->execute([$expertise['victime_de_id']]);
            $nom_type = $stmt->fetchColumn();
            
            if ($nom_type) {
                $expertise['nom_victime_de'] = $nom_type;
            } else {
                // Si on ne trouve pas le nom, utiliser l'ID comme fallback
                $expertise['nom_victime_de'] = 'Type ' . $expertise['victime_de_id'];
            }
        }
    }

    // Envoi de la réponse JSON
    echo json_encode([
        'status' => 'success',
        'expertises' => $expertises,
        'total' => count($expertises)
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
