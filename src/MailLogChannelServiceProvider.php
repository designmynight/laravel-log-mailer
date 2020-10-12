<?php

namespace Shaffe\MailLogChannel;

use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider;

class MailLogChannelServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app['log'] instanceof LogManager) {
            $this->app['log']->extend('mail', function ($app, array $config) {
                $logger = new MailLogger();

                return $logger($config);
            });
        }
    }
}
