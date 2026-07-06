<?php
// actions/calcul_depense_global.php
include '../config/db.php'; 

try {
    // Recuperer toutes les annees existantes
    $stmtAnnees = $pdo->prepare("SELECT DISTINCT annee FROM budgets ORDER BY annee DESC");
    $stmtAnnees->execute();
    $list_annees = $stmtAnnees->fetchAll(PDO::FETCH_ASSOC);
    
    $donnees_par_annee = [];
    
    foreach ($list_annees as $a) {
        $an = $a['annee'];
        
        // Budget Global de l'annee
        $stmtB = $pdo->prepare("SELECT id_budget, montant FROM budgets WHERE annee = ?");
        $stmtB->execute([$an]);
        $bData = $stmtB->fetch(PDO::FETCH_ASSOC);
        $id_b = $bData ? $bData['id_budget'] : 0;
        $total_b = $bData ? floatval($bData['montant']) : 0;
        
        // Somme totale depensee pour cette annee (sans filtre)
        $stmtD = $pdo->prepare("SELECT IFNULL(SUM(montant), 0) AS total FROM depense_realisees WHERE id_budget = ?");
        $stmtD->execute([$id_b]);
        $dData = $stmtD->fetch(PDO::FETCH_ASSOC);
        $total_d = $dData ? floatval($dData['total']) : 0;
        
        // Repartition par departements pour cette annee specifique
        $depts = ['Etude', 'Administration', 'Logistique'];
        $details_depts = [];
        
        foreach ($depts as $dept) {
            // Previsionnel du departement
            $stP = $pdo->prepare("SELECT IFNULL(SUM(dp.montant_departement), 0) as total 
                                  FROM depense_prevision dp 
                                  JOIN departements d ON dp.id_departement = d.id_departement 
                                  WHERE d.nom_departement = ? AND dp.id_budget = ?");
            $stP->execute([$dept, $id_b]);
            $pVal = $stP->fetch(PDO::FETCH_ASSOC);
            $pVal = $pVal ? floatval($pVal['total']) : 0;
            
            // Reel du departement (sans filtre)
            $stR = $pdo->prepare("SELECT IFNULL(SUM(dr.montant), 0) as total 
                                  FROM depense_realisees dr 
                                  JOIN departements d ON dr.id_departement = d.id_departement 
                                  WHERE d.nom_departement = ? AND dr.id_budget = ?");
            $stR->execute([$dept, $id_b]);
            $rVal = $stR->fetch(PDO::FETCH_ASSOC);
            $rVal = $rVal ? floatval($rVal['total']) : 0;
            
            $details_depts[] = [
                'nom_departement' => $dept,
                'budget_alloue' => $pVal,
                'total_depense' => $rVal
            ];
        }
        
        $donnees_par_annee[$an] = [
            'budget_global' => $total_b,
            'total_depense' => $total_d,
            'departements' => $details_depts
        ];
    }
} catch (Exception $e) {
    $donnees_par_annee = [];
}
?>