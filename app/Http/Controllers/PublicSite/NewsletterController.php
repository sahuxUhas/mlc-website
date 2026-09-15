<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:190'],
        ], [], ['email' => 'ইমেইল']);

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => strtolower($validated['email'])]);

        if ($subscriber->exists && $subscriber->is_active) {
            return back()->with('error', 'এই ইমেইলটি ইতিমধ্যে সাবস্ক্রাইব করা।');
        }

        $subscriber->is_active = true;
        $subscriber->save();

        return back()->with('success', 'সাবস্ক্রিপশন সফল হয়েছে। ধন্যবাদ!');
    }
}
