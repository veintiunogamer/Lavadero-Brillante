<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{   
    /**
     * Muestra la lista de clientes.
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @return \Illuminate\View\View
    */
    public function index()
    {
        return view('clients.index');
    }

    /**
     * Muestra la lista de clientes en JSON.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiIndex()
    {
        $clients = Client::all();
        return response()->json($clients);
    }

    /**
     * Crea un nuevo cliente.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'license_plaque' => 'nullable|string|max:20',
        ]);

        $client = Client::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => $request->name,
            'phone' => $request->phone,
            'license_plaque' => $request->license_plaque,
            'creation_date' => now(),
        ]);
        return response()->json($client, 201);
    }

    /**
     * Muestra un cliente específico.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client);
    }

    /**
     * Actualiza un cliente.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'license_plaque' => 'nullable|string|max:20',
        ]);

        $client->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'license_plaque' => $request->license_plaque,
        ]);
        return response()->json($client);
    }

    /**
     * Elimina un cliente.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
        return response()->json(['message' => 'Cliente eliminado']);
    }
}
