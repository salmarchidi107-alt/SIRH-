<?php $__env->startSection('title', 'Vue ensemble - Temps de travail'); ?>
<?php $__env->startSection('page-title', 'Vue ensemble du temps de travail'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ===================================================================
   VARIABLES & BASE
=================================================================== */
:root {
    --blue-50:   #eff6ff;
    --blue-100:  #dbeafe;
    --blue-200:  #bfdbfe;
    --blue-500:  #3b82f6;
    --blue-600:  #2563eb;
    --blue-700:  #1d4ed8;

    --teal-50:   #f0fdfa;
    --teal-100:  #ccfbf1;
    --teal-500:  #14b8a6;
    --teal-600:  #0d9488;
    --teal-700:  #0f766e;

    --green-50:  #f0fdf4;
    --green-100: #dcfce7;
    --green-500: #22c55e;
    --green-600: #16a34a;

    --amber-50:  #fffbeb;
    --amber-100: #fef3c7;
    --amber-500: #f59e0b;
    --amber-600: #d97706;

    --red-50:    #fef2f2;
    --red-100:   #fee2e2;
    --red-500:   #ef4444;
    --red-600:   #dc2626;

    --slate-50:  #f8fafc;
    --slate-100: #f1f5f9;
    --slate-200: #e2e8f0;
    --slate-300: #cbd5e1;
    --slate-400: #94a3b8;
    --slate-500: #64748b;
    --slate-600: #475569;
    --slate-700: #334155;
    --slate-800: #1e293b;
    --slate-900: #0f172a;

    --white:     #ffffff;
    --radius-sm: 6px;
    --radius:    10px;
    --radius-lg: 14px;
    --shadow-xs: 0 1px 2px rgba(0,0,0,.05);
    --shadow-sm: 0 1px 3px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.04);
    --shadow:    0 4px 6px -1px rgba(0,0,0,.07), 0 2px 4px -2px rgba(0,0,0,.05);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body,
.ov-wrap,
.ov-wrap * {
    font-family: Arial, Helvetica, sans-serif;
}

.ov-wrap {
    padding: 0 0 48px;
    color: var(--slate-800);
}

/* ===================================================================
   BARRE DE FILTRES
=================================================================== */
.filters-bar {
    background: var(--white);
    border: 1px solid var(--slate-200);
    border-radius: var(--radius);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 22px;
    box-shadow: var(--shadow-sm);
}

.filters-bar form {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    flex: 1;
}

.fb-select,
.fb-input {
    height: 36px;
    padding: 0 10px;
    border: 1px solid var(--slate-200);
    border-radius: var(--radius-sm);
    font-family: Arial, Helvetica, sans-serif;
    font-size: 13px;
    color: var(--slate-700);
    background: var(--slate-50);
    transition: border-color .15s, background .15s;
}

.fb-select:focus,
.fb-input:focus {
    outline: none;
    border-color: var(--teal-500);
    background: var(--white);
}

.fb-select { min-width: 200px; }
.fb-input-year { width: 76px; }

.btn-filter {
    height: 36px;
    padding: 0 16px;
    border: none;
    border-radius: var(--radius-sm);
    background: var(--teal-600);
    color: var(--white);
    font-family: Arial, Helvetica, sans-serif;
    font-size: 13px;
    font-weight: bold;
    cursor: pointer;
    transition: background .15s;
}

.btn-filter:hover { background: var(--teal-700); }

/* Navigation periode */
.period-nav {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-left: auto;
    position: relative;
}

.period-nav a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--slate-200);
    background: var(--slate-50);
    color: var(--slate-600);
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
    transition: all .15s;
}

.period-nav a:hover {
    background: var(--teal-600);
    border-color: var(--teal-600);
    color: var(--white);
}

.period-label {
    padding: 6px 14px;
    border-radius: var(--radius-sm);
    background: var(--teal-600);
    color: var(--white);
    font-size: 13px;
    font-weight: bold;
    white-space: nowrap;
}

/* ===================================================================
   CALENDRIER POPUP
=================================================================== */
.cal-popup-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--slate-200);
    background: var(--slate-50);
    color: var(--slate-600);
    cursor: pointer;
    font-size: 15px;
    transition: all .15s;
    position: relative;
    line-height: 1;
}

.cal-popup-btn:hover {
    background: var(--teal-600);
    border-color: var(--teal-600);
    color: var(--white);
}

.cal-popup-overlay {
    position: fixed;
    inset: 0;
    z-index: 998;
    display: none;
}

.cal-popup-overlay.open { display: block; }

.cal-popup {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 316px;
    background: var(--white);
    border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg);
    box-shadow: 0 10px 30px rgba(0,0,0,.14), 0 4px 10px rgba(0,0,0,.07);
    z-index: 1000;
    overflow: hidden;
}

.cal-popup-header {
    padding: 12px 14px 10px;
    background: linear-gradient(135deg, var(--teal-600), var(--blue-600));
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.cal-popup-title {
    font-size: 13px;
    font-weight: bold;
    text-transform: capitalize;
    margin-bottom: 6px;
}

.cal-popup-switcher {
    display: inline-flex;
    background: rgba(255,255,255,.18);
    border-radius: 5px;
    padding: 2px;
    gap: 2px;
}

.cal-popup-switcher button {
    padding: 3px 10px;
    border: none;
    border-radius: 4px;
    background: transparent;
    color: rgba(255,255,255,.85);
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    font-weight: bold;
    cursor: pointer;
    transition: all .12s;
    line-height: 1.6;
}

.cal-popup-switcher button.active {
    background: var(--white);
    color: var(--teal-700);
}

.cal-popup-nav {
    display: flex;
    align-items: center;
    gap: 4px;
    align-self: flex-start;
    margin-top: 2px;
}

.cal-popup-nav button {
    width: 27px;
    height: 27px;
    border: 1px solid rgba(255,255,255,.3);
    border-radius: var(--radius-sm);
    background: rgba(255,255,255,.15);
    color: var(--white);
    font-size: 13px;
    font-weight: bold;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background .12s;
    font-family: Arial, Helvetica, sans-serif;
    line-height: 1;
}

.cal-popup-nav button:hover { background: rgba(255,255,255,.3); }

.cal-popup-body { padding: 12px; }

/* Grille mensuelle */
.cal-popup-daynames {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    margin-bottom: 4px;
}

.cal-popup-dayname {
    text-align: center;
    font-size: 10px;
    font-weight: bold;
    color: var(--slate-400);
    text-transform: uppercase;
    padding: 3px 0;
    letter-spacing: .04em;
}

.cal-popup-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
}

.cal-popup-cell {
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    font-size: 12px;
    font-weight: bold;
    color: var(--slate-600);
    background: transparent;
    position: relative;
    transition: background .1s;
    line-height: 1;
}

.cal-popup-cell.cp-weekend {
    color: var(--slate-300);
}

.cal-popup-cell.cp-other-month {
    color: var(--slate-300);
}

.cal-popup-cell.cp-planifie {
    background: var(--teal-50);
    color: var(--teal-700);
    border: 1px solid var(--teal-100);
}

.cal-popup-cell.cp-planifie::after {
    content: '';
    position: absolute;
    bottom: 3px;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: var(--teal-500);
}

.cal-popup-cell.cp-today {
    background: var(--teal-600) !important;
    color: var(--white) !important;
    border: none !important;
}

.cal-popup-cell.cp-today::after {
    background: rgba(255,255,255,.7) !important;
}

/* Légende */
.cal-popup-legend {
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid var(--slate-100);
    display: flex;
    gap: 14px;
    font-size: 10px;
    color: var(--slate-400);
    flex-wrap: wrap;
}

.cal-popup-legend-item {
    display: flex;
    align-items: center;
    gap: 4px;
}

.cal-popup-legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

/* Vue hebdomadaire */
.cal-week-label {
    font-size: 10px;
    color: var(--slate-400);
    text-align: center;
    margin-bottom: 8px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.cal-week-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

.cal-week-cell {
    border-radius: var(--radius-sm);
    padding: 8px 3px;
    text-align: center;
    background: var(--slate-50);
    border: 1px solid var(--slate-100);
}

.cal-week-cell.cw-planifie {
    background: var(--teal-50);
    border-color: var(--teal-100);
}

.cal-week-cell.cw-today {
    background: var(--teal-600);
    border-color: var(--teal-600);
}

.cal-week-cell.cw-weekend {
    background: var(--slate-50);
    opacity: .55;
}

.cal-week-dayname {
    font-size: 9px;
    font-weight: bold;
    text-transform: uppercase;
    color: var(--slate-400);
    letter-spacing: .03em;
    margin-bottom: 4px;
    line-height: 1;
}

.cal-week-cell.cw-planifie .cal-week-dayname { color: var(--teal-600); }
.cal-week-cell.cw-today    .cal-week-dayname { color: rgba(255,255,255,.75); }

.cal-week-num {
    font-size: 15px;
    font-weight: bold;
    color: var(--slate-700);
    line-height: 1.3;
}

.cal-week-cell.cw-planifie .cal-week-num { color: var(--teal-700); }
.cal-week-cell.cw-today    .cal-week-num { color: var(--white); }
.cal-week-cell.cw-weekend  .cal-week-num { color: var(--slate-300); }

.cal-week-shift {
    font-size: 8px;
    color: var(--teal-600);
    margin-top: 4px;
    line-height: 1.4;
    word-break: break-all;
}

.cal-week-cell.cw-today .cal-week-shift { color: rgba(255,255,255,.85); }

/* ===================================================================
   BANDEAU EMPLOYE
=================================================================== */
.emp-banner {
    background: var(--white);
    border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg);
    padding: 18px 22px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
}

