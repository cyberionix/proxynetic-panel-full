<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Models\User;
use App\Traits\AjaxResponses;
use Illuminate\Support\Facades\Auth;
use Mailjet\Request;

class AuthController extends Controller
{
    use AjaxResponses;

    public function login()
    {
        return view('admin.pages.auth.login');
    }

    public function loginPost(LoginRequest $request)
    {
        if (auth()->guard('admin')->attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            return $this->successResponse(__('login_successful') . ' ' . __('redirecting'), ["redirectUrl" => route("admin.dashboard")]);
        } else {
            return $this->errorResponse(__("your_details_are_incorrect") . ". " . __("please_try_again"));
        }
    }

    public function logOutPost()
    {
        auth()->guard('admin')->logout();
        return redirect()->route("admin.auth.login");
    }

    public function userAccountLogin(User $user)
    {
        $guard = Auth::guard('web');
        $guard->setUser($user);
        session()->put($guard->getName(), $user->getAuthIdentifier());

        return $this->successResponse(__('login_successful'), ["redirectUrl" => route("portal.dashboard")]);
    }
}
