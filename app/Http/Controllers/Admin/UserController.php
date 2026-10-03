<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use App\View\Components\StatusBadge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        // TODO(shared): replace DemoData with User::query()->filter($request)->paginate()
        $users = DemoData::users();

        if (array_key_exists($role = (string) $request->query('role'), StatusBadge::options('role'))) {
            $users = $users->where('role', $role);
        }
        if ($search = trim((string) $request->query('q'))) {
            $users = $users->filter(fn ($u) => str_contains(mb_strtolower($u->name.' '.$u->email), mb_strtolower($search)));
        }

        return view('admin.users.index', [
            'users' => DemoData::paginate($users->values(), 10),
            'roles' => StatusBadge::options('role'),
            'counts' => DemoData::users()->countBy('role'),
        ]);
    }

    public function edit(int $user): View
    {
        return view('admin.users.edit', [
            'user' => DemoData::user($user),
            'roles' => StatusBadge::options('role'),
        ]);
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        $user = DemoData::user($user);
        $data = $request->validate(['role' => ['required', 'in:'.implode(',', array_keys(StatusBadge::options('role')))]]);

        // TODO(shared): User::findOrFail($id)->forceFill(['role' => $data['role']])->save()
        return redirect()->route('admin.users.index')->with('success', "Rôle de {$user->name} : ".StatusBadge::label('role', $data['role']).'.');
    }
}
