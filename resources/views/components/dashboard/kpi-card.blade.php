<div {{ $attributes->merge(['class' => 'kpi-card bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-all duration-300 overflow-hidden']) }}>
    <div class="flex items-center justify-between mb-4">
        <div class="kpi-label font-semibold text-sm text-gray-600 uppercase tracking-wide">{{ $label }}</div>
        @isset($icon)
            <div class="kpi-icon text-2xl" style="color: {{ $iconColor ?? '#3b82f6' }}">{{ $icon }}</div>
        @endisset
    </div>
    <div class="kpi-value text-4xl font-bold mb-2" style="color: {{ $valueColor ?? '#111827' }}">{{ $value }}</div>
    @isset($unit)
        <div class="kpi-unit text-2xl font-semibold text-gray-500">{{ $unit }}</div>
    @endisset
    @isset($change)
        <div class="kpi-change mt-3 text-sm font-medium {{ $change > 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $change > 0 ? '+' : '' }}{{ $change }}% ce mois
        </div>
    @endisset
</div>

