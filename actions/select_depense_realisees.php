<?php
// actions/select_depense_realisees.php
include '../config/db.php';

try {
    $sql = "SELECT 
                d.nom_departement, 
                dr.rubrique AS designation,
                dr.montant, 
                dr.date_depense, 
                b.annee
            FROM depense_realisees dr 
            JOIN departements d ON dr.id_departement = d.id_departement
            JOIN budgets b ON dr.id_budget = b.id_budget
            ORDER BY b.annee DESC, dr.date_depense DESC";
    $stm = $pdo->prepare($sql);
    $stm->execute();
    $select_realisees = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $select_realisees = [];
}
?>