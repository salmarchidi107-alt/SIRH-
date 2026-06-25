<?php $__env->startSection('title', 'Authentification — Pointage'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.login-wrap {
  flex: 1; display: flex; align-items: center; justify-content: center;
  padding: 24px; min-height: 100vh;
}
.login-card {
  width: 100%; max-width: 440px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.10);
  border-radius: 28px; padding: 44px 40px;
  backdrop-filter: blur(32px); -webkit-backdrop-filter: blur(32px);
  box-shadow: 0 32px 80px rgba(0,0,0,0.45);
  animation: cardIn .5s cubic-bezier(0.16,1,0.3,1);
}
@keyframes cardIn {
  from { opacity:0; transform: translateY(28px) scale(.97); }
  to   { opacity:1; transform: none; }
}

.login-back {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 13px; color: rgba(255,255,255,.4);
  text-decoration: none; margin-bottom: 28px; transition: color .2s;
}
.login-back:hover { color: rgba(255,255,255,.8); }

.type-pill {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 5px 14px; border-radius: 99px;
  font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
  margin-bottom: 12px;
}
.type-pill.entree { background: rgba(13,148,136,.15); color: var(--teal2); border: 1px solid rgba(13,148,136,.25); }
.type-pill.sortie { background: rgba(245,158,11,.15);  color: var(--amber2); border: 1px solid rgba(245,158,11,.25); }

.login-title { font-size: 22px; font-weight: 800; letter-spacing: -.4px; margin-bottom: 4px; }
.login-sub   { font-size: 13px; color: rgba(255,255,255,.4); margin-bottom: 32px; }

.alert-error {
  background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.28);
  border-radius: 12px; padding: 12px 16px;
  font-size: 13px; color: #fca5a5; margin-bottom: 20px;
  display: flex; align-items: center; gap: 8px;
}
.alert-warning {
  background: rgba(245,158,11,.10); border: 1px solid rgba(245,158,11,.25);
  border-radius: 12px; padding: 12px 16px;
  font-size: 13px; color: #fde68a; margin-bottom: 20px;
  display: flex; align-items: center; gap: 8px;
}

.field-label {
  display: block; font-size: 11px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .08em; color: rgba(255,255,255,.45); margin-bottom: 10px;
}

/* ══════════════════════════════════════════════════════════════
   SÉLECTEUR TYPE DE SHIFT (style radio pills compact)
══════════════════════════════════════════════════════════════ */
.shift-type-wrap {
  margin-bottom: 24px;
}
.shift-type-group {
  display: inline-flex; align-items: center; gap: 4px;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 99px; padding: 4px 5px;
}
.shift-radio-opt {
  display: flex; align-items: center; gap: 7px;
  padding: 7px 16px; border-radius: 99px;
  cursor: pointer; transition: all .2s;
  font-size: 13px; font-weight: 600;
  color: rgba(255,255,255,.45);
  user-select: none;
}
.shift-radio-opt input[type=radio] { display: none; }
.shift-dot {
  width: 15px; height: 15px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,.25);
  display: flex; align-items: center; justify-content: center;
  transition: all .2s; flex-shrink: 0;
}
.shift-dot::after {
  content: ''; width: 6px; height: 6px;
  border-radius: 50%; background: white;
  opacity: 0; transition: opacity .15s;
}
.shift-radio-opt.active {
  background: rgba(255,255,255,.10);
  color: rgba(255,255,255,.92);
}
.shift-radio-opt.active .shift-dot {
  border-color: var(--teal2);
  background: var(--teal);
}
.shift-radio-opt.active .shift-dot::after { opacity: 1; }

/* ══════════════════════════════════════════════════════════════
   RADIO ACTION (Début shift / Retour pause / etc.)
══════════════════════════════════════════════════════════════ */
.radio-group { margin-bottom: 24px; }
.radio-opt {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 16px; border-radius: 14px;
  border: 1px solid rgba(255,255,255,.08);
  margin-bottom: 8px; cursor: pointer; transition: background .15s, border-color .15s;
}
.radio-opt:has(input:checked)            { border-color: var(--teal);  background: rgba(13,148,136,.08); }
.radio-opt:has(input:checked).sortie-opt { border-color: var(--amber); background: rgba(245,158,11,.08); }
.radio-opt input[type=radio]             { accent-color: var(--teal);  width:16px; height:16px; flex-shrink:0; }
.sortie-opt input[type=radio]            { accent-color: var(--amber); }
.radio-lbl { font-size: 14px; font-weight: 600; }
.radio-sub { font-size: 12px; color: rgba(255,255,255,.4); margin-top: 2px; }

