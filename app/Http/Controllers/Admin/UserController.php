<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** ইউজার ও রোল ব্যবস্থাপনা (শুধু সুপার অ্যাডমিন) */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount('posts')->latest();
        if ($role = $request->query('role')) { $query->where('role', $role); }
        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w->where('name', 'like', '%'.$q.'%')->orWhere('email', 'like', '%'.$q.'%'));
        }
        return view('admin.users.index', ['users' => $query->paginate(20)->withQueryString()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateUser($request);
        $data['password'] = $request->input('password');

        $user = User::create($data);
        ActivityLogger::created($user, 'users', 'নতুন ইউজার: '.$user->name.' ('.$user->roleLabel().')');

        return back()->with('success', 'ইউজার যোগ হয়েছে।');
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validateUser($request, $user);

        if (! empty($request->input('password'))) {
            $data['password'] = $request->input('password');
        } else {
            unset($data['password']);
        }

        // নিজের রোল নিজে বদলানো যাবে না (লকআউট রোধ)
        if ($user->id === $request->user()->id && isset($data['role']) && $data['role'] !== $user->role) {
            return back()->with('error', 'নিজের রোল নিজে পরিবর্তন করা যাবে না।');
        }

        $user->update($data);
        ActivityLogger::updated($user, 'users', 'ইউজার হালনাগাদ: '.$user->name);

        return back()->with('success', 'ইউজার হালনাগাদ হয়েছে।');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'নিজের অ্যাকাউন্ট মুছে ফেলা যাবে না।');
        }
        if ($user->isSuperAdmin() && User::where('role', 'super_admin')->count() <= 1) {
            return back()->with('error', 'কমপক্ষে একজন সুপার অ্যাডমিন থাকতে হবে।');
        }

        ActivityLogger::deleted($user, 'users', 'ইউজার মুছে ফেলা: '.$user->name);
        $user->delete();

        return back()->with('success', 'ইউজার মুছে ফেলা হয়েছে।');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'min:2', 'max:100'],
            'email'       => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user?->id)],
            'password'    => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role'        => ['required', Rule::in(array_keys(User::ROLES))],
            'phone'       => ['nullable', 'string', 'max:30'],
            'designation' => ['nullable', 'string', 'max:120'],
            'bio'         => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['nullable', 'boolean'],
        ], [], ['name' => 'নাম', 'email' => 'ইমেইল', 'password' => 'পাসওয়ার্ড']);

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
