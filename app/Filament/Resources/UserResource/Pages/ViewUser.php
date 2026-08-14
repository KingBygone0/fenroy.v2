<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;

class ViewUser extends Page
{
    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.view-user';

    public User $record;

    public function mount(int|string $record): void
    {
        $this->record = User::findOrFail($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->record($this->record),
        ];
    }

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function getOrdersHtml(): HtmlString
    {
        $orders = Order::where('customer_email', $this->record->email)
            ->orderByDesc('created_at')
            ->paginate(10);

        if ($orders->isEmpty()) {
            return new HtmlString('<p style="color:#6b7280;font-size:14px;padding:12px 0;">No orders yet.</p>');
        }

        $statusColor = [
            'processing'       => '#f59e0b',
            'picking'          => '#3b82f6',
            'packed'           => '#3b82f6',
            'out-for-delivery' => '#6366f1',
            'delivered'        => '#16a34a',
            'cancelled'        => '#ef4444',
            'refunded'         => '#6b7280',
        ];

        $rows = '';
        foreach ($orders as $o) {
            $color = $statusColor[$o->status] ?? '#6b7280';
            $payColor = $o->payment_status === 'paid' ? '#16a34a' : ($o->payment_status === 'failed' ? '#ef4444' : '#f59e0b');
            $rows .= '<tr>'
                . '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:14px;font-weight:600;">' . htmlspecialchars($o->order_number) . '</td>'
                . '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:14px;"><span style="background:' . $color . '1a;color:' . $color . ';padding:2px 8px;border-radius:9999px;font-size:12px;font-weight:600;">' . ucfirst(str_replace('-', ' ', $o->status)) . '</span></td>'
                . '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:14px;"><span style="background:' . $payColor . '1a;color:' . $payColor . ';padding:2px 8px;border-radius:9999px;font-size:12px;font-weight:600;">' . ucfirst($o->payment_status) . '</span></td>'
                . '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:14px;font-weight:600;">GH₵ ' . number_format($o->total, 2) . '</td>'
                . '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:14px;color:#6b7280;">' . $o->created_at->format('d M Y, H:i') . '</td>'
                . '</tr>';
        }

        $html = '<table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">'
            . '<thead><tr style="background:#f9fafb;">'
            . '<th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Order #</th>'
            . '<th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Status</th>'
            . '<th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Payment</th>'
            . '<th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Total</th>'
            . '<th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Date</th>'
            . '</tr></thead><tbody>' . $rows . '</tbody></table>';

        return new HtmlString($html);
    }

    public function getStats(): array
    {
        $baseQuery = Order::where('customer_email', $this->record->email);

        return [
            'total_orders' => $baseQuery->count(),
            'total_spent'  => (clone $baseQuery)->where('payment_status', 'paid')->sum('total'),
        ];
    }
}
