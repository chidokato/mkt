<?php

namespace App\Console\Commands;

use App\Jobs\SendPersonalEmailJob;
use App\Models\User;
use Illuminate\Console\Command;

class SendBulkPersonalEmails extends Command
{
    protected $signature = 'mail:bulk-personal {--subject=Thông báo} {--content=} {--chunk=500}';
    protected $description = 'Send personalized emails to users in DB';

    public function handle(): int
    {
        $subject = (string) $this->option('subject');
        $content = (string) $this->option('content');
        $chunk   = (int) $this->option('chunk');

        if (trim($content) === '') {
            $this->error('Missing --content=');
            return self::FAILURE;
        }

        User::query()
            ->select('id')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id')
            ->chunkById($chunk, function ($users) use ($subject, $content) {
                foreach ($users as $u) {
                    SendPersonalEmailJob::dispatch($u->id, $subject, $content)
                        ->onQueue('mail');
                }
            });

        $this->info('Dispatched jobs to queue.');
        return self::SUCCESS;
    }
}
