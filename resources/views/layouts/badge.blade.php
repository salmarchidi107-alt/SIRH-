{{-- resources/views/layouts/badge.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'HospitalRH — Pointage')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --navy:   #0a1628;
      --teal:   #0d9488;
      --teal2:  #14b8a6;
      --amber:  #f59e0b;
      --red:    #ef4444;
      --green:  #22c55e;
      --white:  #ffffff;
      --glass:  rgba(255,255,255,0.07);
      --border: rgba(255,255,255,0.12);
    }
    html, body {
      height: 100%; width: 100%;
      font-family: 'DM Sans', sans-serif;
      background: var(--navy);
      color: var(--white);
      overflow-x: hidden;
    }
    /* Fond animé */
    body::before {
      content: '';
      position: fixed; inset: 0; z-index: 0;
      background:
        radial-gradient(ellipse 80% 60% at 20% -10%, rgba(13,148,136,.35) 0%, transparent 60%),
        radial-gradient(ellipse 60% 50% at 80% 110%, rgba(245,158,11,.15) 0%, transparent 60%),
        var(--navy);
      pointer-events: none;
    }
    .badge-app {
      position: relative; z-index: 1;
      min-height: 100vh;
      display: flex; flex-direction: column;
    }
    /* Modal fix */
    #choiceModal {
      z-index: 9999 !important;
    }
    #choiceModal > div {
      max-width: 90vw;
      max-height: 90vh;
      overflow: auto;
    }
    @yield('body_content')
  </style>
  @stack('styles')
</head>
<body>
<div class="badge-app">
  @yield('content')
</div>
@stack('scripts')
</body>
</html>