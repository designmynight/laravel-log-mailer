<?php

namespace DesignMyNight\Laravel\Tests;

use DesignMyNight\Laravel\Logging\Mail\Log as MailableLog;
use DesignMyNight\Laravel\Logging\MailableLogServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Orchestra\Testbench\TestCase;

class MailableLogTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [MailableLogServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('mail.default', 'array');
        $app['config']->set('logging.channels.mail', [
            'driver' => 'mail',
            'level' => 'error',
            'bubble' => true,
            'to' => [['address' => 'errors@example.com', 'name' => 'Errors']],
            'from' => ['address' => 'logger@example.com', 'name' => 'Logger'],
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_error_log_is_sent_to_the_configured_addresses_with_subject_and_body(): void
    {
        Log::channel('mail')->error('Something broke');

        Mail::assertSentCount(1);
        Mail::assertSent(
            MailableLog::class,
            fn ($mail) => $mail->hasTo('errors@example.com', 'Errors')
                && $mail->hasFrom('logger@example.com', 'Logger')
                && str_contains($mail->subject, 'ERROR: Something broke')
                && str_contains($mail->render(), 'Something broke')
        );
    }

    public function test_critical_log_is_sent_above_the_configured_level(): void
    {
        Log::channel('mail')->critical('Something critical');

        Mail::assertSentCount(1);
        Mail::assertSent(
            MailableLog::class,
            fn ($mail) => str_contains($mail->subject, 'CRITICAL:')
        );
    }

    public function test_warning_log_below_the_configured_level_is_not_sent(): void
    {
        Log::channel('mail')->warning('Something happened');

        Mail::assertNothingSent();
    }

    public function test_custom_subject_format_is_used(): void
    {
        config(['mailablelog.subject_format' => '%level_name%: %message%']);

        Log::channel('mail')->error('Something broke');

        Mail::assertSent(
            MailableLog::class,
            fn ($mail) => $mail->subject === 'ERROR: Something broke'
        );
    }
}
