<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class testCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-collection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $co = collect([
            [
                'name' => 'John',
                'age' => 25,
                'name1' => [
                    'name' => 'John3',
                ],
            ],
            [
                'name' => 'John2',
                'age' => 25,
            ],
        ]);
        dd($co->pluck('name1.name')->filter());
    }
}
