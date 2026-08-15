<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SmsBalanceWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 7;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $balance = Cache::remember('arkesel_sms_balance', 300, function () {
            try {
                $response = Http::timeout(8)
                    ->withHeaders(['api-key' => config('arkesel.api_key')])
                    ->get('https://sms.arkesel.com/api/v2/clients/balance-details');

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['data']['sms_balance'] ?? ($data['balance'] ?? null);
                }
            } catch (\Throwable) {
            }
            return null;
        });

        $label = $balance !== null ? number_format((float) $balance) . ' SMS units' : 'Unavailable';
        $color = match (true) {
            $balance === null         => 'gray',
            (float) $balance < 50    => 'danger',
            (float) $balance < 200   => 'warning',
            default                  => 'success',
        };

        return [
            Stat::make('SMS Balance (Arkesel)', $label)
                ->description('Refreshes every 5 minutes')
                ->color($color)
                ->icon('heroicon-o-chat-bubble-left-ellipsis'),
        ];
    }
}