.emp-avatar {
    width: 52px;
    height: 52px;
    border-radius: var(--radius);
    background: linear-gradient(135deg, var(--teal-600), var(--blue-500));
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 18px;
    font-weight: bold;
    flex-shrink: 0;
    letter-spacing: 1px;
}

.emp-info { flex: 1; }
.emp-name { font-size: 16px; font-weight: bold; color: var(--slate-800); }
.emp-sub  { font-size: 12px; color: var(--slate-500); margin-top: 2px; }

.emp-tags {
    display: flex;
    gap: 6px;
    margin-top: 6px;
    flex-wrap: wrap;
}

.tag {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: bold;
}

.tag-teal   { background: var(--teal-50);  color: var(--teal-700);  border: 1px solid var(--teal-100); }
.tag-blue   { background: var(--blue-50);  color: var(--blue-700);  border: 1px solid var(--blue-100); }
.tag-amber  { background: var(--amber-50); color: var(--amber-600); border: 1px solid var(--amber-100); }

.emp-kpis {
    display: flex;
    gap: 24px;
    margin-left: auto;
}

.emp-kpi { text-align: center; }
.emp-kpi-val { font-size: 20px; font-weight: bold; color: var(--teal-600); }
.emp-kpi-lbl { font-size: 11px; color: var(--slate-500); margin-top: 2px; text-transform: uppercase; letter-spacing: .04em; }

/* ===================================================================
   KPI CARDS
=================================================================== */
.kpi-grid {
    display: grid;
    gap: 14px;
    margin-bottom: 22px;
}

.kpi-grid-4 { grid-template-columns: repeat(4, 1fr); }
.kpi-grid-5 { grid-template-columns: repeat(5, 1fr); }

.kpi-card {
    background: var(--white);
    border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg);
    padding: 16px 18px;
    box-shadow: var(--shadow-sm);
    border-top: 3px solid transparent;
    transition: box-shadow .15s;
}

.kpi-card:hover { box-shadow: var(--shadow); }

.kpi-card.ct { border-top-color: var(--teal-500); }
.kpi-card.cb { border-top-color: var(--blue-500); }
.kpi-card.ca { border-top-color: var(--amber-500); }
.kpi-card.cg { border-top-color: var(--green-500); }
.kpi-card.cr { border-top-color: var(--red-500); }
.kpi-card.cp { border-top-color: #8b5cf6; }

.kpi-label { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .05em; color: var(--slate-500); margin-bottom: 8px; }
.kpi-value { font-size: 28px; font-weight: bold; line-height: 1; }
.kpi-card.ct .kpi-value { color: var(--teal-600); }
.kpi-card.cb .kpi-value { color: var(--blue-600); }
.kpi-card.ca .kpi-value { color: var(--amber-600); }
.kpi-card.cg .kpi-value { color: var(--green-600); }
.kpi-card.cr .kpi-value { color: var(--red-600); }
.kpi-card.cp .kpi-value { color: #7c3aed; }
.kpi-sub { font-size: 12px; color: var(--slate-400); margin-top: 4px; }

.prog-bar {
    height: 4px;
    background: var(--slate-100);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 8px;
}
.prog-fill { height: 100%; border-radius: 2px; }
.prog-g { background: var(--green-500); }
.prog-a { background: var(--amber-500); }
.prog-r { background: var(--red-500); }

/* ===================================================================
   ONGLETS
=================================================================== */
.tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid var(--slate-200);
    margin-bottom: 20px;
}

.tab-btn {
    padding: 10px 20px;
    border: none;
    background: none;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 13px;
    font-weight: bold;
    color: var(--slate-500);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all .15s;
    white-space: nowrap;
}

.tab-btn:hover { color: var(--teal-600); }
.tab-btn.active { color: var(--teal-600); border-bottom-color: var(--teal-600); }

/* ===================================================================
   CARDS GENERIQUES
=================================================================== */
.card {
    background: var(--white);
    border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.card-header {
    padding: 13px 18px;
    border-bottom: 1px solid var(--slate-200);
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
    font-weight: bold;
    color: var(--slate-700);
    background: var(--slate-50);
}

.card-body { padding: 18px; }

/* ===================================================================
   LEGENDE GRAPHIQUE
=================================================================== */
.chart-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 12px;
    font-size: 12px;
    color: var(--slate-500);
    padding: 0 2px;
}

.chart-legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.chart-legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 2px;
    flex-shrink: 0;
}

.chart-legend-line {
    width: 18px;
    height: 3px;
    border-radius: 2px;
    flex-shrink: 0;
    position: relative;
}

.chart-legend-line::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: inherit;
}

.chart-calcs {
    margin-top: 12px;
    font-size: 12px;
    color: var(--slate-500);
    line-height: 1.9;
    padding: 10px 14px;
    background: var(--slate-50);
    border-radius: var(--radius-sm);
    border: 1px solid var(--slate-100);
}

.chart-calcs strong { color: var(--slate-700); }
.chart-calcs .cc-teal  { color: var(--teal-600); font-weight: bold; }
.chart-calcs .cc-amber { color: var(--amber-600); font-weight: bold; }
.chart-calcs .cc-green { color: var(--green-600); font-weight: bold; }
.chart-calcs .cc-red   { color: var(--red-600); font-weight: bold; }

/* ===================================================================
   SECTION MENSUELLE - CALENDRIER
=================================================================== */
.cal-grid-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    margin-bottom: 4px;
}

.cal-day-name {
    text-align: center;
    font-size: 11px;
    font-weight: bold;
    color: var(--slate-400);
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: 5px 0;
}

.cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

.cal-cell {
    border: 1px solid var(--slate-200);
    border-radius: var(--radius-sm);
    padding: 7px 8px;
    min-height: 80px;
    background: var(--white);
    position: relative;
    transition: border-color .12s;
    font-size: 12px;
}

.cal-cell:hover:not(.cal-empty):not(.cal-weekend) {
    border-color: var(--teal-500);
}

.cal-empty   { background: transparent; border-color: transparent; min-height: 80px; }
.cal-weekend { background: var(--slate-50); }
.cal-today   { border-color: var(--teal-500) !important; box-shadow: 0 0 0 2px var(--teal-100); }

.cal-num {
    font-size: 13px;
    font-weight: bold;
    color: var(--slate-700);
    line-height: 1;
}

.cal-today .cal-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: var(--teal-600);
    color: var(--white);
    font-size: 12px;
}

.cal-short-day { font-size: 10px; color: var(--slate-400); margin-bottom: 5px; }

.cal-hours {
    font-size: 13px;
    font-weight: bold;
    margin-top: 5px;
}

.cal-shift {
    font-size: 10px;
    color: var(--slate-400);
    margin-top: 3px;
}

.cal-ecart {
    font-size: 11px;
    font-weight: bold;
    margin-top: 2px;
}

.cal-dot {
    position: absolute;
    bottom: 6px;
    right: 7px;
    width: 7px;
    height: 7px;
    border-radius: 50%;
}

.color-present    { color: var(--teal-600); }
.color-absent     { color: var(--red-500); }
.color-planifie   { color: var(--blue-500); }
.color-non-plan   { color: var(--slate-400); }
.color-pos        { color: var(--green-600); }
.color-neg        { color: var(--red-600); }

.dot-present  { background: var(--teal-500); }
.dot-absent   { background: var(--red-500); }
.dot-planifie { background: var(--blue-500); }
.dot-weekend  { background: var(--slate-300); }
.dot-none     { background: var(--slate-200); }

.cal-legend {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 11px;
    color: var(--slate-500);
}

.cal-legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.cal-legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

/* ===================================================================
   TABLEAU JOURNALIER
=================================================================== */
.day-table-wrap { overflow-x: auto; margin-top: 16px; }

.day-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.day-table thead tr {
    background: var(--slate-50);
    border-bottom: 2px solid var(--slate-200);
}

.day-table th {
    padding: 10px 12px;
    text-align: left;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--slate-500);
    white-space: nowrap;
}

.day-table th:not(:first-child) { text-align: right; }

.day-table td {
    padding: 9px 12px;
    border-bottom: 1px solid var(--slate-100);
    vertical-align: middle;
    white-space: nowrap;
}

.day-table td:not(:first-child) { text-align: right; }

.day-table tbody tr:hover { background: var(--slate-50); }
.day-table tbody tr.row-weekend { background: var(--slate-50); color: var(--slate-400); }
.day-table tbody tr.row-today { background: var(--teal-50); }
.day-table tfoot tr { background: var(--slate-100); font-weight: bold; border-top: 2px solid var(--slate-200); }
.day-table tfoot td { padding: 10px 12px; }

.status-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: bold;
}

