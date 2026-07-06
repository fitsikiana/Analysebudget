<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: acceuil.php");
    exit();
}
include "../config/db.php";

include "../actions/calcul_budget_general.php"; 
include "../actions/calcul_depense_global.php"; 
include "../actions/select_utilisateurs.php";
include "../actions/select_depense_realisees.php"; 
include "../actions/calcul_departement_stats.php";
include "../actions/budget_par_annee.php";
include "../actions/rapport_depenses.php";

// Vérification et initialisation des variables
$annee_courante = !empty($annees_disponibles) ? $annees_disponibles[0] : date('Y') . '-' . (date('Y') + 1);
$global_budget = 0;
$global_depense = 0;

if (!empty($situationsAnnuelles)) {
    $annee_base = $situationsAnnuelles[0]['annee'];
    $annee_courante = (strpos($annee_base, '-') !== false) ? $annee_base : $annee_base . "-" . ($annee_base + 1);
    $global_budget = floatval($situationsAnnuelles[0]['budget_global'] ?? 0);
    $global_depense = floatval($situationsAnnuelles[0]['total_depenses_cumulees'] ?? 0);
}
$global_disponible = $global_budget - $global_depense;
$global_taux = ($global_budget > 0) ? round(($global_depense / $global_budget) * 100, 1) : 0;

$global_bar_class = "low-success"; 
if ($global_taux > 100) { 
    $global_bar_class = "high-error"; 
} elseif ($global_taux >= 75) { 
    $global_bar_class = "medium-warning"; 
}

// ============================================
// GESTION DES IMPORTS
// ============================================
$msg_budget = "";
$status_budget = ""; 
$msg_depense = "";
$status_depense = ""; 

// 1. IMPORT BUDGET
if (isset($_POST['import_budget']) && isset($_FILES['file_budget'])) {
    include "../actions/import_budget.php"; 
    exit();
}

// 2. IMPORT DEPENSE
if (isset($_POST['import_depense']) && isset($_FILES['file_depense'])) {
    include "../actions/serv_depense.php"; 
    exit();
}

// ============================================
// GESTION DES MESSAGES D'IMPORT
// ============================================
if (isset($_GET['success'])) {
    $successMsg = htmlspecialchars($_GET['success']);
    
    if (strpos($_GET['success'], 'depense') !== false || 
        strpos($_GET['success'], 'Depense') !== false ||
        strpos($_GET['success'], 'depenses') !== false) {
        $status_depense = "success";
        $msg_depense = $successMsg;
    } else {
        $status_budget = "success";
        $msg_budget = $successMsg;
    }
}

if (isset($_GET['error'])) {
    $errorMsg = htmlspecialchars($_GET['error']);
    
    if (strpos($_GET['error'], 'depense') !== false || 
        strpos($_GET['error'], 'Depense') !== false ||
        strpos($_GET['error'], 'depenses') !== false) {
        $status_depense = "duplicate";
        $msg_depense = $errorMsg;
    } else {
        $status_budget = "duplicate";
        $msg_budget = $errorMsg;
    }
}

if (isset($_GET['info']) && $_GET['info'] === 'ajoute') {
    $msg_user_success = "Nouvel utilisateur ajoute avec succes.";
}

// Annee selectionnee pour le filtre
$annee_selectionnee = isset($_GET['annee']) ? $_GET['annee'] : $annee_courante;
if (!isset($donnees_par_annee[$annee_selectionnee]) && !empty($donnees_par_annee)) {
    $annee_selectionnee = array_key_first($donnees_par_annee);
}

$donnees_affichees = isset($donnees_par_annee[$annee_selectionnee]) ? $donnees_par_annee[$annee_selectionnee] : [
    'budget' => 0,
    'depense' => 0,
    'disponible' => 0,
    'taux' => 0,
    'departements' => [],
    'depenses' => []
];

// Departement selectionne
$departement_selectionne = isset($_GET['dept']) ? $_GET['dept'] : 'all';
$details_departement = [];
$total_budget_dept = 0;
$total_depense_dept = 0;

// Donnees pour l'affichage des KPI
$kpi_budget = $donnees_affichees['budget'];
$kpi_depense = $donnees_affichees['depense'];
$kpi_disponible = $donnees_affichees['disponible'];
$kpi_taux = $donnees_affichees['taux'];
$kpi_label = "Budget Global";

if ($departement_selectionne != 'all') {
    if (function_exists('getDetailsDepartement')) {
        $details_departement = getDetailsDepartement($pdo, $departement_selectionne, $annee_selectionnee);
    }
    if (function_exists('getTotalBudgetDepartement')) {
        $total_budget_dept = getTotalBudgetDepartement($pdo, $departement_selectionne, $annee_selectionnee);
    }
    if (function_exists('getTotalDepenseDepartement')) {
        $total_depense_dept = getTotalDepenseDepartement($pdo, $departement_selectionne, $annee_selectionnee);
    }
}

