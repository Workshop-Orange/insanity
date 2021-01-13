<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\SanityMainRepo;
use App\Engines\SanityEngine;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SanityMainReposComponent extends Component
{
    use AuthorizesRequests;
    
    public $repos, $title, $git, $branch, $sanity_main_repo_id;
    public $isOpen = 0;

    public function render(Request $request)
    {
        $this->repos = $request->user()->currentTeam->sanityMainRepos;
        return view('livewire.sanity-main-repos');
    }

    public function create()
    {
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
        $this->git = '';
        $this->branch = '';
        $this->sanity_main_repo_id = '';
    }


    public function store(Request $request)
    {
        if($this->sanity_main_repo_id) {
            $repo = SanityMainRepo::find($this->sanity_main_repo_id);
            $this->authorize('update', $repo);
        } else {
            $this->authorize('create', SanityMainRepo::class);
        }
        
        $user = $request->user();
        
        $this->validate([
            'title' => 'required',
            'git' => 'required',
            'branch' => 'required',
        ]);

        SanityMainRepo::updateOrCreate(['id' => $this->sanity_main_repo_id], [
            'title' => $this->title,
            'git' => $this->git,
            'branch' => $this->branch,
            'team_id' => $user->currentTeam->id
        ]);

        session()->flash('message',
            $this->sanity_main_repo_id ? 'Main Repo Updated Successfully.' : 'Main Repo Created Successfully.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function deployAll(SanityEngine $engine, $id)
    {
        $repo = SanityMainRepo::findOrFail($id);
        $engine->stageAllSanityDeployments($repo);
        $repo->refresh();
    }

    public function cancelDeployAll(SanityEngine $engine, $id)
    {
        $repo = SanityMainRepo::findOrFail($id);
        $engine->cancelAllSanityDeployments($repo);
        $repo->refresh();
    }

    public function edit($id)
    {
        $repo = SanityMainRepo::findOrFail($id);
        $this->authorize('update', $repo);

        $this->sanity_main_repo_id = $id;
        $this->title = $repo->title;
        $this->git = $repo->git;
        $this->branch = $repo->branch;
        $this->openModal();
    }

    public function delete($id)
    {
        $repo = SanityMainRepo::find($id);
        $this->authorize('delete', $repo);

        SanityMainRepo::find($id)->delete();
        session()->flash('message', 'Main Repo Deleted Successfully.');
    }
}
