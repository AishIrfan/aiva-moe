<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponder;

    public function index()
    {
        return $this->ok(User::all(['id', 'name', 'email', 'role', 'school_id']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:moe_admin,school_admin,teacher,operator'],
            'school_id' => ['nullable', 'exists:schools,id'],
        ]);
        $u = User::create($data);
        return $this->ok($u->only(['id', 'name', 'email', 'role', 'school_id']));
    }

    public function show(User $user) { return $this->ok($user->only(['id', 'name', 'email', 'role', 'school_id'])); }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string'],
            'role' => ['sometimes', 'in:moe_admin,school_admin,teacher,operator'],
            'school_id' => ['nullable', 'exists:schools,id'],
        ]);
        $user->update($data);
        return $this->ok($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return $this->ok(null);
    }
}
