<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Repositories\User\UserRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestCreateAndSave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-create-and-save';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(UserRepository $userRepository)
    {
        $formData = [
            'name' => 'leppp5',
            'email' => 'leppp5@gmail.com',
            'password' => 123456,
            'remember_token' => 'world',
        ];
        // //        User::query()->save($formData);
        //        DB::table('users')->insert($formData);
        // //        User::saved($formData);
        //
        //        User::saved($formData);

        $user = $userRepository->update($formData, ['id' => 19]);
    }
}
