<?php

namespace App\Engines\Sanity;

use App\Models\SanityDeployment;
use App\Models\SanityMainRepo;

trait MainRepoTrait
{
  public function stageAllSanityDeployments(SanityMainRepo $sanityMainRepo)
  {
    foreach($sanityMainRepo->sanityDeployments as $deployment)
    {
      $this->stageSanityDeployment($deployment);
    }
  }

  public function cancelAllSanityDeployments(SanityMainRepo $sanityMainRepo)
  {
    foreach($sanityMainRepo->sanityDeployments as $deployment)
    {
      $this->cancelSanityDeployment($deployment);
    }
  }

  public function hasDeploymentsInProgress(SanityMainRepo $sanityMainRepo)
  {
    if($this->countDeploymentsInProgress($sanityMainRepo) > 0) {
      return TRUE;
    }

    return FALSE;
  }

  public function countDeploymentsInProgress(SanityMainRepo $sanityMainRepo)
  {
    $total = 0;
    foreach($sanityMainRepo->sanityDeployments as $deployment)
    {
      if($this->isDeploymentInProgress($deployment)) $total++;
    }

    return $total;
  }
}
