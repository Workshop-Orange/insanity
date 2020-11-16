<?php

namespace App\Engines;

use Illuminate\Support\Facades\Http;
use App\Exceptions\SanityTokenNotProvidedException;
use App\Models\SanityDeployment;

class SanityEngineApi
{
  protected $apiBase = "https://api.sanity.io/v1";
  protected $token;
  protected $insanityId;

  public function __construct($insanityId) {
    $this->insanityId = $insanityId;
  }

  public function apiToken($apiToken)
  {
    $this->token = $apiToken;
    return $this;
  }

  public function doAPIGet($apiSection, $apiBase = null)
  {
    if(!$this->token) {
      throw new SanityTokenNotProvidedException(__("Sanity API Token Missing"));
    }

    if(! preg_match("/^\//", $apiSection)) {
      $apiSection = "/" . $apiSection;
    }

    $apiUrl = ($apiBase ? $apiBase : $this->apiBase) . $apiSection;

    $response = Http::withToken($this->token)->get($apiUrl);

    return (object) [
      'url' => $apiUrl,
      'status' => $response->status(),
      'data' => $response->json()
    ];
  }

  public function doAPIPost($apiSection, $data, $apiBase = null)
  {
    if(!$this->token) {
      throw new SanityTokenNotProvidedException(__("Sanity API Token Missing"));
    }

    if(! preg_match("/^\//", $apiSection)) {
      $apiSection = "/" . $apiSection;
    }

    $apiUrl = ($apiBase ? $apiBase : $this->apiBase) . $apiSection;

    $response = Http::withToken($this->token)->post($apiUrl, $data);

    return (object) [
      'url' => $apiUrl,
      'status' => $response->status(),
      'data' => $response->json()
    ];
  }

  public function doAPIPatch($apiSection, $data, $apiBase = null)
  {
    if(!$this->token) {
      throw new SanityTokenNotProvidedException(__("Sanity API Token Missing"));
    }

    if(! preg_match("/^\//", $apiSection)) {
      $apiSection = "/" . $apiSection;
    }

    $apiUrl = ($apiBase ? $apiBase : $this->apiBase) . $apiSection;

    $response = Http::withToken($this->token)->patch($apiUrl, $data);

    return (object) [
      'url' => $apiUrl,
      'status' => $response->status(),
      'data' => $response->json()
    ];
  }

  public function verifySanityToken()
  {
    $projects = $this->doAPIGet("/projects");

    if($projects->status == 200) {
      return TRUE;
    }

    return FALSE;
  }

  public function doesProjectExistForDeployment(SanityDeployment $sanityDeployment)
  {
    $projectNamePrefix = $sanityDeployment->sanity_project_name_prefix;
    $projects = $this->projects();
    foreach($projects as $project)
    {
      if(preg_match("/^$projectNamePrefix/", $project['displayName'])) {
        return TRUE;
      }
    }

    return FALSE;
  }

  public function linkProjectToDeployment(SanityDeployment $sanityDeployment)
  {
    $projectNamePrefix = $sanityDeployment->sanity_project_name_prefix;
    $projects = $this->projects();
    foreach($projects as $project)
    {
      if(preg_match("/^$projectNamePrefix/", $project['displayName'])) {
        $sanityProjectId = $project['id'];
        $sanityDeployment->sanity_project_id = $sanityProjectId;
        $sanityDeployment->save();
      }
    }
  }

  public function doesProjectDatasetExistForDeployment(SanityDeployment $sanityDeployment)
  {
    $response = $this->doAPIGet("/insanity?query=*[0]", "https://" . $sanityDeployment->sanity_project_id . ".api.sanity.io/v1/data/query");
    return $response->status == 200;
  }

  public function createProject(SanityDeployment $sanityDeployment)
  {
    $projectName = $sanityDeployment->sanity_project_name;
    if(! $this->doesProjectExistForDeployment($sanityDeployment)) {
      $project = $this->doApiPost("/projects", ["displayName" => $projectName]);
      $sanityProjectId = $project->data['id'];
      $sanityDeployment->sanity_project_id = $sanityProjectId;
      $sanityDeployment->save();

      $updateData = [
        "displayName" => $projectName,
        'studioHost' => $sanityDeployment->sanity_studio_host
      ];

      $project = $this->doApiPatch("/projects/" . $sanityProjectId, $updateData);
    }

    return TRUE;
  }

  public function projects()
  {
    $projects = $this->doAPIGet("/projects");
    return collect($projects->data);
  }
}
