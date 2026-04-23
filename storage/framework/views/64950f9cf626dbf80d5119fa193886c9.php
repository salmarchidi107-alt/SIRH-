<?php $__env->startSection('title', 'Pointage HospitalRH'); ?>

<?php $__env->startPush('styles'); ?>

<style>
/* ── Layout général ──────────────────────────────────────────────────── */
.badge-wrap {
  flex: 1; display: flex; flex-direction: column;
  min-height: 100vh; padding: 0;
}

/* ── Header ──────────────────────────────────────────────────────────── */
.badge-header {
  display: flex; align-items: center; justify-content: center;
  padding: 24px 28px;
  border-bottom: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.03);
}
.header-brand {
  display: flex; align-items: center; gap: 12px; text-align: center;
}
.brand-icon {
  width: 48px; height: 48px; border-radius: 12px;
  background: linear-gradient(135deg, var(--teal), var(--teal2));
  display: flex; align-items: center; justify-content: center; font-size: 22px;
}
.brand-name { font-size: 18px; font-weight: 700; }
.brand-sub  { font-size: 13px; color: rgba(255,255,255,.5); }

/* ── Status info ────────────────────────────────────────────────────── */
.welcome-status {
  text-align: center; max-width: 480px; margin: 0 auto 40px;
}
.status-welcome { font-size: 16px; font-weight: 600; color: rgba(255,255,255,.8); margin-bottom: 8px; }
.status-subtitle { font-size: 14px; color: rgba(255,255,255,.4); }

/* ── Boutons principaux ───────────────────────────────────────────────── */
.action-buttons {
  display: flex; gap: 24px; flex-wrap: wrap; justify-content: center; max-width: 600px; margin: 0 auto;
}
.btn-action {
  width: 240px; height: 240px; border-radius: 32px;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center; gap: 16px;
  border: none; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: all .3s cubic-bezier(0.16,1,0.3,1);
  position: relative; overflow: hidden; text-decoration: none; color: white;
}
.btn-action::before {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(160deg, rgba(255,255,255,.15) 0%, transparent 60%);
  opacity: 0; transition: opacity .3s;
}
.btn-action:hover::before { opacity: 1; }
.btn-action .action-icon { font-size: 60px; line-height: 1; }
.btn-action .action-label { font-size: 26px; font-weight: 800; }
.btn-action .action-sub   { font-size: 15px; color: rgba(255,255,255,.7); }

/* Entrée */
.btn-entree {
  background: linear-gradient(145deg, #0d9488, #0f766e);
  box-shadow: 0 20px 60px rgba(13,148,136,.4), 0 4px 20px rgba(0,0,0,.2);
}
.btn-entree:hover {
  transform: translateY(-8px) scale(1.03);
  box-shadow: 0 32px 80px rgba(13,148,136,.55), 0 12px 32px rgba(0,0,0,.3);
}

/* Sortie */
.btn-sortie {
  background: linear-gradient(145deg, #d97706, #b45309);
  box-shadow: 0 20px 60px rgba(245,158,11,.35), 0 4px 20px rgba(0,0,0,.2);
}
.btn-sortie:hover {
  transform: translateY(-8px) scale(1.03);
  box-shadow: 0 32px 80px rgba(245,158,11,.5), 0 12px 32px rgba(0,0,0,.3);
}

.btn-action:active { transform: translateY(-2px) scale(1.01) !important; }

/* Horloge centrale */
.central-clock {
  font-family: 'DM Mono', monospace;
  font-size: 72px; font-weight: 700; letter-spacing: -4px;
  color: rgba(255,255,255,.15); margin: 40px 0;
  text-align: center;
}

/* Responsive */
@media (max-width: 600px) {
  .btn-action { width: 200px; height: 200px; }
  .btn-action .action-icon { font-size: 50px; }
  .btn-action .action-label { font-size: 22px; }
  .central-clock { font-size: 52px; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="badge-wrap">
  
  <div class="badge-header">
    <div class="header-brand">
      <div class="brand-icon">⏱</div>
      <div>
        <div class="brand-name">HospitalRH Pointage</div>
        <div class="brand-sub">Badgeuse tactile</div>
      </div>
    </div>
  </div>

  
  <div class="welcome-status">
    <div class="status-welcome">Sélectionnez votre action</div>
    <div class="status-subtitle">Pointer votre arrivée ou votre départ</div>
  </div>

  
  <div class="central-clock" id="liveClock">--:--</div>

  
  <div class="action-buttons">
    
    <form method="GET" action="<?php echo e(route('badge.auth.show', ['action' => 'entree', 'intent' => 'entree'])); ?>">
      <button type="submit" class="btn-action btn-entree">
        <span class="action-icon">📥</span>
        <span class="action-label">Entrée</span>
        <span class="action-sub">Pointer mon arrivée</span>
      </button>
    </form>

    
    <form method="GET" action="<?php echo e(route('badge.auth.show', ['action' => 'sortie', 'intent' => 'sortie'])); ?>">
      <button type="submit" class="btn-action btn-sortie">
        <span class="action-icon">📤</span>
        <span class="action-label">Sortie</span>
        <span class="action-sub">Pointer mon départ</span>
      </button>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Horloge live
function updateClock() {
  document.getElementById('liveClock').textContent =
    new Date().toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
}
updateClock();
setInterval(updateClock, 1000);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.badge', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/badge/pointage.blade.php ENDPATH**/ ?>