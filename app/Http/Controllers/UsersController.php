<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except'    =>  ['show', 'create', 'store', 'index', 'confirmEmail']
        ]);

        $this->middleware('guest', [
            'only'      =>  ['create']
        ]);

        // 限流 1个小时内只能提交 10 次请求
        $this->middleware('throttle:10,60', [
            'only'      =>  ['store']
        ]);
    }

    public function index()
    {
        $users = User::paginate(6);
        return view('users.index', compact('users'));
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

        /*
        // 注册后直接登录，增强体验
        Auth::login($user);
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程！');
        return redirect()->route('users.show', compact('user'));
        */
        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已经发送至您的注册邮箱，请注意查收！');
        return redirect()->route('home');
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

    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户 ' . $user->getAttribute('name') . ' ！');
        return redirect()->back();
    }

    public function confirmEmail($token)
    {
        $user = User::query()->where('activation_token', $token)->firstOrFail();

        $user->setAttribute('activated', true);
        $user->setAttribute('activation_token', null);
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜您，验证成功，将开启一段奇妙的旅程！');
        return redirect()->route('users.show', compact('user'));
    }

    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'admin@weibo.test';
        $name = 'admin';
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }
}
