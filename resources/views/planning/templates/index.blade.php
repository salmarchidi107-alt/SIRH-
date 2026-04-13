@extends('layouts.app')

@section('title', 'Semaines Types')
@section('page-title', 'Modèles de Semaines')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1> Semaines Types</h1>
        <p>Créer et gérer des modèles de planification réutilisables</p>
    </div>
    <div class="page-header-right">
        <a href="{{ route('planning.templates.create') }}" class="btn btn-primary">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nouvelle semaine type
        </a>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nom du modèle</th>
                    <th>Description rapide</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                <tr>
                    <td>
                        <div style="font-weight:600">{{ $template->name }}</div>
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap">
                            @if($template->monday_shift_type)
                                <span class="shift-pill shift-{{ $template->monday_shift_type }}">L</span>
                            @endif
                            @if($template->tuesday_shift_type)
                                <span class="shift-pill shift-{{ $template->tuesday_shift_type }}">Ma</span>
                            @endif
                            @if($template->wednesday_shift_type)
                                <span class="shift-pill shift-{{ $template->wednesday_shift_type }}">Me</span>
                            @endif
                            @if($template->thursday_shift_type)
                                <span class="shift-pill shift-{{ $template->thursday_shift_type }}">J</span>
                            @endif
                            @if($template->friday_shift_type)
                                <span class="shift-pill shift-{{ $template->friday_shift_type }}">V</span>
                            @endif
                            @if($template->saturday_shift_type)
                                <span class="shift-pill shift-{{ $template->saturday_shift_type }}">S</span>
                            @endif
                            @if($template->sunday_shift_type)
                                <span class="shift-pill shift-{{ $template->sunday_shift_type }}">D</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;gap:8px">
                            <a href="{{ route('planning.templates.apply') }}?template_id={{ $template->id }}" class="btn btn-sm btn-outline">
                                Appliquer
                            </a>
                            <form method="POST" action="{{ route('planning.templates.destroy', $template->id) }}" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline" style="color:var(--danger);border-color:var(--danger)" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce modèle?')">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center;padding:48px;color:var(--text-muted)">
                        <div style="font-size:2.5rem;margin-bottom:12px">  </div>
                        <div>Aucune semaine type créée</div>
                        <a href="{{ route('planning.templates.create') }}" class="btn btn-primary" style="margin-top:12px">Créer une semaine type</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:24px">
    <a href="{{ route('planning.weekly') }}" class="btn btn-outline">← Retour au planning</a>
</div>
@endsection
