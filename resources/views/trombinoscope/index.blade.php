@extends('layouts.app')

@section('title', 'Trombinoscope')
@section('page-title', 'Trombinoscope')

@section('content')
<div class="filters-container" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;margin-bottom:24px;display:flex;flex-wrap:wrap;gap:16px;align-items:end;">
    <form method="GET" action="{{ route('trombinoscope') }}" style="display:flex;flex-wrap:wrap;gap:16px;align-items:end;flex:1;">
        <div>
            <label style="display:block;font-weight:500;margin-bottom:4px;color:var(--text);">Recherche par nom</label>
            <input type="text" name="search" value="{{ request('search', '') }}" placeholder="Nom, prénom, poste..." 
                   style="width:280px;padding:10px;border:1px solid var(--border);border-radius:var(--radius);font-size:14px;">
        </div>
        <div>
            <label style="display:block;font-weight:500;margin-bottom:4px;color:var(--text);">Département</label>
            <select name="department" style="width:220px;padding:10px;border:1px solid var(--border);border-radius:var(--radius);font-size:14px;">
                <option value="">Tous les départements</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                 @endforeach
            </select>
        </div>
        <button type="submit" style="background:var(--primary);color:white;border:none;padding:12px 24px;border-radius:var(--radius);font-weight:600;cursor:pointer;font-size:14px;">
            Filtrer
        </button>
    </form>
    @if(request()->hasAny(['search', 'department']))
        <a href="{{ route('trombinoscope') }}" style="padding:12px 24px;border:1px solid var(--border);border-radius:var(--radius);text-decoration:none;color:var(--text);font-weight:500;font-size:14px;">
            Réinitialiser
        </a>
    @endif
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:20px;">


@forelse($employees as $employee)

@php
    $pointage = $pointages[$employee->id] ?? null;
    $statusColor = 'gray';
    $statusText = 'Non pointé';

    if ($pointage) {
        if ($pointage->statut === 'present') {
            $statusColor = 'green';
            $statusText = 'Présent';
        } elseif ($pointage->statut === 'absent') {
            $statusColor = 'red';
            $statusText = 'Absent';
        }
    }
@endphp

@if(auth()->user()->role === 'employee' && auth()->user()->employee_id != $employee->id)
    <div class="trombino-card" style="border:2px solid {{ $statusColor }}" onclick="alert('Accès restreint'); return false;">
@else
    <a href="{{ route('employees.show', $employee) }}" class="trombino-card" style="border:2px solid {{ $statusColor }}">
@endif

    <div class="trombino-photo">
        @if($employee->photo)
            <img src="{{ $employee->photo_url }}" alt="{{ $employee->full_name }}">
        @else
            {{ strtoupper(substr($employee->first_name,0,1).substr($employee->last_name,0,1)) }}
        @endif
    </div>

    <div class="trombino-name">{{ $employee->full_name }}</div>
    <div class="trombino-role">{{ $employee->position }}</div>
    <div class="trombino-dept">{{ $employee->department }}</div>

    {{--  STATUT --}}
    <div style="margin-top:8px;font-weight:bold;color:{{ $statusColor }}">
        ● {{ $statusText }}
    </div>

    {{-- CONTACT --}}
    <div class="trombino-contact" style="font-size:0.75rem;color:var(--text-muted);margin-top:12px;border-top:1px solid var(--border);padding-top:12px;">
        
        <div style="display:flex;align-items:center;gap:4px;margin-bottom:4px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.686l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            {{ $employee->phone ?: $employee->numero ?: 'N/A' }}
        </div>

        <div style="display:flex;align-items:center;gap:4px">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
            </svg>
            {{ $employee->email ?: 'N/A' }}
        </div>

    </div>

@if(auth()->user()->role === 'employee' && auth()->user()->employee_id != $employee->id)
    </div>
@else
    </a>
@endif

@empty
<div style="grid-column:1/-1;text-align:center;padding:48px">
    Aucun employé trouvé
</div>
@endforelse

</div>

<style>
.trombino-card {
    display:block;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:24px;
    text-align:center;
    transition:all 0.2s;
    text-decoration:none;
    color:inherit;
    cursor:pointer;
}
.trombino-card:hover {
    transform:translateY(-4px);
    box-shadow:var(--shadow-lg);
    border-color:var(--primary);
}
.trombino-photo {
    width:100px;
    height:100px;
    border-radius:50%;
    background:var(--primary);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:2rem;
    font-weight:700;
    margin:0 auto 16px;
    overflow:hidden;
}
.trombino-photo img {
    width:100%;
    height:100%;
    object-fit:cover;
}
.trombino-name {
    font-weight:700;
    font-size:1rem;
    margin-bottom:4px;
}
.trombino-role {
    font-size:0.875rem;
    color:var(--text-muted);
    margin-bottom:4px;
}
.trombino-dept {
    font-size:0.75rem;
    color:var(--primary);
    font-weight:500;
}
</style>
@endsection
