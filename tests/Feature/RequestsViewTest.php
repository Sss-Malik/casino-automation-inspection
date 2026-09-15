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

        $detail = json_decode($row['detail'], true);
        $this->assertSame($long, $detail['request']['payload']['note']);
        $this->assertStringContainsString($long, $row['payload'], 'full payload must be reachable from the cell');
        $this->assertSame('juwa', $detail['backend']);
    }

    public function test_requests_data_includes_the_backend_and_filters_by_it(): void
    {
        $juwa = $this->backend('juwa');
        $juwa2 = $this->backend('juwa2');
        $this->task(['backend_id' => $juwa], ['type' => 'read']);
        $this->task(['backend_id' => $juwa2], ['type' => 'read']);

        $response = $this->actingAs($this->superAdmin())->getJson(
            '/requests/data?'.$this->dataTablesQuery($this->columns, ['backend' => (string) $juwa])
        );

        $response->assertOk()->assertJsonPath('recordsFiltered', 1);
        $this->assertSame('juwa', $response->json('data.0.backend'));
    }

    public function test_requests_page_lists_backends_from_the_database(): void
    {
        $id = $this->backend('dragonfury');

        $this->actingAs($this->superAdmin())->get('/requests/view')
            ->assertOk()
            ->assertSee('<option value="'.$id.'"', false)
            ->assertSee('dragonfury');
    }

    public function test_request_detail_keeps_special_characters_intact(): void
    {
        $this->task(['backend_id' => $this->backend('juwa'), 'description' => "it's & <b>"], [
            'payload' => json_encode(['action' => 'read-account', 'backend' => 'juwa', 'account_id' => "user'JW&1"]),
        ]);

        $row = $this->actingAs($this->superAdmin())
            ->getJson('/requests/data?'.$this->dataTablesQuery($this->columns))
            ->assertOk()
            ->json('data.0');

        $detail = json_decode($row['detail'], true);
        $this->assertSame("it's & <b>", $detail['description']);
        $this->assertSame("user'JW&1", $detail['request']['payload']['account_id']);
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

        $detail = json_decode($row['detail'], true);
        $this->assertSame('read', $detail['request']['type']);
        $this->assertNull($detail['status']);
        $this->assertSame('', $row['backend']);
    }
}
