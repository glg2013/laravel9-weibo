<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(User $user)
    {
        $this->authorize('follow', $user);

        if ( ! Auth::user()->isFollowing($user->getAttribute('id'))) {
            Auth::user()->follow($user->getAttribute('id'));
        }

        return redirect()->route('users.show', $user->getAttribute('id'));
    }

    public function destroy(User $user)
    {
        $this->authorize('follow', $user);

        if (Auth::user()->isFollowing($user->getAttribute('id'))) {
            Auth::user()->unfollow($user->getAttribute('id'));
        }

        return redirect()->route('users.show', $user->getAttribute('id'));
    }
}
