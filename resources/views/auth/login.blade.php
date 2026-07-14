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

    <style>
        :root {
            /* Couleur du tenant (dynamique) */
            --primary: {{ $tenantData['brand_color'] ?? '#0f6b7c' }};
            --primary-light: color-mix(in srgb, var(--primary) 65%, white);
            --primary-deep: color-mix(in srgb, var(--primary) 75%, black);
            --primary-tint: color-mix(in srgb, var(--primary) 10%, white);
            --primary-glow: color-mix(in srgb, var(--primary) 45%, transparent);

            /* Identité visuelle du panneau (fixe) */
            --navy-deep: #1B1747;
            --navy: #2C2670;
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
                linear-gradient(170deg, var(--navy-deep) 0%, #171340 100%);
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

        .visual-top-row{
            position:relative;
            z-index:1;
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:26px;
        }
        .visual-top{ flex:1; min-width:0; }
        .signature-zone{
            position:relative;
            z-index:1;
            flex:1;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:10px;
        }
        .visual-bottom{ position:relative; z-index:1; }

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
            font-size:18px;
            line-height:1.6;
            color:#C6C5EA;
            max-width:34ch;
            margin:0;
        }

        /* ---- logo (haut) + scène RH animée (centrée) ---- */
        .logo-wrap{
            position:relative;
            display:flex;
            align-items:center;
            justify-content:center;
            perspective:900px;
            animation:idleFloat 6s ease-in-out infinite;
            cursor:pointer;
            flex-shrink:0;
        }
        @keyframes idleFloat{
            0%,100%{ transform:translateY(0); }
            50%{ transform:translateY(-9px); }
        }
        .logo-wrap::before{
            content:"";
            position:absolute;
            width:210px; height:210px;
            border-radius:50%;
            background:radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            filter:blur(8px);
            z-index:0;
        }
        .logo-card{
            position:relative;
            z-index:1;
            width:175px;
            border-radius:var(--radius-lg);
            background:linear-gradient(160deg,#ffffff, #EEEFF8);
            box-shadow:
                0 30px 60px -22px rgba(0,0,0,.5),
                0 0 0 1px rgba(255,255,255,.08);
            padding:20px 17px;
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
        .logo-card img{ max-width:200px; max-height:40px; object-fit:contain; }
        .logo-card .bar{ width:100%; height:4px; border-radius:3px; background:var(--line); overflow:hidden; }
        .logo-card .bar span{ display:block; height:100%; width:74%; background:linear-gradient(90deg, var(--primary), var(--primary-light)); }

        /* ---- scène RH animée : un collaborateur qui classe les documents à son bureau ---- */
        .hr-scene-wrap{
            width:100%;
            max-width:440px;
            height:220px;
            position:relative;
            perspective:900px;
            cursor:pointer;
        }
        .hr-scene-inner{
            width:100%; height:100%;
            transform-style:preserve-3d;
            transition:transform .5s var(--ease);
            will-change:transform;
        }
        .hr-scene-svg{ width:100%; height:100%; overflow:visible; display:block; }

        /* halo ambiant sous le bureau */
        .hr-floor-glow{
            transform-origin:center;
            animation:floorPulse 4.5s ease-in-out infinite;
        }
        @keyframes floorPulse{
            0%,100%{ opacity:.28; transform:scaleX(1); }
            50%{ opacity:.45; transform:scaleX(1.06); }
        }

        /* écran de l'ordinateur : lignes qui "s'écrivent" en boucle */
        .hr-type-line{
            transform-origin:left center;
            animation:typeLine 2.6s ease-in-out infinite;
        }
        .hr-type-line--2{ animation-delay:.35s; }
        .hr-type-line--3{ animation-delay:.7s; }
        @keyframes typeLine{
            0%{ transform:scaleX(0); opacity:.25; }
            35%{ transform:scaleX(1); opacity:1; }
            75%{ transform:scaleX(1); opacity:1; }
            92%,100%{ transform:scaleX(0); opacity:.25; }
        }
        .hr-screen-glow{
            animation:screenGlow 2.6s ease-in-out infinite;
        }
        @keyframes screenGlow{
            0%,100%{ opacity:.5; }
            50%{ opacity:.9; }
        }

        /* bras du collaborateur : geste répété pour ranger les feuilles */
        .hr-arm{
            transform-origin:198px 84px;
            animation:armSweep 1.75s ease-in-out infinite;
        }
        @keyframes armSweep{
            0%,100%{ transform:rotate(-6deg); }
            50%{ transform:rotate(16deg); }
        }

        /* tête : léger hochement de concentration */
        .hr-head{
            transform-origin:210px 62px;
            animation:headNod 3.5s ease-in-out infinite;
        }
        @keyframes headNod{
            0%,100%{ transform:rotate(0deg); }
            50%{ transform:rotate(-3deg); }
        }

        /* feuilles volantes qui arrivent en désordre et se rangent en pile,
           chacune en boucle décalée pour un flux continu */
        .hr-paper{ opacity:0; }
        .hr-paper-1{ animation:paperFly1 6.8s var(--ease) infinite; }
        .hr-paper-2{ animation:paperFly2 6.8s var(--ease) infinite; animation-delay:1.7s; }
        .hr-paper-3{ animation:paperFly3 6.8s var(--ease) infinite; animation-delay:3.4s; }
        .hr-paper-4{ animation:paperFly4 6.8s var(--ease) infinite; animation-delay:5.1s; }

        @keyframes paperFly1{
            0%{ transform:translate(20px,10px) rotate(-30deg); opacity:0; }
            8%{ opacity:1; }
            48%{ transform:translate(58px,68px) rotate(-14deg); opacity:1; }
            66%{ transform:translate(88px,100px) rotate(-7deg); opacity:1; }
            76%{ transform:translate(93px,106px) rotate(-4deg); }
            94%{ transform:translate(93px,106px) rotate(-4deg); opacity:1; }
            100%{ transform:translate(93px,106px) rotate(-4deg); opacity:0; }
        }
        @keyframes paperFly2{
            0%{ transform:translate(252px,14px) rotate(24deg); opacity:0; }
            8%{ opacity:1; }
            48%{ transform:translate(168px,54px) rotate(12deg); opacity:1; }
            66%{ transform:translate(112px,95px) rotate(6deg); }
            76%{ transform:translate(99px,110px) rotate(3deg); }
            94%{ transform:translate(99px,110px) rotate(3deg); opacity:1; }
            100%{ transform:translate(99px,110px) rotate(3deg); opacity:0; }
        }
        @keyframes paperFly3{
            0%{ transform:translate(14px,92px) rotate(-18deg); opacity:0; }
            8%{ opacity:1; }
            48%{ transform:translate(52px,96px) rotate(-9deg); opacity:1; }
            66%{ transform:translate(80px,108px) rotate(-4deg); }
            76%{ transform:translate(91px,109px) rotate(-2deg); }
            94%{ transform:translate(91px,109px) rotate(-2deg); opacity:1; }
            100%{ transform:translate(91px,109px) rotate(-2deg); opacity:0; }
        }
        @keyframes paperFly4{
            0%{ transform:translate(268px,96px) rotate(20deg); opacity:0; }
            8%{ opacity:1; }
            48%{ transform:translate(178px,100px) rotate(10deg); opacity:1; }
            66%{ transform:translate(114px,102px) rotate(4deg); }
            76%{ transform:translate(97px,105px) rotate(1deg); }
            94%{ transform:translate(97px,105px) rotate(1deg); opacity:1; }
            100%{ transform:translate(97px,105px) rotate(1deg); opacity:0; }
        }

        .hr-scene-caption{
            font-family:var(--font-mono);
            font-size:10.5px;
            letter-spacing:.08em;
            text-transform:uppercase;
            color:#B9B8E4;
            text-align:center;
            margin-top:2px;
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

        /* ============ DROITE — FORMULAIRE (agrandi, premium) ============ */
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
            max-width:520px;
            background:transparent;
            border:none;
            border-radius:0;
            box-shadow:none;
            padding:0;
            opacity:0;
            transform:translateY(16px);
            animation:formRise .8s var(--ease) forwards .15s;
        }
        @keyframes formRise{ from{ opacity:0; transform:translateY(16px);} to{ opacity:1; transform:translateY(0);} }

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

        .field{ margin-bottom:20px; }
        .field label{
            display:flex;
            align-items:center;
            gap:8px;
            font-size:13px;
            font-weight:600;
            color:var(--ink);
            margin-bottom:8px;
        }
        .field label svg{
            width:16px; height:16px;
            color:var(--muted);
            flex-shrink:0;
        }
        .input-wrap{
            position:relative;
            display:flex;
            align-items:center;
            border-radius:var(--radius-sm);
            border:1.5px solid var(--line);
            background:var(--mist);
            transition:border-color .22s var(--ease), background .22s var(--ease), box-shadow .22s var(--ease);
        }
        .input-icon{ display:none; }
        .input-wrap input{
            flex:1;
            min-width:0;
            width:100%;
            padding:18px 16px;
            border:none;
            outline:none;
            background:transparent;
            font-size:16px;
            font-family:var(--font-body);
            color:var(--ink);
        }
        .input-wrap input::placeholder{ color:#AFAFCB; }
        .input-wrap:hover{ border-color:#c9cadf; }
        .input-wrap.is-focused{
            border-color:var(--primary);
            background:#fff;
            box-shadow:0 0 0 4px color-mix(in srgb, var(--primary) 14%, transparent);
        }
        .input-wrap.is-focused .input-icon{ color:var(--primary); }

        /* masque l'icône native du navigateur (contacts/mot de passe) qui se
           superpose sinon à notre propre icône et casse l'affichage */
        .input-wrap input::-webkit-contacts-auto-fill-button,
        .input-wrap input::-webkit-credentials-auto-fill-button,
        .input-wrap input::-webkit-caps-lock-indicator{
            visibility:hidden;
            display:none !important;
            pointer-events:none;
            position:absolute;
            right:0;
            width:0; height:0;
            margin:0;
        }
        .input-wrap input:-webkit-autofill,
        .input-wrap input:-webkit-autofill:hover,
        .input-wrap input:-webkit-autofill:focus{
            -webkit-text-fill-color:var(--ink);
            transition:background-color 9999s ease-in-out 0s;
            box-shadow:0 0 0 50px var(--mist) inset;
        }
        .input-wrap.is-focused input:-webkit-autofill{
            box-shadow:0 0 0 50px #fff inset;
        }



        .row-between{
            display:flex; align-items:center; justify-content:space-between;
            margin:0 0 26px;
            font-size:13.5px;
        }
        .remember{ display:flex; align-items:center; gap:9px; color:var(--ink); cursor:pointer; user-select:none; }
        .remember input{ width:16px; height:16px; accent-color:var(--primary); cursor:pointer; }
        .forgot{ color:var(--primary); text-decoration:none; font-weight:600; position:relative; }
        .forgot::after{
            content:''; position:absolute; left:0; bottom:-2px; width:0; height:1.5px;
            background:var(--primary); transition:width .25s var(--ease);
        }
        .forgot:hover::after{ width:100%; }

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
            margin:22px 0 16px;
        }
        .divider::before, .divider::after{
            content:""; flex:1; height:1px; background:var(--line);
        }

        .powered{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            font-size:12px;
            color:var(--muted);
            text-align:center;
        }
        .powered img{ height:16px; width:auto; object-fit:contain; }
        .powered a{ color:var(--primary); text-decoration:none; font-weight:600; }
        .powered a:hover{ text-decoration:underline; }

        @media (max-width: 980px){
            .page{ grid-template-columns:1fr; height:100vh; overflow:hidden; }
            .panel-visual{ display:none; }
            .panel-form{ padding:28px 20px; background:var(--paper); height:100vh; }
            .card{ border:none; box-shadow:none; padding:0; }
        }
        @media (max-height: 760px) and (min-width: 981px){
            .panel-visual{ padding:32px 44px; }
            .logo-card{ width:120px; padding:14px 12px; }
            .hr-scene-wrap{ max-width:300px; height:150px; }
            .card{ padding:32px 32px 24px; }
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
