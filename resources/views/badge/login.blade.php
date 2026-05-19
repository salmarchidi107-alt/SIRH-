@extends('layouts.badge')
@section('title', 'Authentification — Pointage')

@push('styles')
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
.radio-group { margin-bottom: 24px; }
.radio-opt {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 16px; border-radius: 14px;
  border: 1px solid rgba(255,255,255,.08);
  margin-bottom: 8px; cursor: pointer; transition: background .15s, border-color .15s;
}
.radio-opt:has(input:checked)           { border-color: var(--teal);  background: rgba(13,148,136,.08); }
.radio-opt:has(input:checked).sortie-opt{ border-color: var(--amber); background: rgba(245,158,11,.08); }
.radio-opt input[type=radio]            { accent-color: var(--teal);  width:16px; height:16px; flex-shrink:0; }
.sortie-opt input[type=radio]           { accent-color: var(--amber); }
.radio-lbl { font-size: 14px; font-weight: 600; }
.radio-sub { font-size: 12px; color: rgba(255,255,255,.4); margin-top: 2px; }

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

.sig-canvas {
  width: 100%; height: 140px; display: block;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 14px; cursor: crosshair; transition: border-color .2s;
}
.sig-canvas.has-sig { border-color: rgba(255,255,255,.3); }
.sig-hint { font-size: 11px; color: rgba(255,255,255,.3); margin-top: 6px; margin-bottom: 4px; }

