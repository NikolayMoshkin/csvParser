<?php

namespace App\Console\Commands;

use App\Connection;
use App\Imports\ConnectionsImport;
use App\Imports\csvImport;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class csvParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import {--filePath=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'csvParser to DB';

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
     * @return mixed
     */
    public function handle()
    {
        $this->info('Import starts');
//        $result = Excel::import(new ConnectionsImport, $this->option('filePath'));
        csvImport::start($this->option('filePath'));

        $this->info('Done!');
    }
}
