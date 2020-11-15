@inject('engine', 'App\Engines\SanityEngine')

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Sanity Repositories
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

            <button wire:click="create()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded my-3">Create New Main Repository</button>
            @if($isOpen)
                @include('livewire.sanity-main-repos-create')
            @endif

            <table class="table-fixed w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Repo</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($repos as $repo)
                    <tr>
                        <td class="border px-4 py-2">
                            <div>
                                <a href="{{ route("sanityMainRepo", $repo->id) }}" class="text-blue-500 hover:text-blue-700">
                                    {{ $repo->title }}
                                </a>
                            </div>
                            <div class="text-xs italic">
                                Git: {{ $repo->git }}
                            </div>
                        </td>
                        <td class="border px-4 py-2">
                            <div>
                                Deployments: {{ $repo->sanityDeployments->count() }}
                            </div>
                            @if($engine->hasDeploymentsInProgress($repo))
                                <div class="animate-pulse text-green-900">
                                    Active deployments: {{ $engine->countDeploymentsInProgress($repo) }}
                                </div>
                            @endif
                        </td>
                        <td class="border px-4 py-2">
                            <div class="mb-2">
                                <button wire:click="edit({{ $repo->id }})" class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-2 rounded">Edit</button>
                                <button onclick="confirm('Are you sure you want to delete the repository?') || event.stopImmediatePropagation()" wire:click="delete({{ $repo->id }})" class="bg-red-500 hover:bg-red-700 text-white py-2 px-2 rounded">Delete</button>
                            </div>
                            <div>
                                <button onclick="confirm('Are you sure you want to trigger all deployments?') || event.stopImmediatePropagation()" wire:click="deployAll({{ $repo->id }})" class="bg-green-500 hover:bg-green-700 text-white py-2 px-2 rounded">Trigger deployments</button>
                                @if($engine->hasDeploymentsInProgress($repo))
                                    <button onclick="confirm('Are you sure you want to cancel all deployments?') || event.stopImmediatePropagation()" wire:click="cancelDeployAll({{ $repo->id }})" class="bg-green-500 hover:bg-green-700 text-white py-2 px-2 rounded">Cancel deployments</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
