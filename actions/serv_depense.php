<?php
session_start();
include '../config/db.php';
include '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_depense'])) {
    
    $file = $_FILES['file_depense'];
    $id_utilisateur = $_SESSION['id_utilisateur'] ?? null;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        header("Location: ../pages/dashboard.php?tab=import-depense&error=" . urlencode("Erreur lors du telechargement du fichier."));
        exit();
    }

    try {
        // 1. Chargement du fichier Excel
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();

        // 2. EXTRACTION DYNAMIQUE DE L'ANNEE DEPUIS LA CELLULE A1
        $titreA1 = $sheet->getCell('A1')->getValue();
        $annee = null;

        if (preg_match('/(\d{4}-\d{4})/', $titreA1, $matches)) {
            $annee = $matches[1];
        }

        if (!$annee) {
            header("Location: ../pages/dashboard.php?tab=import-depense&error=" . urlencode("Impossible de detecter l'annee universitaire."));
            exit();
        }

        // 3. SÉCURISATION ET VÉRIFICATION DE L'EXISTENCE DU BUDGET
        $stmtCheckBudget = $pdo->prepare("SELECT id_budget FROM budgets WHERE annee = ?");
        $stmtCheckBudget->execute([$annee]);
        $budgetExistant = $stmtCheckBudget->fetch();

        if (!$budgetExistant) {
            header("Location: ../pages/dashboard.php?tab=import-depense&error=" . urlencode("Aucun budget trouve pour l'annee $annee. Veuillez d'abord importer le budget."));
            exit();
        }

        $id_budget = $budgetExistant['id_budget'];

        // 🔐 LOGIQUE DE SÉCURITÉ RE-SÉCURISÉE :
        // On vérifie s'il existe déjà des dépenses physiques en BDD pour ce budget
        $stmtCheckRealisees = $pdo->prepare("SELECT COUNT(*) FROM depense_realisees WHERE id_budget = ?");
        $stmtCheckRealisees->execute([$id_budget]);
        $nbDepensesEnBDD = intval($stmtCheckRealisees->fetchColumn());

        // On bloque UNIQUEMENT si des dépenses existent déjà ET que le fichier n'a pas changé
        if ($nbDepensesEnBDD > 0) {
            $fileHash = md5_file($file['tmp_name']);
            $stmtCheckHash = $pdo->prepare("SELECT id FROM imports_history WHERE file_hash = ? AND file_type = 'depense'");
            $stmtCheckHash->execute([$fileHash]);
            if ($stmtCheckHash->fetch()) {
                header("Location: ../pages/dashboard.php?tab=import-depense&error=depense_" . urlencode("Ce fichier depense a déjà été importé et ne contient aucune modification."));
                exit();
            }
        } else {
            // Si aucune dépense n'existe en BDD, on recalcule le hash pour l'étape suivante
            $fileHash = md5_file($file['tmp_name']);
        }

        // 4. LECTURE DES DONNEES (Correspondance exacte des colonnes A, B, C...)
        $colonnes = [
            'Etude'          => ['rubrique' => 'A', 'montant' => 'B', 'date' => 'C'],
            'Administration' => ['rubrique' => 'D', 'montant' => 'E', 'date' => 'F'],
            'Logistique'     => ['rubrique' => 'G', 'montant' => 'H', 'date' => 'I']
        ];
        
        $donneesDepenses = [];
        $montantTotal = 0;
        $highestRow = $sheet->getHighestRow();
        
        for ($row = 4; $row <= $highestRow; $row++) {
            foreach ($colonnes as $nomDept => $cols) {
                $rubrique = trim($sheet->getCell($cols['rubrique'] . $row)->getValue());
                $montant = floatval($sheet->getCell($cols['montant'] . $row)->getValue());
                $dateRaw = $sheet->getCell($cols['date'] . $row)->getValue();
                
                if (empty($rubrique) || strtolower($rubrique) == 'total' || $montant <= 0) {
                    continue;
                }
                
                // Gestion saine de la date
                if (!empty($dateRaw)) {
                    if (is_numeric($dateRaw)) {
                        $timestamp = ($dateRaw - 25569) * 86400;
                        $date_depense = date('Y-m-d', $timestamp);
                    } else {
                        $dateObj = DateTime::createFromFormat('d/m/Y', $dateRaw);
                        if ($dateObj) {
                            $date_depense = $dateObj->format('Y-m-d');
                        } else {
                            $dateObj = DateTime::createFromFormat('Y-m-d', $dateRaw);
                            if ($dateObj) {
                                $date_depense = $dateObj->format('Y-m-d');
                            } else {
                                $date_depense = date('Y-m-d');
                            }
                        }
                    }
                } else {
                    $date_depense = date('Y-m-d');
                }
                
                $donneesDepenses[] = [
                    'nom_departement' => $nomDept,
                    'rubrique' => $rubrique,
                    'montant' => $montant,
                    'date_depense' => $date_depense
                ];
                $montantTotal += $montant;
            }
        }

        if (empty($donneesDepenses)) {
            header("Location: ../pages/dashboard.php?tab=import-depense&error=" . urlencode("Le fichier Excel ne contient aucune ligne de depense valide."));
            exit();
        }

        // 5. DEBUT DE LA TRANSACTION ET INSERTIONS
        $pdo->beginTransaction();

        // Nettoyage préalable des anciennes dépenses associées à ce budget pour repartir sur une base propre
        $stmtDeleteOld = $pdo->prepare("DELETE FROM depense_realisees WHERE id_budget = ?");
        $stmtDeleteOld->execute([$id_budget]);

        $stmtGetDept = $pdo->prepare("SELECT id_departement FROM departements WHERE nom_departement = ? AND annee = ?");
        $stmtInsertDept = $pdo->prepare("INSERT INTO departements (nom_departement, annee) VALUES (?, ?)");
        $stmtInsertDepense = $pdo->prepare("INSERT INTO depense_realisees (id_budget, id_departement, rubrique, montant, date_depense, annee) VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($donneesDepenses as $depense) {
            $stmtGetDept->execute([$depense['nom_departement'], $annee]);
            $deptRow = $stmtGetDept->fetch();

            if ($deptRow) {
                $id_departement = $deptRow['id_departement'];
            } else {
                $stmtInsertDept->execute([$depense['nom_departement'], $annee]);
                $id_departement = $pdo->lastInsertId();
            }

            $stmtInsertDepense->execute([
                $id_budget,
                $id_departement,
                $depense['rubrique'],
                $depense['montant'],
                $depense['date_depense'],
                $annee
            ]);
        }

        // Enregistrement ou mise à jour sécurisée du hash dans l'historique
        $stmtSaveHash = $pdo->prepare("INSERT INTO imports_history (file_type, file_hash) VALUES ('depense', ?) ON DUPLICATE KEY UPDATE file_hash = VALUES(file_hash)");
        $stmtSaveHash->execute([$fileHash]);

        if ($id_utilisateur) {
            $stmtLog = $pdo->prepare("INSERT INTO import_logs (id_utilisateur, nom_fichier, type_import, date_import) VALUES (?, ?, 'Depenses Realisees', CURDATE())");
            $stmtLog->execute([$id_utilisateur, $file['name']]);
        }

        $pdo->commit();

        header("Location: ../pages/dashboard.php?tab=import-depense&success=depense_" . urlencode("Importation reussie pour l'annee $annee. Total des depenses: " . number_format($montantTotal, 0, ',', ' ') . " Ar."));
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: ../pages/dashboard.php?tab=import-depense&error=depense_" . urlencode("Erreur : " . $e->getMessage()));
        exit();
    }

} else {
    header("Location: ../pages/dashboard.php?tab=import-depense");
    exit();
}
?>