<?php $__env->startSection('title', 'LMS — Planning des Formations'); ?>
<?php $__env->startSection('page-title', 'Planning des Formations'); ?>

<?php
    $isEmployee = auth()->user()->isEmployee();
?>

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

            
            <?php if(!$isEmployee): ?>
                <a href="<?php echo e(route('referentiel.index')); ?>" class="btn-lp btn-ghost">Referentiel</a>
                <a href="<?php echo e(route('lms.exportPdf')); ?>" class="btn-lp btn-ghost">Exporter PDF</a>
                <button class="btn-lp btn-main" onclick="openModal()">
                    <i class="fas fa-plus"></i> Ajouter formation
                </button>
            <?php endif; ?>
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

        
        <?php if(!$isEmployee): ?>
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
                        $c        = $avC[$idx % 7];
                        $prenom   = $emp->prenom ?? $emp->first_name ?? '';
                        $nom      = $emp->nom    ?? $emp->last_name  ?? $emp->name ?? '';
                        $full     = trim("$prenom $nom") ?: '—';
                        $ini      = strtoupper(mb_substr($prenom?:$nom,0,1).mb_substr($nom,0,1));
                        $dept     = $emp->getAttribute('dept_name') ?? $emp->dept_name ?? '—';
                        $empForms = $formationsSemaine->where('employee_id', $emp->id);
                        $mainF    = $empForms->first()?->titre ?? '—';
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
                                        
                                        <?php if(!$isEmployee): ?>
                                            <span class="pg-session"
                                                  title="<?php echo e($session->titre); ?> — <?php echo e($hDeb); ?> à <?php echo e($hFin); ?>"
                                                  onclick='prefillModal(<?php echo e($emp->id); ?>, "<?php echo e($dateStr); ?>", <?php echo json_encode($session, 15, 512) ?>, <?php echo e($emp->department_id ?? 'null'); ?>)'>
                                                <span class="pg-sess-titre"><?php echo e(Str::limit($session->titre, 14)); ?></span>
                                                <span class="pg-sess-heure"><?php echo e($hDeb); ?> – <?php echo e($hFin); ?></span>
                                            </span>
                                        <?php else: ?>
                                            
                                            <span class="pg-session" style="cursor:default;"
                                                  title="<?php echo e($session->titre); ?> — <?php echo e($hDeb); ?> à <?php echo e($hFin); ?>">
                                                <span class="pg-sess-titre"><?php echo e(Str::limit($session->titre, 14)); ?></span>
                                                <span class="pg-sess-heure"><?php echo e($hDeb); ?> – <?php echo e($hFin); ?></span>
                                            </span>
                                        <?php endif; ?>

                                    <?php elseif(!$isEmployee): ?>
                                        
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


<?php if(!$isEmployee): ?>
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
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php if(!auth()->user()->isEmployee()): ?>
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

/* ─── Referentiel ─── */
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
    if (rfCache[cacheKey]) { buildOptions(sel, rfCache[cacheKey], labelKey, placeholder, freeId, fieldName); return Promise.resolve(); }
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
    const found = Array.from(sel.options).some(o => o.value === val && o.value !== '' && o.value !== '__autre__');
    if (found) {
        sel.value = val; sel.name = fieldName;
        inp.classList.add('hidden'); inp.required = false; inp.value = '';
    } else {
        sel.value = '__autre__'; sel.removeAttribute('name');
        inp.classList.remove('hidden'); inp.required = true; inp.value = val;
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
    document.getElementById('fDate').value = dateStr;

    if (session) {
        document.getElementById('lmsForm').reset();
        document.getElementById('lmsForm').action = `/lms/${session.id}`;
        document.getElementById('fMethod').value  = 'PUT';
        document.getElementById('mTitle').textContent  = 'Modifier la formation';
        document.getElementById('mSubmit').textContent = 'Mettre à jour';
        document.getElementById('fDate').value    = (session.date  || '').slice(0, 10);
        document.getElementById('fDebut').value   = (session.heure_debut || '').slice(0, 5);
        document.getElementById('fFin').value     = (session.heure_fin   || '').slice(0, 5);
        document.getElementById('fStatut').value  = session.statut || 'Planifiée';

        ['fTitreLib','fFormateurLib','fOrganismeLib'].forEach(id => {
            const el = document.getElementById(id);
            el.classList.add('hidden'); el.required = false; el.value = '';
        });
        ['fTitre','fFormateur','fOrganisme'].forEach((id, i) => {
            document.getElementById(id).name = ['titre','formateur','organisme'][i];
        });

        const empSel = document.getElementById('fEmp');
        empSel.innerHTML = '<option value="">Chargement...</option>';
        empSel.disabled = true;

        document.getElementById('lmsOv').classList.add('open');
        document.body.style.overflow = 'hidden';

        loadReferentiel().then(() => {
            setSelectOrFree('fTitre',    'fTitreLib',    'titre',     session.titre);
            setSelectOrFree('fFormateur','fFormateurLib','formateur', session.formateur);
            setSelectOrFree('fOrganisme','fOrganismeLib','organisme', session.organisme);
        });

        if (deptId) {
            document.getElementById('fDept').value = deptId;
            loadEmployees(deptId, empId);
        }
    } else {
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

document.addEventListener('DOMContentLoaded', () => loadReferentiel());
</script>
<?php endif; ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/lms/planning.blade.php ENDPATH**/ ?>