{{-- resources/views/pdf/badges-pin.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        /* En-tête */
        .header {
            background: linear-gradient(135deg, #7c3aed 0%, #0d9488 100%);
            color: #fff;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 8px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 12px;
            opacity: 0.9;
        }

        /* Info filtre */
        .filter-info {
            background: #f3f4f6;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 11px;
            color: #6b7280;
        }

        /* Département card */
        .dept-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .dept-header {
            background: #f9fafb;
            border-left: 4px solid #7c3aed;
            padding: 12px 16px;
            margin-bottom: 12px;
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
            background: linear-gradient(135deg, #f5f3ff 0%, #f0fdfa 100%);
            color: #7c3aed;
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #e9d5ff;
            display: inline-block;
            min-width: 85px;
            text-align: center;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: right;
        }

        /* Page break */
        .dept-section:nth-child(2n) {
            margin-bottom: 30px;
        }

        @page {
            margin: 10mm;
            size: A4;
        }

        @media print {
            body {
                background: #fff;
            }
            .dept-section {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- En-tête --}}
        <div class="header">
            <h1>🔐 BADGES PIN</h1>
            <p>Listing des codes PIN d'accès badges</p>
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
