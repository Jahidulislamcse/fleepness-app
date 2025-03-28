<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\SMSService;
use App\Events\SellerStatusUpdated;

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


        return redirect()->route('admin.user.list')->with('success', 'User created successfully!');
    }

    public function application(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|in:vendor,admin,rider',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone,
            'address' => $request->address,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'status' => 'pending',
        ]);

        // Check if the request expects JSON (API request)
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Account created successfully!',
                'user' => $user
            ], 201);
        }

        // If it's a web request, redirect to a specific route with a success message
        return redirect()->route('create.vendor.account')->with('success', 'Account created successfully!');
    }

    public function sellerRequest()
    {
        // Fetch users with role 'vendor' and status 'pending'
        $pendingSellers = User::where('role', 'vendor')
            ->where('status', 'pending')
            ->get();

        return response()->json([
            'message' => 'Pending seller requests retrieved successfully.',
            'data' => $pendingSellers
        ]);
    }

    public function checkProfile()
    {
        // Get logged user info
        $user = auth()->user();

        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'status' => $user->status,
        ]);
    }

    public function checkStatus()
    {
        // Retrieve only the 'status' column for the authenticated user
        $status = User::where('id', auth()->id())->value('status');
        // dd($status);

        return response()->json([
            'status' => $status
        ], 200);
    }

    public function sellerApprove(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update([
            'status' => 'approved',
            // 'role' => 'vendor' // If you want to update role as well
        ]);

        return response()->json(['message' => 'Seller approved successfully', 'user' => $user]);
    }

    public function sellerReject(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
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

        return redirect()->route('admin.user.list')->with('success', 'User updated successfully');
    }

    public function status($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'status' => 'approved',
            'activation_date' => now()
        ]);

        $password = $user->raw_password ?? "Your set password";
        $message = "Dear {$user->name}, your admin account has been approved!\n";
        $message .= "Email: {$user->email}\n";
        $message .= "Password: {$password}\n";


        $smsSent = $this->smsService->sendSMS($user->phone, $message);

        return redirect()->to(route('super-admin.user.manage') . '?tab=pending_admins')->with('success', 'User status updated and SMS sent successfully');
    }

    public function approveSeller(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:users,id',
        ]);

        $seller = User::find($request->seller_id);

        if (!$seller) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $seller->status = 'approved';
        $seller->save();

        // Send SMS using SMSController
        $smsController = new SMSController();
        $smsController->sendSMS(new Request([
            'Message' => "Your seller request has been approved by Fleepness!",
            'MobileNumbers' => $seller->phone_number,
        ]));

        // Send notification
        event(new SellerStatusUpdated($seller->id, "Your seller request has been approved! 🎉"));

        return response()->json(['message' => 'Seller approved successfully']);
    }

    public function rejectSeller(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:users,id',
        ]);

        $seller = User::find($request->seller_id);

        if (!$seller) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $seller->status = 'rejected';
        $seller->save();

        // Send SMS using SMSController
        $smsController = new SMSController();
        $smsController->sendSMS(new Request([
            'Message' => "Your seller request has been Rejected by Fleepness!",
            'MobileNumbers' => $seller->phone_number,
        ]));

        // Send notification
        event(new SellerStatusUpdated($seller->user_id, "Your seller request has been rejected. ❌"));

        return response()->json(['message' => 'Seller rejected successfully']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();


        return redirect()->route('admin.user.list')->with('success', 'User deleted successfully');
    }
}
