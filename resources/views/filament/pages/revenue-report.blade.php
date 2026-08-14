<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Filters row --}}
        <div class="flex flex-wrap gap-4 items-end bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">From</label>
                <input type="date" wire:model.live="dateFrom"
                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">To</label>
                <input type="date" wire:model.live="dateTo"
                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Group by</label>
                <select wire:model.live="groupBy"
                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="day">Day</option>
                    <option value="week">Week</option>
                    <option value="month">Month</option>
                </select>
            </div>
            <button wire:click="exportCsv"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export CSV
            </button>
        </div>

        {{-- Stats cards --}}
        @php $totals = $this->getTotals(); @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Revenue</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white">GH₵ {{ number_format($totals['revenue'], 2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Paid Orders</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ number_format($totals['orders']) }}</p>
            </div>
        </div>

        {{-- Data table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Orders</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($this->getData() as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $row->period }}</td>
                        <td class="px-5 py-3 text-right text-gray-600 dark:text-gray-300">{{ $row->orders }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900 dark:text-white">GH₵ {{ number_format($row->revenue, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-5 py-10 text-center text-gray-400 dark:text-gray-500">
                            No paid orders in this period.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-filament-panels::page>
