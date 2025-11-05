<?php

namespace App\Http\Controllers\Vendor;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Container\Attributes\CurrentUser;

class VendorProfileController extends Controller
{
    public function edit()
    {
        return view('vendor.profile.edit', [
            'user' => Auth::user(),
        ]);
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
