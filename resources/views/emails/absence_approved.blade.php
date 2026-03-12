<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de Congé Approuvée</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: #ffffff;
            border-radius: 16px; 
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .header { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white; 
            padding: 40px 30px; 
            text-align: center;
        }
        .header-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 24px; 
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .body { 
            padding: 40px 30px; 
        }
        .greeting {
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 24px;
        }
        .greeting strong {
            color: #059669;
        }
        .status-badge {
            display: inline-block;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 8px 20px;
            border-radius: 24px;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 24px;
        }
        .info-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 24px;
            margin: 20px 0;
            border: 1px solid #e5e7eb;
        }
        .info-row { 
            display: flex; 
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label { 
            font-weight: 500;
            color: #6b7280;
            font-size: 14px;
        }
        .info-value { 
            color: #1f2937;
            font-weight: 600;
            font-size: 14px;
        }
        .footer { 
            background: #f9fafb;
            padding: 24px 30px;
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 4px 0;
        }
        .company {
            color: #6b7280;
            font-weight: 600;
            margin-top: 12px !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">✅</div>
            <h1>Demande Approuvée</h1>
            <p>Votre demande de congé a été acceptée</p>
        </div>
        
        <div class="body">
            <div class="greeting">
                Bonjour <strong>{{ $absence->employee->full_name }}</strong>,
            </div>
            
            <div style="text-align: center;">
                <span class="status-badge">✓ Approuvée</span>
            </div>
            
            <p style="color: #4b5563; margin-bottom: 20px; line-height: 1.6;">
                Nous avons le plaisir de vous informer que votre demande de congé a été <strong>approuvée</strong>. 
                Vous pouvez désormais profiter de vos congés en toute tranquillité.
            </p>
            
            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">📅 Type de congé</span>
                    <span class="info-value">{{ \App\Models\Absence::TYPES[$absence->type] ?? $absence->type }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">📆 Date de début</span>
                    <span class="info-value">{{ $absence->start_date->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">📆 Date de fin</span>
                    <span class="info-value">{{ $absence->end_date->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">⏱️ Nombre de jours</span>
                    <span class="info-value">{{ $absence->days }} jour(s)</span>
                </div>
                <div class="info-row">
                    <span class="info-label">✅ Approuvé le</span>
                    <span class="info-value">{{ $absence->approved_at ? $absence->approved_at->format('d/m/Y à H:i') : now()->format('d/m/Y à H:i') }}</span>
                </div>
                @if($absence->replacement)
                <div class="info-row">
                    <span class="info-label">🔄 Remplacé par</span>
                    <span class="info-value">{{ $absence->replacement->full_name }}</span>
                </div>
                @endif
            </div>
            
            <p style="color: #6b7280; font-size: 14px; margin-top: 24px;">
                Pour toute question, n'hésitez pas à contacter le service des ressources humaines.
            </p>
            
            <p style="color: #059669; font-weight: 600; margin-top: 20px;">
                Bonne continuité ! 👋
            </p>
        </div>
        
        <div class="footer">
            <p>Ce message est automatique, merci de ne pas y répondre directement.</p>
            <p class="company">🏥 HospitalRH - Gestion des Ressources Humaines</p>
        </div>
    </div>
</body>
</html>
