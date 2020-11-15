<?php

namespace App\Engines\Sanity;

use App\Models\SanityDeployment;
use App\Models\SanityMainRepo;
use Carbon\Carbon;

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
        return __("Pending deployment");
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
}
