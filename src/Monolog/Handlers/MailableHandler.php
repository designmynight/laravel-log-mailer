<?php

namespace Shaffe\MailLogChannel\Monolog\Handlers;

use Illuminate\Mail\Mailable;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\MailHandler;
use Monolog\Logger;

class MailableHandler extends MailHandler
{
    /** @var \Illuminate\Mail\Mailable */
    protected $mailable;

    /** @var \Illuminate\Contracts\Mail\Mailer */
    protected $mailer;

    /** @var \Monolog\Formatter\LineFormatter */
    protected $subjectFormatter;

    /**
     * Create the mailable handler.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @param  \Monolog\Formatter\LineFormatter  $subjectFormatter
     * @param  int  $level  The minimum logging level at which this handler will be triggered
     * @param  bool  $bubble  Whether the messages that are handled can bubble up the stack or not
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(
        Mailable $mailable,
        LineFormatter $subjectFormatter,
        int $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
        $this->mailable = $mailable;
        $this->subjectFormatter = $subjectFormatter;
        $this->mailer = app()->make('mailer');
    }

    /**
     * Set the subject.
     *
     * @param  array  $records
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
        $this->mailable->with(
            [
                'content' => $content,
                'records' => $records,
            ]
        );

        $this->setSubject($records);

        $this->mailer->send($this->mailable);
    }
}
