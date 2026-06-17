@extends('layouts.app')

@section('title', 'Décharge ' . $discharge->reference)
@section('page-title', 'Décharge de matériel')

@push('styles')
<style>
.discharge-doc { border: 1px solid var(--border, #e2e8f0); border-radius: 10px; padding: 20px; background: #fafbfc; font-size: 13px; }
.discharge-doc table td { padding: 3px 0; }
.signature-box { height: 56px; border: 1px dashed var(--border, #e2e8f0); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #94a3b8; margin-top: 6px; }
.tag { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 6px; font-size: 11px; font-weight: 500; }
.t-amber { background: #FAEEDA; color: #633806; }
.t-green { background: #EAF3DE; color: #27500A; }
.pending-item { display: flex; align-items: center; justify-content: space-between; padding: 9px 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 6px; }
</style>
@endpush

@section('content')

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="page-header">
    <div class="page-header-left">
        <h1>Décharge {{ $discharge->reference }}</h1>
        <p>{{ $discharge->type === 'remise' ? 'Remise de matériel' : 'Restitution de matériel' }} — générée le {{ $discharge->created_at->format('d/m/Y') }}</p>
    </div>
</div>

<div class="form-grid" style="grid-template-columns: 1.4fr 1fr; gap: 20px">
    <div class="card">
        <div class="card-header"><div class="card-title">Aperçu du document</div></div>
        <div class="card-body">
            <div class="discharge-doc">
                <div style="text-align:center;margin-bottom:14px">
                    <div style="font-size:15px;font-weight:600">Décharge de {{ $discharge->type === 'remise' ? 'remise' : 'restitution' }} de matériel</div>
                    <div style="color:#94a3b8;font-size:11px">Document n° {{ $discharge->reference }}</div>
                </div>
                <div style="border-top:1px solid var(--border,#e2e8f0);padding-top:10px;margin-bottom:10px">
                    <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;margin-bottom:6px">Salarié</div>
                    <table>
                        <tr><td style="color:#94a3b8;width:140px">Nom &amp; prénom</td><td style="font-weight:500">{{ $discharge->assignment->employee->full_name }}</td></tr>
                        <tr><td style="color:#94a3b8">Matricule</td><td>{{ $discharge->assignment->employee->matricule }}</td></tr>
                        <tr><td style="color:#94a3b8">Fonction</td><td>{{ $discharge->assignment->employee->position }}</td></tr>
                        <tr><td style="color:#94a3b8">Service</td><td>{{ $discharge->assignment->employee->department }}</td></tr>
                    </table>
                </div>
                <div style="border-top:1px solid var(--border,#e2e8f0);padding-top:10px;margin-bottom:10px">
                    <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;margin-bottom:6px">Matériel concerné</div>
                    <table style="width:100%">
                        <thead>
                            <tr><th style="text-align:left;color:#94a3b8;font-size:11px">Désignation</th><th style="text-align:left;color:#94a3b8;font-size:11px">Réf.</th><th style="text-align:right;color:#94a3b8;font-size:11px">Valeur</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $discharge->assignment->equipment->designation }}</td>
                                <td style="font-family:monospace;font-size:11px">{{ $discharge->assignment->equipment->reference }}</td>
                                <td style="text-align:right">{{ number_format($discharge->assignment->equipment->value, 0, ',', ' ') }} MAD</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="border-top:1px solid var(--border,#e2e8f0);padding-top:10px;margin-bottom:12px;font-size:11px;color:#64748b;line-height:1.6">
                    Je soussigné(e) <strong style="color:#1e293b">{{ $discharge->assignment->employee->full_name }}</strong>,
                    reconnais avoir {{ $discharge->type === 'remise' ? 'reçu' : 'restitué' }} le matériel listé ci-dessus
                    @if($discharge->type === 'remise')
                        en bon état et m'engage à le restituer dans l'état à ma sortie ou sur demande de la direction.
                    @else
                        et confirme que l'état constaté est conforme à ce qui est indiqué.
                    @endif
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                    <div style="text-align:center">
                        <div style="font-size:11px;color:#94a3b8;margin-bottom:6px">Signature du salarié</div>
                        <div class="signature-box">{{ $discharge->status === 'signee' ? 'Signé' : 'En attente' }}</div>
                    </div>
                    <div style="text-align:center">
                        <div style="font-size:11px;color:#94a3b8;margin-bottom:6px">Cachet &amp; signature RH</div>
                        <div class="signature-box">{{ $discharge->status === 'signee' ? 'Signé' : 'En attente' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card mb-4">
            <div class="card-header"><div class="card-title">Actions</div></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                <a href="{{ route('equipment.discharge.pdf', $discharge) }}" class="btn btn-primary" style="width:100%;justify-content:center">
                    Télécharger le PDF
                </a>
                @unless($readonly)
                    @if($discharge->status === 'en_attente')
                        <form action="{{ route('equipment.discharge.sign', $discharge) }}" method="POST" style="width:100%">
                            @csrf
                            <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center">
                                Marquer comme signée
                            </button>
                        </form>
                    @else
                        <span class="tag t-green" style="justify-content:center;padding:8px">Décharge signée le {{ $discharge->signed_at?->format('d/m/Y') }}</span>
                    @endif
                @endunless
            </div>
        </div>

        @if($pending->isNotEmpty())
        <div class="card">
            <div class="card-header"><div class="card-title">Décharges en attente de signature</div></div>
            <div class="card-body">
                @foreach($pending as $p)
                    <div class="pending-item">
                        <div>
                            <div style="font-size:13px;font-weight:500">{{ $p->reference }}</div>
                            <div style="font-size:11px;color:#94a3b8">{{ $p->assignment->employee->full_name }}</div>
                        </div>
                        <a href="{{ route('equipment.discharge', $p) }}" class="tag t-amber" style="text-decoration:none">En attente</a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
