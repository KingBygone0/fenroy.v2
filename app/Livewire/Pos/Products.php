<?php

namespace App\Livewire\Pos;

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Products extends Component
{
    use WithPagination;

    public string $search          = '';
    public string $activeCategory  = '';

    public function updatedSearch(): void         { $this->resetPage(); }
    public function updatedActiveCategory(): void { $this->resetPage(); }

    public function setCategory(string $cat): void
    {
        $this->activeCategory = $this->activeCategory === $cat ? '' : $cat;
        $this->resetPage();
    }

    #[Computed]
    public function categories(): array
    {
        return Product::where('is_active', true)
            ->whereNotNull('category')->where('category', '!=', '')
            ->distinct()->orderBy('category')->pluck('category')->toArray();
    }

    public function render()
    {
        $products = Product::where('is_active', true)
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('sku', 'like', '%'.$this->search.'%')
                  ->orWhere('category', 'like', '%'.$this->search.'%')
            )
            ->when($this->activeCategory, fn ($q) => $q->where('category', $this->activeCategory))
            ->orderBy('category')->orderBy('name')
            ->paginate(30);

        return view('livewire.pos.products', compact('products'))
            ->layout('layouts.pos');
    }
}
