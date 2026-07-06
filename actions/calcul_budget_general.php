<?php
// actions/calcul_budget_general.php
include '../config/db.php';

try {
    // Recuperer toutes les situations budgetaires annuelles ordonnees par annee decroissante
    $sql = "SELECT 
                b.id_budget,
                b.annee,
                b.montant AS budget_global,
                IFNULL((SELECT SUM(dr.montant) FROM depense_realisees dr WHERE dr.id_budget = b.id_budget), 0) AS total_depenses_cumulees
            FROM budgets b
            ORDER BY b.annee DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $situationsAnnuelles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($situationsAnnuelles)) {
        $situationsAnnuelles[] = [
            'id_budget' => 0,
            'annee' => '2026-2027',
            'budget_global' => 0,
            'total_depenses_cumulees' => 0
        ];
    }
} catch (Exception $e) {
    $situationsAnnuelles = [
        [
            'id_budget' => 0,
            'annee' => '2026-2027',
            'budget_global' => 0,
            'total_depenses_cumulees' => 0
        ]
    ];
}
?>