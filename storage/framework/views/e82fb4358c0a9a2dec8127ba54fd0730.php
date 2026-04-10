<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pointage</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: "Times New Roman", Times, serif;
    font-size: 9.5pt;
    color: #1a1a2e;
    background: #fff;
}

/* ── EN-TÊTE ─────────────────────────────────────────── */
.header {
    width: 95%;
    margin: 0 auto 14px auto;
    padding-bottom: 10px;
    border-bottom: 2.5px solid #1a1a2e;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}

.header-left h1 {
    font-size: 16pt;
    font-weight: bold;
    letter-spacing: 0.5px;
    color: #1a1a2e;
    margin-bottom: 3px;
}

.header-left .date {
    font-size: 10pt;
    color: #444;
    font-style: italic;
}

.header-left .filter {
    font-size: 8.5pt;
    color: #666;
    margin-top: 2px;
}

.header-right {
    text-align: right;
    font-size: 8pt;
    color: #888;
    line-height: 1.6;
}

/* ── TABLEAU ─────────────────────────────────────────── */
table {
    width: 95%;
    margin: 0 auto;
    border-collapse: collapse;
    table-layout: fixed;
}

/* Largeurs des colonnes */
col.col-nom       { width: 22%; }
col.col-dept      { width: 14%; }
col.col-entree    { width: 8%; }
col.col-sortie    { width: 8%; }
col.col-pause     { width: 9%; }
col.col-total     { width: 10%; }
col.col-statut    { width: 16%; }
col.col-valide    { width: 7%; }
col.col-heures    { width: 6%; }

thead tr {
    background: #1a1a2e;
    color: #ffffff;
}

th {
    font-size: 8.5pt;
    font-weight: bold;
    padding: 6px 5px;
    text-align: center;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

th:first-child { text-align: left; padding-left: 8px; }

/* Séparateur léger entre colonnes header */
th + th { border-left: 1px solid rgba(255,255,255,0.15); }

/* Lignes du corps */
tbody tr {
    height: 20px;
}

tbody tr:nth-child(even) {
    background: #f5f7fa;
}

tbody tr:nth-child(odd) {
    background: #ffffff;
}

tbody tr:hover {
    background: #eef2ff;
}

td {
    font-size: 9pt;
    padding: 4px 5px;
    border-bottom: 1px solid #dde2ec;
    text-align: center;
    vertical-align: middle;
    line-height: 1.3;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* Colonne nom à gauche */
td:first-child {
    text-align: left;
    font-weight: bold;
    padding-left: 8px;
    color: #1a1a2e;
}

/* Colonne département */
td:nth-child(2) {
    color: #555;
    font-style: italic;
}

/* Colonnes heures */
td.time {
    font-family: "Courier New", Courier, monospace;
    font-size: 8.5pt;
    color: #333;
    letter-spacing: 0.3px;
}

/* Total heures */
td.total {
    font-family: "Courier New", Courier, monospace;
    font-weight: bold;
    color: #1a6b55;
    font-size: 9pt;
}

/* Statuts */
td.status {
    font-size: 8pt;
    font-weight: bold;
    border-radius: 3px;
}

td.status.present  { color: #15803d; }
td.status.absent   { color: #dc2626; }
td.status.nobadge  { color: #92400e; }

/* Validé */
td.valide-ok  { color: #15803d; font-weight: bold; font-size: 11pt; }
td.valide-non { color: #aaa;    font-size: 9pt; }

/* Ligne de séparation finale du tableau */
tbody tr:last-child td {
    border-bottom: 2px solid #1a1a2e;
}

/* ── STATISTIQUES ────────────────────────────────────── */
.stats {
    width: 95%;
    margin: 16px auto 0 auto;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.stat {
    background: #f5f7fa;
    border: 1px solid #dde2ec;
    border-top: 3px solid #1a1a2e;
    padding: 8px 18px;
    text-align: center;
    min-width: 80px;
}

.stat strong {
    display: block;
    font-size: 14pt;
    font-weight: bold;
    color: #1a1a2e;
    line-height: 1.2;
}

.stat span {
    font-size: 7.5pt;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Stat présents */
.stat.presents { border-top-color: #15803d; }
.stat.presents strong { color: #15803d; }

/* Stat absents */
.stat.absents { border-top-color: #dc2626; }
.stat.absents strong { color: #dc2626; }

/* ── VIDE ────────────────────────────────────────────── */
.empty {
    width: 95%;
    margin: 30px auto;
    text-align: center;
    color: #888;
    font-style: italic;
    font-size: 10pt;
    padding: 20px;
    border: 1px dashed #ccc;
}

/* ── IMPRESSION ──────────────────────────────────────── */
@page {
    margin: 1.2cm 1cm;
}

@media print {
    tbody tr:nth-child(even) { -webkit-print-color-adjust: exact; print-color-adjust: exact; background: #f5f7fa !important; }
    thead tr { -webkit-print-color-adjust: exact; print-color-adjust: exact; background: #1a1a2e !important; color: #fff !important; }
}
</style>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <h1>Pointage Journalier</h1>
            <div class="date"><?php echo e($dateStr); ?></div>
            <?php if($filterInfo): ?>
            <div class="filter">Filtre : <?php echo e($filterInfo); ?></div>
            <?php endif; ?>
        </div>
        <div class="header-right">
            <div>Document genere le</div>
            <div><strong><?php echo e($generatedAt); ?></strong></div>
        </div>
    </div>

    <?php if($employees->isEmpty()): ?>
        <div class="empty">Aucun pointage trouve pour ces criteres.</div>
    <?php else: ?>

        <table>
            <colgroup>
                <col class="col-nom">
                <col class="col-dept">
                <col class="col-entree">
                <col class="col-sortie">
                <col class="col-pause">
                <col class="col-total">
                <col class="col-statut">
                <col class="col-valide">
            </colgroup>

            <thead>
                <tr>
                    <th>Employe</th>
                    <th>Departement</th>
                    <th>Entree</th>
                    <th>Sortie</th>
                    <th>Pause</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Valide</th>
                </tr>
            </thead>

            <tbody>
                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $p        = $emp['pointage'];
                        $statut   = $p?->statut ?? 'pas_de_badge';
                        $valide   = $p?->valide ?? false;
                        $isAbsent = in_array($statut, ['absent', 'absence']);
                        $isNoBadge = $statut === 'pas_de_badge' && !$p?->heure_entree;
                        $statusClass = $isAbsent ? 'absent' : ($isNoBadge ? 'nobadge' : 'present');
                        $statusLabel = $isAbsent ? 'Absent' : ($isNoBadge ? 'Pas de badge' : 'Present');
                    ?>

                    <tr>
                        <td><?php echo e($emp['nom']); ?></td>
                        <td><?php echo e($emp['department'] ?? '—'); ?></td>
                        <td class="time"><?php echo e($p?->heure_entree ? \Carbon\Carbon::parse($p->heure_entree)->format('H:i') : '—'); ?></td>
                        <td class="time"><?php echo e($p?->heure_sortie ? \Carbon\Carbon::parse($p->heure_sortie)->format('H:i') : '—'); ?></td>
                        <td class="time"><?php echo e($p?->pause_formatee ?? '—'); ?></td>
                        <td class="total"><?php echo e($p?->total_heures_formate ?? '—'); ?></td>
                        <td class="status <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></td>
                        <td class="<?php echo e($valide ? 'valide-ok' : 'valide-non'); ?>">
                            <?php echo e($valide ? 'Oui' : 'Non'); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <div class="stats">
            <div class="stat presents">
                <strong><?php echo e($stats['presents'] ?? 0); ?></strong>
                <span>Presents</span>
            </div>
            <div class="stat absents">
                <strong><?php echo e($stats['absents'] ?? 0); ?></strong>
                <span>Absents</span>
            </div>
            <div class="stat">
                <strong><?php echo e($stats['valides']); ?></strong>
                <span>Valides</span>
            </div>
            <div class="stat">
                <strong><?php echo e($stats['total']); ?></strong>
                <span>Total</span>
            </div>
        </div>

    <?php endif; ?>

</body>
</html><?php /**PATH C:\Users\HP\SI_RH\resources\views/pdf/pointage.blade.php ENDPATH**/ ?>