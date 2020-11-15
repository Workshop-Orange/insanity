<?php

namespace App\Engines;

use App\Models\SanityDeployment;
use App\Models\SanityMainRepo;

use App\Engines\Sanity\DeploymentTrait;
use App\Engines\Sanity\MainRepoTrait;

class SanityEngine {
  use DeploymentTrait;
  use MainRepoTrait;
}
