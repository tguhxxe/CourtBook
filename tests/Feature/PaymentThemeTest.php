<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentThemeTest extends TestCase
{
    private function preferences(): array
    {
        return ['merchant_id' => 'QA', 'display_name' => 'Before', 'locale' => 'en',
            'brand' => ['typography' => 'Arial', 'header_color' => '#ffffff', 'button_color' => '#000000', 'input_border_radius' => 0, 'button_border_radius' => 0, 'hide_header' => true],
            'payment_channels' => [['name' => 'bca_va', 'enabled' => true]],
            'allow_retry' => true, 'finish_payment_return_url' => 'https://example.test/finish'];
    }

    public function test_preview_never_updates_remote_preferences(): void
    {
        config(['courtbook.midtrans.server_key' => 'qa-key']);
        Http::fake(['*' => Http::response($this->preferences())]);
        $this->artisan('courtbook:payment-theme')->expectsOutputToContain('Pratinjau saja')->assertSuccessful();
        Http::assertSentCount(1);
        Http::assertSent(fn (Request $r) => $r->method() === 'GET');
    }

    public function test_apply_sends_only_visual_preferences_and_preserves_others(): void
    {
        config(['courtbook.midtrans.server_key' => 'qa-key']);
        Storage::fake('local');
        $before = $this->preferences();
        $after = array_replace($before, ['display_name' => 'CourtBook', 'locale' => 'id', 'brand' => [
            'typography' => 'Nunito', 'header_color' => '#102c59', 'button_color' => '#1455d9',
            'input_border_radius' => 6, 'button_border_radius' => 6, 'hide_header' => false,
        ]]);
        Http::fake(['*' => Http::sequence()->push($before)->push($after)->push($after)]);
        $this->artisan('courtbook:payment-theme --apply')->expectsOutputToContain('tersimpan dan terverifikasi')->assertSuccessful();
        Http::assertSentCount(3);
        Http::assertSent(function (Request $r) use ($after) {
            return $r->method() === 'PATCH' && $r->url() === 'https://app.sandbox.midtrans.com/snap/v3/merchant-preferences'
                && $r->data() === array_intersect_key($after, array_flip(['display_name', 'locale', 'brand']));
        });
        $files = Storage::disk('local')->files('midtrans-theme');
        $this->assertCount(1, $files);
        $backup = json_decode(Storage::disk('local')->get($files[0]), true);
        $this->assertSame($before['brand'], $backup['before']['brand']);
        $this->assertStringNotContainsString('qa-key', Storage::disk('local')->get($files[0]));
    }

    public function test_missing_key_makes_no_request(): void
    {
        config(['courtbook.midtrans.server_key' => null]);
        Http::fake();
        $this->artisan('courtbook:payment-theme --apply')->assertFailed();
        Http::assertNothingSent();
    }

    public function test_matching_theme_does_not_send_a_patch(): void
    {
        config(['courtbook.midtrans.server_key' => 'qa-key']);
        Storage::fake('local');
        $current = array_replace($this->preferences(), ['display_name' => 'CourtBook', 'locale' => 'id', 'brand' => [
            'typography' => 'Nunito', 'header_color' => '#102c59', 'button_color' => '#1455d9',
            'input_border_radius' => 6, 'button_border_radius' => 6, 'hide_header' => false,
        ]]);
        Http::fake(['*' => Http::response($current)]);
        $this->artisan('courtbook:payment-theme --apply')->expectsOutputToContain('sudah sesuai')->assertSuccessful();
        Http::assertSentCount(1);
        $this->assertCount(0, Storage::disk('local')->files('midtrans-theme'));
    }
}
