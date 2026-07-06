<?php
// actions/generate_pdf.php
session_start();
include '../config/db.php';
require('../fpdf186/fpdf.php'); 

$departement = isset($_GET['departement']) ? trim($_GET['departement']) : 'all';
$annee_selectionnee = isset($_GET['annee']) ? trim($_GET['annee']) : '2026-2027';

try {
    // Requete SQL dynamique
    if ($departement === 'all') {
        $sql = "SELECT 
                    d.nom_departement AS rubrique, 
                    IFNULL(SUM(dp.montant_departement), 0) AS montant_prevu, 
                    IFNULL(SUM(dr.montant), 0) AS total_depense,
                    (IFNULL(SUM(dp.montant_departement), 0) - IFNULL(SUM(dr.montant), 0)) AS solde_reste
                FROM departements d
                LEFT JOIN depense_prevision dp ON d.id_departement = dp.id_departement
                LEFT JOIN depense_realisees dr ON d.id_departement = dr.id_departement
                LEFT JOIN budgets b ON dp.id_budget = b.id_budget
                WHERE d.annee = ?
                GROUP BY d.nom_departement";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$annee_selectionnee]);
    } else {
        $sql = "SELECT 
                    dr.rubrique, 
                    IFNULL(dp.montant_departement, 0) AS montant_prevu, 
                    IFNULL(SUM(dr.montant), 0) AS total_depense,
                    (IFNULL(dp.montant_departement, 0) - IFNULL(SUM(dr.montant), 0)) AS solde_reste
                FROM depense_realisees dr
                JOIN departements d ON dr.id_departement = d.id_departement
                LEFT JOIN depense_prevision dp ON d.id_departement = dp.id_departement AND dr.rubrique = dp.rubrique
                LEFT JOIN budgets b ON dr.id_budget = b.id_budget
                WHERE d.nom_departement = ? AND d.annee = ?
                GROUP BY dr.rubrique, dp.montant_departement";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$departement, $annee_selectionnee]);
    }
    $rapports = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur Base de donnees : " . $e->getMessage());
}

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode("ONIVERSITY FJKM RAVELOJAONA ANTSIRABE"), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 5, utf8_decode("Systeme de Gestion Budgetaire (RegieBudget)"), 0, 1, 'C');
        $this->Ln(10);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode("RAPPORT BUDGETAIRE : " . strtoupper($departement === 'all' ? 'VUE GLOBALE' : $departement)), 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, utf8_decode("Annee Universitaire : " . $annee_selectionnee), 0, 1, 'L');
$pdf->Cell(0, 6, utf8_decode("Date d'edition : " . date('d/m/Y H:i')), 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(65, 10, utf8_decode("Designation"), 1, 0, 'C', true);
$pdf->Cell(40, 10, utf8_decode("Prevu (Ar)"), 1, 0, 'C', true);
$pdf->Cell(40, 10, utf8_decode("Dépense (Ar)"), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
foreach ($rapports as $row) {
    $pdf->Cell(65, 8, utf8_decode($row['rubrique']), 1, 0, 'L');
    $pdf->Cell(40, 8, number_format($row['montant_prevu'], 0, '.', ' '), 1, 0, 'R');
    $pdf->Cell(40, 8, number_format($row['total_depense'], 0, '.', ' '), 1, 1, 'R');
    
}

$pdf->Output('I', 'Rapport_' . $departement . '.pdf');
exit;
?>