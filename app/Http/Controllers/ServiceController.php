<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{   
    /**
     * Muestra la lista de servicios.
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @return \Illuminate\View\View
    */
    public function index()
    {
        return view('services.index');
    }

    /**
     * Muestra la lista de servicios en JSON.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiIndex()
    {
        $services = Service::all();
        return response()->json($services);
    }

    /**
     * Crea un nuevo servicio.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'details' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'category_id' => 'required|exists:category,id',
        ]);

        $service = Service::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'category_id' => $request->category_id,
            'name' => $request->name,
            'details' => $request->details,
            'value' => $request->value,
            'duration' => $request->duration,
            'creation_date' => now(),
        ]);
        return response()->json($service, 201);
    }

    /**
     * Muestra un servicio específico.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $service = Service::findOrFail($id);
        return response()->json($service);
    }

    /**
     * Actualiza un servicio.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'details' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'category_id' => 'required|exists:category,id',
        ]);

        $service->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'details' => $request->details,
            'value' => $request->value,
            'duration' => $request->duration,
        ]);
        return response()->json($service);
    }

    /**
     * Elimina un servicio.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        return response()->json(['message' => 'Servicio eliminado']);
    }
}
