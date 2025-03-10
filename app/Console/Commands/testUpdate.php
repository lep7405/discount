<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class testUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-update';

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
//        $user=User::query()->where('id',1)->update([
//            'name'=>'phuoc2',
//            'app_name'=>'cs'
//        ]);
        $user=User::create();
        $user=User::query()->create([
            'name'=>'phuoc2',
            'app_name'=>'cs',
            'password'=>Hash::make('phuoc2'),
            'email'=>'phuoc3@gmail.com'
        ]);
        dd(1);
    }
}
