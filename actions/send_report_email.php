<?php
// actions/send_report_email.php
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => ''];

try {
    $inputData = json_decode(file_get_contents('php://input'), true);
    $departement = isset($inputData['departement']) ? trim($inputData['departement']) : '';
    $email_destinataire = isset($inputData['email_destinataire']) ? trim($inputData['email_destinataire']) : '';
    $annee_selectionnee = isset($inputData['annee']) ? trim($inputData['annee']) : '2026-2027';

    if (empty($departement)) {
        throw new Exception("Aucun departement selectionne.");
    }

    if (empty($email_destinataire) || !filter_var($email_destinataire, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Veuillez saisir une adresse email valide.");
    }

    // Connection a la base de donnees
    $pdo = new PDO("mysql:host=localhost;dbname=gestion_budgetaire;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Requete SQL pour le departement et l'annee
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
    $rapports = $stmt->fetchAll();

    if (empty($rapports)) {
        throw new Exception("Aucune donnee pour ce departement: " . $departement);
    }

    $tot_prevu = 0;
    $tot_depense = 0;
    $tot_reste = 0;

    $tableau_html = "";
    foreach ($rapports as $row) {
        $tot_prevu += $row['montant_prevu'];
        $tot_depense += $row['total_depense'];
        $tot_reste += $row['solde_reste'];

        $style_reste = $row['solde_reste'] < 0 ? "color: red; font-weight: bold;" : "";

        $tableau_html .= "
        <tr>
            <td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($row['rubrique']) . "</td>
            <td style='border: 1px solid #ddd; padding: 10px; text-align: right;'>" . number_format($row['montant_prevu'], 0, ',', ' ') . " Ar</td>
            <td style='border: 1px solid #ddd; padding: 10px; text-align: right;'>" . number_format($row['total_depense'], 0, ',', ' ') . " Ar</td>
            <td style='border: 1px solid #ddd; padding: 10px; text-align: right; {$style_reste}'>" . number_format($row['solde_reste'], 0, ',', ' ') . " Ar</td>
        </tr>";
    }

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'faniahmiary@gmail.com';
    $mail->Password = 'wusgdkedecufseii';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('faniahmiary@gmail.com', 'RegieBudget ONIFRA');
    $mail->addAddress($email_destinataire);

    $mail->isHTML(true);
    $mail->Subject = "Rapport Budgetaire : Departement " . ucfirst($departement) . " (" . $annee_selectionnee . ")";
    
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; padding: 20px; border-radius: 8px;'>
        <h2 style='color: #0275d8; text-align: center; border-bottom: 2px solid #0275d8; padding-bottom: 10px;'>Oniversity FJKM RAVELOJAONA</h2>
        <p>Bonjour,</p>
        <p>Ici c'est le dernier rapport pour le departement <strong>" . htmlspecialchars(ucfirst($departement)) . "</strong> pour l'annee <strong>" . $annee_selectionnee . "</strong>.</p>
        
        <table style='width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px;'>
            <thead>
                <tr style='background-color: #f2f2f2;'>
                    <th style='border: 1px solid #ddd; padding: 10px; text-align: left;'>Rubrique</th>
                    <th style='border: 1px solid #ddd; padding: 10px; text-align: right;'>Prevu</th>
                    <th style='border: 1px solid #ddd; padding: 10px; text-align: right;'>Depense</th>
                    <th style='border: 1px solid #ddd; padding: 10px; text-align: right;'>Reste</th>
                </tr>
            </thead>
            <tbody>
                " . $tableau_html . "
                <tr style='background-color: #e9ecef; font-weight: bold;'>
                    <td style='border: 1px solid #ddd; padding: 10px;'>TOTAL</td>
                    <td style='border: 1px solid #ddd; padding: 10px; text-align: right;'>" . number_format($tot_prevu, 0, ',', ' ') . " Ar</td>
                    <td style='border: 1px solid #ddd; padding: 10px; text-align: right;'>" . number_format($tot_depense, 0, ',', ' ') . " Ar</td>
                    <td style='border: 1px solid #ddd; padding: 10px; text-align: right;'>" . number_format($tot_reste, 0, ',', ' ') . " Ar</td>
                </tr>
            </tbody>
        </table>
        <p style='margin-top: 30px; font-size: 12px; color: #777; text-align: center; border-top: 1px solid #e0e0e0; padding-top: 10px;'>
           Ce message a ete envoye automatiquement par le systeme RegieBudget.
        </p>
    </div>";

    $mail->send();
    
    $response['success'] = true;
    $response['message'] = "Rapport envoye avec succes a " . htmlspecialchars($email_destinataire);

} catch (Exception $e) {
    $response['message'] = "Erreur SMTP: " . $mail->ErrorInfo;
} catch (PDOException $e) {
    $response['message'] = "Erreur Base de donnees: " . $e->getMessage();
} catch (\Throwable $e) {
    $response['message'] = "Erreur: " . $e->getMessage();
}

echo json_encode($response);
exit;
?>