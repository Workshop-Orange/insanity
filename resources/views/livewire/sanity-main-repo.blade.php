@inject('engine', 'App\Engines\SanityEngine')
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight text-underline">
        <a href="{{ route('sanityMainRepos') }}">Sanity Repositories</a> /
        <a href="{{ route('sanityMainRepo', $sanityMainRepo->id) }}">{{ $sanityMainRepo->title }}</a>
    </h2>
</x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
            @if (session()->has('message'))
                <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md my-3" role="alert">
                  <div class="flex">
                    <div>
                      <p class="text-sm">{{ session('message') }}</p>
                    </div>
                  </div>
                </div>
            @endif

            <button wire:click="create()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded my-3">Create New Deployment</button>
            @if($isOpen)
                @include('livewire.sanity-deployment-create')
            @endif

            <table class="table-fixed w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Deployment</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deployments as $deployment)
                    <tr>
                        <td class="border px-4 py-2">
                            {{ $deployment->title }}
                            <div class="text-sm italic {{$deployment->sanity_api_token ? 'text-green-500' : ''}}">
                                @if($deployment->sanity_api_token)
                                    {{ _("Sanity token set") }}
                                @else
                                    {{ _("Sanity token not set") }}
                                @endif
                            </div>
                            <div class="text-sm italic {{$deployment->sanity_project_id ? 'text-green-500' : 'text-red-500'}}">
                                @if($deployment->sanity_project_id)
                                    {{ _("Sanity Project: " . $deployment->sanity_project_id) }}
                                @else
                                    {{ _("Sanity not detected") }}
                                @endif
                            </div>
                        </td>
                        <td class="border px-4 py-2">
                            <div class="font-bold {{ $engine->isDeploymentInProgress($deployment) ? "animate-pulse text-green-900" : ""  }}">
                            {{ $engine->humanState($deployment) }}
                            </div>
                            <div class="text-sm">
                                @if($deployment->deployment_message)
                                        {{ $deployment->deployment_message }}
                                @endif
                            </div>
                        </td>
                        <td class="border px-4 py-2">
                            <button wire:click="edit({{ $deployment->id }})" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</button>
                            @if($engine->isDeploymentInProgress($deployment))
                                <button onclick="confirm('Are you sure you want to cancel the deployment?') || event.stopImmediatePropagation()" wire:click="cancelDeploy({{ $deployment->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Cancel Deployment</button>
                            @else
                                <button onclick="confirm('Are you sure you want to trigger a deployment?') || event.stopImmediatePropagation()" wire:click="deploy({{ $deployment->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Deploy</button>
                                <button onclick="confirm('Are you sure you want to delete the deployment?') || event.stopImmediatePropagation()" wire:click="delete({{ $deployment->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
