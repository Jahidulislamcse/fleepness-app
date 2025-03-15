<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
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
            'status' => 'pending',
        ]);


        return redirect()->route('create.vendor.account')->with('success', 'Account created successfully!');
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
        return redirect()->route('admin.user.list')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();


        return redirect()->route('admin.user.list')->with('success', 'User deleted successfully');
    }
}
