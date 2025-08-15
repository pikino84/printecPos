<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    // 👇 Lista de roles que un Asociado Administrador puede asignar
    private const ASOCIADO_ROLES = [
        'Asociado Administrador',
        'Asociado Vendedor',
    ];
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->partner_id != 1) {
            // Si es asociado, solo ve sus usuarios
            $users = User::with('roles')
                ->where('partner_id', auth()->user()->partner_id)
                ->get();
        } else {
            // Si es usuario Printec (admin), ve todos
            $users = User::with('roles')->get();
        }

        return view('users.index', compact('users'));
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        if ($user->hasRole('Asociado Administrador')) {
            // Usa la constante para filtrar los roles disponibles
            $roles = collect(array_combine(self::ASOCIADO_ROLES, self::ASOCIADO_ROLES));
        } else {
            // Admin o super admin: todos los roles
            $roles = Role::pluck('name', 'name');
        }

        $partners = null;
        if ($user->partner_id === 1) {
            $partners = \App\Models\Partner::all();
        }

        return view('users.create', compact('roles', 'partners'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Definir roles permitidos según el rol del usuario
        $allowedRoles = $user->hasRole('Asociado Administrador')
            ? self::ASOCIADO_ROLES
            : Role::pluck('name')->toArray();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role'     => ['required', Rule::in($allowedRoles)],
        ]);

        $partnerId = $user->partner_id ?? $request->partner_id;

        $newUser = User::create([
            'name'                 => $request->name,
            'email'                => $request->email,
            'password'             => Hash::make($request->password),
            'partner_id'           => $partnerId,
            'must_change_password' => true,
        ]);

        $newUser->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'Usuario creado');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $auth = auth()->user();

        $roles = $auth->hasRole('Asociado Administrador')
            ? collect(array_combine(self::ASOCIADO_ROLES, self::ASOCIADO_ROLES))
            : Role::pluck('name', 'name');

        $partners = $auth->partner_id === 1 ? \App\Models\Partner::all() : null;

        return view('users.edit', compact('user', 'roles', 'partners'));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $userToUpdate = User::findOrFail($id);
        $auth = auth()->user();

        // Determinar qué roles puede asignar el usuario autenticado
        $allowedRoles = $auth->hasRole('Asociado Administrador')
            ? self::ASOCIADO_ROLES
            : Role::pluck('name')->toArray();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$id}",
            'role'  => ['required', Rule::in($allowedRoles)],
        ]);

        // Actualizar datos básicos
        $userToUpdate->name  = $request->name;
        $userToUpdate->email = $request->email;

        // Solo admin (o printec) puede cambiar el partner
        if ($auth->partner_id === 1 && $request->filled('partner_id')) {
            $userToUpdate->partner_id = $request->partner_id;
        }

        // Si se cambia la contraseña
        if ($request->filled('password')) {
            $userToUpdate->password = Hash::make($request->password);
            $userToUpdate->must_change_password = false;
        }

        $userToUpdate->save();

        // Sincroniza rol solo si es válido
        $userToUpdate->syncRoles($request->role);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->hasRole('super admin')) {
            return redirect()->route('users.index')->with('error', 'No se puede eliminar a un Super Admin.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }
}
