<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except'    =>  ['show', 'create', 'store']
        ]);

        $this->middleware('guest', [
            'only'      =>  ['create']
        ]);
    }

    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
           'name'       =>  'required|unique:users|max:50',
           'email'      =>  'required|email|unique:users|max:255',
           'password'   =>  'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name'      =>  $request->input('name'),
            'email'     =>  $request->input('email'),
            'password'  =>  bcrypt($request->input('password'))
        ]);

        // 注册后直接登录，增强体验
        Auth::login($user);
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程！');
        return redirect()->route('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        // 授权检测
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        // 授权检测
        $this->authorize('update', $user);

        $this->validate($request, [
            'name'      =>  'required|unique:users|max:50',
            'password'  =>  'nullable|confirmed|min:6',     // 密码允许为空，不用每次都输入
        ]);

        $data = [];
        $data['name'] = $request->input('name');
        if ($request->has('password')) {
            $data['password'] = $request->input('password');
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', compact('user'));
    }
}
