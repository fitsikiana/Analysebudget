<?php
// actions/budget_par_annee.php
include '../config/db.php';

function getBudgetsParAnnee($pdo) {
    try {
        // Recuperer tous les budgets avec leurs annees
        $stmt = $pdo->prepare("SELECT id_budget, montant, annee FROM budgets ORDER BY annee DESC");
        $stmt->execute();
        $budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $resultats = [];
        
        foreach ($budgets as $budget) {
            $annee = $budget['annee'];
            $id_budget = $budget['id_budget'];
            $budget_global = floatval($budget['montant']);
            
            // Recuperer les departements pour cette annee
            $stmtDept = $pdo->prepare("SELECT id_departement, nom_departement FROM departements WHERE annee = ? GROUP BY nom_departement");
            $stmtDept->execute([$annee]);
            $departements = $stmtDept->fetchAll(PDO::FETCH_ASSOC);
            
            $departements_data = [];
            $total_depense = 0;
            $toutes_depenses = [];
            
            if (empty($departements)) {
                $stmtDep = $pdo->prepare("SELECT SUM(montant) as total FROM depense_realisees WHERE id_budget = ?");
                $stmtDep->execute([$id_budget]);
                $depenses = $stmtDep->fetch(PDO::FETCH_ASSOC);
                $total_depense = floatval($depenses['total'] ?? 0);
                
                $stmtDetails = $pdo->prepare("SELECT dr.rubrique, dr.montant, dr.date_depense, dr.annee, IFNULL(dp.rubrique, dr.rubrique) AS rubrique_affichee
                    FROM depense_realisees dr
                    LEFT JOIN depense_prevision dp ON dr.id_budget = dp.id_budget AND dr.rubrique = dp.rubrique
                    WHERE dr.id_budget = ?
                    GROUP BY dr.id_depense_realisee");
                $stmtDetails->execute([$id_budget]);
                $details = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($details as $detail) {
                    $toutes_depenses[] = [
                        'nom_departement' => 'Inconnu',
                        'rubrique' => $detail['rubrique_affichee'] ?? $detail['rubrique'],
                        'montant' => floatval($detail['montant']),
                        'date_depense' => $detail['date_depense'],
                        'annee' => $detail['annee']
                    ];
                }
            } else {
                foreach ($departements as $dept) {
                    $id_dept = $dept['id_departement'];
                    $nom_dept = $dept['nom_departement'];
                    
                    $stmtDepDept = $pdo->prepare("SELECT SUM(montant) as total FROM depense_realisees WHERE id_departement = ? AND id_budget = ?");
                    $stmtDepDept->execute([$id_dept, $id_budget]);
                    $depDept = $stmtDepDept->fetch(PDO::FETCH_ASSOC);
                    $depense_dept = floatval($depDept['total'] ?? 0);
                    
                    $departements_data[$nom_dept] = $depense_dept;
                    $total_depense += $depense_dept;
                    
                    $stmtDetails = $pdo->prepare("SELECT dr.rubrique, dr.montant, dr.date_depense, dr.annee, IFNULL(dp.rubrique, dr.rubrique) AS rubrique_affichee
                        FROM depense_realisees dr
                        LEFT JOIN depense_prevision dp ON dr.id_budget = dp.id_budget AND dr.rubrique = dp.rubrique
                        WHERE dr.id_departement = ? AND dr.id_budget = ?
                        GROUP BY dr.id_depense_realisee");
                    $stmtDetails->execute([$id_dept, $id_budget]);
                    $details = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($details as $detail) {
                        $toutes_depenses[] = [
                            'nom_departement' => $nom_dept,
                            'rubrique' => $detail['rubrique_affichee'] ?? $detail['rubrique'],
                            'montant' => floatval($detail['montant']),
                            'date_depense' => $detail['date_depense'],
                            'annee' => $detail['annee']
                        ];
                    }
                }
            }
            
            $resultats[$annee] = [
                'budget' => $budget_global,
                'depense' => $total_depense,
                'disponible' => $budget_global - $total_depense,
                'taux' => $budget_global > 0 ? round(($total_depense / $budget_global) * 100, 1) : 0,
                'departements' => $departements_data,
                'depenses' => $toutes_depenses
            ];
        }
        
        return $resultats;
    } catch (Exception $e) {
        error_log("Erreur dans getBudgetsParAnnee: " . $e->getMessage());
        return [];
    }
}

// Recuperer les donnees
$donnees_par_annee = getBudgetsParAnnee($pdo);
$annees_disponibles = array_keys($donnees_par_annee);

// Annee courante (la plus recente)
$annee_courante = !empty($annees_disponibles) ? $annees_disponibles[0] : '2026-2027';

// Donnees pour l'annee courante
$donnees_affichees = isset($donnees_par_annee[$annee_courante]) ? $donnees_par_annee[$annee_courante] : [
    'budget' => 0,
    'depense' => 0,
    'disponible' => 0,
    'taux' => 0,
    'departements' => [],
    'depenses' => []
];
?>