/* ══════════════════════════════════════════════════════════════
   CHAMPS
══════════════════════════════════════════════════════════════ */
.field { margin-bottom: 18px; }
.form-input {
  width: 100%;
  background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12);
  border-radius: 14px; padding: 14px 18px;
  font-size: 16px; color: white;
  font-family: 'DM Mono', 'JetBrains Mono', monospace;
  letter-spacing: .15em; text-transform: uppercase;
  outline: none; transition: all .2s;
}
.form-input::placeholder { color: rgba(255,255,255,.25); letter-spacing:0; font-family:'DM Sans',sans-serif; }
.form-input:focus { border-color: var(--teal); background: rgba(13,148,136,.10); box-shadow: 0 0 0 3px rgba(13,148,136,.18); }
.form-input.error { border-color: var(--red); }

/* ══════════════════════════════════════════════════════════════
   SIGNATURE
══════════════════════════════════════════════════════════════ */
.sig-canvas {
  width: 100%; height: 140px; display: block;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 14px; cursor: crosshair; transition: border-color .2s;
}
.sig-canvas.has-sig { border-color: rgba(255,255,255,.3); }
.sig-hint { font-size: 11px; color: rgba(255,255,255,.3); margin-top: 6px; margin-bottom: 4px; }

/* ══════════════════════════════════════════════════════════════
   GRILLE GÉOLOC + CAMÉRA
══════════════════════════════════════════════════════════════ */
.geo-cam-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 20px;
}

/* ── Carré Géolocalisation ── */
.geo-square {
  border-radius: 16px; padding: 14px 14px 12px;
  min-height: 90px; display: flex; flex-direction: column;
  justify-content: center; gap: 6px;
  transition: all .35s ease; position: relative; overflow: hidden;
}
.geo-square.loading { background: rgba(245,158,11,.08); border: 1px solid rgba(245,158,11,.2); }
.geo-square.ok      { background: rgba(13,148,136,.08); border: 1px solid rgba(13,148,136,.2); }
.geo-square.denied  { background: rgba(239,68,68,.07);  border: 1px solid rgba(239,68,68,.18); }

