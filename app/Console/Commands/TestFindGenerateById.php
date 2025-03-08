<?php

namespace App\Console\Commands;

use App\Repositories\Generate\GenerateRepository;
use Illuminate\Console\Command;

class TestFindGenerateById extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-find-generate-by-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(GenerateRepository $generateRepository)
    {
        $data = $generateRepository->find(1);
        dd($data);
    }
}
