{{--
===================================================================
  SNIPPET À AJOUTER dans resources/views/layouts/app.blade.php
  (ou ton fichier de layout / sidebar partiel)

  Cherche la section "TEMPS & PRÉSENCE" dans ton sidebar et
  ajoute l'item "Pointage" juste après "Vue d'ensemble".
===================================================================
--}}

{{-- ── Item Pointage à insérer dans le sidebar ── --}}
<a href="{{ route('pointage.index') }}"
   class="nav-item {{ request()->routeIs('pointage.*') ? 'active' : '' }}">

    {{-- Icône badgeuse / pointage --}}
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
         stroke="currentColor" stroke-width="1.5" style="flex-shrink:0">
        <rect x="2" y="2" width="5" height="12" rx="1"/>
        <rect x="9" y="2" width="5" height="5" rx="1"/>
        <rect x="9" y="9" width="5" height="5" rx="1"/>
    </svg>

    <span>Pointage</span>

    {{-- Badge "live" animé si des pointages du jour sont en attente --}}
    @php
        $enAttente = \App\Models\Pointage::forDate(today()->toDateString())
            ->where('valide', false)
            ->where('statut', 'present')
            ->count();
    @endphp
    @if($enAttente > 0)
    <span style="
        margin-left:auto;
        background:#0d9488;
        color:#fff;
        font-size:10px;
        font-weight:700;
        padding:1px 7px;
        border-radius:20px;
        line-height:1.6;
    ">{{ $enAttente }}</span>
    @endif
</a>

{{--
===================================================================
  EXEMPLE de section complète "TEMPS & PRÉSENCE" pour référence :
===================================================================

<div class="nav-section">Temps & Présence</div>

<a href="{{ route('planning.index') }}"
   class="nav-item {{ request()->routeIs('planning.*') ? 'active' : '' }}">
    <svg ...>...</svg>
    <span>Planning</span>
</a>

<a href="{{ route('vue-ensemble.index') }}"
   class="nav-item {{ request()->routeIs('vue-ensemble.*') ? 'active' : '' }}">
    <svg ...>...</svg>
    <span>Vue d'ensemble</span>
</a>

{{-- ← AJOUTER ICI L'ITEM POINTAGE CI-DESSUS --}}

===================================================================
--}}
