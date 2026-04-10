@extends('layouts.app')

@section('title', 'Trombinoscope')
@section('page-title', 'Trombinoscope')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Trombinoscope</h1>
        <p>Galerie photos du personnel</p>
    </div>
</div>

<div class="filters-bar">
    <form method="GET" action="{{ route('trombinoscope') }}" class="filters-bar" style="margin:0;flex-wrap:wrap;gap:12px">
        <div class="search-bar">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
        </div>
        <select name="department" class="filter-select" onchange="this.form.submit()">
            <option value="">Tous les services</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
    </form>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:20px">
    @forelse($employees as $employee)
<a href="{{ route('employees.show', $employee) }}" class="trombino-card">
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
</a>   {{-- ← ICI la fermeture manquait --}}

@empty
    <div style="grid-column:1/-1;text-align:center;padding:48px;color:var(--text-muted)">
        <div style="font-size:2.5rem;margin-bottom:12px">👥</div>
        <div>Aucun employé trouvé</div>
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

