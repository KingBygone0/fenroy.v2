<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $orders = Order::orderByDesc('created_at')->get();
                    return response()->streamDownload(function () use ($orders) {
                        $handle = fopen('php://output', 'w');
                        fputcsv($handle, ['Order #', 'Customer', 'Phone', 'Email', 'Total', 'Delivery Fee', 'Status', 'Payment', 'Address', 'Date']);
                        foreach ($orders as $o) {
                            fputcsv($handle, [
                                $o->order_number,
                                $o->customer_name,
                                $o->customer_phone,
                                $o->customer_email,
                                number_format($o->total, 2),
                                number_format($o->delivery_fee ?? 0, 2),
                                $o->status,
                                $o->payment_status,
                                $o->delivery_address,
                                $o->created_at->format('Y-m-d H:i'),
                            ]);
                        }
                        fclose($handle);
                    }, 'orders-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
                }),
            CreateAction::make(),
        ];
    }
}
