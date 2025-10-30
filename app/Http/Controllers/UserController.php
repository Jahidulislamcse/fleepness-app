<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserPayment;
use App\Services\SMSService;
use Illuminate\Http\Request;
use App\Events\SellerStatusUpdated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Container\Attributes\CurrentUser;
use App\Models\SellerOrder;

class UserController extends Controller
{
    protected $smsService;

    public function __construct(SMSService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function userList()
    {

        $users = User::all();

        return view('admin.user.index', compact('users'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|in:vendor,admin,rider',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone,
            'address' => $request->address,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'status' => 'approved',
        ]);

        return to_route('admin.user.list')->with('success', 'User created successfully!');
    }

    public function getBalanceStats()
    {
        $user = Auth::user();

        $lifetimeBalance = $user->balance ?? 0;

        $sellerOrders = SellerOrder::where('seller_id', $user->id)
            ->where('status', 'delivered')
            ->get();

        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        $dailyBalance = $sellerOrders->where('created_at', '>=', $today)->sum('balance');
        $weeklyBalance = $sellerOrders->where('created_at', '>=', $startOfWeek)->sum('balance');
        $monthlyBalance = $sellerOrders->where('created_at', '>=', $startOfMonth)->sum('balance');

        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'daily_balance' => $dailyBalance,
            'weekly_balance' => $weeklyBalance,
            'monthly_balance' => $monthlyBalance,
            'lifetime_balance' => $lifetimeBalance,
        ]);
    }

    public function applyForSeller(Request $request, #[CurrentUser] User $user)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'shop_name' => 'required|string|max:255',
            'shop_category' => 'nullable|exists:shop_categories,id',
            'payments' => 'nullable|array', // this should be like ['payment_method_id' => 'account_number', ...]
            'payments.*' => 'nullable|string|max:20',
            'pickup_location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $validator->validate();

        // Access validated data
        $validatedData = $validator->validated();

        // Update the user
        $user->update([
            'name' => $validatedData['name'] ?? null,
            'email' => $validatedData['email'],
            'shop_name' => $validatedData['shop_name'],
            'shop_category' => $validatedData['shop_category'],
            'pickup_location' => $validatedData['pickup_location'] ?? null,
            'description' => $validatedData['description'] ?? null,
            'status' => 'pending', // pending admin approval
        ]);

        // Store user payment methods
        if (! empty($validatedData['payments'])) {
            foreach ($validatedData['payments'] as $payment_method_id => $account_number) {
                if ($account_number) {
                    UserPayment::create([
                        'user_id' => $user->id,
                        'payment_method_id' => $payment_method_id,
                        'account_number' => $account_number,
                    ]);
                }
            }
        }

        // Handle banner image
        if ($request->hasFile('banner_image')) {
            $banner_image = $request->file('banner_image');
            $name_gen = hexdec(uniqid()).'.'.$banner_image->getClientOriginalExtension();
            $path = public_path('upload/user');

            if (! file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $banner_image->move($path, $name_gen);

            $user->banner_image = 'upload/user/'.$name_gen;
            $user->save();
        }

        // Handle cover image
        if ($request->hasFile('cover_image')) {
            $cover_image = $request->file('cover_image');
            $name_gen = hexdec(uniqid()).'.'.$cover_image->getClientOriginalExtension();
            $path = public_path('upload/user');

            if (! file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $cover_image->move($path, $name_gen);

            $user->cover_image = 'upload/user/'.$name_gen;
            $user->save();
        }

        return response()->json([
            'message' => 'Seller application submitted successfully. Pending admin approval.',
            'user' => $user,
        ], 200);
    }

    public function application(Request $request)
    {
        $messages = [
            'phone.digits' => 'Invalid phone number. It must be exactly 11 digits.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'shop_name' => 'required|string|max:255',
            'shop_category' => 'nullable',
            'email' => 'required|email|unique:users,email',
            'phone' => ['required', 'digits:11', 'unique:users,phone_number'],
            'payments' => 'nullable|array',
            'payments.*' => 'nullable|string|max:20',
            'pickup_location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], $messages);

        if ($validator->fails()) {
            if (
                $validator->errors()->has('phone') &&
                str_contains($validator->errors()->first('phone'), 'Invalid number')
            ) {
                return response()->json(['message' => $validator->errors()->first('phone')], 400);
            }

            return response()->json(['errors' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();
        $otp = rand(1000, 9999);

        $user = User::create([
            'name' => $validatedData['name'],
            'shop_name' => $validatedData['shop_name'],
            'shop_category' => $validatedData['shop_category'] ?? null,
            'pickup_location' => $validatedData['pickup_location'] ?? null,
            'description' => $validatedData['description'] ?? null,
            'email' => $validatedData['email'],
            'phone_number' => $validatedData['phone'],
            'status' => 'pending',
        ]);

        Cache::put('otp_'.$user->phone_number, $otp, now()->addMinutes(10));

        if (! empty($validatedData['payments'])) {
            foreach ($validatedData['payments'] as $payment_method_id => $account_number) {
                if ($account_number) {
                    UserPayment::create([
                        'user_id' => $user->id,
                        'payment_method_id' => $payment_method_id,
                        'account_number' => $account_number,
                    ]);
                }
            }
        }

        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('users/banners');
            $user->banner_image = $bannerPath;
        }

        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('users/covers');
            $user->cover_image = $coverPath;
        }

        $user->save();

        $smsController = new SMSController;
        $smsController->sendSMS(new Request([
            'Message' => "Your OTP is: $otp",
            'MobileNumbers' => $user->phone_number,
        ]));

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'OTP sent to your phone. Please verify to complete registration.',
                'user_id' => $user->id,
                'otp' => $otp,
            ], 201);
        }

        return to_route('create.vendor.account')
            ->with('success', 'OTP sent to your phone. Please verify to complete registration.');
    }

    public function sellerRequest()
    {
        $pendingSellers = User::where('status', 'pending')
            ->get();

        return response()->json([
            'message' => 'Pending seller requests retrieved successfully.',
            'data' => $pendingSellers,
        ]);
    }

    public function checkProfile(#[CurrentUser] User $user)
    {
        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'status' => $user->status,
        ]);
    }

    public function checkStatus(#[CurrentUser] User $user)
    {
        return response()->json([
            'status' => $user->status,
        ]);
    }

    public function sellerApprove(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update([
            'status' => 'approved',
            'role' => 'vendor', // If you want to update role as well
        ]);

        return response()->json(['message' => 'Seller approved successfully', 'user' => $user]);
    }

    public function sellerReject(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update([
            'status' => 'rejected',
        ]);

        return response()->json(['message' => 'Seller rejected successfully', 'user' => $user]);
    }

    public function riderApplication(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|in:rider',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone,
            'address' => $request->address,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Account created successfully!');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());
        $user = User::findOrFail($id);
        $user->update($request->all());

        // $message = "Dear {$user->name}, your seller account has been approved!\n";
        // $message .= "Email: {$user->email}\n";

        // $smsSent = $this->smsService->sendSMS($user->phone, $message);

        return to_route('admin.user.list')->with('success', 'User updated successfully');
    }

    public function status($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'status' => 'approved',
            'activation_date' => now(),
        ]);

        $password = $user->raw_password ?? 'Your set password';
        $message = "Dear {$user->name}, your admin account has been approved!\n";
        $message .= "Email: {$user->email}\n";
        $message .= "Password: {$password}\n";

        $this->smsService->sendSMS($user->phone_number, $message);

        return to_route('super-admin.user.manage', ['tab' => 'pending_admins'])
            ->withSuccess('User status updated and SMS sent successfully');
    }

    public function approveSeller(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:users,id',
        ]);

        $seller = User::find($request->seller_id);

        if (! $seller) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $seller->role = 'vendor';
        $seller->status = 'approved';
        $seller->save();

        // Send SMS using SMSController
        $smsController = new SMSController;
        $smsController->sendSMS(new Request([
            'Message' => 'Your seller request has been approved by Fleepness!',
            'MobileNumbers' => $seller->phone_number,
        ]));

        // 🔔 Send real-time notification
        event(new SellerStatusUpdated($seller->id, 'Your seller request has been approved! 🎉'));

        return response()->json(['message' => 'Seller approved successfully']);
    }

    public function rejectSeller(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:users,id',
        ]);

        $seller = User::find($request->seller_id);

        if (! $seller) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $seller->status = 'rejected';
        $seller->save();

        // Send SMS using SMSController
        $smsController = new SMSController;
        $smsController->sendSMS(new Request([
            'Message' => 'Your seller request has been Rejected by Fleepness!',
            'MobileNumbers' => $seller->phone_number,
        ]));

        // Send notification
        event(new SellerStatusUpdated($seller->user_id, 'Your seller request has been rejected. ❌'));

        return response()->json(['message' => 'Seller rejected successfully']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return to_route('admin.user.list')->with('success', 'User deleted successfully');
    }
}
