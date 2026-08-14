<?php

namespace App\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class FaqPage extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'topic')]
    public string $activeTopic = 'all';

    public ?string $openQuestion = null;

    private function allGroups(): array
    {
        return [
            [
                'key'   => 'orders',
                'label' => 'Orders',
                'items' => [
                    ['id' => 'track-order',   'q' => 'How do I track my order?',             'a' => 'Once your order is confirmed, you\'ll receive an SMS with updates. Your order moves through four stages: Confirmed → Being Picked → Out for Delivery → Delivered. You\'ll get an SMS at each stage. When the rider is on the way, you\'ll receive their name and a call about 10 minutes before arrival.'],
                    ['id' => 'change-order',  'q' => 'Can I change my order after placing it?', 'a' => 'You can request changes within 10 minutes of placing by calling us on 0302 555 019 or messaging on WhatsApp. After that window, picking may have already started and changes may not be possible.'],
                    ['id' => 'out-of-stock',  'q' => 'What happens if an item is out of stock?', 'a' => 'At checkout you choose a substitution preference: we can call you to discuss, automatically substitute with a similar product, or leave the item out and adjust your total. You\'re never charged for an item we can\'t fulfil.'],
                    ['id' => 'cancel-order',  'q' => 'How do I cancel an order?',             'a' => 'Contact us as soon as possible on WhatsApp or by phone. If picking hasn\'t started, we\'ll cancel and process a full refund. Once picking is under way, cancellation may not be possible.'],
                ],
            ],
            [
                'key'   => 'payments',
                'label' => 'Payments',
                'items' => [
                    ['id' => 'payment-methods', 'q' => 'What payment methods do you accept?', 'a' => 'We accept MTN Mobile Money, Vodafone Cash, AirtelTigo Money, and card payments (Visa and Mastercard). All payments are processed securely — we never store your card details.'],
                    ['id' => 'momo-failed',     'q' => 'Why did my MoMo charge fail?',         'a' => 'A MoMo prompt is sent to your phone at checkout. The charge fails if you don\'t approve the prompt in time, your wallet has insufficient funds, or your number is incorrect. Check your balance and try again, or switch to a different payment method.'],
                    ['id' => 'when-charged',    'q' => 'When am I charged?',                   'a' => 'For MoMo, you\'re charged immediately when you approve the payment prompt. For card, the charge happens as soon as you click "Place Order." You\'re never charged for items we can\'t fulfil — any difference is refunded within 1–3 business days.'],
                ],
            ],
            [
                'key'   => 'delivery',
                'label' => 'Delivery & Returns',
                'items' => [
                    ['id' => 'delivery-areas',  'q' => 'Which areas do you deliver to?',                   'a' => 'We currently deliver to Osu, Labone, Cantonments, East Legon, Airport Residential, Spintex, Teshie, Achimota, Dansoman, Tema and Kasoa. Not sure if you\'re covered? Chat with us on WhatsApp.'],
                    ['id' => 'damaged-item',    'q' => 'What do I do if an item arrives damaged or wrong?', 'a' => 'We\'re sorry to hear that. Please contact us within 24 hours of delivery — take a quick photo and message us on WhatsApp or email hello@fenroy.com. We\'ll arrange a replacement or refund straight away.'],
                    ['id' => 'someone-else',    'q' => 'Can someone else receive my delivery?',             'a' => 'Yes — just let us know in the order notes or call us ahead of time. Our rider will hand the order to whoever is at the address.'],
                ],
            ],
        ];
    }

    #[Computed]
    public function groups(): array
    {
        $term = strtolower(trim($this->search));

        return collect($this->allGroups())
            ->when($this->activeTopic !== 'all', fn ($c) => $c->filter(fn ($g) => $g['key'] === $this->activeTopic))
            ->map(function ($group) use ($term) {
                if ($term !== '') {
                    $group['items'] = array_filter($group['items'], fn ($item) =>
                        str_contains(strtolower($item['q']), $term) ||
                        str_contains(strtolower($item['a']), $term)
                    );
                }
                return $group;
            })
            ->filter(fn ($g) => count($g['items']) > 0)
            ->values()
            ->all();
    }

    public function toggle(string $id): void
    {
        $this->openQuestion = $this->openQuestion === $id ? null : $id;
    }

    public function setTopic(string $topic): void
    {
        $this->activeTopic = $topic;
        $this->openQuestion = null;
    }

    public function render()
    {
        return view('livewire.faq-page');
    }
}
