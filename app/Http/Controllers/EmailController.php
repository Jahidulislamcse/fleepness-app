<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function sendTestEmail(Request $request)
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'receiver' => 'required|email',
                'subject' => 'required|string|max:255',
                'body' => 'required|string',
            ]);

            // Log mail config (optional, for debugging)
            \Log::info('Mail Config', [
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'password' => config('mail.mailers.smtp.password'),
            ]);

            // Send the email
            Mail::html($validated['body'], function ($message) use ($validated) {
                $message->to($validated['receiver'])
                    ->subject($validated['subject']);
            });

            return response()->json(['message' => 'Email sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function receiveCustomerEmail(Request $request)
    {
        try {
            // Validate incoming customer data
            $validated = $request->validate([
                'customer_email' => 'required|email',
                'customer_name' => 'required|string|max:255',
                'message_body' => 'required|string',
            ]);

            // Your email where you want to receive customer emails
            $myEmail = 'jahidcse181@gmail.com'; // Change this to your email

            // Prepare email content
            $emailContent = "
            <h2>New Customer Message</h2>
            <p><strong>Name:</strong> {$validated['customer_name']}</p>
            <p><strong>Email:</strong> {$validated['customer_email']}</p>
            <p><strong>Message:</strong><br>{$validated['message_body']}</p>
        ";

            // Send the email to you
            Mail::html($emailContent, function ($message) use ($validated, $myEmail) {
                $message->to($myEmail)
                    ->replyTo($validated['customer_email']) // So you can click reply directly
                    ->subject('New Customer Inquiry from ' . $validated['customer_name']);
            });

            return response()->json(['message' => 'Customer email received and sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
