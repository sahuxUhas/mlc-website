<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

/** নিজের প্রোফাইল ও পাসওয়ার্ড পরিবর্তন */
class ProfileController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    public function edit(Request $request)
    {
        return view('admin.settings.profile', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:190', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar'=> ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], ['name' => 'নাম', 'email' => 'ইমেইল']);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->uploader->store($request->file('avatar'), 'avatars')->path;
        }

        $user->update($data);
        ActivityLogger::log('updated', 'profile', 'প্রোফাইল হালনাগাদ', $user);

        return back()->with('success', 'প্রোফাইল হালনাগাদ হয়েছে।');
    }

    /** Secure Password Hashing — পুরোনো পাসওয়ার্ড যাচাইসহ */
    public function password(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers()->mixedCase()],
        ], [], ['current_password' => 'বর্তমান পাসওয়ার্ড', 'password' => 'নতুন পাসওয়ার্ড']);

        $user->update(['password' => $validated['password']]);

        ActivityLogger::log('password_changed', 'profile', 'পাসওয়ার্ড পরিবর্তন করা হয়েছে', $user);

        // অন্য সব সেশন থেকে লগআউট (নিরাপত্তা)
        \Illuminate\Support\Facades\Auth::logoutOtherDevices($validated['current_password']);

        return back()->with('success', 'পাসওয়ার্ড পরিবর্তন সফল হয়েছে।');
    }
}
