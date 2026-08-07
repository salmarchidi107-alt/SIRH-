@extends('layouts.badge')
@section('title', 'Pointage enregistré')

@push('styles')
{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"/>
<style>
/* ═══════════════════════════════════════════════════
   LAYOUT
═══════════════════════════════════════════════════ */
.result-page {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 28px 16px;
  min-height: 100vh;
  overflow-y: auto;
}
.result-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 22px;
  width: 100%;
  max-width: 460px;
}

/* ── Icône animée ── */
.icon-area {
  position: relative;
  display: flex; align-items: center; justify-content: center;
  width: 96px; height: 96px; flex-shrink: 0;
}
.ripple {
  position: absolute; width: 96px; height: 96px;
  border-radius: 50%; animation: ripOut 2.2s ease-out infinite;
}
.ripple:nth-child(2) { animation-delay: .65s; }
@keyframes ripOut {
  0%   { transform: scale(.45); opacity: .7; }
  100% { transform: scale(2.8); opacity: 0;  }
}
.icon-box {
  width: 96px; height: 96px; border-radius: 24px;
  display: flex; align-items: center; justify-content: center;
  font-size: 44px; position: relative; z-index: 1;
  animation: iconPop .5s cubic-bezier(.16,1,.3,1) both;
}
@keyframes iconPop {
  from { transform: scale(0) rotate(-12deg); opacity:0; }
  to   { transform: none; opacity:1; }
}

/* ── Card principale ── */
.result-card {
  width: 100%;
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.10);
  border-radius: 24px;
  padding: 28px 24px;
  backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
  animation: cardIn .5s .12s cubic-bezier(.16,1,.3,1) both;
  text-align: center;
}
@keyframes cardIn {
  from { opacity:0; transform:translateY(16px); }
  to   { opacity:1; transform:none; }
}

.type-badge {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 5px 13px; border-radius: 99px;
  font-size: 11px; font-weight: 700;
  letter-spacing: .09em; text-transform: uppercase;
  margin-bottom: 14px;
}
.emp-name { font-size: 22px; font-weight: 800; letter-spacing: -.4px; margin-bottom: 2px; }
.emp-dept { font-size: 12px; color: rgba(255,255,255,.4); margin-bottom: 18px; }
.time-lbl  { font-size: 10px; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .1em; }
.time-big  { font-family: 'DM Mono','JetBrains Mono',monospace; font-size: 50px; font-weight: 700; letter-spacing:-2px; line-height:1.1; }
.time-date { font-size: 12px; color: rgba(255,255,255,.3); margin-top: 2px; }

.div { height: 1px; background: rgba(255,255,255,.08); margin: 18px 0; }

/* ── Grille shift ── */
.sg { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; margin-bottom: 18px; }
.sc {
  text-align: center; padding: 10px 4px;
  background: rgba(255,255,255,.03);
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 12px;
}
.sc-lbl { font-size: 10px; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing:.05em; margin-bottom:5px; }
.sc-val { font-size: 17px; font-weight:700; font-family:'DM Mono',monospace; line-height:1; }
.c-teal  { color: var(--teal2); }
.c-green { color: #86efac; }
.c-amber { color: var(--amber2); }

/* ── Titre GPS ── */
.geo-title {
  font-size: 10px; font-weight:700; color: rgba(255,255,255,.3);
  text-transform: uppercase; letter-spacing: .12em;
  display: flex; align-items: center; gap: 10px; margin-bottom: 12px;
}
.geo-title::before, .geo-title::after {
  content:''; flex:1; height:1px; background: rgba(255,255,255,.07);
}

/* ════════════════════════════════
   BLOC GEO OK
════════════════════════════════ */
.geo-block {
  border-radius: 16px; overflow: hidden;
  border: 1px solid rgba(13,148,136,.22);
  margin-bottom: 18px;
}

/* ── Carte satellite Leaflet ── */
.geo-map-wrap {
  position: relative;
  width: 100%; height: 210px;
  background: #0a1628;
  border-bottom: 1px solid rgba(13,148,136,.15);
}
#satelliteMap {
  width: 100%; height: 100%;
  z-index: 1;
}

