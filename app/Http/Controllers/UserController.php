<?php
namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Helpers\ValidationHelper;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller
{   
    /**
     * Muestra la lista de usuarios.
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @return \Illuminate\View\View
    */
    public function index()
    {
        $activeUsers = User::with('role')->where('status', true)->get();
        $inactiveUsers = User::with('role')->where('status', false)->get();
        $roles = Role::where('status', Role::STATUS_ACTIVE)->get();

        return view('users.index', compact('activeUsers', 'inactiveUsers', 'roles'));
    }

    /**
     * Almacena un nuevo usuario
     *
     * @author Jose Alzate <josealzate97@gmail.com>
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

        // Validación adicional para teléfono español
        if ($request->filled('phone') && !ValidationHelper::validateSpanishPhone($request->phone)) {
            return response()->json(['success' => false, 'message' => 'Formato de teléfono inválido. Use formato español (ej: +34 600 123 456)']);
        }

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'status' => true,
            'creation_date' => now(),
        ]);
        $user->id = (string) Str::uuid();
        $user->save();

        return response()->json([
            'success' => true, 
            'message' => 'Usuario creado exitosamente', 
            'user' => $user
        ]);
    }

    /**
     * Actualiza un usuario existente
     *
     * @author Jose Alzate <josealzate97@gmail.com>
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

        // Validación adicional para teléfono español
        if ($request->filled('phone') && !ValidationHelper::validateSpanishPhone($request->phone)) {
            return response()->json(['success' => false, 'message' => 'Formato de teléfono inválido. Use formato español (ej: +34 600 123 456)']);
        }

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

        return response()->json([
            'success' => true, 
            'message' => 'Usuario actualizado exitosamente', 
            'user' => $user->fresh()
        ]);
    }

    /**
     * Elimina un usuario (soft delete, cambia status a 0)
     * 
     * @author Jose Alzate <josealzate97@gmail.com>
     * @param  string  $id
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {   
        $user = User::findOrFail($id);
        
        $user->update(['status' => false]);

        return response()->json([
            'success' => true, 
            'message' => 'Usuario desactivado exitosamente'
        ]);
    }

    /**
     * Activa un usuario (cambia status a 1)
     * 
     * @author Jose Alzate <josealzate97@gmail.com>
     * @param  string  $id
     * @return \Illuminate\Http\Response
    */
    public function activate($id)
    {   
        $user = User::findOrFail($id);
        
        $user->update(['status' => true]);

        return response()->json([
            'success' => true, 
            'message' => 'Usuario activado exitosamente'
        ]);
    }

    /**
     * Muestra el perfil del usuario autenticado
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @return \Illuminate\View\View
    */
    public function profile()
    {
        $user = Auth::user()->load('role');
        return view('users.profile', compact('user'));
    }

    /**
     * Actualiza el perfil del usuario autenticado
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
        ]);

        // Validación adicional para teléfono español
        if ($request->filled('phone') && !ValidationHelper::validateSpanishPhone($request->phone)) {
            return response()->json(['success' => false, 'message' => 'Formato de teléfono inválido. Use formato español (ej: +34 600 123 456)']);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'username' => $request->username,
        ]);

        if ($request->filled('current_password') && $request->filled('new_password')) {

            // Verificar contraseña actual
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
            }

            $request->validate(['new_password' => 'string|min:8']);
            $user->update(['password' => Hash::make($request->new_password)]);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Perfil actualizado exitosamente', 
            'user' => $user->fresh()
        ]);
    }
}
