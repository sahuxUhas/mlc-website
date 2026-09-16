<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'min:2', 'max:80'],
            'email'   => ['required', 'email', 'max:190'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
            'website' => ['nullable', 'max:0'], // হানিপট
        ], [], ['name' => 'নাম', 'message' => 'বার্তা']);

        ContactMessage::create($validated + ['ip_address' => $request->ip()]);

        return back()->with('success', 'আপনার বার্তা পাঠানো হয়েছে। আমরা শীঘ্রই যোগাযোগ করব।');
    }
}
