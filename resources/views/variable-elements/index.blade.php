@extends('layouts.app')

@section('title', 'Éléments Variables')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Accueil</a></li>
                        <li class="breadcrumb-item active">Éléments Variables</li>
                    </ol>
                </div>
                <h4 class="page-title">Éléments Variables - {{ $month }}/{{ $year }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row mb-4">
                        <div class="col-md-3">
                            <label>Mois:</label>
                            <select name="month" class="form-control">
                                @for($m=1; $m<=12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Année:</label>
                            <input type="number" name="year" value="{{ $year }}" class="form-control" min="2020" max="2030">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Filtrer</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Employé</th>
                                    <th>Rubrique</th>
                                    <th>Label</th>
                                    <th>Montant</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($variableElements as $element)
                                    <tr>
                                        <td>{{ $element->employee->first_name . ' ' . $element->employee->last_name ?? 'N/A' }}</td>
                                        <td>{{ $element->rubrique ?? $element->category }}</td>
                                        <td>{{ $element->label }}</td>
                                        <td>{{ number_format($element->amount, 2) }} {{ $element->unit }}</td>
                                        <td>
                                            <span class="badge bg-{{ $element->type_color }}">{{ $element->type_label }}</span>
                                        </td>
                                        <td>{{ $element->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Aucun élément variable pour {{ $month }}/{{ $year }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $variableElements->appends(request()->query())->links() }}
                    </div>

                    <h5 class="mt-4">Employés Actifs ({{ $employees->count() }})</h5>
                    <p>Utilisez cette liste pour ajouter de nouveaux éléments variables.</p>
                    {{-- Add employee selector form here if needed --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
