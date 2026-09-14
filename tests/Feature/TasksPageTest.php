<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\AutomationTestCase;

class TasksPageTest extends AutomationTestCase
{
    private array $columns = [
        'id', 'user_id', 'description', 'task_id', 'type', 'status', 'duration_seconds',
        'payload', 'data_rendered', 'backend', 'order_id', 'screenshot', 'created_at', 'updated_at', 'action',
    ];

    public function test_tasks_page_lists_backend_filter_options_from_the_database(): void
    {
        $this->backend('dragonfury', status: 0);

        $this->actingAs($this->superAdmin())->get('/tasks')
            ->assertOk()
            ->assertSee('<option value="dragonfury"', false)
            ->assertSee('dragonfury (disabled)');
    }

    public function test_tasks_page_does_not_query_tasks_itself(): void
    {
        // index() used to run a 500-row query with logs eager-loaded that the
        // view never read; rows come from /tasks/data.
        $this->task(['backend_id' => $this->backend('juwa')]);

        $taskQueries = 0;
        DB::listen(function ($query) use (&$taskQueries) {
            if (str_contains($query->sql, 'automation_results')) {
                $taskQueries++;
            }
        });

        $this->actingAs($this->superAdmin())->get('/tasks')->assertOk();

        $this->assertSame(0, $taskQueries);
    }

    public function test_tasks_data_shows_the_request_type_and_payload(): void
    {
        $juwa = $this->backend('juwa');
        $this->task(['backend_id' => $juwa], [
            'type' => 'recharge',
            'payload' => json_encode(['action' => 'recharge-account', 'backend' => 'juwa', 'account_id' => 'user_JW77', 'count' => 25]),
        ]);

        $row = $this->actingAs($this->superAdmin())
            ->getJson('/tasks/data?'.$this->dataTablesQuery($this->columns))
            ->assertOk()
            ->json('data.0');

        $this->assertStringContainsString('recharge', $row['type']);
        $this->assertStringContainsString('user_JW77', $row['payload']);
        $this->assertSame('user_JW77', $row['detail']['request']['payload']['account_id']);
        $this->assertSame(25, $row['detail']['request']['payload']['count']);
        $this->assertSame('recharge', $row['detail']['request']['type']);
        $this->assertSame('juwa', $row['detail']['backend']);
    }

    public function test_tasks_data_survives_a_task_with_no_request_row(): void
    {
        DB::table('automation_results')->insert([
            'task_id' => 'aaaaaaaa-0000-0000-0000-000000000001',
            'status' => 'pending', 'backend_id' => $this->backend('juwa'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $row = $this->actingAs($this->superAdmin())
            ->getJson('/tasks/data?'.$this->dataTablesQuery($this->columns))
            ->assertOk()
            ->json('data.0');

        $this->assertNull($row['detail']['request']);
        $this->assertSame('—', $row['payload']);
    }

    public function test_tasks_data_renders_the_finished_status(): void
    {
        // 29 production rows carry status 'finished', which the status map
        // did not know; rendering them raised "Undefined array key".
        $this->task(['backend_id' => $this->backend('firekirin'), 'status' => 'finished']);

        $row = $this->actingAs($this->superAdmin())
            ->getJson('/tasks/data?'.$this->dataTablesQuery($this->columns))
            ->assertOk()
            ->json('data.0');

        $this->assertStringContainsString('finished', $row['status']);
        $this->assertStringContainsString('badge', $row['status']);
    }

    public function test_tasks_data_filters_by_backend_and_status(): void
    {
        $juwa = $this->backend('juwa');
        $river = $this->backend('river');
        $this->task(['backend_id' => $juwa, 'status' => 'failed', 'description' => 'juwa failed']);
        $this->task(['backend_id' => $juwa, 'status' => 'success', 'description' => 'juwa ok']);
        $this->task(['backend_id' => $river, 'status' => 'failed', 'description' => 'river failed']);

        $response = $this->actingAs($this->superAdmin())->getJson(
            '/tasks/data?'.$this->dataTablesQuery($this->columns, ['backend' => 'juwa', 'status' => 'failed'])
        );

        $response->assertOk()->assertJsonPath('recordsFiltered', 1);
        $this->assertStringContainsString('juwa failed', $response->json('data.0.description'));
    }
}
