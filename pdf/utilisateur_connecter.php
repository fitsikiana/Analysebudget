<?php
session_start();
require('../fpdf186/fpdf.php');
include '../config/db.php'; // ton fichier PDO
if (!isset($_SESSION['id_utilisateurs'])) {
    die("Utilisateur non connecté");
}
$id_user = $_SESSION['id_utilisateurs'] ;

$sql = "SELECT 
            departements.code_compte, 
            departements.montant_initial as budget_initial,
            depenses.date_depense as mois,
            SUM(depenses.montant_depense) as total_argent,
            (SELECT SUM(montant_depense) 
             FROM depenses 
             WHERE id_departement = departements.id_departement) as total_depense_tous,
            (departements.montant_initial - IFNULL(
                (SELECT SUM(montant_depense) 
                 FROM depenses 
                 WHERE id_departement = departements.id_departement), 0)
            ) as solde_restant
        FROM departements 
        JOIN utilisateurs ON utilisateurs.id_departement = departements.id_departement
        LEFT JOIN depenses ON departements.id_departement = depenses.id_departement
        WHERE utilisateurs.id_user = ?
        GROUP BY departements.id_departement, mois
        ORDER BY departements.code_compte ASC, mois DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_user]);
$rapports = $stmt->fetchAll(PDO::FETCH_ASSOC);

//CREATION PDF
$pdf = new FPDF();
$pdf->AddPage();

// TITRE
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Rapport Mensuel Budget',0,1,'C');
$pdf->Ln(8);

// ENTETE COLOREE
$pdf->SetFillColor(41,128,185); // bleu
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',10);

$pdf->Cell(25,10,'Compte',1,0,'C',true);
$pdf->Cell(30,10,'Budget',1,0,'C',true);
$pdf->Cell(30,10,'Dep mois',1,0,'C',true);
$pdf->Cell(25,10,'Periode',1,0,'C',true);
$pdf->Cell(30,10,'Total dep',1,0,'C',true);
$pdf->Cell(30,10,'Solde',1,0,'C',true);
$pdf->Cell(20,10,'Taux %',1,1,'C',true);

//RESET TEXTE
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',9);

// DONNEES
foreach ($rapports as $rapport) {

    $budget = $rapport['budget_initial'];
    $depense = $rapport['total_depense_tous'];
    $taux = ($budget > 0) ? ($depense / $budget) * 100 : 0;

    //COULEUR CONDITIONNELLE (bonus)
    if ($taux > 100) {
        $pdf->SetTextColor(231,76,60); // rouge
    } else {
        $pdf->SetTextColor(0,0,0);
    }

    $pdf->Cell(25,8,$rapport['code_compte'],1);
    $pdf->Cell(30,8,number_format($budget,0,',',' ')." Ar",1,0,'R');
    $pdf->Cell(30,8,number_format($rapport['total_argent'],0,',',' ')." Ar",1,0,'R');
    $pdf->Cell(25,8,$rapport['mois'],1);
    $pdf->Cell(30,8,number_format($depense,0,',',' ')." Ar",1,0,'R');
    $pdf->Cell(30,8,number_format($rapport['solde_restant'],0,',',' ')." Ar",1,0,'R');
    $pdf->Cell(20,8,round($taux,2)." %",1,1,'C');
}

// 🔹 FOOTER
$pdf->Ln(5);
$pdf->SetFont('Arial','I',8);
$pdf->Cell(0,10,'Genere le '.date('d/m/Y'),0,0,'R');
$pdf->Output()
?>