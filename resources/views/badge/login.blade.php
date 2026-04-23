{{-- resources/views/badge/login.blade.php --}}
@extends('layouts.badge')
@section('title', 'Connexion — Pointage')

@push('styles')
<style>
.login-wrap {
  flex: 1; display: flex; align-items: center; justify-content: center;
  padding: 24px; min-height: 100vh;
}
.login-card {
  width: 100%; max-width: 420px;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 28px;
  padding: 44px 40px;
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  box-shadow: 0 32px 80px rgba(0,0,0,0.4);
  animation: cardIn .5s cubic-bezier(0.16,1,0.3,1);
}
@keyframes cardIn {
  from { opacity:0; transform: translateY(30px) scale(.97); }
  to   { opacity:1; transform: translateY(0) scale(1); }
}
/* Logo */
.login-logo {
  display: flex; align-items: center; justify-content: center;
  gap: 12px; margin-bottom: 32px;
}
.logo-icon {
  width: 56px; height: 56px; border-radius: 16px;
  background: linear-gradient(135deg, var(--teal), var(--teal2));
  display: flex; align-items: center; justify-content: center;
  font-size: 26px;
  box-shadow: 0 8px 24px rgba(13,148,136,.4);
}
.logo-text h1 { font-size: 22px; font-weight: 800; letter-spacing: -.5px; }
.logo-text p  { font-size: 13px; color: rgba(255,255,255,.5); margin-top: 2px; }

/* Titre */
.login-title {
  text-align: center; margin-bottom: 32px;
}
.login-title h2 { font-size: 20px; font-weight: 700; }
.login-title p  { font-size: 14px; color: rgba(255,255,255,.5); margin-top: 6px; }

/* Champs */
.form-group { margin-bottom: 18px; }
.form-label {
  display: block; font-size: 13px; font-weight: 600;
  color: rgba(255,255,255,.7); margin-bottom: 8px;
}
.form-input {
  width: 100%; background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.15);
  border-radius: 14px; padding: 14px 18px;
  font-size: 15px; color: white; font-family: 'DM Sans', sans-serif;
  outline: none; transition: all .2s;
}
.form-input::placeholder { color: rgba(255,255,255,.3); }
.form-input:focus {
  border-color: var(--teal);
  background: rgba(13,148,136,.12);
  box-shadow: 0 0 0 3px rgba(13,148,136,.2);
}
.form-input.error { border-color: var(--red); }

/* Erreur */
.error-msg {
  background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.3);
  border-radius: 12px; padding: 12px 16px;
  font-size: 13px; color: #fca5a5; margin-bottom: 20px;
  display: flex; align-items: center; gap: 8px;
}

/* Remember */
.remember-row {
  display: flex; align-items: center; gap: 8px;
  margin-bottom: 24px; cursor: pointer;
}
.remember-row input[type=checkbox] { accent-color: var(--teal); width: 16px; height: 16px; }
.remember-row span { font-size: 13px; color: rgba(255,255,255,.6); }

/* Bouton submit */
.btn-login {
  width: 100%; padding: 16px;
  background: linear-gradient(135deg, var(--teal), var(--teal2));
  color: white; border: none; border-radius: 14px;
  font-size: 16px; font-weight: 700; font-family: 'DM Sans', sans-serif;
  cursor: pointer; transition: all .25s;
  box-shadow: 0 8px 24px rgba(13,148,136,.35);
  position: relative; overflow: hidden;
}
.btn-login::after {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,.1), transparent);
  pointer-events: none;
}
.btn-login:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(13,148,136,.5); }
.btn-login:active { transform: translateY(0); }

/* Footer */
.login-footer {
  text-align: center; margin-top: 28px;
  font-size: 12px; color: rgba(255,255,255,.3);
}
.login-footer a {
  color: rgba(255,255,255,.5); text-decoration: none;
  transition: color .2s;
}
.login-footer a:hover { color: var(--teal2); }

/* Horloge en fond */
.bg-clock {
  position: fixed; bottom: 40px; right: 40px; z-index: 0;
  font-size: 80px; font-weight: 800; color: rgba(255,255,255,.03);
  font-family: 'DM Mono', monospace; letter-spacing: -4px;
  pointer-events: none; user-select: none;
}
</style>
@endpush

@section('content')
<div class="login-wrap">
  <div class="login-card">

    <div class="login-logo">
      <div class="logo-icon">⏱</div>
      <div class="logo-text">
        <h1>HospitalRH</h1>
        <p>Système de pointage</p>
      </div>
    </div>

    <div class="login-title">
      <h2>{{ $action === 'entree' ? 'Pointage Entrée' : 'Pointage Sortie' }}</h2>
      <p>Authentifiez-vous avec votre PIN et signature</p>
    </div>

    @if($errors->any())
      <div class="error-msg">
        ⚠️ {{ $errors->first() }}
      </div>
    @endif

    @if(session('warning'))
      <div class="error-msg" style="border-color:rgba(245,158,11,.3);background:rgba(245,158,11,.1);color:#fde68a;">
        ⚠️ {{ session('warning') }}
      </div>
    @endif

    <form method="POST" action="{{ route('badge.auth.validate') }}" id="authForm">
      @csrf
