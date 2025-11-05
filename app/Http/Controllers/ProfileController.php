<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Container\Attributes\CurrentUser;

class ProfileController extends Controller
{
    public function show(#[CurrentUser()] Authenticatable $user)
    {
        return UserResource::make($user);
    }

    public function edit(Request $request)
    {
        return view('admin.profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function createVendor()
    {
        return view('vendor.account.create');
    }

    public function createRider()
    {
        return view('rider.account.create');
    }

    public function update(Request $request, #[CurrentUser()] User $user)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'regex:/^\+?[0-9]{10,15}$/', Rule::unique(User::class, 'phone_number')
                ->ignore($user)],
        ]);

        $user->update($validatedData);

        return redirect()->back()->with('status', 'profile-updated');
    }
}
