<x-filament-panels::page>
    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-left">
                        <th class="px-4 py-3 font-semibold text-gray-500">Time</th>
                        <th class="px-4 py-3 font-semibold text-gray-500">Email</th>
                        <th class="px-4 py-3 font-semibold text-gray-500">IP Address</th>
                        <th class="px-4 py-3 font-semibold text-gray-500">Result</th>
                        <th class="px-4 py-3 font-semibold text-gray-500">User Agent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($this->activities as $activity)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                            {{ $activity->created_at->format('d M Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3 text-gray-800 font-medium">
                            {{ htmlspecialchars($activity->email, ENT_QUOTES, 'UTF-8') }}
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-600">
                            {{ $activity->ip }}
                        </td>
                        <td class="px-4 py-3">
                            @if($activity->successful)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    Success
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    Failed
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate">
                            {{ $activity->user_agent }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                            No login activity recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
