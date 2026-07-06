<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: acceuil.php");
    exit();
}
include "../config/db.php";

// ============================================
// RECUPERATION DES FILTRES
// ============================================
$annee_selectionnee = isset($_GET['annee']) ? $_GET['annee'] : '';
$departement_selectionne = isset($_GET['departement']) ? $_GET['departement'] : '';

// ============================================
// RECUPERER TOUTES LES ANNEES DISPONIBLES
// ============================================
$sql_annees = "SELECT DISTINCT annee FROM budgets ORDER BY annee DESC";
$stmt = $pdo->query($sql_annees);
$annees_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($annees_disponibles)) {
    $annees_disponibles = [date('Y') . '-' . (date('Y') + 1)];
}

if (empty($annee_selectionnee)) {
    $annee_selectionnee = $annees_disponibles[0];
}

// ============================================
// RECUPERER TOUS LES DEPARTEMENTS
// ============================================
$sql_depts = "SELECT DISTINCT nom_departement FROM departements ORDER BY nom_departement";
$stmt = $pdo->query($sql_depts);
$departements_liste = $stmt->fetchAll(PDO::FETCH_COLUMN);

// ============================================
// DEPENSES PAR RUBRIQUE ET DEPARTEMENT
// ============================================
$sql_depenses = "
    SELECT 
        d.nom_departement,
        dr.rubrique,
        COALESCE(SUM(dr.montant), 0) as montant_total
    FROM departements d
    LEFT JOIN depense_realisees dr ON dr.id_departement = d.id_departement
    LEFT JOIN budgets b ON b.id_budget = dr.id_budget
    WHERE 1=1
";

$params_depenses = [];

if (!empty($annee_selectionnee)) {
    $sql_depenses .= " AND d.annee = :annee AND b.annee = :annee";
    $params_depenses[':annee'] = $annee_selectionnee;
}

if (!empty($departement_selectionne)) {
    $sql_depenses .= " AND d.nom_departement = :departement";
    $params_depenses[':departement'] = $departement_selectionne;
}

$sql_depenses .= " GROUP BY d.nom_departement, dr.rubrique ORDER BY d.nom_departement, montant_total DESC";

$stmt = $pdo->prepare($sql_depenses);
$stmt->execute($params_depenses);
$depenses_par_rubrique = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// ORGANISER LES DONNEES POUR LE GRAPHIQUE
// ============================================
$departements_data = [];
$rubriques_data = [];

foreach ($depenses_par_rubrique as $row) {
    if (!empty($row['rubrique']) && !in_array($row['rubrique'], $rubriques_data)) {
        $rubriques_data[] = $row['rubrique'];
    }
}

foreach ($depenses_par_rubrique as $row) {
    $dept = $row['nom_departement'];
    $rubrique = $row['rubrique'];
    $montant = floatval($row['montant_total']);
    
    if (!isset($departements_data[$dept])) {
        $departements_data[$dept] = [];
    }
    
    if (!empty($rubrique)) {
        $departements_data[$dept][$rubrique] = $montant;
    }
}

// ============================================
// STATISTIQUES
// ============================================
$total_depense = 0;
foreach ($depenses_par_rubrique as $row) {
    $total_depense += floatval($row['montant_total']);
}
$total_rubriques = count($rubriques_data);
$total_departements = count($departements_data);

// ============================================
// CALCUL DU BUDGET PREVU - CORRIGE
// ============================================
$budget_prevu = 0;

if (empty($departement_selectionne)) {
    // Budget GLOBAL pour "Tous les departements"
    $sql_budget = "
        SELECT COALESCE(SUM(montant), 0) as budget_total
        FROM budgets
        WHERE 1=1
    ";
    $params_budget = [];
    
    if (!empty($annee_selectionnee)) {
        $sql_budget .= " AND annee = :annee";
        $params_budget[':annee'] = $annee_selectionnee;
    }
    
    $stmt = $pdo->prepare($sql_budget);
    $stmt->execute($params_budget);
    $budget_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $budget_prevu = floatval($budget_result['budget_total'] ?? 0);
    
} else {
    // Budget du DEPARTEMENT SPECIFIQUE
    $sql_budget = "
        SELECT COALESCE(SUM(dp.montant_departement), 0) as budget_total
        FROM departements d
        LEFT JOIN depense_prevision dp ON dp.id_departement = d.id_departement
        LEFT JOIN budgets b ON b.id_budget = dp.id_budget
        WHERE d.nom_departement = :departement
    ";
    $params_budget = [':departement' => $departement_selectionne];
    
    if (!empty($annee_selectionnee)) {
        $sql_budget .= " AND d.annee = :annee AND b.annee = :annee";
        $params_budget[':annee'] = $annee_selectionnee;
    }
    
    $stmt = $pdo->prepare($sql_budget);
    $stmt->execute($params_budget);
    $budget_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $budget_prevu = floatval($budget_result['budget_total'] ?? 0);
}

