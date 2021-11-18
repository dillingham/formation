<?php

namespace Dillingham\Formation\Tests\Fixtures;

use Illuminate\Foundation\Auth\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user):bool
    {
        return config('formations.testing-policies.viewAny', true);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @return bool
     */
    public function view(User $user):bool
    {
        return config('formations.testing-policies.view', true);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user):bool
    {
        return config('formations.testing-policies.create', true);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @return bool
     */
    public function update(User $user):bool
    {
        return config('formations.testing-policies.update', true);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @return bool
     */
    public function delete(User $user):bool
    {
        return config('formations.testing-policies.delete', true);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User  $user
     * @return bool
     */
    public function restore(User $user):bool
    {
        return config('formations.testing-policies.restore', true);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  User  $user
     * @return bool
     */
    public function forceDelete(User $user):bool
    {
        return config('formations.testing-policies.forceDelete', true);
    }
}
