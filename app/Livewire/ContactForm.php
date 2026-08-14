<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ContactForm extends Component
{
    #[Rule('required|min:2')]
    public string $name = '';

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required|min:9')]
    public string $phone = '';

    #[Rule('required')]
    public string $topic = 'My order';

    public string $order_number = '';

    #[Rule('required|min:10')]
    public string $message = '';

    public bool $sent = false;

    public function send(): void
    {
        $this->validate();

        $body = implode("\n", [
            "Name:     {$this->name}",
            "Email:    {$this->email}",
            "Phone:    {$this->phone}",
            "Topic:    {$this->topic}",
            "Order #:  {$this->order_number}",
            '',
            $this->message,
        ]);

        try {
            Mail::raw($body, function ($msg) {
                $msg->to(config('mail.from.address', 'hello@fenroy.com'))
                    ->subject("Contact: {$this->topic}")
                    ->replyTo($this->email, $this->name);
            });
        } catch (\Throwable $e) {
            Log::error('ContactForm mail failed', ['error' => $e->getMessage(), 'data' => $body]);
        }

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
