<?php

namespace App\Engines\Sanity;

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
    $sanityDeployment->deployment_status = SanityDeployment::STATUS_PENDING_DEPLOYMENT;
    $sanityDeployment->deployment_message = __("Deployment staged at ") . Carbon::now();
    $sanityDeployment->save();
  }

  public function cancelSanityDeployment(SanityDeployment $sanityDeployment)
  {
    $sanityDeployment->deployment_status = SanityDeployment::STATUS_CANCELLED;
    $sanityDeployment->deployment_message = __("Deployment cancelled at ") . Carbon::now();
    $sanityDeployment->save();
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

  public function setDeploymentStatus($sanityDeployment, string $status)
  {
    if(! in_array($status, SanityDeployment::STATUSES)) {
      throw new SanityDeploymentInvalidStatus($status);
    }
    $this->logAboutSanityDeployment($sanityDeployment, "Setting deployment status to '" . $status."'");

    $sanityDeployment->deployment_status = $status;
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
    print $path . "\n";

    return TRUE;
  }

  public function createDeploymentConfiguration(SanityDeployment $sanityDeployment, $createDataset)
  {
    $filename = 'sanityDeployments/' . $sanityDeployment->id . "_" . md5($sanityDeployment->title . $sanityDeployment->id) . ".json";

    $contents = [
      'metadata' => [
        'created' => Carbon::now()
      ],
      'deployment' => $sanityDeployment->toArray(),
      'mainRepo' => $sanityDeployment->sanityMainRepo->toArray()
    ];

    $contents['deployment']['create_dataset'] = $createDataset;

    Storage::put($filename, json_encode($contents));

    return $filename;
  }

  public function logAboutSanityDeployment(SanityDeployment $sanityDeployment, $logLine = NULL)
  {
    $log = array_merge([ 'message' => $logLine ], $sanityDeployment->toArray());

    Log::info(json_encode($log));
  }
}
