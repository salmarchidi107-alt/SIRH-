<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vérification de sécurité — {{ $name ?? 'Vérification' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        :root {
            --primary: {{ $tenantData['brand_color'] ?? '#0f6b7c' }};
            --primary-light: #1a8fa5;
            --accent: #00c9a7;
            --navy: #0d2137;
            --bg: #f4f7fa;
        }

        body {
            margin: 0; padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }

        .auth-wrapper { width: 100%; max-width: 440px; padding: 20px; }

        .auth-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px -3px rgba(0,0,0,.08), 0 4px 6px -2px rgba(0,0,0,.04);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .auth-header {
            background: linear-gradient(135deg, var(--navy) 0%, var(--primary) 100%);
            padding: 32px 24px; text-align: center;
            position: relative; overflow: hidden;
        }
        .auth-header::before {
            content: ''; position: absolute;
            top: -30px; right: -30px;
            width: 120px; height: 120px;
            background: rgba(255,255,255,.04); border-radius: 50%;
        }

        .auth-brand { display: flex; align-items: center; justify-content: center; gap: 12px; }
        .auth-brand-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: white;
            box-shadow: 0 4px 12px rgba(0,201,167,0.35);
            overflow: hidden;
        }
        .auth-logo-img { width: 100%; height: 100%; object-fit: cover; }
        .auth-brand-name {
            font-family: 'Syne', sans-serif;
            font-size: 1.5rem; font-weight: 700;
            color: white; letter-spacing: -0.3px;
        }
        .auth-subtitle { color: rgba(255,255,255,.6); font-size: 0.875rem; margin-top: 8px; }

        .auth-body { padding: 32px 28px; }

        /* ── Alerte erreur ── */
        .alert {
            padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;
            font-size: 0.85rem; display: flex; align-items: center; gap: 10px;
            border-left: 4px solid;
        }
        .alert-danger { background: #fef2f2; color: #991b1b; border-color: #ef4444; }

        /* ── Label ── */
        .auth-label {
            display: block; font-size: 0.8rem; font-weight: 600;
            color: #0f172a; margin-bottom: 10px; text-align: center;
        }

        /* ── 6 cases OTP ── */
        .otp-digits { display: flex; gap: 10px; justify-content: center; margin-bottom: 8px; }
        .otp-digit {
            width: 50px; height: 58px;
            border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: 1.5rem; font-weight: 700; text-align: center;
            color: #0f172a; font-family: 'Courier New', monospace;
            background: #f8fafc; outline: none;
            transition: all 0.2s; box-sizing: border-box;
        }
        .otp-digit:focus {
            border-color: var(--primary); background: #fff;
            box-shadow: 0 0 0 3px rgba(15,107,124,0.1);
        }
        .otp-digit.has-error { border-color: #ef4444; background: #fef2f2; }

        .auth-hint { font-size: 0.75rem; color: #94a3b8; text-align: center; margin-bottom: 24px; }

        /* ── Bouton principal ── */
        .auth-btn {
            width: 100%; padding: 12px 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white; border: none; border-radius: 8px;
            font-size: 0.9rem; font-weight: 600; cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(15,107,124,0.3);
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-bottom: 16px;
        }
        .auth-btn:hover { box-shadow: 0 6px 18px rgba(15,107,124,0.4); transform: translateY(-1px); }

        /* ── Séparateur ── */
        .auth-divider {
            display: flex; align-items: center; gap: 12px;
            margin: 4px 0 14px; color: #94a3b8; font-size: 0.78rem;
        }
        .auth-divider::before, .auth-divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

        /* ── Bouton déconnexion ── */
        .auth-logout {
            width: 100%; padding: 10px 14px;
            background: #f4f7fa; border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: 0.82rem; font-weight: 500; color: #64748b;
            cursor: pointer; font-family: inherit;
            display: flex; align-items: center; justify-content: center; gap: 7px;
            transition: all 0.2s;
        }
        .auth-logout:hover { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

        /* ── Pied de card ── */
        .auth-footer {
            text-align: center; padding: 16px 20px 20px;
            border-top: 1px solid #e2e8f0; margin-top: 8px;
        }
        .auth-footer p { font-size: 0.85rem; color: #64748b; margin: 0; }
        .auth-footer a { color: var(--primary); font-weight: 600; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-header">
            <div class="auth-brand">
                <div class="auth-brand-icon">
                    @if(isset($tenantData['logo_path']) && $tenantData['logo_path'])
                        <img src="{{ asset('storage/' . $tenantData['logo_path']) }}"
                             alt="{{ $tenantData['name'] ?? 'Logo' }}" class="auth-logo-img">
                    @else
                        <svg width="26" height="26" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944
                                     a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9
                                     c0 5.591 3.824 10.29 9 11.622
                                     5.176-1.332 9-6.03 9-11.622
                                     0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    @endif
                </div>
                <div class="auth-brand-name">{{ $tenantData['name'] ?? 'Vérification' }}</div>
            </div>
            <p class="auth-subtitle">Vérification de sécurité</p>
        </div>

        <div class="auth-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4M12 16h.01"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('2fa.verify') }}" autocomplete="off" id="otp-form">
                @csrf

                {{-- Champ caché qui reçoit la valeur assemblée avant soumission --}}
                <input type="hidden" name="code" id="otp-hidden">

                <label class="auth-label">Code de vérification à 6 chiffres</label>

                <div class="otp-digits">
                    @for ($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            class="otp-digit {{ $errors->has('code') ? 'has-error' : '' }}"
                            maxlength="1"
                            inputmode="numeric"
                            pattern="[0-9]"
                            data-index="{{ $i }}"
                            autocomplete="off"
                            spellcheck="false"
                            {{ $i === 0 ? 'autofocus' : '' }}
                        >
                    @endfor
                </div>

                <p class="auth-hint">Saisissez le code attribué par votre administrateur</p>

                <button type="submit" class="auth-btn">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Vérifier mon identité
                </button>
            </form>

            <div class="auth-divider">ou</div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="auth-logout">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Se déconnecter · Retour à la connexion
                </button>
            </form>

        </div>

        <div class="auth-footer">
            <p>Pas de code ? Contactez votre <a href="#">administrateur</a></p>
        </div>

    </div>
</div>

<script>
(function () {
    const digits  = [...document.querySelectorAll('.otp-digit')];
    const hidden  = document.getElementById('otp-hidden');
    const form    = document.getElementById('otp-form');

    digits.forEach((inp, i) => {
        inp.addEventListener('input', () => {
            inp.value = inp.value.replace(/\D/g, '').slice(0, 1);
            if (inp.value && i < digits.length - 1) digits[i + 1].focus();
        });

        inp.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !inp.value && i > 0) digits[i - 1].focus();
        });

        inp.addEventListener('paste', e => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData)
                .getData('text').replace(/\D/g, '').slice(0, 6);
            [...text].forEach((ch, j) => { if (digits[i + j]) digits[i + j].value = ch; });
            const next = Math.min(i + text.length, digits.length - 1);
            digits[next].focus();
        });
    });

    form.addEventListener('submit', () => {
        hidden.value = digits.map(d => d.value).join('');
    });
})();
</script>
</body>
</html>
