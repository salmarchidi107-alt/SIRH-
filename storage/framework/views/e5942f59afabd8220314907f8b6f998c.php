<?php $__env->startSection('title', 'Clients'); ?>

<?php $__env->startSection('content'); ?>
<div class="sa-main">
    <div class="sa-main__head">
        <h1 class="sa-main__title sa-main__title--with-icon">
            <i class="sa-main__title-icon mdi mdi-account-group-outline"></i>
            Clients / Tenants (<?php echo e($clients->total()); ?>)
        </h1>
    </div>

    <div class="sa-main__body">
        <div class="sa-card">
            <div class="sa-card__body">
                <table class="sa-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Slug</th>
                            <th>Sector</th>
                            <th>Users</th>
                            <th>Créé le</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="sa-table__avatar" style="background-color: <?php echo e($client->brand_color ?? '#4f46e5'); ?>;">
                                    <?php echo e($client->initials); ?>

                                </div>
                                <div>
                                    <div class="sa-table__name"><?php echo e($client->name); ?></div>
                                    <div class="sa-table__meta"><?php echo e($client->admin?->name ?? 'No admin'); ?></div>
                                </div>
                            </td>
                            <td>
                                <code><?php echo e($client->slug); ?></code>
                                <div class="sa-table__meta"><?php echo e($client->domain); ?></div>
                            </td>
                            <td><?php echo e($client->sector ?? 'N/A'); ?></td>
                            <td><?php echo e($client->users_count); ?></td>
                            <td><?php echo e($client->created_at->format('d/m/Y')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center py-8 text-muted">
                                Aucun client trouvé.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="sa-pagination">
                    <?php echo e($clients->links()); ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/superadmin/clients/index.blade.php ENDPATH**/ ?>