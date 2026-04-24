<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Connexion - <?php echo e($name ?? 'HospitalRH'); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <style>
        :root {
            --primary: <?php echo e($tenantData['brand_color'] ?? '#0f6b7c'); ?>;
            --primary-light: #1a8fa5;
            --accent: #00c9a7;
            --navy: #0d2137;
            --bg: #f4f7fa;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px -3px rgba(0,0,0,.08), 0 4px 6px -2px rgba(0,0,0,.04);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .auth-header {
            background: linear-gradient(135deg, var(--navy) 0%, var(--primary) 100%);
            padding: 32px 24px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .auth-header::before {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 120px;
            height: 120px;
            background: rgba(255,255,255,.04);
            border-radius: 50%;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .auth-brand-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: white;
            box-shadow: 0 4px 12px rgba(0, 201, 167, 0.35);
            overflow: hidden;
        }

        .auth-logo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .auth-brand-name {
            font-family: 'Syne', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            letter-spacing: -0.3px;
        }

        .auth-subtitle {
            color: rgba(255,255,255,.6);
            font-size: 0.875rem;
            margin-top: 8px;
        }

        .auth-body {
            padding: 32px 28px;
        }

        .auth-form-group {
            margin-bottom: 20px;
        }

        .auth-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .auth-input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #0f172a;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .auth-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 107, 124, 0.1);
        }

        .auth-input::placeholder {
            color: #94a3b8;
        }

        .auth-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .auth-checkbox input {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
        }

        .auth-checkbox label {
            font-size: 0.8rem;
            color: #64748b;
            cursor: pointer;
        }

        .auth-btn {
            width: 100%;
            padding: 12px 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(15, 107, 124, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .auth-btn:hover {
            box-shadow: 0 6px 18px rgba(15, 107, 124, 0.4);
            transform: translateY(-1px);
        }

        .auth-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            margin-top: 8px;
        }

        .auth-footer p {
            font-size: 0.85rem;
            color: #64748b;
            margin: 0;
        }

        .auth-footer a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-brand">
                    <div class="auth-brand-icon">
                        <?php if(isset($tenantData['logo_path']) && $tenantData['logo_path']): ?>
                            <img src="<?php echo e(asset('storage/' . $tenantData['logo_path'])); ?>" alt="<?php echo e($tenantData['name'] ?? 'Login'); ?>" class="auth-logo-img">
                        <?php else: ?>
                            <img src="<?php echo e(asset('images/login-icon.svg')); ?>" width="36">
                        <?php endif; ?>
                    </div>
                    <div class="auth-brand-name"><?php echo e($tenantData['name'] ?? 'Login'); ?></div>
                </div>
                <p class="auth-subtitle">Connexion à votre compte</p>
            </div>

            <div class="auth-body">
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        <span>Email ou mot de passe incorrect</span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('login')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="auth-form-group">
                        <label for="email" class="auth-label">Adresse email</label>
                        <input
                            type="email"
                            class="auth-input"
                            id="email"
                            name="email"
                            value="<?php echo e(old('email')); ?>"
                            placeholder="vous@exemple.com"
                            required
                            autofocus
                        >
                    </div>

                    <div class="auth-form-group">
                        <label for="password" class="auth-label">Mot de passe</label>
                        <input
                            type="password"
                            class="auth-input"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required
                        >
                    </div>

                    <div class="auth-checkbox">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Se souvenir de moi</label>
                    </div>

                    <button type="submit" class="auth-btn">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        Connexion
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\Projects\HospitalRh\resources\views/auth/login.blade.php ENDPATH**/ ?>