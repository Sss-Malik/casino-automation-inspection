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
        $id = $this->backend('dragonfury', status: 0);

        $this->actingAs($this->superAdmin())->get('/tasks')
            ->assertOk()
            ->assertSee('<option value="'.$id.'"', false)
            ->assertSee('dragonfury (disabled)');
    }

    public function test_tasks_page_prefills_the_task_filter_from_the_query_string(): void
    {
        // The make-request results and the logs page link here by task id.
        $this->actingAs($this->superAdmin())
            ->get('/tasks?task_id=aaaaaaaa-0000-0000-0000-000000000001')
            ->assertOk()
            ->assertSee('value="aaaaaaaa-0000-0000-0000-000000000001"', false);
    }

    public function test_tasks_data_filters_by_exact_task_id(): void
    {
        // A task id is a unique-index point lookup; it must not go through the
        // global LIKE search, which scans every row on the live DB.
        $juwa = $this->backend('juwa');
        $wanted = $this->task(['backend_id' => $juwa]);
        $this->task(['backend_id' => $juwa]);

        $response = $this->actingAs($this->superAdmin())->getJson(
            '/tasks/data?'.$this->dataTablesQuery($this->columns, [], ['task_id' => $wanted])
        );

        $response->assertOk()->assertJsonPath('recordsFiltered', 1);
        $this->assertSame($wanted, json_decode($response->json('data.0.detail'), true)['task_id']);
    }

    public function test_task_detail_keeps_special_characters_intact(): void
    {
        // Yajra HTML-escapes every nested string unless the column is raw; the
        // modal renders with .text(), so entities would show literally and a
        // presigned screenshot URL with & would break.
        $this->task([
            'backend_id' => $this->backend('juwa'),
            'description' => "Account 'user_JW1' not found & retry <3",
            'screenshot_url' => 'https://s3.example/shot.png?X-Amz-Algorithm=AWS4&X-Amz-Signature=abc',
        ], [
            'payload' => json_encode(['action' => 'read-account', 'backend' => 'juwa', 'account_id' => "user'JW&1"]),
        ]);

        $row = $this->actingAs($this->superAdmin())
            ->getJson('/tasks/data?'.$this->dataTablesQuery($this->columns))
            ->assertOk()
            ->json('data.0');

        $detail = json_decode($row['detail'], true);
        $this->assertSame("Account 'user_JW1' not found & retry <3", $detail['description']);
        $this->assertSame('https://s3.example/shot.png?X-Amz-Algorithm=AWS4&X-Amz-Signature=abc', $detail['screenshot_url']);
        $this->assertSame("user'JW&1", $detail['request']['payload']['account_id']);
    }

    public function test_tasks_data_filters_by_type_and_by_payload_text(): void
    {
        $juwa = $this->backend('juwa');
        $this->task(['backend_id' => $juwa], ['type' => 'recharge', 'payload' => json_encode(['account_id' => 'user_JW77'])]);
        $this->task(['backend_id' => $juwa], ['type' => 'read', 'payload' => json_encode(['account_id' => 'user_JW77'])]);
        $this->task(['backend_id' => $juwa], ['type' => 'read', 'payload' => json_encode(['account_id' => 'user_JW99'])]);

        $admin = $this->superAdmin();

        $this->actingAs($admin)->getJson('/tasks/data?'.$this->dataTablesQuery($this->columns, ['type' => 'recharge']))
            ->assertOk()->assertJsonPath('recordsFiltered', 1);

        $this->actingAs($admin)->getJson('/tasks/data?'.$this->dataTablesQuery($this->columns, ['payload' => 'user_JW77']))
            ->assertOk()->assertJsonPath('recordsFiltered', 2);
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
        $detail = json_decode($row['detail'], true);
        $this->assertSame('user_JW77', $detail['request']['payload']['account_id']);
        $this->assertSame(25, $detail['request']['payload']['count']);
        $this->assertSame('recharge', $detail['request']['type']);
        $this->assertSame('juwa', $detail['backend']);
        $this->assertStringContainsString('data-bs-html="true"', $row['description']);
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

        $this->assertNull(json_decode($row['detail'], true)['request']);
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
        // The dropdown sends the backend id; "juwa" must never also match "juwa2".
        $juwa = $this->backend('juwa');
        $juwa2 = $this->backend('juwa2');
        $this->task(['backend_id' => $juwa, 'status' => 'failed', 'description' => 'juwa failed']);
        $this->task(['backend_id' => $juwa, 'status' => 'success', 'description' => 'juwa ok']);
        $this->task(['backend_id' => $juwa2, 'status' => 'failed', 'description' => 'juwa2 failed']);

        $response = $this->actingAs($this->superAdmin())->getJson(
            '/tasks/data?'.$this->dataTablesQuery($this->columns, ['backend' => (string) $juwa, 'status' => 'failed'])
        );

        $response->assertOk()->assertJsonPath('recordsFiltered', 1);
        $this->assertStringContainsString('juwa failed', $response->json('data.0.description'));
    }
}
