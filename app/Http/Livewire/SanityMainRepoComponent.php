<?php

namespace App\Http\Livewire;

use Livewire\Component;

use Illuminate\Support\Facades\Auth;
use App\Models\SanityMainRepo;
use App\Models\SanityDeployment;
use App\Engines\SanityEngine;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class SanityMainRepoComponent extends Component
{
    use AuthorizesRequests;

    public $deployments, $title, $sanity_api_token, $sanity_deployment_id;
    public $sanityMainRepo;
    public $isOpen = 0;
    protected $sanityEngine;

    public function getListeners()
    {
        return [
            "echo:sanityMainRepo.". $this->sanityMainRepo->id .",SanityDeploymentStatusChanged" => 'refreshDeployments',
        ];
    }

    public function mount(SanityMainRepo $sanityMainRepo)
    {
        $this->sanityMainRepo = $sanityMainRepo;
    }

    public function render()
    {
        $this->authorize('view', $this->sanityMainRepo);
        $this->deployments = $this->sanityMainRepo->sanityDeployments;

        return view('livewire.sanity-main-repo');
    }

    public function create()
    {
        $this->authorize('create', SanityDeployment::class);

        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields() {
        $this->title = '';
        $this->sanity_api_token = '';
        $this->sanity_deployment_id = '';
    }

    public function refreshDeployments()
    {
        $this->sanityMainRepo->refresh();
        $this->deployments = $this->sanityMainRepo->sanityDeployments;

    }

    public function store()
    {
        $user = Auth::user();
        $this->validate([
            'title' => 'required',
            'sanity_api_token' => 'required',
        ]);

        if($this->sanity_deployment_id) {
            $deploy = SanityDeployment::find($this->sanity_deployment_id);
            $this->authorize('update', $deploy);
        } else {
            $this->authorize('create', SanityDeployment::class);
        }

        SanityDeployment::updateOrCreate(['id' => $this->sanity_deployment_id
        ], [
            'title' => $this->title,
            'sanity_api_token' => $this->sanity_api_token,
            'sanity_main_repo_id' => $this->sanityMainRepo->id,
            'team_id' => $user->currentTeam->id
        ]);

        session()->flash('message',
            $this->sanity_deployment_id ? 'Deployment Updated Successfully.' : 'Deployment Created Successfully.');

        $this->closeModal();
        $this->resetInputFields();
        $this->sanityMainRepo->refresh();
    }

    public function deploy(SanityEngine $engine, $id)
    {
        $deploy = SanityDeployment::findOrFail($id);
        $engine->stageSanityDeployment($deploy);
        $this->sanityMainRepo->refresh();
    }

    public function cancelDeploy(SanityEngine $engine, $id)
    {
        $deploy = SanityDeployment::findOrFail($id);
        $engine->cancelSanityDeployment($deploy);
        $this->sanityMainRepo->refresh();
    }

    public function edit($id)
    {
        $deploy = SanityDeployment::findOrFail($id);
        $this->authorize('update', $deploy);

        $this->sanity_deployment_id = $id;
        $this->title = $deploy->title;
        $this->sanity_api_token = $deploy->sanity_api_token;
        $this->openModal();
    }

    public function delete($id)
    {
        $deploy = SanityDeployment::findOrFail($id);
        $this->authorize('delete', $deploy);

        $deploy->delete();
        session()->flash('message', 'Deployment Deleted Successfully.');
        $this->sanityMainRepo->refresh();
    }
}