.geo-square-icon  { font-size: 20px; line-height: 1; }
.geo-square-title {
  font-size: 11px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .07em; line-height: 1.2;
}
.geo-square.loading .geo-square-title { color: #fde68a; }
.geo-square.ok      .geo-square-title { color: var(--teal2); }
.geo-square.denied  .geo-square-title { color: #fca5a5; }

.geo-square-sub {
  font-size: 10px; opacity: .65;
  font-family: 'DM Mono', monospace;
  line-height: 1.3; word-break: break-word;
}
.geo-square.loading .geo-square-sub { color: #fde68a; }
.geo-square.ok      .geo-square-sub { color: var(--teal2); }
.geo-square.denied  .geo-square-sub { color: #fca5a5; }

.geo-dot {
  position: absolute; top: 12px; right: 12px;
  width: 7px; height: 7px; border-radius: 50%;
}
.geo-square.loading .geo-dot { background: #fde68a; animation: blink .7s ease-in-out infinite alternate; }
.geo-square.ok      .geo-dot { background: var(--teal2); animation: pdot 2s ease-in-out infinite; }
.geo-square.denied  .geo-dot { background: #fca5a5; }

@keyframes blink { from{opacity:.2;} to{opacity:1;} }
@keyframes pdot  { 0%,100%{box-shadow:0 0 0 0 rgba(94,234,212,.5);}50%{box-shadow:0 0 0 6px rgba(94,234,212,0);} }

/* ── Carré Caméra ── */
.cam-square {
  border-radius: 16px; padding: 14px 14px 12px;
  min-height: 90px; display: flex; flex-direction: column;
  justify-content: center; align-items: flex-start;
  gap: 6px; cursor: pointer; transition: all .25s;
  border: 1px solid rgba(239,68,68,.35);
  background: rgba(239,68,68,.06);
  position: relative; overflow: hidden;
}
.cam-square:hover {
  background: rgba(13,148,136,.12);
  border-color: var(--teal); border-style: solid;
}
.cam-square.has-photo {
  background: rgba(13,148,136,.08);
  border: 1px solid rgba(13,148,136,.3); border-style: solid;
}
.cam-square-icon  { font-size: 20px; line-height: 1; }
.cam-square-title {
  font-size: 11px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .07em; color: #fca5a5; line-height: 1.2;
}
.cam-square.has-photo .cam-square-title { color: var(--teal2); }
.cam-square-sub { font-size: 10px; color: rgba(255,100,100,.5); line-height: 1.3; }
.cam-square.has-photo .cam-square-sub { color: rgba(94,234,212,.65); }

/* Badge "Requis" sur le carré caméra */
.cam-required-badge {
  position: absolute; top: 8px; right: 8px;
  background: rgba(239,68,68,.25); border: 1px solid rgba(239,68,68,.45);
  border-radius: 99px; padding: 2px 7px;
  font-size: 9px; font-weight: 800; text-transform: uppercase;
  letter-spacing: .07em; color: #fca5a5;
  transition: opacity .3s;
}
.cam-square.has-photo .cam-required-badge { opacity: 0; }

.cam-thumb {
  position: absolute; inset: 0;
  object-fit: cover; border-radius: 15px;
  opacity: .22; pointer-events: none; transform: scaleX(-1);
}
.cam-check {
  position: absolute; top: 10px; right: 10px;
  width: 18px; height: 18px; border-radius: 50%;
  background: var(--teal); display: none;
  align-items: center; justify-content: center;
  font-size: 10px; color: #021a18; font-weight: 900;
}
.cam-square.has-photo .cam-check { display: flex; }

/* ══════════════════════════════════════════════════════════════
   BOUTON SUBMIT
══════════════════════════════════════════════════════════════ */
.btn-submit {
  width: 100%; padding: 16px;
  background: linear-gradient(135deg, var(--teal), var(--teal2));
  color: #021a18; border: none; border-radius: 14px;
  font-size: 15px; font-weight: 800; font-family: 'DM Sans', 'Syne', sans-serif;
  cursor: pointer; transition: all .25s; letter-spacing: -.2px;
  box-shadow: 0 8px 24px rgba(13,148,136,.35);
  opacity: .35; pointer-events: none;
}
.btn-submit.ready { opacity:1; pointer-events:all; }
.btn-submit.ready:hover { transform: translateY(-2px); box-shadow: 0 14px 36px rgba(13,148,136,.5); }

/* ══════════════════════════════════════════════════════════════
   MODALE CAMÉRA
══════════════════════════════════════════════════════════════ */
.camera-overlay {
  position: fixed; inset: 0; z-index: 9999;
  background: rgba(0,0,0,.85); backdrop-filter: blur(12px);
  display: flex; align-items: center; justify-content: center; padding: 24px;
  opacity: 0; pointer-events: none; transition: opacity .3s ease;
}
.camera-overlay.active { opacity: 1; pointer-events: all; }
.camera-modal {
  background: rgba(15,20,30,.95);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 24px; padding: 28px;
  width: 100%; max-width: 480px;
  transform: scale(.95);
  transition: transform .3s cubic-bezier(0.16,1,0.3,1);
}
.camera-overlay.active .camera-modal { transform: scale(1); }
.camera-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 20px;
}
.camera-header h3 { font-size: 16px; font-weight: 700; color: white; display: flex; align-items: center; gap: 8px; }
.camera-header h3 span { color: var(--teal2); }
.btn-close-camera {
  background: rgba(255,255,255,.08); border: none;
  color: rgba(255,255,255,.6); border-radius: 50%;
  width: 32px; height: 32px; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: 16px; transition: all .2s;
}
.btn-close-camera:hover { background: rgba(239,68,68,.2); color: #fca5a5; }

.camera-viewfinder {
  position: relative; border-radius: 16px; overflow: hidden;
  background: #000; aspect-ratio: 4/3;
  border: 2px solid rgba(255,255,255,.1);
}
#cameraVideo { width: 100%; height: 100%; object-fit: cover; display: block; transform: scaleX(-1); }
#cameraCanvas { display: none; }
.scan-overlay {
  position: absolute; inset: 0; pointer-events: none;
  background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(13,148,136,.04) 2px, rgba(13,148,136,.04) 4px);
}
.corner { position: absolute; width: 24px; height: 24px; border-color: var(--teal2); border-style: solid; opacity: .8; }
.corner.tl { top: 12px; left: 12px; border-width: 2px 0 0 2px; border-radius: 4px 0 0 0; }
.corner.tr { top: 12px; right: 12px; border-width: 2px 2px 0 0; border-radius: 0 4px 0 0; }
.corner.bl { bottom: 12px; left: 12px; border-width: 0 0 2px 2px; border-radius: 0 0 0 4px; }
.corner.br { bottom: 12px; right: 12px; border-width: 0 2px 2px 0; border-radius: 0 0 4px 0; }
.scan-line {
  position: absolute; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, transparent, var(--teal), var(--teal2), var(--teal), transparent);
  animation: scanMove 2.5s ease-in-out infinite; opacity: .7;
}
@keyframes scanMove { 0%{top:8%;} 50%{top:88%;} 100%{top:8%;} }
.camera-status { margin-top: 16px; text-align: center; }
.camera-status-text { font-size: 14px; color: rgba(255,255,255,.7); margin-bottom: 8px; }
.camera-countdown {
  font-size: 48px; font-weight: 900; color: var(--teal2);
  font-family: 'DM Mono', monospace; letter-spacing: -2px; line-height: 1; transition: transform .5s;
}
.camera-countdown.pulse { animation: cPulse .25s ease; }
@keyframes cPulse { 0%{transform:scale(1);} 50%{transform:scale(1.15);} 100%{transform:scale(1);} }
.progress-bar-wrap { background: rgba(255,255,255,.08); border-radius: 99px; height: 4px; overflow: hidden; margin-top: 12px; }
.progress-bar-inner { height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--teal), var(--teal2)); width: 100%; transition: width 1s linear; }
.photo-taken-wrap { display: none; flex-direction: column; align-items: center; gap: 16px; }
.photo-taken-wrap img { width: 100%; border-radius: 14px; border: 2px solid var(--teal); transform: scaleX(-1); }
.photo-taken-badge {
  display: flex; align-items: center; gap: 8px;
  background: rgba(13,148,136,.2); border: 1px solid rgba(13,148,136,.4);
  border-radius: 99px; padding: 8px 18px;
  font-size: 13px; color: #5eead4; font-weight: 600;
}
.camera-actions { display: flex; gap: 10px; margin-top: 16px; }
.btn-retake {
  flex: 1; padding: 12px;
  background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.15);
  border-radius: 12px; color: rgba(255,255,255,.7);
  font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif;
  cursor: pointer; transition: all .2s;
}
.btn-retake:hover { background: rgba(255,255,255,.12); color: white; }
.btn-validate-photo {
  flex: 2; padding: 12px;
  background: linear-gradient(135deg, var(--teal), var(--teal2));
  border: none; border-radius: 12px; color: white;
  font-size: 14px; font-weight: 700; font-family: 'DM Sans', sans-serif;
  cursor: pointer; transition: all .2s; box-shadow: 0 4px 16px rgba(13,148,136,.35);
}
.btn-validate-photo:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(13,148,136,.5); }
.camera-error {
  display: none; padding: 16px;
  background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3);
  border-radius: 12px; text-align: center; color: #fca5a5;
  font-size: 13px; line-height: 1.6;
}

