<div class="stat-card bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
    <div class="stat-label font-medium text-sm text-gray-500 uppercase tracking-wide mb-1">{{ $label }}</div>
    <div class="stat-value text-3xl font-bold text-gray-900 mb-1" style="color: {{ $color ?? '#1e293b' }}">{{ $value }}</div>
    @isset($subtitle)
        <div class="stat-subtitle text-sm font-medium" style="color: {{ $subtitleColor ?? '#64748b' }}">{{ $subtitle }}</div>
    @endisset
    @isset($icon)
        <div class="stat-icon mt-2 p-2 bg-gradient-to-r from-{{ $iconBg ?? 'blue' }}-100 to-{{ $iconBg ?? 'blue' }}-200 rounded-lg inline-flex">
            {{ $icon }}
        </div>
    @endisset
</div>