$fond_disponible = $budget_prevu - $total_depense;

// ============================================
// VALEUR MAX POUR LE GRAPHIQUE
// ============================================
$max_value = 0;
foreach ($departements_data as $rubriques) {
    foreach ($rubriques as $montant) {
        if ($montant > $max_value) $max_value = $montant;
    }
}
if ($max_value == 0) $max_value = 1;

// COULEUR UNIQUE FONCEE
$couleur_unique = '#8B0000';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse Depenses</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #e8e8e8;
            color: #1a1a2e;
            padding: 12px;
        }
        
        .container { max-width: 1500px; margin: 0 auto; }
        
        /* HEADER */
        .header {
            background: cadetblue;
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .header-title h1 {
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
        }
        
        .header-title p {
            font-size: 11px;
            color: #adb5bd;
        }
        
        .header-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .header-controls label {
            font-weight: 600;
            font-size: 11px;
            color: #adb5bd;
        }
        
        .header-controls select {
            padding: 5px 25px 5px 10px;
            border: 1.5px solid #2d2d44;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            background: #2d2d44;
            color: #ffffff;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            cursor: pointer;
            min-width: 110px;
        }
        
        .header-controls select:focus {
            outline: none;
            border-color: #8B0000;
        }
        
        .header-controls select option {
            background: #1a1a2e;
            color: #ffffff;
        }
        
        .btn {
            padding: 5px 14px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-filtrer { background: #8B0000; color: white; }
        .btn-filtrer:hover { background: #6B0000; }
        .btn-reset { background: #1a3a2e; color: white; }
        .btn-reset:hover { background: #0d2a1e; }
        .btn-retour { background: transparent; color: #ffffff; border: 1.5px solid #2d2d44; }
        .btn-retour:hover { background: #2d2d44; }
        
        /* STATS - 3 CARDS */
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 12px;
        }
        
        .stat {
            background: #1a1a2e;
            border-radius: 10px;
            padding: 10px 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 3px solid #8B0000;
        }
        
        .stat.vert { border-left-color: #1a4a3a; }
        .stat.blanc { border-left-color: #3d3d5c; }
        
        .stat .label {
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            color: #adb5bd;
            letter-spacing: 0.3px;
        }
        
        .stat .value {
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
        }
        
        .stat .value small {
            font-size: 10px;
            font-weight: 400;
            color: #adb5bd;
        }
        
        .stat .sub {
            font-size: 10px;
            color: #adb5bd;
            margin-top: 2px;
        }
        
        /* FILTRE INFO */
        .filtre-info {
            background: white;
            padding: 8px 16px;
            border-radius: 8px;
            margin-bottom: 12px;
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
            font-size: 12px;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .filtre-info .label { font-weight: 600; color: #adb5bd; }
        .filtre-info .value { color: #ffffff; font-weight: 500; }
        .filtre-info .value.rouge { color: #cc5555; }
        .filtre-info .value.vert { color: #55cc88; }
        
        /* LAYOUT - GRAPHIQUE + TABLE COTE A COTE */
        .content-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        /* CARD */
        .card {
            background: #1a1a2e;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .card-header {
            padding: 10px 16px;
            border-bottom: 1px solid #2d2d44;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }
        
        .card-header h3 {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
        }
        
        .card-header .badge {
            font-size: 10px;
            padding: 2px 12px;
            border-radius: 20px;
            background: #2d2d44;
            color: #adb5bd;
            font-weight: 600;
        }
        
        .card-body { padding: 12px 14px; }
        
        /* GRAPHIQUE */
        .chart-scroll {
            overflow-x: auto;
            padding: 4px 0;
        }
        
        .chart {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            min-height: 200px;
            gap: 20px;
            padding: 5px 8px;
            min-width: 400px;
        }
        
        .chart-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }
        
        .chart-group .group-label {
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
            text-align: center;
        }
        
        .chart-bars {
            display: flex;
            align-items: flex-end;
            gap: 5px;
            height: 140px;
            justify-content: center;
        }
        
        .chart-bar-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        
        .chart-bar {
            width: 22px;
            border-radius: 3px 3px 0 0;
            min-height: 3px;
            transition: height 0.6s ease;
            cursor: pointer;
            background: #8B0000;
        }
        
        .chart-bar:hover {
            opacity: 0.7;
            transform: scaleY(1.02);
        }
        
        /* Tooltip */
        .chart-bar-wrapper .tooltip {
            display: none;
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #0d0d1a;
            color: #ffffff;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
            white-space: nowrap;
            margin-bottom: 5px;
            z-index: 10;
            pointer-events: none;
            border: 1px solid #2d2d44;
        }
        
        .chart-bar-wrapper:hover .tooltip {
            display: block;
        }
        
        .chart-bar-wrapper .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: #0d0d1a;
        }
        
        /* TABLE */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        .detail-table thead th {
            background: #0d0d1a;
            color: #adb5bd;
            padding: 6px 10px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #2d2d44;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .detail-table tbody td {
            padding: 5px 10px;
            border-bottom: 1px solid #2d2d44;
            color: #e0e0e0;
        }
        
        .detail-table tbody tr:hover { background: #0d0d1a; }
        .detail-table .total-row {
            font-weight: 700;
            background: #0d0d1a;
            border-top: 2px solid #8B0000;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-700 { font-weight: 700; }
        .color-rouge { color: #cc5555; }
        .color-vert { color: #55cc88; }
        
        .empty-state {
            text-align: center;
            padding: 25px 10px;
            color: #adb5bd;
        }
        .empty-state h3 { font-size: 15px; margin-bottom: 4px; color: #ffffff; }
        .empty-state p { font-size: 12px; color: #adb5bd; }
        
        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .content-wrapper {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .stats { grid-template-columns: 1fr 1fr; }
            .header { flex-direction: column; align-items: stretch; }
            .header-controls { flex-direction: column; align-items: stretch; }
            .header-controls select { width: 100%; }
            .chart { gap: 12px; min-width: 300px; min-height: 170px; }
            .chart-bar { width: 18px; }
            .chart-bars { height: 110px; }
            .filtre-info { flex-direction: column; gap: 4px; }
        }
        
        @media (max-width: 480px) {
            .stats { grid-template-columns: 1fr; }
            .chart { gap: 8px; min-width: 250px; min-height: 150px; }
            .chart-bar { width: 14px; }
            .chart-bars { height: 90px; }
            .detail-table { font-size: 10px; }
            .detail-table th, .detail-table td { padding: 4px 6px; }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <div class="header-title">
            <h1>Analyse du Budget</h1>
            <p>Analyse des depenses par departement</p>
        </div>
        <div class="header-controls">
            <form method="GET" action="" style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                <div>
                    <label>Annee</label>
                    <select name="annee">
                        <option value="">Toutes</option>
                        <?php foreach ($annees_disponibles as $annee): ?>
                            <option value="<?php echo htmlspecialchars($annee); ?>" <?php echo ($annee_selectionnee == $annee) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($annee); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Departement</label>
                    <select name="departement">
                        <option value="">Tous</option>
                        <?php foreach ($departements_liste as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo ($departement_selectionne == $dept) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-filtrer">Filtrer</button>
                <a href="ai_recap.php" class="btn btn-reset">Reset</a>
            </form>
            <button class="btn btn-retour" onclick="window.location.href='dashboard.php'">Dashboard</button>
        </div>
    </div>

    <!-- FILTRE INFO -->
    <div class="filtre-info">
        <span><span class="label">Annee :</span> <span class="value <?php echo empty($annee_selectionnee) ? '' : 'rouge'; ?>"><?php echo empty($annee_selectionnee) ? 'Toutes' : htmlspecialchars($annee_selectionnee); ?></span></span>
        <span><span class="label">Departement :</span> <span class="value <?php echo empty($departement_selectionne) ? '' : 'vert'; ?>"><?php echo empty($departement_selectionne) ? 'Tous' : htmlspecialchars($departement_selectionne); ?></span></span>
        <span><span class="label">Budget Prevu :</span> <span class="value vert"><?php echo number_format($budget_prevu, 0, '.', ' '); ?> Ar</span></span>
        <span><span class="label">Depenses :</span> <span class="value rouge"><?php echo number_format($total_depense, 0, '.', ' '); ?> Ar</span></span>
        <span><span class="label">Fond Disponible :</span> <span class="value" style="color: green"><?php echo number_format($fond_disponible, 0, '.', ' '); ?> Ar</span></span>
    </div>

    <!-- STATS - 3 CARDS -->
    <div class="stats">
        <div class="stat">
            <div class="label">Budget Prevu</div>
            <div class="value"><?php echo number_format($budget_prevu, 0, '.', ' '); ?> <small>Ar</small></div>
            <div class="sub"><?php echo empty($departement_selectionne) ? 'Budget global' : 'Budget ' . htmlspecialchars($departement_selectionne); ?></div>
        </div>
        <div class="stat vert">
            <div class="label">Depense Cumulee</div>
            <div class="value"><?php echo number_format($total_depense, 0, '.', ' '); ?> <small>Ar</small></div>
            <div class="sub">Depenses realisees</div>
        </div>
        <div class="stat blanc">
            <div class="label">Fond Disponible</div>
            <div class="value"><?php echo number_format($fond_disponible, 0, '.', ' '); ?> <small>Ar</small></div>
            <div class="sub">Budget restant</div>
        </div>
    </div>

    <!-- CONTENU : GRAPHIQUE + TABLE COTE A COTE -->
    <div class="content-wrapper">
        
        <!-- GRAPHIQUE -->
        <div class="card">
            <div class="card-header">
                <h3>Depenses par Rubrique</h3>
                <span class="badge"><?php echo empty($annee_selectionnee) ? 'Toutes' : htmlspecialchars($annee_selectionnee); ?></span>
            </div>
            <div class="card-body">
                <?php if (!empty($departements_data) && !empty($rubriques_data)): ?>
                    <div class="chart-scroll">
                        <div class="chart">
                            <?php foreach ($departements_data as $dept => $rubriques): ?>
                                <div class="chart-group">
                                    <div class="group-label"><?php echo htmlspecialchars($dept); ?></div>
                                    <div class="chart-bars">
                                        <?php 
                                        foreach ($rubriques as $rubrique => $montant):
                                            $height = ($montant / $max_value) * 120;
                                        ?>
                                            <div class="chart-bar-wrapper">
                                                <div class="chart-bar" style="height: <?php echo max($height, 3); ?>px;"></div>
                                                <div class="tooltip"><?php echo htmlspecialchars($rubrique); ?>: <?php echo number_format($montant, 0, '.', ' '); ?> Ar</div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>Aucune donnee</h3>
                        <p>Importez des depenses pour visualiser.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TABLE -->
        <div class="card">
            <div class="card-header">
                <h3>Detail des Depenses</h3>
                <span class="badge"><?php echo count($depenses_par_rubrique); ?></span>
            </div>
            <div class="card-body" style="padding: 8px 12px;">
                <?php if (!empty($depenses_par_rubrique)): ?>
                    <div style="overflow-x: auto; max-height: 260px; overflow-y: auto;">
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>Departement</th>
                                    <th>Rubrique</th>
                                    <th class="text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                foreach ($depenses_par_rubrique as $row):
                                    $montant = floatval($row['montant_total']);
                                    $total += $montant;
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['nom_departement'] ?? ''); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['rubrique'] ?? 'Sans'); ?></td>
                                        <td class="text-right color-rouge fw-700"><?php echo number_format($montant, 0, '.', ' '); ?> Ar</td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td colspan="2"><strong>TOTAL</strong></td>
                                    <td class="text-right color-rouge fw-700"><?php echo number_format($total, 0, '.', ' '); ?> Ar</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>Aucune depense</h3>
                        <p>Importez des depenses pour voir les details.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            document.querySelectorAll('.chart-bar').forEach(function(bar) {
                var height = bar.style.height;
                bar.style.height = '0px';
                setTimeout(function() {
                    bar.style.height = height;
                }, 150);
            });
        }, 300);
    });
</script>

</body>
</html>