<?php $__env->startSection('title', 'Référentiel Formations'); ?>
<?php $__env->startSection('page-title', 'Référentiel Formations'); ?>

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

        <?php
            $categories = $formations->pluck('categorie')->filter()->unique()->sort()->values();
        ?>

        <div class="rf-filter-bar">

            <div class="search-wrap">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" class="fi-search" id="cfSearch"
                       placeholder="Rechercher une formation…"
                       onkeydown="if(event.key==='Enter') filterFormations()">
            </div>
            <button class="btn-search" onclick="filterFormations()">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                Rechercher
            </button>

            <?php if($categories->isNotEmpty()): ?>
            <div class="rf-cat-wrap" id="catWrap">
                <button class="rf-cat-btn" id="catBtn" onclick="toggleCatDropdown()" type="button">
                    <span class="rf-cat-active-dot" id="catDot"></span>
                    <span class="rf-cat-btn-label" id="catBtnLabel">Toutes les catégories</span>
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="rf-cat-dropdown" id="catDropdown">
                    <div class="rf-cat-option selected" data-cat="" onclick="selectCat(this, '', 'Toutes les catégories')">
                        <svg class="rf-cat-option-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Toutes les catégories
                    </div>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rf-cat-option" data-cat="<?php echo e($cat); ?>" onclick="selectCat(this, '<?php echo e(addslashes($cat)); ?>', '<?php echo e(addslashes($cat)); ?>')">
                        <svg class="rf-cat-option-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="opacity:0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <?php echo e($cat); ?>

                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <span class="rf-filter-count" id="cfCount"></span>
        </div>

        <div class="rf-card">
            <table class="rf-tbl" id="tableFormations">
                <thead>
                    <tr>
                        <th>Titre &amp; description</th>
                        <th>Catégorie</th>
                        <th>Durée</th>
                        <th>Type</th>
                        <th>Date création</th>
                        <th>Statut</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody id="tbodyFormations">
                <?php $__empty_1 = true; $__currentLoopData = $formations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $tb = $f->type_badge; ?>
                    <tr data-cat="<?php echo e($f->categorie); ?>" data-titre="<?php echo e(strtolower($f->titre)); ?>">
                        <td class="desc-cell">
                            <div class="desc-title"><?php echo e($f->titre); ?></div>
                            <?php if($f->description): ?>
                                <?php $long = strlen($f->description) > 80; ?>
                                <div class="desc-preview-wrap">
                                    <div class="desc-text-preview" id="desc-prev-<?php echo e($f->id); ?>"><?php echo e($f->description); ?></div>
                                    <?php if($long): ?>
                                        <div class="desc-text-full" id="desc-full-<?php echo e($f->id); ?>"><?php echo e($f->description); ?></div>
                                        <button class="desc-toggle" id="desc-btn-<?php echo e($f->id); ?>"
                                                onclick="toggleDesc(<?php echo e($f->id); ?>)" title="Voir la description complète">
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                            Voir plus
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($f->categorie): ?>
                                <span class="rf-badge" style="background:#f3f4f6;color:#374151;font-size:11px;">
                                    <?php echo e($f->categorie); ?>

                                </span>
                            <?php else: ?>
                                <span style="color:#9ca3af;">—</span>
                            <?php endif; ?>
                        </td>
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
                    <tr id="rowEmpty"><td colspan="7">
                        <div class="rf-empty">
                            <i class="fas fa-book-open"></i>
                            <p>Aucune formation dans le catalogue</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <div class="rf-empty-filter" id="cfEmptyFilter">
                <i class="fas fa-filter"></i>
                <p>Aucune formation ne correspond à ce filtre.</p>
            </div>
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
            <div class="rm-icon" style="background:var(--blue-l, var(--rf-blue-l));color:var(--blue);"><i class="fas fa-book-open"></i></div>
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
                <textarea name="description" id="cfDesc" class="fi" rows="4"
                          placeholder="Objectifs, contenu, prérequis…"
                          oninput="updateDescCounter()"></textarea>
                <div class="desc-counter" id="descCounter">0 caractère</div>
            </div>
            <div class="frow2">
                <div class="fg">
                    <label>Catégorie</label>
                    <input type="text" name="categorie" id="cfCat" class="fi"
                           placeholder="Ex: Sécurité, RH, Technique…"
                           list="cfCatList">
                    <datalist id="cfCatList">
                        <?php $__currentLoopData = $categories ?? $formations->pluck('categorie')->filter()->unique()->sort(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat); ?>">
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </datalist>
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
            <div class="rm-icon" style="background:var(--amber-l, var(--rf-amber-l));color:var(--amber);"><i class="fas fa-building"></i></div>
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
let currentTab = '<?php echo e($onglet); ?>';

