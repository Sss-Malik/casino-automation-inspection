<?php

namespace Tests\Unit;

use App\Support\TaskDetail;
use PHPUnit\Framework\TestCase;

class TaskDetailTest extends TestCase
{
    public function test_payload_cell_tolerates_a_non_object_payload(): void
    {
        // automation_requests.payload is free JSON; the `array` cast hands a
        // scalar straight through, which must not take the whole page down.
        $this->assertSame('—', TaskDetail::payloadCell(null));
        $this->assertStringContainsString('abc', TaskDetail::payloadCell('abc'));
        $this->assertStringContainsString('user_JW1', TaskDetail::payloadCell(['action' => 'x', 'account_id' => 'user_JW1']));
        $this->assertStringNotContainsString('action', TaskDetail::payloadCell(['action' => 'x', 'account_id' => 'user_JW1']));
    }
}
