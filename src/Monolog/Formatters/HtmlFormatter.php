<?php

namespace Shaffe\MailLogChannel\Monolog\Formatters;

class HtmlFormatter extends \Monolog\Formatter\HtmlFormatter
{
    protected function addRow(string $th, string $td = ' ', bool $escapeTd = true): string
    {
        return strtr(
            parent::addRow($th, $td, $escapeTd),
            [
                '<td style="' => '<td style="white-space: nowrap;',
                '<th style="' => '<th style="white-space: nowrap;',
            ]
        );
    }
}
