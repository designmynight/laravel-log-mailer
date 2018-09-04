<?php

namespace DesignMyNight\Laravel\Logging\Monolog\Handlers;

use Illuminate\Mail\Mailable;
use Monolog\Handler\MailHandler;
use Monolog\Formatter\LineFormatter;

class MailableHandler extends MailHandler
{
    /**
     * Create the mailable handler.
     *
     * @param Mailable      $mailable
     * @param LineFormatter $subjectFormatter
     *
     * @return void
     */
    public function __construct(Mailable $mailable, LineFormatter $subjectFormatter)
    {
        $this->mailer = app()->make('mailer');
        $this->subjectFormatter = $subjectFormatter;
        $this->mailable = $mailable;
    }

    /**
     * Set the subject.
     *
     * @param array $records
     *
     * @return void
     */
    protected function setSubject(array $records)
    {
        $this->mailable->subject($this->subjectFormatter->format($this->getHighestRecord($records)));
    }

    /**
     * {@inheritdoc}
     */
    protected function send($content, array $records)
    {
        $this->mailable->with([
            'content' => $content,
            'records' => $records,
        ]);

        $this->setSubject($records);

        $this->mailer->send($this->mailable);
    }
}
