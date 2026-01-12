<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VehicleType;

class VehicleTypeController extends Controller
{
    /**
     * Muestra la lista de tipos de vehículo en JSON.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $vehicleTypes = VehicleType::all();
        return response()->json($vehicleTypes);
    }

    /**
     * Crea un nuevo tipo de vehículo.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $vehicleType = VehicleType::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => $request->name,
            'creation_date' => now(),
        ]);
        return response()->json($vehicleType, 201);
    }

    /**
     * Muestra un tipo de vehículo específico.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $vehicleType = VehicleType::findOrFail($id);
        return response()->json($vehicleType);
    }

    /**
     * Actualiza un tipo de vehículo.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $vehicleType = VehicleType::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $vehicleType->update([
            'name' => $request->name,
        ]);
        return response()->json($vehicleType);
    }

    /**
     * Elimina un tipo de vehículo.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $vehicleType = VehicleType::findOrFail($id);
        $vehicleType->delete();
        return response()->json(['message' => 'Tipo de vehículo eliminado']);
    }
}