function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.rf-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.rf-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('panel-' + tab).classList.add('active');
    document.querySelectorAll('.rf-tab')[['formateurs','formations','organismes'].indexOf(tab)].classList.add('active');
    const labels = { formateurs:'Ajouter un formateur', formations:'Ajouter une formation', organismes:'Ajouter un organisme' };
    document.getElementById('btnAjouterLabel').textContent = labels[tab];
}

function openAddModal() {
    const map = { formateurs:'Formateur', formations:'Formation', organismes:'Organisme' };
    window['openAdd' + map[currentTab]]();
}

function openModal(id)  { document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') ['ovFormateur','ovFormation','ovOrganisme'].forEach(id => closeModal(id));
});

let activeCat = '';

function toggleCatDropdown() {
    const btn      = document.getElementById('catBtn');
    const dropdown = document.getElementById('catDropdown');
    if (!btn || !dropdown) return;
    const isOpen = dropdown.classList.contains('open');
    if (isOpen) {
        closeCatDropdown();
    } else {
        dropdown.classList.add('open');
        btn.classList.add('open');
    }
}

function closeCatDropdown() {
    document.getElementById('catBtn')?.classList.remove('open');
    document.getElementById('catDropdown')?.classList.remove('open');
}

function selectCat(el, cat, label) {
    activeCat = cat;

    document.querySelectorAll('.rf-cat-option').forEach(o => {
        o.classList.remove('selected');
        const check = o.querySelector('.rf-cat-option-check');
        if (check) check.style.opacity = '0';
    });
    el.classList.add('selected');
    const check = el.querySelector('.rf-cat-option-check');
    if (check) check.style.opacity = '1';

    const btnLabel = document.getElementById('catBtnLabel');
    if (btnLabel) btnLabel.textContent = label;
    const dot = document.getElementById('catDot');
    if (dot) dot.classList.toggle('visible', cat !== '');

    closeCatDropdown();
    filterFormations();
}

document.addEventListener('click', e => {
    const wrap = document.getElementById('catWrap');
    if (wrap && !wrap.contains(e.target)) closeCatDropdown();
});

function filterFormations() {
    const search = (document.getElementById('cfSearch')?.value || '').toLowerCase().trim();
    const tbody  = document.getElementById('tbodyFormations');
    if (!tbody) return;

    const rows = tbody.querySelectorAll('tr[data-titre]');
    let visible = 0;

    rows.forEach(row => {
        const rowCat   = (row.dataset.cat   || '').toLowerCase();
        const rowTitre = (row.dataset.titre || '').toLowerCase();

        const catMatch    = !activeCat || rowCat === activeCat.toLowerCase();
        const searchMatch = !search    || rowTitre.includes(search);

        if (catMatch && searchMatch) {
            row.classList.remove('hidden-row');
            visible++;
        } else {
            row.classList.add('hidden-row');
        }
    });

    const counter = document.getElementById('cfCount');
    if (counter) counter.textContent = visible + ' formation' + (visible > 1 ? 's' : '');

    const emptyFilter = document.getElementById('cfEmptyFilter');
    if (emptyFilter) emptyFilter.style.display = (visible === 0 && rows.length > 0) ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', () => filterFormations());

function toggleDesc(id) {
    const full = document.getElementById('desc-full-' + id);
    const btn  = document.getElementById('desc-btn-'  + id);
    const prev = document.getElementById('desc-prev-' + id);
    if (!full || !btn) return;

    const isOpen = full.style.display === 'block';
    if (isOpen) {
        full.style.display = 'none';
        if (prev) prev.style.display = '';
        btn.classList.remove('open');
        btn.innerHTML = `<svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg> Voir plus`;
    } else {
        full.style.display = 'block';
        if (prev) prev.style.display = 'none';
        btn.classList.add('open');
        btn.innerHTML = `<svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg> Réduire`;
    }
}

function updateDescCounter() {
    const ta  = document.getElementById('cfDesc');
    const ctr = document.getElementById('descCounter');
    if (!ta || !ctr) return;
    const n = ta.value.length;
    ctr.textContent = n + ' caractère' + (n > 1 ? 's' : '');
    ctr.style.color = n > 800 ? '#E24B4A' : '#9ca3af';
}

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

function openAddFormation() {
    document.getElementById('formFormation').reset();
    document.getElementById('formFormation').action = '<?php echo e(route('referentiel.formations.store')); ?>';
    document.getElementById('methodFormation').value = 'POST';
    document.getElementById('titleFormation').textContent  = 'Ajouter une formation au catalogue';
    document.getElementById('submitFormation').textContent = 'Enregistrer';
    document.getElementById('cfDate').value = new Date().toISOString().slice(0,10);
    updateDescCounter();
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
    updateDescCounter();
    openModal('ovFormation');
}

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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/referentiel/index.blade.php ENDPATH**/ ?>