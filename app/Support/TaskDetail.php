<?php

namespace App\Support;

use App\Models\AutomationRequest;
use App\Models\AutomationResult;
use Illuminate\Support\Str;

/**
 * One task as the detail modal and the table cells see it: the
 * automation_results row (may be missing) plus its automation_requests row
 * (may be missing). Raw values only — the Blade/JS side renders them.
 */
class TaskDetail
{
    /**
     * The detail as a JSON string for a raw DataTables column. Emitted as a
     * string on purpose: Yajra HTML-escapes every nested value of an array
     * column, and the modal renders with .text(), so entities would show
     * literally and a presigned screenshot URL with & would break.
     */
    public static function json(?AutomationResult $result, ?AutomationRequest $request): string
    {
        return json_encode(self::make($result, $request), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function make(?AutomationResult $result, ?AutomationRequest $request): array
    {
        return [
            'id' => $result?->id,
            'user_id' => $result?->user_id,
            'description' => $result?->description,
            'task_id' => $result?->task_id ?? $request?->task_id,
            'status' => $result?->status,
            'duration_seconds' => $result?->duration_seconds,
            'data' => $result?->data,
            'backend' => $result?->backend?->name,
            'order_id' => $result?->order_id,
            'screenshot_url' => $result?->screenshot_url,
            'created_at' => Format::dateTime($result?->created_at),
            'updated_at' => Format::dateTime($result?->updated_at),
            'logs_url' => route('logs.index', ['taskId' => $result?->task_id ?? $request?->task_id]),
            'request' => $request ? [
                'type' => $request->type,
                'payload' => $request->payload,
                'created_at' => Format::dateTime($request->created_at),
            ] : null,
        ];
    }

    /**
     * Compact one-line JSON of a request payload for a table cell, with the
     * full JSON in a tooltip. `action` is dropped: the Type column has it.
     */
    public static function payloadCell(mixed $payload): string
    {
        if ($payload === null || $payload === [] || $payload === '') {
            return '—';
        }

        // The column is free JSON written by the Python service; the `array`
        // cast hands a scalar document straight through.
        if (is_array($payload)) {
            unset($payload['action']);
        }
        $json = is_string($payload) ? $payload : json_encode($payload, JSON_UNESCAPED_SLASHES);

        return '<code class="small payload-cell" role="button" data-bs-toggle="tooltip" title="'.e($json).'">'
            .e(Str::limit($json, 80))
            .'</code>';
    }
}