<input type="hidden" name="action" value="{{ $action }}">
      @if (isset($intent))
      <div class="form-group">
        <label class="form-label">Choisissez votre action</label>
        @if($intent === 'entree')
        <label style="display:block; margin-bottom:12px; font-weight:600; color:rgba(255,255,255,.8);">
          <input type="radio" name="action_sub" value="debut" checked style="margin-right:8px; accent-color:var(--teal);">
          Début de shift
        </label>
        <label style="display:block; font-weight:600; color:rgba(255,255,255,.8);">
          <input type="radio" name="action_sub" value="retour_pause" style="margin-right:8px; accent-color:var(--teal);">
          Retour de pause
        </label>
        @elseif($intent === 'sortie')
        <label style="display:block; margin-bottom:12px; font-weight:600; color:rgba(255,255,255,.8);">
          <input type="radio" name="action_sub" value="fin_shift" checked style="margin-right:8px; accent-color:var(--amber);">
          Fin de shift
        </label>
        <label style="display:block; font-weight:600; color:rgba(255,255,255,.8);">
          <input type="radio" name="action_sub" value="sortie_pause" style="margin-right:8px; accent-color:var(--amber);">
          Sortie en pause
        </label>
        @endif
      </div>
      @endif
      <div class="form-group">
        <label class="form-label" for="pin">Code PIN (4 chiffres + 2 lettres)</label>
        <input
          type="password" id="pin" name="pin" maxlength="6" pattern="[0-9]{4}[A-Z]{2}"
          class="form-input {{ $errors->has('pin') ? 'error' : '' }}"
          placeholder="1234AB"
          autocomplete="one-time-code"
          inputmode="text"
          style="text-transform: uppercase;"
        >
      </div>

      <div class="form-group">
        <label class="form-label">Signature</label>
        <canvas id="signatureCanvas" width="380" height="180" class="form-input" style="border: 1px solid rgba(255,255,255,.15); border-radius: 12px; cursor: crosshair; background: rgba(255,255,255,.05);"></canvas>
        <input type="hidden" name="signature" id="signatureData">
        <small style="color: rgba(255,255,255,.4);">Dessinez votre signature</small>
      </div>

      <button type="submit" class="btn-login" id="submitBtn" disabled>
        Valider pointage →
      </button>
    </form>

    <div class="login-footer">
      <a href="{{ url('/') }}">← Retour à l'espace admin</a>
    </div>
  </div>
</div>

{{-- Horloge décorative en fond --}}
<div class="bg-clock" id="bgClock"></div>
@endsection

@push('scripts')
<script>
  function updateClock() {
    document.getElementById('bgClock').textContent =
      new Date().toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
  }
  updateClock();
  setInterval(updateClock, 1000);

  // Signature canvas
  const canvas = document.getElementById('signatureCanvas');
  const ctx = canvas.getContext('2d');
  const signatureData = document.getElementById('signatureData');
  const submitBtn = document.getElementById('submitBtn');
  let isDrawing = false;

  ctx.strokeStyle = 'rgb(255,255,255)';
  ctx.lineWidth = 2;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';
  ctx.fillStyle = 'rgba(255,255,255,0.05)';
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  function startDrawing(e) {
    isDrawing = true;
    ctx.beginPath();
    ctx.moveTo(e.offsetX, e.offsetY);
  }

  function draw(e) {
    if (!isDrawing) return;
    ctx.lineTo(e.offsetX, e.offsetY);
    ctx.stroke();
    signatureData.value = canvas.toDataURL('image/png');
    submitBtn.disabled = false;
  }

  function stopDrawing() {
    isDrawing = false;
  }

  canvas.addEventListener('mousedown', startDrawing);
  canvas.addEventListener('mousemove', draw);
  canvas.addEventListener('mouseup', stopDrawing);
  canvas.addEventListener('mouseout', stopDrawing);

  // Touch support
  canvas.addEventListener('touchstart', (e) => {
    e.preventDefault();
    const touch = e.touches[0];
    const rect = canvas.getBoundingClientRect();
    startDrawing({offsetX: touch.clientX - rect.left, offsetY: touch.clientY - rect.top});
  });

  canvas.addEventListener('touchmove', (e) => {
    e.preventDefault();
    const touch = e.touches[0];
    const rect = canvas.getBoundingClientRect();
    draw({offsetX: touch.clientX - rect.left, offsetY: touch.clientY - rect.top});
  });

  canvas.addEventListener('touchend', (e) => {
    e.preventDefault();
    stopDrawing();
  });

  // Clear signature
  canvas.addEventListener('dblclick', () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = 'rgba(255,255,255,0.05)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    signatureData.value = '';
    submitBtn.disabled = true;
  });

</script>
@endpush
