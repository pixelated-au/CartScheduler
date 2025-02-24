<?php

namespace App\Lib;

use Symfony\Component\Console\Output\StreamOutput;

class FilterAnsiEscapeSequencesStreamedOutput extends StreamOutput
{
    protected function doWrite(string $message, bool $newline): void
    {
        // Strip ANSI escape sequences (including color codes)
        $message = preg_replace('/\x1b?\[[0-9;]*m/', '', $message);

        // Add custom formatting
        if ($newline) {
            $message .= PHP_EOL;
        }

        // Write to stream
        parent::doWrite($message, false);

        // Ensure output is flushed immediately
        flush();
    }
}