.bg-clock {
  position: fixed; bottom: 36px; right: 36px; z-index: 0;
  font-size: 76px; font-weight: 800; color: rgba(255,255,255,.025);
  font-family: 'DM Mono', monospace; letter-spacing: -4px;
  pointer-events: none; user-select: none;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="login-wrap">
  <div class="login-card">

    <a href="<?php echo e(route('badge.pointage')); ?>" class="login-back">← Retour</a>

    <div class="type-pill <?php echo e($intent); ?>">
      <?php echo $intent === 'entree' ? '📥 Entrée' : '📤 Sortie'; ?>

    </div>
    <div class="login-title">
      <?php echo e($intent === 'entree' ? 'Pointage Entrée' : 'Pointage Sortie'); ?>

    </div>
    <div class="login-sub">Authentifiez-vous avec votre PIN, signature et photo</div>

    <?php if($errors->any()): ?>
      <div class="alert-error">⚠ <?php echo e($errors->first()); ?></div>
    <?php endif; ?>
    <?php if(session('warning')): ?>
      <div class="alert-warning">⚠ <?php echo e(session('warning')); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('badge.auth.validate')); ?>" id="authForm" novalidate>
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action"       value="<?php echo e($action); ?>">
      <input type="hidden" name="face_photo"   id="facePhotoData">
      <input type="hidden" name="shift_type"   id="shiftTypeInput" value="normal">

      
      <input type="hidden" name="geo_latitude"  id="geo_latitude"  value="">
      <input type="hidden" name="geo_longitude" id="geo_longitude" value="">
      <input type="hidden" name="geo_accuracy"  id="geo_accuracy"  value="">
      <input type="hidden" name="geo_address"   id="geo_address"   value="">
      <input type="hidden" name="geo_denied"    id="geo_denied"    value="1">

      
      <div class="shift-type-wrap">
        <span class="field-label">Type de shift</span>
        <div class="shift-type-group">
          <label class="shift-radio-opt active" data-value="normal">
            <input type="radio" name="_shift_display" value="normal" checked>
            <span class="shift-dot"></span> Shift
          </label>
          <label class="shift-radio-opt" data-value="garde">
            <input type="radio" name="_shift_display" value="garde">
            <span class="shift-dot"></span> Garde
          </label>
        </div>
      </div>

      
      <div class="radio-group">
        <span class="field-label">Votre action</span>
        <?php if($intent === 'entree'): ?>
          <label class="radio-opt">
            <input type="radio" name="action_sub" value="debut" checked>
            <div><div class="radio-lbl">Début de shift</div><div class="radio-sub">Première pointée de la journée</div></div>
          </label>
          <label class="radio-opt">
            <input type="radio" name="action_sub" value="retour_pause">
            <div><div class="radio-lbl">Retour de pause</div><div class="radio-sub">Reprise après une pause</div></div>
          </label>
        <?php else: ?>
          <label class="radio-opt sortie-opt">
            <input type="radio" name="action_sub" value="sortie_pause" checked>
            <div><div class="radio-lbl">Partir en pause</div><div class="radio-sub">Reprise prévue dans ce shift</div></div>
          </label>
          <label class="radio-opt sortie-opt">
            <input type="radio" name="action_sub" value="fin_shift">
            <div><div class="radio-lbl">Fin de shift</div><div class="radio-sub">Terminer ma journée de travail</div></div>
          </label>
        <?php endif; ?>
      </div>

      
      <div class="field">
        <label class="field-label" for="pin">Code PIN (4 chiffres + 2 lettres)</label>
        <input type="password" id="pin" name="pin"
          maxlength="6" pattern="[0-9]{4}[A-Z]{2}"
          class="form-input <?php echo e($errors->has('pin') ? 'error' : ''); ?>"
          placeholder="1234AB" autocomplete="one-time-code" inputmode="text"
          oninput="this.value=this.value.toUpperCase(); checkReady();">
      </div>

      
      <div class="field">
        <label class="field-label">Signature</label>
        <canvas id="sigCanvas" class="sig-canvas" height="140"></canvas>
        <input type="hidden" name="signature" id="signatureData">
        <div class="sig-hint">Dessinez votre signature · Double-clic pour effacer</div>
      </div>

      
      <div class="geo-cam-grid">

        
        <div class="geo-square loading" id="geoSquare">
          <div class="geo-dot"></div>
          <div class="geo-square-title" id="geoSquareTitle">Localisation…</div>
          <div class="geo-square-sub"   id="geoSquareSub"></div>
        </div>

        
        <div class="cam-square" id="camSquare" role="button" tabindex="0" title="Capture faciale obligatoire">
          <img class="cam-thumb" id="camThumb" src="" alt="">
          <div class="cam-check" id="camCheck">✓</div>
          <div class="cam-required-badge">Requis</div>
          <div class="cam-square-title" id="camSquareTitle">Capture faciale</div>
          <div class="cam-square-sub"   id="camSquareSub">Obligatoire · Cliquez pour activer</div>
        </div>

      </div>
      

      <button type="submit" class="btn-submit" id="submitBtn" disabled>
        Valider le pointage →
      </button>
    </form>

  </div>
