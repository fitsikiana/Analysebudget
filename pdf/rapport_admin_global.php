<?php
require '../fpdf186/fpdf.php';
include "../config/db.php";
// SQL Query: Maka ny budget, ny volana, ny total isam-bolana, ary ny total ankapobeny isaky ny departemanta
$sql = "SELECT 
            departements.code_compte, 
            departements.montant_initial as budget_initial,depenses.montant_depense,
            depenses.date_depense as mois,
            SUM(depenses.montant_depense) as total_argent,
            -- Ity subquery ity dia mikajy ny total depense rehetra ho an'io departemanta io
            (SELECT SUM(montant_depense) FROM depenses WHERE id_departement = departements.id_departement) as total_depense_tous,
            (departements.montant_initial - IFNULL((SELECT SUM(montant_depense) FROM depenses WHERE id_departement = departements.id_departement), 0)) 
            as solde_restant
        FROM departements 
        LEFT JOIN depenses ON departements.id_departement = depenses.id_departement
        GROUP BY departements.id_departement, mois
        ORDER BY departements.code_compte ASC, mois DESC";

$res = $pdo->query($sql);
$results = $res->fetchAll(PDO::FETCH_ASSOC);
//CREATION PDF
$pdf = new FPDF();
$pdf->AddPage();

// TITRE
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Rapport Global des Utulisateurs',0,1,'C');
$pdf->Ln(8);

// ENTETE COLOREE
$pdf->SetFillColor(41,128,185); // bleu
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',10);

$pdf->Cell(25,10,'Compte',1,0,'C',true);
$pdf->Cell(30,10,'Budget Initial',1,0,'C',true);
$pdf->Cell(30,10,'Depense du mois',1,0,'C',true);
$pdf->Cell(25,10,'Periode',1,0,'C',true);
$pdf->Cell(30,10,'Total depense',1,0,'C',true);
$pdf->Cell(30,10,'Solde',1,0,'C',true);
$pdf->Cell(20,10,'Taux %',1,1,'C',true);

//RESET TEXTE
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',9);

// DONNEES
foreach ($results as $result) {

    $budget = $result['budget_initial'];
    $depense = $result['total_argent'];
    $mois = $result['mois'];
    $taux = ($budget > 0) ? ($depense / $budget) * 100 : 0;

    //COULEUR CONDITIONNELLE (bonus)
    if ($taux > 100) {
        $pdf->SetTextColor(231,76,60); // rouge
    } else {
        $pdf->SetTextColor(0,0,0);
    }

    $pdf->Cell(25,8,$result['code_compte'],1);
    $pdf->Cell(30,8,number_format($budget,0,',',' ')." Ar",1,0,'R');
    $pdf->Cell(30,8,number_format($result['montant_depense'],0,',',' ')." Ar",1,0,'R');
    $pdf->Cell(25,8,$result['mois'],1);
    $pdf->Cell(30,8,number_format($depense,0,',',' ')." Ar",1,0,'R');
    $pdf->Cell(30,8,number_format($result['solde_restant'],0,',',' ')." Ar",1,0,'R');
    $pdf->Cell(20,8,round($taux,2)." %",1,1,'C');
}

// 🔹 FOOTER
$pdf->Ln(5);
$pdf->SetFont('Arial','I',8);
$pdf->Cell(0,10,'Genere le '.date('d/m/Y'),0,0,'R');
$pdf->Output()
?>