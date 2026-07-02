<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Badges PIN</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            background: #fff;
            color: #1f2937;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 100%;
        }

        /* En-tête — fond blanc, juste une bordure, pas de gros padding */
        .header {
            background: #fff;
            color: #1f2937;
            padding: 8px 0 14px 0;
            margin-bottom: 18px;
            border-bottom: 2px solid #b5b4b7;
            text-align: left;
        }

        .header h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #1f2937;
        }

        .header p {
            font-size: 11px;
            color: #6b7280;
        }

        /* Info filtre */
        .filter-info {
            background: #f3f4f6;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 11px;
            color: #6b7280;
        }

        /* Département card */
        .dept-section {
            margin-bottom: 22px;
        }

        .dept-header {
            background: #f9fafb;
            border-left: 4px solid #b5b4b7;
            padding: 10px 14px;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .dept-title {
            font-size: 13px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .dept-count {
            font-size: 11px;
            color: #9ca3af;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }

        thead {
            background: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
        }

        th {
            padding: 10px;
            text-align: left;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.05em;
        }

        td {
            padding: 9px 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Colonnes */
        .col-num {
            width: 40px;
            text-align: center;
            color: #9ca3af;
        }

        .col-name {
            width: 35%;
            font-weight: 500;
        }

        .col-matricule {
            width: 25%;
            font-family: 'Courier New', monospace;
            color: #6b7280;
        }

        .col-pin {
            width: 25%;
            text-align: center;
        }

        /* PIN code */
        .pin-badge {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
            background: #f5f3ff;
            color: #b5b4b7;
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #e9d5ff;
            display: inline-block;
            min-width: 85px;
            text-align: center;
        }

        /* Footer */
        .footer {
            margin-top: 25px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: right;
        }

        @page {
            margin: 10mm;
            size: A4;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- En-tête --}}
        <div class="header">
    <h1>{{ $tenant?->name ?? config('app.name') }}</h1>
    <p>BADGES PIN - Liste des codes PIN d'accès badges</p>
</div>

        {{-- Info filtre --}}
        @if($deptFilter !== 'Tous')
        <div class="filter-info">
            <strong>Département :</strong> {{ $deptFilter }}
        </div>
        @endif

        {{-- Départements --}}
        @forelse($byDept as $dept => $employees)
        <div class="dept-section">
            <div class="dept-header">
                <div class="dept-title">{{ $dept ?: 'Sans département' }}</div>
                <div class="dept-count">{{ $employees->count() }} employé{{ $employees->count() > 1 ? 's' : '' }}</div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th class="col-name">Employé</th>
                        <th class="col-matricule">Matricule</th>
                        <th class="col-pin">PIN Badge</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $i => $emp)
                    <tr>
                        <td class="col-num">{{ $i + 1 }}</td>
                        <td class="col-name">{{ $emp->first_name }} {{ $emp->last_name }}</td>
                        <td class="col-matricule">{{ $emp->matricule ?? '—' }}</td>
                        <td class="col-pin">
                            <span class="pin-badge">{{ $emp->plain_pin ?? '——' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @empty
        <div class="filter-info">
            Aucun employé trouvé.
        </div>
        @endforelse

        {{-- Footer --}}
        <div class="footer">
            Généré le {{ $generatedAt }}
        </div>
    </div>
</body>
</html>
