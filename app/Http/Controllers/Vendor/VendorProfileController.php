<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Constants\FeedbackMessage;
use App\Data\Resources\UserData;
use App\Data\UpdateEmailData;
use App\Data\UpdatePhoneData;
use App\Data\UpdateProfileData;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Firebase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class VendorProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('vendor.profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'regex:/^\+?[0-9]{10,15}$/'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $existingUser = User::where('phone_number', $validatedData['phone_number'])
            ->where('id', '!=', $user->id) 
            ->first();

        if ($existingUser) {
            return redirect()->back()->withErrors(['phone_number' => 'This phone number is already in use.']);
        }


        $user->update($validatedData);

        return redirect()->back()->with('status', 'profile-updated');
    }
}
