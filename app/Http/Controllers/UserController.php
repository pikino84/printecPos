<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Asociado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->asociado_id) {
            // Si es afiliado, solo ve sus usuarios
            $users = User::with('roles')
                ->where('asociado_id', auth()->user()->asociado_id)
                ->get();
        } else {
            // Si es usuario de Printec, ve todos
            $users = User::with('roles')->get();
        }

        return view('users.index', compact('users'));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::pluck('name', 'name');
        

        $asociados = null;
        if (auth()->user()->asociado_id === null) {
            $asociados = Asociado::all();
        }

        return view('users.create', compact('roles', 'asociados'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {        
        //dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
        ]);
        
        $asociadoId = auth()->user()->asociado_id ?? $request->asociado_id;
        //dd($asociadoId);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'asociado_id' => $asociadoId, // hereda el asociado si aplica
            'must_change_password' => true, // obliga a cambiar clave si lo deseas
        ]);

        $user->assignRole($request->role); // Asignar el rol al usuario

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
        $roles = Role::pluck('name', 'name')->toArray(); // si quieres solo nombre
        $asociados = auth()->user()->asociado_id === null ? Asociado::all() : [];

        return view('users.edit', compact('user', 'roles', 'asociados'));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => "required|email|unique:users,email,{$id}",
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Solo usuarios Printec pueden cambiar el asociado
        if (auth()->user()->asociado_id === null) {
            $user->asociado_id = $request->asociado_id;
        }

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
            $user->must_change_password = false;
        }
        $user->save();
        $user->syncRoles($request->role);

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
