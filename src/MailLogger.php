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
        $mailableClass = $this->config('mailable') ?? MailableLog::class;
        /** @var \Illuminate\Contracts\Mail\Mailable $mailable */
        $mailable = new $mailableClass();

        if (! ($recipients = $this->buildRecipients())) {
            throw new InvalidArgumentException('"To" address is required. Please check the `to` driver\'s logging config.');
        }

        $mailable->to($recipients);

        if (! $this->defaultFromAddress() && ! isset($this->config('from')['address'])) {
            throw new InvalidArgumentException('"From" address is required. Please check the `from.address` driver\'s config and the `mail.from.address` config.');
        }

        $mailable->from(
            $this->config('from')['address'] ?? $this->defaultFromAddress(),
            $this->config('from')['name'] ?? $this->defaultFromName()
        );

        return $mailable;
    }

    protected function buildRecipients(): array
    {
        if (! ($to = $this->config('to'))) {
            return [];
        }

        $recipients = [];
        foreach ((array)$to as $emailOrIndex => $nameOrEmail) {
            if (is_array($nameOrEmail)) {
                $email = $nameOrEmail['email'] ?? $nameOrEmail['address'] ?? null;
                if ($email) {
                    $recipients[] = [
                        'email' => $email,
                        'name' => $nameOrEmail['name'] ?? null,
                    ];
                }
            } elseif (is_string($emailOrIndex)) {
                $recipients[] = [
                    'email' => $emailOrIndex,
                    'name' => $nameOrEmail,
                ];
            } elseif (is_string($nameOrEmail)) {
                $recipients[] = [
                    'email' => $nameOrEmail,
                    'name' => null,
                ];
            }
        }

        return $recipients;
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
