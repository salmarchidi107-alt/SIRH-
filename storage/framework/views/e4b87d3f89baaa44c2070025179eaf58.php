<?php $__env->startSection('title','Rôles'); ?>
<?php $__env->startSection('page-title','Gestion des rôles'); ?>
<?php $__env->startSection('content'); ?>
<div style="background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;padding:20px;max-width:640px;">
    <div style="font-size:13px;font-weight:500;color:#1e293b;margin-bottom:16px;padding-bottom:10px;border-bottom:0.5px solid #f1f5f9;">Rôles disponibles</div>
    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="display:flex;align-items:flex-start;gap:14px;padding:14px 0;border-bottom:0.5px solid #f8fafc;">
        <div style="width:36px;height:36px;border-radius:9px;background:<?php echo e($role['name']==='superadmin'?'#1e1b4b':($role['name']==='admin'?'#eff6ff':'#f1f5f9')); ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="<?php echo e($role['name']==='superadmin'?'#a5b4fc':($role['name']==='admin'?'#2563eb':'#64748b')); ?>" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <div>
            <div style="font-size:13px;font-weight:500;color:#1e293b;"><?php echo e($role['label']); ?></div>
            <div style="font-size:12px;color:#64748b;margin-top:3px;"><?php echo e($role['description']); ?></div>
            <div style="margin-top:5px;font-size:11px;font-family:monospace;color:#94a3b8;"><?php echo e($role['name']); ?></div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/superadmin/roles/index.blade.php ENDPATH**/ ?>