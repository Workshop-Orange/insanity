<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\SanityMainRepo;
use App\Engines\SanityEngine;

class SanityMainReposComponent extends Component
{
    public $repos, $title, $git, $branch, $sanity_main_repo_id;
    public $isOpen = 0;

    public function render()
    {
        $this->repos = SanityMainRepo::all();
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


    public function store()
    {
        $user = Auth::user();
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
        $this->sanity_main_repo_id = $id;
        $this->title = $repo->title;
        $this->git = $repo->git;
        $this->branch = $repo->branch;
        $this->openModal();
    }

    public function delete($id)
    {
        SanityMainRepo::find($id)->delete();
        session()->flash('message', 'Main Repo Deleted Successfully.');
    }
}