// Recuperer la synthese de tous les departements
$synthese_departements = [];
if (function_exists('getSyntheseDepartements')) {
    $synthese_departements = getSyntheseDepartements($pdo, $annee_selectionnee);
}

// Recuperer les annees disponibles pour le filtre
$annees_disponibles = array_keys($donnees_par_annee);
if (empty($annees_disponibles)) {
    $annees_disponibles = [date('Y') . '-' . (date('Y') + 1)];
}

// Couleur de la barre de progression
$bar_class = "low-success";
if ($kpi_taux > 100) { 
    $bar_class = "high-error"; 
} elseif ($kpi_taux >= 75) { 
    $bar_class = "medium-warning"; 
}

// Vérification de l'existence de $data_affichage pour le JavaScript
$data_affichage = $data_affichage ?? ['Etude' => ['budget' => 0, 'depense' => 0], 'Administration' => ['budget' => 0, 'depense' => 0], 'Logistique' => ['budget' => 0, 'depense' => 0]];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegieBudget — ONIFRA Antsirabe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .progress-bar.high-error { background-color: #dc3545 !important; } 
        .progress-bar.medium-warning { background-color: #fd7e14 !important; } 
        .progress-bar.low-success { background-color: #198754 !important; } 
        
        .table-progress-container { display: flex; align-items: center; gap: 10px; min-width: 160px; }
        .table-progress-track { flex-grow: 1; background-color: #e9ecef; height: 8px; border-radius: 4px; overflow: hidden; }
        .table-progress-bar { height: 100%; border-radius: 4px; transition: width 0.4s ease; }
        .table-taux-text { font-weight: 700; font-size: 13px; min-width: 45px; text-align: right; }
        
        .text-danger-custom { color: #dc3545 !important; }
        .text-warning-custom { color: #fd7e14 !important; }
        .text-success-custom { color: #198754 !important; }

        .status-badge.danger { background-color: #f8d7da; color: #842029; }
        .status-badge.warning { background-color: #fff3cd; color: #664d03; }
        .status-badge.success { background-color: #d1e7dd; color: #0f5132; }

        .alert-box {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 14px;
            line-height: 1.5;
            display: flex;
            align-items: center;
        }
        .alert-box.alert-success { background-color: #d1e7dd; color: #0f5132; border-left: 5px solid #198754; }
        .alert-box.alert-duplicate { background-color: #fff3cd; color: #664d03; border-left: 5px solid #ffc107; }

        .mini-table { width: 100%; margin-top: 15px; margin-bottom: 30px; border-collapse: collapse; font-size: 13px; }
        .mini-table th { background-color: #f8f9fa; color: #495057; text-align: left; padding: 10px; border-bottom: 2px solid #dee2e6; font-weight: 600; }
        .mini-table td { padding: 10px; border-bottom: 1px solid #dee2e6; color: #212529; }
        .badge-status-js { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; display: inline-block; }
        .badge-status-js.succes { background-color: #d1e7dd; color: #0f5132; }
        .badge-status-js.refuse { background-color: #f8d7da; color: #842029; }

        .modal-user-custom {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }
        .modal-user-content {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        .modal-user-header h3 { margin: 0; color: var(--marine); }
        .close-user-modal {
            font-size: 24px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }
        .close-user-modal:hover { color: #000; }

        .filter-annee-dashboard {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px 20px;
            background: var(--gris-1);
            border-radius: 8px;
            border: 1px solid var(--gris-2);
        }
        .filter-annee-dashboard label {
            font-weight: 600;
            font-size: 14px;
            color: var(--texte);
        }
        .filter-annee-dashboard select {
            padding: 8px 32px 8px 14px;
            border: 1.5px solid var(--gris-2);
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            color: var(--texte);
            background: var(--blanc) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%23767676' d='M5 6L0 0h10z'/%3E%3C/svg%3E") no-repeat right 12px center;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            cursor: pointer;
            min-width: 180px;
        }
        .filter-annee-dashboard select:focus {
            outline: none;
            border-color: var(--rouge);
        }
        .filter-annee-dashboard .annee-info {
            font-size: 13px;
            color: var(--texte-2);
            margin-left: 10px;
        }

        .detail-container {
            margin-top: 20px;
        }
        .detail-container h3 {
            margin-bottom: 15px;
            color: var(--marine);
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 15px;
        }
        .detail-table th {
            background-color: darkseagreen;
            color: white;
            padding: 10px;
            text-align: left;
        }
        .detail-table td {
            padding: 10px;
            border-bottom: 1px solid var(--gris-2);
        }
        .detail-table tbody tr:hover {
            background-color: var(--gris-1);
        }
        .summary-card {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            padding: 15px 20px;
            background: var(--gris-1);
            border-radius: 8px;
            border: 1px solid var(--gris-2);
        }
        .summary-card .item {
            flex: 1;
            min-width: 150px;
        }
        .summary-card .item .label {
            font-size: 12px;
            color: var(--texte-2);
            font-weight: 600;
            text-transform: uppercase;
        }
        .summary-card .item .value {
            font-size: 18px;
            font-weight: 700;
            color: var(--texte);
        }
        .summary-card .item .value.marine { color: var(--marine); }
        .summary-card .item .value.orange { color: var(--orange); }
        .summary-card .item .value.vert { color: var(--vert); }
        .summary-card .item .value.rouge { color: var(--rouge); }

        .btn-action {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
        }
        .btn-pdf {
            background-color: #dc3545;
            color: white;
        }
        .btn-pdf:hover {
            background-color: #b02a37;
        }
        .btn-email {
            background-color: #198754;
            color: white;
        }
        .btn-email:hover {
            background-color: #157347;
        }
        .action-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .action-bar input[type="email"] {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 13px;
            width: 220px;
        }
        .empty-message {
            text-align: center;
            color: var(--texte-2);
            padding: 40px 20px;
            font-size: 15px;
        }
        .kpi-dept-name {
            font-size: 13px;
            color: var(--marine);
            font-weight: 700;
            display: block;
            margin-top: 2px;
        }
        .btn-orange {
            background-color: var(--orange);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-orange:hover {
            background-color: #b05a15;
        }
        .badge-taux {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-taux.eleve {
            background-color: #f8d7da;
            color: #842029;
        }
        .badge-taux.moyen {
            background-color: #fff3cd;
            color: #664d03;
        }
        .badge-taux.faible {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .synthese-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .synthese-table th {
            background-color: darkseagreen;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .synthese-table td {
            padding: 12px;
            border-bottom: 1px solid var(--gris-2);
        }
        .synthese-table tbody tr:hover {
            background-color: var(--gris-1);
        }
        .synthese-total {
            font-weight: bold;
            background-color: var(--gris-1);
            border-top: 2px solid var(--marine);
        }
        .synthese-total td {
            padding: 12px;
        }
        .btn-supprimer-log {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 13px;
            padding: 4px 12px;
            border-radius: 4px;
            transition: all 0.2s ease;
            opacity: 0.6;
            color: #dc3545;
            font-weight: 600;
        }
        .btn-supprimer-log:hover {
            background-color: #fee2e2;
            opacity: 1;
            transform: scale(1.05);
        }
        .btn-supprimer-log:active {
            transform: scale(0.95);
        }
        .filter-select {
            padding: 8px 15px;
            border: 1.5px solid var(--gris-2);
            border-radius: 6px;
            font-size: 14px;
            background: var(--blanc);
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="app-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <span class="brand-title">RegieBudget</span>
            <span class="brand-subtitle">ONIFRA ANTSIRABE</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">Menu Principal</div>
            <ul class="nav-list">
                <li class="nav-item active" data-tab="dashboard">
                    <a href="#" onclick="showTab('dashboard'); return false;">Tableau de Bord</a>
                </li>
                <li class="nav-item" data-tab="import-budget">
                    <a href="#" onclick="showTab('import-budget'); return false;">Import Budget</a>
                </li>
                <li class="nav-item" data-tab="import-depense">
                    <a href="#" onclick="showTab('import-depense'); return false;">Import Depenses</a>
                </li>
                <li class="nav-item" data-tab="gestion-utilisateurs">
                    <a href="#" onclick="showTab('gestion-utilisateurs'); return false;">Gestion Utilisateurs</a>
                </li>
                <li class="nav-item" style="margin-top: 30px;">
                    <a href="../actions/serv_deconnexion.php" style="color: #dc3545; font-weight: bold;">Deconnexion</a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <div class="breadcrumb">Application / <span id="breadcrumb-text">Tableau de Bord</span></div>
                <h2 class="page-title">Suivi Budgetaire Oniversity</h2>
            </div>
            <div style="display: flex; gap: 15px; align-items: center;">
                <a href="ai_recap.php" class="btn btn-primary" style="background: linear-gradient(135deg, #6366f1, #a855f7); color: white; text-decoration: none; font-weight: bold; padding: 10px 18px; border-radius: 6px; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3); display: flex; align-items: center; gap: 8px;">
                    ANALYSE DU BUDGET
                </a>
                <div class="year-badge" style="color: black"><?php echo htmlspecialchars($_SESSION['email'] ?? 'Utilisateur'); ?></div>
            </div>
        </header>

        <div class="content-area">

            <div id="dashboard" class="tab-panel active">
                
                <!-- Filtres -->
                <div class="filter-annee-dashboard">
                    <label>Filtrer par annee :</label>
                    <select id="annee-filter-dashboard" class="filter-annee-select" onchange="changeAnnee(this.value)">
                        <?php foreach ($annees_disponibles as $annee): ?>
                            <option value="<?php echo htmlspecialchars($annee); ?>" <?php echo ($annee_selectionnee == $annee) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($annee); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="annee-info">Affichage des donnees historiques pour l'annee selectionnee</span>
                </div>

                <div class="panel" style="margin-bottom: 20px;">
                    <div class="panel-header" style="background: var(--gris-1);">
                        <div class="panel-title"><h3>Filtrer par Departement</h3></div>
                        <select id="main-dept-filter" class="filter-select" onchange="changeDepartement(this.value)">
                            <option value="all" <?php echo ($departement_selectionne == 'all') ? 'selected' : ''; ?>>Tous les departements</option>
                            <option value="Etude" <?php echo ($departement_selectionne == 'Etude') ? 'selected' : ''; ?>>Etude</option>
                            <option value="Administration" <?php echo ($departement_selectionne == 'Administration') ? 'selected' : ''; ?>>Administration</option>
                            <option value="Logistique" <?php echo ($departement_selectionne == 'Logistique') ? 'selected' : ''; ?>>Logistique</option>
                        </select>
                    </div>
                </div>

                <!-- KPI - Affichage uniquement si "Tous les departements" est selectionne -->
                <?php if ($departement_selectionne == 'all'): ?>
                <div class="kpi-grid">
                    <div class="kpi-card c-marine" id="kpi-budget-card">
                        <span class="kpi-label" id="kpi-budget-label">
                            <?php echo htmlspecialchars($kpi_label); ?>
                        </span>
                        <span class="kpi-value" id="kpi-budget-value" data-global="<?php echo $kpi_budget; ?>">
                            <?php echo number_format($kpi_budget, 0, '.', ' '); ?> Ar
                        </span>
                    </div>
                    <div class="kpi-card c-orange" id="kpi-depense-card">
                        <span class="kpi-label" id="kpi-depense-label">
                            Depenses Cumulees
                        </span>
                        <span class="kpi-value" id="kpi-depense-value" data-global="<?php echo $kpi_depense; ?>">
                            <?php echo number_format($kpi_depense, 0, '.', ' '); ?> Ar
                        </span>
                    </div>
                    <div class="kpi-card c-vert" id="kpi-dispo-card">
                        <span class="kpi-label" id="kpi-dispo-label">
                            Fond Disponible
                        </span>
                        <span class="kpi-value" id="kpi-dispo-value" data-global="<?php echo $kpi_disponible; ?>">
                            <?php echo number_format($kpi_disponible, 0, '.', ' '); ?> Ar
                        </span>
                    </div>
                    <div class="kpi-card c-rouge" id="kpi-taux-card">
                        <span class="kpi-label">
                            Taux d'Execution
                        </span>
                        <span class="kpi-value" id="kpi-taux-value" data-global="<?php echo $kpi_taux; ?>">
                            <?php echo $kpi_taux; ?> %
                        </span>
                        <div class="progress-container" style="margin-top: 5px;">
                            <div class="progress-track">
                                <div class="progress-bar <?php echo $bar_class; ?>" id="kpi-taux-bar" style="width: <?php echo min($kpi_taux, 100); ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Synthese des Departements - Affichage uniquement si "Tous les departements" est selectionne -->
                <?php if ($departement_selectionne == 'all'): ?>
                <div class="panel" style="margin-top: 20px;">
                    <div class="panel-header">
                        <div class="panel-title">
                            <h3>Synthese par Departement</h3>
                        </div>
                        <div style="font-size: 13px; color: var(--texte-2);">
                            Annee: <strong><?php echo htmlspecialchars($annee_selectionnee); ?></strong>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($synthese_departements)): ?>
                            <table class="synthese-table">
                                <thead>
                                    <tr>
                                        <th>Departement</th>
                                        <th style="text-align: right;">Budget Prevu</th>
                                        <th style="text-align: right;">Depenses Realisees</th>
                                        <th style="text-align: right;">Fond Disponible</th>
                                        <th style="text-align: center;">Taux</th>
                                        <th style="text-align: center;">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_budget = 0;
                                    $total_depense = 0;
                                    $total_disponible = 0;
                                    foreach ($synthese_departements as $row):
                                        $budget = floatval($row['budget'] ?? 0);
                                        $depense = floatval($row['depense'] ?? 0);
                                        $disponible = $budget - $depense;
                                        $total_budget += $budget;
                                        $total_depense += $depense;
                                        $total_disponible += $disponible;
                                        $taux = $budget > 0 ? round(($depense / $budget) * 100, 1) : 0;
                                        $statut_class = ($taux > 100) ? 'danger' : (($taux >= 75) ? 'warning' : 'success');
                                        $statut = ($taux > 100) ? 'Depasse' : (($taux >= 75) ? 'Critique' : 'Bon');
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['departement']); ?></strong></td>
                                        <td style="text-align: right; color: var(--marine); font-weight:600;">
                                            <?php echo number_format($budget, 0, '.', ' '); ?> Ar
                                        </td>
                                        <td style="text-align: right; color: var(--orange); font-weight:600;">
                                            <?php echo number_format($depense, 0, '.', ' '); ?> Ar
                                        </td>
                                        <td style="text-align: right; color: var(--vert); font-weight:600;">
                                            <?php echo number_format($disponible, 0, '.', ' '); ?> Ar
                                        </td>
                                        <td style="text-align: center; font-weight:700;">
                                            <?php echo $taux; ?> %
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="status-badge <?php echo $statut_class; ?>">
                                                <?php echo $statut; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="synthese-total">
                                        <td><strong>TOTAL GENERAL</strong></td>
                                        <td style="text-align: right; color: var(--marine); font-weight:700;">
                                            <?php echo number_format($total_budget, 0, '.', ' '); ?> Ar
                                        </td>
                                        <td style="text-align: right; color: var(--orange); font-weight:700;">
                                            <?php echo number_format($total_depense, 0, '.', ' '); ?> Ar
                                        </td>
                                        <td style="text-align: right; color: var(--vert); font-weight:700;">
                                            <?php echo number_format($total_disponible, 0, '.', ' '); ?> Ar
                                        </td>
                                        <td style="text-align: center; font-weight:700;">
                                            <?php 
                                            $taux_total = $total_budget > 0 ? round(($total_depense / $total_budget) * 100, 1) : 0;
                                            echo $taux_total . ' %';
                                            ?>
                                        </td>
                                        <td style="text-align: center;"></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-message">
                                Aucune donnee disponible pour l'annee <?php echo htmlspecialchars($annee_selectionnee); ?>.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Details des Depenses par Departement -->
                <?php if ($departement_selectionne != 'all'): ?>
                <div class="detail-container">
                    <div class="panel">
                        <div class="panel-header">
                            <div class="panel-title">
                                <h3 id="detail-panel-title">
                                    Details des Depenses Realisees — <?php echo htmlspecialchars($departement_selectionne); ?>
                                </h3>
                            </div>
                            <div class="action-bar">
                                <input type="email" id="input-email-destinataire" placeholder="Email destinataire">
                                <a href="../actions/generate_pdf.php" target="_blank" id="pdf-link" onclick="updatePdfLink()">
                                    <button class="btn-action btn-pdf" type="button">EXPORTER EN PDF</button>
                                </a>
                                <button class="btn-action btn-email" id="btn-send-email" type="button">ENVOYER PAR EMAIL</button>
                            </div>
                        </div>
                        <div class="panel-body">
                            <?php if (!empty($details_departement)): ?>
                                <!-- Resume cards -->
                                <div class="summary-card">
                                    <div class="item">
                                        <div class="label">Budget Alloue</div>
                                        <div class="value marine"><?php echo number_format($total_budget_dept, 0, '.', ' '); ?> Ar</div>
                                    </div>
                                    <div class="item">
                                        <div class="label">Total Depenses</div>
                                        <div class="value orange"><?php echo number_format($total_depense_dept, 0, '.', ' '); ?> Ar</div>
                                    </div>
                                    <div class="item">
                                        <div class="label">Fond Disponible</div>
                                        <div class="value vert"><?php echo number_format($total_budget_dept - $total_depense_dept, 0, '.', ' '); ?> Ar</div>
                                    </div>
                                    <div class="item">
                                        <div class="label">Taux de Consommation</div>
                                        <div class="value <?php 
                                            $taux_total = $total_budget_dept > 0 ? round(($total_depense_dept / $total_budget_dept) * 100, 1) : 0;
                                            echo ($taux_total > 100) ? 'rouge' : (($taux_total >= 75) ? 'orange' : 'vert');
                                        ?>">
                                            <?php echo $taux_total; ?> %
                                        </div>
                                    </div>
                                </div>

                                <table class="detail-table" id="detail-table">
                                    <thead>
                                        <tr>
                                            <th>Designation</th>
                                            <th style="text-align: right;">Montant</th>
                                            <th>Date Depense</th>
                                            <th style="text-align: right;">Budget Prevu</th>
                                            <th style="text-align: center;">Taux de Consommation</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail-body">
                                        <?php 
                                        if (!empty($details_departement)):
                                            foreach ($details_departement as $row):
                                                $montant = floatval($row['montant'] ?? 0);
                                                $budget_prevu = floatval($row['budget_prevu'] ?? 0);
                                                $taux = $budget_prevu > 0 ? round(($montant / $budget_prevu) * 100, 1) : 0;
                                                $designation = $row['rubrique'] ?? $row['designation'] ?? 'Sans designation';
                                        ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($designation); ?></strong></td>
                                                <td style="text-align: right; color: var(--orange); font-weight:600;">
                                                    <?php echo number_format($montant, 0, '.', ' '); ?> Ar
                                                </td>
                                                <td><?php echo htmlspecialchars($row['date_depense'] ?? ''); ?></td>
                                                <td style="text-align: right;">
                                                    <?php echo number_format($budget_prevu, 0, '.', ' '); ?> Ar
                                                </td>
                                                <td style="text-align: center;">
                                                    <span class="badge-taux <?php 
                                                        if ($taux > 100) echo 'eleve'; 
                                                        elseif ($taux >= 75) echo 'moyen'; 
                                                        else echo 'faible'; 
                                                    ?>">
                                                        <?php echo $taux; ?> %
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php 
                                            endforeach;
                                        endif; 
                                        ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-message">
                                    Aucune depense detaillee pour le departement <?php echo htmlspecialchars($departement_selectionne); ?> pour l'annee <?php echo htmlspecialchars($annee_selectionnee); ?>.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Onglet IMPORT BUDGET -->
            <div id="import-budget" class="tab-panel">
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title"><h3>Importer un Fichier Budget (Excel)</h3></div>
                    </div>
                    <div class="panel-body">
                        <?php if(!empty($msg_budget)): ?>
                            <div class="alert-box <?php echo ($status_budget == 'success') ? 'alert-success' : 'alert-duplicate'; ?>">
                                <?php echo htmlspecialchars($msg_budget); ?>
                            </div>
                        <?php endif; ?>
                        
                        <p style="color: var(--texte-2); margin-bottom: 20px;">Veuillez choisir le fichier du budget previsionnel</p>
                        <form id="form-import-budget" action="" method="POST" enctype="multipart/form-data" style="max-width: 500px;">
                            <div style="margin-bottom: 15px;">
                                <input type="file" id="file-input-budget" name="file_budget" accept=".csv, .xlsx" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                            </div>
                            <button type="submit" name="import_budget" class="btn btn-primary">Lancer l'importation</button>
                        </form>

                        <!-- Historique des imports budget -->
                        <div style="margin-top: 30px;">
                            <h4 style="margin-bottom: 10px; color: var(--marine);">Historique des imports budget</h4>
                            <table class="mini-table" id="table-log-budget">
                                <thead>
                                    <tr>
                                        <th>Nom du Fichier</th>
                                        <th>Date d'importation</th>
                                        <th>Statut</th>
                                        <th>Suppression</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" style="text-align:center; color:gray;">Aucun historique disponible</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onglet IMPORT DEPENSE -->
            <div id="import-depense" class="tab-panel">
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title"><h3>Importer un Fichier Depenses Realisees</h3></div>
                    </div>
                    <div class="panel-body">
                        <?php if(!empty($msg_depense)): ?>
                            <div class="alert-box <?php echo ($status_depense == 'success') ? 'alert-success' : 'alert-duplicate'; ?>">
                                <?php echo htmlspecialchars($msg_depense); ?>
                            </div>
                        <?php endif; ?>
                        
                        <p style="color: var(--texte-2); margin-bottom: 20px;">Veuillez choisir le fichier de depenses realisees</p>
                        <form id="form-import-depense" action="" method="POST" enctype="multipart/form-data" style="max-width: 500px;">
                            <div style="margin-bottom: 15px;">
                                <input type="file" id="file-input-depense" name="file_depense" accept=".csv, .xlsx" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                            </div>
                            <button type="submit" name="import_depense" class="btn-orange">Importer les depenses</button>
                        </form>

                        <!-- Historique des imports depenses -->
                        <div style="margin-top: 30px;">
                            <h4 style="margin-bottom: 10px; color: var(--marine);">Historique des imports depenses</h4>
                            <table class="mini-table" id="table-log-depense">
                                <thead>
                                    <tr>
                                        <th>Nom du Fichier</th>
                                        <th>Date d'importation</th>
                                        <th>Statut</th>
                                        <th>Suppression</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" style="text-align:center; color:gray;">Aucun historique disponible</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <p style="text-transform: uppercase; font-weight: bolder; color: var(--texte-3); margin-top: 20px;">Liste des depenses realisees</p>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>DEPARTEMENT</th>
                                <th>DESIGNATION</th>
                                <th>Montant</th>
                                <th>DATE DEPENSE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($select_realisees)): ?>
                                <?php foreach ($select_realisees as $select_realisee): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($select_realisee['nom_departement'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($select_realisee['rubrique'] ?? $select_realisee['designation'] ?? ''); ?></td>
                                        <td><?php echo number_format(floatval($select_realisee['montant'] ?? 0), 0, '.', ' '); ?> Ar</td>
                                        <td><?php echo htmlspecialchars($select_realisee['date_depense'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align:center; color:gray;">Aucune depense enregistree</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Onglet GESTION UTILISATEURS -->
            <div id="gestion-utilisateurs" class="tab-panel">
                <div class="panel">
                    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="panel-title"><h3>Liste des Utilisateurs de l'Application</h3></div>
                        <button class="btn btn-primary" onclick="ouvrirPopupAjout()" style="background-color: #198754; border: none; font-weight: 600;">
                            AJOUTER UN UTILISATEUR
                        </button>
                    </div>
                    <div class="panel-body">
                        <?php if(isset($msg_user_success)): ?>
                            <div class="alert-box alert-success"><?php echo htmlspecialchars($msg_user_success); ?></div>
                        <?php endif; ?>
                        
                        <p style="color: var(--texte-2); margin-bottom: 15px;">Voici la liste complete des agents administratifs autorises sur RegieBudget.</p>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom complet</th>
                                    <th>Adresse Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($select_utilisateurs)): ?>
                                    <tr><td colspan="4" style="text-align:center; color:gray;">Aucun utilisateur trouve.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($select_utilisateurs as $user): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($user['id_utilisateur']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($user['nom']); ?></td>
                                            <td style="color: var(--marine); font-weight: 500;"><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <button class="btn btn-primary"
                                                    style="background-color: blue; border:none; cursor: pointer;"
                                                    onclick="ouvrirPopupModification('<?php echo $user['id_utilisateur']; ?>', '<?php echo htmlspecialchars(addslashes($user['nom'])); ?>', '<?php echo htmlspecialchars(addslashes($user['email'])); ?>')">
                                                    Modifier
                                                </button>
                                                <button class="btn btn-primary"
                                                    style="background-color: red; border:none; cursor: pointer; color:white;"
                                                    onclick="declencherSuppression('<?php echo $user['id_utilisateur']; ?>', '<?php echo htmlspecialchars(addslashes($user['nom'])); ?>')">
                                                    Supprimer
                                                </button>
                                            </td>                                      
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Modals -->
<div id="user-add-modal" class="modal-user-custom">
    <div class="modal-user-content">
        <div class="modal-user-header">
            <h3>Ajouter un nouvel Utilisateur</h3>
            <span class="close-user-modal" onclick="fermerPopupAjout()">&times;</span>
        </div>
        <form action="../actions/serv_inscription.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Nom complet</label>
                <input type="text" name="nom" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Adresse Email</label>
                <input type="email" name="email" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Mot de passe</label>
                <input type="password" name="mot_de_passe" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="text-align: right;">
                <button type="button" onclick="fermerPopupAjout()" style="background-color: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-right: 5px;">Annuler</button>
                <button type="submit" name="ajouter_utilisateur" style="background-color: #198754; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<div id="user-edit-modal" class="modal-user-custom">
    <div class="modal-user-content">
        <div class="modal-user-header">
            <h3>Modifier l'Utilisateur</h3>
            <span class="close-user-modal" onclick="fermerPopupModification()">&times;</span>
        </div>
        <form action="../actions/update_utilisateurs.php" method="POST">
            <input type="hidden" id="modal-user-id" name="id_utilisateur">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Nom complet</label>
                <input type="text" id="modal-user-nom" name="nom" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Adresse Email</label>
                <input type="email" id="modal-user-email" name="email" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="text-align: right;">
                <button type="button" onclick="fermerPopupModification()" style="background-color: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-right: 5px;">Annuler</button>
                <button type="submit" name="modifier_utilisateur" style="background-color: blue; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Donnees PHP vers JavaScript
    var departementsStatsData = [
        { nom_departement: 'Etude', budget_alloue: <?php echo floatval($data_affichage['Etude']['budget'] ?? 0); ?>, total_depense: <?php echo floatval($data_affichage['Etude']['depense'] ?? 0); ?> },
        { nom_departement: 'Administration', budget_alloue: <?php echo floatval($data_affichage['Administration']['budget'] ?? 0); ?>, total_depense: <?php echo floatval($data_affichage['Administration']['depense'] ?? 0); ?> },
        { nom_departement: 'Logistique', budget_alloue: <?php echo floatval($data_affichage['Logistique']['budget'] ?? 0); ?>, total_depense: <?php echo floatval($data_affichage['Logistique']['depense'] ?? 0); ?> }
    ];
    var rawRealisedExpensesData = <?php echo json_encode($select_realisees ?? []); ?>;
    
    // Donnees par annee depuis la base de donnees
    var donneesParAnnee = <?php echo json_encode($donnees_par_annee); ?>;
    var anneeCourante = '<?php echo $annee_courante; ?>';

    // ============================================
    // FONCTIONS DE NAVIGATION
    // ============================================
    function showTab(tabId) {
        // Cacher tous les panneaux
        document.querySelectorAll('.tab-panel').forEach(function(panel) {
            panel.classList.remove('active');
        });
        // Afficher le panneau selectionne
        var panel = document.getElementById(tabId);
        if (panel) {
            panel.classList.add('active');
        }
        // Mettre a jour la classe active dans la sidebar
        document.querySelectorAll('.nav-item').forEach(function(item) {
            item.classList.remove('active');
            if (item.dataset.tab === tabId) {
                item.classList.add('active');
            }
        });
        // Mettre a jour le breadcrumb
        var breadcrumbMap = {
            'dashboard': 'Tableau de Bord',
            'import-budget': 'Import Budget',
            'import-depense': 'Import Depenses',
            'gestion-utilisateurs': 'Gestion Utilisateurs'
        };
        document.getElementById('breadcrumb-text').textContent = breadcrumbMap[tabId] || tabId;
    }

    // ============================================
    // FONCTIONS DE FILTRAGE
    // ============================================
    function changeAnnee(annee) {
        var url = new URL(window.location.href);
        url.searchParams.set('annee', annee);
        var dept = document.getElementById('main-dept-filter').value;
        if (dept && dept !== 'all') {
            url.searchParams.set('dept', dept);
        }
        window.location.href = url.toString();
    }

    function changeDepartement(dept) {
        var url = new URL(window.location.href);
        if (dept && dept !== 'all') {
            url.searchParams.set('dept', dept);
        } else {
            url.searchParams.delete('dept');
        }
        var annee = document.getElementById('annee-filter-dashboard').value;
        if (annee) {
            url.searchParams.set('annee', annee);
        }
        window.location.href = url.toString();
    }

    // ============================================
    // FONCTIONS DES MODALS
    // ============================================
    function ouvrirPopupAjout() {
        document.getElementById('user-add-modal').style.display = 'flex';
    }
    function fermerPopupAjout() {
        document.getElementById('user-add-modal').style.display = 'none';
    }

    function ouvrirPopupModification(id, nom, email) {
        document.getElementById('modal-user-id').value = id;
        document.getElementById('modal-user-nom').value = nom;
        document.getElementById('modal-user-email').value = email;
        document.getElementById('user-edit-modal').style.display = 'flex';
    }
    function fermerPopupModification() {
        document.getElementById('user-edit-modal').style.display = 'none';
    }

    function declencherSuppression(id, nom) {
        if (confirm('Voulez-vous vraiment supprimer l\'utilisateur "' + nom + '" ?')) {
            window.location.href = '../actions/delete_utilisateur.php?id=' + id;
        }
    }

    // ============================================
    // FONCTION PDF LINK
    // ============================================
    function updatePdfLink() {
        var dept = document.getElementById('main-dept-filter').value;
        var annee = document.getElementById('annee-filter-dashboard').value;
        document.getElementById('pdf-link').href = '../actions/generate_pdf.php?departement=' + dept + '&annee=' + annee;
    }

    // ============================================
    // FONCTION ENVOI EMAIL - CORRIGEE AVEC AJAX
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        var btnEmail = document.getElementById('btn-send-email');
        if (btnEmail) {
            btnEmail.addEventListener('click', function() {
                var email = document.getElementById('input-email-destinataire').value;
                if (!email) {
                    alert('Veuillez saisir une adresse email destinataire.');
                    return;
                }
                
                // Validation simple de l'email
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    alert('Veuillez saisir une adresse email valide.');
                    return;
                }
                
                var dept = document.getElementById('main-dept-filter').value;
                var annee = document.getElementById('annee-filter-dashboard').value;
                
                // Afficher un message de chargement
                btnEmail.textContent = 'ENVOI EN COURS...';
                btnEmail.disabled = true;
                btnEmail.style.opacity = '0.7';
                
                // Envoyer la requete AJAX vers send_report_email.php
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '../actions/send_report_email.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
                
                xhr.onload = function() {
                    btnEmail.textContent = 'ENVOYER PAR EMAIL';
                    btnEmail.disabled = false;
                    btnEmail.style.opacity = '1';
                    
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert(response.message);
                        } else {
                            alert('Erreur: ' + response.message);
                        }
                    } catch (e) {
                        alert('Erreur lors de l\'envoi: ' + xhr.responseText);
                    }
                };
                
                xhr.onerror = function() {
                    btnEmail.textContent = 'ENVOYER PAR EMAIL';
                    btnEmail.disabled = false;
                    btnEmail.style.opacity = '1';
                    alert('Erreur de connexion au serveur.');
                };
                
                var data = {
                    departement: dept,
                    email_destinataire: email,
                    annee: annee
                };
                
                xhr.send(JSON.stringify(data));
            });
        }

        // Initialiser le lien PDF
        updatePdfLink();

        // Gestion des onglets depuis l'URL
        var urlParams = new URLSearchParams(window.location.search);
        var tab = urlParams.get('tab');
        if (tab) {
            showTab(tab);
        }
    });

    // Fermer les modals en cliquant en dehors
    window.addEventListener('click', function(event) {
        var addModal = document.getElementById('user-add-modal');
        var editModal = document.getElementById('user-edit-modal');
        if (event.target === addModal) {
            addModal.style.display = 'none';
        }
        if (event.target === editModal) {
            editModal.style.display = 'none';
        }
    });
</script>
<script src="../assets/js/script.js"></script>
</body>
</html>