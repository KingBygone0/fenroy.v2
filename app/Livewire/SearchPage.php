<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class SearchPage extends Component
{
    #[Url(as: 'q')]
    public string $query = '';

    #[Url]
    public string $sort = 'relevance';

    #[Url]
    public bool $inStockOnly = false;

    #[Url]
    public string $pricePreset = '';

    #[Url]
    public int $page = 1;

    public int $perPage = 12;

    public function mount(string $q = ''): void
    {
        if ($q !== '') {
            $this->query = $q;
        }
    }

    public function updated(string $property): void
    {
        if ($property !== 'page') {
            $this->page = 1;
        }
    }

    public function setPreset(string $preset): void
    {
        $this->pricePreset = $this->pricePreset === $preset ? '' : $preset;
        $this->page = 1;
    }

    public function setQuery(string $q): void
    {
        $this->query = $q;
        $this->page  = 1;
    }

    public function clearFilters(): void
    {
        $this->reset(['sort', 'inStockOnly', 'pricePreset', 'page']);
    }

    public function goToPage(int $p): void
    {
        $this->page = max(1, $p);
    }

    private function buildQuery()
    {
        $q = Product::where('is_active', true);

        if ($this->query !== '') {
            $q->where(function ($sub) {
                $sub->where('name', 'like', '%' . $this->query . '%')
                    ->orWhere('category', 'like', '%' . $this->query . '%')
                    ->orWhere('description', 'like', '%' . $this->query . '%');
            });
        }

        if ($this->inStockOnly) {
            $q->where('stock', '>', 0);
        }

        [$min, $max] = match ($this->pricePreset) {
            'under50'  => [0, 50],
            '50to100'  => [50, 100],
            'over100'  => [100, 99999],
            default    => [0, 99999],
        };
        $q->whereBetween('price', [$min, $max]);

        match ($this->sort) {
            'price-asc'  => $q->orderBy('price'),
            'price-desc' => $q->orderByDesc('price'),
            'name'       => $q->orderBy('name'),
            default      => $q->orderByDesc('is_best_seller')->orderByDesc('is_featured'),
        };

        return $q;
    }

    public function render()
    {
        $q        = $this->buildQuery();
        $total    = $q->count();
        $lastPage = max(1, (int) ceil($total / $this->perPage));
        $this->page = min($this->page, $lastPage);

        $results = $q->skip(($this->page - 1) * $this->perPage)->take($this->perPage)
            ->get()->map->toCardArray()->all();

        return view('livewire.search-page', [
            'results'          => $results,
            'total'            => $total,
            'lastPage'         => $lastPage,
            'hasActiveFilters' => $this->sort !== 'relevance' || $this->inStockOnly || $this->pricePreset !== '',
        ]);
    }
}
