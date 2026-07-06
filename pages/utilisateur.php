<?php
session_start();
include "../config/db.php";
// Vérification de session
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'user' && $_SESSION['role'] !== 'admin')) {
    header("Location: index.php");
    exit();
}
// Vérification de l'existence de l'utilisateur
$stmt = $pdo->prepare("SELECT id_user FROM utilisateurs WHERE id_user = ?");
$stmt->execute([$_SESSION['id_utilisateurs']]);
$user_exists = $stmt->fetch();

if (!$user_exists) {
    session_unset();
    session_destroy();
    header("Location: index.php?error=account_deleted");
    exit();
}
// Inclusions
include "../actions/select_user_departements.php";
include "../actions/select_rapport_utilisateur.php";
include "../actions/select_import.php";

/* ---------- Helper alerte ---------- */
function getAlerte($taux) {
    if ($taux > 100) {
        return [
            'type' => 'type-rouge',
            'color' => '#dc2626',
            'msg' => 'Le budget alloué est dépassé. Une action corrective est nécessaire.'
        ];
    } elseif ($taux >= 75) {
        return [
            'type' => 'type-orange',
            'color' => '#f59e0b',
            'msg' => "Le seuil d'alerte est atteint. Une attention particulière est recommandée."
        ];
    }
    return [
        'type' => 'type-vert',
        'color' => '#10b981',
        'msg' => 'Le compte est en bon état. La consommation reste dans les limites normales.'
    ];
}

/* ---------- Données ---------- */
$budget_g = $results['budget_initial'] ?? 0;
$depense_g = $results['total_depense'] ?? 0;
$solde = $budget_g - $depense_g;
$taux_g = ($budget_g > 0) ? ($depense_g / $budget_g) * 100 : 0;

$color_g = '#10b981';
if ($taux_g >= 80 && $taux_g <= 100) $color_g = '#f59e0b';
elseif ($taux_g > 100) $color_g = '#dc2626';

// Comptage des alertes
$nb_alertes = 0;
foreach ($comptes as $c) {
    $t = ($c['budget'] > 0) ? ($c['total_depenses'] / $c['budget']) * 100 : 0;
    if ($t >= 75) $nb_alertes++;
}

// Nom de l'utilisateur
$username = htmlspecialchars($_SESSION['username'] ?? 'Utilisateur');
$user_initials = strtoupper(substr($username, 0, 2));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegieBudget - Espace Utilisateur</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800&family=IBM+Plex+Mono:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<!-- ==================== NAVBAR ==================== -->
<nav class="navbar">
    <div class="nav-inner">
        <a href="#" class="nav-brand">
            <div class="brand-icon">R</div>
            <div>
                <span class="brand-text">RegieBudget</span>
                <span class="brand-sub">Gestion budgetaire</span>
            </div>
        </a>

        <div class="nav-links">
            <button class="nav-btn active" onclick="navigate('accueil', this)">Tableau de bord</button>
            <button class="nav-btn" onclick="navigate('rapports', this)">Rapports</button>
            <button class="nav-btn" onclick="navigate('alertes', this)">Alertes</button>
        </div>

        <div class="nav-right">
            <div class="nav-alert" onclick="navigate('alertes', document.querySelectorAll('.nav-btn')[2])">
                <div class="alert-btn">🔔</div>
                <?php if ($nb_alertes > 0): ?>
                    <span class="alert-badge"><?= $nb_alertes ?></span>
                <?php endif; ?>
            </div>
            <div class="nav-user"><span><?= $username ?></span></div>
            <a href="#" class="nav-logout" onclick="if(confirm('Voulez-vous vraiment vous deconnecter ?')) { window.location.href='../actions/deconnexion_utilisateur.php'; } return false;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span>Deconnexion</span>
            </a>
        </div>
    </div>
