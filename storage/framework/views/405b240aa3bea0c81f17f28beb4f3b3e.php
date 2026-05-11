


<?php $__env->startSection('title', 'LMS — Planning des Formations'); ?>
<?php $__env->startSection('page-title', 'Planning des Formations'); ?>

<?php $__env->startPush('styles'); ?>
<style>
:root {
    --teal:#1D9E75; --teal-light:#E1F5EE; --teal-dark:#085041; --teal-mid:#9FE1CB;
    --amber:#BA7517; --amber-light:#FAEEDA;
    --green:#639922; --green-light:#EAF3DE;
    --red:#E24B4A;   --red-light:#FCEBEB;
    --blue:#378ADD;  --blue-light:#E6F1FB;
    --purple:#7F77DD;--purple-light:#EEEDFE;
    --coral:#D85A30; --coral-light:#FAECE7;
    --pink:#D4537E;  --pink-light:#FBEAF0;
    --gray-av:#888780; --gray-light:#F1EFE8;
    --border:#e5e7eb; --bg2:#f9fafb;
}

.lp { padding:28px 32px; }

/* Header */
.lp-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:22px; }
.lp-title  { font-size:22px; font-weight:600; color:#111827; line-height:1.2; }
.lp-sub    { font-size:13px; color:#6b7280; margin-top:3px; }
.lp-acts   { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }

/* Toggle */
.lp-toggle { display:inline-flex; border:1px solid var(--border); border-radius:8px; overflow:hidden; background:#fff; }
.lp-toggle a { padding:7px 18px; font-size:13px; color:#6b7280; text-decoration:none; background:transparent; transition:background .12s,color .12s; white-space:nowrap; }
.lp-toggle a.active { background:#f3f4f6; color:#111827; font-weight:500; }
.lp-toggle a:not(:last-child) { border-right:1px solid var(--border); }

/* Buttons */
.btn-lp { display:inline-flex; align-items:center; gap:6px; padding:7px 16px; border-radius:8px; font-size:13px; cursor:pointer; text-decoration:none; white-space:nowrap; border:1px solid transparent; transition:background .12s,border-color .12s; }
.btn-ghost { background:#fff; border-color:var(--border); color:#374151; }
.btn-ghost:hover { background:var(--bg2); color:#374151; }
.btn-main  { background:#14B8A6; border-color:#14B8A6; color:#fff; font-weight:500; }
.btn-main:hover { background:var(--teal-dark); }

/* Toolbar */
.lp-bar { background:#fff; border:0.5px solid var(--border); border-radius:12px; padding:12px 16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
.lp-week-btn { height:34px; padding:0 14px; border-radius:8px; font-size:13px; border:1px solid var(--border); background:#fff; color:#374151; cursor:pointer; display:flex; align-items:center; gap:6px; text-decoration:none; white-space:nowrap; transition:background .12s; }
.lp-week-btn:hover { background:var(--bg2); color:#374151; }
.lp-week-label { padding:0 16px; height:34px; display:flex; align-items:center; font-size:13px; font-weight:500; color:#111827; background:var(--bg2); border:1px solid var(--border); border-radius:8px; white-space:nowrap; }
.lp-sel { height:34px; padding:0 10px; font-size:13px; border-radius:8px; border:0.5px solid var(--border); background:#fff; color:#374151; cursor:pointer; outline:none; }

/* Grid */
.lp-card { background:#fff; border:0.5px solid var(--border); border-radius:12px; overflow:hidden; }
.lp-scroll { overflow-x:auto; }
.pg-table { width:100%; border-collapse:collapse; min-width:960px; table-layout:fixed; }

.pg-table thead th { padding:10px 8px; font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; background:var(--bg2); border-bottom:0.5px solid var(--border); text-align:center; white-space:nowrap; }
.pg-table thead th.col-emp  { text-align:left; padding-left:16px; width:190px; }
.pg-table thead th.col-form { text-align:left; padding-left:12px; width:140px; }
.pg-table thead th.col-day  { width:calc((100% - 330px) / 7); }

.th-today { background:var(--teal-light) !important; }
.th-today .th-num,.th-today .th-day { color:var(--teal-dark) !important; }
.th-num { font-size:15px; font-weight:700; color:#111827; display:block; line-height:1; }
.th-day { font-size:11px; color:#6b7280; display:block; margin-top:3px; font-weight:500; letter-spacing:.02em; }

.pg-table tbody tr { border-bottom:0.5px solid #f3f4f6; }
.pg-table tbody tr:last-child { border-bottom:none; }
.pg-table tbody tr:hover td { background:#fafafa; }
.pg-table tbody td { padding:0; vertical-align:middle; border-right:0.5px solid #f3f4f6; }
.pg-table tbody td:last-child { border-right:none; }

.pg-emp  { display:flex; align-items:center; gap:10px; padding:12px 8px 12px 16px; }
.pg-av   { width:32px; height:32px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:600; }
.pg-name { font-size:13px; font-weight:500; color:#111827; line-height:1.2; }
.pg-dept { font-size:11px; color:#9ca3af; margin-top:1px; }
.pg-form-cell { padding:12px; font-size:12px; color:#6b7280; }
/* Cellule jour — plus haute pour afficher titre + horaire */
.pg-day  { padding:6px 5px; text-align:center; height:64px; display:flex; align-items:center; justify-content:center; }

/* Session avec titre + horaire */
.pg-session {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    background:var(--teal-light); border:0.5px solid var(--teal-mid);
    border-radius:5px; padding:5px 7px; width:100%;
    cursor:pointer; text-align:center; line-height:1.3;
    transition:background .12s; text-decoration:none;
    overflow:hidden;
}
.pg-session:hover { background:var(--teal-mid); }
.pg-sess-titre {
    font-size:11px; font-weight:500; color:var(--teal-dark);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    max-width:100%; display:block;
}
.pg-sess-heure {
    font-size:10px; color:var(--teal); margin-top:2px;
    white-space:nowrap; display:block; font-variant-numeric:tabular-nums;
}

.pg-create { display:flex; align-items:center; justify-content:center; gap:3px; font-size:11px; color:#d1d5db; cursor:pointer; padding:4px; transition:color .12s; background:none; border:none; width:100%; }
.pg-create:hover { color:var(--teal); }

.lp-empty { text-align:center; padding:60px 20px; color:#9ca3af; }
.lp-empty i { font-size:40px; display:block; margin-bottom:10px; }

/* Modal (styles partages avec index) */
.lms-ov { display:none; position:fixed; inset:0; background:rgba(17,24,39,.48); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(2px); }
.lms-ov.open { display:flex; }
.lms-modal { background:#fff; border-radius:14px; border:0.5px solid var(--border); width:600px; max-width:96vw; max-height:93vh; overflow-y:auto; box-shadow:0 24px 64px rgba(0,0,0,.18); animation:mIn .18s ease; }
@keyframes mIn { from{opacity:0;transform:translateY(-14px) scale(.98)} to{opacity:1;transform:none} }
.m-head { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 16px; border-bottom:0.5px solid #f3f4f6; position:sticky; top:0; background:#fff; z-index:2; border-radius:14px 14px 0 0; }
.m-title { font-size:16px; font-weight:600; color:#111827; display:flex; align-items:center; gap:10px; }
.m-icon  { width:34px; height:34px; border-radius:9px; background:var(--teal-light); color:var(--teal); display:flex; align-items:center; justify-content:center; font-size:16px; }
.m-close { width:30px; height:30px; border-radius:7px; border:0.5px solid var(--border); background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:15px; transition:all .12s; }
.m-close:hover { background:#f3f4f6; color:#374151; }
.m-body { padding:22px 24px; }
.m-foot { display:flex; justify-content:flex-end; gap:8px; padding:16px 24px; border-top:0.5px solid #f3f4f6; position:sticky; bottom:0; background:#fff; }
.btn-lms-ghost { background:#fff; border:1px solid var(--border); color:#374151; display:inline-flex; align-items:center; gap:6px; padding:7px 16px; border-radius:8px; font-size:13px; cursor:pointer; }
.btn-lms-main  { background:var(--teal); border:1px solid var(--teal-dark); color:#fff; font-weight:500; display:inline-flex; align-items:center; gap:6px; padding:7px 16px; border-radius:8px; font-size:13px; cursor:pointer; }
.fg { margin-bottom:16px; }
.fg label { display:block; font-size:13px; font-weight:500; color:#374151; margin-bottom:6px; }
.fg .req { color:var(--red); margin-left:2px; }
.fi { width:100%; padding:9px 12px; border:0.5px solid #d1d5db; border-radius:8px; background:#fff; color:#111827; font-size:13px; outline:none; font-family:inherit; transition:border-color .12s; }
.fi:focus { border-color:var(--teal); box-shadow:0 0 0 3px rgba(29,158,117,.08); }
.fi.hidden { display:none; }
.fi.mt { margin-top:8px; }
.frow { display:grid; gap:12px; }
.fr2  { grid-template-columns:1fr 1fr; }
.fr3  { grid-template-columns:1fr 1fr 1fr; }
.fdiv { border:none; border-top:0.5px solid #f3f4f6; margin:18px 0; }
.fsec { font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:#9ca3af; margin-bottom:12px; }

@media(max-width:600px) { .fr2,.fr3{grid-template-columns:1fr} .lp{padding:16px} }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="lp">


    
    <div class="lp-header">
        <div>
            <div class="lp-title">Planning des Formations</div>
            <div class="lp-sub">Semaine du <?php echo e($debutSem->locale('fr')->translatedFormat('d F')); ?> au <?php echo e($finSem->locale('fr')->translatedFormat('d F Y')); ?></div>
        </div>
        <div class="lp-acts">
            <div class="lp-toggle">
                <a href="<?php echo e(route('lms.index')); ?>">Liste</a>
                <a href="<?php echo e(route('lms.planning')); ?>" class="active">Planning</a>
            </div>
            <a href="<?php echo e(route('referentiel.index')); ?>" class="btn-lp btn-ghost">
                 Referentiel
            </a>
            <a href="<?php echo e(route('lms.exportPdf')); ?>" class="btn-lp btn-ghost">
                Exporter PDF
            </a>
            <button class="btn-lp btn-main" onclick="openModal()">
                <i class="fas fa-plus"></i> Ajouter formation
            </button>
        </div>
    </div>

    
    <form method="GET" action="<?php echo e(route('lms.planning')); ?>" class="lp-bar">
        <div style="display:flex;align-items:center;gap:6px;">
            <a href="<?php echo e(route('lms.planning', ['semaine'=>$semaine-1,'annee'=>$annee,'formation'=>request('formation'),'presence'=>request('presence')])); ?>"
               class="lp-week-btn">
                <i class="fas fa-chevron-left" style="font-size:11px;"></i> Precedente
            </a>
            <div class="lp-week-label">Semaine <?php echo e($semaine); ?> &mdash; <?php echo e($annee); ?></div>
            <a href="<?php echo e(route('lms.planning', ['semaine'=>$semaine+1,'annee'=>$annee,'formation'=>request('formation'),'presence'=>request('presence')])); ?>"
               class="lp-week-btn">
                Suivante <i class="fas fa-chevron-right" style="font-size:11px;"></i>
            </a>
        </div>

        <select name="formation" class="lp-sel" onchange="this.form.submit()">
            <option value="">Toutes les formations</option>
            <?php $__currentLoopData = \App\Models\Formation::getTitres(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($t); ?>" <?php echo e(request('formation')===$t?'selected':''); ?>><?php echo e($t); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <select name="presence" class="lp-sel" onchange="this.form.submit()">
            <option value="">Tous les employes</option>
            <option value="present" <?php echo e(request('presence')==='present'?'selected':''); ?>>Avec formation</option>
            <option value="absent"  <?php echo e(request('presence')==='absent' ?'selected':''); ?>>Sans formation</option>
        </select>

        <?php if(request()->hasAny(['formation','presence'])): ?>
        <a href="<?php echo e(route('lms.planning', ['semaine'=>$semaine,'annee'=>$annee])); ?>"
           class="btn-lp btn-ghost" style="padding:7px 12px;height:34px;">
            <i class="fas fa-times"></i>
        </a>
        <?php endif; ?>

        <input type="hidden" name="semaine" value="<?php echo e($semaine); ?>">
        <input type="hidden" name="annee"   value="<?php echo e($annee); ?>">
    </form>

    
    <div class="lp-card">
        <div class="lp-scroll">
            <table class="pg-table">
                <thead>
                    <tr>
                        <th class="col-emp">Employe</th>
                        <th class="col-form">Formation</th>
                        <?php $__currentLoopData = $joursSemaine; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isToday = $jour->isToday();
                                // Jour en français : Lun, Mar, Mer, Jeu, Ven, Sam, Dim
                                $joursFr = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
                                $dowFr   = $joursFr[$jour->dayOfWeekIso - 1] ?? $jour->format('D');
                            ?>
                            <th class="col-day <?php echo e($isToday ? 'th-today' : ''); ?>">
                                <span class="th-num"><?php echo e($jour->format('d')); ?></span>
                                <span class="th-day"><?php echo e($dowFr); ?></span>
                            </th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $avC = [
                        ['#1D9E75','#E1F5EE'],['#378ADD','#E6F1FB'],['#D85A30','#FAECE7'],
                        ['#BA7517','#FAEEDA'],['#7F77DD','#EEEDFE'],['#D4537E','#FBEAF0'],
                        ['#888780','#F1EFE8'],
                    ];
                ?>
                <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $c       = $avC[$idx % 7];
                        $prenom  = $emp->prenom ?? $emp->first_name ?? '';
                        $nom     = $emp->nom    ?? $emp->last_name  ?? $emp->name ?? '';
                        $full    = trim("$prenom $nom") ?: '—';
                        $ini     = strtoupper(mb_substr($prenom?:$nom,0,1).mb_substr($nom,0,1));
                        $dept    = $emp->getAttribute('dept_name') ?? $emp->dept_name ?? '—';
                        $empForms = $formationsSemaine->where('employee_id', $emp->id);
                        $mainF   = $empForms->first()?->titre ?? '—';
                    ?>
                    <tr>
                        <td>
                            <div class="pg-emp">
                                <div class="pg-av" style="background:<?php echo e($c[1]); ?>;color:<?php echo e($c[0]); ?>;"><?php echo e($ini); ?></div>
                                <div>
                                    <div class="pg-name"><?php echo e($full); ?></div>
                                    <div class="pg-dept"><?php echo e($dept); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><div class="pg-form-cell"><?php echo e($mainF); ?></div></td>
                        <?php $__currentLoopData = $joursSemaine; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $dateStr = $jour->format('Y-m-d');
                                $session = $empForms->first(fn($f) => $f->date->format('Y-m-d') === $dateStr);
                            ?>
                            <td>
                                <div class="pg-day">
                                    <?php if($session): ?>
                                        <?php
                                            $hDeb = substr($session->heure_debut, 0, 5);
                                            $hFin = substr($session->heure_fin,   0, 5);
                                        ?>
                                        <span class="pg-session"
                                              title="<?php echo e($session->titre); ?> — <?php echo e($hDeb); ?> à <?php echo e($hFin); ?>"
                                              onclick='prefillModal(<?php echo e($emp->id); ?>, "<?php echo e($dateStr); ?>", <?php echo json_encode($session, 15, 512) ?>, <?php echo e($emp->department_id ?? 'null'); ?>)'>
                                            <span class="pg-sess-titre"><?php echo e(Str::limit($session->titre, 14)); ?></span>
                                            <span class="pg-sess-heure"><?php echo e($hDeb); ?> – <?php echo e($hFin); ?></span>
                                        </span>
                                    <?php else: ?>
                                        <button type="button" class="pg-create"
                                                onclick='prefillModal(<?php echo e($emp->id); ?>, "<?php echo e($dateStr); ?>", null, <?php echo e($emp->department_id ?? 'null'); ?>)'>
                                            <i class="fas fa-plus" style="font-size:10px;"></i> Créer
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="<?php echo e(2+7); ?>">
                            <div class="lp-empty">
                                <i class="fas fa-calendar-times"></i>
                                <p>Aucun employe trouve pour ce filtre</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>


<div class="lms-ov" id="lmsOv" onclick="backdropClose(event)">
<div class="lms-modal" id="lmsModal">

    <div class="m-head">
        <div class="m-title">
            <div class="m-icon"><i class="fas fa-graduation-cap"></i></div>
            <span id="mTitle">Ajouter une formation</span>
        </div>
        <button class="m-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
    </div>

    <form id="lmsForm" action="<?php echo e(route('lms.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="_method" id="fMethod" value="POST">

        <div class="m-body">

            <div class="fsec">Affectation</div>
            <div class="frow fr2">
                <div class="fg">
                    <label>Departement <span class="req">*</span></label>
                    <select name="departement_id" id="fDept" class="fi" required onchange="loadEmployees(this.value)">
                        <option value="">Choisir un departement</option>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($d->id); ?>"><?php echo e($d->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="fg">
                    <label>Employe <span class="req">*</span></label>
                    <select name="employee_id" id="fEmp" class="fi" required disabled>
                        <option value="">Choisir d'abord un departement</option>
                    </select>
                </div>
            </div>

            <hr class="fdiv">
            <div class="fsec">
                Contenu de la formation
                <a href="<?php echo e(route('referentiel.index')); ?>" target="_blank"
                   style="font-size:11px;color:var(--teal);text-decoration:none;margin-left:10px;font-weight:400;text-transform:none;letter-spacing:0;">
                    <i class="fas fa-external-link-alt" style="font-size:10px;"></i> Gerer le referentiel
                </a>
            </div>

            <div class="fg">
                <label>Formation <span class="req">*</span></label>
                <select name="titre" id="fTitre" class="fi" required>
                    <option value="">Chargement...</option>
                </select>
                <input type="text" name="titre_libre" id="fTitreLib" class="fi mt hidden" placeholder="Nom de la formation...">
            </div>

            <div class="frow fr2">
                <div class="fg">
                    <label>Formateur <span class="req">*</span></label>
                    <select name="formateur" id="fFormateur" class="fi" required>
                        <option value="">Chargement...</option>
                    </select>
                    <input type="text" name="formateur_libre" id="fFormateurLib" class="fi mt hidden" placeholder="Nom du formateur...">
                </div>
                <div class="fg">
                    <label>Organisme <span class="req">*</span></label>
                    <select name="organisme" id="fOrganisme" class="fi" required>
                        <option value="">Chargement...</option>
                    </select>
                    <input type="text" name="organisme_libre" id="fOrganismeLib" class="fi mt hidden" placeholder="Nom de l'organisme...">
                </div>
            </div>

            <hr class="fdiv">
            <div class="fsec">Planification</div>

            <div class="frow fr3">
                <div class="fg">
                    <label>Date <span class="req">*</span></label>
                    <input type="date" name="date" id="fDate" class="fi" required>
                </div>
                <div class="fg">
                    <label>Heure debut</label>
                    <input type="time" name="heure_debut" id="fDebut" class="fi" required value="08:00">
                </div>
                <div class="fg">
                    <label>Heure fin</label>
                    <input type="time" name="heure_fin" id="fFin" class="fi" required value="17:00">
                </div>
            </div>

            <div class="fg" style="margin-bottom:0">
                <label>Statut</label>
                <select name="statut" id="fStatut" class="fi">
                    <?php $__currentLoopData = \App\Models\Formation::STATUTS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>" <?php echo e($s==='Planifiee'?'selected':''); ?>><?php echo e($s); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

        </div>

        <div class="m-foot">
            <button type="button" class="btn-lp btn-ghost" onclick="closeModal()">Annuler</button>
            <button type="submit" class="btn-lp btn-main">
                <i class="fas fa-check"></i> <span id="mSubmit">Enregistrer</span>
            </button>
        </div>
    </form>

</div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
/* ─── Modal open/close ─── */
function openModal()  { resetModal(); document.getElementById('lmsOv').classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal() { document.getElementById('lmsOv').classList.remove('open'); document.body.style.overflow=''; }
function backdropClose(e) { if(e.target===document.getElementById('lmsOv')) closeModal(); }
document.addEventListener('keydown', e => { if(e.key==='Escape') closeModal(); });

function resetModal() {
    document.getElementById('lmsForm').reset();
    document.getElementById('lmsForm').action = '<?php echo e(route('lms.store')); ?>';
    document.getElementById('fMethod').value   = 'POST';
    document.getElementById('mTitle').textContent  = 'Ajouter une formation';
    document.getElementById('mSubmit').textContent = 'Enregistrer';
    const emp = document.getElementById('fEmp');
    emp.innerHTML = '<option value="">Choisir d\'abord un departement</option>';
    emp.disabled  = true;
    document.getElementById('fDate').value  = new Date().toISOString().slice(0,10);
    document.getElementById('fDebut').value = '08:00';
    document.getElementById('fFin').value   = '17:00';
    loadReferentiel();
}

/* ─── Referentiel : charge depuis la DB ─── */
const rfCache = { formations: null, formateurs: null, organismes: null };

function loadReferentiel() {
    return Promise.all([
        loadSelect('fTitre',    '<?php echo e(route('referentiel.api.formations')); ?>', 'titre', 'formations', 'fTitreLib',    'titre',    'Selectionner une formation'),
        loadSelect('fFormateur','<?php echo e(route('referentiel.api.formateurs')); ?>', 'label', 'formateurs', 'fFormateurLib','formateur','Selectionner un formateur'),
        loadSelect('fOrganisme','<?php echo e(route('referentiel.api.organismes')); ?>', 'nom',   'organismes', 'fOrganismeLib','organisme','Selectionner un organisme'),
    ]);
}

function loadSelect(selId, url, labelKey, cacheKey, freeId, fieldName, placeholder) {
    const sel = document.getElementById(selId);
    if (!sel) return Promise.resolve();

    if (rfCache[cacheKey]) {
        buildOptions(sel, rfCache[cacheKey], labelKey, placeholder, freeId, fieldName);
        return Promise.resolve();
    }

    sel.innerHTML = `<option value="">Chargement...</option>`;
    sel.disabled  = true;

    return fetch(url)
        .then(r => r.json())
        .then(data => { rfCache[cacheKey] = data; buildOptions(sel, data, labelKey, placeholder, freeId, fieldName); sel.disabled = false; })
        .catch(() => { sel.disabled = false; buildOptions(sel, [], labelKey, placeholder, freeId, fieldName); });
}

function buildOptions(sel, data, labelKey, placeholder, freeId, fieldName) {
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    data.forEach(item => {
        const val = item[labelKey] ?? item.titre ?? item.nom ?? item.label ?? '';
        const opt = document.createElement('option');
        opt.value = opt.textContent = val;
        sel.appendChild(opt);
    });
    const autre = document.createElement('option');
    autre.value = '__autre__'; autre.textContent = '— Autre (saisie libre) —';
    sel.appendChild(autre);
    sel.name = fieldName;
    sel.onchange = () => toggleAutre(sel.id, freeId, fieldName);
}

function toggleAutre(selId, inputId, fieldName) {
    const sel = document.getElementById(selId);
    const inp = document.getElementById(inputId);
    if (!sel || !inp) return;
    if (sel.value === '__autre__') {
        inp.classList.remove('hidden'); inp.required = true; inp.focus();
        sel.removeAttribute('name');
    } else {
        inp.classList.add('hidden'); inp.required = false; inp.value = '';
        sel.name = fieldName;
    }
}

function setSelectOrFree(selId, inputId, fieldName, val) {
    const sel = document.getElementById(selId);
    const inp = document.getElementById(inputId);
    if (!sel || !inp || !val) return;

    // Chercher l'option dont la valeur correspond exactement
    const found = Array.from(sel.options).some(o => o.value === val && o.value !== '' && o.value !== '__autre__');

    if (found) {
        sel.value   = val;
        sel.name    = fieldName;
        inp.classList.add('hidden');
        inp.required = false;
        inp.value   = '';
    } else {
        // Valeur non trouvée dans le referentiel → champ libre
        sel.value   = '__autre__';
        sel.removeAttribute('name');
        inp.classList.remove('hidden');
        inp.required = true;
        inp.value   = val;
    }
}

/* ─── AJAX employes ─── */
function loadEmployees(deptId, selectedId = null) {
    const empSel = document.getElementById('fEmp');
    if (!deptId) { empSel.innerHTML = '<option value="">Choisir d\'abord un departement</option>'; empSel.disabled = true; return; }
    empSel.disabled = true;
    empSel.innerHTML = '<option value="">Chargement...</option>';
    fetch(`<?php echo e(route('lms.employeesByDepartment')); ?>?departement_id=${deptId}`)
        .then(r => r.json())
        .then(list => {
            empSel.innerHTML = list.length
                ? list.map(e => {
                    const name = `${e.prenom??e.first_name??''} ${e.nom??e.last_name??e.name??''}`.trim();
                    return `<option value="${e.id}" ${e.id==selectedId?'selected':''}>${name}</option>`;
                  }).join('')
                : '<option value="">Aucun employe</option>';
            empSel.disabled = false;
        })
        .catch(() => { empSel.innerHTML = '<option value="">Erreur</option>'; });
}

/* ─── Pre-remplir depuis la grille ─── */
function prefillModal(empId, dateStr, session, deptId) {
    // Vider le cache pour forcer le rechargement si besoin
    // (important si le referentiel a ete modifie entre temps)

    document.getElementById('fDate').value = dateStr;

    if (session) {
        // Champs simples — avant l'ouverture du modal
        document.getElementById('lmsForm').reset();
        document.getElementById('lmsForm').action = `/lms/${session.id}`;
        document.getElementById('fMethod').value  = 'PUT';
        document.getElementById('mTitle').textContent  = 'Modifier la formation';
        document.getElementById('mSubmit').textContent = 'Mettre à jour';
        document.getElementById('fDate').value    = (session.date  || '').slice(0, 10);
        document.getElementById('fDebut').value   = (session.heure_debut || '').slice(0, 5);
        document.getElementById('fFin').value     = (session.heure_fin   || '').slice(0, 5);
        document.getElementById('fStatut').value  = session.statut || 'Planifiée';

        // Réinitialiser les champs libres
        ['fTitreLib','fFormateurLib','fOrganismeLib'].forEach(id => {
            const el = document.getElementById(id);
            el.classList.add('hidden'); el.required = false; el.value = '';
        });
        // Remettre les names sur les selects
        ['fTitre','fFormateur','fOrganisme'].forEach((id, i) => {
            const fields = ['titre','formateur','organisme'];
            document.getElementById(id).name = fields[i];
        });

        // Réinitialiser employe
        const empSel = document.getElementById('fEmp');
        empSel.innerHTML = '<option value="">Chargement...</option>';
        empSel.disabled = true;

        // Ouvrir le modal immédiatement
        document.getElementById('lmsOv').classList.add('open');
        document.body.style.overflow = 'hidden';

        // Charger le referentiel PUIS remplir les selects
        loadReferentiel().then(() => {
            setSelectOrFree('fTitre',    'fTitreLib',    'titre',     session.titre);
            setSelectOrFree('fFormateur','fFormateurLib','formateur', session.formateur);
            setSelectOrFree('fOrganisme','fOrganismeLib','organisme', session.organisme);
        });

        // Charger département + employé en parallèle
        if (deptId) {
            document.getElementById('fDept').value = deptId;
            loadEmployees(deptId, empId);
        } else if (session.employee_id) {
            // Fallback : chercher le dept via employee_id
            fetch(`<?php echo e(route('lms.employeesByDepartment')); ?>?departement_id=0`)
                .catch(() => {});
        }

    } else {
        // Nouvelle formation : reset complet
        resetModal();
        document.getElementById('fDate').value = dateStr;

        document.getElementById('lmsOv').classList.add('open');
        document.body.style.overflow = 'hidden';

        if (deptId) {
            document.getElementById('fDept').value = deptId;
            loadEmployees(deptId, empId);
        }
    }
}

// Pre-charger le referentiel au chargement de la page
document.addEventListener('DOMContentLoaded', () => loadReferentiel());
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/lms/planning.blade.php ENDPATH**/ ?>