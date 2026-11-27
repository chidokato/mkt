<?php

namespace App\Jobs;

use App\Mail\BulkPersonalMail;
use App\Models\MailLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPersonalEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public int $userId,
        public string $subject,
        public string $contentTemplate,
        public string $batchId
    ) {
    }

    public function handle(): void
    {
        $user = User::select('id', 'name', 'yourname', 'email')
            ->where('id', $this->userId)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->first();

        if (! $user) {
            return;
        }

        $displayName = $user->yourname ?: ($user->name ?: '');
        $content = str_replace(['{name}', '{email}'], [$displayName, $user->email ?? ''], $this->contentTemplate);

        try {
            Mail::to($user->email)->send(
                new BulkPersonalMail($displayName ?: 'Ban', $content, $this->subject)
            );

            MailLog::where('batch_id', $this->batchId)
                ->where('user_id', $user->id)
                ->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'error' => null,
                    'updated_at' => now(),
                ]);
        } catch (Throwable $e) {
            MailLog::where('batch_id', $this->batchId)
                ->where('user_id', $user->id)
                ->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'updated_at' => now(),
                ]);

            throw $e;
        }
    }

    public function backoff(): array
    {
        return [30, 120];
    }

    public function failed(Throwable $e): void
    {
        Log::error('SendPersonalEmailJob FAILED()', [
            'user_id' => $this->userId,
            'message' => $e->getMessage(),
            'class' => get_class($e),
        ]);
    }
}
