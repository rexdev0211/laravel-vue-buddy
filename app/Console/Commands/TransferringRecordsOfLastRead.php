<?php

namespace App\Console\Commands;

use App\Models\Event\EventMessagesRead;
use Illuminate\Console\Command;

class TransferringRecordsOfLastRead extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transfer-last-read { --chunk= }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring records from the "event_messages_read" table to Mongo';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->option('chunk')) {
            $this->info('No chunk parameter');
            return false;
        }

        if ($this->confirm('Do you wish to continue?')) {
            $this->transfer((int) $this->option('chunk'));
        }
    }

    public function transfer(int $chunk)
    {
        $this->info('Start...');

        \DB::connection('mysql')
            ->table('event_messages_read')
            ->orderBy('user_id')
            ->chunk($chunk, function ($lastRead) {
                foreach ($lastRead as $read) {

                    EventMessagesRead::create([
                        'event_id' => $read->event_id,
                        'user_id'  => $read->user_id,
                        'latest_read' => $read->latest_read
                    ]);

                }
            });

        $this->info('Finish...');
    }
}
