<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\AutomationTestCase;

class BackendAccountsTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.casino_automation.app_key' => 'test-app-key',
            'services.casino_automation.base_url' => 'http://automation.test/automation',
        ]);
        Http::fake(['automation.test/*' => Http::response(['status' => 'scheduled', 'task_id' => 'abc'], 200)]);
    }

    public function test_creating_pool_accounts_is_not_possible_with_a_get(): void
    {
        // A GET that fires create-account at the live service can be triggered
        // by any <img src> a logged-in admin's browser loads.
        $id = $this->backend('juwa');

        $this->actingAs($this->superAdmin())->get("/backend/{$id}/accounts/create")->assertStatus(405);

        Http::assertNothingSent();
    }

    public function test_creating_pool_accounts_posts_with_the_app_key(): void
    {
        $id = $this->backend('juwa');

        $this->actingAs($this->superAdmin())
            ->post("/backend/{$id}/accounts/create")
            ->assertRedirect()
            ->assertSessionHas('response');

        Http::assertSent(fn (Request $r) => $r->url() === 'http://automation.test/automation/create-account'
            && $r->hasHeader('x-app-key', 'test-app-key')
            && $r['backend'] === 'juwa');
    }

    public function test_stats_page_offers_create_more_as_a_form(): void
    {
        $id = $this->backend('juwa');
        \Illuminate\Support\Facades\DB::table('backend_accounts')->insert([
            'backend_id' => $id, 'username' => 'u1', 'password' => 'p1', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($this->superAdmin())->get('/backend/accounts/stats')
            ->assertOk()
            ->assertSee('action="'.url("/backend/{$id}/accounts/create").'"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_api_user_scaffold_route_is_gone(): void
    {
        $this->getJson('/api/user')->assertNotFound();
    }
}
