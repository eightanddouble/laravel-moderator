<?php

namespace EightAndDouble\LaravelModerator\Commands;

use Illuminate\Console\Command;

class LaravelModeratorCommand extends Command
{
    public $signature = 'laravel-moderator';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
