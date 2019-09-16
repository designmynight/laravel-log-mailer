Laravel Mail Logger
===============

[![Latest Stable Version](http://img.shields.io/github/release/designmynight/laravel-log-mailer.svg)](https://packagist.org/packages/designmynight/laravel-log-mailer) [![Total Downloads](http://img.shields.io/packagist/dm/designmynight/laravel-log-mailer.svg)](https://packagist.org/packages/designmynight/laravel-log-mailer)
[![StyleCI](https://github.styleci.io/repos/147424037/shield?branch=master)](https://github.styleci.io/repos/147424037)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A service provider to add support for logging via email using Laravels built-in mail provider

![image](https://user-images.githubusercontent.com/12199424/45576336-a93c1300-b86e-11e8-9575-d1e4c5ed5dec.png)


Table of contents
-----------------
* [Installation](#installation)
* [Configuration](#configuration)

Installation
------------

Installation using composer:

```sh
composer require designmynight/laravel-log-mailer
```

### Laravel version Compatibility

 Laravel  | Package |
:---------|:--------|
 6.x      | 1.0.x   |
 5.6.x    | 1.0.x   |

And add the service provider in `config/app.php`:

```php
DesignMyNight\Laravel\Logging\MailableLogServiceProvider::class,
```

For usage with [Lumen](http://lumen.laravel.com), add the service provider in `bootstrap/app.php`.

```php
$app->register(DesignMyNight\Laravel\Logging\MailableLogServiceProvider::class);
```

Configuration
------------

Most configuration options can be automatically populated by environment variables or in config/mailablelog.php, to generate it run php artisan vendor:publish.

To ensure all unhandled exceptions are mailed, set up a mail logging channel and add it to your logging stack in config/logging.php:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        // Add mail to the stack:
        'channels' => ['single', 'mail'],
    ],

    // ...

    // Create a mail logging channel:
    'mail' => [
        'driver' => 'mail',
        // Specify who to mail the log to
        'to' => [
            [
                'address' => 'errors@designmynight.com',
                'name' => 'Error'
            ]
        ],
        // Optionally specify who the log mail was sent by
        // This is overidable in config/mailablelog.php and
        // falls back to your global from in config/mail.php
        'from' => [
            'address' => 'errors@designmynight.com',
            'name' => 'Errors'
        ],
        // Optionally overwrite the mailable template
        // 'mailable' => NewLogMailable::class
    ],
],
```

You can specify multiple channels and change the recipients and customise the email template per channel.

