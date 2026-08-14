<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;

class CategoryPage extends Component
{
    public string $slug = 'fruits-vegetables';
    public string $categoryName = 'Fruits & Vegetables';
    public string $categoryDescription = 'Farm-fresh fruits and vegetables, delivered the same day they arrive.';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $sort = 'popular';

    #[Url]
    public bool $inStockOnly = false;

    #[Url]
    public bool $onSaleOnly = false;

    #[Url]
    public array $types = [];

    #[Url]
    public int $priceMin = 0;

    #[Url]
    public int $priceMax = 200;

    #[Url]
    public int $page = 1;

    public int $perPage = 12;

    public function mount(string $slug = 'fruits-vegetables'): void
    {
        $this->slug = $slug;

        $names = [
            'fruits-vegetables' => ['Fruits & Vegetables', 'Farm-fresh fruits and vegetables, delivered the same day they arrive.'],
            'beverages'         => ['Beverages', 'Water, juices, soft drinks and more — always chilled, always fresh.'],
            'snacks-sweets'     => ['Snacks & Sweets', 'Biscuits, chocolates and treats for every craving.'],
            'pantry'            => ['Pantry', 'Rice, oils, canned goods and every staple your kitchen needs.'],
            'dairy-eggs'        => ['Dairy & Eggs', 'Fresh milk, cheese, yoghurt and farm eggs.'],
            'household'         => ['Household', 'Cleaning, laundry and home essentials.'],
            'personal-care'     => ['Personal Care', 'Bath, skin and hair care for the whole family.'],
            'baby-care'         => ['Baby Care', 'Diapers, wipes, formula and everything baby.'],
        ];

        [$this->categoryName, $this->categoryDescription] = $names[$slug] ?? $names['fruits-vegetables'];
    }

    public function updated($property): void
    {
        if ($property !== 'page') {
            $this->page = 1;
        }
    }

    public function toggleType(string $type): void
    {
        if (in_array($type, $this->types)) {
            $this->types = array_values(array_diff($this->types, [$type]));
        } else {
            $this->types[] = $type;
        }
        $this->page = 1;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'inStockOnly', 'onSaleOnly', 'types', 'priceMin', 'priceMax', 'page']);
    }

    public function goToPage(int $page): void
    {
        $this->page = max(1, $page);
    }

    public function getActiveFilterCountProperty(): int
    {
        return ($this->inStockOnly ? 1 : 0)
            + ($this->onSaleOnly ? 1 : 0)
            + count($this->types)
            + (($this->priceMin > 0 || $this->priceMax < 200) ? 1 : 0);
    }

    public function render()
    {
        $query = Product::where('category', $this->slug)->where('is_active', true);

        if ($this->search !== '') {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        if ($this->inStockOnly) {
            $query->where('stock', '>', 0);
        }
        if ($this->onSaleOnly) {
            $query->whereNotNull('old_price');
        }
        if (!empty($this->types)) {
            $query->whereIn('type', $this->types);
        }
        $query->whereBetween('price', [$this->priceMin, $this->priceMax]);

        match ($this->sort) {
            'price-asc'  => $query->orderBy('price'),
            'price-desc' => $query->orderByDesc('price'),
            'name'       => $query->orderBy('name'),
            'newest'     => $query->latest(),
            default      => $query->orderByDesc('is_best_seller')->orderByDesc('is_featured'),
        };

        $total    = $query->count();
        $lastPage = max(1, (int) ceil($total / $this->perPage));
        $this->page = min($this->page, $lastPage);

        $items = $query->skip(($this->page - 1) * $this->perPage)->take($this->perPage)
            ->get()->map->toCardArray()->all();

        $typeOptions = Product::where('category', $this->slug)->where('is_active', true)
            ->selectRaw('type, count(*) as cnt')
            ->groupBy('type')
            ->pluck('cnt', 'type')
            ->all();

        return view('livewire.category-page', [
            'products'    => $items,
            'total'       => $total,
            'lastPage'    => $lastPage,
            'typeOptions' => $typeOptions,
        ]);
    }
}
