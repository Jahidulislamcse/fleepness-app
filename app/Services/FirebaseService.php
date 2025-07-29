<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Auth;

class FirebaseService
{
    protected $auth;

    public function __construct()
    {
        $credentialsPath = config('firebase.credentials');

        \Log::info('Firebase credentials file path: ' . $credentialsPath);

        if (!file_exists($credentialsPath)) {
            \Log::error('Firebase credentials file does not exist at path: ' . $credentialsPath);
            throw new \Exception('Firebase credentials file not found at ' . $credentialsPath);
        }

        $json = file_get_contents($credentialsPath);
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('Invalid JSON in Firebase credentials file: ' . json_last_error_msg());
            throw new \Exception('Invalid JSON in Firebase credentials file: ' . json_last_error_msg());
        } else {
            \Log::info('Firebase credentials JSON is valid.');
        }

        // Create the Firebase Factory without database
        $factory = (new Factory)->withServiceAccount($credentialsPath);

        $this->auth = $factory->createAuth();
    }

    public function getAuth(): Auth
    {
        return $this->auth;
    }
}