/* Badge "Vue satellite" en overlay */
.map-badge-sat {
  position: absolute; top: 10px; left: 10px; z-index: 500;
  display: flex; align-items: center; gap: 5px;
  padding: 4px 10px; border-radius: 8px;
  background: rgba(0,0,0,.60); backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,.15);
  font-size: 10px; font-weight: 700; color: rgba(255,255,255,.8);
  letter-spacing: .06em; text-transform: uppercase;
  pointer-events: none;
}
.map-badge-sat::before {
  content: '';
  width: 6px; height: 6px; border-radius: 50%;
  background: #5eead4;
  box-shadow: 0 0 6px #5eead4;
  animation: satPulse 2s ease-in-out infinite;
}
@keyframes satPulse {
  0%,100% { opacity:1; }
  50%      { opacity:.3; }
}

/* Badge précision en overlay */
.map-badge-acc {
  position: absolute; bottom: 10px; right: 10px; z-index: 500;
  padding: 4px 10px; border-radius: 8px;
  background: rgba(0,0,0,.60); backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,.15);
  font-size: 10px; font-weight: 700;
  font-family: 'DM Mono', monospace;
  pointer-events: none;
}

/* Masquer contrôles Leaflet inutiles sur mobile */
.leaflet-control-attribution { display: none !important; }
.leaflet-control-zoom        { display: none !important; }

/* ── Marqueur GPS custom ── */
.gps-marker-outer {
  width: 34px; height: 34px;
  border-radius: 50%;
  background: rgba(94,234,212,.18);
  border: 2px solid rgba(94,234,212,.65);
  display: flex; align-items: center; justify-content: center;
  animation: markerPulse 2.4s ease-in-out infinite;
}
.gps-marker-inner {
  width: 12px; height: 12px;
  border-radius: 50%;
  background: #5eead4;
  border: 2.5px solid white;
  box-shadow: 0 0 10px rgba(94,234,212,.9);
}
@keyframes markerPulse {
  0%,100% { box-shadow: 0 0 0 0 rgba(94,234,212,.45); }
  50%      { box-shadow: 0 0 0 14px rgba(94,234,212,0); }
}

/* ── Adresse ── */
.geo-addr {
  display: flex; align-items: flex-start; gap: 10px;
  padding: 14px 16px 12px;
  border-bottom: 1px solid rgba(255,255,255,.05);
  background: rgba(13,148,136,.04);
}
.geo-addr-ico  { font-size: 18px; flex-shrink:0; margin-top:1px; }
.geo-addr-lbl  { font-size: 10px; color:rgba(255,255,255,.35); text-transform:uppercase; letter-spacing:.07em; margin-bottom:3px; }
.geo-addr-val  { font-size: 13px; font-weight:600; color:rgba(255,255,255,.9); line-height:1.45; text-align:left; }

/* ── Coords grid ── */
.geo-coords {
  display: grid; grid-template-columns: repeat(3,1fr);
  border-bottom: 1px solid rgba(255,255,255,.05);
  background: rgba(13,148,136,.03);
}
.geo-coord { padding: 11px 14px; border-right: 1px solid rgba(255,255,255,.05); }
.geo-coord:last-child { border-right: none; }
.geo-coord-lbl { font-size: 10px; color:rgba(255,255,255,.35); text-transform:uppercase; letter-spacing:.06em; margin-bottom:4px; }
.geo-coord-val { font-size: 12px; font-weight:700; font-family:'DM Mono',monospace; line-height:1; }

/* ── Footer ── */
.geo-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 9px 14px;
  background: rgba(0,0,0,.18);
  font-size: 11px; color: rgba(255,255,255,.3);
}
.geo-pill {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 3px 9px; border-radius: 99px;
  font-size: 10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em;
}
.geo-pill.ok  { background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.25); color:#86efac; }
.geo-pill.low { background:rgba(245,158,11,.12); border:1px solid rgba(245,158,11,.25); color:var(--amber2); }

/* ════════════════════════════════
   BLOC GEO REFUSÉ
════════════════════════════════ */
.geo-denied {
  display: flex; align-items: center; gap: 12px;
  padding: 16px 18px; border-radius: 16px; margin-bottom: 18px;
  background: rgba(239,68,68,.06); border: 1px solid rgba(239,68,68,.18);
  color: #fca5a5;
}
.geo-denied-ico   { font-size: 26px; flex-shrink:0; }
.geo-denied-title { font-size: 14px; font-weight:700; margin-bottom:3px; }
.geo-denied-sub   { font-size: 12px; opacity:.7; line-height:1.5; text-align:left; }

