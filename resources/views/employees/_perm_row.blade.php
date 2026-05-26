@php
    $sub = $sub ?? false;
@endphp

<div class="perm-row {{ $sub ? 'perm-row--sub' : '' }}">

    {{-- Nom du module --}}
    <div class="perm-mod-name">
        <span>{!! $label !!}</span>
    </div>

    {{-- Colonnes permissions --}}
    @foreach (['view', 'create', 'edit', 'delete'] as $perm)

        @if(in_array($perm, $actions))
            <div class="perm-cell">
                <input
                    type="checkbox"
                    name="permissions[{{ $key }}][{{ $perm }}]"
                    value="1"
                    {{ old("permissions.$key.$perm") ? 'checked' : '' }}
                >
            </div>
        @else
            <div class="perm-cell">
                <span class="perm-na">—</span>
            </div>
        @endif

    @endforeach

</div>
