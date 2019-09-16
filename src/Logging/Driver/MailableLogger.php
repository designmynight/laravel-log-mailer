<?php

namespace DesignMyNight\Laravel\Logging\Driver;

use DesignMyNight\Laravel\Logging\Mail\Log as MailableLog;
use DesignMyNight\Laravel\Logging\Monolog\Handlers\MailableHandler;
use Illuminate\Contracts\Mail\Mailable;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

class MailableLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param array $config
     *
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
         $this->config = array_merge([
            'level' => Logger::DEBUG,
            'bubble' => true
        ], $config );

        $mailHandler = new MailableHandler(
            $this->buildMailable(),
            $this->subjectFormatter(),
            $this->config('level'),
            $this->config('bubble')
        );

        $mailHandler->setFormatter(new HtmlFormatter());

        return new Logger('mailable', [$mailHandler]);
    }

    /**
     * Get the default from address.
     *
     * @return string
     */
    protected function defaultFromAddress(): string
    {
        return config('mailablelog.from.address') ?? config('mail.from.address');
    }

    /**
     * Get the default from name.
     *
     * @return string
     */
    protected function defaultFromName(): string
    {
        return config('mailablelog.from.name') ?? config('mail.from.name');
    }

    /**
     * Get the subject formatter.
     *
     * @return LineFormatter
     */
    protected function subjectFormatter(): LineFormatter
    {
        $format = $this->config('subject_format') ?? config('mailablelog.subject_format');

        return new LineFormatter($format);
    }

    /**
     * Create the mailable log.
     *
     * @return Mailable
     */
    protected function buildMailable(): Mailable
    {
        $mailable = $this->config('mailable') ?? MailableLog::class;
        $mailable = new $mailable();

        if (empty($recipients = $this->config('to'))) {
            throw new InvalidArgumentException('To addresses required');
        }

        foreach ($recipients as $recipient) {
            $mailable->to(
                $recipient['address'],
                $recipient['name']
            );
        }

        $mailable->from(
            $this->config('from')['address'] ?? $this->defaultFromAddress(),
            $this->config('from')['name'] ?? $this->defaultFromName()
        );

        return $mailable;
    }

    /**
     * Get the value from the passed in config.
     *
     * @param string $field
     *
     * @return mixed
     */
    private function config(string $field)
    {
        return $this->config[$field] ?? null;
    }
}
