<?php $__env->startSection('title', 'Créer une Semaine Type'); ?>
<?php $__env->startSection('page-title', 'Nouveau Modèle de Semaine'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <h1> Créer une Semaine Type</h1>
        <p>Définissez les shifts pour chaque jour de la semaine</p>
    </div>
</div>

<form method="POST" action="<?php echo e(route('planning.templates.store')); ?>">
    <?php echo csrf_field(); ?>
    
        <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <div class="card-title">Informations générales</div>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:600px">
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Nom du modèle *</label>
                    <input type="text" name="name" required placeholder="Ex: Semaine classique Urgences" 
                        style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                </div>
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Département</label>
                    <select name="department" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                        <option value="">— Pour tous les départements —</option>
                        <?php $__currentLoopData = \App\Models\Department::orderBy('name')->pluck('name')->filter(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($dept); ?>"><?php echo e($dept); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Configuration des jours</div>
        </div>
        <div class="card-body">
            <div style="display:grid;gap:16px">
                
                <!-- Monday -->
                <div style="display:grid;grid-template-columns:120px 1fr 1fr 1fr 1fr;gap:12px;align-items:center;padding:12px;background:var(--surface-2);border-radius:8px">
                    <div style="font-weight:600">Lundi</div>
                    <select name="monday_shift_type" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                        <option value="">— Pas de shift —</option>
                        <option value="matin"> Matin</option>
                        <option value="apres_midi"> Après-midi</option>
                        <option value="journee"> Journée</option>
                        <option value="nuit"> Nuit</option>
                        <option value="garde"> Garde</option>
                    </select>
                    <input type="time" name="monday_start" placeholder="Début" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                    <input type="time" name="monday_end" placeholder="Fin" style="padding:8px;border:1px solid var(--border);border-radius:6px">
<select name="monday_room" 
        style="padding:8px;border:1px solid var(--border);border-radius:6px">

    <option value="">— Choisir une salle —</option>

    <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($room->id); ?>">
            <?php echo e($room->name); ?>

        </option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</select>                </div>

                <!-- Tuesday -->
                <div style="display:grid;grid-template-columns:120px 1fr 1fr 1fr 1fr;gap:12px;align-items:center;padding:12px;background:var(--surface-2);border-radius:8px">
                    <div style="font-weight:600">Mardi</div>
                    <select name="tuesday_shift_type" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                        <option value="">— Pas de shift —</option>
                        <option value="matin"> Matin</option>
                        <option value="apres_midi"> Après-midi</option>
                        <option value="journee"> Journée</option>
                        <option value="nuit"> Nuit</option>
                        <option value="garde"> Garde</option>
                    </select>
                    <input type="time" name="tuesday_start" placeholder="Début" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                    <input type="time" name="tuesday_end" placeholder="Fin" style="padding:8px;border:1px solid var(--border);border-radius:6px">
<select name="tuesday_room" 
        style="padding:8px;border:1px solid var(--border);border-radius:6px">

    <option value="">— Choisir une salle —</option>

    <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($room->id); ?>">
            <?php echo e($room->name); ?>

        </option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</select>                </div>

                <!-- Wednesday -->
                <div style="display:grid;grid-template-columns:120px 1fr 1fr 1fr 1fr;gap:12px;align-items:center;padding:12px;background:var(--surface-2);border-radius:8px">
                    <div style="font-weight:600">Mercredi</div>
                    <select name="wednesday_shift_type" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                        <option value="">— Pas de shift —</option>
                        <option value="matin"> Matin</option>
                        <option value="apres_midi"> Après-midi</option>
                        <option value="journee"> Journée</option>
                        <option value="nuit"> Nuit</option>
                        <option value="garde"> Garde</option>
                    </select>
                    <input type="time" name="wednesday_start" placeholder="Début" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                    <input type="time" name="wednesday_end" placeholder="Fin" style="padding:8px;border:1px solid var(--border);border-radius:6px">
<select name="wednesday_room" 
        style="padding:8px;border:1px solid var(--border);border-radius:6px">

    <option value="">— Choisir une salle —</option>

    <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($room->id); ?>">
            <?php echo e($room->name); ?>

        </option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</select>                </div>

                <!-- Thursday -->
                <div style="display:grid;grid-template-columns:120px 1fr 1fr 1fr 1fr;gap:12px;align-items:center;padding:12px;background:var(--surface-2);border-radius:8px">
                    <div style="font-weight:600">Jeudi</div>
                    <select name="thursday_shift_type" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                        <option value="">— Pas de shift —</option>
                        <option value="matin"> Matin</option>
                        <option value="apres_midi"> Après-midi</option>
                        <option value="journee"> Journée</option>
                        <option value="nuit"> Nuit</option>
                        <option value="garde"> Garde</option>
                    </select>
                    <input type="time" name="thursday_start" placeholder="Début" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                    <input type="time" name="thursday_end" placeholder="Fin" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                    <select name="thursday_room" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                        <option value="">— Choisir une salle —</option>
                        <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($room->id); ?>">
                                <?php echo e($room->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Friday -->
                <div style="display:grid;grid-template-columns:120px 1fr 1fr 1fr 1fr;gap:12px;align-items:center;padding:12px;background:var(--surface-2);border-radius:8px">
                    <div style="font-weight:600">Vendredi</div>
                    <select name="friday_shift_type" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                        <option value="">— Pas de shift —</option>
                        <option value="matin"> Matin</option>
                        <option value="apres_midi"> Après-midi</option>
                        <option value="journee"> Journée</option>
                        <option value="nuit"> Nuit</option>
                        <option value="garde"> Garde</option>
                    </select>
                    <input type="time" name="friday_start" placeholder="Début" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                    <input type="time" name="friday_end" placeholder="Fin" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                    <select name="friday_room" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                        <option value="">— Choisir une salle —</option>
                        <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($room->id); ?>">
                                <?php echo e($room->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Saturday -->
                <div style="display:grid;grid-template-columns:120px 1fr 1fr 1fr 1fr;gap:12px;align-items:center;padding:12px;background:var(--surface-2);border-radius:8px">
                    <div style="font-weight:600">Samedi</div>
                    <select name="saturday_shift_type" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                        <option value="">— Pas de shift —</option>
                        <option value="matin"> Matin</option>
                        <option value="apres_midi"> Après-midi</option>
                        <option value="journee"> Journée</option>
                        <option value="nuit"> Nuit</option>
                        <option value="garde"> Garde</option>
                    </select>
                    <input type="time" name="saturday_start" placeholder="Début" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                    <input type="time" name="saturday_end" placeholder="Fin" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                    <select name="saturday_room" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                        <option value="">— Choisir une salle —</option>
                        <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($room->id); ?>">
                                <?php echo e($room->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Sunday -->
                <div style="display:grid;grid-template-columns:120px 1fr 1fr 1fr 1fr;gap:12px;align-items:center;padding:12px;background:var(--surface-2);border-radius:8px">
                    <div style="font-weight:600">Dimanche</div>
                    <select name="sunday_shift_type" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                        <option value="">— Pas de shift —</option>
                        <option value="matin"> Matin</option>
                        <option value="apres_midi"> Après-midi</option>
                        <option value="journee"> Journée</option>
                        <option value="nuit"> Nuit</option>
                        <option value="garde"> Garde</option>
                    </select>
                    <input type="time" name="sunday_start" placeholder="Début" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                    <input type="time" name="sunday_end" placeholder="Fin" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                    <select name="sunday_room" style="padding:8px;border:1px solid var(--border);border-radius:6px">
                        <option value="">— Choisir une salle —</option>
                        <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($room->id); ?>">
                                <?php echo e($room->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;margin-top:20px">
        <a href="<?php echo e(route('planning.templates.index')); ?>" class="btn btn-outline">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer le modèle</button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/planning/templates/create.blade.php ENDPATH**/ ?>