<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class testValue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-value';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function testValue(&$data)
    {
        $data['name'] = 'name2';
        $data['value'] = 'value2';
    }

    public function handle()
    {
        $data = [
            'name' => 'name1',
            'value' => 'value1',
        ];
        $this->testValue($data);
        dd($data);
    }
}
