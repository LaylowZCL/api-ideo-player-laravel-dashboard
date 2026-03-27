<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function __construct()
    {
        // Apenas a view precisa de autenticação web
        $this->middleware('auth')->only(['goToUsers']);
        
        // Todos os métodos API usam verificação interna
        $this->middleware('internal.api')->except(['goToUsers']);
    }

    public function goToUsers()
    {
        if (!Gate::allows('isAdmin') && !Gate::allows('isManager')) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }
        
        return view('users');
    }

    /**
     * Lista todos os usuários
     */
    public function index(Request $request)
    {
        /*
        if (!Gate::allows('isAdmin') && !Gate::allows('isManager')) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para acessar esta área.'
            ], 403);
        } 
        */

        $query = User::query();
        
        // Se for manager, não mostra admins
        if (Gate::allows('isManager') && !Gate::allows('isAdmin')) {
            $query->whereNotIn('role', ['admin', 'super_admin'])
                ->whereNotIn('user_type', ['admin', 'super_admin']);
        }
        
        $users = $query->orderBy('name')->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'role' => $user->roleName(),
                'role_name' => $this->getRoleName($user->roleName()),
                'permissions' => $user->permissionList(),
                'created_at' => $user->created_at->format('d/m/Y')
            ];
        });

        return response()->json($users);
    }

    /**
     * Cria um novo usuário
     */
    public function store(Request $request)
    {
        // Verifica permissão para criar usuários
        if (!Gate::allows('isAdmin') && !Gate::allows('isManager')) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para criar usuários.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => [
                'required',
                Rule::in(['user', 'manager', 'admin', 'super_admin']),
                function ($attribute, $value, $fail) {
                    if (!$this->canAssignRole(auth()->user(), $value)) {
                        $fail('Você não tem permissão para atribuir este perfil.');
                    }
                },
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in(User::MODULE_PERMISSIONS)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'permissions' => $this->normalizePermissions($request->input('permissions', []), $request->role),
            ]);
            $user->syncLegacyRoleFields();
            $user->save();

            app(AuditLogService::class)->log('user.create', 'success', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->roleName(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'role' => $user->roleName(),
                    'role_name' => $this->getRoleName($user->roleName()),
                    'permissions' => $user->permissionList(),
                    'created_at' => $user->created_at->format('d/m/Y')
                ]
            ]);

        } catch (\Exception $e) {
            app(AuditLogService::class)->log('user.create', 'failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ], 'error');
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza um usuário
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();
        $isSelf = $user->id === $currentUser->id;

        // Verifica permissão básica (exceto autoedição)
        if (!$isSelf && !Gate::allows('isAdmin') && !Gate::allows('isManager')) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para editar usuários.'
            ], 403);
        }

        // Manager não pode editar admin
        if (!$isSelf && in_array($user->roleName(), ['admin', 'super_admin'], true) && Gate::allows('isManager') && !Gate::allows('isAdmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para editar administradores.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'password' => 'sometimes|string|min:8|confirmed',
            'role' => [
                'sometimes',
                Rule::in(['user', 'manager', 'admin', 'super_admin']),
                function ($attribute, $value, $fail) use ($user, $currentUser, $isSelf) {
                    if ($isSelf) {
                        $fail('Você não pode alterar seu próprio tipo de usuário.');
                        return;
                    }

                    if (!$this->canAssignRole($currentUser, $value) || !$this->canManageTargetUser($currentUser, $user)) {
                        $fail('Você não tem permissão para alterar este perfil.');
                    }
                },
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in(User::MODULE_PERMISSIONS)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Atualiza dados básicos
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }
            
            if ($request->has('role')) {
                if ($isSelf) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Você não pode alterar seu próprio tipo de usuário.'
                    ], 403);
                }

                $user->role = $request->role;
            }

            if ($request->has('permissions')) {
                $user->permissions = $this->normalizePermissions(
                    $request->input('permissions', []),
                    $request->input('role', $user->roleName())
                );
            } elseif ($request->has('role')) {
                $user->permissions = $this->normalizePermissions($user->permissionList(), $request->role);
            }

            $user->syncLegacyRoleFields();
            
            $user->save();

            app(AuditLogService::class)->log('user.update', 'success', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'role' => $user->roleName(),
                    'role_name' => $this->getRoleName($user->roleName()),
                    'permissions' => $user->permissionList(),
                    'created_at' => $user->created_at->format('d/m/Y')
                ]
            ]);

        } catch (\Exception $e) {
            app(AuditLogService::class)->log('user.update', 'failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ], 'error');
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove um usuário
     */
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();

        // Não pode remover a si mesmo
        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode remover sua própria conta.'
            ], 403);
        }

        // Verifica permissão para excluir
        if (!Gate::allows('isAdmin') && !Gate::allows('isManager')) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para excluir usuários.'
            ], 403);
        }

        // Manager não pode excluir admin
        if (!$this->canManageTargetUser($currentUser, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para excluir este utilizador.'
            ], 403);
        }

        try {
            $user->delete();

            app(AuditLogService::class)->log('user.delete', 'success', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuário removido com sucesso'
            ]);

        } catch (\Exception $e) {
            app(AuditLogService::class)->log('user.delete', 'failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ], 'error');
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Obtém nome do role
     */
    private function getRoleName($userType)
    {
        $roles = [
            'super_admin' => 'Super Administrador',
            'admin' => 'Administrador',
            'manager' => 'Gestor',
            'user' => 'Usuário'
        ];
        
        return $roles[$userType] ?? 'Usuário';
    }

    private function canAssignRole(User $actor, string $role): bool
    {
        $actorRole = $actor->roleName();

        return match ($actorRole) {
            'super_admin' => in_array($role, ['super_admin', 'admin', 'manager', 'user'], true),
            'admin' => in_array($role, ['admin', 'manager', 'user'], true),
            'manager' => in_array($role, ['manager', 'user'], true),
            default => false,
        };
    }

    private function canManageTargetUser(User $actor, User $target): bool
    {
        $actorRole = $actor->roleName();
        $targetRole = $target->roleName();

        if ($actorRole === 'super_admin') {
            return $targetRole !== 'super_admin';
        }

        if ($actorRole === 'admin') {
            return in_array($targetRole, ['admin', 'manager', 'user'], true);
        }

        if ($actorRole === 'manager') {
            return $targetRole === 'user';
        }

        return false;
    }

    private function normalizePermissions(array $permissions, string $role): array
    {
        if ($role === 'super_admin') {
            return User::MODULE_PERMISSIONS;
        }

        $allowed = User::defaultPermissionsForRole($role);

        if (empty($permissions)) {
            return $allowed;
        }

        return array_values(array_unique(array_intersect(User::MODULE_PERMISSIONS, $permissions, $allowed)));
    }
}
