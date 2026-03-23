@extends('layouts.app')

@section('title', $employee->full_name . ' — Profil')
@section('page-title', $employee->full_name)

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $employee->full_name }}</h1>
        <p>{{ $employee->position }} — {{ $employee->department }}</p>
    </div>
</div>

<div style="max-width: 600px; margin: 0 auto;">
    <div class="card">
        <div class="profile-photo-large">
            @if($employee->photo)
                <img src="{{ $employee->photo_url }}" alt="{{ $employee->full_name }}">
            @else
                <div style="font-size:4rem;font-weight:700;color:white;background:var(--primary);width:100%;height:100%;border-radius:50%;display:flex;align-items:center;justify-content:center">
                    {{ strtoupper(substr($employee->first_name,0,1).substr($employee->last_name,0,1)) }}
                </div>
            @endif
        </div>
        <div style="text-align:center;padding:32px 24px">
            <h1 style="font-size:2rem;font-weight:700;margin-bottom:8px">{{ $employee->full_name }}</h1>
            <div style="font-size:1.1rem;color:var(--primary);font-weight:500;margin-bottom:24px">{{ $employee->position }}</div>
            <div style="color:var(--text-muted);font-size:0.95rem;margin-bottom:16px">{{ $employee->department }}</div>

            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
                @if($employee->status == 'active')
                    <span class="badge badge-success">● Actif</span>
                @elseif($employee->status == 'leave')
                    <span class="badge badge-warning">◐ En congé</span>
                @else
                    <span class="badge badge-neutral">○ Inactif</span>
                @endif
                <span class="badge badge-info">{{ $employee->contract_type ?? 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

