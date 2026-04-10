<section {{ $attributes->merge(['class' => 'metric-section bg-white rounded-3xl shadow-xl border border-gray-100 p-10']) }}>
    <div class="section-header mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
            {{ $title }}
            @isset($subtitle)
                <span class="text-lg text-gray-500 font-normal">{{ $subtitle }}</span>
            @endisset
        </h2>
        @isset($description)
            <p class="text-gray-600">{{ $description }}</p>
        @endisset
    </div>
    <div class="metrics-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ $columns ?? 3 }} gap-8">
        {{ $slot }}
    </div>
</section>

