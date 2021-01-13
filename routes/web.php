<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\SanityMainRepoComponent;
use App\Http\Livewire\SanityMainReposComponent;
use App\Models\SanityMainRepo;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth:sanctum', 'verified'])->get('/', function () {
    return redirect()->route('sanityMainRepos');
})->name('home');

Route::middleware(['auth:sanctum', 'verified'])->get('/sanity-main-repos', SanityMainReposComponent::class)->name('sanityMainRepos');
Route::middleware(['auth:sanctum', 'verified'])->get('/sanity-main-repo/{sanityMainRepo}', SanityMainRepoComponent::class)->name('sanityMainRepo');
