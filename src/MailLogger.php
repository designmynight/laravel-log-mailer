<?php

namespace Shaffe\MailLogChannel;

use Illuminate\Contracts\Mail\Mailable;
use InvalidArgumentException;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Shaffe\MailLogChannel\Mail\Log as MailableLog;
use Shaffe\MailLogChannel\Monolog\Formatters\HtmlFormatter;
use Shaffe\MailLogChannel\Monolog\Handlers\MailableHandler;

class MailLogger
{
    /** @var array */
    protected $config = [];

    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     *
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        if (isset($config['level'])) {
            $config['level'] = Logger::toMonologLevel($config['level']);
        }

        $this->config = array_merge(
            ['level' => Logger::DEBUG, 'bubble' => true],
            $config
        );

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
     * Create the mailable log.
     *
     * @return \Illuminate\Contracts\Mail\Mailable
     */
    protected function buildMailable(): Mailable
    {
        $mailable = $this->config('mailable') ?? MailableLog::class;
        $mailable = new $mailable();

        if (! ($recipients = $this->config('to'))) {
            throw new InvalidArgumentException('"To" address is required.');
        }

        foreach ($recipients as $recipient) {
            $mailable->to(
                $recipient['address'],
                $recipient['name']
            );
        }

        if (! $this->defaultFromAddress()) {
            throw new InvalidArgumentException('"From" address is required. Please check the `from.address` driver\'s config and the `mail.from.address` config.');
        }

        $mailable->from(
            $this->config('from')['address'] ?? $this->defaultFromAddress(),
            $this->config('from')['name'] ?? $this->defaultFromName()
        );

        return $mailable;
    }


    /**
     * Get the default from address.
     *
     * @return string
     */
    protected function defaultFromAddress(): ?string
    {
        return config('mail.from.address');
    }

    /**
     * Get the default from name.
     *
     * @return string
     */
    protected function defaultFromName(): ?string
    {
        return config('mail.from.name');
    }

    /**
     * Get the subject formatter.
     *
     * @return \Monolog\Formatter\LineFormatter
     */
    protected function subjectFormatter(): LineFormatter
    {
        $format = $this->config('subject_format') ?? '[%datetime%] %level_name%: %message%';

        return new LineFormatter($format);
    }

    /**
     * Get the value from the passed in config.
     *
     * @param  string  $field
     *
     * @return mixed
     */
    private function config(string $field)
    {
        return $this->config[$field] ?? null;
    }
}
