<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Vérification - <?php echo e($name ?? 'MedStaff'); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">

    
    <style>
        .auth-page {
            --primary: <?php echo e($tenantData['brand_color'] ?? '#0f6b7c'); ?>;
            --primary-light: color-mix(in srgb, var(--primary) 65%, white);
            --primary-deep: color-mix(in srgb, var(--primary) 75%, black);
            --primary-tint: color-mix(in srgb, var(--primary) 10%, white);
            --primary-glow: color-mix(in srgb, var(--primary) 45%, transparent);
        }
    </style>
</head>
<body class="auth-page auth-page-2fa">

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
                    <?php if(isset($tenantData['logo_path']) && $tenantData['logo_path']): ?>
                        <img src="<?php echo e(asset('storage/' . $tenantData['logo_path'])); ?>" alt="<?php echo e($tenantData['name'] ?? 'Logo'); ?>">
                    <?php else: ?>
                        <img src="<?php echo e(asset('images/medstaff-logo.jpeg')); ?>" alt="MedStaff">
                    <?php endif; ?>
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
                <?php if(isset($tenantData['logo_path']) && $tenantData['logo_path']): ?>
                    <img src="<?php echo e(asset('storage/' . $tenantData['logo_path'])); ?>" alt="<?php echo e($tenantData['name'] ?? 'Logo'); ?>" class="brand-logo-img">
                <?php else: ?>
                    <img src="<?php echo e(asset('images/medstaff-logo.jpeg')); ?>" alt="MedStaff" class="brand-logo-img">
                <?php endif; ?>
                <span class="brand-sub"><?php echo e($tenantData['name'] ?? 'HR Solutions'); ?></span>
            </div>

            <h1 class="title">Vérification</h1>
            <p class="lead">Entrez le code de vérification à 6 chiffres.</p>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4M12 16h.01"/>
                    </svg>
                    <span><?php echo e($errors->first()); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('2fa.verify')); ?>" autocomplete="off" id="otp-form">
                <?php echo csrf_field(); ?>

                
                <input type="hidden" name="code" id="otp-hidden">

                <div class="otp-row">
                    <?php for($i = 0; $i < 6; $i++): ?>
                        <input
                            type="text"
                            class="otp-box otp-digit <?php echo e($errors->has('code') ? 'has-error' : ''); ?>"
                            maxlength="1"
                            inputmode="numeric"
                            pattern="[0-9]"
                            data-index="<?php echo e($i); ?>"
                            autocomplete="off"
                            spellcheck="false"
                            <?php echo e($i === 0 ? 'autofocus' : ''); ?>

                        >
                    <?php endfor; ?>
                </div>

                <p class="auth-hint">Saisissez le code attribué par votre administrateur</p>

                <button type="submit" class="auth-btn">
                    Vérifier mon identité
                    <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </form>

            <div class="divider"><span>ou</span></div>

            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
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
<?php /**PATH C:\Users\HP\Medstaff-second-main\resources\views/auth/otp.blade.php ENDPATH**/ ?>