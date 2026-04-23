@extends('layouts.badge')
@section('title', 'Pointage — ' . ($employee?->first_name ?? 'Accueil'))

@push('styles')
@vite(['resources/css/badge.css'])
<style>
.badge-wrap { flex:1; display:flex; flex-direction:column; min-height:100vh; }
.badge-header { display:flex; align-items:center; justify-content:space-between; padding:20px 28px; border-bottom:1px solid rgba(255,255,255,.08); background: rgba(255,255,255,.03);}
.header-clock, .header-date { text-align:center; color:white; }
.action-buttons { display:flex; gap:20px; flex-wrap:wrap; justify-content:center; margin-top:20px;}
.btn-action { width:220px; height:220px; border-radius:28px; display:flex; flex-direction:column; justify-content:center; align-items:center; gap:12px; border:none; cursor:pointer; color:white; position:relative;}
.btn-entree { background: linear-gradient(145deg, #0d9488, #0f766e); }
.btn-sortie  { background: linear-gradient(145deg, #d97706, #b45309); }
.btn-action:disabled { opacity:.3; cursor:not-allowed; pointer-events:none; }
.shift-summary { display:flex; gap:16px; justify-content:center; flex-wrap:wrap; margin-top:20px; }
.shift-item { background: rgba(255,255,255,.06); border-radius:16px; padding:16px 24px; text-align:center; min-width:120px; }
</style>
@endpush

@section('content')
<div class="badge-wrap">
  <div class="badge-header">
    <div>
      <div class="header-clock" id="liveClock">--:--</div>
      <div class="header-date" id="liveDate">--</div>
    </div>
    <div>
      @if($employee)
        <div>{{ $employee->first_name }} {{ $employee->last_name }}</div>
      @endif
    </div>
  </div>

  @if($employee)
  <div class="shift-summary">
    @if($todayShift['first_entree'])
      <div class="shift-item">Entrée: {{ \Carbon\Carbon::parse($todayShift['first_entree'])->format('H:i') }}</div>
    @endif
    @if($todayShift['last_sortie'])
      <div class="shift-item">Sortie: {{ \Carbon\Carbon::parse($todayShift['last_sortie'])->format('H:i') }}</div>
    @endif
    @if($todayShift['pause_display'])
      <div class="shift-item">Pause: {{ $todayShift['pause_display'] }}</div>
    @endif
    <div class="shift-item">Total: {{ $todayShift['total_human'] }}</div>
  </div>
  @endif

  <div class="action-buttons">
    <button class="btn-action btn-entree" {{ !$canEntree ? 'disabled' : '' }} onclick="openModal('entree')">📥 Entrée</button>
    <button class="btn-action btn-sortie" {{ !$canSortie ? 'disabled' : '' }} onclick="openModal('sortie')">📤 Sortie</button>
  </div>
</div>

<div id="choiceModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); align-items:center; justify-content:center; z-index:999;">
  <div style="background:#1e293b; padding:30px; border-radius:16px; text-align:center; min-width:300px;">
    <h3 id="modalTitle" style="color:white;margin-bottom:20px;"></h3>
    <button id="btn1" style="padding:12px 20px;margin:10px;border:none;border-radius:10px;"></button>
    <button id="btn2" style="padding:12px 20px;margin:10px;border:none;border-radius:10px;"></button>
    <br><button onclick="closeModal()">Annuler</button>
  </div>
</div>
@endsection

@push('scripts')
<script>
function updateClock() {
  const now = new Date();
  document.getElementById('liveClock').textContent = now.toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit',second:'2-digit', timeZone:'Africa/Casablanca'});
  document.getElementById('liveDate').textContent = now.toLocaleDateString('fr-FR', {weekday:'long', day:'numeric', month:'long', year:'numeric', timeZone:'Africa/Casablanca'});
}
updateClock(); setInterval(updateClock, 1000);

let currentType = null;
function openModal(type) {
  currentType = type;
  const modal = document.getElementById('choiceModal');
  const title = document.getElementById('modalTitle');
  const btn1 = document.getElementById('btn1');
  const btn2 = document.getElementById('btn2');
  modal.style.display = 'flex';

  if(type==='entree'){
    title.textContent='Type d\'entrée';
    btn1.textContent='🟢 Début shift'; btn1.style.background='#10b981'; btn1.onclick=()=>submitAction('debut');
    btn2.textContent='🔵 Retour pause'; btn2.style.background='#3b82f6'; btn2.onclick=()=>submitAction('pause');
  }
  if(type==='sortie'){
    title.textContent='Choisir une action';
    btn1.textContent='⏸ Je pars en pause'; btn1.style.background='#fbbf24'; btn1.onclick=()=>submitAction('pause');
    btn2.textContent='🏁 J\'ai fini mon shift'; btn2.style.background='#6366f1'; btn2.onclick=()=>submitAction('fin');
  }
}

function closeModal(){document.getElementById('choiceModal').style.display='none';}

async function submitAction(action){
  const res=await fetch('/badge/action',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Content-Type':'application/json'},body:JSON.stringify({type:currentType,action:action})});
  const data=await res.json();
  window.location.href=data.redirect;
}
</script>
@endpush
