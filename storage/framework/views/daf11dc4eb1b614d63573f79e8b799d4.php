


<?php $__env->startSection('title', 'Référentiel Formations'); ?>
<?php $__env->startSection('page-title', 'Référentiel Formations'); ?>

<?php $__env->startPush('styles'); ?>
<style>
:root {
    --teal:#1D9E75; --teal-l:#E1F5EE; --teal-d:#085041; --teal-m:#9FE1CB;
    --amber:#BA7517; --amber-l:#FAEEDA;
    --green:#639922; --green-l:#EAF3DE;
    --red:#E24B4A;   --red-l:#FCEBEB;
    --blue:#378ADD;  --blue-l:#E6F1FB;
    --purple:#7F77DD;--purple-l:#EEEDFE;
    --bd:#e5e7eb; --bg2:#f9fafb;
}

.rf { padding:28px 32px; }

/* ── Header ── */
.rf-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:22px; }
.rf-title  { font-size:22px; font-weight:600; color:#111827; }
.rf-sub    { font-size:13px; color:#6b7280; margin-top:3px; }

/* ── Stats ── */
.rf-stats { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:22px; }
.rf-stat  {
    background:#fff; border:0.5px solid var(--bd); border-radius:12px;
    padding:14px 20px; display:flex; align-items:center; gap:14px; min-width:160px;
}
.rf-stat-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.rf-stat-num  { font-size:22px; font-weight:700; color:#111827; line-height:1; }
.rf-stat-lbl  { font-size:12px; color:#6b7280; margin-top:2px; }

/* ── Onglets ── */
.rf-tabs { display:flex; border-bottom:1.5px solid var(--bd); margin-bottom:20px; gap:0; }
.rf-tab  {
    padding:10px 22px; font-size:13px; font-weight:400; color:#6b7280;
    cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-1.5px;
    display:flex; align-items:center; gap:7px; text-decoration:none;
    transition:color .12s, border-color .12s; background:none; border-top:none; border-left:none; border-right:none;
}
.rf-tab:hover { color:#374151; }
.rf-tab.active { color:var(--teal); border-bottom-color:var(--teal); font-weight:500; }
.rf-tab-count { background:#f3f4f6; color:#6b7280; font-size:11px; padding:1px 7px; border-radius:20px; font-weight:500; }
.rf-tab.active .rf-tab-count { background:var(--teal-l); color:var(--teal-d); }

/* ── Panel ── */
.rf-panel { display:none; }
.rf-panel.active { display:block; }

/* ── Table card ── */
.rf-card { background:#fff; border:0.5px solid var(--bd); border-radius:12px; overflow:hidden; }
.rf-tbl  { width:100%; border-collapse:collapse; }
.rf-tbl thead th { padding:11px 16px; font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.07em; background:var(--bg2); border-bottom:0.5px solid var(--bd); text-align:left; white-space:nowrap; }
.rf-tbl tbody td { padding:13px 16px; font-size:13px; color:#374151; border-bottom:0.5px solid #f3f4f6; vertical-align:middle; }
.rf-tbl tbody tr:last-child td { border-bottom:none; }
.rf-tbl tbody tr:hover td { background:#fafafa; }

/* ── Badges ── */
.rf-badge { display:inline-flex; align-items:center; padding:3px 10px; border-radius:20px; font-size:12px; white-space:nowrap; }
.b-interne { background:var(--teal-l);   color:var(--teal-d); }
.b-externe { background:var(--blue-l);   color:#185FA5; }
.b-pres    { background:var(--teal-l);   color:var(--teal-d); }
.b-dist    { background:var(--blue-l);   color:#185FA5; }
.b-mixte   { background:var(--amber-l);  color:var(--amber); }
.b-agree   { background:var(--green-l);  color:var(--green); }
.b-non-agree { background:#f3f4f6;       color:#6b7280; }
.b-actif   { background:var(--teal-l);   color:var(--teal-d); }
.b-inactif { background:var(--red-l);    color:var(--red); }

/* ── Buttons ── */
.btn-rf {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 16px; border-radius:8px; font-size:13px;
    cursor:pointer; border:1px solid transparent; text-decoration:none;
    transition:background .12s; white-space:nowrap;
}
.btn-ghost { background:#fff; border-color:var(--bd); color:#374151; }
.btn-ghost:hover { background:var(--bg2); color:#374151; }
.btn-main  { background:#14B8A6; border-color:#14B8A6; color:#fff; font-weight:500; }
.btn-main:hover { background:#0F9F90; }

.rf-act { width:30px; height:30px; border-radius:7px; border:0.5px solid var(--bd); background:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; color:#6b7280; transition:all .12s; }
.rf-act:hover { background:#f3f4f6; color:#111827; }

/* ── Empty ── */
.rf-empty { text-align:center; padding:52px 20px; color:#9ca3af; }
.rf-empty i { font-size:36px; display:block; margin-bottom:10px; }
.rf-empty p { font-size:13px; margin:0; }

/* ── Durée chip ── */
.dur-chip { background:var(--purple-l); color:var(--purple); font-size:12px; padding:2px 9px; border-radius:20px; font-weight:500; white-space:nowrap; }

/* ══════════ MODAL ══════════ */
.rf-ov { display:none; position:fixed; inset:0; background:rgba(17,24,39,.48); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(2px); }
.rf-ov.open { display:flex; }
.rf-modal { background:#fff; border-radius:14px; border:0.5px solid var(--bd); width:560px; max-width:96vw; max-height:93vh; overflow-y:auto; box-shadow:0 24px 64px rgba(0,0,0,.18); animation:rfIn .18s ease; }
@keyframes rfIn { from{opacity:0;transform:translateY(-12px) scale(.98)} to{opacity:1;transform:none} }

.rm-head { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 16px; border-bottom:0.5px solid #f3f4f6; position:sticky; top:0; background:#fff; z-index:2; border-radius:14px 14px 0 0; }
.rm-title { font-size:16px; font-weight:600; color:#111827; display:flex; align-items:center; gap:10px; }
.rm-icon  { width:34px; height:34px; border-radius:9px; background:var(--teal-l); color:var(--teal); display:flex; align-items:center; justify-content:center; font-size:16px; }
.rm-close { width:30px; height:30px; border-radius:7px; border:0.5px solid var(--bd); background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:15px; }
.rm-close:hover { background:#f3f4f6; color:#374151; }
.rm-body  { padding:22px 24px; }
.rm-foot  { display:flex; justify-content:flex-end; gap:8px; padding:16px 24px; border-top:0.5px solid #f3f4f6; position:sticky; bottom:0; background:#fff; }

.fg { margin-bottom:15px; }
.fg:last-child { margin-bottom:0; }
.fg label { display:block; font-size:13px; font-weight:500; color:#374151; margin-bottom:6px; }
.fg .req  { color:var(--red); margin-left:2px; }
.fi { width:100%; padding:9px 12px; border:0.5px solid #d1d5db; border-radius:8px; background:#fff; color:#111827; font-size:13px; outline:none; font-family:inherit; transition:border-color .12s; }
.fi:focus { border-color:var(--teal); box-shadow:0 0 0 3px rgba(29,158,117,.08); }
.frow2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.fsec  { font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:#9ca3af; margin-bottom:12px; }
.fdiv  { border:none; border-top:0.5px solid #f3f4f6; margin:16px 0; }
.fi-check { display:flex; align-items:center; gap:8px; }
.fi-check input[type=checkbox] { width:16px; height:16px; accent-color:var(--teal); }

@media(max-width:580px) { .frow2{grid-template-columns:1fr} .rf{padding:16px} }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="rf">

    

    
    <div class="rf-header">
        <div>
            <div class="rf-title">Référentiel Formations</div>
            <div class="rf-sub">Gérez formateurs, catalogue de formations et organismes</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <a href="<?php echo e(route('lms.index')); ?>" class="btn-rf btn-ghost">
                <i class="fas fa-arrow-left"></i> Retour au LMS
            </a>
            <button class="btn-rf btn-main" id="btnAjouter" onclick="openAddModal()">
                <i class="fas fa-plus"></i> <span id="btnAjouterLabel">Ajouter</span>
            </button>
        </div>
    </div>

   

    
    <div class="rf-tabs">
        <button class="rf-tab <?php echo e($onglet==='formateurs'?'active':''); ?>" onclick="switchTab('formateurs')">
             Formateurs
            <span class="rf-tab-count"><?php echo e($stats['formateurs']); ?></span>
        </button>
        <button class="rf-tab <?php echo e($onglet==='formations'?'active':''); ?>" onclick="switchTab('formations')">
             Catalogue formations
            <span class="rf-tab-count"><?php echo e($stats['formations']); ?></span>
        </button>
        <button class="rf-tab <?php echo e($onglet==='organismes'?'active':''); ?>" onclick="switchTab('organismes')">
            Organismes
            <span class="rf-tab-count"><?php echo e($stats['organismes']); ?></span>
        </button>
    </div>

    
    <div class="rf-panel <?php echo e($onglet==='formateurs'?'active':''); ?>" id="panel-formateurs">
        <div class="rf-card">
            <table class="rf-tbl">
                <thead>
                    <tr>
                        <th>Nom complet</th>
                        <th>Spécialité</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $formateurs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <div style="font-weight:500;"><?php echo e($f->prenom); ?> <?php echo e($f->nom); ?></div>
                        </td>
                        <td style="color:#6b7280;"><?php echo e($f->specialite ?: '—'); ?></td>
                        <td style="color:#6b7280;"><?php echo e($f->email ?: '—'); ?></td>
                        <td style="color:#6b7280;"><?php echo e($f->telephone ?: '—'); ?></td>
                        <td>
                            <span class="rf-badge <?php echo e($f->type==='interne'?'b-interne':'b-externe'); ?>">
                                <?php echo e(ucfirst($f->type)); ?>

                            </span>
                        </td>
                        <td>
                            <span class="rf-badge <?php echo e($f->actif?'b-actif':'b-inactif'); ?>">
                                <?php echo e($f->actif ? 'Actif' : 'Inactif'); ?>

                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <button class="rf-act" title="Modifier"
                                        onclick='editFormateur(<?php echo json_encode($f, 15, 512) ?>)'>
                                    <i class="fas fa-pen" style="font-size:11px;"></i>
                                </button>
                                <form action="<?php echo e(route('referentiel.formateurs.destroy', $f)); ?>" method="POST"
                                      onsubmit="return confirm('Supprimer ce formateur ?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="rf-act" type="submit" title="Supprimer">
                                        <i class="fas fa-trash-alt" style="font-size:11px;color:var(--red);"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7">
                        <div class="rf-empty">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <p>Aucun formateur enregistré</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="rf-panel <?php echo e($onglet==='formations'?'active':''); ?>" id="panel-formations">
        <div class="rf-card">
            <table class="rf-tbl">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Durée</th>
                        <th>Type</th>
                        <th>Date création</th>
                        <th>Statut</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $formations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $tb = $f->type_badge; ?>
                    <tr>
                        <td>
                            <div style="font-weight:500;"><?php echo e($f->titre); ?></div>
                            <?php if($f->description): ?>
                                <div style="font-size:11px;color:#9ca3af;margin-top:2px;"><?php echo e(Str::limit($f->description,60)); ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="color:#6b7280;"><?php echo e($f->categorie ?: '—'); ?></td>
                        <td><span class="dur-chip"><?php echo e($f->duree_libelle); ?></span></td>
                        <td>
                            <span class="rf-badge" style="background:<?php echo e($tb[1]); ?>;color:<?php echo e($tb[2]); ?>;">
                                <?php echo e($tb[0]); ?>

                            </span>
                        </td>
                        <td style="color:#6b7280;">
                            <?php echo e($f->date_creation ? $f->date_creation->format('d/m/Y') : '—'); ?>

                        </td>
                        <td>
                            <span class="rf-badge <?php echo e($f->actif?'b-actif':'b-inactif'); ?>">
                                <?php echo e($f->actif ? 'Actif' : 'Inactif'); ?>

                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <button class="rf-act" title="Modifier"
                                        onclick='editFormation(<?php echo json_encode($f, 15, 512) ?>)'>
                                    <i class="fas fa-pen" style="font-size:11px;"></i>
                                </button>
                                <form action="<?php echo e(route('referentiel.formations.destroy', $f)); ?>" method="POST"
                                      onsubmit="return confirm('Supprimer cette formation du catalogue ?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="rf-act" type="submit" title="Supprimer">
                                        <i class="fas fa-trash-alt" style="font-size:11px;color:var(--red);"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7">
                        <div class="rf-empty">
                            <i class="fas fa-book-open"></i>
                            <p>Aucune formation dans le catalogue</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="rf-panel <?php echo e($onglet==='organismes'?'active':''); ?>" id="panel-organismes">
        <div class="rf-card">
            <table class="rf-tbl">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Site web</th>
                        <th>Agréé</th>
                        <th>Date création</th>
                        <th>Statut</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $organismes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td style="font-weight:500;"><?php echo e($o->nom); ?></td>
                        <td style="color:#6b7280;"><?php echo e($o->email ?: '—'); ?></td>
                        <td style="color:#6b7280;"><?php echo e($o->telephone ?: '—'); ?></td>
                        <td>
                            <?php if($o->site_web): ?>
                                <a href="<?php echo e($o->site_web); ?>" target="_blank" style="color:var(--blue);font-size:12px;">
                                    <i class="fas fa-external-link-alt" style="font-size:10px;"></i>
                                    <?php echo e(parse_url($o->site_web, PHP_URL_HOST)); ?>

                                </a>
                            <?php else: ?>
                                <span style="color:#9ca3af;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="rf-badge <?php echo e($o->agree?'b-agree':'b-non-agree'); ?>">
                                <?php echo e($o->agree ? 'Agréé' : 'Non agréé'); ?>

                            </span>
                        </td>
                        <td style="color:#6b7280;">
                            <?php echo e($o->date_creation ? $o->date_creation->format('d/m/Y') : '—'); ?>

                        </td>
                        <td>
                            <span class="rf-badge <?php echo e($o->actif?'b-actif':'b-inactif'); ?>">
                                <?php echo e($o->actif ? 'Actif' : 'Inactif'); ?>

                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <button class="rf-act" title="Modifier"
                                        onclick='editOrganisme(<?php echo json_encode($o, 15, 512) ?>)'>
                                    <i class="fas fa-pen" style="font-size:11px;"></i>
                                </button>
                                <form action="<?php echo e(route('referentiel.organismes.destroy', $o)); ?>" method="POST"
                                      onsubmit="return confirm('Supprimer cet organisme ?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="rf-act" type="submit" title="Supprimer">
                                        <i class="fas fa-trash-alt" style="font-size:11px;color:var(--red);"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8">
                        <div class="rf-empty">
                            <i class="fas fa-building"></i>
                            <p>Aucun organisme enregistré</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>



<div class="rf-ov" id="ovFormateur" onclick="if(event.target===this)closeModal('ovFormateur')">
<div class="rf-modal">
    <div class="rm-head">
        <div class="rm-title">
            <div class="rm-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <span id="titleFormateur">Ajouter un formateur</span>
        </div>
        <button class="rm-close" onclick="closeModal('ovFormateur')"><i class="fas fa-times"></i></button>
    </div>
    <form id="formFormateur" action="<?php echo e(route('referentiel.formateurs.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="_method" id="methodFormateur" value="POST">
        <div class="rm-body">
            <div class="fsec">Identité</div>
            <div class="frow2">
                <div class="fg">
                    <label>Prénom <span class="req">*</span></label>
                    <input type="text" name="prenom" id="fPren" class="fi" required placeholder="Prénom">
                </div>
                <div class="fg">
                    <label>Nom <span class="req">*</span></label>
                    <input type="text" name="nom" id="fNom" class="fi" required placeholder="Nom">
                </div>
            </div>
            <div class="frow2">
                <div class="fg">
                    <label>Email</label>
                    <input type="email" name="email" id="fEmail" class="fi" placeholder="email@exemple.com">
                </div>
                <div class="fg">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" id="fTel" class="fi" placeholder="06 XX XX XX XX">
                </div>
            </div>
            <hr class="fdiv">
            <div class="fsec">Profil</div>
            <div class="frow2">
                <div class="fg">
                    <label>Spécialité</label>
                    <input type="text" name="specialite" id="fSpec" class="fi" placeholder="Ex: Sécurité, RH…">
                </div>
                <div class="fg">
                    <label>Type <span class="req">*</span></label>
                    <select name="type" id="fType" class="fi" required>
                        <option value="externe">Externe</option>
                        <option value="interne">Interne</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="rm-foot">
            <button type="button" class="btn-rf btn-ghost" onclick="closeModal('ovFormateur')">Annuler</button>
            <button type="submit" class="btn-rf btn-main">
                <i class="fas fa-check"></i> <span id="submitFormateur">Enregistrer</span>
            </button>
        </div>
    </form>
</div>
</div>


<div class="rf-ov" id="ovFormation" onclick="if(event.target===this)closeModal('ovFormation')">
<div class="rf-modal">
    <div class="rm-head">
        <div class="rm-title">
            <div class="rm-icon" style="background:var(--blue-l);color:var(--blue);"><i class="fas fa-book-open"></i></div>
            <span id="titleFormation">Ajouter une formation</span>
        </div>
        <button class="rm-close" onclick="closeModal('ovFormation')"><i class="fas fa-times"></i></button>
    </div>
    <form id="formFormation" action="<?php echo e(route('referentiel.formations.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="_method" id="methodFormation" value="POST">
        <div class="rm-body">
            <div class="fg">
                <label>Titre <span class="req">*</span></label>
                <input type="text" name="titre" id="cfTitre" class="fi" required placeholder="Intitulé de la formation">
            </div>
            <div class="fg">
                <label>Description</label>
                <textarea name="description" id="cfDesc" class="fi" rows="2" placeholder="Objectifs, contenu…" style="resize:vertical;"></textarea>
            </div>
            <div class="frow2">
                <div class="fg">
                    <label>Catégorie</label>
                    <input type="text" name="categorie" id="cfCat" class="fi" placeholder="Ex: Sécurité, RH, Technique…">
                </div>
                <div class="fg">
                    <label>Durée (heures) <span class="req">*</span></label>
                    <input type="number" name="duree_heures" id="cfDuree" class="fi" required min="1" placeholder="Ex: 8">
                </div>
            </div>
            <div class="frow2">
                <div class="fg">
                    <label>Type <span class="req">*</span></label>
                    <select name="type" id="cfType" class="fi" required>
                        <option value="presentiel">Présentiel</option>
                        <option value="distanciel">Distanciel</option>
                        <option value="mixte">Mixte</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Date de création</label>
                    <input type="date" name="date_creation" id="cfDate" class="fi">
                </div>
            </div>
        </div>
        <div class="rm-foot">
            <button type="button" class="btn-rf btn-ghost" onclick="closeModal('ovFormation')">Annuler</button>
            <button type="submit" class="btn-rf btn-main">
                <i class="fas fa-check"></i> <span id="submitFormation">Enregistrer</span>
            </button>
        </div>
    </form>
</div>
</div>


<div class="rf-ov" id="ovOrganisme" onclick="if(event.target===this)closeModal('ovOrganisme')">
<div class="rf-modal">
    <div class="rm-head">
        <div class="rm-title">
            <div class="rm-icon" style="background:var(--amber-l);color:var(--amber);"><i class="fas fa-building"></i></div>
            <span id="titleOrganisme">Ajouter un organisme</span>
        </div>
        <button class="rm-close" onclick="closeModal('ovOrganisme')"><i class="fas fa-times"></i></button>
    </div>
    <form id="formOrganisme" action="<?php echo e(route('referentiel.organismes.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="_method" id="methodOrganisme" value="POST">
        <div class="rm-body">
            <div class="fg">
                <label>Nom de l'organisme <span class="req">*</span></label>
                <input type="text" name="nom" id="ogNom" class="fi" required placeholder="Nom officiel">
            </div>
            <div class="frow2">
                <div class="fg">
                    <label>Email</label>
                    <input type="email" name="email" id="ogEmail" class="fi" placeholder="contact@organisme.ma">
                </div>
                <div class="fg">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" id="ogTel" class="fi" placeholder="05 XX XX XX XX">
                </div>
            </div>
            <div class="frow2">
                <div class="fg">
                    <label>Site web</label>
                    <input type="url" name="site_web" id="ogSite" class="fi" placeholder="https://…">
                </div>
                <div class="fg">
                    <label>Date de création</label>
                    <input type="date" name="date_creation" id="ogDate" class="fi">
                </div>
            </div>
            <div class="fg">
                <label>Adresse</label>
                <input type="text" name="adresse" id="ogAdr" class="fi" placeholder="Adresse complète">
            </div>
            <div class="fg">
                <label style="margin-bottom:0;">
                    <div class="fi-check">
                        <input type="checkbox" name="agree" id="ogAgree" value="1">
                        <span style="font-size:13px;font-weight:500;color:#374151;">Organisme agréé</span>
                    </div>
                </label>
            </div>
        </div>
        <div class="rm-foot">
            <button type="button" class="btn-rf btn-ghost" onclick="closeModal('ovOrganisme')">Annuler</button>
            <button type="submit" class="btn-rf btn-main">
                <i class="fas fa-check"></i> <span id="submitOrganisme">Enregistrer</span>
            </button>
        </div>
    </form>
</div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
/* ── Onglets ── */
let currentTab = '<?php echo e($onglet); ?>';

function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.rf-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.rf-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('panel-' + tab).classList.add('active');
    document.querySelectorAll('.rf-tab')[['formateurs','formations','organismes'].indexOf(tab)].classList.add('active');
    // Mettre à jour le bouton "Ajouter"
    const labels = { formateurs:'Ajouter un formateur', formations:'Ajouter une formation', organismes:'Ajouter un organisme' };
    document.getElementById('btnAjouterLabel').textContent = labels[tab];
}

function openAddModal() {
    const map = { formateurs:'Formateur', formations:'Formation', organismes:'Organisme' };
    window['openAdd' + map[currentTab]]();
}

/* ── Modal helpers ── */
function openModal(id)  { document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        ['ovFormateur','ovFormation','ovOrganisme'].forEach(id => closeModal(id));
    }
});

/* ══════════ FORMATEUR ══════════ */
function openAddFormateur() {
    document.getElementById('formFormateur').reset();
    document.getElementById('formFormateur').action = '<?php echo e(route('referentiel.formateurs.store')); ?>';
    document.getElementById('methodFormateur').value = 'POST';
    document.getElementById('titleFormateur').textContent  = 'Ajouter un formateur';
    document.getElementById('submitFormateur').textContent = 'Enregistrer';
    openModal('ovFormateur');
}

function editFormateur(f) {
    document.getElementById('formFormateur').action = `/referentiel/formateurs/${f.id}`;
    document.getElementById('methodFormateur').value = 'PUT';
    document.getElementById('titleFormateur').textContent  = 'Modifier le formateur';
    document.getElementById('submitFormateur').textContent = 'Mettre à jour';
    document.getElementById('fPren').value  = f.prenom      || '';
    document.getElementById('fNom').value   = f.nom         || '';
    document.getElementById('fEmail').value = f.email       || '';
    document.getElementById('fTel').value   = f.telephone   || '';
    document.getElementById('fSpec').value  = f.specialite  || '';
    document.getElementById('fType').value  = f.type        || 'externe';
    openModal('ovFormateur');
}

/* ══════════ FORMATION CATALOGUE ══════════ */
function openAddFormation() {
    document.getElementById('formFormation').reset();
    document.getElementById('formFormation').action = '<?php echo e(route('referentiel.formations.store')); ?>';
    document.getElementById('methodFormation').value = 'POST';
    document.getElementById('titleFormation').textContent  = 'Ajouter une formation au catalogue';
    document.getElementById('submitFormation').textContent = 'Enregistrer';
    document.getElementById('cfDate').value = new Date().toISOString().slice(0,10);
    openModal('ovFormation');
}

function editFormation(f) {
    document.getElementById('formFormation').action = `/referentiel/formations/${f.id}`;
    document.getElementById('methodFormation').value = 'PUT';
    document.getElementById('titleFormation').textContent  = 'Modifier la formation';
    document.getElementById('submitFormation').textContent = 'Mettre à jour';
    document.getElementById('cfTitre').value  = f.titre         || '';
    document.getElementById('cfDesc').value   = f.description   || '';
    document.getElementById('cfCat').value    = f.categorie      || '';
    document.getElementById('cfDuree').value  = f.duree_heures  || '';
    document.getElementById('cfType').value   = f.type          || 'presentiel';
    document.getElementById('cfDate').value   = (f.date_creation || '').slice(0,10);
    openModal('ovFormation');
}

/* ══════════ ORGANISME ══════════ */
function openAddOrganisme() {
    document.getElementById('formOrganisme').reset();
    document.getElementById('formOrganisme').action = '<?php echo e(route('referentiel.organismes.store')); ?>';
    document.getElementById('methodOrganisme').value = 'POST';
    document.getElementById('titleOrganisme').textContent  = 'Ajouter un organisme';
    document.getElementById('submitOrganisme').textContent = 'Enregistrer';
    document.getElementById('ogDate').value = new Date().toISOString().slice(0,10);
    openModal('ovOrganisme');
}

function editOrganisme(o) {
    document.getElementById('formOrganisme').action = `/referentiel/organismes/${o.id}`;
    document.getElementById('methodOrganisme').value = 'PUT';
    document.getElementById('titleOrganisme').textContent  = 'Modifier l\'organisme';
    document.getElementById('submitOrganisme').textContent = 'Mettre à jour';
    document.getElementById('ogNom').value   = o.nom           || '';
    document.getElementById('ogEmail').value = o.email         || '';
    document.getElementById('ogTel').value   = o.telephone     || '';
    document.getElementById('ogSite').value  = o.site_web      || '';
    document.getElementById('ogAdr').value   = o.adresse       || '';
    document.getElementById('ogDate').value  = (o.date_creation || '').slice(0,10);
    document.getElementById('ogAgree').checked = !!o.agree;
    openModal('ovOrganisme');
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/referentiel/index.blade.php ENDPATH**/ ?>