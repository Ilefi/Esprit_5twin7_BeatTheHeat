<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\View\Components\StatusBadge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = (string) $request->query('role');
        $search = trim((string) $request->query('q'));

        $users = User::withCount(['reviews', 'reports'])
            ->when(array_key_exists($role, StatusBadge::options('role')), fn (Builder $query) => $query->where('role', $role))
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderBy('id');

        return view('admin.users.index', [
            'users' => $users->paginate(10)->withQueryString(),
            'roles' => StatusBadge::options('role'),
            'counts' => User::pluck('role')->countBy(),
        ]);
    }

    public function edit(int $user): View
    {
        return view('admin.users.edit', [
            'user' => User::findOrFail($user),
            'roles' => StatusBadge::options('role'),
        ]);
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        $user = User::findOrFail($user);
        $data = $request->validate(['role' => ['required', 'in:'.implode(',', array_keys(StatusBadge::options('role')))]]);

        // TODO(shared): User::findOrFail($id)->forceFill(['role' => $data['role']])->save()
        return redirect()->route('admin.users.index')->with('success', "Rôle de {$user->name} : ".StatusBadge::labelFor('role', $data['role']).'.');
    }
}
