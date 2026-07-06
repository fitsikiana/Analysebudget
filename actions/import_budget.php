<?php
session_start();
include '../config/db.php';
include '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_budget'])) {
    
    $file = $_FILES['file_budget'];
    $id_utilisateur = $_SESSION['id_utilisateur'] ?? null;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        header("Location: ../pages/dashboard.php?tab=import-budget&error=" . urlencode("Erreur lors du téléchargement du fichier Excel."));
        exit();
    }

    try {
        // 1. Chargement du fichier Excel avec PhpSpreadsheet
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();

        // 2. EXTRACTION DYNAMIQUE DE L'ANNÉE DEPUIS LA CELLULE A1
        $titreA1 = $sheet->getCell('A1')->getValue();
        $annee = null;

        if (preg_match('/(\d{4}-\d{4})/', $titreA1, $matches)) {
            $annee = $matches[1];
        }

        if (!$annee) {
            header("Location: ../pages/dashboard.php?tab=import-budget&error=" . urlencode("Impossible de détecter l'année universitaire dans la cellule A1 (Format attendu: AAAA-AAAA)."));
            exit();
        }

        // 3. SÉCURISATION ET VÉRIFICATION DE L'EXISTENCE DU BUDGET
        $stmtCheckBudget = $pdo->prepare("SELECT id_budget FROM budgets WHERE annee = ?");
        $stmtCheckBudget->execute([$annee]);
        $budgetExistant = $stmtCheckBudget->fetch();

        // Calcul du hash MD5 du fichier Excel
        $fileHash = md5_file($file['tmp_name']);

        // 🔐 LOGIQUE DE SÉCURITÉ MODIFIÉE :
        // On bloque UNIQUEMENT si le budget existe DÉJÀ dans la BDD ET que le fichier n'a pas de modification (même hash)
        if ($budgetExistant) {
            $stmtCheckHash = $pdo->prepare("SELECT id FROM imports_history WHERE file_hash = ? AND file_type = 'budget'");
            $stmtCheckHash->execute([$fileHash]);
            if ($stmtCheckHash->fetch()) {
                header("Location: ../pages/dashboard.php?tab=import-budget&error=" . urlencode("Ce fichier budget a déjà été importé et ne contient aucune modification."));
                exit();
            }
        }

        // 4. DEBUT DE LA TRANSACTION ET MONTAGES DES CALCULS
        $pdo->beginTransaction();

        // Calcul pour le montant total global depuis Excel
        $montantTotalGlobal = 0;
        $highestRow = $sheet->getHighestRow();
        $colonnesBudget = ['B', 'D', 'F']; 
        
        for ($row = 4; $row <= $highestRow; $row++) {
            foreach ($colonnesBudget as $col) {
                $val = floatval($sheet->getCell($col . $row)->getValue());
                $montantTotalGlobal += $val;
            }
        }

        if ($budgetExistant) {
            $id_budget = $budgetExistant['id_budget'];
            $stmtUpdateBudgetGlobal = $pdo->prepare("UPDATE budgets SET montant = ? WHERE id_budget = ?");
            $stmtUpdateBudgetGlobal->execute([$montantTotalGlobal, $id_budget]);
        } else {
            $stmtInsertBudget = $pdo->prepare("INSERT INTO budgets (montant, annee) VALUES (?, ?)");
            $stmtInsertBudget->execute([$montantTotalGlobal, $annee]);
            $id_budget = $pdo->lastInsertId();
        }

        // 5. LECTURE ET PROCESSING PAR DEPARTEMENT
        $mappingDepartements = [
            'Etude'          => ['rubrique' => 'A', 'montant' => 'B'],
            'Administration' => ['rubrique' => 'C', 'montant' => 'D'],
            'Logistique'     => ['rubrique' => 'E', 'montant' => 'F']
        ];

        $donneesPrevision = [];

        for ($row = 4; $row <= $highestRow; $row++) {
            foreach ($mappingDepartements as $nomDept => $cols) {
                $rubrique = trim($sheet->getCell($cols['rubrique'] . $row)->getValue());
                $montant = floatval($sheet->getCell($cols['montant'] . $row)->getValue());

                if (empty($rubrique) || strtolower($rubrique) == 'total' || $montant <= 0) {
                    continue;
                }

                $donneesPrevision[] = [
                    'nom_departement' => $nomDept,
                    'rubrique' => $rubrique,
                    'montant' => $montant
                ];
            }
        }

        // 6. INSERTION ET UPDATE DANS DEPENSE_PREVISION
        $stmtGetDept = $pdo->prepare("SELECT id_departement FROM departements WHERE nom_departement = ? AND annee = ?");
        $stmtInsertDept = $pdo->prepare("INSERT INTO departements (nom_departement, annee) VALUES (?, ?)");
        
        $stmtCheckPrevision = $pdo->prepare("SELECT id_depense_prevision FROM depense_prevision WHERE id_departement = ? AND id_budget = ? AND rubrique = ?");
        $stmtUpdatePrevision = $pdo->prepare("UPDATE depense_prevision SET montant_departement = ? WHERE id_depense_prevision = ?");
        $stmtInsertPrevision = $pdo->prepare("INSERT INTO depense_prevision (id_departement, id_budget, rubrique, montant_departement) VALUES (?, ?, ?, ?)");

        foreach ($donneesPrevision as $prev) {
            $stmtGetDept->execute([$prev['nom_departement'], $annee]);
            $deptRow = $stmtGetDept->fetch();

            if ($deptRow) {
                $id_departement = $deptRow['id_departement'];
            } else {
                $stmtInsertDept->execute([$prev['nom_departement'], $annee]);
                $id_departement = $pdo->lastInsertId();
            }

            $stmtCheckPrevision->execute([$id_departement, $id_budget, $prev['rubrique']]);
            $prevExistant = $stmtCheckPrevision->fetch();

            if ($prevExistant) {
                $stmtUpdatePrevision->execute([$prev['montant'], $prevExistant['id_depense_prevision']]);
            } else {
                $stmtInsertPrevision->execute([
                    $id_departement,
                    $id_budget,
                    $prev['rubrique'],
                    $prev['montant']
                ]);
            }
        }

        // Enregistrement ou mise à jour du hash historique pour ce type
        $stmtSaveHash = $pdo->prepare("INSERT INTO imports_history (file_type, file_hash) VALUES ('budget', ?) ON DUPLICATE KEY UPDATE file_hash = VALUES(file_hash)");
        $stmtSaveHash->execute([$fileHash]);

        if ($id_utilisateur) {
            $stmtLog = $pdo->prepare("INSERT INTO import_logs (id_utilisateur, nom_fichier, type_import, date_import) VALUES (?, ?, 'Budget Annuel', CURDATE())");
            $stmtLog->execute([$id_utilisateur, $file['name']]);
        }

        $pdo->commit();

        header("Location: ../pages/dashboard.php?tab=import-budget&success=" . urlencode("Importation réussie pour l'année $annee. Budget Global: " . number_format($montantTotalGlobal, 0, ',', ' ') . " Ar."));
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: ../pages/dashboard.php?tab=import-budget&error=" . urlencode("Erreur lors de l'importation : " . $e->getMessage()));
        exit();
    }

} else {
    header("Location: ../pages/dashboard.php?tab=import-budget");
    exit();
}
?>