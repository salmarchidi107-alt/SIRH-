<?php $__env->startSection('title', 'Pointage enregistré'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.result-wrap {
  flex: 1; display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 32px 24px; min-height: 100vh; gap: 28px;
}

.result-icon-wrap {
  position: relative; display: flex; align-items: center; justify-content: center;
}
.result-ripple {
  position: absolute; width: 140px; height: 140px; border-radius: 50%;
  animation: ripple 2s ease-out infinite;
}
@keyframes ripple {
  0%   { transform: scale(.7); opacity: .6; }
  100% { transform: scale(2);  opacity: 0; }
}
.result-ripple.entree { background: rgba(13,148,136,.4); }
.result-ripple.sortie { background: rgba(245,158,11,.4); }
.result-ripple.pause  { background: rgba(99,102,241,.4); }

.result-icon {
  width: 100px; height: 100px; border-radius: 28px;
  display: flex; align-items: center; justify-content: center;
  font-size: 48px; position: relative; z-index: 1;
  animation: iconPop .5s cubic-bezier(0.16,1,0.3,1) both;
}
@keyframes iconPop {
  from { transform: scale(0) rotate(-10deg); opacity:0; }
  to   { transform: scale(1) rotate(0);      opacity:1; }
}
.result-icon.entree {
  background: linear-gradient(135deg, var(--teal), var(--teal2));
  box-shadow: 0 16px 48px rgba(13,148,136,.5);
}
.result-icon.sortie {
  background: linear-gradient(135deg, #d97706, #b45309);
  box-shadow: 0 16px 48px rgba(245,158,11,.4);
}
.result-icon.pause {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  box-shadow: 0 16px 48px rgba(99,102,241,.4);
}

.result-card {
  background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
  border-radius: 24px; padding: 36px 40px; text-align: center;
  max-width: 460px; width: 100%;
  backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
  animation: cardIn .5s .15s cubic-bezier(0.16,1,0.3,1) both;
}
@keyframes cardIn {
  from { opacity:0; transform: translateY(20px); }
  to   { opacity:1; transform: translateY(0); }
}

.result-type {
  font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .1em;
  margin-bottom: 8px;
}
.result-type.entree { color: var(--teal2); }
.result-type.sortie { color: var(--amber); }
.result-type.pause  { color: #818cf8; }

.result-name {
  font-size: 28px; font-weight: 800; letter-spacing: -.5px; margin-bottom: 4px;
}
.result-dept { font-size: 14px; color: rgba(255,255,255,.45); margin-bottom: 24px; }

.result-time-big {
  font-family: 'DM Mono', monospace;
  font-size: 56px; font-weight: 500; letter-spacing: -3px;
  line-height: 1; margin: 8px 0;
}
.result-time-big.entree { color: var(--teal2); }
.result-time-big.sortie { color: var(--amber); }
.result-time-big.pause  { color: #818cf8; }
.result-time-label {
  font-size: 12px; color: rgba(255,255,255,.35);
  text-transform: uppercase; letter-spacing: .08em;
}

.divider { height: 1px; background: rgba(255,255,255,.1); margin: 24px 0; }

.shift-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;
  margin-bottom: 28px;
}
.shift-cell { text-align: center; }
.shift-cell-label {
  font-size: 12px; color: rgba(255,255,255,.45);
  text-transform: uppercase; letter-spacing: .05em;
  margin-bottom: 6px; font-weight: 600;
}
.shift-cell-value {
  font-size: 22px; font-weight: 800;
  font-family: 'DM Mono', monospace;
  line-height: 1.1; margin-bottom: 4px;
}
.shift-cell-value.heure-sortie { color: var(--amber); }
.shift-cell-value.total-heures {
  color: var(--green); font-size: 28px;
  text-shadow: 0 2px 8px rgba(34,197,94,.3);
}

.result-actions { display: flex; flex-direction: column; gap: 12px; }
.btn-logout-sm {
  display: block; width: 100%; padding: 12px;
  background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12);
  color: rgba(255,255,255,.6); border-radius: 14px;
  font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif;
  cursor: pointer; text-decoration: none; text-align: center; transition: all .2s;
}
.btn-logout-sm:hover { background: rgba(255,255,255,.1); color: white; }

.auto-return {
  font-size: 12px; color: rgba(255,255,255,.3); margin-top: 8px; text-align: center;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
  $tz      = 'Africa/Casablanca';
  $nowCasa = \Carbon\Carbon::now($tz);

  $typeIcon = match($type) {
    'entree' => '📥',
    'sortie' => '📤',
    'pause'  => '⏸',
    default  => '✅',
  };

  $typeLabel = match($type) {
    'entree' => '✓ Entrée enregistrée',
    'sortie' => '✓ Sortie enregistrée',
    'pause'  => '⏸ Pause enregistrée',
    default  => '✓ Pointage enregistré',
  };

  $timeLabel = match($type) {
    'entree' => "Heure d'arrivée",
    'sortie' => 'Heure de départ',
    'pause'  => 'Heure de pause',
    default  => 'Heure',
  };

  $displayTime = match($type) {
    'entree' => $todayShift['first_entree'] ?? $nowCasa->format('H:i'),
    'sortie' => $todayShift['last_sortie']  ?? $nowCasa->format('H:i'),
    'pause'  => $todayShift['pause_start']  ?? $nowCasa->format('H:i'),
    default  => $nowCasa->format('H:i'),
  };

  // Classe CSS : sortie et pause → couleur différente
  $typeClass = in_array($type, ['entree','sortie','pause']) ? $type : 'entree';
?>

<div class="result-wrap">

  
  <div class="result-icon-wrap">
    <div class="result-ripple <?php echo e($typeClass); ?>"></div>
    <div class="result-ripple <?php echo e($typeClass); ?>" style="animation-delay:.5s;"></div>
    <div class="result-icon <?php echo e($typeClass); ?>"><?php echo e($typeIcon); ?></div>
  </div>

  
  <div class="result-card">

    <div class="result-type <?php echo e($typeClass); ?>"><?php echo e($typeLabel); ?></div>

    <?php if($employee): ?>
      <div class="result-name"><?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?></div>
      <div class="result-dept"><?php echo e($employee->department ?? '—'); ?> · <?php echo e($employee->matricule); ?></div>
    <?php else: ?>
      <div class="result-name"><?php echo e(auth()->guard('badge')->user()->name ?? '—'); ?></div>
      <div class="result-dept">—</div>
    <?php endif; ?>

    
    <div class="result-time-label"><?php echo e($timeLabel); ?></div>
    <div class="result-time-big <?php echo e($typeClass); ?>"><?php echo e($displayTime); ?></div>
    <div class="result-time-label" style="margin-top:4px;">
      <?php echo e($nowCasa->isoFormat('dddd D MMMM YYYY')); ?>

    </div>

    
    <div class="divider"></div>
    <div class="shift-grid">
      <div class="shift-cell">
        <div class="shift-cell-label">📥 Entrée</div>
        <div class="shift-cell-value"><?php echo e($todayShift['first_entree'] ?? '—'); ?></div>
      </div>
      <div class="shift-cell">
        <div class="shift-cell-label">📤 Sortie</div>
        <div class="shift-cell-value heure-sortie"><?php echo e($todayShift['last_sortie'] ?? '—'); ?></div>
      </div>
      <div class="shift-cell">
        <div class="shift-cell-label">⏱ Total</div>
        <div class="shift-cell-value total-heures"><?php echo e($todayShift['total_human'] ?? '—'); ?></div>
      </div>
    </div>

    <?php if(!empty($todayShift['pause_display'])): ?>
    <div class="divider"></div>
    <div class="shift-grid">
      <div class="shift-cell">
        <div class="shift-cell-label">⏸ Début pause</div>
        <div class="shift-cell-value"><?php echo e($todayShift['pause_start'] ?? '—'); ?></div>
      </div>
      <div class="shift-cell">
        <div class="shift-cell-label">↩ Retour</div>
        <div class="shift-cell-value"><?php echo e($todayShift['pause_end'] ?? '—'); ?></div>
      </div>
      <div class="shift-cell">
        <div class="shift-cell-label">⏱ Durée pause</div>
        <div class="shift-cell-value total-heures"><?php echo e($todayShift['total_pause_human'] ?? '—'); ?></div>
      </div>
    </div>
    <?php endif; ?>

    
    <div class="result-actions">
      <form method="POST" action="<?php echo e(route('badge.logout')); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn-logout-sm">Se déconnecter</button>
      </form>
    </div>

    <div class="auto-return">
      Retour automatique dans <strong id="countdown">100</strong>s
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let seconds = 100;
const countEl = document.getElementById('countdown');
const timer = setInterval(() => {
  seconds--;
  if (countEl) countEl.textContent = seconds;
  if (seconds <= 0) {
    clearInterval(timer);
    window.location.href = '<?php echo e(route('badge.pointage')); ?>';
  }
}, 1000);

document.addEventListener('click', () => {
  clearInterval(timer);
  const autoEl = document.querySelector('.auto-return');
  if (autoEl) autoEl.textContent = '';
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.badge', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/badge/result.blade.php ENDPATH**/ ?>