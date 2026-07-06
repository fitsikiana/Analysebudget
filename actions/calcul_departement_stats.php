<?php
// actions/calcul_departement_stats.php
include '../config/db.php';

// Recuperer l'annee courante depuis la base
$stmtAnnee = $pdo->prepare("SELECT annee FROM budgets ORDER BY annee DESC LIMIT 1");
$stmtAnnee->execute();
$anneeRow = $stmtAnnee->fetch(PDO::FETCH_ASSOC);
$annee_cible = $anneeRow ? $anneeRow['annee'] : '2026-2027';

$data_affichage = [
    'Etude' => ['budget' => 0, 'depense' => 0, 'disponible' => 0],
    'Administration' => ['budget' => 0, 'depense' => 0, 'disponible' => 0],
    'Logistique' => ['budget' => 0, 'depense' => 0, 'disponible' => 0]
];

try {
    // 1. Recuperer la somme des previsions par departement pour l'annee cible
    $sqlPrevs = "SELECT d.nom_departement, IFNULL(SUM(dp.montant_departement), 0) AS total_prev
                 FROM departements d
                 JOIN depense_prevision dp ON d.id_departement = dp.id_departement
                 JOIN budgets b ON dp.id_budget = b.id_budget
                 WHERE b.annee = ?
                 GROUP BY d.nom_departement";
    
    $stmtPrevs = $pdo->prepare($sqlPrevs);
    $stmtPrevs->execute([$annee_cible]);
    while ($row = $stmtPrevs->fetch(PDO::FETCH_ASSOC)) {
        if (isset($data_affichage[$row['nom_departement']])) {
            $data_affichage[$row['nom_departement']]['budget'] = floatval($row['total_prev']);
        }
    }

    // 2. Recuperer la somme des depenses reelles par departement pour l'annee cible (sans filtre)
    $sqlReals = "SELECT d.nom_departement, IFNULL(SUM(dr.montant), 0) AS total_real
                 FROM departements d
                 JOIN depense_realisees dr ON d.id_departement = dr.id_departement
                 JOIN budgets b ON dr.id_budget = b.id_budget
                 WHERE b.annee = ?
                 GROUP BY d.nom_departement";
                 
    $stmtReals = $pdo->prepare($sqlReals);
    $stmtReals->execute([$annee_cible]);
    while ($row = $stmtReals->fetch(PDO::FETCH_ASSOC)) {
        if (isset($data_affichage[$row['nom_departement']])) {
            $data_affichage[$row['nom_departement']]['depense'] = floatval($row['total_real']);
        }
    }
    
    // Calculer le disponible pour chaque departement
    foreach ($data_affichage as $dept => $values) {
        $data_affichage[$dept]['disponible'] = $values['budget'] - $values['depense'];
    }
} catch (Exception $e) {
    // En cas d'erreur, conserve les valeurs par defaut a 0
}
?>