<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Engines\SanityEngine;
use App\Models\SanityDeployment;

class SanityDeploymentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanity:deploy {sanityDeployment} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger a sanity deployment';

    protected $engine;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SanityEngine $engine)
    {
        parent::__construct();

        $this->engine = $engine;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sanityDeployment = SanityDeployment::find($this->argument('sanityDeployment'));
        if(!$sanityDeployment) {
            $this->error('Depoyment not found.');
            return;
        }

        if (!$this->option('force')  && !$this->confirm('Do you wish to deploy '.$sanityDeployment->title.'?')) {
            $this->warn('Bailing.');
            return;
        }

        if ($this->option('force') && in_array($sanityDeployment->deployment_status, [
                SanityDeployment::STATUS_DEPLOYING,
            ])) {
                $this->engine->setDeploymentStatus($sanityDeployment, SanityDeployment::STATUS_PENDING_DEPLOYMENT, "Resetting status");
        }

        try {
            $this->info('Deploying ' . $sanityDeployment->title);
            if($this->engine->processDeployment($sanityDeployment)) {
                $sanityDeployment->refresh();
                $this->info("Done. Result Status: " . $this->engine->humanState($sanityDeployment));
                $this->info($sanityDeployment->deployment_message);
                return;
            } else {
                $sanityDeployment->refresh();
                $this->line("Done. Result Status: " . $this->engine->humanState($sanityDeployment));
                $this->error($sanityDeployment->deployment_message);
                return 255;
            }

        } catch(\Exception $ex) {
            $sanityDeployment->refresh();
            $this->info("Done. Result Status: " . $this->engine->humanState($sanityDeployment));
            $this->error($sanityDeployment->deployment_message);
            $this->error($ex->getMessage());
            return 254;
        }


    }
}
