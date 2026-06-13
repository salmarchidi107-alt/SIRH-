<?php
    $sub = $sub ?? false;
?>

<div class="perm-row <?php echo e($sub ? 'perm-row--sub' : ''); ?>">

    
    <div class="perm-mod-name">
        <span><?php echo $label; ?></span>
    </div>

    
    <?php $__currentLoopData = ['view', 'create', 'edit', 'delete']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

        <?php if(in_array($perm, $actions)): ?>
            <div class="perm-cell">
                <input
                    type="checkbox"
                    name="permissions[<?php echo e($key); ?>][<?php echo e($perm); ?>]"
                    value="1"
                    <?php echo e(old("permissions.$key.$perm") ? 'checked' : ''); ?>

                >
            </div>
        <?php else: ?>
            <div class="perm-cell">
                <span class="perm-na">—</span>
            </div>
        <?php endif; ?>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</div>
<?php /**PATH C:\Users\HP\SIRH-v2\resources\views/employees/_perm_row.blade.php ENDPATH**/ ?>