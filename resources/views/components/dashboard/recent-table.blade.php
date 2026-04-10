<div class="recent-table bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="table-header bg-gray-50 px-6 py-4 border-b border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            {{ $title }}
            @isset($link)
                <a href="{{ $link }}" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium">Voir tout →</a>
            @endisset
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
@foreach((array)$headers as $header)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($items as $item)
                    <tr class="hover:bg-gray-50 transition">
                        {{ $item->renderRow ?? '' }} {{-- Slot for custom row --}}
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) }}" class="px-6 py-12 text-center text-gray-500">
                            {{ $empty ?? 'Aucun élément pour le moment.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

