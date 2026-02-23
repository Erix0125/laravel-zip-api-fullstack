<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExportPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $title,
        public string $bodyText,
        public string $pdfFilename,
        public string $pdfData,
    ) {}

    public function build(): self
    {
        return $this
            ->subject($this->title)
            ->view('emails.export-pdf')
            ->with([
                'title' => $this->title,
                'bodyText' => $this->bodyText,
                'sentAt' => now()->format('Y-m-d H:i:s'),
            ])
            ->attachData($this->pdfData, $this->pdfFilename, [
                'mime' => 'application/pdf',
            ]);
    }
}
