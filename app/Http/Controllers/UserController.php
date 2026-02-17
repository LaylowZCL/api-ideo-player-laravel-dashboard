<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            $query->where('user_type', '!=', 'admin');
        }
        
        $users = $query->orderBy('name')->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'role_name' => $this->getRoleName($user->user_type),
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
            'user_type' => [
                'required',
                Rule::in(['user', 'manager', 'admin']),
                function ($attribute, $value, $fail) {
                    // Manager não pode criar admin
                    if (Gate::allows('isManager') && !Gate::allows('isAdmin') && $value === 'admin') {
                        $fail('Você não tem permissão para criar administradores.');
                    }
                },
            ],
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
                'user_type' => $request->user_type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'role_name' => $this->getRoleName($user->user_type),
                    'created_at' => $user->created_at->format('d/m/Y')
                ]
            ]);

        } catch (\Exception $e) {
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
        if (!$isSelf && $user->user_type === 'admin' && Gate::allows('isManager') && !Gate::allows('isAdmin')) {
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
            'user_type' => [
                'sometimes',
                Rule::in(['user', 'manager', 'admin']),
                function ($attribute, $value, $fail) use ($user, $currentUser, $isSelf) {
                    if ($isSelf) {
                        $fail('Você não pode alterar seu próprio tipo de usuário.');
                        return;
                    }

                    // Manager não pode promover para admin
                    if (Gate::allows('isManager') && !Gate::allows('isAdmin') && $value === 'admin') {
                        $fail('Você não tem permissão para promover para administrador.');
                    }
                    
                    // Manager não pode alterar admin para outro tipo
                    if ($user->user_type === 'admin' && Gate::allows('isManager') && !Gate::allows('isAdmin')) {
                        $fail('Você não tem permissão para alterar administradores.');
                    }
                    
                    // Manager pode promover user para manager
                    if (Gate::allows('isManager') && !Gate::allows('isAdmin') && 
                        $user->user_type === 'user' && $value === 'manager') {
                        // Permitido
                    }
                    
                    // Manager não pode rebaixar manager para user
                    if (Gate::allows('isManager') && !Gate::allows('isAdmin') && 
                        $user->user_type === 'manager' && $value === 'user') {
                        $fail('Você não tem permissão para rebaixar gestores.');
                    }
                },
            ],
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
            
            // Atualiza user_type se especificado e permitido
            if ($request->has('user_type')) {
                if ($isSelf) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Você não pode alterar seu próprio tipo de usuário.'
                    ], 403);
                }

                // Apenas admin pode mudar para/desde admin
                if ($request->user_type === 'admin' || $user->user_type === 'admin') {
                    if (!Gate::allows('isAdmin')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Apenas administradores podem gerenciar outros administradores.'
                        ], 403);
                    }
                }
                
                $user->user_type = $request->user_type;
            }
            
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'role_name' => $this->getRoleName($user->user_type),
                    'created_at' => $user->created_at->format('d/m/Y')
                ]
            ]);

        } catch (\Exception $e) {
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
        if ($user->user_type === 'admin' && Gate::allows('isManager') && !Gate::allows('isAdmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para excluir administradores.'
            ], 403);
        }

        // Manager não pode excluir outro manager
        if ($user->user_type === 'manager' && Gate::allows('isManager') && !Gate::allows('isAdmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para excluir outros gestores.'
            ], 403);
        }

        try {
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuário removido com sucesso'
            ]);

        } catch (\Exception $e) {
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
            'admin' => 'Administrador',
            'manager' => 'Gestor',
            'user' => 'Usuário'
        ];
        
        return $roles[$userType] ?? 'Usuário';
    }
}
