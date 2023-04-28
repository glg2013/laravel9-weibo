<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @param User $currentUser 系统默认当前登录用户实例
     * @param User $user        要进行授权的用户实例
     * @return bool
     */
    public function update(User $currentUser, User $user)
    {
        return $currentUser->getAttribute('id') === $user->getAttribute('id');
    }

    public function destroy(User $currentUser, User $user)
    {
        return $currentUser->getAttribute('is_admin') && $currentUser->id !== $user->id;
    }

    public function follow(User $currentUser, User $user)
    {
        return $currentUser->getAttribute('id') !== $user->getAttribute('id');
    }
}
