<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vérification - {{ $name ?? 'MedStaff' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Couleur du tenant (dynamique) */
            --primary: {{ $tenantData['brand_color'] ?? '#0f6b7c' }};
            --primary-light: color-mix(in srgb, var(--primary) 65%, white);
            --primary-deep: color-mix(in srgb, var(--primary) 75%, black);
            --primary-tint: color-mix(in srgb, var(--primary) 10%, white);
            --primary-glow: color-mix(in srgb, var(--primary) 45%, transparent);

            /* Identité visuelle du panneau (fixe) */
            --navy-deep: #0F0C30;
            --navy: #1B1750;
            --teal: #16B999;

            --ink: #14152B;
            --paper: #FFFFFF;
            --mist: #F6F7FB;
            --line: #E6E7F1;
            --muted: #8A8CA8;
            --danger: #E22F24;

            --font-display: 'Manrope', 'Syne', sans-serif;
            --font-body: 'Inter', 'Plus Jakarta Sans', sans-serif;
            --font-mono: 'IBM Plex Mono', monospace;

            --radius-lg: 20px;
            --radius-md: 14px;
            --radius-sm: 11px;
            --ease: cubic-bezier(.22, 1, .36, 1);
        }

        *{ box-sizing:border-box; }
        html,body{ height:100%; margin:0; }
        body{
            font-family:var(--font-body);
            color:var(--ink);
            -webkit-font-smoothing:antialiased;
            overflow:hidden;
        }

        .page{
            height:100vh;
            display:grid;
            grid-template-columns:1fr 1fr;
        }

        /* ============ GAUCHE — PANNEAU SOMBRE + SIGNATURE ============ */
        .panel-visual{
            position:relative;
            background:
                radial-gradient(70% 55% at 8% 0%, var(--primary-glow), transparent 62%),
                radial-gradient(55% 50% at 100% 100%, color-mix(in srgb, var(--primary-light) 20%, transparent), transparent 60%),
                linear-gradient(170deg, var(--navy-deep) 0%, #0A0822 100%);
            color:#fff;
            overflow:hidden;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            padding:52px 54px;
            opacity:0;
            animation:panelFade .9s var(--ease) forwards;
        }
        .panel-visual::before{
            content:"";
            position:absolute;
            inset:0;
            background-image:radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px);
            background-size:26px 26px;
            mask-image:radial-gradient(ellipse 70% 60% at 50% 40%, black 0%, transparent 75%);
            pointer-events:none;
        }

        /* blobs ambiants animés */
        .ms-blob{
            position:absolute;
            border-radius:50%;
            filter:blur(75px);
            opacity:.32;
            pointer-events:none;
        }
        .ms-blob--1{
            width:360px; height:360px;
            background:var(--primary);
            top:-110px; left:-90px;
            animation:drift1 17s ease-in-out infinite;
        }
        .ms-blob--2{
            width:300px; height:300px;
            background:var(--teal);
            bottom:-120px; right:-70px;
            animation:drift2 19s ease-in-out infinite;
        }
        @keyframes drift1{ 0%,100%{ transform:translate(0,0) scale(1);} 50%{ transform:translate(26px,34px) scale(1.07);} }
        @keyframes drift2{ 0%,100%{ transform:translate(0,0) scale(1);} 50%{ transform:translate(-22px,-26px) scale(1.05);} }
        @keyframes panelFade{ from{opacity:0;} to{opacity:1;} }

        .visual-top, .signature-zone, .visual-bottom{ position:relative; z-index:1; }

        .eyebrow{
            font-family:var(--font-mono);
            font-size:11.5px;
            font-weight:500;
            letter-spacing:.14em;
            text-transform:uppercase;
            color:#B9B8E4;
            display:flex;
            align-items:center;
            gap:9px;
        }
        .eyebrow::before{
            content:"";
            width:7px; height:7px;
            border-radius:50%;
            background:var(--primary-light);
            box-shadow:0 0 0 3px color-mix(in srgb, var(--primary-light) 25%, transparent);
            animation:dotPulse 2.4s ease-in-out infinite;
        }
        @keyframes dotPulse{ 0%,100%{ opacity:.6; } 50%{ opacity:1; } }

        .visual-top h1{
            font-family:var(--font-display);
            font-weight:800;
            font-size:clamp(26px, 2.25vw, 34px);
            line-height:1.24;
            letter-spacing:-.01em;
            margin:16px 0 12px;
            max-width:10.5em;
        }
        .visual-top h1 span{ color:var(--primary-light); }

        .visual-top p{
            font-size:14.5px;
            line-height:1.6;
            color:#C6C5EA;
            max-width:34ch;
            margin:0;
        }

        /* ---- zone signature : logo + tracé ECG -> organigramme ---- */
        .signature-zone{
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:22px;
            margin:8px 0;
        }

        .logo-wrap{
            position:relative;
            display:flex;
            align-items:center;
            justify-content:center;
            perspective:900px;
            animation:idleFloat 6s ease-in-out infinite;
            cursor:pointer;
        }
        @keyframes idleFloat{
            0%,100%{ transform:translateY(0); }
            50%{ transform:translateY(-9px); }
        }
        .logo-wrap::before{
            content:"";
            position:absolute;
            width:260px; height:260px;
            border-radius:50%;
            background:radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            filter:blur(8px);
            z-index:0;
        }
        .logo-card{
            position:relative;
            z-index:1;
            width:236px;
            border-radius:var(--radius-lg);
            background:linear-gradient(160deg,#ffffff, #EEEFF8);
            box-shadow:
                0 30px 60px -22px rgba(0,0,0,.5),
                0 0 0 1px rgba(255,255,255,.08);
            padding:30px 26px;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:14px;
            opacity:0;
            transform:translateY(14px) scale(.97) rotateX(0deg) rotateY(0deg);
            transition:opacity .8s var(--ease), transform .5s var(--ease), box-shadow .4s var(--ease);
            transition-delay:.2s, 0s, 0s;
            transform-style:preserve-3d;
            will-change:transform;
        }
        .logo-card.is-visible{
            opacity:1;
            transform:translateY(0) scale(1) rotateX(0deg) rotateY(0deg);
        }
        .logo-wrap:hover .logo-card{
            box-shadow:
                0 40px 80px -20px rgba(0,0,0,.55),
                0 0 0 1px rgba(255,255,255,.12);
        }
        .logo-card img{ max-width:168px; max-height:78px; object-fit:contain; }
        .logo-card .bar{ width:100%; height:4px; border-radius:3px; background:var(--line); overflow:hidden; }
        .logo-card .bar span{ display:block; height:100%; width:74%; background:linear-gradient(90deg, var(--primary), var(--primary-light)); }

        .signature-svg-wrap{ width:100%; max-width:300px; height:86px; position:relative; }
        .signature-svg{ width:100%; height:100%; overflow:visible; }
        .ms-pulse-path{
            fill:none;
            stroke:url(#pulseGradient);
            stroke-width:2.25;
            stroke-linecap:round;
            stroke-linejoin:round;
            stroke-dasharray:600;
            stroke-dashoffset:600;
            animation:drawLine 2s var(--ease) forwards .6s;
        }
        @keyframes drawLine{ to{ stroke-dashoffset:0; } }
        .ms-node{ opacity:0; animation:nodePop .45s var(--ease) forwards; }
        @keyframes nodePop{ from{ opacity:0; transform:scale(.3);} to{ opacity:1; transform:scale(1);} }

        .platform-line{
            display:flex;
            align-items:center;
            gap:11px;
            padding-top:20px;
            border-top:1px solid rgba(255,255,255,.12);
            margin-bottom:16px;
        }
        .platform-line .plate{
            background:#fff;
            border-radius:8px;
            padding:5px 8px;
            display:flex;
            align-items:center;
        }
        .platform-line .plate img{ height:18px; width:auto; object-fit:contain; }
        .platform-line span{ font-size:12.5px; color:#C6C5EA; }
        .platform-line b{ color:#fff; font-weight:700; }

        .certs{ display:flex; gap:8px; flex-wrap:wrap; }
        .certs span, .certs a{
            font-family:var(--font-mono);
            font-size:10px;
            letter-spacing:.05em;
            color:#E4E3F5;
            border:1px solid rgba(255,255,255,.16);
            background:rgba(255,255,255,.04);
            padding:4px 10px;
            border-radius:100px;
        }
        .certs a{
            text-decoration:none;
            cursor:pointer;
            transition:border-color .2s var(--ease), background .2s var(--ease), color .2s var(--ease);
        }
        .certs a:hover{
            border-color:color-mix(in srgb, var(--primary-light) 55%, transparent);
            background:rgba(255,255,255,.09);
            color:#fff;
        }

        /* ============ DROITE — VÉRIFICATION ============ */
        .panel-form{
            position:relative;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:24px 64px;
            overflow:hidden;
            background:var(--paper);
        }
        .panel-form::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(34% 40% at 12% 18%, color-mix(in srgb, var(--primary) 12%, transparent), transparent 70%),
                radial-gradient(30% 36% at 88% 82%, color-mix(in srgb, var(--teal) 10%, transparent), transparent 70%),
                radial-gradient(24% 30% at 80% 15%, color-mix(in srgb, var(--primary-light) 8%, transparent), transparent 70%);
            filter:blur(50px);
            animation:formDrift 24s ease-in-out infinite;
            pointer-events:none;
            z-index:0;
            will-change:transform;
        }
        .panel-form::after{
            content:"";
            position:absolute;
            inset:0;
            background-image:radial-gradient(rgba(20,21,43,.035) 1px, transparent 1px);
            background-size:30px 30px;
            mask-image:radial-gradient(ellipse 65% 55% at 50% 45%, black 0%, transparent 78%);
            pointer-events:none;
            z-index:0;
        }
        @keyframes formDrift{
            0%,100%{ transform:translate(0,0) scale(1); }
            50%{ transform:translate(2.5%, -3%) scale(1.06); }
        }

        .card{
            position:relative;
            z-index:1;
            width:100%;
            max-width:460px;
            background:transparent;
            border:none;
            border-radius:0;
            box-shadow:none;
            padding:0;
            opacity:0;
            /* petite entrée en glissement depuis la droite : donne la sensation
               d'une continuité douce avec la page de connexion, sans fusionner
               les deux pages */
            transform:translateX(28px);
            animation:cardSlideIn .6s var(--ease) forwards .1s;
        }
        @keyframes cardSlideIn{ from{ opacity:0; transform:translateX(28px);} to{ opacity:1; transform:translateX(0);} }

        .head{ margin-bottom:22px; }
        .brand-logo-img{ max-height:56px; max-width:220px; object-fit:contain; display:block; margin-bottom:6px; }
        .brand-sub{
            display:block;
            font-size:12.5px;
            color:var(--muted);
            font-weight:500;
        }

        h1.title{
            font-family:var(--font-display);
            font-size:24px;
            font-weight:800;
            letter-spacing:-.01em;
            margin:10px 0 5px;
        }
        .lead{
            font-size:14.5px;
            color:var(--muted);
            margin:0 0 26px;
            line-height:1.5;
        }
        .lead strong{ color:var(--ink); font-weight:600; }

        .field{ margin-bottom:20px; }

        .auth-hint{
            font-size:12.5px;
            color:var(--muted);
            text-align:center;
            margin:0 0 22px;
        }

        .otp-row{
            display:flex;
            gap:10px;
            margin-bottom:8px;
        }
        .otp-box{
            width:100%;
            aspect-ratio:1;
            max-width:64px;
            text-align:center;
            font-family:var(--font-display);
            font-size:22px;
            font-weight:700;
            color:var(--ink);
            border-radius:var(--radius-sm);
            border:1.5px solid var(--line);
            background:var(--mist);
            outline:none;
            transition:border-color .2s var(--ease), background .2s var(--ease), box-shadow .2s var(--ease);
        }
        .otp-box:focus{
            border-color:var(--primary);
            background:#fff;
            box-shadow:0 0 0 4px color-mix(in srgb, var(--primary) 14%, transparent);
        }
        .otp-box.has-error{
            border-color:var(--danger);
            background:#fef2f2;
        }

        .auth-btn{
            width:100%;
            padding:16px 18px;
            border:none;
            border-radius:var(--radius-sm);
            background:linear-gradient(90deg, var(--primary-deep), var(--primary) 55%, var(--primary-light));
            background-size:180% 100%;
            background-position:0% 0%;
            box-shadow:
                0 14px 28px -10px color-mix(in srgb, var(--primary) 55%, transparent),
                inset 0 1px 0 rgba(255,255,255,.25);
            color:#fff;
            font-weight:700;
            font-size:15.5px;
            cursor:pointer;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:9px;
            margin-bottom:16px;
            transition:background-position .5s var(--ease), transform .18s var(--ease), box-shadow .3s var(--ease);
        }
        .auth-btn:hover{
            background-position:100% 0%;
            transform:translateY(-2px);
            box-shadow:0 18px 34px -10px color-mix(in srgb, var(--primary) 60%, transparent);
        }
        .auth-btn:active{ transform:translateY(0); }
        .auth-btn svg{ width:17px; height:17px; transition:transform .3s var(--ease); }
        .auth-btn:hover svg{ transform:translateX(4px); }
        .auth-btn:focus-visible{ outline:2px solid var(--primary-deep); outline-offset:3px; }

        .alert{
            padding:12px 14px;
            border-radius:var(--radius-sm);
            margin-bottom:20px;
            font-size:.86rem;
            display:flex;
            align-items:center;
            gap:9px;
            border-left:4px solid;
        }
        .alert-danger{
            background:#fef2f2;
            color:#991b1b;
            border-color:var(--danger);
        }

        .divider{
            display:flex; align-items:center; gap:10px;
            margin:4px 0 16px;
            color:var(--muted);
            font-size:12.5px;
        }
        .divider::before, .divider::after{
            content:""; flex:1; height:1px; background:var(--line);
        }

        .auth-logout{
            width:100%;
            padding:14px 16px;
            background:var(--mist);
            border:1.5px solid var(--line);
            border-radius:var(--radius-sm);
            font-size:13.5px;
            font-weight:600;
            color:var(--muted);
            cursor:pointer;
            font-family:inherit;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            transition:background .2s var(--ease), border-color .2s var(--ease), color .2s var(--ease);
        }
        .auth-logout:hover{
            background:#fef2f2;
            border-color:#fecaca;
            color:#991b1b;
        }

        @media (max-width: 980px){
            .page{ grid-template-columns:1fr; height:100vh; overflow:hidden; }
            .panel-visual{ display:none; }
            .panel-form{ padding:28px 20px; background:var(--paper); height:100vh; }
            .card{ border:none; box-shadow:none; padding:0; }
        }
        @media (max-height: 760px) and (min-width: 981px){
            .panel-visual{ padding:32px 44px; }
            .logo-card{ width:180px; padding:22px 18px; }
        }
        @media (prefers-reduced-motion: reduce){
            *{ animation-duration:.01ms !important; animation-iteration-count:1 !important; transition-duration:.01ms !important; }
        }
    </style>
</head>
<body>

<div class="page">

    <!-- ============ GAUCHE : PANNEAU SOMBRE + SIGNATURE ============ -->
    <div class="panel-visual">
        <div class="ms-blob ms-blob--1"></div>
        <div class="ms-blob ms-blob--2"></div>

        <div class="visual-top">
            <div class="eyebrow">SIRH · Plénitude Groupe</div>
            <h1>Votre sécurité, <span>notre priorité</span></h1>
            <p>Un dernier code pour confirmer que c'est bien vous avant d'accéder à votre espace RH.</p>
        </div>

        <div class="signature-zone">
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

            <div class="signature-svg-wrap">
                <svg class="signature-svg" viewBox="0 0 300 86" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="pulseGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" style="stop-color:var(--primary-light)"/>
                            <stop offset="100%" style="stop-color:var(--teal)"/>
                        </linearGradient>
                    </defs>
                    <!-- rythme ECG se transformant en organigramme -->
                    <path class="ms-pulse-path" d="
                        M0,60 L20,60 L28,60 L34,38 L42,74 L50,20 L58,60 L78,60
                        C 92,60 96,60 110,60
                        L 128,60 L 128,32
                        M 128,32 L 96,15
                        M 128,32 L 128,10
                        M 128,32 L 160,15
                        L 96,15 L 96,2
                        L 128,10 L 128,-2
                        L 160,15 L 160,2
                    "/>
                    <g class="ms-node" style="animation-delay:2s"><circle cx="128" cy="32" r="4.5" style="fill:#0F0C30; stroke:var(--primary-light); stroke-width:1.6"/></g>
                    <g class="ms-node" style="animation-delay:2.15s"><circle cx="96" cy="2" r="3.6" style="fill:var(--teal); stroke:var(--primary-light); stroke-width:1.2"/></g>
                    <g class="ms-node" style="animation-delay:2.25s"><circle cx="128" cy="-2" r="3.6" style="fill:var(--teal); stroke:var(--primary-light); stroke-width:1.2"/></g>
                    <g class="ms-node" style="animation-delay:2.35s"><circle cx="160" cy="2" r="3.6" style="fill:var(--teal); stroke:var(--primary-light); stroke-width:1.2"/></g>
                </svg>
            </div>
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

    <!-- ============ DROITE : CODE DE VÉRIFICATION ============ -->
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

            <h1 class="title">Vérification</h1>
            <p class="lead">Entrez le code de vérification à 6 chiffres.</p>

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

                <div class="otp-row">
                    @for ($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            class="otp-box otp-digit {{ $errors->has('code') ? 'has-error' : '' }}"
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
                    Vérifier mon identité
                    <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </form>

            <div class="divider"><span>ou</span></div>

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
    </div>

</div>

<script>
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

        logoWrap.addEventListener('mousemove', function(e){
            applyTilt(e.clientX, e.clientY);
        });
        logoWrap.addEventListener('mouseleave', resetTilt);

        logoWrap.addEventListener('touchmove', function(e){
            if(e.touches && e.touches[0]){
                applyTilt(e.touches[0].clientX, e.touches[0].clientY);
            }
        }, { passive: true });
        logoWrap.addEventListener('touchend', resetTilt);
    }

    // ============ CODE DE VÉRIFICATION : saisie, effacement, collage, assemblage ============
    (function(){
        var otpForm   = document.getElementById('otp-form');
        var otpHidden = document.getElementById('otp-hidden');
        var otpBoxes  = Array.prototype.slice.call(document.querySelectorAll('.otp-digit'));

        if(!otpForm) return;

        // Assemble les 6 cases dans le champ caché avant la soumission réelle
        // vers route('2fa.verify')
        otpForm.addEventListener('submit', function(){
            if(otpHidden){
                otpHidden.value = otpBoxes.map(function(b){ return b.value; }).join('');
            }
        });

        otpBoxes.forEach(function(input, idx){
            input.addEventListener('input', function(){
                input.value = input.value.replace(/[^0-9]/g, '').slice(0, 1);
                if(input.value && otpBoxes[idx + 1]){
                    otpBoxes[idx + 1].focus();
                }
            });
            input.addEventListener('keydown', function(e){
                if(e.key === 'Backspace' && !input.value && otpBoxes[idx - 1]){
                    otpBoxes[idx - 1].focus();
                }
            });
            input.addEventListener('paste', function(e){
                e.preventDefault();
                var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                pasted.split('').forEach(function(ch, i){
                    if(otpBoxes[i]) otpBoxes[i].value = ch;
                });
                var next = otpBoxes[Math.min(pasted.length, otpBoxes.length - 1)];
                if(next) next.focus();
            });
        });
    })();
</script>

</body>
</html>
