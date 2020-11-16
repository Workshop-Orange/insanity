<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Engines\SanityEngineApi;

class SanityProjectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanity:list-projects {token?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List the Sanity.io projects in the account';

    protected $engineApi;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SanityEngineApi $engineApi)
    {
        parent::__construct();

        $this->engineApi = $engineApi;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $token = $this->argument('token');
        if(!$token) {
            $this->warn("Reading API Token from SANITY_API_TOKEN");
            $token = env('SANITY_API_TOKEN');
        }

        if($this->engineApi
            ->apiToken($token)
            ->verifySanityToken()) {
                $this->info("Sanity token verified");
            } else {
                $this->error("Sanity token verification failed.");
                return 255;
            }

        $data = $this->engineApi
            ->apiToken($token)
            ->projects();

        $headers = ['ID', 'Name','Studio Host'];
        $projects = $data->map(function($item, $key) {
            return collect($item)->only(['id', 'displayName','studioHost'])->toArray();
        });

        $this->table($headers, $projects);
    }
}
