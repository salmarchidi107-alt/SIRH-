@extends('layouts.superadmin')
@section('title','Rôles')
@section('page-title','Gestion des rôles')
@section('content')
<div style="background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;padding:20px;max-width:640px;">
    <div style="font-size:13px;font-weight:500;color:#1e293b;margin-bottom:16px;padding-bottom:10px;border-bottom:0.5px solid #f1f5f9;">Rôles disponibles</div>
    @foreach($roles as $role)
    <div style="display:flex;align-items:flex-start;gap:14px;padding:14px 0;border-bottom:0.5px solid #f8fafc;">
        <div style="width:36px;height:36px;border-radius:9px;background:{{ $role['name']==='superadmin'?'#1e1b4b':($role['name']==='admin'?'#eff6ff':'#f1f5f9') }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $role['name']==='superadmin'?'#a5b4fc':($role['name']==='admin'?'#2563eb':'#64748b') }}" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <div>
            <div style="font-size:13px;font-weight:500;color:#1e293b;">{{ $role['label'] }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:3px;">{{ $role['description'] }}</div>
            <div style="margin-top:5px;font-size:11px;font-family:monospace;color:#94a3b8;">{{ $role['name'] }}</div>
        </div>
    </div>
    @endforeach
</div>
@endsection
