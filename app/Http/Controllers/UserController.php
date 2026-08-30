<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Director-only staff account management. This is the only way a new
 * account gets created - there is no public self-registration - so every
 * account a Director doesn't create themselves simply doesn't exist.
 */
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $perPage = in_array($request->query('per_page'), ['10', '25', '50'], true) ? (int) $request->query('per_page') : 10;

        $users = User::latest()->paginate($perPage)->withQueryString();

        $totalStaff = User::count();
        $instructorCount = User::where('role', 'instructor')->count();
        $administratorCount = $totalStaff - $instructorCount;

        return view('users.index', compact('users', 'perPage', 'totalStaff', 'instructorCount', 'administratorCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => $request->validated('role'),
        ]);

        ActivityLog::record("Added a staff account for {$user->name} ({$user->role})");

        return Redirect::route('users.index')->with('status', 'user-created');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        ActivityLog::record("Updated the staff account for {$user->name} ({$user->role})");

        return Redirect::route('users.index')->with('status', 'user-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return Redirect::back()->withErrors([
                'user' => 'You cannot remove your own account.',
            ]);
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::record("Removed the staff account for {$name}");

        return Redirect::route('users.index')->with('status', 'user-deleted');
    }
}
