<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaystackSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_rejects_invalid_reference_format(): void
    {
        $response = $this->postJson('/paystack/verify', [
            'reference' => '../etc/passwd',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['status' => 'error', 'message' => 'Invalid reference.']);
    }

    public function test_verify_rejects_reference_with_special_chars(): void
    {
        $response = $this->postJson('/paystack/verify', [
            'reference' => 'ref"; DROP TABLE orders;--',
        ]);

        $response->assertStatus(422);
    }

    public function test_verify_requires_reference(): void
    {
        $response = $this->postJson('/paystack/verify', []);

        $response->assertStatus(422)
                 ->assertJson(['status' => 'error', 'message' => 'No reference provided.']);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        // Configure a webhook secret so the route doesn't reject early
        \App\Models\Setting::set('paystack_webhook_secret', 'test-secret-key');

        $response = $this->postJson('/paystack/webhook', ['event' => 'charge.success'], [
            'X-Paystack-Signature' => 'invalid-signature',
        ]);

        $response->assertStatus(400);
    }

    public function test_webhook_accepts_valid_hmac_signature(): void
    {
        $secret = 'test-secret-key';
        \App\Models\Setting::set('paystack_webhook_secret', $secret);

        $payload = json_encode(['event' => 'charge.success', 'data' => ['reference' => 'REF123']]);
        $sig     = hash_hmac('sha512', $payload, $secret);

        $response = $this->call('POST', '/paystack/webhook', [], [], [], [
            'HTTP_X-Paystack-Signature' => $sig,
            'CONTENT_TYPE'              => 'application/json',
        ], $payload);

        $response->assertStatus(200);
    }

    public function test_webhook_rejects_all_calls_when_secret_unconfigured(): void
    {
        \App\Models\Setting::where('key', 'paystack_webhook_secret')->delete();

        $response = $this->postJson('/paystack/webhook', ['event' => 'charge.success']);

        $response->assertStatus(400);
    }
}
