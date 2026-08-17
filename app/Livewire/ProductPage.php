<?php

namespace App\Livewire;

use App\Models\Product;
use App\Support\ProductImages;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ProductPage extends Component
{
    #[Locked]
    public string $slug;

    #[Locked]
    public array $product = [];

    public int $qty = 1;
    public bool $wishlisted = false;

    public function mount(string $slug): void
    {
        $this->slug    = $slug;
        $this->product = $this->findProduct($slug);

        if (auth()->check()) {
            $this->wishlisted = \App\Models\Wishlist::where('user_id', auth()->id())
                ->whereHas('product', fn ($q) => $q->where('slug', $slug))
                ->exists();
        }
    }

    public function increment(): void
    {
        $this->qty = min(99, $this->qty + 1);
    }

    public function decrement(): void
    {
        $this->qty = max(1, $this->qty - 1);
    }

    public function toggleWishlist(): void
    {
        if (! auth()->check()) {
            $this->js("window.location.href = '" . route('login') . "'");
            return;
        }

        $product = Product::where('slug', $this->slug)->first();
        if (! $product) return;

        if ($this->wishlisted) {
            \App\Models\Wishlist::where('user_id', auth()->id())
                ->where('product_id', $product->id)->delete();
            $this->wishlisted = false;
        } else {
            \App\Models\Wishlist::firstOrCreate([
                'user_id'    => auth()->id(),
                'product_id' => $product->id,
            ]);
            $this->wishlisted = true;
        }
    }

    public function addToCart(): void
    {
        $dbProduct = Product::where('slug', $this->slug)->where('is_active', true)->first();
        if (! $dbProduct) return;

        $cartItems = session('cart_items', []);
        $found = false;
        foreach ($cartItems as &$item) {
            if ($item['slug'] === $this->slug) {
                $item['qty']   += $this->qty;
                $item['price']  = $dbProduct->price;
                $found = true;
                break;
            }
        }
        unset($item);
        if (! $found) {
            $cartItems[] = [
                'name'      => $dbProduct->name,
                'unit'      => $dbProduct->unit ?? '',
                'price'     => $dbProduct->price,
                'old_price' => $dbProduct->old_price ?: null,
                'qty'       => $this->qty,
                'slug'      => $this->slug,
                'image'     => $dbProduct->image_url,
            ];
        }

        $count     = array_sum(array_column($cartItems, 'qty'));
        $cartTotal = array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $cartItems));

        session([
            'cart_items' => $cartItems,
            'cart_count' => $count,
            'cart_total' => $cartTotal,
        ]);
        session()->save();

        $name      = json_encode('✓ Added ' . $this->product['name'] . ' to cart');
        $formatted = number_format($cartTotal, 2);

        $this->js(<<<JS
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: {$name} } }));
            document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = '{$count}');
            document.querySelectorAll('[data-cart-total]').forEach(el => el.textContent = 'GH₳ {$formatted}');
            var mb = document.getElementById('mobile-cart-badge');
            if (mb) { mb.textContent = '{$count}'; mb.style.display = ''; }
        JS);
    }

    #[Computed]
    public function lineTotal(): float
    {
        return $this->qty * ($this->product['price'] ?? 0);
    }

    public function render()
    {
        return view('livewire.product-page', [
            'related'   => $this->relatedProducts(),
            'lineTotal' => $this->lineTotal,
        ]);
    }

    private function findProduct(string $slug): array
    {
        $p = Product::where('slug', $slug)->with(['reviews' => fn ($q) => $q->where('is_approved', true)->latest()])->first();

        if ($p) {
            $avgRating   = $p->reviews->avg('rating') ?? 4.5;
            $ratingCount = $p->reviews->count();

            return [
                'name'         => $p->name,
                'unit'         => $p->unit ?? '1 pack',
                'unit_note'    => null,
                'price'        => $p->price,
                'old_price'    => $p->old_price,
                'stock'        => $p->stock,
                'sku'          => $p->sku,
                'rating'       => round($avgRating, 1),
                'rating_count' => $ratingCount,
                'description'  => $p->description ?? "Quality {$p->name} from trusted suppliers, checked for freshness before every delivery.",
                'info'         => [
                    'Category' => ucwords(str_replace('-', ' ', $p->category ?? 'Groceries')),
                    'Type'     => $p->type ?? '—',
                    'SKU'      => $p->sku ?? '—',
                    'Storage'  => 'See packaging',
                ],
                'image'        => $p->image_url,
                'images'       => [$p->image_url],
                'reviews'      => $p->reviews->map(fn ($r) => [
                    'name'   => $r->reviewer_name,
                    'rating' => $r->rating,
                    'body'   => $r->body,
                    'date'   => $r->created_at->diffForHumans(),
                ])->all(),
            ];
        }

        // Fallback for slugs not yet in DB
        $name = str($slug)->replace('-', ' ')->title()->value();
        return [
            'name' => $name, 'unit' => '1 pack', 'unit_note' => null,
            'price' => 24.00, 'old_price' => 30.00, 'stock' => 12, 'sku' => 'GEN-0000',
            'rating' => 4.5, 'rating_count' => 0,
            'description' => "Quality {$name} from trusted suppliers, checked for freshness before every delivery.",
            'info' => ['Category' => 'Groceries', 'Storage' => 'See packaging'],
            'image'   => ProductImages::get($slug),
            'images'  => [ProductImages::get($slug)],
            'reviews' => [],
        ];
    }

    private function relatedProducts(): array
    {
        $current = Product::where('slug', $this->slug)->first();

        $query = Product::where('is_active', true)->where('slug', '!=', $this->slug);
        if ($current) {
            $query->where('category', $current->category);
        }

        return $query->inRandomOrder()->limit(4)->get()->map->toCardArray()->all();
    }
}
