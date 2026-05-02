

<?php $__env->startSection('content'); ?>

<div class="container">
    <h2 class="mb-4">Gestion des salles</h2>

    <!-- FORMULAIRE -->
    <form action="<?php echo e(route('rooms.store')); ?>" method="POST" class="mb-4">
        <?php echo csrf_field(); ?>

        <div class="row">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" placeholder="Nom de la salle" required>
            </div>
            <br>
            <div class="col-md-4">
                <select name="department_id" class="form-control" required>
                    <option value="">Choisir service</option>
                    <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <br>
            <div class="col-md-2">
                <button class="btn btn-primary">Ajouter</button>
            </div>
        </div>
    </form>

    <!-- TABLE -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Salle</th>
                <th>Service</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($room->id); ?></td>
                <td><?php echo e($room->name); ?></td>
                <td><?php echo e($room->department->name ?? '-'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/rooms/index.blade.php ENDPATH**/ ?>