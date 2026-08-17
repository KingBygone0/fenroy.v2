<?php

namespace App\Livewire;

use App\Models\Address;
use Livewire\Attributes\Rule;
use Livewire\Component;

class AccountAddresses extends Component
{
    public array $addresses = [];
    public bool $showForm = false;

    #[Rule('required|min:2')]
    public string $full_name = '';
    #[Rule('required')]
    public string $phone = '';
    #[Rule('required|min:5')]
    public string $line1 = '';
    #[Rule('required')]
    public string $city = 'Accra';
    #[Rule('required')]
    public string $region = 'Greater Accra';
    public bool $is_default = false;

    public function mount(): void
    {
        $this->loadAddresses();
        if (auth()->check()) {
            $this->full_name = auth()->user()->name;
        }
    }

    private function loadAddresses(): void
    {
        $this->addresses = auth()->check()
            ? Address::where('user_id', auth()->id())->latest()->get()->toArray()
            : [];
    }

    public function save(): void
    {
        $this->validate();

        if ($this->is_default) {
            Address::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        $address           = new Address();
        $address->user_id  = auth()->id();  // set directly — user_id is not in fillable
        $address->fill([
            'full_name'  => $this->full_name,
            'phone'      => $this->phone,
            'line1'      => $this->line1,
            'city'       => $this->city,
            'region'     => $this->region,
            'is_default' => $this->is_default || Address::where('user_id', auth()->id())->count() === 0,
        ]);
        $address->save();

        $this->reset(['full_name', 'phone', 'line1', 'city', 'region', 'is_default', 'showForm']);
        $this->full_name = auth()->user()->name;
        $this->loadAddresses();

        $this->js("window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Address saved.' } }))");
    }

    public function setDefault(int $id): void
    {
        Address::where('user_id', auth()->id())->update(['is_default' => false]);
        Address::where('id', $id)->where('user_id', auth()->id())->update(['is_default' => true]);
        $this->loadAddresses();
    }

    public function delete(int $id): void
    {
        Address::where('id', $id)->where('user_id', auth()->id())->delete();
        $this->loadAddresses();
    }

    public function render()
    {
        return view('livewire.account-addresses');
    }
}
