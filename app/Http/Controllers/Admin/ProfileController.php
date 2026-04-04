<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        $admin = auth()->guard('admin')->user();
        return view('admin.pages.profile.index', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = auth()->guard('admin')->user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $admin->update($request->only('first_name', 'last_name', 'email', 'phone'));

        return $this->successResponse('Profil bilgileri güncellendi.');
    }

    public function updatePassword(Request $request)
    {
        $admin = auth()->guard('admin')->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $admin->password)) {
            return $this->errorResponse('Mevcut şifre yanlış.');
        }

        $admin->update(['password' => Hash::make($request->new_password)]);

        return $this->successResponse('Şifre başarıyla güncellendi.');
    }

    public function updateSignature(Request $request)
    {
        $admin = auth()->guard('admin')->user();

        $admin->update(['signature' => $request->input('signature')]);

        return $this->successResponse('İmza başarıyla güncellendi.');
    }
}
