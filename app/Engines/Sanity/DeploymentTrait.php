<?php

namespace App\Engines\Sanity;

use App\Events\SanityDeploymentStatusChanged;
use App\Engines\SanityEngineApi;
use App\Models\SanityDeployment;
use App\Models\SanityMainRepo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Exceptions\SanityDeploymentInvalidStatus;
use App\Exceptions\SanityDeploymentAlreadyDeploying;
use Illuminate\Support\Facades\Storage;


trait DeploymentTrait {
  public function stageSanityDeployment(SanityDeployment $sanityDeployment)
  {
    $this->setDeploymentStatus($sanityDeployment, SanityDeployment::STATUS_PENDING_DEPLOYMENT, __("Deployment staged at ") . Carbon::now());
  }

  public function cancelSanityDeployment(SanityDeployment $sanityDeployment)
  {
    $this->setDeploymentStatus($sanityDeployment, SanityDeployment::STATUS_CANCELLED, __("Deployment cancelled at ") . Carbon::now());
  }

  public function isDeploymentInProgress(SanityDeployment $sanityDeployment)
  {
    if(in_array($sanityDeployment->deployment_status, [
        SanityDeployment::STATUS_PENDING_DEPLOYMENT,
        SanityDeployment::STATUS_DEPLOYING
      ])) {
        return TRUE;
      }

      return FALSE;
  }

  public function humanState($sanityDeployment)
  {
    switch($sanityDeployment->deployment_status) {
      case 'undeployed':
        return __("Not deployed");
        break;
      case 'pending':
        return __("Pending deployment");
        break;
      case 'deploying':
        return __("Running deployment");
        break;
      case 'deployed':
        return __("Deployed");
        break;
      case 'failed':
        return __("Failed");
        break;
      case 'cancelled':
        return __("Cancelled");
        break;
      default:
      return __("Unknown");
    }
  }

  public function setDeploymentStatus($sanityDeployment, string $status, string $message = null)
  {
    if(! in_array($status, SanityDeployment::STATUSES)) {
      throw new SanityDeploymentInvalidStatus($status);
    }

    $triggerEvent = FALSE;
    if($sanityDeployment->deployment_status != $status) {
      $triggerEvent = TRUE;
    }

    $this->logAboutSanityDeployment($sanityDeployment, "Setting deployment status to '" . $status."'");
    $sanityDeployment->deployment_status = $status;

    if($message) {
      $this->logAboutSanityDeployment($sanityDeployment, $message);
      $sanityDeployment->deployment_message = $status;
    }

    if($triggerEvent) {
      event(new SanityDeploymentStatusChanged($sanityDeployment));
    }

    $sanityDeployment->save();

    return $sanityDeployment;
  }

  public function processDeployment(SanityDeployment $sanityDeployment)
  {
    $engineApi = app(SanityEngineApi::class)->apiToken($sanityDeployment->sanity_api_token);
    $this->logAboutSanityDeployment($sanityDeployment, "Starting deployment " . $sanityDeployment->sanity_api_token);

    if($sanityDeployment->deployment_status == SanityDeployment::STATUS_DEPLOYING) {
      throw new SanityDeploymentAlreadyDeploying("Deployment is already deploying");
    }

    $this->setDeploymentStatus($sanityDeployment, SanityDeployment::STATUS_DEPLOYING);

    if(!$engineApi->doesProjectExistForDeployment($sanityDeployment)) {
      $engineApi->createProject($sanityDeployment);
    } else {
      if(! $sanityDeployment->sanity_project_id) {
        $engineApi->linkProjectToDeployment($sanityDeployment);
      }
    }

    $createDataset = ! $engineApi->doesProjectDatasetExistForDeployment($sanityDeployment);
    $deploymentConfigPath = $this->createDeploymentConfiguration($sanityDeployment, $createDataset);
    $path = Storage::path($deploymentConfigPath);

    $cwd = getcwd();
    chdir("/Users/bryan/Projects/insanity/robo");

    $cmd = "/Users/bryan/.composer/vendor/bin/robo sanity:deploy " . $path;
    $process = proc_open($cmd, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes);
    $proc_status = proc_get_status($process);
    $exit_code = proc_close($process);
    chdir($cwd);

    if($exit_code == 0) {
      $this->setDeploymentStatus($sanityDeployment, SanityDeployment::STATUS_DEPLOYED);
      return TRUE;
    } else {
      $this->setDeploymentStatus($sanityDeployment, SanityDeployment::STATUS_FAILED);
      return FALSE;
    }

    return TRUE;
  }

  public function createDeploymentConfiguration(SanityDeployment $sanityDeployment, $createDataset)
  {
    $filename = 'sanityDeployments/' . $sanityDeployment->id . "_" . md5($sanityDeployment->title . $sanityDeployment->id) . "-". uniqid().".json";

    $contents = [
      'metadata' => [
        'created' => Carbon::now()
      ],
      'deployment' => $sanityDeployment->toArray(),
      'mainRepo' => $sanityDeployment->sanityMainRepo->toArray()
    ];

    $contents['deployment']['create_dataset'] = $createDataset;

    Storage::put($filename, json_encode($contents));
    $path = Storage::path($filename);

    $this->logAboutSanityDeployment($sanityDeployment, "Created deployment file: " . $path);
    return $filename;
  }

  public function logAboutSanityDeployment(SanityDeployment $sanityDeployment, $logLine = NULL)
  {
    $log = array_merge([ 'message' => $logLine ], $sanityDeployment->toArray());

    Log::info(json_encode($log));
  }
}
