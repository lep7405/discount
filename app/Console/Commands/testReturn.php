<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class testReturn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-return';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function test1(): string
    {
        echo 'test1';

        return 'test2';
    }

    public function handle()
    {
        $this->test1();
        echo 2;
    }
}
