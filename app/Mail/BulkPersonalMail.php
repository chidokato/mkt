<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkPersonalMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $content;
    public string $subjectLine;

    public function __construct(string $name, string $content, string $subjectLine)
    {
        $this->name = $name;
        $this->content = $content;
        $this->subjectLine = $subjectLine;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view('emails.bulk.personal', [
                'name' => $this->name,
                'content' => $this->content,
            ])
            ->withSwiftMessage(function ($message) {
                $headers = $message->getHeaders();
                $fromAddress = (string) config('mail.from.address');

                if (!empty($fromAddress)) {
                    $headers->addTextHeader('List-Unsubscribe', "<mailto:{$fromAddress}?subject=unsubscribe>");
                }

                $headers->addTextHeader('Precedence', 'bulk');
                $headers->addTextHeader('X-Auto-Response-Suppress', 'All');
            });
    }
}
