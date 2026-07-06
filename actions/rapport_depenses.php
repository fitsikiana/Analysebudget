<?php
include '../config/db.php';

function getRapportDepenses($pdo, $annee = null, $departement = null) {
    $conditions = [];
    $params = [];
    
    if ($annee && $annee != 'all') {
        $conditions[] = "b.annee = ?";
        $params[] = $annee;
    }
    
    if ($departement && $departement != 'all') {
        $conditions[] = "d.nom_departement = ?";
        $params[] = $departement;
    }
    
    $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";
    
    $sql = "SELECT 
                d.nom_departement,
                b.annee,
                dr.rubrique,
                dr.montant,
                dr.date_depense,
                IFNULL((SELECT dp.montant_departement FROM depense_prevision dp WHERE dp.id_budget = b.id_budget AND dp.id_departement = d.id_departement AND dp.rubrique = dr.rubrique LIMIT 1), 0) AS budget_prevu
            FROM depense_realisees dr
            JOIN departements d ON dr.id_departement = d.id_departement
            JOIN budgets b ON dr.id_budget = b.id_budget
            $whereClause
            AND NOT (dr.rubrique REGEXP '^[0-9]+$')
            ORDER BY b.annee DESC, dr.date_depense DESC, d.nom_departement ASC";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function getTotalBudgetDepartement($pdo, $departement, $annee) {
    $sql = "SELECT IFNULL(SUM(dp.montant_departement), 0) as total 
            FROM depense_prevision dp
            JOIN departements d ON dp.id_departement = d.id_departement
            JOIN budgets b ON dp.id_budget = b.id_budget
            WHERE d.nom_departement = ? AND b.annee = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$departement, $annee]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return floatval($result['total'] ?? 0);
}

function getTotalDepenseDepartement($pdo, $departement, $annee) {
    $sql = "SELECT IFNULL(SUM(dr.montant), 0) as total 
            FROM depense_realisees dr
            JOIN departements d ON dr.id_departement = d.id_departement
            JOIN budgets b ON dr.id_budget = b.id_budget
            WHERE d.nom_departement = ? AND b.annee = ?
            AND NOT (dr.rubrique REGEXP '^[0-9]+$')";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$departement, $annee]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return floatval($result['total'] ?? 0);
}

function getDetailsDepartement($pdo, $departement, $annee) {
    $sql = "SELECT 
                d.nom_departement,
                b.annee,
                dr.rubrique,
                dr.montant,
                dr.date_depense,
                IFNULL((SELECT dp.montant_departement FROM depense_prevision dp WHERE dp.id_budget = b.id_budget AND dp.id_departement = d.id_departement AND dp.rubrique = dr.rubrique LIMIT 1), 0) AS budget_prevu
            FROM depense_realisees dr
            JOIN departements d ON dr.id_departement = d.id_departement
            JOIN budgets b ON dr.id_budget = b.id_budget
            WHERE d.nom_departement = ? AND b.annee = ?
            AND NOT (dr.rubrique REGEXP '^[0-9]+$')
            ORDER BY dr.date_depense DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$departement, $annee]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// NOUVELLE FONCTION : Recuperer le synthese de tous les departements pour une annee
function getSyntheseDepartements($pdo, $annee) {
    $departements = ['Etude', 'Administration', 'Logistique'];
    $resultats = [];
    
    foreach ($departements as $dept) {
        $budget = getTotalBudgetDepartement($pdo, $dept, $annee);
        $depense = getTotalDepenseDepartement($pdo, $dept, $annee);
        
        $taux = $budget > 0 ? round(($depense / $budget) * 100, 1) : 0;
        
        if ($taux > 100) {
            $statut = "Depassement";
            $statut_class = "danger";
        } elseif ($taux >= 75 && $taux <= 100) {
            $statut = "Seuil atteint";
            $statut_class = "warning";
        } else {
            $statut = "Normal";
            $statut_class = "success";
        }
        
        $resultats[] = [
            'departement' => $dept,
            'budget' => $budget,
            'depense' => $depense,
            'disponible' => $budget - $depense,
            'taux' => $taux,
            'statut' => $statut,
            'statut_class' => $statut_class
        ];
    }
    
    return $resultats;
}
?>