<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\AutomationTestCase;

class RequestsViewTest extends AutomationTestCase
{
    private array $columns = ['id', 'task_id', 'type_badge', 'status_badge', 'backend', 'payload', 'created_fmt', 'updated_fmt'];

    public function test_requests_data_exposes_the_full_payload(): void
    {
        // The table used to truncate the payload at 120 characters with no
        // way to see the rest.
        $long = str_repeat('x', 150);
        $this->task(['backend_id' => $this->backend('juwa')], [
            'payload' => json_encode(['action' => 'recharge-account', 'backend' => 'juwa', 'account_id' => 'user_JW1', 'note' => $long]),
        ]);

        $row = $this->actingAs($this->superAdmin())
            ->getJson('/requests/data?'.$this->dataTablesQuery($this->columns))
            ->assertOk()
            ->json('data.0');

        $this->assertSame($long, $row['detail']['request']['payload']['note']);
        $this->assertStringContainsString($long, $row['payload'], 'full payload must be reachable from the cell');
        $this->assertSame('juwa', $row['detail']['backend']);
    }

    public function test_requests_data_includes_the_backend_and_filters_by_it(): void
    {
        $juwa = $this->backend('juwa');
        $river = $this->backend('river');
        $this->task(['backend_id' => $juwa], ['type' => 'read']);
        $this->task(['backend_id' => $river], ['type' => 'read']);

        $response = $this->actingAs($this->superAdmin())->getJson(
            '/requests/data?'.$this->dataTablesQuery($this->columns, ['backend' => 'river'])
        );

        $response->assertOk()->assertJsonPath('recordsFiltered', 1);
        $this->assertSame('river', $response->json('data.0.backend'));
    }

    public function test_requests_page_lists_backends_from_the_database(): void
    {
        $this->backend('dragonfury');

        $this->actingAs($this->superAdmin())->get('/requests/view')
            ->assertOk()
            ->assertSee('<option value="dragonfury"', false);
    }

    public function test_requests_data_survives_a_request_with_no_result_row(): void
    {
        DB::table('automation_requests')->insert([
            'task_id' => (string) Str::uuid(), 'type' => 'read',
            'payload' => json_encode(['backend' => 'juwa']),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $row = $this->actingAs($this->superAdmin())
            ->getJson('/requests/data?'.$this->dataTablesQuery($this->columns))
            ->assertOk()
            ->json('data.0');

        $this->assertSame('read', $row['detail']['request']['type']);
        $this->assertNull($row['detail']['status']);
        $this->assertSame('', $row['backend']);
    }
}
