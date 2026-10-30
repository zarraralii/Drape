<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => 'required|email|max:150',
        ]);

        Subscriber::firstOrCreate(['email' => $data['email']]);

        return back()->with('success', 'Thanks for subscribing!');
    }
}
