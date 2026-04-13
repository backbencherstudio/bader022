<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookDemo;
use Illuminate\Support\Facades\Mail;

class BookdemoController extends Controller
{
    public function bookDemo(Request $request)
        {
            $validated = $request->validate([
                'name'          => 'required|string|max:255',
                'email'         => 'required|email|max:255',
                'business_name' => 'required|string|max:255',
                'phone'         => 'required|string|max:20',

            ]);

            $demo = BookDemo::create($validated);

            Mail::send('emails.demo_confirmation', ['demo' => $demo], function ($message) use ($demo) {
                $message->to($demo->email)
                        ->subject('Thank you for your Demo Request!');
            });


            Mail::send('emails.admin_notification', ['demo' => $demo], function ($message) {
                $message->to('noreply@bokli.io') // --- IGNORE ---
                        ->subject('New Demo Request Received');
            });

            return response()->json(['message' => 'Demo request submitted successfully!']);
        }
}
