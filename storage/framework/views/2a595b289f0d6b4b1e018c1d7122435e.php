<?php $__env->startSection('title', 'Créer un tenant'); ?>
<?php $__env->startSection('page-title', 'Créer un tenant'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="sa-breadcrumb">
        <a href="<?php echo e(route('superadmin.tenants.index')); ?>">Tenants</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        <span style="color:var(--text);font-weight:600;">Nouveau</span>
    </div>
    <div class="sa-page-title">Créer un nouvel tenant</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('superadmin.tenants.store')); ?>" enctype="multipart/form-data">
<?php echo csrf_field(); ?>
<div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:20px;align-items:start;">

    
    <div>
        
        <div class="sa-card" style="margin-bottom:16px;">
            <div class="sa-card-header"><div class="sa-card-title">Identité de la société</div></div>
            <div class="sa-card-body" style="display:flex;flex-direction:column;gap:0;">

                <div class="sa-field">
                    <label class="sa-label">Nom de la société *</label>
                    <input type="text" name="company_name" id="company-name" class="sa-input"
                           value="<?php echo e(old('company_name')); ?>" oninput="updatePreview()" required>
                    <?php $__errorArgs = ['company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="sa-field">
                    <div>
                        <label class="sa-label">Slug URL *</label>
                        <input type="text" name="slug" id="slug" class="sa-input"
                               value="<?php echo e(old('slug')); ?>" oninput="updatePreview()" required pattern="[a-z0-9\-]+">
                        <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="sa-label">Secteur</label>
                        <select name="sector" class="sa-input">
                            <?php $__currentLoopData = ['SaaS / Tech','Finance','Santé','Éducation','Retail','Autre']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option <?php echo e(old('sector')===$s?'selected':''); ?>><?php echo e($s); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <div class="sa-field">
            <label class="sa-label">Région *</label>
            <select name="region" class="sa-input" required>
                <option value="">Choisir une région...</option>
                <?php $__currentLoopData = [
                    'Casablanca-Settat',
                    'Rabat-Salé-Kénitra',
                    'Fès-Meknès',
                    'Marrakech-Safi',
                    'Béni Mellal-Khénifra',
                    'Tanger-Tétouan-Al Hoceïma',
                    'Oriental',
                    'Drâa-Tafilalet',
                    'Souss-Massa',
                    'Guelmim-Oued Noun',
                    'Laâyoune-Sakia El Hamra',
                    'Dakhla-Oued Ed-Dahab'
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option <?php echo e(old('region') == $r ? 'selected' : ''); ?>><?php echo e($r); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['region'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="sa-field">
            <label class="sa-label">Logo de la société</label>
                    <div class="sa-upload" onclick="document.getElementById('file-input').click()">
                        <div id="logo-preview-icon" style="font-size:26px;margin-bottom:6px;">📁</div>
                        <div id="upload-text" style="font-size:12px;color:var(--text-muted);">Glisser-déposer le logo ou cliquer</div>
                        <div style="font-size:11px;color:var(--text-light);margin-top:3px;">PNG, SVG, JPG · Max 2 Mo</div>
                    </div>
                    <input type="file" id="file-input" name="logo" accept="image/*" style="display:none" onchange="handleFile(this)">
                    <?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="sa-field">
                    <label class="sa-label">Couleur principale *</label>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <input type="color" id="brand-color" name="brand_color" value="<?php echo e(old('brand_color','#1a8fa5')); ?>"
                               oninput="updatePreview()" style="width:38px;height:34px;padding:2px;cursor:pointer;border:1.5px solid var(--border);border-radius:8px;background:var(--surface);">
                        <input type="text" id="brand-hex" class="sa-input" style="width:100px;font-family:monospace;"
                               value="<?php echo e(old('brand_color','#1a8fa5')); ?>" oninput="syncColor('brand')">
                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                            <?php $__currentLoopData = ['#1a8fa5','#4f46e5','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#0f172a']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="sa-swatch <?php echo e(old('brand_color','#1a8fa5')===$col?'active':''); ?>"
                                  style="background:<?php echo e($col); ?>;" onclick="setColor('brand','<?php echo e($col); ?>', this)"></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php $__errorArgs = ['brand_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="sa-field">
                    <label class="sa-label">Couleur de la sidebar *</label>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <input type="color" id="sidebar-color" name="sidebar_color" value="<?php echo e(old('sidebar_color','#0d2137')); ?>"
                               oninput="updatePreview()" style="width:38px;height:34px;padding:2px;cursor:pointer;border:1.5px solid var(--border);border-radius:8px;background:var(--surface);">
                        <input type="text" id="sidebar-hex" class="sa-input" style="width:100px;font-family:monospace;"
                               value="<?php echo e(old('sidebar_color','#0d2137')); ?>" oninput="syncColor('sidebar')">
                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                            <?php $__currentLoopData = ['#0d2137','#1e1b4b','#14532d','#1c1917','#1e3a5f','#312e81','#134e4a','#1a1a2e']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="sa-swatch sidebar-swatch <?php echo e(old('sidebar_color','#0d2137')===$col?'active':''); ?>"
                                  style="background:<?php echo e($col); ?>;" onclick="setColor('sidebar','<?php echo e($col); ?>', this)"></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php $__errorArgs = ['sidebar_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                

            </div>
        </div>

        
        <div class="sa-card" style="margin-bottom:16px;">
            <div class="sa-card-header"><div class="sa-card-title">Compte admin principal</div></div>
            <div class="sa-card-body" style="display:flex;flex-direction:column;gap:0;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="sa-field">
                    <div>
                        <label class="sa-label">Prénom *</label>
                        <input type="text" name="first_name" class="sa-input" value="<?php echo e(old('first_name')); ?>" required>
                        <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="sa-label">Nom *</label>
                        <input type="text" name="last_name" class="sa-input" value="<?php echo e(old('last_name',)); ?>" required>
                        <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                <div class="sa-field">
                    <label class="sa-label">Email admin *</label>
                    <input type="email" name="admin_email" class="sa-input" value="<?php echo e(old('admin_email')); ?>" required>
                    <?php $__errorArgs = ['admin_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="sa-field">
                    <label class="sa-label">Mot de passe temporaire *</label>
                    <input type="password" name="temp_password" class="sa-input" required minlength="8">
                    <?php $__errorArgs = ['temp_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        
        <div class="sa-card" style="margin-bottom:20px;">
            <div class="sa-card-header"><div class="sa-card-title">Plan & Configuration</div></div>
            <div class="sa-card-body" style="display:flex;flex-direction:column;gap:0;">
                <div class="sa-field">
                    <label class="sa-label">Plan</label>
                    <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="sa-radio-opt <?php echo e(old('plan','starter')===$plan->value?'selected':''); ?>"
                         onclick="selectPlan(this,'<?php echo e($plan->value); ?>')">
                        <div class="sa-radio-dot <?php echo e(old('plan','starter')===$plan->value?'on':''); ?>"></div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:var(--text);"><?php echo e($plan->label()); ?></div>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                <?php if($plan->value==='starter'): ?> Petites équipes (jusqu'à 50 utilisateurs)
                                <?php elseif($plan->value==='pro'): ?> Équipes moyennes (500 utilisateurs)
                                <?php else: ?> Illimité  <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <input type="hidden" name="plan" id="plan-input" value="<?php echo e(old('plan','starter')); ?>">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="sa-field">
                    <div>
                        <label class="sa-label">Statut initial</label>
                        <select name="status" class="sa-input">
                            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s->value); ?>" <?php echo e(old('status','trial')===$s->value?'selected':''); ?>><?php echo e($s->label()); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:10px;">
            <a href="<?php echo e(route('superadmin.tenants.index')); ?>" class="sa-btn sa-btn-ghost">Annuler</a>
            <button type="submit" class="sa-btn sa-btn-primary" style="flex:1;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Créer le tenant et l'admin
            </button>
        </div>
    </div>

    
    <div>
        <div class="sa-card" style="margin-bottom:16px;">
            <div class="sa-card-header"><div class="sa-card-title">Aperçu en temps réel</div></div>
            <div style="padding:16px;">
                
                <div class="sa-preview-sidebar" id="prev-sidebar">
                    <div class="sa-preview-topbar">
                        <div class="sa-preview-logo" id="prev-nav-logo" style="background:#1a8fa5;">T</div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#fff;" id="prev-nav-name">Nouveau Tenants</div>
                            <div style="font-size:10px;color:rgba(255,255,255,.35);font-family:monospace;" id="prev-slug">nouveau.tenantes.io</div>
                        </div>
                    </div>
                    <div class="sa-preview-item act">Tableau de bord</div>
                    <div class="sa-preview-item">Liste du Personnel</div>
                    <div class="sa-preview-item">Planning</div>
                </div>

                <div style="margin-top:12px;">
                    <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;font-weight:700;margin-bottom:8px;">Page de connexion</div>
                    <div class="sa-preview-login">
                        <div class="sa-preview-login-logo" id="prev-logo-big" style="background:#1a8fa5;">A</div>
                        <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:2px;" id="prev-title">App</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:10px;">Connectez-vous à votre espace</div>
                        <div class="sa-fake-input"></div>
                        <div class="sa-fake-input"></div>
                        <div class="sa-fake-btn" id="prev-btn" style="background:#1a8fa5;">Se connecter</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sa-card">
            <div class="sa-card-header"><div class="sa-card-title">Ce que reçoit l'admin</div></div>
            <div style="padding:14px 16px;display:flex;flex-direction:column;gap:6px;">
                <?php $__currentLoopData = [
                    ['#818cf8', "Email de bienvenue avec lien d'activation"],
                    ['#818cf8', 'URL de connexion dédiée'],
                    ['#818cf8', 'Mot de passe temporaire à changer à la 1ère connexion'],
                    ['#818cf8', 'Accès total à son espace'],
                    ['#f59e0b', 'Ne voit PAS les autres tenants'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$dot, $text]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="display:flex;align-items:flex-start;gap:8px;padding:8px;background:var(--surface-2);border-radius:8px;">
                    <div style="width:6px;height:6px;border-radius:50%;background:<?php echo e($dot); ?>;flex-shrink:0;margin-top:4px;"></div>
                    <div style="font-size:12px;color:var(--text-muted);line-height:1.5;"><?php echo e($text); ?></div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div style="padding:0 16px 16px;">
                <div style="background:var(--surface-2);border-radius:8px;padding:12px;">
                    <div style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Isolation garantie</div>
                    <div style="display:flex;flex-wrap:wrap;gap:5px;">
                        <?php $__currentLoopData = ['DB propre','Logo custom','Domaine dédié','Auth séparée']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="sa-tag">
                            <span style="width:5px;height:5px;border-radius:50%;background:var(--success);"></span><?php echo e($tag); ?>

                        </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function getInitials(name) {
    return name.trim().split(/\s+/).slice(0, 2).map(w => w[0] || '').join('').toUpperCase() || '?';
}

function updatePreview() {
    const name    = document.getElementById('company-name').value || 'App';
    const slug    = document.getElementById('slug').value || 'app';
    const brand   = document.getElementById('brand-color').value || '#1a8fa5';
    const sidebar = document.getElementById('sidebar-color')?.value || '#0d2137';
    const ini     = getInitials(name);

    // ── Logos : initiales + couleur principale ──
    ['prev-nav-logo', 'prev-logo-big'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = ini;
            el.style.setProperty('background', brand, 'important');
        }
    });

    // ── Textes ──
    const navName = document.getElementById('prev-nav-name');
    const prevSlug = document.getElementById('prev-slug');
    const prevTitle = document.getElementById('prev-title');
    if (navName)   navName.textContent  = name;
    if (prevSlug)  prevSlug.textContent = slug + '.hospitalrh.test';
    if (prevTitle) prevTitle.textContent = name;

    // ── Bouton login = couleur principale ──
    const btn = document.getElementById('prev-btn');
    if (btn) btn.style.setProperty('background', brand, 'important');

    // ── Sidebar preview = couleur sidebar ──
    const sidebarEl = document.getElementById('prev-sidebar');
    if (sidebarEl) sidebarEl.style.setProperty('background', sidebar, 'important');

    // ── Sync hex inputs ──
    const brandHex   = document.getElementById('brand-hex');
    const sidebarHex = document.getElementById('sidebar-hex');
    if (brandHex)   brandHex.value   = brand;
    if (sidebarHex) sidebarHex.value = sidebar;
}

function syncColor(type) {
    const hex = document.getElementById(type + '-hex').value;
    if (/^#[0-9a-fA-F]{6}$/.test(hex)) {
        document.getElementById(type + '-color').value = hex;
        updatePreview();
    }
}

function setColor(type, color, el) {
    document.getElementById(type + '-color').value = color;
    document.getElementById(type + '-hex').value   = color;

    // Retirer active uniquement sur les swatches du même groupe
    const selector = type === 'sidebar' ? '.sidebar-swatch' : '.sa-swatch:not(.sidebar-swatch)';
    document.querySelectorAll(selector).forEach(s => s.classList.remove('active'));
    el.classList.add('active');
    updatePreview();
}

function handleFile(input) {
    if (!input.files || !input.files[0]) return;
    const r = new FileReader();
    r.onload = e => {
        document.getElementById('logo-preview-icon').innerHTML =
            `<img src="${e.target.result}" style="width:40px;height:40px;object-fit:contain;border-radius:8px;">`;
        document.getElementById('upload-text').textContent = input.files[0].name + ' ✓';
    };
    r.readAsDataURL(input.files[0]);
}

function selectPlan(el, val) {
    document.querySelectorAll('.sa-radio-opt').forEach(o => {
        o.classList.remove('selected');
        o.querySelector('.sa-radio-dot').classList.remove('on');
    });
    el.classList.add('selected');
    el.querySelector('.sa-radio-dot').classList.add('on');
    document.getElementById('plan-input').value = val;
}

// Lancer la preview au chargement
document.addEventListener('DOMContentLoaded', updatePreview);
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Override forcé pour que le JS puisse changer le background de la sidebar preview */
#prev-sidebar {
    background: #0d2137 !important; /* valeur par défaut, le JS va l'écraser */
    transition: background 0.3s ease;
}
#prev-sidebar * {
    transition: background 0.3s ease, color 0.3s ease;
}
#prev-btn {
    transition: background 0.3s ease !important;
}
#prev-nav-logo, #prev-logo-big {
    transition: background 0.3s ease !important;
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/superadmin/tenants/create.blade.php ENDPATH**/ ?>