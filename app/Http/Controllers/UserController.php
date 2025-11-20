<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller
{   
    /**
     * Muestra la lista de usuarios.
     *
     * @return \Illuminate\View\View
    */
    public function index()
    {
        $users = User::with('role')->get();
        $roles = Role::where('status', Role::STATUS_ACTIVE)->get();
        // Debug: quitar después
        // dd($users, $roles);
        return view('usuarios.index', compact('users', 'roles'));
    }

    /**
     * Almacena un nuevo usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:8',
            'rol' => 'required|uuid|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'status' => true,
            'creation_date' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Usuario creado exitosamente', 'user' => $user]);
    }

    /**
     * Actualiza un usuario existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'rol' => 'required|uuid|exists:roles,id',
            'status' => 'required|boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'username' => $request->username,
            'rol' => $request->rol,
            'status' => (bool) $request->status,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json(['success' => true, 'message' => 'Usuario actualizado exitosamente', 'user' => $user->fresh()]);
    }

    /**
     * Elimina un usuario.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
    }
}
