<?php $__env->startSection('title', 'Créer un tenant'); ?>
<?php $__env->startSection('page-title', 'Créer un tenant'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="sa-breadcrumb">
        <a href="<?php echo e(route('superadmin.tenants.index')); ?>">Tenants</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        <span style="color:var(--text);font-weight:600;">Nouveau</span>
    </div>
    <div class="sa-page-title" style="font-size:clamp(15px,2.5vw,22px);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;">
        Créer un nouvel tenant
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<?php if($errors->any()): ?>
    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:18px;">
        <div style="font-weight:600;color:#dc2626;margin-bottom:6px;">Veuillez corriger les erreurs suivantes :</div>
        <ul style="margin:0;padding-left:18px;color:#b91c1c;font-size:13px;">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?php echo e(route('superadmin.tenants.store')); ?>" enctype="multipart/form-data">
<?php echo csrf_field(); ?>

    
    <div class="sa-card" style="margin-bottom:16px;">
        <div class="sa-card-header"><div class="sa-card-title">Identité de la société</div></div>
        <div class="sa-card-body" style="display:flex;flex-direction:column;gap:0;">

            
            <div class="sa-field">
                <label class="sa-label">Nom de la société *</label>
                <input type="text" name="company_name" id="company-name" class="sa-input"
                       value="<?php echo e(old('company_name')); ?>" required>
                <?php $__errorArgs = ['company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="sa-field">
                <label class="sa-label">Secteur</label>
                <select name="sector" id="sector-select" class="sa-input"
                        onchange="toggleSectorOther(this.value)">
                    <?php $__currentLoopData = ['SaaS / Tech','Finance','Santé','Éducation','Retail','Autre']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option <?php echo e(old('sector') === $s ? 'selected' : ''); ?>><?php echo e($s); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <div id="sector-other-wrap" style="margin-top:8px;display:<?php echo e(old('sector') === 'Autre' ? 'block' : 'none'); ?>;">
                    <input type="text" name="sector_other" class="sa-input"
                           value="<?php echo e(old('sector_other')); ?>"
                           placeholder="Précisez le secteur...">
                </div>
            </div>

            
            <div class="sa-field">
                <label class="sa-label">Région *</label>
                <select name="region" id="region-select" class="sa-input" required
                        onchange="toggleRegionOther(this.value)">
                    <option value="">Choisir une région...</option>
                    <?php $__currentLoopData = [
                        'Casablanca-Settat','Rabat-Salé-Kénitra','Fès-Meknès','Marrakech-Safi',
                        'Béni Mellal-Khénifra','Tanger-Tétouan-Al Hoceïma','Oriental',
                        'Drâa-Tafilalet','Souss-Massa','Guelmim-Oued Noun',
                        'Laâyoune-Sakia El Hamra','Dakhla-Oued Ed-Dahab','Autre'
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option <?php echo e(old('region') == $r ? 'selected' : ''); ?>><?php echo e($r); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <div id="region-other-wrap" style="margin-top:8px;display:<?php echo e(old('region') === 'Autre' ? 'block' : 'none'); ?>;">
                    <input type="text" name="region_other" id="region-other-input" class="sa-input"
                           value="<?php echo e(old('region_other')); ?>"
                           placeholder="Précisez la région...">
                </div>
                <?php $__errorArgs = ['region'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <?php $__errorArgs = ['region_other'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="sa-field">
                <label class="sa-label">Adresse *</label>
                <textarea name="address" class="sa-input" rows="2"
                          placeholder="Ex: 23 Rue Mohammed V, Casablanca" required
                          style="resize:vertical;line-height:1.5;"><?php echo e(old('address')); ?></textarea>
                <?php $__errorArgs = ['address'];
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
                    <label class="sa-label">Téléphone *</label>
                    <input type="tel" name="phone" class="sa-input"
                           value="<?php echo e(old('phone')); ?>" placeholder="+212 6XX XXX XXX" required>
                    <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="sa-label">ICE *
                        <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--text-muted);">(15 chiffres)</span>
                    </label>
                    <input type="text" name="ice" class="sa-input"
                           value="<?php echo e(old('ice')); ?>" placeholder="000000000000000"
                           required maxlength="15" pattern="\d{15}"
                           oninput="this.value=this.value.replace(/\D/g,'')">
                    <?php $__errorArgs = ['ice'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="sa-field">
                <div>
                    <label class="sa-label">Email société *</label>
                    <input type="email" name="email_societe" class="sa-input"
                           value="<?php echo e(old('email_societe')); ?>" placeholder="contact@societe.ma" required>
                    <?php $__errorArgs = ['email_societe'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="sa-label">Site web
                        <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--text-muted);">(optionnel)</span>
                    </label>
                    <input type="url" name="website" class="sa-input"
                           value="<?php echo e(old('website')); ?>" placeholder="https://www.societe.ma">
                    <?php $__errorArgs = ['website'];
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
                    <input type="text" name="last_name" class="sa-input" value="<?php echo e(old('last_name')); ?>" required>
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

    <div style="display:flex;gap:10px;">
        <a href="<?php echo e(route('superadmin.tenants.index')); ?>" class="sa-btn sa-btn-ghost">Annuler</a>
        <button type="submit" class="sa-btn sa-btn-primary" style="flex:1;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Créer le tenant et l'admin
        </button>
    </div>

</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
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

function toggleSectorOther(val) {
    const wrap = document.getElementById('sector-other-wrap');
    if (wrap) wrap.style.display = val === 'Autre' ? 'block' : 'none';
}

function toggleRegionOther(val) {
    const wrap  = document.getElementById('region-other-wrap');
    const input = document.getElementById('region-other-input');
    if (!wrap) return;
    if (val === 'Autre') {
        wrap.style.display = 'block';
        input.required = true;
    } else {
        wrap.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.sa-card {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
}
@media (max-width: 640px) {
    .sa-page-title { font-size: 15px !important; }
    div[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/superadmin/tenants/create.blade.php ENDPATH**/ ?>