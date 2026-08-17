<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ContactForm extends Component
{
    #[Rule('required|min:2|max:100')]
    public string $name = '';

    #[Rule('required|email|max:254')]
    public string $email = '';

    #[Rule('required|min:9|max:20')]
    public string $phone = '';

    #[Rule('required|in:My order,Delivery,Returns,Product,Other')]
    public string $topic = 'My order';

    #[Rule('nullable|regex:/^[A-Za-z0-9\-]{0,30}$/')]
    public string $order_number = '';

    #[Rule('required|min:10|max:2000')]
    public string $message = '';

    public bool $sent = false;

    public function send(): void
    {
        $key = 'contact.' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('message', 'Too many submissions. Please wait a few minutes before trying again.');
            return;
        }

        $this->validate();

        RateLimiter::hit($key, 3600);

        $body = implode("\n", [
            'Name:     ' . strip_tags($this->name),
            'Email:    ' . strip_tags($this->email),
            'Phone:    ' . strip_tags($this->phone),
            'Topic:    ' . $this->topic,
            'Order #:  ' . strip_tags($this->order_number),
            '',
            strip_tags($this->message),
        ]);

        try {
            Mail::raw($body, function ($msg) {
                $msg->to(config('mail.from.address', 'hello@fenroy.com'))
                    ->subject('Contact: ' . $this->topic)
                    ->replyTo($this->email, strip_tags($this->name));
            });
        } catch (\Throwable $e) {
            Log::error('ContactForm mail failed', ['error' => $e->getMessage(), 'ip' => request()->ip()]);
        }

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
