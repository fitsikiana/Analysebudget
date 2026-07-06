<?php
/**
 * Script d'importation du budget universitaire depuis un fichier Excel.
 * Système anti-doublon adaptatif : Écrase et remplace les rubriques existantes pour l'année concernée.
 * Architecture PHP Simple (sans MVC).
 */

// 1. Démarrage de la session pour récupérer l'utilisateur connecté
session_start();

// 2. Configuration de la Base de Données
$host = 'localhost';
$dbname = 'gestion_budgetaire'; // Soloy ny anaran'ny BDD-nao raha hafa
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// 3. Chargement de PhpSpreadsheet via Composer
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Récupération de l'ID utilisateur de la session (eto no nisy fahadisoana teo)
// Raha mbola tsy misy session dia mampiasa ID 1 ho solony (izay tokony ho ao amin'ny table utilisateurs-nao)
$id_utilisateur = $_SESSION['id_utilisateur'] ?? 1; 

$message = "";

if (isset($_POST['import_excel']) && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = "<div style='color: red; padding: 10px; border: 1px solid red;'>Erreur lors du téléchargement du fichier.</div>";
    } else {
        $filePath = $file['tmp_name'];
        $fileName = $file['name'];
        
        try {
            // Charger le fichier Excel
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            
            // 4. Famantarana ny taona avy ao amin'ny cellule A1 ("BUDGET UNIVERSITAIRE POUR L'ANNEE 2026-2027")
            $titleText = $sheet->getCell('A1')->getValue();
            $annee = '2026-2027'; // Taona default raha misy olana ny famakiana azy
            if (preg_match('/(\\d{4}-\\d{4})/', $titleText, $matches)) {
                $annee = $matches[1];
            }

            // Démarrer la transaction SQL
            $pdo->beginTransaction();
            
            // 5. Insertion ao amin'ny `import_logs`
            $stmtLog = $pdo->prepare("INSERT INTO import_logs (nom_fichier, type_import, id_utilisateur, date_import) VALUES (?, 'Budget Annuel', ?, NOW())");
            $stmtLog->execute([$fileName, $id_utilisateur]);
            $id_import = $pdo->lastInsertId();
            
            // Configuration ny colonne isaky ny departemanta d'après ny sary
            $departmentsConfig = [
                ['nom' => 'Etude', 'col_des' => 'A', 'col_mt' => 'B'],
                ['nom' => 'Administration', 'col_des' => 'C', 'col_mt' => 'D'],
                ['nom' => 'Logistique', 'col_des' => 'E', 'col_mt' => 'F']
            ];
            
            // Fanomanana ny requêtes rehetra ilaina
            $stmtDeptCheck = $pdo->prepare("SELECT id_departement FROM departements WHERE nom_departement = ? AND annee = ?");
            $stmtDeptInsert = $pdo->prepare("INSERT INTO departements (nom_departement, annee, budget_global, created_at) VALUES (?, ?, 0, NOW())");
            $stmtDeptUpdateBudget = $pdo->prepare("UPDATE departements SET budget_global = ? WHERE id_departement = ?");
            $stmtBudgetInsert = $pdo->prepare("INSERT INTO budgets (id_departement, rubrique, montant_prevu, id_import, created_at) VALUES (?, ?, ?, ?, NOW())");
            
            // REGLE ANTI-DOUBLON : Requéte hamafana ny rubriques taloha an'io departemanta io ihany
            $stmtCleanOldBudgets = $pdo->prepare("DELETE FROM budgets WHERE id_departement = ?");
            
            foreach ($departmentsConfig as $config) {
                $nom_dept = $config['nom'];
                
                // Jerena raha efa misy ilay departemanta amin'io taona io
                $stmtDeptCheck->execute([$nom_dept, $annee]);
                $dept = $stmtDeptCheck->fetch();
                
                if ($dept) {
                    $id_departement = $dept['id_departement'];
                    
                    // RAHA EFA MISY : Fafana ny rubrique taloha rehetra ao amin'ny table `budgets`
                    // Mba handraisana ny modification vaovao nefa tsy hisian'ny dika miverina (doublons)
                    $stmtCleanOldBudgets->execute([$id_departement]);
                } else {
                    // RAHA MBOLA TSY MISY : Ampidirina vaovao ilay departemanta
                    $stmtDeptInsert->execute([$nom_dept, $annee]);
                    $id_departement = $pdo->lastInsertId();
                }
                
                $total_budget_dept = 0;
                $row = 4; // Ny data dia manomboka eo amin'ny laharana faha-4 foana
                
                // Famakiana ny tsanganana (colonnes) isaky ny departemanta
                while (true) {
                    $rubrique = $sheet->getCell($config['col_des'] . $row)->getValue();
                    $montant = $sheet->getCell($config['mt'] ?? $config['col_mt'] . $row)->getValue();
                    
                    $rubriqueClean = trim((string)$rubrique);
                    
                    // Mijonona raha banga ny tsipika na rehefa tonga eo amin'ny "Total"
                    if (empty($rubriqueClean) || strtolower($rubriqueClean) === 'total') {
                        break;
                    }
                    
                    // Fanadiovana ny montant ho lasa chiffre (Float) tsotra
                    $montantNumeric = floatval(str_replace([' ', ','], ['', '.'], $montant));
                    
                    // Insertion ny rubrique tsirairay ao amin'ny table `budgets`
                    $stmtBudgetInsert->execute([$id_departement, $rubriqueClean, $montantNumeric, $id_import]);
                    
                    // Kajy ny total vaovao ho an'ny budget_global
                    $total_budget_dept += $montantNumeric;
                    
                    $row++;
                }
                
                // Mettre à jour ny budget_global ao amin'ny table `departement`
                $stmtDeptUpdateBudget->execute([$total_budget_dept, $id_departement]);
            }
            
            // Valider-na ny transaction rehefa milamina ny zava-drehetra
            $pdo->commit();
            $message = "<div style='color: green; padding: 15px; border: 1px solid green; background-color: #e8f8f5; font-weight: bold;'>L'importation et la mise à jour du budget ($annee) ont été effectuées avec succès ! (Log ID : $id_import)</div>";
            
        } catch (Exception $e) {
            // Raha misy fahadisoana dia averina amin'ny laoniny (rollback) ny BDD
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = "<div style='color: red; padding: 15px; border: 1px solid red; background-color: #fdebd0; font-weight: bold;'>Erreur lors du traitement du fichier : " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RégieBudget — Importation</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 50px; }
        .container { max-width: 650px; margin: auto; background: white; padding: 35px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        h2 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 12px; margin-top: 0; }
        .form-group { margin: 25px 0; }
        label { display: block; margin-bottom: 10px; font-weight: bold; color: #5c6b73; }
        input[type="file"] { display: block; width: 100%; padding: 12px; border: 2px dashed #bdc3c7; background: #fafafa; border-radius: 5px; box-sizing: border-box; }
        button { background-color: #3498db; color: white; padding: 14px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; width: 100%; transition: background 0.2s; }
        button:hover { background-color: #2980b9; }
        .alert-box { margin-bottom: 25px; border-radius: 5px; font-size: 15px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Système d'Importation Excel — RégieBudget</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert-box"><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="excel_file">Sélectionnez le fichier Excel du Budget Universitaire :</label>
            <input type="file" name="excel_file" id="excel_file" accept=".xlsx, .xls" required>
        </div>
        <button type="submit" name="import_excel">Mettre à jour et Importer</button>
    </form>
</div>

</body>
</html>