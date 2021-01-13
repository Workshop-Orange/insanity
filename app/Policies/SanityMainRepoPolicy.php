<?php

namespace App\Policies;

use App\Models\SanityMainRepo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SanityMainRepoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SanityMainRepo  $sanityMainRepo
     * @return mixed
     */
    public function view(User $user, SanityMainRepo $sanityMainRepo)
    {
        return $user->belongsToTeam($sanityMainRepo->team)
            && $user->currentTeam->id == $sanityMainRepo->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SanityMainRepo  $sanityMainRepo
     * @return mixed
     */
    public function update(User $user, SanityMainRepo $sanityMainRepo)
    {
        return $user->belongsToTeam($sanityMainRepo->team)
            && $user->currentTeam->id == $sanityMainRepo->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SanityMainRepo  $sanityMainRepo
     * @return mixed
     */
    public function delete(User $user, SanityMainRepo $sanityMainRepo)
    {
        return $user->belongsToTeam($sanityMainRepo->team)
            && $user->currentTeam->id == $sanityMainRepo->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SanityMainRepo  $sanityMainRepo
     * @return mixed
     */
    public function restore(User $user, SanityMainRepo $sanityMainRepo)
    {
        return $user->belongsToTeam($sanityMainRepo->team)
            && $user->currentTeam->id == $sanityMainRepo->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SanityMainRepo  $sanityMainRepo
     * @return mixed
     */
    public function forceDelete(User $user, SanityMainRepo $sanityMainRepo)
    {
        return $user->belongsToTeam($sanityMainRepo->team)
            && $user->currentTeam->id == $sanityMainRepo->id;
    }
}
