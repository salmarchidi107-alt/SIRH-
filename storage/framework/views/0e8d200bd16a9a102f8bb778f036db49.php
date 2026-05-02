<?php $__env->startSection('title', 'Modifier '.$tenant->name); ?>
<?php $__env->startSection('page-title', 'Modifier le tenant'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="sa-breadcrumb">
        <a href="<?php echo e(route('superadmin.tenants.index')); ?>">Tenants</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        <span style="color:var(--text);font-weight:600;"><?php echo e($tenant->name); ?></span>
    </div>
    <div class="sa-page-title">Modifier le tenant</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('superadmin.tenants.update', $tenant)); ?>" enctype="multipart/form-data">
<?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

<div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:20px;align-items:start;">
    <div>
        <div class="sa-card" style="margin-bottom:16px;">
            <div class="sa-card-header"><div class="sa-card-title">Identité</div></div>
            <div class="sa-card-body" style="display:flex;flex-direction:column;gap:0;">

                <div class="sa-field">
                    <label class="sa-label">Nom de la société *</label>
                    <input type="text" name="company_name" id="company-name" class="sa-input"
                           value="<?php echo e(old('company_name',$tenant->name)); ?>" oninput="updatePreview()" required>
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
                               value="<?php echo e(old('slug',$tenant->slug)); ?>" oninput="updatePreview()" required>
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
                            <option <?php echo e(old('sector',$tenant->sector)===$s?'selected':''); ?>><?php echo e($s); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="sa-field">
                    <label class="sa-label">Logo (vide = conserver l'actuel)</label>
                <?php if($tenant->logo_path): ?>
                    <img src="<?php echo e(asset('storage/' . $tenant->logo_path)); ?>" alt="Logo" class="w-32 h-32 object-cover rounded-lg shadow-md">
                <?php endif; ?>

                    <div class="sa-upload" onclick="document.getElementById('file-input').click()">
                        <div id="upload-text" style="font-size:12px;color:var(--text-muted);">Cliquer pour changer le logo</div>
                    </div>
                    <input type="file" id="file-input" name="logo" accept="image/*" style="display:none" onchange="handleFile(this)">
                </div>

                <div class="sa-field">
                    <label class="sa-label">Couleur principale *</label>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <input type="color" id="brand-color" name="brand_color"
                               value="<?php echo e(old('brand_color',$tenant->brand_color)); ?>" oninput="updatePreview()"
                               style="width:38px;height:34px;padding:2px;cursor:pointer;border:1.5px solid var(--border);border-radius:8px;background:var(--surface);">
                        <input type="text" id="brand-hex" class="sa-input" style="width:100px;font-family:monospace;"
                               value="<?php echo e(old('brand_color',$tenant->brand_color)); ?>" oninput="syncColor()">
                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                            <?php $__currentLoopData = ['#1a8fa5','#4f46e5','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#0f172a']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="sa-swatch <?php echo e(old('brand_color',$tenant->brand_color)===$col?'active':''); ?>"
                                  style="background:<?php echo e($col); ?>;" onclick="setColor('<?php echo e($col); ?>')"></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:10px;">
            <a href="<?php echo e(route('superadmin.tenants.index')); ?>" class="sa-btn sa-btn-ghost">Annuler</a>
            <button type="submit" class="sa-btn sa-btn-primary" style="flex:1;">Enregistrer les modifications</button>
        </div>
    </div>
</div>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function getInitials(name){ return name.trim().split(/\s+/).slice(0,2).map(w=>w[0]||'').join('').toUpperCase()||'?'; }
function updatePreview(){
    const name=document.getElementById('company-name').value||'App';
    const slug=document.getElementById('slug').value||'app';
    const color=document.getElementById('brand-color').value;
    const ini=getInitials(name);
    ['prev-logo','prev-login-logo'].forEach(id=>{document.getElementById(id).textContent=ini;document.getElementById(id).style.background=color;});
    document.getElementById('prev-name').textContent=name;
    document.getElementById('prev-slug').textContent=slug+'.tenantes.io';
    document.getElementById('prev-title').textContent=name;
    document.getElementById('prev-btn').style.background=color;
    document.getElementById('brand-hex').value=color;
}
function syncColor(){
    const hex=document.getElementById('brand-hex').value;
    if(/^#[0-9a-fA-F]{6}$/.test(hex)){document.getElementById('brand-color').value=hex;updatePreview();}
}
function setColor(c){
    document.getElementById('brand-color').value=c;document.getElementById('brand-hex').value=c;
    document.querySelectorAll('.sa-swatch').forEach(s=>s.classList.remove('active'));
    event.target.classList.add('active');updatePreview();
}
function handleFile(input){
    if(!input.files||!input.files[0])return;
    document.getElementById('upload-text').textContent=input.files[0].name+' ✓';
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/superadmin/tenants/edit.blade.php ENDPATH**/ ?>