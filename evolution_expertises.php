<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=expertise_medicale;charset=utf8",
        "saw24",
        "saw24",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Récupérer les statistiques des 6 derniers mois
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(date_heure, '%Y-%m') as mois,
            COUNT(*) as total
        FROM 
            t_expertises
        WHERE 
            date_heure >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
        GROUP BY 
            DATE_FORMAT(date_heure, '%Y-%m')
        ORDER BY 
            mois ASC
    ");

    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formater les données pour Chart.js
    $labels = [];
    $data = [];
    $moisFr = [
        '01' => 'Janvier', '02' => 'Février', '03' => 'Mars',
        '04' => 'Avril', '05' => 'Mai', '06' => 'Juin',
        '07' => 'Juillet', '08' => 'Août', '09' => 'Septembre',
        '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
    ];

    foreach ($stats as $stat) {
        list($annee, $mois) = explode('-', $stat['mois']);
        $labels[] = $moisFr[$mois] . ' ' . $annee;
        $data[] = intval($stat['total']);
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'labels' => $labels,
            'values' => $data
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
