<?php

namespace DesignMyNight\Laravel\Logging\Monolog\Handlers;

use Illuminate\Mail\Mailable;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\MailHandler;
use Monolog\Logger;

class MailableHandler extends MailHandler
{
    /**
     * Create the mailable handler.
     *
     * @param Mailable      $mailable
     * @param LineFormatter $subjectFormatter
     * @param int     $level  The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     *
     * @return void
     */
    public function __construct(Mailable $mailable, LineFormatter $subjectFormatter, int $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
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
