<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non authentifie']);
    exit();
}

include "../config/db.php";

$action = isset($_GET['action']) ? $_GET['action'] : '';

header('Content-Type: application/json');

switch ($action) {
    case 'budgets_globaux':
        $sql = "SELECT annee, COALESCE(SUM(montant), 0) as total FROM budgets GROUP BY annee ORDER BY annee";
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;
        
    case 'depenses_globales':
        $sql = "SELECT annee, COALESCE(SUM(montant), 0) as total FROM depense_realisees GROUP BY annee ORDER BY annee";
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;
        
    case 'taux_execution':
        $annee = isset($_GET['annee']) ? $_GET['annee'] : null;
        $sql = "
            SELECT 
                d.nom_departement,
                COALESCE(SUM(b.montant), 0) as budget,
                COALESCE(SUM(dr.montant), 0) as depense
            FROM departements d
            LEFT JOIN budgets b ON b.id_departement = d.id_departement" . ($annee ? " AND b.annee = :annee" : "") . "
            LEFT JOIN depense_realisees dr ON dr.id_departement = d.id_departement" . ($annee ? " AND dr.annee = :annee" : "") . "
            GROUP BY d.id_departement, d.nom_departement
            ORDER BY d.nom_departement
        ";
        $stmt = $pdo->prepare($sql);
        if ($annee) {
            $stmt->execute([':annee' => $annee]);
        } else {
            $stmt->execute();
        }
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as &$row) {
            $budget = floatval($row['budget']);
            $depense = floatval($row['depense']);
            $row['taux'] = $budget > 0 ? round(($depense / $budget) * 100, 1) : 0;
            $row['disponible'] = $budget - $depense;
        }
        echo json_encode($results);
        break;
        
    case 'evolution':
        $sql = "
            SELECT 
                b.annee,
                d.nom_departement,
                COALESCE(SUM(b.montant), 0) as budget,
                COALESCE(SUM(dr.montant), 0) as depense
            FROM budgets b
            LEFT JOIN departements d ON b.id_departement = d.id_departement
            LEFT JOIN depense_realisees dr ON dr.id_departement = d.id_departement AND dr.annee = b.annee
            GROUP BY b.annee, d.id_departement, d.nom_departement
            ORDER BY b.annee, d.nom_departement
        ";
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;
        
    default:
        echo json_encode(['error' => 'Action non reconnue']);
        break;
}
?>