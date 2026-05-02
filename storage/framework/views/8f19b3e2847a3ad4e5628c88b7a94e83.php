

<?php $__env->startSection('title', 'Paramétrage'); ?>
<?php $__env->startSection('page-title', 'Paramétrage'); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── Onglets ──────────────────────────────────────── */
.param-tabs {
    display: flex;
    gap: 4px;
    background: var(--surface-2, #f3f4f6);
    padding: 6px;
    border-radius: 12px;
    margin-bottom: 28px;
    width: fit-content;
}
.param-tab {
    padding: 10px 22px;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    background: transparent;
    color: var(--text-muted, #6b7280);
    transition: all .2s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.param-tab.active {
    background: white;
    color: var(--primary, #0ea5e9);
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}
.param-tab:hover:not(.active) {
    background: rgba(255,255,255,.6);
    color: var(--primary, #0ea5e9);
}

/* ── Panneau ─────────────────────────────────────── */
.param-panel { display: none; }
.param-panel.active { display: block; }

/* ── Layout 2 colonnes ───────────────────────────── */
.param-grid {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 24px;
    align-items: start;
}

/* ── Card de formulaire ──────────────────────────── */
.param-form-card {
    background: white;
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 12px;
    overflow: hidden;
    position: sticky;
    top: 24px;
}
.param-form-card-header {
    padding: 18px 20px;
    border-bottom: 1px solid var(--border, #e5e7eb);
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    display: flex;
    align-items: center;
    gap: 10px;
}
.param-form-card-header.green {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}
.param-form-card-header h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0369a1;
    margin: 0;
}
.param-form-card-header.green h3 { color: #15803d; }
.param-form-card-body { padding: 20px; }

/* ── Card liste ──────────────────────────────────── */
.param-list-card {
    background: white;
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 12px;
    overflow: hidden;
}
.param-list-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border, #e5e7eb);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.param-list-header h3 {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text, #111827);
    margin: 0;
}
.param-count-badge {
    background: var(--primary, #0ea5e9);
    color: white;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 10px;
    border-radius: 20px;
}

/* ── Ligne de liste ──────────────────────────────── */
.param-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border, #e5e7eb);
    transition: background .15s;
}
.param-item:last-child { border-bottom: none; }
.param-item:hover { background: #f9fafb; }
.param-item-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.param-item-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.param-item-icon.blue { background: #e0f2fe; }
.param-item-icon.green { background: #dcfce7; }
.param-item-name {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--text, #111827);
}
.param-item-sub {
    font-size: 0.75rem;
    color: var(--text-muted, #6b7280);
    margin-top: 2px;
}
.param-item-actions {
    display: flex;
    gap: 6px;
}
.btn-icon-sm {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid var(--border, #e5e7eb);
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .15s;
    color: var(--text-muted, #6b7280);
    font-size: 13px;
}
.btn-icon-sm:hover { background: #f3f4f6; color: var(--primary, #0ea5e9); }
.btn-icon-sm.danger:hover { background: #fef2f2; color: #ef4444; border-color: #fecaca; }

/* ── Vide ────────────────────────────────────────── */
.param-empty {
    padding: 48px 20px;
    text-align: center;
    color: var(--text-muted, #6b7280);
}
.param-empty-icon { font-size: 2.5rem; margin-bottom: 10px; }
.param-empty p { font-size: 0.875rem; }

/* ── Form inputs ─────────────────────────────────── */
.param-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-muted, #6b7280);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.param-input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 8px;
    font-size: 0.9rem;
    background: white;
    transition: border-color .2s, box-shadow .2s;
    box-sizing: border-box;
    font-family: inherit;
}
.param-input:focus {
    outline: none;
    border-color: var(--primary, #0ea5e9);
    box-shadow: 0 0 0 3px rgba(14,165,233,.12);
}
.param-input-group { margin-bottom: 16px; }

/* ── Bouton submit ───────────────────────────────── */
.btn-param-submit {
    width: 100%;
    padding: 11px 16px;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-family: inherit;
}
.btn-param-submit.blue {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
    box-shadow: 0 4px 12px rgba(14,165,233,.3);
}
.btn-param-submit.blue:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(14,165,233,.4);
}
.btn-param-submit.green {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    box-shadow: 0 4px 12px rgba(34,197,94,.3);
}
.btn-param-submit.green:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(34,197,94,.4);
}

/* ── Alert ───────────────────────────────────────── */
.param-alert {
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 0.82rem;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.param-alert.success {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #15803d;
}
.param-alert.error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #b91c1c;
}

/* ── Modal édition ───────────────────────────────── */
.param-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 1000;
    background: rgba(0,0,0,.45);
    align-items: center;
    justify-content: center;
}
.param-modal-overlay.open { display: flex; }
.param-modal {
    background: white;
    border-radius: 14px;
    padding: 28px;
    width: 90%;
    max-width: 420px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    animation: modalIn .25s cubic-bezier(0.16,1,0.3,1);
}
@keyframes modalIn {
    from { opacity:0; transform:translateY(20px) scale(.97); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}
.param-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.param-modal-header h3 { font-size: 1rem; font-weight: 700; margin: 0; }
.btn-modal-close {
    background: none;
    border: none;
    font-size: 1.3rem;
    color: var(--text-muted, #6b7280);
    cursor: pointer;
    line-height: 1;
}
.btn-modal-close:hover { color: #ef4444; }

/* ── Recherche ───────────────────────────────────── */
.param-search {
    padding: 8px 14px;
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 8px;
    font-size: 0.8rem;
    outline: none;
    width: 200px;
    font-family: inherit;
}
.param-search:focus { border-color: var(--primary, #0ea5e9); }

/* ── Couleur dept ────────────────────────────────── */
.dept-color-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}
</style>
<?php $__env->stopPush(); ?>


<div class="page-header">
    <div class="page-header-left">
        <h1>Paramétrage</h1>
        <p>Gestion des salles et des départements</p>
    </div>
</div>


<?php if(session('success')): ?>
<div class="param-alert success" style="max-width:600px;margin-bottom:20px">
    ✓ <?php echo e(session('success')); ?>

</div>
<?php endif; ?>
<?php if(session('error')): ?>
<div class="param-alert error" style="max-width:600px;margin-bottom:20px">
    ✗ <?php echo e(session('error')); ?>

</div>
<?php endif; ?>


<div class="param-tabs">
    <button class="param-tab active" onclick="switchTab('rooms', this)">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Salles
        <span style="background:#e0f2fe;color:#0369a1;font-size:0.7rem;font-weight:700;padding:1px 7px;border-radius:10px"><?php echo e($rooms->count()); ?></span>
    </button>
    <button class="param-tab" onclick="switchTab('departments', this)">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        Départements
        <span style="background:#dcfce7;color:#15803d;font-size:0.7rem;font-weight:700;padding:1px 7px;border-radius:10px"><?php echo e($departments->count()); ?></span>
    </button>
</div>


<div id="panel-rooms" class="param-panel active">
    <div class="param-grid">

        
        <div class="param-form-card">
            <div class="param-form-card-header">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#0369a1" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <h3>Nouvelle salle</h3>
            </div>
            <div class="param-form-card-body">
                <form method="POST" action="<?php echo e(route('rooms.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php if($errors->has('name') || $errors->has('department_id')): ?>
                    <div class="param-alert error">
                        ✗ <?php echo e($errors->first()); ?>

                    </div>
                    <?php endif; ?>
                    <div class="param-input-group">
                        <label class="param-label" for="room_name">Nom de la salle</label>
                        <input type="text" id="room_name" name="name"
                               class="param-input <?php echo e($errors->has('name') ? 'border-red-500' : ''); ?>"
                               value="<?php echo e(old('name')); ?>"
                               placeholder="Ex: Salle des urgences A"
                               required>
                    </div>
                    <div class="param-input-group">
                        <label class="param-label" for="room_department">Département</label>
                        <select id="room_department" name="department_id" class="param-input" required>
                            <option value="">Sélectionner un département…</option>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($dept->id); ?>" <?php echo e(old('department_id') == $dept->id ? 'selected' : ''); ?>>
                                    <?php echo e($dept->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php if($departments->isEmpty()): ?>
                        <p style="font-size:0.75rem;color:#f59e0b;margin-top:6px">
                            ⚠️ Créez d'abord un département dans l'onglet "Départements"
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="param-input-group">
                        <label class="param-label" for="room_capacity">Capacité (optionnel)</label>
                        <input type="number" id="room_capacity" name="capacity"
                               class="param-input"
                               value="<?php echo e(old('capacity')); ?>"
                               placeholder="Ex: 12"
                               min="1">
                    </div>
                    <div class="param-input-group">
                        <label class="param-label" for="room_description">Description (optionnel)</label>
                        <textarea id="room_description" name="description"
                                  class="param-input"
                                  rows="2"
                                  placeholder="Ex: Salle équipée d'un défibrillateur…"
                                  style="resize:vertical"><?php echo e(old('description')); ?></textarea>
                    </div>
                    <button type="submit" class="btn-param-submit blue">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Créer la salle
                    </button>
                </form>
            </div>
        </div>

        
        <div class="param-list-card">
            <div class="param-list-header">
                <h3>Toutes les salles <span class="param-count-badge"><?php echo e($rooms->count()); ?></span></h3>
                <input type="text" class="param-search" placeholder="🔍 Rechercher…" oninput="filterList(this, 'rooms-list')">
            </div>

            <div id="rooms-list">
                <?php $__empty_1 = true; $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="param-item" data-search="<?php echo e(strtolower($room->name . ' ' . ($room->department?->name ?? ''))); ?>">
                    <div class="param-item-left">
                        <div class="param-item-icon blue">🏥</div>
                        <div>
                            <div class="param-item-name"><?php echo e($room->name); ?></div>
                            <div class="param-item-sub">
                                <?php echo e($room->department?->name ?? '—'); ?>

                                <?php if(isset($room->capacity) && $room->capacity): ?>
                                    · <?php echo e($room->capacity); ?> places
                                <?php endif; ?>
                                <?php if(isset($room->description) && $room->description): ?>
                                    · <?php echo e(Str::limit($room->description, 40)); ?>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="param-item-actions">
                        <button class="btn-icon-sm" title="Modifier"
                                onclick="openEditRoomModal(<?php echo e($room->id); ?>, '<?php echo e(addslashes($room->name)); ?>', <?php echo e($room->department_id); ?>, <?php echo e($room->capacity ?? 'null'); ?>, '<?php echo e(addslashes($room->description ?? '')); ?>')">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </button>
                        <form method="POST" action="<?php echo e(route('rooms.destroy', $room)); ?>" style="display:inline"
                              onsubmit="return confirm('Supprimer cette salle ?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn-icon-sm danger" title="Supprimer">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                    <path d="M10 11v6M14 11v6"/>
                                    <path d="M9 6V4h6v2"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="param-empty">
                    <div class="param-empty-icon">🏥</div>
                    <p>Aucune salle créée.<br>Utilisez le formulaire pour en ajouter une.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div id="panel-departments" class="param-panel">
    <div class="param-grid">

        
        <div class="param-form-card">
            <div class="param-form-card-header green">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#15803d" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <h3>Nouveau département</h3>
            </div>
            <div class="param-form-card-body">
                <form method="POST" action="<?php echo e(route('departments.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php if($errors->has('dept_name')): ?>
                    <div class="param-alert error">
                        ✗ <?php echo e($errors->first('dept_name')); ?>

                    </div>
                    <?php endif; ?>
                    <div class="param-input-group">
                        <label class="param-label" for="dept_name">Nom du département</label>
                        <input type="text" id="dept_name" name="name"
                               class="param-input"
                               value="<?php echo e(old('name')); ?>"
                               placeholder="Ex: Cardiologie"
                               required>
                    
                    </div>
                   
                    <div class="param-input-group">
                        <label class="param-label" for="dept_chef">Chef de service (optionnel)</label>
                        <input type="text" id="dept_chef" name="chef"
                               class="param-input"
                               value="<?php echo e(old('chef')); ?>"
                               placeholder="Ex: Dr. Martin">
                    </div>
                    <div class="param-input-group">
                        <label class="param-label" for="dept_description">Description (optionnel)</label>
                        <textarea id="dept_description" name="description"
                                  class="param-input"
                                  rows="2"
                                  placeholder="Description du service…"
                                  style="resize:vertical"><?php echo e(old('description')); ?></textarea>
                    </div>
                    <button type="submit" class="btn-param-submit green">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Créer le département
                    </button>
                </form>
            </div>
        </div>

        
        <div class="param-list-card">
            <div class="param-list-header">
                <h3>Tous les départements <span class="param-count-badge" style="background:#22c55e"><?php echo e($departments->count()); ?></span></h3>
                <input type="text" class="param-search" placeholder="🔍 Rechercher…" oninput="filterList(this, 'departments-list')">
            </div>

            <div id="departments-list">
                <?php $__empty_1 = true; $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="param-item" data-search="<?php echo e(strtolower($dept->name . ' ' . ($dept->code ?? '') . ' ' . ($dept->chef ?? ''))); ?>">
                    <div class="param-item-left">
                        <div class="param-item-icon green">
                            <?php if(isset($dept->color) && $dept->color): ?>
                            <span class="dept-color-dot" style="background:<?php echo e($dept->color); ?>;width:18px;height:18px;"></span>
                            <?php else: ?>
                            🏢
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="param-item-name" style="display:flex;align-items:center;gap:8px">
                                <?php echo e($dept->name); ?>

                                <?php if(isset($dept->code) && $dept->code): ?>
                                <span style="background:#f3f4f6;color:#6b7280;font-size:0.65rem;font-weight:700;padding:1px 7px;border-radius:4px;letter-spacing:.04em">
                                    <?php echo e($dept->code); ?>

                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="param-item-sub">
                                <?php if(isset($dept->chef) && $dept->chef): ?> Chef : <?php echo e($dept->chef); ?> · <?php endif; ?>
                                <?php echo e($dept->rooms_count ?? $dept->rooms?->count() ?? 0); ?> salle(s)
                                <?php if(isset($dept->description) && $dept->description): ?>
                                    · <?php echo e(Str::limit($dept->description, 35)); ?>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="param-item-actions">
                        <button class="btn-icon-sm" title="Modifier"
                                onclick="openEditDeptModal(<?php echo e($dept->id); ?>, '<?php echo e(addslashes($dept->name)); ?>', '<?php echo e(addslashes($dept->code ?? '')); ?>', '<?php echo e($dept->color ?? '#0ea5e9'); ?>', '<?php echo e(addslashes($dept->chef ?? '')); ?>', '<?php echo e(addslashes($dept->description ?? '')); ?>')">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </button>
                        <form method="POST" action="<?php echo e(route('departments.destroy', $dept)); ?>" style="display:inline"
                              onsubmit="return confirm('Supprimer ce département ? Les salles associées seront également affectées.')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn-icon-sm danger" title="Supprimer">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                    <path d="M10 11v6M14 11v6"/>
                                    <path d="M9 6V4h6v2"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="param-empty">
                    <div class="param-empty-icon">🏢</div>
                    <p>Aucun département créé.<br>Utilisez le formulaire pour en ajouter un.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="param-modal-overlay" id="editRoomModal">
    <div class="param-modal">
        <div class="param-modal-header">
            <h3>✏️ Modifier la salle</h3>
            <button class="btn-modal-close" onclick="closeModal('editRoomModal')">×</button>
        </div>
        <form id="editRoomForm" method="POST">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="param-input-group">
                <label class="param-label">Nom de la salle</label>
                <input type="text" name="name" id="editRoomName" class="param-input" required>
            </div>
            <div class="param-input-group">
                <label class="param-label">Département</label>
                <select name="department_id" id="editRoomDept" class="param-input" required>
                    <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="param-input-group">
                <label class="param-label">Capacité (optionnel)</label>
                <input type="number" name="capacity" id="editRoomCapacity" class="param-input" min="1" placeholder="Ex: 12">
            </div>
            <div class="param-input-group">
                <label class="param-label">Description (optionnel)</label>
                <textarea name="description" id="editRoomDescription" class="param-input" rows="2" style="resize:vertical"></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                <button type="button" onclick="closeModal('editRoomModal')"
                        style="padding:9px 18px;border:1px solid var(--border);border-radius:8px;background:white;cursor:pointer;font-size:0.875rem;font-family:inherit">
                    Annuler
                </button>
                <button type="submit" class="btn-param-submit blue" style="width:auto;padding:9px 24px">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>


<div class="param-modal-overlay" id="editDeptModal">
    <div class="param-modal">
        <div class="param-modal-header">
            <h3>✏️ Modifier le département</h3>
            <button class="btn-modal-close" onclick="closeModal('editDeptModal')">×</button>
        </div>
        <form id="editDeptForm" method="POST">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="param-input-group">
                <label class="param-label">Nom du département</label>
                <input type="text" name="name" id="editDeptName" class="param-input" required>
            </div>
            <div class="param-input-group">
                <label class="param-label">Code</label>
                <input type="text" name="code" id="editDeptCode" class="param-input" maxlength="10" style="text-transform:uppercase">
            </div>
            <div class="param-input-group">
                <label class="param-label">Couleur</label>
                <input type="color" name="color" id="editDeptColor"
                       style="width:48px;height:40px;border-radius:8px;border:1px solid var(--border);cursor:pointer;padding:2px">
            </div>
            <div class="param-input-group">
                <label class="param-label">Chef de service</label>
                <input type="text" name="chef" id="editDeptChef" class="param-input" placeholder="Ex: Dr. Martin">
            </div>
            <div class="param-input-group">
                <label class="param-label">Description</label>
                <textarea name="description" id="editDeptDescription" class="param-input" rows="2" style="resize:vertical"></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                <button type="button" onclick="closeModal('editDeptModal')"
                        style="padding:9px 18px;border:1px solid var(--border);border-radius:8px;background:white;cursor:pointer;font-size:0.875rem;font-family:inherit">
                    Annuler
                </button>
                <button type="submit" class="btn-param-submit green" style="width:auto;padding:9px 24px">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
// ── Onglets ───────────────────────────────────────
function switchTab(tab, btn) {
    document.querySelectorAll('.param-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.param-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');

    // Garder l'onglet actif dans l'URL
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    window.history.replaceState({}, '', url);
}

// Restaurer onglet depuis l'URL
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const tab    = params.get('tab');
    if (tab === 'departments') {
        document.querySelectorAll('.param-tab')[1].click();
    }
});

// ── Recherche dans liste ──────────────────────────
function filterList(input, listId) {
    const q     = input.value.toLowerCase();
    const items = document.querySelectorAll('#' + listId + ' .param-item');
    items.forEach(item => {
        const match = item.dataset.search.includes(q);
        item.style.display = match ? '' : 'none';
    });
}

// ── Modals ────────────────────────────────────────
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = 'auto';
}
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}

// Fermer au clic en dehors
document.querySelectorAll('.param-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) closeModal(overlay.id);
    });
});

// Fermer avec Échap
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.param-modal-overlay.open').forEach(m => closeModal(m.id));
    }
});

// ── Modal Édition Salle ───────────────────────────
function openEditRoomModal(id, name, deptId, capacity, description) {
    document.getElementById('editRoomForm').action = '/rooms/' + id;
    document.getElementById('editRoomName').value         = name;
    document.getElementById('editRoomDept').value         = deptId;
    document.getElementById('editRoomCapacity').value     = capacity || '';
    document.getElementById('editRoomDescription').value  = description || '';
    openModal('editRoomModal');
}

// ── Modal Édition Département ─────────────────────
function openEditDeptModal(id, name, code, color, chef, description) {
    document.getElementById('editDeptForm').action = '/departments/' + id;
    document.getElementById('editDeptName').value        = name;
    document.getElementById('editDeptCode').value        = code || '';
    document.getElementById('editDeptColor').value       = color || '#0ea5e9';
    document.getElementById('editDeptChef').value        = chef || '';
    document.getElementById('editDeptDescription').value = description || '';
    openModal('editDeptModal');
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/parametrage/index.blade.php ENDPATH**/ ?>