</nav>
<!-- ==================== MAIN CONTENT ==================== -->
<main class="container">
    <!-- ===== PAGE ACCUEIL ===== -->
    <div id="page-accueil" class="page active">
        <div class="page-header">
            <div>
                <div class="page-title">Tableau de bord</div>
                <div class="page-sub">Vue d'ensemble budgetaire</div>
            </div>
            <?php 
              date_default_timezone_set('Indian/Antananarivo'); 
            ?>
            <div class="page-actions">
               <span id="heure-madagascar" style="font-family:var(--font-mono);font-size:11px;color:var(--text-3);padding:6px 14px;background:var(--surface-2);border-radius:var(--r-full);border:1px solid var(--border);">
                 <?= date('d/m/Y H:i:s') ?>
              </span>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card c-blue">
                <div class="stat-stripe"></div>
                <div class="stat-label">Budget alloue</div>
                <div class="stat-value"><?= number_format($budget_g, 0, '.', ' ') ?><span class="stat-unit">Ar</span></div>
                <div class="stat-desc">Montant total disponible</div>
            </div>
            <div class="stat-card c-red">
                <div class="stat-stripe"></div>
                <div class="stat-label">Depenses cumulees</div>
                <div class="stat-value"><?= number_format($depense_g, 0, '.', ' ') ?><span class="stat-unit">Ar</span></div>
                <div class="stat-desc">Total des depenses engagees</div>
            </div>
            <div class="stat-card c-green">
                <div class="stat-stripe"></div>
                <div class="stat-label">Solde disponible</div>
                <div class="stat-value"><?= number_format($solde, 0, '.', ' ') ?><span class="stat-unit">Ar</span></div>
                <div class="stat-desc">Budget restant a utiliser</div>
            </div>
            <div class="stat-card c-amber">
                <div class="stat-stripe"></div>
                <div class="stat-label">Comptes en alerte</div>
                <div class="stat-value" style="font-size:32px;"><?= $nb_alertes ?></div>
                <div class="stat-desc">Comptes depassant 75% d'utilisation</div>
            </div>
        </div>
        <!-- Taux execution -->
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Taux d'execution global</span>
                <span class="panel-meta"><?= round($taux_g, 1) ?>%</span>
            </div>
            <div class="panel-body">
                <div class="exec-bar-wrap">
                    <div class="exec-bar-header">
                        <span class="exec-bar-label">Execution budgetaire</span>
                        <span class="exec-bar-pct"><?= round($taux_g, 1) ?>%</span>
                    </div>
                    <div class="exec-bar-track">
                        <div class="exec-bar-fill" id="barGlobal"
                             style="width:0%; background:<?= $color_g ?>;"
                             data-taux="<?= round($taux_g, 1) ?>">
                        </div>
                    </div>
                </div>
                <div class="legend-group">
                    <div class="legend-item">
                        <span class="legend-dot green"></span>
                        Bon etat - moins de 80%
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot amber"></span>
                        Seuil d'alerte - 80 a 100%
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot red"></span>
                        Depassement - au-dela de 100%
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernieres depenses -->
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Dernieres depenses</span>
                <span class="panel-meta">5 dernieres</span>
            </div>
            <div class="panel-body">
                <?php if (empty($rapports)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">-</div>
                        <div class="empty-label">Aucune depense</div>
                        <div class="empty-sub">Aucune depense n'a encore ete enregistree.</div>
                    </div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Compte</th>
                                    <th>Montant</th>
                                    <th>Periode</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $i = 0;
                                foreach ($rapports as $rapport): 
                                    if ($i >= 5) break;
                                    $b = $rapport['budget_initial'];
                                    $d = $rapport['total_depense_tous'];
                              $t = ($b > 0) ? ($d / $b) * 100 : 0;
                              $statut = $t > 100 ? 'badge-red' : ($t >= 75 ? 'badge-amber' : 'badge-green');
                              $label = $t > 100 ? 'Depassement' : ($t >= 75 ? 'Alerte' : 'Normal');
                                ?>
                                    <tr>
                                        <td><span class="badge badge-blue"><?= htmlspecialchars($rapport['code_compte']) ?></span></td>
                                        <td class="num"><?= number_format($rapport['total_argent'], 0, '.', ' ') ?> Ar</td>
                                        <td><?= htmlspecialchars($rapport['mois']) ?></td>
                                        <td><span class="badge <?= $statut ?>"><?= $label ?></span></td>
                                    </tr>
                                <?php 
                                    $i++;
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===== PAGE RAPPORTS ===== -->
    <div id="page-rapports" class="page">
        <div class="page-header">
            <div>
                <div class="page-title">Rapports mensuels</div>
                <div class="page-sub">Compte rendu d'etat par compte budgetaire</div>
            </div>
            <div class="page-actions">
                <a href="../pdf/telechargement_pdf.php" class="btn-download">Telecharger PDF</a>
                <a href="../excel/excel_utilisateur.php" class="btn-download">Telecharger EXCEL</a>
                <a href="../pdf/utilisateur_connecter.php" target="_blank" class="btn btn-green">Exporter PDF</a>
            </div>
        </div>

        <!-- Filtre -->
        <div class="panel">
            <div class="filter-bar">
                <span class="filter-label">Filtrer par periode</span>
                <input type="month" class="field" id="filterMois" style="width:180px;">
                <button class="btn btn-blue" onclick="filtrerRapports()">Appliquer</button>
                <button class="btn btn-secondary" onclick="resetFiltre()">Reinitialiser</button>
            </div>
        </div>

        <!-- Table -->
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Etat des comptes</span>
                <span class="panel-meta"><?= count($rapports) ?> ligne(s)</span>
            </div>
            <?php if (empty($rapports)): ?>
                <div class="panel-body">
                    <div class="empty-state">
                        <div class="empty-icon">-</div>
                        <div class="empty-label">Aucun rapport disponible</div>
                        <div class="empty-sub">Les rapports budgetaires apparaissent ici apres l'import des depenses.</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="data-table" id="tableRapports">
                        <thead>
                            <tr>
                                <th>Compte</th>
                                <th>Budget initial</th>
                                <th>Dep. du mois</th>
                                <th>Periode</th>
                                <th>Total depense</th>
                                <th>Solde restant</th>
                                <th>Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rapports as $rapport):
                                $b = $rapport['budget_initial'];
                                $d = $rapport['total_depense_tous'];
                                $t = ($b > 0) ? ($d / $b) * 100 : 0;
                                $tc = $t > 100 ? 'badge-red' : ($t >= 75 ? 'badge-amber' : 'badge-green');
                            ?>
                                <tr>
                                    <td><span class="code-pill"><?= htmlspecialchars($rapport['code_compte']) ?></span></td>
                                    <td class="num"><?= number_format($b, 0, '.', ' ') ?> Ar</td>
                                    <td class="num"><?= number_format($rapport['total_argent'], 0, '.', ' ') ?> Ar</td>
                                    <td style="color:var(--text-3);"><?= htmlspecialchars($rapport['mois']) ?></td>
                                    <td class="num"><?= number_format($d, 0, '.', ' ') ?> Ar</td>
                                    <td class="num"><?= number_format($rapport['solde_restant'], 0, '.', ' ') ?> Ar</td>
                                    <td><span class="<?= $tc ?>"><?= round($t, 1) ?>%</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Journal imports -->
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Journal des importations</span>
                <span class="panel-meta"><?= count($resultImport) ?> entree(s)</span>
            </div>
            <div class="panel-body">
                <?php if (empty($resultImport)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">-</div>
                        <div class="empty-label">Aucune importation</div>
                        <div class="empty-sub">L'historique des fichiers importes apparaît ici.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($resultImport as $resImport): ?>
                        <div class="notice">
                            <div class="notice-dot"></div>
                            <div class="notice-text">
                                <strong><?= htmlspecialchars($resImport['nom_fichier']) ?></strong>
                                - importe avec succes le <?= date('d/m/Y', strtotime($resImport['date_import'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===== PAGE ALERTES ===== -->
    <div id="page-alertes" class="page">
        <div class="page-header">
            <div>
                <div class="page-title">Centre d'alertes</div>
                <div class="page-sub">Notifications de depassement et de seuil budgetaire</div>
            </div>
            <div class="page-actions">
                <span style="font-family:var(--font-mono);font-size:13px;font-weight:700;color:var(--amber);padding:6px 16px;background:var(--amber-dim);border-radius:var(--r-full);border:1px solid var(--amber-border);">
                    <?= $nb_alertes ?> alerte(s) active(s)
                </span>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Etat par compte</span>
                <span class="panel-meta"><?= count($comptes) ?> compte(s)</span>
            </div>
            <div class="panel-body">
                <?php if (empty($comptes)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">-</div>
                        <div class="empty-label">Aucune alerte</div>
                        <div class="empty-sub">Tous les comptes sont en bon etat ou aucune donnee n'est disponible.</div>
                    </div>
                <?php else: ?>
                    <div class="alert-list">
                        <?php foreach ($comptes as $compte):
                            $taux_c = ($compte['budget'] > 0) ? ($compte['total_depenses'] / $compte['budget']) * 100 : 0;
                            $taux_c = min($taux_c, 999);
                            $alerte = getAlerte($taux_c);
                            $periode = !empty($compte['date']) ? date('Y-m', strtotime($compte['date'])) : date('Y-m');
                        ?>
                            <div class="alert-item <?= $alerte['type'] ?>"
                                 style="border-left: 4px solid <?= $alerte['color'] ?>;">
                                <div class="alert-dot" style="background:<?= $alerte['color'] ?>;box-shadow:0 0 8px <?= $alerte['color'] ?>;"></div>
                                <div class="alert-body">
                                    <div class="alert-compte" style="color:<?= $alerte['color'] ?>;">
                                        <?= htmlspecialchars($compte['code']) ?>
                                    </div>
                                    <div class="alert-msg"><?= $alerte['msg'] ?></div>
                                    <div style="margin-top:6px;font-family:var(--font-mono);font-size:11px;color:var(--text-3);">
                                        Budget: <?= number_format($compte['budget'], 0, '.', ' ') ?> Ar |
                                        Depense: <?= number_format($compte['total_depenses'], 0, '.', ' ') ?> Ar |
                                        Taux: <?= round($taux_c, 1) ?>%
                                    </div>
                                </div>
                                <div class="alert-time" style="color:<?= $alerte['color'] ?>;"><?= $periode ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</main>

<!-- ==================== SCRIPTS ==================== -->
<script>
    // ==================== NAVIGATION ====================
    function navigate(pageId, btn) {
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        
        const target = document.getElementById('page-' + pageId);
        if (target) target.classList.add('active');
        if (btn) btn.classList.add('active');
        
        animateBars();
    }

    // ==================== FILTRAGE ====================
    function filtrerRapports() {
        const val = document.getElementById('filterMois').value;
        if (!val) return;
        const [yr, mo] = val.split('-');
        document.querySelectorAll('#tableRapports tbody tr').forEach(tr => {
            const periode = tr.cells[3]?.textContent.trim() || '';
            tr.style.display = periode.includes(yr + '-' + mo) ? '' : 'none';
        });
    }

    function resetFiltre() {
        document.getElementById('filterMois').value = '';
        document.querySelectorAll('#tableRapports tbody tr').forEach(tr => tr.style.display = '');
    }

    // ==================== ANIMATION DES BARRES ====================
    function animateBars() {
        document.querySelectorAll('.exec-bar-fill[data-taux]').forEach(el => {
            const taux = parseFloat(el.getAttribute('data-taux')) || 0;
            el.style.width = '0%';
            setTimeout(() => {
                el.style.width = Math.min(taux, 100) + '%';
            }, 100);
        });
    }

    // ==================== INITIALISATION ====================
    document.addEventListener('DOMContentLoaded', function() {
        animateBars();
        
        // Animation des cartes statistiques
        document.querySelectorAll('.stat-card').forEach((el, i) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(14px)';
            setTimeout(() => {
                el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, i * 80 + 200);
        });
    });
    // heure
    function mettreAJourChaqueSeconde() {
    let maintenant = new Date();
    let utc = maintenant.getTime() + (maintenant.getTimezoneOffset() * 60000);
    let heureMalgache = new Date(utc + (3600000 * 5));

    let jour = String(heureMalgache.getDate()).padStart(2, '0');
    let mois = String(heureMalgache.getMonth() + 1).padStart(2, '0');
    let annee = heureMalgache.getFullYear();
    
    let heure = String(heureMalgache.getHours()).padStart(2, '0');
    let minute = String(heureMalgache.getMinutes()).padStart(2, '0');
    let seconde = String(heureMalgache.getSeconds()).padStart(2, '0');
    let formatHeure = jour + '/' + mois + '/' + annee + ' ' + heure + ':' + minute + ':' + seconde;
    document.getElementById('heure-madagascar').textContent = formatHeure;
}
mettreAJourChaqueSeconde();
setInterval(mettreAJourChaqueSeconde, 1000);
</script>
</body>
</html>