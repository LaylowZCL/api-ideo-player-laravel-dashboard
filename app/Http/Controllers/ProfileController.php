<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function showPage()
    {
        return view('profile');
    }

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->roleName(),
                'role_name' => match ($user->roleName()) {
                    'super_admin' => 'Super Administrador',
                    'admin' => 'Administrador',
                    'manager' => 'Gestor',
                    default => 'Utilizador',
                },
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($validated['password'])) {
            if (empty($validated['current_password']) || !Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A palavra-passe actual é obrigatória para definir uma nova.',
                ], 422);
            }

            $user->password = Hash::make($validated['password']);
            $user->password_changed_at = now();
            $user->must_change_password = false;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->username = $validated['username'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Os seus dados foram actualizados com sucesso.',
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->roleName(),
            ],
        ]);
    }
}