/* ── Boutons ── */
.btn-logout {
  display:block; width:100%; padding:13px;
  background: rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09);
  border-radius:14px; color:rgba(255,255,255,.55);
  font-size:14px; font-weight:600; font-family:'DM Sans',sans-serif;
  text-align:center; text-decoration:none; cursor:pointer; transition:all .2s;
}
.btn-logout:hover { background:rgba(255,255,255,.1); color:rgba(255,255,255,.9); }
.countdown { font-size:12px; color:rgba(255,255,255,.2); text-align:center; margin-top:10px; }
</style>
@endpush

@section('content')
@php
  /*
   * ── Fuseau horaire ────────────────────────────────────────────────────
   * Avant : Africa/Casablanca était codé en dur ici, ce qui désynchronisait
   * l'affichage (date, "Localisé à ...") du reste du pointage dès qu'un
   * tenant utilisait un autre fuseau (Cairo, etc.).
   * Maintenant : résolution inline directe (pas de dépendance à un helper
   * global externe, pour éviter les soucis de chargement de fichier) —
   * on lit le tenant_id de l'employé, puis son timezone en base.
   */
  $tenantId = $employee?->user?->tenant_id;
  $tz       = $tenantId
      ? (\App\Models\Tenant::where('id', $tenantId)->value('timezone') ?: 'Africa/Casablanca')
      : 'Africa/Casablanca';
  $nowCasa  = \Carbon\Carbon::now($tz)->locale('fr');

  $jours  = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
  $mois   = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
  $dateFr = $jours[$nowCasa->dayOfWeek].' '.$nowCasa->day.' '.$mois[$nowCasa->month].' '.$nowCasa->year;

  $typeIcon  = match($type) {
    'entree'                => '📥',
    'sortie'                => '📤',
    'pause','pause_start'   => '⏸',
    'retour_pause'          => '↩️',
    default                 => '✅',
  };
  $typeLabel = match($type) {
    'entree'                => '✓ Entrée enregistrée',
    'sortie'                => '✓ Sortie enregistrée',
    'pause','pause_start'   => '⏸ Pause enregistrée',
    'retour_pause'          => '↩ Retour de pause',
    default                 => '✓ Pointage enregistré',
  };
  $timeLabel = match($type) {
    'entree'                => "Heure d'arrivée",
    'sortie'                => 'Heure de départ',
    'pause','pause_start'   => 'Début de pause',
    'retour_pause'          => 'Retour de pause',
    default                 => 'Heure',
  };

  $pal = [
    'entree'       => ['rgba(13,148,136,.35)',  'linear-gradient(135deg,#0d9488,#0f766e)', 'rgba(13,148,136,.15)', 'rgba(13,148,136,.3)',  'var(--teal2)'],
    'sortie'       => ['rgba(245,158,11,.3)',   'linear-gradient(135deg,#d97706,#b45309)', 'rgba(245,158,11,.15)', 'rgba(245,158,11,.3)',  'var(--amber2)'],
    'pause'        => ['rgba(99,102,241,.35)',  'linear-gradient(135deg,#6366f1,#4f46e5)', 'rgba(99,102,241,.15)', 'rgba(99,102,241,.3)',  '#818cf8'],
    'pause_start'  => ['rgba(99,102,241,.35)',  'linear-gradient(135deg,#6366f1,#4f46e5)', 'rgba(99,102,241,.15)', 'rgba(99,102,241,.3)',  '#818cf8'],
    'retour_pause' => ['rgba(34,197,94,.35)',   'linear-gradient(135deg,#16a34a,#15803d)', 'rgba(34,197,94,.15)',  'rgba(34,197,94,.3)',   '#4ade80'],
  ];
  [$ripple,$iconBg,$badgeBg,$badgeBorder,$textColor] = $pal[$type] ?? $pal['entree'];

  $displayTime = match($type) {
    'entree'                => $todayShift['first_entree'] ?? $nowCasa->format('H:i'),
    'sortie'                => $todayShift['last_sortie']  ?? $nowCasa->format('H:i'),
    'pause','pause_start'   => $todayShift['pause_start']  ?? $nowCasa->format('H:i'),
    'retour_pause'          => $todayShift['pause_end']    ?? $nowCasa->format('H:i'),
    default                 => $nowCasa->format('H:i'),
  };

  /* ── Géolocalisation ── */
  $geo     = $geoData ?? [];
  $geoOk   = !empty($geo)
           && !($geo['denied'] ?? true)
           && isset($geo['latitude'], $geo['longitude'])
           && $geo['latitude'] !== null
           && $geo['longitude'] !== null;

  $geoLat     = $geoOk ? number_format((float)$geo['latitude'],  6, '.', '') : null;
  $geoLngRaw  = $geoOk ? (float)$geo['longitude'] : null;
  $geoLng     = $geoOk ? number_format(abs($geoLngRaw), 6, '.', '') : null;
  $geoLngSign = ($geoOk && $geoLngRaw < 0) ? '−' : '';
  $geoHem     = ($geoOk && $geoLngRaw < 0) ? 'W' : 'E';
  $geoAcc     = (isset($geo['accuracy']) && $geo['accuracy'] !== null) ? (int)$geo['accuracy'] : null;
  $geoAddr    = !empty($geo['address']) ? trim($geo['address']) : null;
  $geoHigh    = ($geoAcc !== null && $geoAcc <= 30);
  $geoTs      = $nowCasa->format('H:i:s');

  $showPause = !empty($todayShift['pause_start']) || !empty($todayShift['pause_end']);
