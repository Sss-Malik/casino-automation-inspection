<?php

namespace Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\AutomationTestCase;

class MakeRequestTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.casino_automation.app_key' => 'test-app-key',
            'services.casino_automation.base_url' => 'http://automation.test/automation',
        ]);
        Http::fake(['automation.test/*' => Http::response(['status' => 'scheduled', 'task_id' => 'abc'], 200)]);
        $this->backend('juwa');
    }

    public function test_make_request_page_offers_only_the_app_key_endpoints(): void
    {
        // Everything else needs a player's token plus an order / freeplay /
        // redeem row in the DB, so it cannot be exercised by a developer here.
        $response = $this->actingAs($this->superAdmin())->get('/requests/make')->assertOk();

        foreach (['read-account', 'read-backend', 'create-account'] as $endpoint) {
            $response->assertSee('value="'.$endpoint.'"', false);
        }
        foreach (['recharge-account', 'withdraw-account', 'freeplay-account', 'reset-password', 'read-account-user'] as $endpoint) {
            $response->assertDontSee('value="'.$endpoint.'"', false);
        }
    }

    public function test_make_request_page_lists_backends_from_the_database(): void
    {
        $this->backend('dragonfury', status: 0);

        $this->actingAs($this->superAdmin())->get('/requests/make')
            ->assertOk()
            ->assertSee('<option value="dragonfury"', false);
    }

    public function test_send_refuses_endpoints_that_need_a_player_context(): void
    {
        $this->actingAs($this->superAdmin())
            ->from('/requests/make')
            ->post('/requests/send', ['endpoint' => 'recharge-account', 'backend' => 'juwa', 'account_id' => 'x', 'count' => 5, 'repeat' => 1])
            ->assertRedirect('/requests/make')
            ->assertSessionHasErrors('endpoint');

        Http::assertNothingSent();
    }

    public function test_send_accepts_any_backend_the_database_knows(): void
    {
        $this->backend('dragonfury');

        $this->actingAs($this->superAdmin())
            ->post('/requests/send', ['endpoint' => 'read-backend', 'backend' => 'dragonfury', 'repeat' => 1])
            ->assertSessionHasNoErrors();

        Http::assertSent(fn (Request $r) => $r->url() === 'http://automation.test/automation/read-backend'
            && $r['backend'] === 'dragonfury');
    }

    public function test_send_refuses_a_backend_the_database_does_not_know(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/requests/send', ['endpoint' => 'read-backend', 'backend' => 'pandamaster', 'repeat' => 1])
            ->assertSessionHasErrors('backend');

        Http::assertNothingSent();
    }

    public function test_send_posts_read_account_with_the_app_key_header(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/requests/send', ['endpoint' => 'read-account', 'backend' => 'juwa', 'account_id' => 'user_JW1', 'repeat' => 1])
            ->assertSessionHasNoErrors();

        Http::assertSent(fn (Request $r) => $r->url() === 'http://automation.test/automation/read-account'
            && $r->hasHeader('x-app-key', 'test-app-key')
            && $r['backend'] === 'juwa'
            && $r['account_id'] === 'user_JW1');
    }

    public function test_send_requires_account_id_for_read_account(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/requests/send', ['endpoint' => 'read-account', 'backend' => 'juwa', 'repeat' => 1])
            ->assertSessionHasErrors('account_id');

        Http::assertNothingSent();
    }

    public function test_send_repeats_and_reports_every_response(): void
    {
        $response = $this->actingAs($this->superAdmin())
            ->post('/requests/send', ['endpoint' => 'create-account', 'backend' => 'juwa', 'repeat' => 3]);

        $response->assertSessionHas('responses', fn ($responses) => count($responses) === 3
            && $responses[0]['status'] === 200
            && $responses[0]['body']['task_id'] === 'abc');
        Http::assertSentCount(3);
    }

    public function test_send_reports_an_unreachable_automation_service_as_a_result(): void
    {
        // "The service is down" is the finding a developer came here for;
        // it must render as a result, not as a 500 page.
        Http::fake(fn () => throw new ConnectionException('cURL error 7: Failed to connect'));

        $response = $this->actingAs($this->superAdmin())
            ->post('/requests/send', ['endpoint' => 'read-backend', 'backend' => 'juwa', 'repeat' => 2]);

        // No point re-trying a dead host: with a 15s timeout, 20 repeats would
        // outlive the web server's request timeout and lose the result page.
        $response->assertSessionHas('responses', fn ($responses) => count($responses) === 1
            && $responses[0]['status'] === 0
            && str_contains($responses[0]['body']['error'], 'Failed to connect')
            && str_contains($responses[0]['body']['note'], '1 remaining'));
    }

    public function test_send_caps_the_repeat_count(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/requests/send', ['endpoint' => 'create-account', 'backend' => 'juwa', 'repeat' => 21])
            ->assertSessionHasErrors('repeat');

        Http::assertNothingSent();
    }

    public function test_login_no_longer_mints_api_tokens(): void
    {
        // The token existed only to call the player-token rails, which the
        // form no longer offers. Minting it left rows in production's
        // personal_access_tokens for every panel login.
        $admin = $this->superAdmin();

        $this->post('/login', ['email' => $admin->email, 'password' => 'secret123'])
            ->assertRedirect(route('dashboard'));

        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }
}
