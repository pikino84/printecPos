<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;


class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = \Spatie\Permission\Models\Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }


    public function create()
    {
        $permissions = \Spatie\Permission\Models\Permission::all();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . ($role->id ?? 'NULL'),
            'permissions' => 'array|required',
        ]);

        Role::create(['name' => $request->name]);

        return redirect()->route('roles.index')->with('success', 'Rol creado con éxito.');
    }

    public function edit(Role $role)
    {        
        $permissions = \Spatie\Permission\Models\Permission::all();
        return view('roles.edit', compact('role', 'permissions'));
    }


    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . ($role->id ?? 'NULL'),
            'permissions' => 'array|required',
        ]);

        $role->update(['name' => $request->name]);

        return redirect()->route('roles.index')->with('success', 'Rol actualizado.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado.');
    }
}