@endphp

<div class="result-page">
<div class="result-inner">

  {{-- Icône animée --}}
  <div class="icon-area">
    <div class="ripple" style="background:{{ $ripple }};"></div>
    <div class="ripple" style="background:{{ $ripple }};"></div>
    <div class="icon-box" style="background:{{ $iconBg }};box-shadow:0 16px 48px {{ $ripple }};">
      {{ $typeIcon }}
    </div>
  </div>

  <div class="result-card">

    <div class="type-badge"
         style="background:{{ $badgeBg }};border:1px solid {{ $badgeBorder }};color:{{ $textColor }};">
      {{ $typeLabel }}
    </div>

    @if($employee)
      <div class="emp-name">{{ $employee->first_name }} {{ $employee->last_name }}</div>
      <div class="emp-dept">{{ $employee->department ?? '—' }} · {{ $employee->matricule ?? 'N/A' }}</div>
    @else
      <div class="emp-name">—</div><div class="emp-dept">—</div>
    @endif

    <div class="time-lbl">{{ $timeLabel }}</div>
    <div class="time-big" style="color:{{ $textColor }};">{{ $displayTime }}</div>
    <div class="time-date">{{ $dateFr }}</div>

    <div class="div"></div>

    {{-- Grille shift --}}
    <div class="sg">
      <div class="sc">
        <div class="sc-lbl">📥 Entrée</div>
        <div class="sc-val c-teal">{{ $todayShift['first_entree'] ?? '—' }}</div>
      </div>
      <div class="sc">
        <div class="sc-lbl">📤 Sortie</div>
        <div class="sc-val c-amber">{{ $todayShift['last_sortie'] ?? '—' }}</div>
      </div>
      <div class="sc">
        <div class="sc-lbl">⏱ Total</div>
        <div class="sc-val" style="color:var(--green);">{{ $todayShift['total_human'] ?? '0h 0m' }}</div>
      </div>
    </div>

    @if($showPause)
    <div class="sg">
      <div class="sc">
        <div class="sc-lbl">⏸ Début pause</div>
        <div class="sc-val" style="color:#818cf8;">{{ $todayShift['pause_start'] ?? '—' }}</div>
      </div>
      <div class="sc">
        <div class="sc-lbl">↩ Retour</div>
        <div class="sc-val c-green">{{ $todayShift['pause_end'] ?? '—' }}</div>
      </div>
      <div class="sc">
        <div class="sc-lbl">⏱ Pause</div>
        <div class="sc-val c-amber">{{ $todayShift['total_pause_human'] ?? '0m' }}</div>
      </div>
    </div>
    @endif

    <div class="div"></div>

    {{-- ══ SECTION GPS ══ --}}
    <div class="geo-title">Localisation GPS</div>

    @if($geoOk)
    <div class="geo-block">

      {{-- ── Carte satellite Leaflet ── --}}
      <div class="geo-map-wrap">
        <div id="satelliteMap"></div>
        <div class="map-badge-sat">Vue satellite</div>
        @if($geoAcc)
          <div class="map-badge-acc" style="color:{{ $geoHigh ? '#86efac' : '#fde68a' }};">
            ± {{ $geoAcc }} m
          </div>
        @endif
      </div>

      {{-- Adresse --}}
      <div class="geo-addr">
        <div class="geo-addr-ico"></div>
        <div style="flex:1; min-width:0;">
          <div class="geo-addr-lbl">Adresse détectée</div>
          <div class="geo-addr-val">
            @if($geoAddr)
              {{ $geoAddr }}
            @else
              {{ $geoLat }}° N, {{ $geoLngSign }}{{ $geoLng }}° {{ $geoHem }}
              @if($geoAcc)
                <span style="font-size:11px;opacity:.5;"> · ±{{ $geoAcc }} m</span>
              @endif
            @endif
          </div>
        </div>
      </div>



      {{-- Footer --}}
      <div class="geo-footer">
        <span>Localisé à {{ $geoTs }}</span>
      </div>

    </div>{{-- /geo-block --}}

    @else
    <div class="geo-denied">
      <div class="geo-denied-ico">📵</div>
      <div>
        <div class="geo-denied-title">Géolocalisation non disponible</div>
        <div class="geo-denied-sub">
          @if(!empty($geo['denied']))
            La géolocalisation a été refusée ou n'est pas disponible sur cet appareil.
          @else
            La position GPS n'a pas pu être déterminée.
          @endif
          Le pointage a été enregistré sans coordonnées GPS.
        </div>
      </div>
    </div>
    @endif

    <form method="POST" action="{{ route('badge.logout') }}">
      @csrf
      <button type="submit" class="btn-logout">Se déconnecter</button>
    </form>
    <div class="countdown">
      Retour automatique dans <strong id="cdown">30</strong>s
    </div>

  </div>{{-- /result-card --}}