</div>


<div class="camera-overlay" id="cameraOverlay">
  <div class="camera-modal">
    <div class="camera-header">
      <h3>📸 Capture faciale <span>Badgeuse</span></h3>
      <button class="btn-close-camera" id="btnCloseCamera" title="Fermer">✕</button>
    </div>

    <div class="camera-viewfinder" id="cameraViewfinder">
      <video id="cameraVideo" autoplay playsinline muted></video>
      <canvas id="cameraCanvas"></canvas>
      <div class="scan-overlay"></div>
      <div class="scan-line" id="scanLine"></div>
      <div class="corner tl"></div>
      <div class="corner tr"></div>
      <div class="corner bl"></div>
      <div class="corner br"></div>
    </div>

    <div class="camera-error" id="cameraError">
      <div style="font-size:32px;margin-bottom:8px;">🚫</div>
      <strong>Accès caméra refusé</strong><br>
      Veuillez autoriser l'accès dans les paramètres de votre navigateur.
    </div>

    <div class="camera-status" id="cameraStatus">
      <div class="camera-status-text" id="cameraStatusText">Positionnez votre visage dans le cadre…</div>
      <div class="camera-countdown" id="cameraCountdown">5</div>
      <div class="progress-bar-wrap">
        <div class="progress-bar-inner" id="progressBarInner"></div>
      </div>
    </div>

    <div class="photo-taken-wrap" id="photoTakenWrap">
      <img id="capturedPhotoPreview" src="" alt="Photo capturée">
      <div class="photo-taken-badge">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Photo prise avec succès
      </div>
      <div class="camera-actions">
        <button type="button" class="btn-retake" id="btnRetake">↺ Reprendre</button>
        <button type="button" class="btn-validate-photo" id="btnValidatePhoto">✓ Valider cette photo</button>
      </div>
    </div>
  </div>
</div>

<div class="bg-clock" id="bgClock"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
/* ── Horloge ─────────────────────────────────────────────── */
(function(){
  var el = document.getElementById('bgClock');
  var tick = function() {
    if (el) el.textContent = new Date().toLocaleTimeString('fr-FR', {
      hour: '2-digit', minute: '2-digit', timeZone: 'Africa/Casablanca'
    });
  };
  tick(); setInterval(tick, 1000);
})();