/* ── Bloc géolocalisation ── */
.geo-bar {
  display: flex; align-items: center; gap: 10px;
  padding: 11px 16px; border-radius: 12px; margin-bottom: 20px;
  font-size: 12px; min-height: 44px; transition: all .35s ease;
}
.geo-bar.loading { background: rgba(245,158,11,.08); border: 1px solid rgba(245,158,11,.2); color: #fde68a; }
.geo-bar.ok      { background: rgba(13,148,136,.08); border: 1px solid rgba(13,148,136,.2); color: var(--teal2); }
.geo-bar.denied  { background: rgba(239,68,68,.07);  border: 1px solid rgba(239,68,68,.18); color: #fca5a5; }

.geo-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.geo-bar.loading .geo-dot { background: #fde68a; animation: blink .7s ease-in-out infinite alternate; }
.geo-bar.ok      .geo-dot { background: var(--teal2); animation: pdot 2s ease-in-out infinite; }
.geo-bar.denied  .geo-dot { background: #fca5a5; }

@keyframes blink { from{opacity:.2;} to{opacity:1;} }
@keyframes pdot  { 0%,100%{box-shadow:0 0 0 0 rgba(94,234,212,.5);}50%{box-shadow:0 0 0 6px rgba(94,234,212,0);} }

.geo-bar-text  { font-weight: 600; line-height: 1.2; }
.geo-bar-sub   { font-size: 10px; opacity: .65; font-family: 'DM Mono', monospace; margin-top: 2px; }

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

.bg-clock {
  position: fixed; bottom: 36px; right: 36px; z-index: 0;
  font-size: 76px; font-weight: 800; color: rgba(255,255,255,.025);
  font-family: 'DM Mono', monospace; letter-spacing: -4px;
  pointer-events: none; user-select: none;
}
</style>
@endpush

@section('content')
<div class="login-wrap">
  <div class="login-card">

    <a href="{{ route('badge.pointage') }}" class="login-back">← Retour</a>

    <div class="type-pill {{ $intent }}">
      {!! $intent === 'entree' ? '📥 Entrée' : '📤 Sortie' !!}
    </div>
    <div class="login-title">
      {{ $intent === 'entree' ? 'Pointage Entrée' : 'Pointage Sortie' }}
    </div>
    <div class="login-sub">Authentifiez-vous avec votre PIN et signature</div>

    @if($errors->any())
      <div class="alert-error">⚠ {{ $errors->first() }}</div>
    @endif
    @if(session('warning'))
      <div class="alert-warning">⚠ {{ session('warning') }}</div>
    @endif

    <form method="POST" action="{{ route('badge.auth.validate') }}" id="authForm" novalidate>
      @csrf
      <input type="hidden" name="action" value="{{ $action }}">

      {{-- Choix action_sub --}}
      <div class="radio-group">
        <span class="field-label">Votre action</span>
        @if($intent === 'entree')
          <label class="radio-opt">
            <input type="radio" name="action_sub" value="debut" checked>
            <div><div class="radio-lbl"> Début de shift</div><div class="radio-sub">Première pointée de la journée</div></div>
          </label>
          <label class="radio-opt">
            <input type="radio" name="action_sub" value="retour_pause">
            <div><div class="radio-lbl"> Retour de pause</div><div class="radio-sub">Reprise après une pause</div></div>
          </label>
        @else
          <label class="radio-opt sortie-opt">
            <input type="radio" name="action_sub" value="sortie_pause" checked>
            <div><div class="radio-lbl">⏸ Partir en pause</div><div class="radio-sub">Reprise prévue dans ce shift</div></div>
          </label>
          <label class="radio-opt sortie-opt">
            <input type="radio" name="action_sub" value="fin_shift">
            <div><div class="radio-lbl"> Fin de shift</div><div class="radio-sub">Terminer ma journée de travail</div></div>
          </label>
        @endif
      </div>

      {{-- PIN --}}
      <div class="field">
        <label class="field-label" for="pin">Code PIN (4 chiffres + 2 lettres)</label>
        <input type="password" id="pin" name="pin"
          maxlength="6" pattern="[0-9]{4}[A-Z]{2}"
          class="form-input {{ $errors->has('pin') ? 'error' : '' }}"
          placeholder="1234AB" autocomplete="one-time-code" inputmode="text"
          oninput="this.value=this.value.toUpperCase(); checkReady();">
      </div>

      {{-- Signature --}}
      <div class="field">
        <label class="field-label">Signature</label>
        <canvas id="sigCanvas" class="sig-canvas" height="140"></canvas>
        <input type="hidden" name="signature" id="signatureData">
        <div class="sig-hint">Dessinez votre signature · Double-clic pour effacer</div>
      </div>

      {{-- ── Inputs géolocalisation (remplis par JS avant submit) ── --}}
      <input type="hidden" name="geo_latitude"  id="geo_latitude"  value="">
      <input type="hidden" name="geo_longitude" id="geo_longitude" value="">
      <input type="hidden" name="geo_accuracy"  id="geo_accuracy"  value="">
      <input type="hidden" name="geo_address"   id="geo_address"   value="">
      <input type="hidden" name="geo_denied"    id="geo_denied"    value="1">

      {{-- Statut géolocalisation --}}
      <div class="geo-bar loading" id="geoBar">
        <div class="geo-dot"></div>
        <div>
          <div class="geo-bar-text" id="geoBarText">Localisation en cours…</div>
          <div class="geo-bar-sub"  id="geoBarSub"></div>
        </div>
      </div>

      <button type="submit" class="btn-submit" id="submitBtn" disabled>
        Valider le pointage →
      </button>
    </form>

  </div>
</div>

<div class="bg-clock" id="bgClock"></div>
@endsection

@push('scripts')
<script>
/* ── Horloge décorative ─────────────────────────────────────────────── */
(function(){
  const el = document.getElementById('bgClock');
  const tick = () => { if(el) el.textContent = new Date().toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit',timeZone:'Africa/Casablanca'}); };
  tick(); setInterval(tick, 1000);
})();

/* ── Signature ──────────────────────────────────────────────────────── */
(function(){
  const canvas = document.getElementById('sigCanvas');
  const ctx    = canvas.getContext('2d');
  const inp    = document.getElementById('signatureData');
  let   dr     = false;

  function resize(){
    canvas.width  = canvas.offsetWidth;
    canvas.height = 140;
    ctx.strokeStyle = 'rgba(255,255,255,.88)';
    ctx.lineWidth   = 2.5;
    ctx.lineCap = ctx.lineJoin = 'round';
  }
  resize();
  new ResizeObserver(resize).observe(canvas);

  const p = e => { const r=canvas.getBoundingClientRect(),s=e.touches?e.touches[0]:e; return{x:s.clientX-r.left,y:s.clientY-r.top}; };
  const start = e => { e.preventDefault(); dr=true; const {x,y}=p(e); ctx.beginPath(); ctx.moveTo(x,y); };
  const move  = e => { e.preventDefault(); if(!dr)return; const {x,y}=p(e); ctx.lineTo(x,y); ctx.stroke(); inp.value=canvas.toDataURL(); canvas.classList.add('has-sig'); checkReady(); };
  const stop  = e => { e.preventDefault(); dr=false; };

  canvas.addEventListener('mousedown',  start);
  canvas.addEventListener('mousemove',  move);
  canvas.addEventListener('mouseup',    stop);
  canvas.addEventListener('mouseleave', stop);
  canvas.addEventListener('touchstart', start, {passive:false});
  canvas.addEventListener('touchmove',  move,  {passive:false});
  canvas.addEventListener('touchend',   stop,  {passive:false});
  canvas.addEventListener('dblclick', () => {
    ctx.clearRect(0,0,canvas.width,canvas.height);
    inp.value=''; canvas.classList.remove('has-sig'); checkReady();
  });
})();

/* ── Vérification bouton ────────────────────────────────────────────── */
function checkReady(){
  const ok = document.getElementById('pin').value.length===6
          && document.getElementById('signatureData').value.length>0;
  const btn = document.getElementById('submitBtn');
  btn.disabled = !ok;
  btn.classList.toggle('ready', ok);
}

/* ══════════════════════════════════════════════════════════════════════
   GÉOLOCALISATION
══════════════════════════════════════════════════════════════════════ */
let geoResolved   = false;
let pendingSubmit = false;
let geoTimer      = null;

function setGeoBar(state, main, sub){
  const bar = document.getElementById('geoBar');
  if (!bar) return;
  bar.className = 'geo-bar ' + state;
  document.getElementById('geoBarText').textContent = main;
  document.getElementById('geoBarSub').textContent  = sub || '';
}

function fillGeoInputs(lat, lng, acc, addr){
  document.getElementById('geo_latitude').value  = (lat !== null && lat !== undefined) ? lat : '';
  document.getElementById('geo_longitude').value = (lng !== null && lng !== undefined) ? lng : '';
  document.getElementById('geo_accuracy').value  = (acc !== null && acc !== undefined) ? acc : '';
  document.getElementById('geo_address').value   = addr || '';
  document.getElementById('geo_denied').value    = (lat === null || lat === undefined) ? '1' : '0';
}

function resolveGeo(ok, lat, lng, acc, addr, statusMain, statusSub){
  if (geoResolved) return;
  geoResolved = true;
  if (geoTimer) { clearTimeout(geoTimer); geoTimer = null; }

  if (ok) {
    fillGeoInputs(lat, lng, acc, addr);
    setGeoBar('ok', statusMain, statusSub);
  } else {
    fillGeoInputs(null, null, null, null);
    setGeoBar('denied', statusMain, statusSub || 'Pointage autorisé sans coordonnées GPS');
  }

  if (pendingSubmit) {
    document.getElementById('authForm').submit();
  }
}

/**
 * Reverse geocoding via Nominatim.
 * Retourne une promesse qui résout avec l'adresse (string) ou null.
 */
function reverseGeocode(lat, lng) {
  return fetch(
    'https://nominatim.openstreetmap.org/reverse'
    + '?lat=' + lat
    + '&lon=' + lng
    + '&format=json'
    + '&zoom=18'
    + '&accept-language=fr',
    {
      headers: {
        'Accept-Language': 'fr',
        'User-Agent': 'HospitalRH-Badge/1.0'
      }
    }
  )
  .then(r => {
    if (!r.ok) return null;
    return r.json();
  })
  .then(data => {
    if (!data) return null;
    const a = data.address || {};

    // Construire une adresse lisible et complète
    const road    = a.road || a.pedestrian || a.footway || a.path || null;
    const num     = a.house_number || null;
    const quarter = a.quarter || a.neighbourhood || a.suburb || null;
    const city    = a.city || a.town || a.village || a.municipality || null;
    const state   = a.state || a.region || null;
    const country = a.country || null;

    // Rue + numéro
    let street = null;
    if (road && num)       street = num + ' ' + road;
    else if (road)         street = road;

    const parts = [street, quarter, city, state, country].filter(Boolean);

    return parts.length > 0
      ? parts.join(', ')
      : (data.display_name || null);
  })
  .catch(() => null);
}

/* ── Vérification HTTPS ── */
const isSecure = location.protocol === 'https:'
              || location.hostname === 'localhost'
              || location.hostname === '127.0.0.1'
              || location.hostname.endsWith('.local');

if (!isSecure) {
  resolveGeo(false, null, null, null, null,
    '⚠ HTTPS requis pour la géolocalisation',
    'Activez HTTPS sur votre serveur pour activer le GPS'
  );

} else if (!('geolocation' in navigator)) {
  resolveGeo(false, null, null, null, null,
    'Géolocalisation non supportée', ''
  );

} else {
  // Timeout de sécurité : 15 s
  geoTimer = setTimeout(() => {
    resolveGeo(false, null, null, null, null,
      'Délai de localisation dépassé',
      'Pointage autorisé sans coordonnées GPS'
    );
  }, 15000);

  navigator.geolocation.getCurrentPosition(
    function(pos) {
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;
      const acc = Math.round(pos.coords.accuracy);

      // Affichage intermédiaire pendant le reverse geocoding
      setGeoBar('loading', 'Position détectée, récupération de l\'adresse…',
        lat.toFixed(5) + '° N · ' + Math.abs(lng).toFixed(5) + '° ' + (lng < 0 ? 'W' : 'E'));

      // Reverse geocoding côté client
      reverseGeocode(lat, lng).then(addr => {
        const sub = addr
          ? addr
          : (lat.toFixed(5) + '° N · ' + Math.abs(lng).toFixed(5) + '° ' + (lng < 0 ? 'W' : 'E') + ' · ±' + acc + ' m');

        resolveGeo(true, lat, lng, acc, addr, ' Position enregistrée', sub);
      });
    },
    function(err) {
      const msgs = {
        1: 'Géolocalisation refusée par l\'utilisateur',
        2: 'Position GPS indisponible',
        3: 'Délai de localisation dépassé',
      };
      resolveGeo(false, null, null, null, null,
        msgs[err.code] || 'Localisation impossible', ''
      );
    },
    { enableHighAccuracy: true, timeout: 13000, maximumAge: 0 }
  );
}

/* ── Interception du submit ─────────────────────────────────────────── */
document.getElementById('authForm').addEventListener('submit', function(e){
  if (!geoResolved) {
    e.preventDefault();
    pendingSubmit = true;
    const btn = document.getElementById('submitBtn');
    btn.textContent   = '⏳ Localisation en cours…';
    btn.style.opacity = '.75';
    btn.style.cursor  = 'wait';
    return;
  }
  // Géoloc répondue → submit normal
});
</script>
@endpush
