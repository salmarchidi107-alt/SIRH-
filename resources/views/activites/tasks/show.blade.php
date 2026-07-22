@extends('layouts.app')

@section('title', $task->title)
@section('page-title', $task->title)

@section('content')
<style>
  .sa-section-head{display:flex; align-items:center; justify-content:space-between; margin:26px 0 12px;}
  .sa-section-head h3{font-size:15px; font-weight:700; margin:0; color:#0f172a; font-family:'Plus Jakarta Sans', inherit;}

  .sa-badge{padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap;}
  .sa-b-a_faire{background:#eef0f3; color:#51586b;}
  .sa-b-en_cours{background:#e6f3f5; color:var(--primary, #0f6b7c);}
  .sa-b-en_pause{background:#fff3e0; color:var(--warning, #f59e0b);}
  .sa-b-terminee{background:#e7f9ee; color:var(--success, #22c55e);}
  .sa-b-annulee{background:#fdeaea; color:var(--danger, #ef4444);}

  .sa-timer{background:#fff; border:1px solid var(--border, #e2e8f0); border-radius:16px; padding:20px 22px; display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; flex-wrap:wrap; gap:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03);}
  .sa-timer .sa-tt{font-size:15px; font-weight:700; margin-bottom:4px; color:#0f172a; font-family:'Plus Jakarta Sans', inherit;}
  .sa-timer .sa-tm{font-size:12px; color:var(--text-muted, #64748b);}
  .sa-timer .sa-clock{font-size:26px; font-weight:800; color:var(--primary, #0f6b7c); font-variant-numeric:tabular-nums;}
  .sa-timer .sa-controls{display:flex; gap:8px; margin-top:8px;}
  .sa-mini-btn{padding:7px 16px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; border:none; transition:opacity .15s ease;}
  .sa-mini-btn:hover{opacity:.9;}
  .sa-mb-pause{background:var(--surface-2, #f1f5f9); color:#0f172a;}
  .sa-mb-finish{background:linear-gradient(135deg, #0f6b7c 0%, #1a8fa5 100%); color:#fff;}

  .sa-form-card{background:#fff; border:1px solid var(--border, #e2e8f0); border-radius:16px; padding:18px 20px; box-shadow:0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03);}
  .sa-form-row{display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap;}
  .sa-field{flex:1; min-width:140px; display:flex; flex-direction:column; gap:5px;}
  .sa-field.sa-small{flex:0 0 140px;}
  .sa-field label{font-size:11.5px; color:var(--text-muted, #64748b); font-weight:600;}
  .sa-field input, .sa-field select, .sa-field textarea{border:1px solid var(--border, #e2e8f0); border-radius:8px; padding:9px 10px; font-size:13px; font-family:inherit; background:#fff; transition:border-color .15s ease;}
  .sa-field input:focus, .sa-field select:focus, .sa-field textarea:focus{outline:none; border-color:var(--primary, #0f6b7c);}
  .sa-field-error{color:var(--danger, #ef4444); font-size:11px; margin-top:2px;}
  .sa-form-foot{display:flex; justify-content:flex-end; gap:8px; margin-top:4px;}

  /* Fallback si .btn-danger n'existe pas déjà dans app.css */
  .btn-danger{background:#fff; color:var(--danger, #ef4444); border:1px solid #f6c9c9; padding:8px 14px; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;}

  .sa-empty-hint{font-size:12px; color:var(--text-muted, #64748b); text-align:center; padding:18px 0;}

  .sa-activity-item{border-bottom:1px solid var(--border, #e2e8f0); padding:10px 0; font-size:12.5px; display:flex; justify-content:space-between; gap:10px;}
  .sa-activity-item:last-child{border-bottom:none;}
  .sa-activity-item .sa-a-meta{color:var(--text-muted, #64748b); font-size:11px;}

  @media (max-width:700px){ .sa-form-row{flex-direction:column;} }
</style>

  <p style="font-size:12.5px; margin-bottom:8px;"><a href="{{ route('activites.projects.show', $project) }}" style="color:var(--text-muted, #64748b);">← {{ $project->name }}</a></p>
  <p style="font-size:12.5px; color:var(--text-muted, #64748b); margin-bottom:20px;">
    Priorité {{ $task->priority }}
    @if ($task->due_date) · Échéance {{ $task->due_date->format('d/m/Y') }} @endif
    · <span class="sa-badge sa-b-{{ $task->status }}">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span>
  </p>

  @if ($task->description)
    <p style="font-size:12.5px; color:var(--text-muted, #64748b); margin-bottom:20px;">{{ $task->description }}</p>
  @endif

  {{-- ------------------ Chronomètre ------------------ --}}
  <div class="sa-timer">
    <div>
      <div class="sa-tt">
        @if ($task->is_timer_running) En cours @else Chronomètre @endif
      </div>
      <div class="sa-tm">
        @if ($task->is_timer_running)
          Démarré à {{ $task->timer_started_at->format('H:i') }}
        @else
          Temps total enregistré : {{ \App\Support\Duration::toHuman($task->logged_minutes) }}
        @endif
      </div>
    </div>
    <div style="text-align:right;">
      @if ($task->is_timer_running)
        <div class="sa-clock" id="live-clock" data-started="{{ $task->timer_started_at->timestamp }}">00:00:00</div>
        <div class="sa-controls">
          <form method="POST" action="{{ route('activites.tasks.timer.pause', [$project, $task]) }}">
            @csrf
            <button class="sa-mini-btn sa-mb-pause" type="submit">⏸ Pause</button>
          </form>
          <form method="POST" action="{{ route('activites.tasks.timer.finish', [$project, $task]) }}">
            @csrf
            <button class="sa-mini-btn sa-mb-finish" type="submit">✓ Terminer</button>
          </form>
        </div>
      @else
        <div class="sa-controls">
          <form method="POST" action="{{ route('activites.tasks.timer.start', [$project, $task]) }}">
            @csrf
            <button class="sa-mini-btn sa-mb-finish" type="submit">▶ Démarrer</button>
          </form>
          @if (!in_array($task->status, ['terminee','annulee']))
            <form method="POST" action="{{ route('activites.tasks.status', [$project, $task]) }}">
              @csrf @method('PATCH')
              <input type="hidden" name="status" value="terminee">
              <button class="sa-mini-btn sa-mb-pause" type="submit">✓ Marquer terminée</button>
            </form>
          @endif
        </div>
      @endif
    </div>
  </div>

  {{-- ------------------ Ajouter une activité manuelle ------------------ --}}
  <div class="sa-section-head"><h3>Ajouter une activité</h3></div>
  <div class="sa-form-card">
    <form method="POST" action="{{ route('activites.tasks.activities.store', [$project, $task]) }}" enctype="multipart/form-data">
      @csrf
      <div class="sa-form-row">
        <div class="sa-field sa-small">
          <label>Date</label>
          <input type="date" name="activity_date" value="{{ old('activity_date', now()->toDateString()) }}" required>
          @error('activity_date') <div class="sa-field-error">{{ $message }}</div> @enderror
        </div>
        <div class="sa-field sa-small">
          <label>Durée</label>
          <input type="text" name="duration" placeholder="1h30" value="{{ old('duration') }}" required>
          @error('duration') <div class="sa-field-error">{{ $message }}</div> @enderror
        </div>
        <div class="sa-field">
          <label>Commentaire</label>
          <input type="text" name="comment" placeholder="Ce que j'ai fait..." value="{{ old('comment') }}">
        </div>
      </div>
      <div class="sa-form-foot">
        <label class="btn" style="cursor:pointer;">
          📎 Joindre un fichier
          <input type="file" name="attachment" style="display:none;">
        </label>
        <button class="btn btn-primary" type="submit">Enregistrer</button>
      </div>
    </form>
  </div>

  {{-- ------------------ Historique des activités ------------------ --}}
  <div class="sa-section-head">
    <h3>Historique</h3>
    <form method="POST" action="{{ route('activites.tasks.destroy', [$project, $task]) }}" onsubmit="return confirm('Supprimer définitivement cette tâche et son historique ?');">
      @csrf @method('DELETE')
      <button class="btn btn-danger" type="submit">🗑 Supprimer la tâche</button>
    </form>
  </div>
  <div class="sa-form-card">
    @forelse ($task->activities as $activity)
      <div class="sa-activity-item">
        <div>
          {{ $activity->comment ?: ($activity->type === 'chrono' ? 'Session chronométrée' : 'Saisie manuelle') }}
          <div class="sa-a-meta">{{ $activity->activity_date->format('d/m/Y') }} · {{ $activity->type === 'chrono' ? 'Chrono' : 'Manuel' }}</div>
        </div>
        <b>{{ \App\Support\Duration::toHuman($activity->duration_minutes) }}</b>
      </div>
    @empty
      <div class="sa-empty-hint">Aucune activité enregistrée sur cette tâche pour l'instant.</div>
    @endforelse
  </div>
@endsection

@push('scripts')
<script>
  const saClockEl = document.getElementById('live-clock');
  if (saClockEl) {
    const startedAt = parseInt(saClockEl.dataset.started, 10) * 1000;
    const tick = () => {
      const diff = Math.max(0, Date.now() - startedAt);
      const totalSec = Math.floor(diff / 1000);
      const h = String(Math.floor(totalSec / 3600)).padStart(2, '0');
      const m = String(Math.floor((totalSec % 3600) / 60)).padStart(2, '0');
      const s = String(totalSec % 60).padStart(2, '0');
      saClockEl.textContent = `${h}:${m}:${s}`;
    };
    tick();
    setInterval(tick, 1000);
  }
</script>
@endpush

