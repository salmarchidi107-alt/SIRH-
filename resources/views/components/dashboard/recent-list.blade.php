<div {{ $attributes->merge(['class' => 'recent-list bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden']) }}>
    <div class="header bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-5 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>
            @isset($viewAllUrl)
                <a href="{{ $viewAllUrl }}" class="text-indigo-600 hover:text-indigo-500 text-sm font-semibold">Voir tout →</a>
            @endisset
        </div>
    </div>
    <div class="body p-6 divide-y divide-gray-100">
        @forelse($items as $item)
            <div class="item py-4 hover:bg-gray-50 transition px-2 rounded-lg">
                {{ $item }}
            </div>
        @empty
            <div class="text-center py-12 text-gray-500">
                <div class="text-3xl mb-2">{{ $emptyIcon ?? '📭' }}</div>
                <p>{{ $emptyText ?? 'Aucun élément disponible' }}</p>
            </div>
        @endforelse
    </div>
</div>

