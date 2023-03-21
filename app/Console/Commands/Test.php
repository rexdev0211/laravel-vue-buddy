<?php

namespace App\Console\Commands;

use App\Repositories\MessageRepository;
use App\User;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:test
                                    {--userId=: User Id }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test command';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle(): int
    {
        // ...

        return 0;
    }
}