/* ── Sélecteur type de shift ─────────────────────────────── */
(function(){
  var inp = document.getElementById('shiftTypeInput');
  document.querySelectorAll('.shift-radio-opt').forEach(function(el) {
    el.addEventListener('click', function() {
      document.querySelectorAll('.shift-radio-opt').forEach(function(o) { o.classList.remove('active'); });
      el.classList.add('active');
      inp.value = el.dataset.value;
    });
  });
})();

/* ── Signature ──────────────────────────────────────────── */
(function(){
  var canvas = document.getElementById('sigCanvas');
  var ctx    = canvas.getContext('2d');
  var inp    = document.getElementById('signatureData');
  var dr     = false;

  function resize(){
    canvas.width  = canvas.offsetWidth;
    canvas.height = 140;
    ctx.strokeStyle = 'rgba(255,255,255,.88)';
    ctx.lineWidth   = 2.5;
    ctx.lineCap = ctx.lineJoin = 'round';
  }
  resize();
  new ResizeObserver(resize).observe(canvas);

  var p = function(e) {
    var r = canvas.getBoundingClientRect(), s = e.touches ? e.touches[0] : e;
    return { x: s.clientX - r.left, y: s.clientY - r.top };
  };
  var start = function(e) { e.preventDefault(); dr = true; var pt = p(e); ctx.beginPath(); ctx.moveTo(pt.x, pt.y); };
  var move  = function(e) { e.preventDefault(); if (!dr) return; var pt = p(e); ctx.lineTo(pt.x, pt.y); ctx.stroke(); inp.value = canvas.toDataURL(); canvas.classList.add('has-sig'); checkReady(); };
  var stop  = function(e) { e.preventDefault(); dr = false; };

  canvas.addEventListener('mousedown',  start);
  canvas.addEventListener('mousemove',  move);
  canvas.addEventListener('mouseup',    stop);
  canvas.addEventListener('mouseleave', stop);
  canvas.addEventListener('touchstart', start, {passive: false});
  canvas.addEventListener('touchmove',  move,  {passive: false});
  canvas.addEventListener('touchend',   stop,  {passive: false});
  canvas.addEventListener('dblclick', function() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    inp.value = ''; canvas.classList.remove('has-sig'); checkReady();
  });
})();

/* ── Vérification bouton ────────────────────────────────── */
/* PHOTO OBLIGATOIRE : les 3 conditions doivent être vraies  */
function checkReady(){
  var pinOk   = document.getElementById('pin').value.length === 6;
  var sigOk   = document.getElementById('signatureData').value.length > 0;
  var photoOk = document.getElementById('facePhotoData').value.length > 0;
  var ok = pinOk && sigOk && photoOk;
  var btn = document.getElementById('submitBtn');
  btn.disabled = !ok;
  btn.classList.toggle('ready', ok);
}

/* ══════════════════════════════════════════════════════════
   GÉOLOCALISATION
══════════════════════════════════════════════════════════ */
var geoResolved   = false;
var pendingSubmit = false;
var geoTimer      = null;

function setGeoSquare(state, title, sub){
  var sq = document.getElementById('geoSquare');
  if (!sq) return;
  sq.className = 'geo-square ' + state;
  document.getElementById('geoSquareTitle').textContent = title;
  document.getElementById('geoSquareSub').textContent   = sub || '';
}

function fillGeoInputs(lat, lng, acc, addr){
  document.getElementById('geo_latitude').value  = (lat != null) ? lat : '';
  document.getElementById('geo_longitude').value = (lng != null) ? lng : '';
  document.getElementById('geo_accuracy').value  = (acc != null) ? acc : '';
  document.getElementById('geo_address').value   = addr || '';
  document.getElementById('geo_denied').value    = (lat == null) ? '1' : '0';
}

function resolveGeo(ok, lat, lng, acc, addr, title, sub){
  if (geoResolved) return;
  geoResolved = true;
  if (geoTimer) { clearTimeout(geoTimer); geoTimer = null; }

  if (ok) {
    fillGeoInputs(lat, lng, acc, addr);
    setGeoSquare('ok', title, sub);
  } else {
    fillGeoInputs(null, null, null, null);
    setGeoSquare('denied', title, sub || 'Pointage autorisé sans GPS');
  }

  if (pendingSubmit) document.getElementById('authForm').submit();
}

