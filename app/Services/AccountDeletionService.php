<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\LoginActivity;
use App\Models\Order;
use App\Models\PendingOrder;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Deletes a customer account (required by Google Play and the App Store).
 * Personal details are removed; orders are kept for accounting with the
 * customer's name, email, phone and address anonymised.
 */
class AccountDeletionService
{
    /** Orders in these states are still being fulfilled, so deletion waits. */
    public const ACTIVE_STATUSES = ['pending', 'processing', 'picking', 'packed', 'out-for-delivery'];

    public function activeOrderCount(User $user): int
    {
        return $this->ordersFor($user)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->where('payment_status', 'paid')
            ->count();
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->ordersFor($user)->update([
                'user_id'          => null,
                'customer_name'    => 'Deleted customer',
                'customer_email'   => null,
                'customer_phone'   => null,
                'delivery_address' => null,
            ]);

            Review::where('user_id', $user->id)->delete();
            Cart::where('user_id', $user->id)->delete();
            PendingOrder::where('user_id', $user->id)->delete();
            LoginActivity::where('user_id', $user->id)->delete();
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
            $user->tokens()->delete();

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Addresses and wishlist rows cascade on delete.
            $user->delete();
        });
    }

    private function ordersFor(User $user)
    {
        return Order::query()->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('customer_email', $user->email);
        });
    }
}
