<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion - {{ $name ?? 'MedStaff' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">

    {{-- Couleur du tenant : dynamique en PHP, ne peut pas vivre dans app.css --}}
    <style>
        .auth-page {
            --primary: {{ $tenantData['brand_color'] ?? '#0f6b7c' }};
            --primary-light: color-mix(in srgb, var(--primary) 65%, white);
            --primary-deep: color-mix(in srgb, var(--primary) 75%, black);
            --primary-tint: color-mix(in srgb, var(--primary) 10%, white);
            --primary-glow: color-mix(in srgb, var(--primary) 45%, transparent);
        }
    </style>
</head>
<body class="auth-page">

<div class="page">

    <!-- ============ GAUCHE : PANNEAU SOMBRE + SIGNATURE ============ -->
    <div class="panel-visual">
        <div class="ms-blob ms-blob--1"></div>
        <div class="ms-blob ms-blob--2"></div>

        <div class="visual-top-row">
            <div class="visual-top">
                <div class="eyebrow">SIRH · Plénitude Groupe</div>
                <h1>Pilotez votre <span>
                    capital humain</span></h1>
                <p>Digitalisez, automatisez et pilotez l'ensemble du cycle de vie de vos collaborateurs depuis une seule plateforme — pensée pour la santé et tous les secteurs.</p>
            </div>

            <div class="logo-wrap">
                <div class="logo-card">
                    @if(isset($tenantData['logo_path']) && $tenantData['logo_path'])
                        <img src="{{ asset('storage/' . $tenantData['logo_path']) }}" alt="{{ $tenantData['name'] ?? 'Logo' }}">
                    @else
                        <img src="{{ asset('images/medstaff-logo.jpeg') }}" alt="MedStaff">
                    @endif
                    <div class="bar"><span></span></div>
                </div>
            </div>
        </div>

        <div class="signature-zone">

            <div class="hr-scene-wrap" id="hrSceneWrap">
                <div class="hr-scene-inner" id="hrSceneInner">
                    <svg class="hr-scene-svg" viewBox="0 0 300 150" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="paperEdge" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#ffffff"/>
                                <stop offset="100%" style="stop-color:#E9EAF6"/>
                            </linearGradient>
                            <radialGradient id="floorGlowGrad" cx="50%" cy="50%" r="50%">
                                <stop offset="0%" style="stop-color:var(--primary-light);stop-opacity:.55"/>
                                <stop offset="100%" style="stop-color:var(--primary-light);stop-opacity:0"/>
                            </radialGradient>
                        </defs>

                        <!-- halo au sol -->
                        <ellipse class="hr-floor-glow" cx="150" cy="128" rx="110" ry="14" fill="url(#floorGlowGrad)"/>

                        <!-- bureau -->
                        <rect x="70" y="118" width="170" height="9" rx="3" fill="#2A2568" stroke="rgba(255,255,255,.14)"/>
                        <rect x="80" y="127" width="7" height="20" fill="#211D52"/>
                        <rect x="222" y="127" width="7" height="20" fill="#211D52"/>

                        <!-- ordinateur -->
                        <rect x="95" y="70" width="70" height="49" rx="6" fill="#221E58" stroke="rgba(255,255,255,.16)" stroke-width="1.4"/>
                        <rect x="101" y="76" width="58" height="37" rx="2" fill="#100D33"/>
                        <rect class="hr-screen-glow" x="101" y="76" width="58" height="37" rx="2" fill="var(--primary-glow)"/>
                        <rect class="hr-type-line hr-type-line--1" x="107" y="84" width="40" height="3" rx="1.5" fill="var(--teal)"/>
                        <rect class="hr-type-line hr-type-line--2" x="107" y="93" width="30" height="3" rx="1.5" fill="var(--primary-light)"/>
                        <rect class="hr-type-line hr-type-line--3" x="107" y="102" width="36" height="3" rx="1.5" fill="var(--teal)"/>

                        <!-- collaborateur RH -->
                        <g>
                            <rect x="190" y="74" width="42" height="46" rx="18" fill="#EDEEF9"/>
                            <g class="hr-head">
                                <circle cx="211" cy="60" r="15" fill="#F7D9BE"/>
                                <path d="M197 55 q14 -18 28 0 q0 -14 -14 -16 q-14 2 -14 16z" fill="#2A2568"/>
                            </g>
                            <g class="hr-arm">
                                <path d="M198 84 Q176 96 160 108" stroke="#EDEEF9" stroke-width="11" stroke-linecap="round" fill="none"/>
                                <circle cx="160" cy="108" r="6" fill="#F7D9BE"/>
                            </g>
                        </g>

                        <!-- pile de feuilles déjà rangées -->
                        <g transform="translate(96,108)">
                            <rect x="-11" y="-14" width="22" height="28" rx="2" fill="url(#paperEdge)" stroke="#D6D8EE" transform="rotate(-4)"/>
                            <rect x="-11" y="-14" width="22" height="28" rx="2" fill="url(#paperEdge)" stroke="#D6D8EE" transform="translate(3,-2) rotate(3)"/>
                        </g>

                        <!-- feuilles volantes qui se rangent en boucle -->
                        <g class="hr-paper hr-paper-1">
                            <rect x="-11" y="-14" width="22" height="28" rx="2" fill="url(#paperEdge)" stroke="#D6D8EE"/>
                            <line x1="-6" y1="-6" x2="6" y2="-6" stroke="var(--muted)" stroke-width="1.4"/>
                            <line x1="-6" y1="-1" x2="6" y2="-1" stroke="var(--muted)" stroke-width="1.4"/>
                            <line x1="-6" y1="4" x2="2" y2="4" stroke="var(--muted)" stroke-width="1.4"/>
                        </g>
                        <g class="hr-paper hr-paper-2">
                            <rect x="-11" y="-14" width="22" height="28" rx="2" fill="url(#paperEdge)" stroke="#D6D8EE"/>
                            <line x1="-6" y1="-6" x2="6" y2="-6" stroke="var(--muted)" stroke-width="1.4"/>
                            <line x1="-6" y1="-1" x2="6" y2="-1" stroke="var(--muted)" stroke-width="1.4"/>
                            <line x1="-6" y1="4" x2="2" y2="4" stroke="var(--muted)" stroke-width="1.4"/>
                        </g>
                        <g class="hr-paper hr-paper-3">
                            <rect x="-11" y="-14" width="22" height="28" rx="2" fill="url(#paperEdge)" stroke="#D6D8EE"/>
                            <line x1="-6" y1="-6" x2="6" y2="-6" stroke="var(--muted)" stroke-width="1.4"/>
                            <line x1="-6" y1="-1" x2="6" y2="-1" stroke="var(--muted)" stroke-width="1.4"/>
                            <line x1="-6" y1="4" x2="2" y2="4" stroke="var(--muted)" stroke-width="1.4"/>
                        </g>
                        <g class="hr-paper hr-paper-4">
                            <rect x="-11" y="-14" width="22" height="28" rx="2" fill="url(#paperEdge)" stroke="#D6D8EE"/>
                            <line x1="-6" y1="-6" x2="6" y2="-6" stroke="var(--muted)" stroke-width="1.4"/>
                            <line x1="-6" y1="-1" x2="6" y2="-1" stroke="var(--muted)" stroke-width="1.4"/>
                            <line x1="-6" y1="4" x2="2" y2="4" stroke="var(--muted)" stroke-width="1.4"/>
                        </g>
                    </svg>
                </div>
            </div>
            <div class="hr-scene-caption">RH, en temps réel</div>
        </div>

        <div class="visual-bottom">
            <div class="platform-line"></div>
            <div class="certs">
                <a href="https://www.medstaff.ma/" target="_blank" rel="noopener">www.medstaff.ma</a>
                <span>ISO 27001</span>
                <span>HDS</span>
                <span>Cloud</span>
            </div>
        </div>
    </div>

    <!-- ============ DROITE : FORMULAIRE ============ -->
    <div class="panel-form">
        <div class="card">

            <div class="head">
                @if(isset($tenantData['logo_path']) && $tenantData['logo_path'])
                    <img src="{{ asset('storage/' . $tenantData['logo_path']) }}" alt="{{ $tenantData['name'] ?? 'Logo' }}" class="brand-logo-img">
                @else
                    <img src="{{ asset('images/medstaff-logo.jpeg') }}" alt="MedStaff" class="brand-logo-img">
                @endif
                <span class="brand-sub">{{ $tenantData['name'] ?? 'HR Solutions' }}</span>
            </div>

            <h1 class="title">Connexion</h1>
            <p class="lead">Accédez à votre espace RH </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <span>Email ou mot de passe incorrect</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label for="email">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M3 6.5A2.5 2.5 0 0 1 5.5 4h13A2.5 2.5 0 0 1 21 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 17.5v-11z" stroke="currentColor" stroke-width="1.6"/><path d="M4 7l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Adresse e-mail
                    </label>
                    <div class="input-wrap" id="wrap-email">
                        <input id="email" type="email" name="email" placeholder="prenom.nom@societe.ma"
                               value="{{ old('email') }}" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="field">
                    <label for="password">
                        <svg viewBox="0 0 24 24" fill="none"><rect x="5" y="10.5" width="14" height="9.5" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                        Mot de passe
                    </label>
                    <div class="input-wrap" id="wrap-password">
                        <input id="password" type="password" name="password" placeholder="••••••••••"
                               required autocomplete="current-password">
                        <button type="button" class="toggle-pw" id="togglePass" aria-label="Afficher le mot de passe">
                            <svg viewBox="0 0 24 24" fill="none" id="eyeIcon"><path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/></svg>
                        </button>
                    </div>
                </div>

                <div class="row-between">
                    <label class="remember">
                        <input type="checkbox" name="remember">
                        Se souvenir de moi
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot">Mot de passe oublié ?</a>
                    @endif
                </div>

                <button type="submit" class="auth-btn">
                    Se connecter
                    <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </form>

            <div class="divider"></div>

            <p class="powered">
                Solution proposée par
                <img src="https://plenitudegroupe.com/wp-content/uploads/2025/10/Plenitude-Groupe-Iso-27OO1-1-Photoroom.png" alt="Plénitude Groupe">
                <a href="https://plenitudegroupe.com/" target="_blank" rel="noopener">Plénitude Groupe</a>
            </p>
        </div>
    </div>

</div>

<script>
    // focus visuel des champs
    document.querySelectorAll('.input-wrap').forEach(function(wrap){
        var input = wrap.querySelector('input');
        if(!input) return;
        input.addEventListener('focus', function(){ wrap.classList.add('is-focused'); });
        input.addEventListener('blur', function(){ wrap.classList.remove('is-focused'); });
    });

    // afficher / masquer le mot de passe
    var toggleBtn = document.getElementById('togglePass');
    var passInput = document.getElementById('password');
    var eyeIcon = document.getElementById('eyeIcon');

    if(toggleBtn){
        toggleBtn.addEventListener('click', function(){
            var isHidden = passInput.type === 'password';
            passInput.type = isHidden ? 'text' : 'password';
            eyeIcon.innerHTML = isHidden
                ? '<path d="M3 3l18 18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M10.6 5.2A10.8 10.8 0 0 1 12 5c7 0 10.5 7 10.5 7a17.9 17.9 0 0 1-3.1 4M6.6 6.6C3.7 8.5 1.5 12 1.5 12S5 19 12 19c1.4 0 2.7-.3 3.8-.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>'
                : '<path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/>';
        });
    }

    // entrée en fondu du bloc logo (3D)
    var logoWrap = document.querySelector('.logo-wrap');
    var logoCard = document.querySelector('.logo-card');

    if(logoWrap && logoCard){
        requestAnimationFrame(function(){
            requestAnimationFrame(function(){ logoCard.classList.add('is-visible'); });
        });

        var maxTilt = 16; // degrés max d'inclinaison

        function applyTilt(clientX, clientY){
            var rect = logoWrap.getBoundingClientRect();
            var px = (clientX - rect.left) / rect.width - 0.5;
            var py = (clientY - rect.top) / rect.height - 0.5;
            var rotateY = px * maxTilt * 2;
            var rotateX = -py * maxTilt * 2;
            logoCard.style.transform =
                'translateY(0) scale(1.05) rotateX(' + rotateX.toFixed(2) + 'deg) rotateY(' + rotateY.toFixed(2) + 'deg)';
        }

        function resetTilt(){
            logoCard.style.transform = '';
        }

        // souris (desktop)
        logoWrap.addEventListener('mousemove', function(e){
            applyTilt(e.clientX, e.clientY);
        });
        logoWrap.addEventListener('mouseleave', resetTilt);

        // tactile (mobile/tablette)
        logoWrap.addEventListener('touchmove', function(e){
            if(e.touches && e.touches[0]){
                applyTilt(e.touches[0].clientX, e.touches[0].clientY);
            }
        }, { passive: true });
        logoWrap.addEventListener('touchend', resetTilt);
    }

    // interaction 3D de la scène RH (bureau / ordinateur / feuilles)
    var hrWrap = document.getElementById('hrSceneWrap');
    var hrInner = document.getElementById('hrSceneInner');

    if(hrWrap && hrInner){
        var hrMaxTilt = 10; // degrés, plus subtil que le logo

        function applyHrTilt(clientX, clientY){
            var rect = hrWrap.getBoundingClientRect();
            var px = (clientX - rect.left) / rect.width - 0.5;
            var py = (clientY - rect.top) / rect.height - 0.5;
            var rotateY = px * hrMaxTilt * 2;
            var rotateX = -py * hrMaxTilt * 2;
            hrInner.style.transform =
                'rotateX(' + rotateX.toFixed(2) + 'deg) rotateY(' + rotateY.toFixed(2) + 'deg) translateZ(6px)';
        }

        function resetHrTilt(){
            hrInner.style.transform = '';
        }

        hrWrap.addEventListener('mousemove', function(e){
            applyHrTilt(e.clientX, e.clientY);
        });
        hrWrap.addEventListener('mouseleave', resetHrTilt);

        hrWrap.addEventListener('touchmove', function(e){
            if(e.touches && e.touches[0]){
                applyHrTilt(e.touches[0].clientX, e.touches[0].clientY);
            }
        }, { passive: true });
        hrWrap.addEventListener('touchend', resetHrTilt);

        // un petit "coup de main" au clic : accélère brièvement le classement
        hrWrap.addEventListener('click', function(){
            hrInner.style.transition = 'transform .15s var(--ease)';
            hrInner.style.transform = 'rotateX(-4deg) rotateY(0deg) scale(1.03) translateZ(10px)';
            setTimeout(function(){
                hrInner.style.transition = 'transform .5s var(--ease)';
                hrInner.style.transform = '';
            }, 160);
        });
    }
</script>

</body>
</html>