function reverseGeocode(lat, lng){
  return fetch(
    'https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json&zoom=18&accept-language=fr',
    { headers: { 'Accept-Language': 'fr', 'User-Agent': 'HospitalRH-Badge/1.0' } }
  )
  .then(function(r) { return r.ok ? r.json() : null; })
  .then(function(data) {
    if (!data) return null;
    var a       = data.address || {};
    var road    = a.road || a.pedestrian || a.footway || null;
    var num     = a.house_number || null;
    var quarter = a.quarter || a.neighbourhood || a.suburb || null;
    var city    = a.city || a.town || a.village || null;
    var state   = a.state || a.region || null;
    var country = a.country || null;
    var street  = road ? (num ? num + ' ' + road : road) : null;
    var parts   = [street, quarter, city, state, country].filter(Boolean);
    return parts.length ? parts.join(', ') : (data.display_name || null);
  })
  .catch(function() { return null; });
}

var isSecure = location.protocol === 'https:'
            || location.hostname === 'localhost'
            || location.hostname === '127.0.0.1'
            || location.hostname.endsWith('.local');

if (!isSecure) {
  resolveGeo(false, null, null, null, null, '⚠ HTTPS requis', 'Activez HTTPS pour le GPS');
} else if (!('geolocation' in navigator)) {
  resolveGeo(false, null, null, null, null, 'Non supporté', '');
} else {
  geoTimer = setTimeout(function() {
    resolveGeo(false, null, null, null, null, 'Délai dépassé', 'Sans coordonnées GPS');
  }, 15000);

  navigator.geolocation.getCurrentPosition(
    function(pos){
      var lat = pos.coords.latitude, lng = pos.coords.longitude, acc = Math.round(pos.coords.accuracy);
      setGeoSquare('loading', 'Adresse en cours…', lat.toFixed(4) + '° · ' + Math.abs(lng).toFixed(4) + '°');
      reverseGeocode(lat, lng).then(function(addr) {
        var sub = addr || (lat.toFixed(5) + '° · ' + Math.abs(lng).toFixed(5) + '° · ±' + acc + 'm');
        resolveGeo(true, lat, lng, acc, addr, 'Position enregistrée', sub);
      });
    },
    function(err){
      var msgs = {1: 'Géoloc refusée', 2: 'Position indisponible', 3: 'Délai dépassé'};
      resolveGeo(false, null, null, null, null, msgs[err.code] || 'Localisation impossible', '');
    },
    { enableHighAccuracy: true, timeout: 13000, maximumAge: 0 }
  );
}

/* ── Interception submit ──────────────────────────────── */
document.getElementById('authForm').addEventListener('submit', function(e){
  /* Sécurité côté client : bloquer si photo manquante */
  if (!document.getElementById('facePhotoData').value) {
    e.preventDefault();
    var sq = document.getElementById('camSquare');
    sq.style.animation = 'none';
    sq.style.borderColor = 'rgba(239,68,68,.8)';
    sq.style.background  = 'rgba(239,68,68,.15)';
    setTimeout(function() { sq.style.borderColor = ''; sq.style.background = ''; }, 1800);
    document.getElementById('camSquare').scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }

  if (!geoResolved) {
    e.preventDefault();
    pendingSubmit = true;
    var btn = document.getElementById('submitBtn');
    btn.textContent = '⏳ Localisation…'; btn.style.opacity = '.75'; btn.style.cursor = 'wait';
  }
});

/* ══════════════════════════════════════════════════════════
   CAMÉRA
══════════════════════════════════════════════════════════ */
var cameraOverlay    = document.getElementById('cameraOverlay');
var cameraVideo      = document.getElementById('cameraVideo');
var cameraCanvas     = document.getElementById('cameraCanvas');
var cameraError      = document.getElementById('cameraError');
var cameraStatus     = document.getElementById('cameraStatus');
var cameraStatusText = document.getElementById('cameraStatusText');
var cameraCountdown  = document.getElementById('cameraCountdown');
var progressBarInner = document.getElementById('progressBarInner');
var photoTakenWrap   = document.getElementById('photoTakenWrap');
var capturedPreview  = document.getElementById('capturedPhotoPreview');
var facePhotoDataEl  = document.getElementById('facePhotoData');
var scanLine         = document.getElementById('scanLine');
var cameraViewfinder = document.getElementById('cameraViewfinder');

var mediaStream     = null;
var countdownTimer  = null;
var secondsLeft     = 5;
var capturedDataUrl = null;

var camSquare = document.getElementById('camSquare');
camSquare.addEventListener('click', openCamera);
camSquare.addEventListener('keydown', function(e) { if (e.key === 'Enter' || e.key === ' ') openCamera(); });

document.getElementById('btnCloseCamera').addEventListener('click', closeCamera);
document.getElementById('btnRetake').addEventListener('click', retakePhoto);
document.getElementById('btnValidatePhoto').addEventListener('click', validatePhoto);

