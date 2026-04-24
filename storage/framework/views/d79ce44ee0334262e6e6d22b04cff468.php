<?php echo $__env->make('layouts.superadmin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-xl">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Détails du Tenant</h1>
            <p class="text-gray-600 mt-1"><?php echo e($tenant->name); ?> (<?php echo e($tenant->slug); ?>)</p>
        </div>
        <div class="flex gap-3">
            <a href="<?php echo e(route('superadmin.tenants.edit', $tenant)); ?>"
               class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Modifier
            </a>
            <a href="<?php echo e(route('superadmin.tenants.index')); ?>"
               class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                Retour
            </a>
        </div>
    </div>

    <style>
.status-badge {
    @apply inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium border;
}
</style>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <span class="status-badge <?php echo e($tenant->status->dotClass()); ?>">
                    <?php echo e($tenant->status->label()); ?>

                </span>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Plan</label>
                <span class="status-badge <?php echo e($tenant->plan->badgeClass()); ?>">
                    <?php echo e($tenant->plan->label()); ?>

                </span>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Région</label>
                <p class="text-lg font-semibold text-gray-900"><?php echo e($tenant->region); ?></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Secteur</label>
                <p class="text-gray-900"><?php echo e($tenant->sector ?? 'Non spécifié'); ?></p>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Admin Principal</label>
                <?php if($tenant->admin): ?>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="font-semibold text-gray-900"><?php echo e($tenant->admin->name); ?></p>
                        <p class="text-gray-600 text-sm"><?php echo e($tenant->admin->email); ?></p>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 italic">Aucun admin assigné</p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Utilisateurs</label>
                <p class="text-2xl font-bold text-blue-600"><?php echo e($tenant->users_count); ?></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Créé le</label>
                <p class="text-gray-900"><?php echo e($tenant->created_at->format('d/m/Y à H:i')); ?></p>
            </div>

            <div class="flex items-center space-x-4 pt-2">
                <?php if($tenant->logo_path): ?>
                    <img src="<?php echo e(Storage::url($tenant->logo_path)); ?>" alt="Logo" class="w-16 h-16 rounded-lg object-cover shadow-md">
                <?php endif; ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Couleurs</label>
                    <div class="flex items-center space-x-4 mt-1">
                        <div class="w-12 h-12 rounded-lg border-2" style="background-color: <?php echo e($tenant->brand_color); ?>"></div>
                        <div class="w-12 h-12 rounded-lg border-2" style="background-color: <?php echo e($tenant->sidebar_color); ?>"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold mb-4">Actions Rapides</h3>
            <div class="flex flex-wrap gap-3">
                <?php if($tenant->status !== \App\Enums\TenantStatus::Suspended): ?>
                    <form method="POST" action="<?php echo e(route('superadmin.tenants.suspend', $tenant)); ?>" class="inline">
                        <?php echo csrf_field(); ?> <?php echo method_field('POST'); ?>
                        <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
                            Suspendre
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('superadmin.tenants.reactivate', $tenant)); ?>" class="inline">
                        <?php echo csrf_field(); ?> <?php echo method_field('POST'); ?>
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            Réactiver
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-right">
            <a href="<?php echo e(route('superadmin.clients.show', $tenant->slug)); ?>"
               class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Voir Client →
            </a>
        </div>
    </div>
</div>

<?php /**PATH D:\Projects\HospitalRh\resources\views/superadmin/tenants/show.blade.php ENDPATH**/ ?>