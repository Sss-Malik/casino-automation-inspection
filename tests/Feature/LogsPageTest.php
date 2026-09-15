<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Tests\AutomationTestCase;

class LogsPageTest extends AutomationTestCase
{
    private array $columns = ['id', 'type', 'description', 'source_url', 'backend', 'task_id', 'created_at'];

    public function test_logs_page_does_not_render_rows_server_side(): void
    {
        // Production holds 274k log rows; the page used to load them all
        // (7 × 500 in the access log). Rows must come through /logs/data.
        $this->log(['description' => 'UNIQUE-LOG-LINE-9f3a']);

        $this->actingAs($this->superAdmin())->get('/logs')
            ->assertOk()
            ->assertDontSee('UNIQUE-LOG-LINE-9f3a');
    }

    public function test_logs_data_returns_rows_with_backend_name(): void
    {
        $juwa = $this->backend('juwa');
        $withBackend = $this->log(['backend_id' => $juwa, 'type' => 'error', 'description' => 'boom']);
        $without = $this->log(['backend_id' => null, 'description' => 'no backend']);

        $response = $this->actingAs($this->superAdmin())
            ->getJson('/logs/data?'.$this->dataTablesQuery($this->columns));

        $response->assertOk()->assertJsonPath('recordsTotal', 2);

        $rows = collect($response->json('data'))->keyBy('id');
        $this->assertSame('juwa', $rows[$withBackend]['backend']);
        $this->assertStringContainsString('error', $rows[$withBackend]['type']);
        $this->assertStringContainsString('boom', $rows[$withBackend]['description']);
        $this->assertSame('', $rows[$without]['backend']);
    }

    public function test_logs_data_filters_by_task_id(): void
    {
        $task = (string) Str::uuid();
        $this->log(['task_id' => $task, 'description' => 'mine']);
        $this->log(['task_id' => (string) Str::uuid(), 'description' => 'other']);

        $response = $this->actingAs($this->superAdmin())
            ->getJson('/logs/data?'.$this->dataTablesQuery($this->columns, [], ['task_id' => $task]));

        $response->assertOk()->assertJsonPath('recordsFiltered', 1);
        $this->assertStringContainsString('mine', $response->json('data.0.description'));
    }

    public function test_logs_data_filters_by_backend_and_type(): void
    {
        $juwa = $this->backend('juwa');
        $juwa2 = $this->backend('juwa2');
        $this->log(['backend_id' => $juwa, 'type' => 'error', 'description' => 'juwa error']);
        $this->log(['backend_id' => $juwa, 'type' => 'info', 'description' => 'juwa info']);
        $this->log(['backend_id' => $juwa2, 'type' => 'error', 'description' => 'juwa2 error']);

        $response = $this->actingAs($this->superAdmin())->getJson(
            '/logs/data?'.$this->dataTablesQuery($this->columns, ['backend' => (string) $juwa, 'type' => 'error'])
        );

        $response->assertOk()->assertJsonPath('recordsFiltered', 1);
        $this->assertStringContainsString('juwa error', $response->json('data.0.description'));
    }

    public function test_logs_page_for_a_task_prefills_the_task_filter(): void
    {
        $task = (string) Str::uuid();

        $this->actingAs($this->superAdmin())->get("/logs/{$task}")
            ->assertOk()
            ->assertSee('value="'.$task.'"', false);
    }

    public function test_logs_page_lists_backend_filter_options_from_the_database(): void
    {
        $id = $this->backend('dragonfury');

        $this->actingAs($this->superAdmin())->get('/logs')
            ->assertOk()
            ->assertSee('<option value="'.$id.'"', false)
            ->assertSee('dragonfury');
    }
}
