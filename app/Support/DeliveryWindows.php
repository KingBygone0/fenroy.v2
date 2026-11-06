<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Delivery windows and the day each one falls on.
 * A window is offered for today only until CUTOFF_HOURS before it ends;
 * after that the same window is offered for tomorrow.
 */
class DeliveryWindows
{
    public const TIMEZONE     = 'Africa/Accra';
    public const CUTOFF_HOURS = 2;

    // key => [start hour, end hour, label]
    private const WINDOWS = [
        'morning'   => [8, 12, '8am – 12pm'],
        'afternoon' => [12, 16, '12pm – 4pm'],
        'evening'   => [16, 20, '4pm – 8pm'],
    ];

    public static function keys(): array
    {
        return array_keys(self::WINDOWS);
    }

    /** @return array<string, array{day: string, time: string, label: string}> */
    public static function all(?Carbon $now = null): array
    {
        $out = [];
        foreach (array_keys(self::WINDOWS) as $key) {
            $out[$key] = self::describe($key, $now);
        }
        return $out;
    }

    /** @return array{day: string, time: string, label: string} */
    public static function describe(string $key, ?Carbon $now = null): array
    {
        [, $end, $time] = self::WINDOWS[$key] ?? self::WINDOWS['morning'];
        $now = ($now ?? Carbon::now())->copy()->setTimezone(self::TIMEZONE);

        $day = $now->hour < ($end - self::CUTOFF_HOURS) ? 'Today' : 'Tomorrow';

        return ['day' => $day, 'time' => $time, 'label' => "{$day}, {$time}"];
    }

    public static function label(string $key, ?Carbon $now = null): string
    {
        return self::describe($key, $now)['label'];
    }

    /** First window that can still be delivered today, or morning (tomorrow). */
    public static function defaultKey(?Carbon $now = null): string
    {
        foreach (self::all($now) as $key => $w) {
            if ($w['day'] === 'Today') return $key;
        }
        return 'morning';
    }

    public static function substitutionLabel(?string $key): ?string
    {
        return match ($key) {
            'call'  => 'Call me first',
            'allow' => 'Allow substitutions',
            'none'  => 'No substitutions',
            default => null,
        };
    }
}
