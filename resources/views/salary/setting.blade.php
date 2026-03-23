@extends('layouts.app')

@section('title', 'Paramètres de paie')

@section('content')
<div class="page-header">
    <h1>Paramètres de paie</h1>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('payroll.settings.update') }}">
    @csrf
    
    <div class="card">
        <div class="card-header">
            <h3>Configuration des taux et montants</h3>
        </div>
        <div class="card-body">
            @foreach($settings as $category => $items)
                <h4 class="mt-4 mb-3 text-primary">{{ ucfirst($category) }}</h4>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Clé</th>
                                <th>Valeur actuelle</th>
                                <th>Nouvelle valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $setting)
                                <tr>
                                    <td>{{ $setting->key }}</td>
                                    <td class="fw-bold">{{ number_format($setting->value, 2) }} MAD</td>
                                    <td>
                                        <input type="hidden" name="settings[{{ $loop->parent->index }}][key]" value="{{ $setting->key }}">
                                        <input type="number" name="settings[{{ $loop->parent->index }}][value]" value="{{ $setting->value }}" 
                                               class="form-control" step="0.01" min="0" required>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
    
    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save"></i> Mettre à jour les paramètres
        </button>
    </div>
</form>
@endsection