.sb-present  { background: var(--teal-50);   color: var(--teal-700);  border: 1px solid var(--teal-100); }
.sb-absent   { background: var(--red-50);    color: var(--red-600);   border: 1px solid var(--red-100); }
.sb-planifie { background: var(--blue-50);   color: var(--blue-700);  border: 1px solid var(--blue-100); }
.sb-weekend  { background: var(--slate-100); color: var(--slate-500); border: 1px solid var(--slate-200); }
.sb-none     { background: var(--slate-50);  color: var(--slate-400); border: 1px solid var(--slate-200); }

/* ===================================================================
   SECTION HEBDOMADAIRE
=================================================================== */
.weeks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}

.week-card {
    background: var(--white);
    border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .15s;
}

.week-card:hover { box-shadow: var(--shadow); }

.week-head {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: var(--white);
}

.week-head.wh-neutral  { background: var(--teal-600); }
.week-head.wh-positive { background: var(--green-600); }
.week-head.wh-warning  { background: var(--amber-500); }
.week-head.wh-negative { background: var(--red-500); }

.wk-title  { font-size: 13px; font-weight: bold; }
.wk-period { font-size: 11px; opacity: .85; margin-top: 1px; }

.wk-solde {
    font-size: 16px;
    font-weight: bold;
    background: rgba(255,255,255,.2);
    padding: 3px 10px;
    border-radius: var(--radius-sm);
}

.week-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    border-bottom: 1px solid var(--slate-100);
}

.wk-stat {
    padding: 12px 10px;
    text-align: center;
    border-right: 1px solid var(--slate-100);
}

.wk-stat:last-child { border-right: none; }
.wk-stat-val { font-size: 16px; font-weight: bold; color: var(--slate-700); }
.wk-stat-val.ct { color: var(--teal-600); }
.wk-stat-val.ca { color: var(--amber-600); }
.wk-stat-lbl { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em; color: var(--slate-400); margin-top: 3px; }

.week-footer {
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
}

.week-prog-wrap { display: flex; align-items: center; gap: 8px; flex: 1; }
.week-prog-track { flex: 1; height: 5px; background: var(--slate-100); border-radius: 3px; overflow: hidden; }
.week-prog-fill { height: 100%; border-radius: 3px; }

/* ===================================================================
   TABLEAU RECAP SEMAINES
=================================================================== */
.recap-table-wrap { overflow-x: auto; }

.recap-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.recap-table thead tr { background: var(--slate-50); border-bottom: 2px solid var(--slate-200); }
.recap-table th {
    padding: 10px 14px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--slate-500);
    text-align: right;
}
.recap-table th:first-child { text-align: left; }
.recap-table td { padding: 10px 14px; border-bottom: 1px solid var(--slate-100); text-align: right; }
.recap-table td:first-child { text-align: left; font-weight: bold; }
.recap-table tbody tr:hover { background: var(--slate-50); }
.recap-table tfoot tr { background: var(--slate-100); font-weight: bold; border-top: 2px solid var(--slate-200); }
.recap-table tfoot td { padding: 10px 14px; }

/* ===================================================================
   TABLEAU EMPLOYES (MODE DEPT)
=================================================================== */
.emp-table-wrap { overflow-x: auto; }

.emp-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.emp-table thead tr { background: var(--slate-50); border-bottom: 2px solid var(--slate-200); }
.emp-table th {
    padding: 10px 14px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--slate-500);
    text-align: center;
    white-space: nowrap;
}
.emp-table th:first-child { text-align: left; }
.emp-table td { padding: 11px 14px; border-bottom: 1px solid var(--slate-100); text-align: center; vertical-align: middle; }
.emp-table td:first-child { text-align: left; }
.emp-table tbody tr:hover { background: var(--slate-50); }
.emp-table tfoot tr { background: var(--slate-100); font-weight: bold; border-top: 2px solid var(--slate-200); }
.emp-table tfoot td { padding: 11px 14px; text-align: center; }
.emp-table tfoot td:first-child { text-align: left; }

.mini-avatar {
    width: 30px;
    height: 30px;
    border-radius: 6px;
    background: linear-gradient(135deg, var(--teal-600), var(--blue-500));
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: bold;
    color: var(--white);
    margin-right: 8px;
    flex-shrink: 0;
    vertical-align: middle;
}

.taux-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
}

.taux-track {
    width: 60px;
    height: 5px;
    background: var(--slate-200);
    border-radius: 3px;
    overflow: hidden;
    flex-shrink: 0;
}

.taux-fill { height: 100%; border-radius: 3px; }

.link-detail {
    font-size: 12px;
    font-weight: bold;
    padding: 4px 10px;
    border-radius: var(--radius-sm);
    background: var(--teal-50);
    color: var(--teal-700);
    text-decoration: none;
    border: 1px solid var(--teal-100);
    white-space: nowrap;
    transition: all .12s;
}
.link-detail:hover { background: var(--teal-600); color: var(--white); border-color: var(--teal-600); }

/* ===================================================================
   BANDEAU DEPARTEMENT
=================================================================== */
.dept-banner {
    border-radius: var(--radius-lg);
    padding: 18px 22px;
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
    background: linear-gradient(135deg, var(--teal-600), var(--blue-600));
    color: var(--white);
    box-shadow: var(--shadow);
}

.dept-icon-box {
    width: 46px;
    height: 46px;
    background: rgba(255,255,255,.2);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.dept-title { font-size: 17px; font-weight: bold; }
.dept-sub   { font-size: 12px; opacity: .85; margin-top: 3px; }

/* ===================================================================
   TABLE RECAP MENSUEL
=================================================================== */
.month-recap-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.month-recap-table td { padding: 11px 14px; border-bottom: 1px solid var(--slate-100); }
.month-recap-table td:last-child { text-align: right; font-weight: bold; }
.month-recap-table tr:last-child td { border-bottom: none; }
.month-recap-table tr.row-total { background: var(--slate-50); font-weight: bold; border-top: 2px solid var(--slate-200); }

/* ===================================================================
   ETAT VIDE
=================================================================== */
.empty-state {
    background: var(--white);
    border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg);
    padding: 60px 24px;
    text-align: center;
    box-shadow: var(--shadow-sm);
}

.empty-title { font-size: 16px; font-weight: bold; color: var(--slate-700); margin-bottom: 8px; }
.empty-sub   { font-size: 13px; color: var(--slate-400); }

/* ===================================================================
   UTILITAIRES
=================================================================== */
.text-teal  { color: var(--teal-600) !important; }
.text-blue  { color: var(--blue-600) !important; }
.text-amber { color: var(--amber-600) !important; }
.text-green { color: var(--green-600) !important; }
.text-red   { color: var(--red-600) !important; }
.text-muted { color: var(--slate-500) !important; }
.fw-bold    { font-weight: bold; }

.grid-2-1 { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; align-items: start; }
.section-title {
    font-size: 14px;
    font-weight: bold;
    color: var(--slate-700);
    margin: 24px 0 12px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--slate-200);
}

