<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|\UnitEnum|null $navigationGroup = 'Commerce';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) Order::whereIn('status', ['processing', 'picking'])->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('order_number')->disabled()->columnSpan(1),
            Select::make('status')
                ->options([
                    'processing'       => 'Processing',
                    'picking'          => 'Picking',
                    'packed'           => 'Packed',
                    'out-for-delivery' => 'Out for Delivery',
                    'delivered'        => 'Delivered',
                    'cancelled'        => 'Cancelled',
                    'refunded'         => 'Refunded',
                ])->required()->columnSpan(1),
            TextInput::make('customer_name')->required()->columnSpan(1),
            TextInput::make('customer_email')->email()->columnSpan(1),
            TextInput::make('customer_phone')->tel()->columnSpan(1),
            Select::make('payment_status')
                ->options([
                    'pending' => 'Pending',
                    'paid'    => 'Paid',
                    'failed'  => 'Failed',
                ])->columnSpan(1),
            TextInput::make('delivery_address')->columnSpan(2),
            TextInput::make('total')->numeric()->prefix('GH₵')->disabled()->columnSpan(1),
            TextInput::make('delivery_fee')->numeric()->prefix('GH₵')->disabled()->columnSpan(1),
            Textarea::make('notes')->columnSpan(2)->rows(3),

            Placeholder::make('items_display')
                ->label('Order Items')
                ->columnSpan(2)
                ->content(function (?Order $record): HtmlString {
                    if (! $record || ! $record->items) {
                        return new HtmlString('<p style="color:#6b7280; font-size:14px;">No items recorded.</p>');
                    }

                    $rows = '';
                    $subtotal = 0;
                    foreach ($record->items as $item) {
                        $qty   = $item['quantity'] ?? 1;
                        $price = $item['price'] ?? 0;
                        $line  = $qty * $price;
                        $subtotal += $line;
                        $rows .= '<tr>'
                            . '<td style="padding:10px 12px; border-bottom:1px solid #f3f4f6; font-size:14px;">' . htmlspecialchars($item['name'] ?? '') . '</td>'
                            . '<td style="padding:10px 12px; border-bottom:1px solid #f3f4f6; font-size:14px; text-align:center;">' . $qty . '</td>'
                            . '<td style="padding:10px 12px; border-bottom:1px solid #f3f4f6; font-size:14px; text-align:right;">GH₵ ' . number_format($price, 2) . '</td>'
                            . '<td style="padding:10px 12px; border-bottom:1px solid #f3f4f6; font-size:14px; text-align:right; font-weight:600;">GH₵ ' . number_format($line, 2) . '</td>'
                            . '</tr>';
                    }

                    $deliveryFee = $record->delivery_fee ?? 0;
                    $discount    = $record->discount ?? 0;

                    $html = '<table style="width:100%; border-collapse:collapse; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">'
                        . '<thead><tr style="background:#f9fafb;">'
                        . '<th style="padding:10px 12px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Product</th>'
                        . '<th style="padding:10px 12px; text-align:center; font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Qty</th>'
                        . '<th style="padding:10px 12px; text-align:right; font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Unit Price</th>'
                        . '<th style="padding:10px 12px; text-align:right; font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Subtotal</th>'
                        . '</tr></thead><tbody>' . $rows . '</tbody>'
                        . '<tfoot>'
                        . ($deliveryFee > 0 ? '<tr><td colspan="3" style="padding:8px 12px; text-align:right; font-size:13px; color:#6b7280;">Delivery fee</td><td style="padding:8px 12px; text-align:right; font-size:13px;">GH₵ ' . number_format($deliveryFee, 2) . '</td></tr>' : '')
                        . ($discount > 0 ? '<tr><td colspan="3" style="padding:8px 12px; text-align:right; font-size:13px; color:#16a34a;">Discount</td><td style="padding:8px 12px; text-align:right; font-size:13px; color:#16a34a;">- GH₵ ' . number_format($discount, 2) . '</td></tr>' : '')
                        . '<tr style="background:#f9fafb;"><td colspan="3" style="padding:10px 12px; text-align:right; font-size:14px; font-weight:700;">Total</td><td style="padding:10px 12px; text-align:right; font-size:14px; font-weight:700;">GH₵ ' . number_format($record->total ?? 0, 2) . '</td></tr>'
                        . '</tfoot></table>';

                    return new HtmlString($html);
                }),

        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->searchable()->sortable()->copyable()->weight('bold'),
                TextColumn::make('customer_name')->searchable()->sortable(),
                TextColumn::make('customer_phone')->searchable(),
                TextColumn::make('total')
                    ->formatStateUsing(fn ($state) => 'GH₵ ' . number_format($state, 2))
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'processing'       => 'warning',
                        'picking'          => 'info',
                        'packed'           => 'info',
                        'out-for-delivery' => 'primary',
                        'delivered'        => 'success',
                        'cancelled'        => 'danger',
                        'refunded'         => 'gray',
                        default            => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'out-for-delivery' => 'Out for Delivery',
                        default            => ucfirst($state),
                    }),
                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'    => 'success',
                        'failed'  => 'danger',
                        default   => 'warning',
                    }),
                TextColumn::make('created_at')->since()->sortable()->label('Placed'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'processing'       => 'Processing',
                    'picking'          => 'Picking',
                    'packed'           => 'Packed',
                    'out-for-delivery' => 'Out for Delivery',
                    'delivered'        => 'Delivered',
                    'cancelled'        => 'Cancelled',
                    'refunded'         => 'Refunded',
                ]),
                SelectFilter::make('payment_status')->options([
                    'pending' => 'Pending',
                    'paid'    => 'Paid',
                    'failed'  => 'Failed',
                ]),
            ])
            ->actions([
                Action::make('advance')
                    ->label('Advance Status')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->color('success')
                    ->visible(fn (Order $record) => ! in_array($record->status, ['delivered', 'cancelled', 'refunded']))
                    ->action(function (Order $record) {
                        $next = [
                            'processing'       => 'picking',
                            'picking'          => 'packed',
                            'packed'           => 'out-for-delivery',
                            'out-for-delivery' => 'delivered',
                        ];
                        if (isset($next[$record->status])) {
                            $record->update(['status' => $next[$record->status]]);
                            Notification::make()->title('Status updated to ' . ucfirst($next[$record->status]))->success()->send();
                        }
                    }),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