function openCamera(){
  cameraError.style.display       = 'none';
  cameraStatus.style.display      = 'block';
  cameraViewfinder.style.display  = 'block';
  photoTakenWrap.style.display    = 'none';
  scanLine.style.display          = 'block';
  secondsLeft = 5;
  cameraCountdown.textContent     = '5';
  progressBarInner.style.width    = '100%';
  cameraStatusText.textContent    = 'Positionnez votre visage dans le cadre…';
  capturedDataUrl                 = null;
  cameraOverlay.classList.add('active');

  navigator.mediaDevices.getUserMedia({
    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
    audio: false
  })
  .then(function(stream) {
    mediaStream = stream;
    cameraVideo.srcObject = stream;
    return cameraVideo.play();
  })
  .then(function() {
    startCountdown();
  })
  .catch(function() {
    cameraError.style.display      = 'block';
    cameraViewfinder.style.display = 'none';
    cameraStatus.style.display     = 'none';
  });
}

function startCountdown(){
  clearInterval(countdownTimer);
  secondsLeft = 5; updateCountdownUI();
  countdownTimer = setInterval(function() {
    secondsLeft--; updateCountdownUI();
    if (secondsLeft <= 0) { clearInterval(countdownTimer); takePhoto(); }
  }, 1000);
}

function updateCountdownUI(){
  cameraCountdown.textContent = secondsLeft;
  cameraCountdown.classList.remove('pulse');
  void cameraCountdown.offsetWidth;
  cameraCountdown.classList.add('pulse');
  progressBarInner.style.width = ((secondsLeft / 5) * 100) + '%';
  cameraStatusText.textContent = secondsLeft <= 3 && secondsLeft > 0
    ? '😊 Souriez ! Capture dans ' + secondsLeft + '…'
    : 'Positionnez votre visage dans le cadre…';
}

function takePhoto(){
  if (!mediaStream) return;
  var vw = cameraVideo.videoWidth || 640, vh = cameraVideo.videoHeight || 480;
  cameraCanvas.width = vw; cameraCanvas.height = vh;
  var cctx = cameraCanvas.getContext('2d');
  cctx.translate(vw, 0); cctx.scale(-1, 1);
  cctx.drawImage(cameraVideo, 0, 0, vw, vh);
  capturedDataUrl = cameraCanvas.toDataURL('image/jpeg', .85);

  var flash = document.createElement('div');
  flash.style.cssText = 'position:absolute;inset:0;background:white;border-radius:14px;z-index:10;opacity:1;transition:opacity .4s';
  cameraViewfinder.appendChild(flash);
  setTimeout(function() { flash.style.opacity = '0'; setTimeout(function() { flash.remove(); }, 400); }, 50);

  scanLine.style.display = 'none';
  setTimeout(function() {
    capturedPreview.src            = capturedDataUrl;
    cameraStatus.style.display     = 'none';
    cameraViewfinder.style.display = 'none';
    photoTakenWrap.style.display   = 'flex';
  }, 300);
  stopStream();
}

function retakePhoto(){
  capturedDataUrl = null;
  photoTakenWrap.style.display   = 'none';
  cameraError.style.display      = 'none';
  cameraStatus.style.display     = 'block';
  cameraViewfinder.style.display = 'block';
  scanLine.style.display         = 'block';
  secondsLeft = 5; cameraCountdown.textContent = '5';
  progressBarInner.style.width   = '100%';
  cameraStatusText.textContent   = 'Positionnez votre visage dans le cadre…';
  navigator.mediaDevices.getUserMedia({
    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
    audio: false
  })
  .then(function(stream) { mediaStream = stream; cameraVideo.srcObject = stream; cameraVideo.play(); startCountdown(); })
  .catch(function() {
    cameraError.style.display      = 'block';
    cameraViewfinder.style.display = 'none';
    cameraStatus.style.display     = 'none';
  });
}

function validatePhoto(){
  if (!capturedDataUrl) return;
  facePhotoDataEl.value = capturedDataUrl;
  var sq = document.getElementById('camSquare');
  sq.classList.add('has-photo');
  document.getElementById('camThumb').src              = capturedDataUrl;
  document.getElementById('camSquareTitle').textContent = 'Photo enregistrée';
  document.getElementById('camSquareSub').textContent   = 'Cliquez pour reprendre';
  closeCamera();
  /* Mettre à jour le bouton submit maintenant que la photo est dispo */
  checkReady();
}

function closeCamera(){
  clearInterval(countdownTimer);
  stopStream();
  cameraOverlay.classList.remove('active');
}
function stopStream(){
  if (mediaStream) { mediaStream.getTracks().forEach(function(t) { t.stop(); }); mediaStream = null; }
}

cameraOverlay.addEventListener('click', function(e) { if (e.target === cameraOverlay) closeCamera(); });
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape' && cameraOverlay.classList.contains('active')) closeCamera();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.badge', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/badge/login.blade.php ENDPATH**/ ?>