/* ===================================================================
   RESPONSIVE
=================================================================== */
@media (max-width: 1100px) {
    .kpi-grid-4, .kpi-grid-5 { grid-template-columns: repeat(2, 1fr); }
    .grid-2-1 { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    .emp-kpis { display: none; }
    .weeks-grid { grid-template-columns: 1fr 1fr; }
    .cal-cell { min-height: 60px; }
    .cal-hours, .cal-shift, .cal-ecart { display: none; }
    .cal-popup { width: 290px; right: -60px; }
}

@media (max-width: 480px) {
    .weeks-grid { grid-template-columns: 1fr; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="ov-wrap">


<div class="filters-bar">
    <form method="GET" action="<?php echo e(route('temps.vue-ensemble')); ?>">

        <select name="employee_id" class="fb-select" onchange="this.form.submit()">
            <option value="">Selectionner un employe</option>
            <?php $__currentLoopData = $listeEmployesSelect; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($emp->id); ?>" <?php echo e(($employeeId ?? '') == $emp->id ? 'selected' : ''); ?>>
                    <?php echo e($emp->first_name); ?> <?php echo e($emp->last_name); ?><?php echo e($emp->matricule ? ' — '.$emp->matricule : ''); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <select name="department" class="fb-select" style="min-width:160px;" onchange="this.form.submit()">
            <option value="">Tous les departements</option>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($dept); ?>" <?php echo e(($department ?? '') == $dept ? 'selected' : ''); ?>><?php echo e($dept); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <input type="number" name="annee" value="<?php echo e($annee); ?>" min="2020" max="2030" class="fb-input fb-input-year">
        <input type="hidden" name="mois" value="<?php echo e($mois); ?>">
        <button type="submit" class="btn-filter">Rechercher</button>
    </form>

    
    <div class="period-nav">
        <a href="<?php echo e(route('temps.vue-ensemble', ['mois' => $moisPrecedent->month, 'annee' => $moisPrecedent->year, 'employee_id' => $employeeId ?? '', 'department' => $department ?? ''])); ?>">&larr;</a>
        <span class="period-label"><?php echo e(\Carbon\Carbon::create($annee, $mois, 1)->locale('fr')->translatedFormat('F Y')); ?></span>
        <a href="<?php echo e(route('temps.vue-ensemble', ['mois' => $moisSuivant->month, 'annee' => $moisSuivant->year, 'employee_id' => $employeeId ?? '', 'department' => $department ?? ''])); ?>">&rarr;</a>

        
        <button class="cal-popup-btn" id="calPopupBtn" title="Voir les jours planifies">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.4" fill="none"/>
                <path d="M1 6h14" stroke="currentColor" stroke-width="1.4"/>
                <path d="M5 1v3M11 1v3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                <rect x="4" y="8.5" width="2" height="2" rx=".4" fill="currentColor"/>
                <rect x="7" y="8.5" width="2" height="2" rx=".4" fill="currentColor"/>
                <rect x="10" y="8.5" width="2" height="2" rx=".4" fill="currentColor"/>
                <rect x="4" y="11.5" width="2" height="2" rx=".4" fill="currentColor"/>
                <rect x="7" y="11.5" width="2" height="2" rx=".4" fill="currentColor"/>
            </svg>
        </button>

        
        <div class="cal-popup-overlay" id="calPopupOverlay"></div>

        
        <div class="cal-popup" id="calPopup" style="display:none;">
            <div class="cal-popup-header">
                <div>
                    <div class="cal-popup-title" id="calPopupTitle">Calendrier</div>
                    <div class="cal-popup-switcher">
                        <button class="active" id="btnMois" onclick="switchCalView('mois')">Mois</button>
                        <button id="btnSemaine" onclick="switchCalView('semaine')">Semaine</button>
                    </div>
                </div>
                <div class="cal-popup-nav">
                    <button onclick="calNavPrev()" title="Precedent">&#8592;</button>
                    <button onclick="calNavNext()" title="Suivant">&#8594;</button>
                </div>
            </div>
            <div class="cal-popup-body" id="calPopupBody">
                <div style="text-align:center;padding:20px;color:var(--slate-400);font-size:13px;">Chargement...</div>
            </div>
        </div>
    </div>
</div>



<?php if($modeDepartement && $statsGlobalesDept): ?>

<div class="dept-banner">
    <div class="dept-icon-box">D</div>
    <div>
        <div class="dept-title">Departement : <?php echo e($nomDepartement); ?></div>
        <div class="dept-sub">
            <?php echo e($statsGlobalesDept->nb_employes); ?> employe<?php echo e($statsGlobalesDept->nb_employes > 1 ? 's' : ''); ?>

            &middot; <?php echo e(\Carbon\Carbon::create($annee, $mois, 1)->locale('fr')->translatedFormat('F Y')); ?>

            &middot; Pause dejeuner 1h deduite des heures planifiees
        </div>
    </div>
</div>

<div class="kpi-grid kpi-grid-5">
    <div class="kpi-card cp">
        <div class="kpi-label">Employes</div>
        <div class="kpi-value"><?php echo e($statsGlobalesDept->nb_employes); ?></div>
    </div>
    <div class="kpi-card cb">
        <div class="kpi-label">Heures planifiees</div>
        <div class="kpi-value"><?php echo e(number_format($statsGlobalesDept->heures_planifiees, 1)); ?>h</div>
        <div class="kpi-sub">Pause 1h/j deduite</div>
    </div>
    <div class="kpi-card ct">
        <div class="kpi-label">Heures realisees</div>
        <div class="kpi-value"><?php echo e(number_format($statsGlobalesDept->heures_realisees, 1)); ?>h</div>
        <?php $t = $statsGlobalesDept->taux_realisation; ?>
        <div class="prog-bar"><div class="prog-fill <?php echo e($t >= 90 ? 'prog-g' : ($t >= 70 ? 'prog-a' : 'prog-r')); ?>" style="width:<?php echo e(min($t,100)); ?>%"></div></div>
        <div class="kpi-sub"><?php echo e($t); ?>% du planning</div>
    </div>
    <div class="kpi-card ca">
        <div class="kpi-label">Heures supp.</div>
        <div class="kpi-value"><?php echo e(number_format($statsGlobalesDept->heures_supplementaires, 1)); ?>h</div>
    </div>
    <div class="kpi-card <?php echo e($statsGlobalesDept->ecart >= 0 ? 'cg' : 'cr'); ?>">
        <div class="kpi-label">Ecart global</div>
        <div class="kpi-value"><?php echo e($statsGlobalesDept->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($statsGlobalesDept->ecart, 1)); ?>h</div>
    </div>
</div>

<div class="tabs">
    <button class="tab-btn active" onclick="showTab(event,'dept-mensuel')">Vue mensuelle</button>
    <button class="tab-btn" onclick="showTab(event,'dept-semaines')">Par semaine</button>
    <button class="tab-btn" onclick="showTab(event,'dept-employes')">Detail par employe</button>
</div>

<div id="dept-mensuel" class="tab-panel">
    <div class="grid-2-1">
        <div class="card">
            <div class="card-header">Evolution annuelle <?php echo e($annee); ?> — <?php echo e($nomDepartement); ?></div>
            <div class="card-body">
                <div class="chart-legend">
                    <div class="chart-legend-item">
                        <div class="chart-legend-dot" style="background:#e2e8f0;"></div>
                        <span>Planifiees</span>
                    </div>
                    <div class="chart-legend-item">
                        <div class="chart-legend-dot" style="background:#0d9488;"></div>
                        <span>Realisees</span>
                    </div>
                    <div class="chart-legend-item">
                        <div class="chart-legend-line" style="background:#d97706;"></div>
                        <span>Supp.</span>
                    </div>
                </div>
                <div style="position:relative;width:100%;height:260px;">
                    <canvas id="chartDept" role="img" aria-label="Evolution annuelle des heures du departement <?php echo e($nomDepartement); ?> pour <?php echo e($annee); ?>">
                        Graphique des heures planifiees, realisees et supplementaires par mois.
                    </canvas>
                </div>
                <div id="chartDeptCalcs" class="chart-calcs" style="display:none;"></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Recapitulatif du mois</div>
            <table class="month-recap-table">
                <tr><td>Heures planifiees</td><td><?php echo e(number_format($statsGlobalesDept->heures_planifiees, 1)); ?>h</td></tr>
                <tr><td>Heures realisees</td><td class="text-teal"><?php echo e(number_format($statsGlobalesDept->heures_realisees, 1)); ?>h</td></tr>
                <tr><td>Heures supplementaires</td><td class="text-amber"><?php echo e(number_format($statsGlobalesDept->heures_supplementaires, 1)); ?>h</td></tr>
                <tr><td>Taux de realisation</td><td><?php echo e($statsGlobalesDept->taux_realisation); ?>%</td></tr>
                <tr class="row-total">
                    <td>Ecart</td>
                    <td class="<?php echo e($statsGlobalesDept->ecart >= 0 ? 'text-green' : 'text-red'); ?>">
                        <?php echo e($statsGlobalesDept->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($statsGlobalesDept->ecart, 1)); ?>h
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div id="dept-semaines" class="tab-panel" style="display:none">
    <div class="weeks-grid">
        <?php $__currentLoopData = $semainerDept; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $solde = $sem['solde'];
            $taux  = $sem['heures_planifiees'] > 0 ? round(($sem['heures_realisees'] / $sem['heures_planifiees']) * 100) : 0;
            $headClass = $solde > 0 ? 'wh-positive' : ($solde == 0 ? 'wh-neutral' : 'wh-warning');
        ?>
        <div class="week-card">
            <div class="week-head <?php echo e($headClass); ?>">
                <div>
                    <div class="wk-title">Semaine <?php echo e($sem['numero']); ?></div>
                    <div class="wk-period"><?php echo e($sem['debut']); ?> — <?php echo e($sem['fin']); ?></div>
                </div>
                <div class="wk-solde"><?php echo e($solde >= 0 ? '+' : ''); ?><?php echo e(number_format($solde, 1)); ?>h</div>
            </div>
            <div class="week-stats">
                <div class="wk-stat">
                    <div class="wk-stat-val"><?php echo e(number_format($sem['heures_planifiees'], 1)); ?>h</div>
                    <div class="wk-stat-lbl">Planifiees</div>
                </div>
                <div class="wk-stat">
                    <div class="wk-stat-val ct"><?php echo e(number_format($sem['heures_realisees'], 1)); ?>h</div>
                    <div class="wk-stat-lbl">Realisees</div>
                </div>
                <div class="wk-stat">
                    <div class="wk-stat-val ca"><?php echo e(number_format($sem['heures_supplementaires'], 1)); ?>h</div>
                    <div class="wk-stat-lbl">Supp.</div>
                </div>
            </div>
            <div class="week-footer">
                <div class="week-prog-wrap">
                    <div class="week-prog-track">
                        <div class="week-prog-fill <?php echo e($taux >= 90 ? 'prog-g' : ($taux >= 70 ? 'prog-a' : 'prog-r')); ?>" style="width:<?php echo e(min($taux,100)); ?>%"></div>
                    </div>
                    <span style="font-size:12px;font-weight:bold;color:var(--slate-500)"><?php echo e($taux); ?>%</span>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<div id="dept-employes" class="tab-panel" style="display:none">
    <div class="card">
        <div class="card-header">
            <span>Detail par employe — <?php echo e($nomDepartement); ?></span>
            <span style="font-size:12px;font-weight:normal;color:var(--slate-500)"><?php echo e($statsGlobalesDept->nb_employes); ?> employe(s)</span>
        </div>
        <div class="emp-table-wrap">
            <table class="emp-table">
                <thead>
                    <tr>
                        <th style="text-align:left">Employe</th>
                        <th>Poste</th>
                        <th>Planifiees</th>
                        <th>Realisees</th>
                        <th>Supp.</th>
                        <th>Ecart</th>
                        <th>Taux</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $employesDept; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $t = $emp['taux']; ?>
                    <tr>
                        <td>
                            <span class="mini-avatar"><?php echo e($emp['initiales']); ?></span>
                            <span class="fw-bold"><?php echo e($emp['nom']); ?></span>
                        </td>
                        <td style="color:var(--slate-500)"><?php echo e($emp['poste']); ?></td>
                        <td><?php echo e(number_format($emp['planifiees'], 1)); ?>h</td>
                        <td class="text-teal fw-bold"><?php echo e(number_format($emp['realisees'], 1)); ?>h</td>
                        <td class="text-amber"><?php echo e(number_format($emp['supp'], 1)); ?>h</td>
                        <td class="<?php echo e($emp['ecart'] >= 0 ? 'text-green' : 'text-red'); ?> fw-bold">
                            <?php echo e($emp['ecart'] >= 0 ? '+' : ''); ?><?php echo e(number_format($emp['ecart'], 1)); ?>h
                        </td>
                        <td>
                            <div class="taux-cell">
                                <div class="taux-track">
                                    <div class="taux-fill <?php echo e($t >= 90 ? 'prog-g' : ($t >= 70 ? 'prog-a' : 'prog-r')); ?>" style="width:<?php echo e(min($t,100)); ?>%"></div>
                                </div>
                                <span style="font-size:12px;font-weight:bold;"><?php echo e($t); ?>%</span>
                            </div>
                        </td>
                        <td>
                            <a href="<?php echo e(route('temps.vue-ensemble', ['employee_id' => $emp['id'], 'annee' => $annee, 'mois' => $mois])); ?>" class="link-detail">Voir detail</a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:left">Total departement</td>
                        <td><?php echo e(number_format($statsGlobalesDept->heures_planifiees, 1)); ?>h</td>
                        <td class="text-teal"><?php echo e(number_format($statsGlobalesDept->heures_realisees, 1)); ?>h</td>
                        <td class="text-amber"><?php echo e(number_format($statsGlobalesDept->heures_supplementaires, 1)); ?>h</td>
                        <td class="<?php echo e($statsGlobalesDept->ecart >= 0 ? 'text-green' : 'text-red'); ?>">
                            <?php echo e($statsGlobalesDept->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($statsGlobalesDept->ecart, 1)); ?>h
                        </td>
                        <td colspan="2"><?php echo e($statsGlobalesDept->taux_realisation); ?>%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>



<?php elseif(!$modeDepartement && $employee && $employee->id): ?>

<div class="emp-banner">
    <div class="emp-avatar">
        <?php echo e(strtoupper(substr($employee->first_name ?? 'U', 0, 1))); ?><?php echo e(strtoupper(substr($employee->last_name ?? '', 0, 1))); ?>

    </div>
    <div class="emp-info">
        <div class="emp-name"><?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?></div>
        <div class="emp-sub"><?php echo e($employee->position ?? 'Employe'); ?></div>
        <div class="emp-tags">
            <span class="tag tag-teal"><?php echo e($employee->department ?? 'Service'); ?></span>
            <span class="tag tag-blue"><?php echo e($employee->contract_type ?? 'CDI'); ?></span>
            <span class="tag tag-blue"><?php echo e($employee->work_hours ?? 35); ?>h / semaine</span>
            <span class="tag tag-amber">Pause dejeuner 1h / jour deduite du planning</span>
        </div>
    </div>
    <?php if($compteurMois): ?>
    <div class="emp-kpis">
        <div class="emp-kpi">
            <div class="emp-kpi-val"><?php echo e(number_format($compteurMois->heures_planifiees, 0)); ?>h</div>
            <div class="emp-kpi-lbl">Planifiees</div>
        </div>
        <div class="emp-kpi">
            <div class="emp-kpi-val"><?php echo e($compteurMois->jours_travailles); ?></div>
            <div class="emp-kpi-lbl">Jours</div>
        </div>
        <div class="emp-kpi">
            <div class="emp-kpi-val" style="color:<?php echo e($compteurMois->taux_realisation >= 90 ? 'var(--green-600)' : ($compteurMois->taux_realisation >= 70 ? 'var(--amber-600)' : 'var(--red-600)')); ?>">
                <?php echo e($compteurMois->taux_realisation); ?>%
            </div>
            <div class="emp-kpi-lbl">Taux</div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if($compteurMois): ?>
<div class="kpi-grid kpi-grid-4">
    <div class="kpi-card cb">
        <div class="kpi-label">Heures planifiees</div>
        <div class="kpi-value"><?php echo e(number_format($compteurMois->heures_planifiees, 1)); ?>h</div>
        <div class="kpi-sub">Pause 1h/j deduite</div>
    </div>
    <div class="kpi-card ct">
        <div class="kpi-label">Heures realisees</div>
        <div class="kpi-value"><?php echo e(number_format($compteurMois->heures_realisees, 1)); ?>h</div>
        <?php $t = $compteurMois->taux_realisation; ?>
        <div class="prog-bar"><div class="prog-fill <?php echo e($t >= 90 ? 'prog-g' : ($t >= 70 ? 'prog-a' : 'prog-r')); ?>" style="width:<?php echo e(min($t,100)); ?>%"></div></div>
        <div class="kpi-sub"><?php echo e($t); ?>% du planning</div>
    </div>
    <div class="kpi-card ca">
        <div class="kpi-label">Heures supp.</div>
        <div class="kpi-value"><?php echo e(number_format($compteurMois->heures_supplementaires, 1)); ?>h</div>
        <div class="kpi-sub"><?php echo e($compteurMois->jours_travailles); ?> jours travailles</div>
    </div>
    <div class="kpi-card <?php echo e($compteurMois->ecart >= 0 ? 'cg' : 'cr'); ?>">
        <div class="kpi-label">Ecart mensuel</div>
        <div class="kpi-value"><?php echo e($compteurMois->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($compteurMois->ecart, 1)); ?>h</div>
        <div class="kpi-sub">Realise + supp. vs planifie</div>
    </div>
</div>
<?php endif; ?>

<div class="tabs">
    <button class="tab-btn active" onclick="showTab(event,'emp-mensuel')">Vue mensuelle</button>
    <button class="tab-btn" onclick="showTab(event,'emp-semaines')">Par semaine</button>
    <button class="tab-btn" onclick="showTab(event,'emp-annuel')">Evolution annuelle</button>
</div>



<div id="emp-mensuel" class="tab-panel">

    <div class="grid-2-1" style="margin-bottom:20px;">

        <div class="card">
            <div class="card-header">
                <span>Calendrier — <?php echo e(\Carbon\Carbon::create($annee, $mois, 1)->locale('fr')->translatedFormat('F Y')); ?></span>
                <div class="cal-legend">
                    <div class="cal-legend-item"><div class="cal-legend-dot dot-present"></div> Present</div>
                    <div class="cal-legend-item"><div class="cal-legend-dot dot-absent"></div> Absent</div>
                    <div class="cal-legend-item"><div class="cal-legend-dot dot-planifie"></div> Planifie</div>
                </div>
            </div>
            <div class="card-body">
                <div class="cal-grid-header">
                    <?php $__currentLoopData = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="cal-day-name"><?php echo e($n); ?></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php
                    $premierJour = \Carbon\Carbon::create($annee, $mois, 1);
                    $decalage    = $premierJour->dayOfWeek === 0 ? 6 : $premierJour->dayOfWeek - 1;
                ?>
                <div class="cal-grid">
                    <?php for($i = 0; $i < $decalage; $i++): ?>
                        <div class="cal-cell cal-empty"></div>
                    <?php endfor; ?>

                    <?php $__currentLoopData = $joursDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $st = $jour['statut'];
                        $dotClass = match($st) {
                            'present'      => 'dot-present',
                            'absent'       => 'dot-absent',
                            'planifie'     => 'dot-planifie',
                            'weekend'      => 'dot-weekend',
                            default        => 'dot-none',
                        };
                        $hrClass = match($st) {
                            'present'  => 'color-present',
                            'absent'   => 'color-absent',
                            'planifie' => 'color-planifie',
                            default    => 'color-non-plan',
                        };
                    ?>
                    <div class="cal-cell <?php echo e($jour['is_weekend'] ? 'cal-weekend' : ''); ?> <?php echo e($jour['is_today'] ? 'cal-today' : ''); ?>"
                         title="<?php echo e(ucfirst($jour['nom_jour_complet'])); ?> <?php echo e($jour['jour']); ?> - <?php echo e($st); ?>">
                        <div class="cal-short-day"><?php echo e($jour['nom_jour']); ?></div>
                        <div class="cal-num"><?php echo e($jour['jour']); ?></div>

                        <?php if(!$jour['is_weekend']): ?>
                            <?php if($jour['heures_realisees'] > 0): ?>
                                <div class="cal-hours <?php echo e($hrClass); ?>"><?php echo e(number_format($jour['heures_realisees'], 1)); ?>h</div>
                            <?php elseif($jour['heures_planifiees'] > 0): ?>
                                <div class="cal-hours color-planifie">— / <?php echo e(number_format($jour['heures_planifiees'], 1)); ?>h</div>
                            <?php endif; ?>

                            <?php if($jour['shift_start'] && $jour['shift_end']): ?>
                                <div class="cal-shift"><?php echo e(substr($jour['shift_start'],0,5)); ?>-<?php echo e(substr($jour['shift_end'],0,5)); ?></div>
                            <?php endif; ?>

                            <?php if($jour['heures_realisees'] > 0 && $jour['heures_planifiees'] > 0): ?>
                                <div class="cal-ecart <?php echo e($jour['ecart'] >= 0 ? 'color-pos' : 'color-neg'); ?>">
                                    <?php echo e($jour['ecart'] >= 0 ? '+' : ''); ?><?php echo e(number_format($jour['ecart'], 1)); ?>h
                                </div>
                            <?php endif; ?>

                            <span class="cal-dot <?php echo e($dotClass); ?>"></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:14px;">
            <div class="card">
                <div class="card-header">Recapitulatif du mois</div>
                <?php if($compteurMois): ?>
                <table class="month-recap-table">
                    <tr><td>Heures planifiees</td><td><?php echo e(number_format($compteurMois->heures_planifiees, 1)); ?>h</td></tr>
                    <tr><td>Heures realisees</td><td class="text-teal"><?php echo e(number_format($compteurMois->heures_realisees, 1)); ?>h</td></tr>
                    <tr><td>Heures supplementaires</td><td class="text-amber"><?php echo e(number_format($compteurMois->heures_supplementaires, 1)); ?>h</td></tr>
                    <tr><td>Jours travailles</td><td><?php echo e($compteurMois->jours_travailles); ?> j</td></tr>
                    <tr><td>Taux de realisation</td><td><?php echo e($compteurMois->taux_realisation); ?>%</td></tr>
                    <tr class="row-total">
                        <td>Ecart</td>
                        <td class="<?php echo e($compteurMois->ecart >= 0 ? 'text-green' : 'text-red'); ?>">
                            <?php echo e($compteurMois->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($compteurMois->ecart, 1)); ?>h
                        </td>
                    </tr>
                </table>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">Regles de calcul</div>
                <div class="card-body" style="font-size:12px;line-height:1.8;color:var(--slate-500)">
                    <div><strong>Planifiees</strong> : duree shift (planning) &minus; 1h pause dejeuner par jour.</div>
                    <div><strong>Realisees</strong> : heures enregistrees dans le pointage (deja nettes).</div>
                    <div><strong>Ecart</strong> : (realisees + supp.) &minus; planifiees.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Detail journalier — <?php echo e(\Carbon\Carbon::create($annee, $mois, 1)->locale('fr')->translatedFormat('F Y')); ?></span>
            <span style="font-size:12px;font-weight:normal;color:var(--slate-500)"><?php echo e(count($joursDetails)); ?> jours</span>
        </div>
        <div class="day-table-wrap">
            <table class="day-table">
                <thead>
                    <tr>
                        <th style="text-align:left">Jour</th>
                        <th>Horaire planifie</th>
                        <th>Planifiees</th>
                        <th>Realisees</th>
                        <th>Supp.</th>
                        <th>Ecart</th>
                        <th>Entree</th>
                        <th>Sortie</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $joursDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $st = $jour['statut'];
                        $rowClass = $jour['is_weekend'] ? 'row-weekend' : ($jour['is_today'] ? 'row-today' : '');
                        $badgeClass = match($st) {
                            'present'  => 'sb-present',
                            'absent'   => 'sb-absent',
                            'planifie' => 'sb-planifie',
                            'weekend'  => 'sb-weekend',
                            default    => 'sb-none',
                        };
                        $badgeLabel = match($st) {
                            'present'      => 'Present',
                            'absent'       => 'Absent',
                            'planifie'     => 'Planifie',
                            'weekend'      => 'Weekend',
                            'non_planifie' => '—',
                            default        => $st,
                        };
                    ?>
                    <tr class="<?php echo e($rowClass); ?>">
                        <td style="text-align:left">
                            <span style="font-weight:bold"><?php echo e(ucfirst($jour['nom_jour'])); ?></span>
                            <span style="color:var(--slate-400);margin-left:4px"><?php echo e($jour['jour']); ?></span>
                        </td>
                        <td style="font-size:12px;color:var(--slate-500)">
                            <?php if($jour['shift_start'] && $jour['shift_end']): ?>
                                <?php echo e(substr($jour['shift_start'],0,5)); ?> &rarr; <?php echo e(substr($jour['shift_end'],0,5)); ?>

                            <?php else: ?>
                                <span style="color:var(--slate-300)">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($jour['heures_planifiees'] > 0): ?>
                                <?php echo e(number_format($jour['heures_planifiees'], 1)); ?>h
                            <?php else: ?>
                                <span style="color:var(--slate-300)">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="<?php echo e($jour['heures_realisees'] > 0 ? 'text-teal fw-bold' : ''); ?>">
                            <?php if($jour['heures_realisees'] > 0): ?>
                                <?php echo e(number_format($jour['heures_realisees'], 1)); ?>h
                            <?php else: ?>
                                <span style="color:var(--slate-300)">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="<?php echo e($jour['heures_supplementaires'] > 0 ? 'text-amber' : ''); ?>">
                            <?php if($jour['heures_supplementaires'] > 0): ?>
                                <?php echo e(number_format($jour['heures_supplementaires'], 1)); ?>h
                            <?php else: ?>
                                <span style="color:var(--slate-300)">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($jour['heures_planifiees'] > 0 && $jour['heures_realisees'] > 0): ?>
                                <span class="<?php echo e($jour['ecart'] >= 0 ? 'text-green' : 'text-red'); ?> fw-bold">
                                    <?php echo e($jour['ecart'] >= 0 ? '+' : ''); ?><?php echo e(number_format($jour['ecart'], 1)); ?>h
                                </span>
                            <?php else: ?>
                                <span style="color:var(--slate-300)">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:var(--slate-500)">
                            <?php echo e($jour['heure_entree'] ? substr($jour['heure_entree'],0,5) : '—'); ?>

                        </td>
                        <td style="font-size:12px;color:var(--slate-500)">
                            <?php echo e($jour['heure_sortie'] ? substr($jour['heure_sortie'],0,5) : '—'); ?>

                        </td>
                        <td><span class="status-badge <?php echo e($badgeClass); ?>"><?php echo e($badgeLabel); ?></span></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <?php if($compteurMois): ?>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:left">Total du mois</td>
                        <td><?php echo e(number_format($compteurMois->heures_planifiees, 1)); ?>h</td>
                        <td class="text-teal"><?php echo e(number_format($compteurMois->heures_realisees, 1)); ?>h</td>
                        <td class="text-amber"><?php echo e(number_format($compteurMois->heures_supplementaires, 1)); ?>h</td>
                        <td class="<?php echo e($compteurMois->ecart >= 0 ? 'text-green' : 'text-red'); ?>">
                            <?php echo e($compteurMois->ecart >= 0 ? '+' : ''); ?><?php echo e(number_format($compteurMois->ecart, 1)); ?>h
                        </td>
                        <td colspan="3" style="color:var(--slate-400);text-align:right"><?php echo e($compteurMois->jours_travailles); ?> j travailles</td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>



<div id="emp-semaines" class="tab-panel" style="display:none">

    <div class="weeks-grid">
        <?php $__currentLoopData = $semaines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $solde = $sem['solde'];
            $taux  = $sem['taux'];
            $headClass = $solde > 0 ? 'wh-positive' : ($solde == 0 ? 'wh-neutral' : 'wh-warning');
        ?>
        <div class="week-card">
            <div class="week-head <?php echo e($headClass); ?>">
                <div>
                    <div class="wk-title">Semaine <?php echo e($sem['numero']); ?></div>
                    <div class="wk-period"><?php echo e($sem['debut']); ?> — <?php echo e($sem['fin']); ?></div>
                </div>
                <div class="wk-solde"><?php echo e($solde >= 0 ? '+' : ''); ?><?php echo e(number_format($solde, 1)); ?>h</div>
            </div>
            <div class="week-stats">
                <div class="wk-stat">
                    <div class="wk-stat-val"><?php echo e(number_format($sem['heures_planifiees'], 1)); ?>h</div>
                    <div class="wk-stat-lbl">Planifiees</div>
                </div>
                <div class="wk-stat">
                    <div class="wk-stat-val ct"><?php echo e(number_format($sem['heures_realisees'], 1)); ?>h</div>
                    <div class="wk-stat-lbl">Realisees</div>
                </div>
                <div class="wk-stat">
                    <div class="wk-stat-val ca"><?php echo e(number_format($sem['heures_supplementaires'], 1)); ?>h</div>
                    <div class="wk-stat-lbl">Supp.</div>
                </div>
            </div>
            <div class="week-footer">
                <span style="font-size:12px;color:var(--slate-500)"><?php echo e($sem['jours_travailles']); ?> j travailles</span>
                <div class="week-prog-wrap" style="max-width:100px">
                    <div class="week-prog-track">
                        <div class="week-prog-fill <?php echo e($taux >= 90 ? 'prog-g' : ($taux >= 70 ? 'prog-a' : 'prog-r')); ?>" style="width:<?php echo e(min($taux,100)); ?>%"></div>
                    </div>
                    <span style="font-size:12px;font-weight:bold;color:var(--slate-500)"><?php echo e($taux); ?>%</span>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if(count($semaines) > 0): ?>
    <?php
        $totPlan  = collect($semaines)->sum('heures_planifiees');
        $totReal  = collect($semaines)->sum('heures_realisees');
        $totSupp  = collect($semaines)->sum('heures_supplementaires');
        $totTotal = collect($semaines)->sum('total');
        $totSolde = collect($semaines)->sum('solde');
        $totJours = collect($semaines)->sum('jours_travailles');
    ?>
    <div class="card" style="margin-top:16px">
        <div class="card-header">Tableau recapitulatif — semaines</div>
        <div class="recap-table-wrap">
            <table class="recap-table">
                <thead>
                    <tr>
                        <th style="text-align:left">Semaine</th>
                        <th>Periode</th>
                        <th>Planifiees</th>
                        <th>Realisees</th>
                        <th>Supp.</th>
                        <th>Total</th>
                        <th>Solde</th>
                        <th>Jours</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $semaines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>Sem. <?php echo e($sem['numero']); ?></td>
                        <td style="color:var(--slate-400);font-size:12px"><?php echo e($sem['debut']); ?> — <?php echo e($sem['fin']); ?></td>
                        <td><?php echo e(number_format($sem['heures_planifiees'], 1)); ?>h</td>
                        <td class="text-teal"><?php echo e(number_format($sem['heures_realisees'], 1)); ?>h</td>
                        <td class="text-amber"><?php echo e(number_format($sem['heures_supplementaires'], 1)); ?>h</td>
                        <td class="fw-bold"><?php echo e(number_format($sem['total'], 1)); ?>h</td>
                        <td class="<?php echo e($sem['solde'] >= 0 ? 'text-green' : 'text-red'); ?> fw-bold">
                            <?php echo e($sem['solde'] >= 0 ? '+' : ''); ?><?php echo e(number_format($sem['solde'], 1)); ?>h
                        </td>
                        <td style="color:var(--slate-500)"><?php echo e($sem['jours_travailles']); ?> j</td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:left">Total du mois</td>
                        <td><?php echo e(number_format($totPlan, 1)); ?>h</td>
                        <td class="text-teal"><?php echo e(number_format($totReal, 1)); ?>h</td>
                        <td class="text-amber"><?php echo e(number_format($totSupp, 1)); ?>h</td>
                        <td><?php echo e(number_format($totTotal, 1)); ?>h</td>
                        <td class="<?php echo e($totSolde >= 0 ? 'text-green' : 'text-red'); ?>">
                            <?php echo e($totSolde >= 0 ? '+' : ''); ?><?php echo e(number_format($totSolde, 1)); ?>h
                        </td>
                        <td style="color:var(--slate-500)"><?php echo e($totJours); ?> j</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>



<div id="emp-annuel" class="tab-panel" style="display:none">
    <div class="card">
        <div class="card-header">Evolution annuelle <?php echo e($annee); ?> — <?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?></div>
        <div class="card-body">
            <div class="chart-legend">
                <div class="chart-legend-item">
                    <div class="chart-legend-dot" style="background:#e2e8f0;"></div>
                    <span>Planifiees</span>
                </div>
                <div class="chart-legend-item">
                    <div class="chart-legend-dot" style="background:#0d9488;"></div>
                    <span>Realisees</span>
                </div>
                <div class="chart-legend-item">
                    <div class="chart-legend-line" style="background:#d97706;"></div>
                    <span>Supp.</span>
                </div>
            </div>
            <div style="position:relative;width:100%;height:260px;">
                <canvas id="chartAnnuel" role="img" aria-label="Evolution annuelle des heures de <?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?> pour <?php echo e($annee); ?>">
                    Graphique des heures planifiees, realisees et supplementaires par mois.
                </canvas>
            </div>
            <div id="chartAnnuelCalcs" class="chart-calcs" style="display:none;"></div>
        </div>
    </div>
</div>



<?php else: ?>
<div class="empty-state">
    <div class="empty-title">Selectionnez un employe ou un departement</div>
    <div class="empty-sub">Utilisez les filtres ci-dessus pour afficher les donnees de temps de travail.</div>
</div>
<?php endif; ?>

</div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// =========================================================================
// GESTION DES ONGLETS
// =========================================================================
function showTab(e, id) {
    var tabs = e.target.closest('.tabs');
    if (tabs) tabs.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
    e.target.classList.add('active');
    var panel = document.getElementById(id);
    if (!panel) return;
    var parent = panel.parentElement;
    parent.querySelectorAll('.tab-panel').forEach(function(p) { p.style.display = 'none'; });
    panel.style.display = '';
}

// =========================================================================
// GRAPHIQUES
// =========================================================================
function buildChart(canvasId, data, calcsId) {
    var ctx = document.getElementById(canvasId);
    if (!ctx || !data || data.length === 0) return;

    var TEAL  = '#0d9488';
    var GREY  = '#e2e8f0';
    var AMBER = '#d97706';

    var labels = data.map(function(d) { return d.mois; });
    var plan   = data.map(function(d) { return parseFloat(d.heures_planifiees) || 0; });
    var real   = data.map(function(d) { return parseFloat(d.heures_realisees)  || 0; });
    var supp   = data.map(function(d) { return parseFloat(d.heures_supp)       || 0; });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Planifiees', data: plan, backgroundColor: GREY, borderRadius: 3, borderSkipped: false, order: 2 },
                { label: 'Realisees', data: real, backgroundColor: TEAL, borderRadius: 3, borderSkipped: false, order: 1 },
                { type: 'line', label: 'Supp.', data: supp, borderColor: AMBER, backgroundColor: 'transparent', borderWidth: 2, pointRadius: 4, pointBackgroundColor: AMBER, pointBorderColor: '#fff', pointBorderWidth: 1.5, tension: 0.3, order: 0 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b', titleColor: '#f1f5f9', bodyColor: '#cbd5e1', padding: 10, cornerRadius: 6,
                    callbacks: { label: function(ctx) { return ' ' + ctx.dataset.label + ' : ' + ctx.parsed.y.toFixed(1) + 'h'; } }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Arial, Helvetica, sans-serif', size: 11 }, color: '#64748b', autoSkip: false } },
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Arial, Helvetica, sans-serif', size: 11 }, color: '#64748b', callback: function(v) { return v + 'h'; } } }
            }
        }
    });

    var moisActifs = data.filter(function(d) {
        return (parseFloat(d.heures_planifiees) || 0) > 0 || (parseFloat(d.heures_realisees) || 0) > 0;
    });

    var totalPlan = plan.reduce(function(a, b) { return a + b; }, 0);
    var totalReal = real.reduce(function(a, b) { return a + b; }, 0);
    var totalSupp = supp.reduce(function(a, b) { return a + b; }, 0);
    var ecart     = totalReal - totalPlan;
    var taux      = totalPlan > 0 ? Math.round((totalReal / totalPlan) * 100) : 0;

    var maxReal   = Math.max.apply(null, real.filter(function(v) { return v > 0; }));
    var meilIdx   = real.indexOf(maxReal);
    var meilLabel = meilIdx >= 0 ? labels[meilIdx] : '—';

    var realActifs = real.filter(function(v) { return v > 0; });
    var minReal    = realActifs.length > 0 ? Math.min.apply(null, realActifs) : 0;
    var pireIdx    = real.indexOf(minReal);
    var pireLabel  = pireIdx >= 0 && minReal > 0 ? labels[pireIdx] : '—';

    var moyReal    = moisActifs.length > 0 ? (totalReal / moisActifs.length) : 0;
    var ecartColor = ecart >= 0 ? '#16a34a' : '#dc2626';
    var ecartStr   = (ecart >= 0 ? '+' : '') + ecart.toFixed(1) + 'h';
    var tauxColor  = taux >= 90 ? '#16a34a' : (taux >= 70 ? '#d97706' : '#dc2626');

    var box = document.getElementById(calcsId);
    if (!box) return;
    box.style.display = '';
    box.innerHTML =
        '<span style="font-weight:bold;color:#334155;font-size:13px;">Calculs annuels</span>' +
        '<span style="color:#cbd5e1;margin:0 8px;">|</span>' +
        '<strong>Mois avec donnees :</strong> ' + moisActifs.length + ' / ' + data.length +
        '<br><strong>Planifiees totales :</strong> ' + totalPlan.toFixed(1) + 'h&nbsp;&nbsp;' +
        '<strong style="color:' + TEAL + '">Realisees totales :</strong> ' + totalReal.toFixed(1) + 'h&nbsp;&nbsp;' +
        '<strong style="color:' + AMBER + '">Supp. totales :</strong> ' + totalSupp.toFixed(1) + 'h' +
        '<br><strong>Ecart annuel :</strong> <span style="color:' + ecartColor + ';font-weight:bold;">' + ecartStr + '</span>&nbsp;&nbsp;' +
        '<strong>Taux de realisation :</strong> <span style="color:' + tauxColor + ';font-weight:bold;">' + taux + '%</span>&nbsp;&nbsp;' +
        '<strong>Moyenne/mois :</strong> ' + moyReal.toFixed(1) + 'h' +
        '<br><strong>Meilleur mois :</strong> ' + meilLabel + ' (' + maxReal.toFixed(1) + 'h)' +
        (pireLabel !== meilLabel && pireLabel !== '—' ? '&nbsp;&nbsp;<strong>Mois le plus faible :</strong> ' + pireLabel + ' (' + minReal.toFixed(1) + 'h)' : '');

    var TEAL_LOCAL  = TEAL;
    var AMBER_LOCAL = AMBER;
}

