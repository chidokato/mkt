<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Jobs\SendPersonalEmailJob;
use App\Models\Department;
use App\Models\MailLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class BulkMailController extends Controller
{
    public function create(Request $request)
    {
        $count = User::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();

        $departments = Department::query()
            ->orderBy('name')
            ->get(['id', 'name', 'parent']);

        $users = User::query()
            ->with('department:id,name')
            ->select('id', 'name', 'yourname', 'email', 'department_id')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('yourname')
            ->orderBy('name')
            ->get();

        $batchId = $request->get('batch_id');
        $logs = collect();
        $stats = null;

        if ($batchId) {
            $logs = MailLog::query()
                ->where('batch_id', $batchId)
                ->orderByDesc('id')
                ->paginate(50)
                ->withQueryString();

            $stats = [
                'queued' => MailLog::where('batch_id', $batchId)->where('status', 'queued')->count(),
                'sent' => MailLog::where('batch_id', $batchId)->where('status', 'sent')->count(),
                'failed' => MailLog::where('batch_id', $batchId)->where('status', 'failed')->count(),
            ];
        }

        return view('emails.bulk_mail.create', compact(
            'count',
            'departments',
            'users',
            'logs',
            'batchId',
            'stats'
        ));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:10000'],
            'send_all' => ['nullable', 'boolean'],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'seconds_per_email' => ['nullable', 'integer', 'min:0', 'max:60'],
            'only_verified' => ['nullable', 'boolean'],
        ]);

        $subject = $data['subject'];
        $contentTemplate = $data['content'];
        $sendAll = (bool) ($data['send_all'] ?? false);
        $onlyVerified = (bool) ($data['only_verified'] ?? false);
        $manualDelaySeconds = (int) ($data['seconds_per_email'] ?? 0);
        $selectedDepartmentIds = collect($data['department_ids'] ?? [])->map(fn ($id) => (int) $id);
        $selectedUserIds = collect($data['user_ids'] ?? [])->map(fn ($id) => (int) $id);

        if (! $sendAll && $selectedDepartmentIds->isEmpty() && $selectedUserIds->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['recipients' => 'Vui long chon it nhat 1 nguoi nhan (email, phong ban hoac tat ca).']);
        }

        $recipientQuery = User::query()
            ->select('id', 'name', 'yourname', 'email', 'department_id')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where('email', 'like', '%@%');

        if ($onlyVerified && \Schema::hasColumn('users', 'email_verified_at')) {
            $recipientQuery->whereNotNull('email_verified_at');
        }

        if (! $sendAll) {
            $departmentUserIds = collect();

            if ($selectedDepartmentIds->isNotEmpty()) {
                $allDepartmentIds = $selectedDepartmentIds
                    ->flatMap(fn ($departmentId) => Department::getChildIds($departmentId))
                    ->unique()
                    ->values();

                $departmentUserIds = User::query()
                    ->whereIn('department_id', $allDepartmentIds)
                    ->pluck('id');
            }

            $finalUserIds = $selectedUserIds
                ->merge($departmentUserIds)
                ->unique()
                ->values();

            if ($finalUserIds->isEmpty()) {
                return back()
                    ->withInput()
                    ->withErrors(['recipients' => 'Khong tim thay nguoi dung hop le tu lua chon cua ban.']);
            }

            $recipientQuery->whereIn('id', $finalUserIds);
        }

        $users = $recipientQuery->orderBy('id')->get()
            ->filter(fn ($u) => ! empty($u->email))
            ->unique(fn ($u) => mb_strtolower(trim((string) $u->email)))
            ->values();

        if ($users->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['recipients' => 'Khong co email hop le de gui.']);
        }

        $maxPerRun = max(1, (int) env('BULK_MAIL_MAX_PER_RUN', 120));
        $baseDelay = max($manualDelaySeconds, (int) env('BULK_MAIL_MIN_DELAY_SECONDS', 2));
        $jitterMax = max(0, (int) env('BULK_MAIL_JITTER_SECONDS', 2));
        $chunkSize = max(1, (int) env('BULK_MAIL_CHUNK_SIZE', 20));
        $chunkCooldown = max(0, (int) env('BULK_MAIL_CHUNK_COOLDOWN_SECONDS', 45));

        $totalRecipients = $users->count();
        $truncated = false;
        if ($totalRecipients > $maxPerRun) {
            $users = $users->take($maxPerRun)->values();
            $truncated = true;
        }

        $batchId = now()->format('YmdHis') . '-' . Str::random(6);
        $rows = [];
        $failedQueueingCount = 0;
        $activeTotal = $users->count();
        $now = now();

        foreach ($users as $i => $u) {
            $rows[] = [
                'batch_id' => $batchId,
                'user_id' => $u->id,
                'email' => $u->email,
                'subject' => $subject,
                'status' => 'queued',
                'sent_at' => null,
                'error' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($rows)) {
            // Store the content template in cache for 1 day
            \Illuminate\Support\Facades\Cache::put("bulk_mail_{$batchId}_content", $contentTemplate, now()->addDay());
            
            // Chunk inserts to avoid query size limits
            foreach (array_chunk($rows, 500) as $chunk) {
                MailLog::insert($chunk);
            }
        }

        $queuedCount = $activeTotal;
        $message = "Đã xếp hàng {$queuedCount}/{$totalRecipients} email. Batch: {$batchId}";
        if ($truncated) {
            $message .= " (Đã giới hạn tối đa {$maxPerRun} email mỗi lần).";
        }
        $message .= ' Hệ thống sẽ tự động gửi ngầm trên trình duyệt, vui lòng KHÔNG đóng tab này cho đến khi hoàn thành.';

        return redirect()
            ->route('admin.bulk_mail.create', ['batch_id' => $batchId])
            ->with('status', $message);
    }

    public function processChunk(Request $request)
    {
        $batchId = $request->input('batch_id');
        $limit = (int) $request->input('limit', 10);
        
        $contentTemplate = \Illuminate\Support\Facades\Cache::get("bulk_mail_{$batchId}_content");
        if (!$contentTemplate) {
            return response()->json(['error' => 'Không tìm thấy nội dung email trong bộ nhớ tạm.'], 400);
        }

        $logs = MailLog::where('batch_id', $batchId)
            ->where('status', 'queued')
            ->limit($limit)
            ->get();

        $processed = 0;
        foreach ($logs as $log) {
            try {
                // Execute job synchronously
                $job = new SendPersonalEmailJob($log->user_id, $log->subject, $contentTemplate, $batchId);
                $job->handle();
                $processed++;
            } catch (\Throwable $e) {
                // Error is already handled and logged by the Job
                $processed++;
            }
        }

        $stats = [
            'queued' => MailLog::where('batch_id', $batchId)->where('status', 'queued')->count(),
            'sent' => MailLog::where('batch_id', $batchId)->where('status', 'sent')->count(),
            'failed' => MailLog::where('batch_id', $batchId)->where('status', 'failed')->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
