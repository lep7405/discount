<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class testArgs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-args';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function test(...$args)
    {
        dd($args);
    }

    public function handle()
    {
        //        $this->test('hello1');
        $this->test((object) [
            'name' => 'hello2',
        ]);
    }
}
