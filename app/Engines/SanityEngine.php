<?php

namespace App\Engines;

use App\Models\SanityDeployment;
use App\Models\SanityMainRepo;

use App\Engines\Sanity\DeploymentTrait;
use App\Engines\Sanity\MainRepoTrait;
use Illuminate\Support\Facades\Log;

class SanityEngine {
  use DeploymentTrait;
  use MainRepoTrait;

  protected $insanityId;
  protected $roboPath;
  protected $roboWorkingPath;

  public function __construct($config) {
    $this->insanityId = $config['insanityId'];
    $this->roboPath = $config['roboli'];
    $this->roboWorkingPath = $config['roboliwd'];
  }
}
