<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('dashboard.users.index', [
            'users' => User::query()->orderByRaw("CASE role WHEN 'admin' THEN 0 WHEN 'editor' THEN 1 ELSE 2 END")->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.users.form', ['user' => new User(['role' => 'editor'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        $user = User::create([...$data, 'email' => mb_strtolower($data['email'])]);
        ActivityLog::record('user.created', $user->email, ['role' => $user->role]);

        return redirect()->route('dashboard.users.index')->with('status', "Account created for {$user->email}.");
    }

    public function edit(User $user): View
    {
        return view('dashboard.users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['nullable', 'confirmed', Password::min(12)],
        ]);

        if ($user->isAdmin() && $data['role'] !== 'admin' && $this->isLastAdmin($user)) {
            return back()->withInput()->withErrors(['role' => 'This is the only admin. Make someone else an admin first.']);
        }

        $user->fill([
            'name' => $data['name'],
            'email' => mb_strtolower($data['email']),
            'role' => $data['role'],
        ]);
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $changes = array_keys($user->getDirty());
        $user->save();

        ActivityLog::record('user.updated', $user->email, ['changed' => array_values(array_diff($changes, ['updated_at']))]);

        return redirect()->route('dashboard.users.index')->with('status', 'Account saved.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }
        if ($user->isAdmin() && $this->isLastAdmin($user)) {
            return back()->withErrors(['user' => 'This is the only admin and cannot be deleted.']);
        }

        ActivityLog::record('user.deleted', $user->email);
        $user->delete();

        return redirect()->route('dashboard.users.index')->with('status', 'Account deleted.');
    }

    /** Lost phone: the person sets up their authenticator app again at their next sign-in. */
    public function resetTwoFactor(User $user): RedirectResponse
    {
        TwoFactor::reset($user);
        ActivityLog::record('auth.two_factor_reset', $user->email);

        return back()->with('status', "Authenticator reset for {$user->email}. They set it up again at their next sign-in.");
    }

    private function isLastAdmin(User $user): bool
    {
        return User::where('role', 'admin')->whereKeyNot($user->id)->doesntExist();
    }
}
