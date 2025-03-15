<?php

namespace App\Http\Controllers;

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

class ProfileController extends Controller
{
    public function show(): UserData
    {
        $user = auth()->user();

        return UserData::from($user);
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

    public function updateEmail(UpdateEmailData $data): UserData
    {
        /** @var User */
        $user = auth()->user();
        $otpCode = $data->otp;
        $email = $data->email;

        $isMatch = otp()->check($otpCode, $email);

        throw_if(! $isMatch, BadRequestHttpException::class, FeedbackMessage::OTP_MISMATCH->value);

        $user->update([
            'email' => $data->email,
        ]);

        otp()->forget($email);

        return UserData::from($user);
    }

    public function updatePhone(UpdatePhoneData $data): UserData
    {
        /** @var User */
        $user = auth()->user();

        $idToken = $data->idToken;

        $verifiedIdToken = Firebase::auth()->verifyIdToken($idToken);

        $uid = $verifiedIdToken->claims()->get('sub');
        $firebaseUser = Firebase::auth()->getUser($uid);

        $user->update([
            'phone_number' => $firebaseUser->phoneNumber,
        ]);

        Firebase::auth()->deleteUser($firebaseUser->uid);

        return UserData::from($user);
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
            ->where('id', '!=', $user->id) // Exclude current user
            ->first();

        if ($existingUser) {
            return redirect()->back()->withErrors(['phone_number' => 'This phone number is already in use.']);
        }


        $user->update($validatedData);

        return redirect()->back()->with('status', 'profile-updated');
    }
}