</div>{{-- /result-inner --}}
</div>{{-- /result-page --}}
@endsection

@push('scripts')
{{-- Leaflet JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

@if($geoOk)
<script>
(function () {
  var lat = {{ (float)$geo['latitude'] }};
  var lng = {{ (float)$geo['longitude'] }};
  var acc = {{ $geoAcc ?? 60 }};

  // Zoom adapté à la précision GPS
  var zoom = 18;
  if (acc > 500)     zoom = 13;
  else if (acc > 200) zoom = 14;
  else if (acc > 80)  zoom = 15;
  else if (acc > 30)  zoom = 16;
  else if (acc > 15)  zoom = 17;
  else                zoom = 18;

  // Init Leaflet — carte statique (non interactive)
  var map = L.map('satelliteMap', {
    center:             [lat, lng],
    zoom:               zoom,
    zoomControl:        false,
    attributionControl: false,
    dragging:           false,
    scrollWheelZoom:    false,
    doubleClickZoom:    false,
    touchZoom:          false,
    boxZoom:            false,
    keyboard:           false,
  });

  // ── Tuiles satellite Esri (gratuit, sans clé API) ─────────────────────
  L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
    { maxZoom: 19 }
  ).addTo(map);

  // ── Noms de lieux / rues par-dessus le satellite ──────────────────────
  L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',
    { maxZoom: 19, opacity: 0.75 }
  ).addTo(map);

  // ── Cercle de précision ───────────────────────────────────────────────
  if (acc > 0) {
    L.circle([lat, lng], {
      radius:      acc,
      color:       '#5eead4',
      fillColor:   '#5eead4',
      fillOpacity: 0.07,
      weight:      1.5,
      dashArray:   '6 5',
    }).addTo(map);
  }

  // ── Marqueur GPS animé ────────────────────────────────────────────────
  var markerHtml =
    '<div class="gps-marker-outer">' +
      '<div class="gps-marker-inner"></div>' +
    '</div>';

  var markerIcon = L.divIcon({
    className:  '',
    html:       markerHtml,
    iconSize:   [34, 34],
    iconAnchor: [17, 17],
  });

  L.marker([lat, lng], { icon: markerIcon }).addTo(map);

  // Forcer le recalcul de la taille après animation CSS
  setTimeout(function () { map.invalidateSize(); }, 350);

})();
</script>
@endif

<script>
(function(){
  let s = 30;
  const el = document.getElementById('cdown');
  const t  = setInterval(() => {
    s--;
    if (el) el.textContent = s;
    if (s <= 0) { clearInterval(t); window.location.href = '{{ route('badge.pointage') }}'; }
  }, 1000);
  document.addEventListener('click', () => {
    clearInterval(t);
    const a = document.querySelector('.countdown');
    if (a) a.style.display = 'none';
  }, { once: true });
})();
</script>
@endpush
