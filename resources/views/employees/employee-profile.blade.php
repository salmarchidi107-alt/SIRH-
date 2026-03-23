@extends('layouts.app')

@section('title', $employee->full_name)
@section('page-title', $employee->full_name)

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $employee->full_name }}</h1>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:20px;max-width:800px;margin:0 auto">
    <div class="trombino-card" style="grid-column:1/-1;text-align:center;padding:64px">
        <div class="trombino-photo" style="width:160px;height:160px;margin:0 auto 24px;font-size:3.5rem">
            @if($employee->photo)
                <img src="{{ $employee->photo_url }}" alt="{{ $employee->full_name }}">
            @else
                {{ strtoupper(substr($employee->first_name,0,1).substr($employee->last_name,0,1)) }}
            @endif
        </div>
        <div class="trombino-name" style="font-size:1.5rem;margin-bottom:8px">{{ $employee->full_name }}</div>
        <div class="trombino-role" style="font-size:1.2rem;margin-bottom:12px">{{ $employee->position }}</div>
        <div class="trombino-dept" style="font-size:1rem">{{ $employee->department }}</div>
    </div>
</div>
@endsection