// =========================================================================
// CALENDRIER POPUP — JOURS PLANIFIES
// =========================================================================
(function () {
    // Donnees planning injectees depuis le controleur
    // Format : { "2026-04-07": { "shift_start": "08:00:00", "shift_end": "17:00:00" }, ... }
    var PLANNING_JOURS = <?php echo json_encode($joursPlanningSemaine ?? [], 15, 512) ?>;

    var calView       = 'mois';
    var calAnnee      = <?php echo e($annee); ?>;
    var calMois       = <?php echo e($mois); ?>;  // 1-12
    var calWeekOffset = 0;
    var today         = new Date();

    var JOURS_COURTS = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    var MOIS_FR = [
        'Janvier','Fevrier','Mars','Avril','Mai','Juin',
        'Juillet','Aout','Septembre','Octobre','Novembre','Decembre'
    ];

    var btn     = document.getElementById('calPopupBtn');
    var popup   = document.getElementById('calPopup');
    var overlay = document.getElementById('calPopupOverlay');

    if (!btn || !popup || !overlay) return;

    // Ouverture
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = popup.style.display !== 'none';
        if (isOpen) {
            closeCalPopup();
        } else {
            popup.style.display = '';
            overlay.classList.add('open');
            calWeekOffset = 0;
            renderCal();
        }
    });

    // Fermeture au clic sur l'overlay
    overlay.addEventListener('click', closeCalPopup);

    function closeCalPopup() {
        popup.style.display = 'none';
        overlay.classList.remove('open');
    }

    // Switcher mois / semaine
    window.switchCalView = function (v) {
        calView = v;
        document.getElementById('btnMois').classList.toggle('active', v === 'mois');
        document.getElementById('btnSemaine').classList.toggle('active', v === 'semaine');
        calWeekOffset = 0;
        renderCal();
    };

    // Navigation
    window.calNavPrev = function () {
        if (calView === 'mois') {
            calMois--;
            if (calMois < 1) { calMois = 12; calAnnee--; }
        } else {
            calWeekOffset--;
        }
        renderCal();
    };

    window.calNavNext = function () {
        if (calView === 'mois') {
            calMois++;
            if (calMois > 12) { calMois = 1; calAnnee++; }
        } else {
            calWeekOffset++;
        }
        renderCal();
    };

    // Helpers
    function pad(n) { return String(n).padStart(2, '0'); }

    function toDateStr(y, m, d) {
        return y + '-' + pad(m) + '-' + pad(d);
    }

    function isPlanifie(ds) {
        return PLANNING_JOURS && PLANNING_JOURS[ds] !== undefined;
    }

    function getShift(ds) {
        if (!PLANNING_JOURS || !PLANNING_JOURS[ds]) return null;
        var s = PLANNING_JOURS[ds];
        if (!s.shift_start || !s.shift_end) return null;
        return String(s.shift_start).substr(0, 5) + '\u2013' + String(s.shift_end).substr(0, 5);
    }

    function isToday(y, m, d) {
        return today.getFullYear() === y && (today.getMonth() + 1) === m && today.getDate() === d;
    }

    function isWeekend(dow) {
        // dow: 0=dim, 6=sam
        return dow === 0 || dow === 6;
    }

    // Rendu principal
    function renderCal() {
        var title = document.getElementById('calPopupTitle');
        var body  = document.getElementById('calPopupBody');

        if (calView === 'mois') {
            title.textContent = MOIS_FR[calMois - 1] + ' ' + calAnnee;
            body.innerHTML    = renderMois();
        } else {
            var sem = getSemaineCourante();
            title.textContent = 'Sem. ' + sem.label;
            body.innerHTML    = renderSemaine(sem);
        }
    }

    // ---- Vue mensuelle ----
    function renderMois() {
        var firstDay  = new Date(calAnnee, calMois - 1, 1);
        var lastDay   = new Date(calAnnee, calMois, 0);
        var startDow  = (firstDay.getDay() + 6) % 7; // 0=Lun
        var totalDays = lastDay.getDate();

        var html = '<div class="cal-popup-daynames">';
        JOURS_COURTS.forEach(function (j) {
            html += '<div class="cal-popup-dayname">' + j + '</div>';
        });
        html += '</div><div class="cal-popup-grid">';

        // Cellules vides avant le 1er du mois
        for (var i = 0; i < startDow; i++) {
            html += '<div class="cal-popup-cell"></div>';
        }

        for (var d = 1; d <= totalDays; d++) {
            var ds   = toDateStr(calAnnee, calMois, d);
            var date = new Date(calAnnee, calMois - 1, d);
            var dow  = date.getDay();
            var wkd  = isWeekend(dow);
            var plan = isPlanifie(ds) && !wkd;
            var tod  = isToday(calAnnee, calMois, d);

            var cls = 'cal-popup-cell';
            if (tod)       cls += ' cp-today';
            else if (wkd)  cls += ' cp-weekend';
            else if (plan) cls += ' cp-planifie';

            html += '<div class="' + cls + '">' + d + '</div>';
        }

        // Compléter la dernière ligne
        var total = startDow + totalDays;
        var reste = total % 7 !== 0 ? 7 - (total % 7) : 0;
        for (var j = 0; j < reste; j++) {
            html += '<div class="cal-popup-cell cp-other-month"></div>';
        }

        html += '</div>';
        html += '<div class="cal-popup-legend">';
        html += '<div class="cal-popup-legend-item"><div class="cal-popup-legend-dot" style="background:var(--teal-500);"></div><span>Planifie</span></div>';
        html += '<div class="cal-popup-legend-item"><div class="cal-popup-legend-dot" style="background:var(--teal-600);"></div><span>Aujourd\'hui</span></div>';
        html += '<div class="cal-popup-legend-item"><div class="cal-popup-legend-dot" style="background:var(--slate-200);"></div><span>Week-end</span></div>';
        html += '</div>';

        return html;
    }

    // ---- Calcul de la semaine courante ----
    function getSemaineCourante() {
        // Semaine de base = semaine contenant le 1er du mois + offset
        var base = new Date(calAnnee, calMois - 1, 1);
        var dow  = (base.getDay() + 6) % 7; // 0=Lun
        base.setDate(base.getDate() - dow + (calWeekOffset * 7));

        var days = [];
        for (var i = 0; i < 7; i++) {
            var d = new Date(base);
            d.setDate(base.getDate() + i);
            days.push(d);
        }

        var debut = days[0];
        var fin   = days[6];
        var label = pad(debut.getDate()) + '/' + pad(debut.getMonth() + 1)
                  + ' \u2013 ' + pad(fin.getDate()) + '/' + pad(fin.getMonth() + 1);

        return { days: days, label: label };
    }

    // ---- Vue hebdomadaire ----
    function renderSemaine(sem) {
        var days = sem.days;
        var html = '<div class="cal-week-label">Semaine du ' + sem.label + '</div>';
        html += '<div class="cal-week-grid">';

        days.forEach(function (d) {
            var y   = d.getFullYear();
            var m   = d.getMonth() + 1;
            var day = d.getDate();
            var ds  = toDateStr(y, m, day);
            var dow = d.getDay();
            var wkd = isWeekend(dow);
            var tod = isToday(y, m, day);
            var plan = isPlanifie(ds) && !wkd;
            var shift = getShift(ds);
            var nomJ = JOURS_COURTS[(dow + 6) % 7];

            var cls = 'cal-week-cell';
            if (tod)       cls += ' cw-today';
            else if (wkd)  cls += ' cw-weekend';
            else if (plan) cls += ' cw-planifie';

            html += '<div class="' + cls + '">';
            html += '<div class="cal-week-dayname">' + nomJ + '</div>';
            html += '<div class="cal-week-num">' + day + '</div>';
            if (shift && plan) {
                html += '<div class="cal-week-shift">' + shift + '</div>';
            }
            html += '</div>';
        });

        html += '</div>';
        html += '<div class="cal-popup-legend" style="margin-top:10px;padding-top:8px;border-top:1px solid var(--slate-100);">';
        html += '<div class="cal-popup-legend-item"><div class="cal-popup-legend-dot" style="background:var(--teal-500);"></div><span>Planifie</span></div>';
        html += '<div class="cal-popup-legend-item"><div class="cal-popup-legend-dot" style="background:var(--teal-600);"></div><span>Aujourd\'hui</span></div>';
        html += '</div>';

        return html;
    }

})();

// =========================================================================
// INIT GRAPHIQUES
// =========================================================================
document.addEventListener('DOMContentLoaded', function () {
    var annualData = <?php echo json_encode($graphiqueMois ?? [], 15, 512) ?>;
    var deptData   = <?php echo json_encode($graphiqueMoisDept ?? [], 15, 512) ?>;

    buildChart('chartAnnuel', annualData, 'chartAnnuelCalcs');
    buildChart('chartDept',   deptData,   'chartDeptCalcs');
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/vue-ensemble/index.blade.php ENDPATH**/ ?>