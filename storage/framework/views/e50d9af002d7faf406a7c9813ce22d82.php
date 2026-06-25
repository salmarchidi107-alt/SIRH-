<?php $__env->startSection('title', $news->title); ?>
<?php $__env->startSection('page-title', 'Détails de l\'actualité'); ?>

<?php $__env->startSection('content'); ?>
<div class="news-detail">
    <?php if($news->image): ?>
    <div class="news-flyer">
        <img src="<?php echo e(asset($news->image)); ?>" alt="<?php echo e($news->title); ?>">
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="card-title">📰 <?php echo e($news->title); ?></div>
            <div style="display:flex;gap:8px">
                <a href="<?php echo e(route('news.edit', $news)); ?>" class="btn btn-ghost">Modifier</a>
                <a href="<?php echo e(route('news.index')); ?>" class="btn btn-ghost">Retour</a>
            </div>
        </div>
        <div class="card-body">
            <div style="margin-bottom:16px">
                <span class="badge bg-<?php echo e($news->type === 'holiday' ? 'success' : ($news->type === 'promotion' ? 'warning' : 'primary')); ?>">
                    <?php echo e(\App\Models\News::TYPES[$news->type] ?? $news->type); ?>

                </span>
                <span class="badge bg-<?php echo e($news->is_active ? 'success' : 'secondary'); ?>">
                    <?php echo e($news->is_active ? 'Actif' : 'Inactif'); ?>

                </span>
            </div>

            <div style="margin-bottom:16px;color:var(--text-muted)">
                <strong>Date:</strong> <?php echo e($news->event_date->format('d/m/Y')); ?>

            </div>

            <?php if($news->description): ?>
            <div>
                <strong>Description:</strong>
                <p style="margin-top:8px;white-space: pre-wrap;"><?php echo e($news->description); ?></p>
            </div>
            <?php endif; ?>

            <form action="<?php echo e(route('news.destroy', $news)); ?>" method="POST" style="margin-top:24px">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité?')">
                    Supprimer
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.news-detail {
    max-width: 900px;
    margin: 0 auto;
}

.news-flyer {
    margin-bottom: 24px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
}

.news-flyer img {
    width: 100%;
    height: auto;
    display: block;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/news/show.blade.php ENDPATH**/ ?>