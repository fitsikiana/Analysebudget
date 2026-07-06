<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Budget universitaire — Diagramme en barres</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: system-ui, -apple-system, sans-serif;
    background: #f5f4ef;
    color: #1a1a18;
    padding: 2rem 1rem;
    min-height: 100vh;
  }

  .page {
    max-width: 900px;
    margin: 0 auto;
  }

  /* ── En-tête ── */
  .header {
    margin-bottom: 1.5rem;
  }
  .header h1 {
    font-size: 20px;
    font-weight: 500;
    color: #1a1a18;
  }
  .header p {
    font-size: 13px;
    color: #73726c;
    margin-top: 3px;
  }
  .header-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 12px;
  }

  /* ── KPI strip ── */
  .kpi-strip {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 10px;
    margin-bottom: 1.5rem;
  }
  .kpi {
    background: #fff;
    border: 0.5px solid #d3d1c7;
    border-radius: 10px;
    padding: 12px 16px;
  }
  .kpi-label { font-size: 11px; color: #73726c; margin-bottom: 4px; }
  .kpi-value { font-size: 20px; font-weight: 500; color: #1a1a18; }
  .kpi-sub   { font-size: 11px; margin-top: 3px; }
  .up   { color: #3b6d11; }
  .down { color: #a32d2d; }
  .neu  { color: #73726c; }

  /* ── Carte principale ── */
  .card {
    background: #fff;
    border: 0.5px solid #d3d1c7;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.25rem;
  }
  .card-title {
    font-size: 14px;
    font-weight: 500;
    color: #1a1a18;
    margin-bottom: 1.25rem;
  }

  /* ── Légende ── */
  .legend {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
  }
  .leg-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #73726c;
  }
  .leg-dot {
    width: 10px;
    height: 10px;
    border-radius: 2px;
    flex-shrink: 0;
  }

  /* ── Diagramme en barres groupées ── */
  .chart-wrap {
    overflow-x: auto;
    padding-bottom: 4px;
  }
  .chart {
    display: flex;
    align-items: flex-end;
    gap: 0;
    min-width: 560px;
    height: 280px;
    border-bottom: 0.5px solid #d3d1c7;
    border-left: 0.5px solid #d3d1c7;
    position: relative;
    padding: 0 16px;
  }

  /* lignes de grille horizontales */
  .grid-lines {
    position: absolute;
    inset: 0;
    pointer-events: none;
  }
  .grid-line {
    position: absolute;
    left: 0; right: 0;
    border-top: 0.5px solid #f1efe8;
  }
  .grid-label {
    position: absolute;
    left: -58px;
    font-size: 10px;
    color: #b4b2a9;
    transform: translateY(50%);
    text-align: right;
    width: 52px;
  }

  .group {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
    position: relative;
  }
  .bars {
    display: flex;
    align-items: flex-end;
    gap: 4px;
    height: 100%;
    padding-top: 20px;
  }
  .bar-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    height: 100%;
  }
  .bar {
    width: 28px;
    border-radius: 3px 3px 0 0;
    transition: opacity 0.15s;
    cursor: default;
    position: relative;
  }
  .bar:hover { opacity: 0.82; }
  .bar-val {
    font-size: 9px;
    color: #73726c;
    margin-bottom: 2px;
    text-align: center;
    white-space: nowrap;
  }

  .bar-alloue  { background: #185fa5; }
  .bar-reel    { background: #0f6e56; }
  .bar-solde   { background: #b4b2a9; }

  .group-label {
    font-size: 11px;
    color: #73726c;
    text-align: center;
    margin-top: 6px;
    line-height: 1.3;
  }

  /* ── Séparateurs entre groupes ── */
  .sep {
    width: 1px;
    align-self: stretch;
    background: #f1efe8;
    flex-shrink: 0;
  }

  /* ── Tableau détaillé ── */
  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
  }
  th {
    text-align: left;
    font-weight: 500;
    color: #73726c;
    padding: 6px 10px;
    border-bottom: 0.5px solid #d3d1c7;
  }
  th.r, td.r { text-align: right; }
  td {
    padding: 7px 10px;
    border-bottom: 0.5px solid #f1efe8;
    color: #1a1a18;
  }
  tr:last-child td { border-bottom: none; }
  tfoot td {
    font-weight: 500;
    border-top: 0.5px solid #d3d1c7;
    border-bottom: none;
  }

  .bar-prog {
    display: inline-block;
    width: 52px;
    height: 5px;
    background: #f1efe8;
    border-radius: 3px;
    vertical-align: middle;
    margin-right: 5px;
  }
  .bar-prog-inner {
    height: 100%;
    border-radius: 3px;
  }

  .tag {
    display: inline-block;
    font-size: 10px;
    font-weight: 500;
    padding: 2px 7px;
    border-radius: 20px;
  }
  .tag-ok   { background: #eaf3de; color: #3b6d11; }
  .tag-warn { background: #faeeda; color: #633806; }
  .tag-err  { background: #fcebeb; color: #791f1f; }

  /* ── Footer ── */
  .foot {
    font-size: 11px;
    color: #b4b2a9;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 0.5rem;
  }
</style>
</head>
<body>

<?php
/* ═══════════════════════════════════════════════
   VARIABLES PHP — remplacez ces valeurs statiques
   ═══════════════════════════════════════════════ */
$universite   = "Université d'Antananarivo";
$exercice     = "2024";
$devise       = "Ar";
$date_maj     = "09/06/2024";

$budget_total     = 850000000;
$depenses_totales = 612000000;
$solde_total      = 238000000;
$taux_global      = 72;

/* Postes budgétaires
   Chaque ligne : poste, alloue, reel
   Le solde et le taux sont calculés automatiquement */
$postes = [
  ["Enseignement",    280000000, 241000000],
  ["Recherche",       150000000,  98000000],
  ["Administration",  120000000, 114000000],
  ["Infrastructure",  180000000,  96000000],
  ["Vie étudiante",    70000000,  43000000],
  ["Bibliothèque",     50000000,  20000000],
];

/* Valeur max pour l'échelle du graphique (auto) */
$max_val = 0;
foreach ($postes as $p) {
  if ($p[1] > $max_val) $max_val = $p[1];
}
$max_val = ceil($max_val / 50000000) * 50000000; // arrondi à 50M supérieur

/* Étape de grille */
$grid_steps = 5;
$step_val   = $max_val / $grid_steps;

/* Formatage compact (M = millions) */
function fmt($n) {
  if ($n >= 1000000) return round($n/1000000, 1).'M';
  if ($n >= 1000)    return round($n/1000).'k';
  return $n;
}
function pct($reel, $alloue) {
  return $alloue > 0 ? round($reel / $alloue * 100) : 0;
}
function tag($t) {
  if ($t > 100) return '<span class="tag tag-err">'.$t.'%</span>';
  if ($t >= 85) return '<span class="tag tag-ok">'.$t.'%</span>';
  return '<span class="tag tag-warn">'.$t.'%</span>';
}
?>

<div class="page">

  <!-- En-tête -->
  <div class="header">
    <div class="header-row">
      <div>
        <h1><?php echo $universite; ?> — Budget <?php echo $exercice; ?></h1>
        <p>Comparaison budget alloué / dépenses réelles par poste — <?php echo $devise; ?></p>
      </div>
      <div style="font-size:11px;color:#b4b2a9;">Mis à jour : <?php echo $date_maj; ?></div>
    </div>
  </div>

  <!-- KPI -->
  <div class="kpi-strip">
    <div class="kpi">
      <div class="kpi-label">Budget total alloué</div>
      <div class="kpi-value"><?php echo fmt($budget_total); ?> <span style="font-size:13px;font-weight:400;color:#73726c;"><?php echo $devise; ?></span></div>
      <div class="kpi-sub neu">Exercice <?php echo $exercice; ?></div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Dépenses réelles</div>
      <div class="kpi-value"><?php echo fmt($depenses_totales); ?> <span style="font-size:13px;font-weight:400;color:#73726c;"><?php echo $devise; ?></span></div>
      <div class="kpi-sub neu">Taux global : <?php echo $taux_global; ?>%</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Solde disponible</div>
      <div class="kpi-value"><?php echo fmt($solde_total); ?> <span style="font-size:13px;font-weight:400;color:#73726c;"><?php echo $devise; ?></span></div>
      <div class="kpi-sub up"><?php echo 100 - $taux_global; ?>% du budget restant</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Postes en sous-exécution</div>
      <?php
        $sous = 0;
        foreach ($postes as $p) if (pct($p[2],$p[1]) < 60) $sous++;
      ?>
      <div class="kpi-value"><?php echo $sous; ?></div>
      <div class="kpi-sub down">sous 60% de consommation</div>
    </div>
  </div>

  <!-- Diagramme en barres -->
  <div class="card">
    <div class="card-title">Comparaison par poste budgétaire</div>

    <div class="legend">
      <div class="leg-item"><span class="leg-dot" style="background:#185fa5;"></span> Budget alloué</div>
      <div class="leg-item"><span class="leg-dot" style="background:#0f6e56;"></span> Dépenses réelles</div>
      <div class="leg-item"><span class="leg-dot" style="background:#b4b2a9;"></span> Solde</div>
    </div>

    <div class="chart-wrap">
      <div style="position:relative;padding-left:60px;">

        <!-- Axe Y labels + grille -->
        <div class="grid-lines">
          <?php for ($i = 0; $i <= $grid_steps; $i++):
            $pct_pos = 100 - ($i / $grid_steps * 100);
            $val = $i * $step_val;
          ?>
          <div class="grid-line" style="bottom:<?php echo ($i/$grid_steps*100); ?>%;">
            <span class="grid-label"><?php echo fmt($val); ?></span>
          </div>
          <?php endfor; ?>
        </div>

        <div class="chart">
          <?php foreach ($postes as $idx => $p):
            $label  = $p[0];
            $alloue = $p[1];
            $reel   = $p[2];
            $solde  = $alloue - $reel;

            $h_alloue = round(($alloue / $max_val) * 240);
            $h_reel   = round(($reel   / $max_val) * 240);
            $h_solde  = round(($solde  / $max_val) * 240);
            if ($h_solde < 0) $h_solde = 0;
          ?>
          <?php if ($idx > 0): ?><div class="sep"></div><?php endif; ?>
          <div class="group">
            <div class="bars">
              <!-- Alloué -->
              <div class="bar-col">
                <div class="bar-val"><?php echo fmt($alloue); ?></div>
                <div class="bar bar-alloue" style="height:<?php echo $h_alloue; ?>px;" title="Alloué : <?php echo number_format($alloue,0,',',' '); ?> <?php echo $devise; ?>"></div>
              </div>
              <!-- Réel -->
              <div class="bar-col">
                <div class="bar-val"><?php echo fmt($reel); ?></div>
                <div class="bar bar-reel" style="height:<?php echo $h_reel; ?>px;" title="Réel : <?php echo number_format($reel,0,',',' '); ?> <?php echo $devise; ?>"></div>
              </div>
              <!-- Solde -->
              <div class="bar-col">
                <div class="bar-val"><?php echo fmt($solde); ?></div>
                <div class="bar bar-solde" style="height:<?php echo $h_solde; ?>px;" title="Solde : <?php echo number_format($solde,0,',',' '); ?> <?php echo $devise; ?>"></div>
              </div>
            </div>
            <div class="group-label"><?php echo $label; ?></div>
          </div>
          <?php endforeach; ?>
        </div>

      </div>
    </div>
  </div>

  <!-- Tableau détaillé -->
  <div class="card">
    <div class="card-title">Détail par poste</div>
    <table>
      <thead>
        <tr>
          <th>Poste</th>
          <th class="r">Alloué (<?php echo $devise; ?>)</th>
          <th class="r">Réel (<?php echo $devise; ?>)</th>
          <th class="r">Solde (<?php echo $devise; ?>)</th>
          <th class="r">Taux</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($postes as $p):
          $t = pct($p[2], $p[1]);
          $s = $p[1] - $p[2];
        ?>
        <tr>
          <td><?php echo $p[0]; ?></td>
          <td class="r"><?php echo number_format($p[1],0,',',' '); ?></td>
          <td class="r"><?php echo number_format($p[2],0,',',' '); ?></td>
          <td class="r" style="color:<?php echo $s >= 0 ? '#3b6d11' : '#a32d2d'; ?>;">
            <?php echo ($s >= 0 ? '+' : '') . number_format($s,0,',',' '); ?>
          </td>
          <td class="r">
            <span class="bar-prog"><span class="bar-prog-inner" style="width:<?php echo min($t,100); ?>%;background:<?php echo $t > 100 ? '#a32d2d' : ($t >= 85 ? '#3b6d11' : '#ba7517'); ?>;"></span></span>
            <?php echo tag($t); ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td>Total</td>
          <td class="r"><?php echo number_format($budget_total,0,',',' '); ?></td>
          <td class="r"><?php echo number_format($depenses_totales,0,',',' '); ?></td>
          <td class="r" style="color:#3b6d11;">+<?php echo number_format($solde_total,0,',',' '); ?></td>
          <td class="r"><?php echo tag($taux_global); ?></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="foot">
    <span>Système de gestion — <?php echo $universite; ?></span>
    <span>Exercice <?php echo $exercice; ?> — Valeurs en <?php echo $devise; ?></span>
  </div>

</div>
</body>
</html>