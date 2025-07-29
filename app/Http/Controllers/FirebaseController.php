<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FirebaseController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function testFirebase(Request $request)
    {
        try {
            $credentialsPath = config('firebase.credentials');

            // Log and check credentials file existence
            Log::info('Firebase credentials path: ' . $credentialsPath);

            if (!file_exists($credentialsPath)) {
                return response()->json([
                    'error' => 'Firebase credentials file not found!',
                    'path' => $credentialsPath
                ], 500);
            }

            $auth = $this->firebaseService->getAuth();

            // Try to list users as a simple test (limit 1 for speed)
            $users = $auth->listUsers(1);

            $firstUser = null;
            foreach ($users as $user) {
                $firstUser = $user;
                break;
            }

            return response()->json([
                'message' => 'Firebase Authentication is working!',
                'first_user' => $firstUser ? $firstUser->uid : 'No users found',
            ]);
        } catch (\Exception $e) {
            Log::error('Firebase test failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'Firebase Authentication failed!',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
