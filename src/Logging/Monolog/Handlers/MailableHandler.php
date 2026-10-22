<?php

namespace DesignMyNight\Laravel\Logging\Monolog\Handlers;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Mailable;
use Monolog\Level;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\MailHandler;

class MailableHandler extends MailHandler
{
    protected Mailer $mailer;

    protected LineFormatter $subjectFormatter;

    protected Mailable $mailable;

    /**
     * Create the mailable handler.
     *
     * @param Mailable      $mailable
     * @param LineFormatter $subjectFormatter
     * @param int|string|Level $level  The minimum logging level at which this handler will be triggered
     * @param bool             $bubble Whether the messages that are handled can bubble up the stack or not
     *
     * @return void
     */
    public function __construct(Mailable $mailable, LineFormatter $subjectFormatter, int|string|Level $level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->mailer = app()->make('mailer');
        $this->subjectFormatter = $subjectFormatter;
        $this->mailable = $mailable;
    }

    /**
     * Set the subject.
     *
     * @param array<\Monolog\LogRecord> $records
     *
     * @return void
     */
    protected function setSubject(array $records): void
    {
        $this->mailable->subject($this->subjectFormatter->format($this->getHighestRecord($records)));
    }

    /**
     * {@inheritdoc}
     */
    protected function send(string $content, array $records): void
    {
        $this->mailable->with([
            'content' => $content,
            'records' => $records,
        ]);

        $this->setSubject($records);

        $this->mailer->send($this->mailable);
    }
}
