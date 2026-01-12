<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Muestra la lista de categorías en JSON.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    /**
     * Crea una nueva categoría.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'cat_name' => 'required|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        $category = Category::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'cat_name' => $request->cat_name,
            'status' => $request->status ?? 1,
            'creation_date' => now(),
        ]);
        return response()->json($category, 201);
    }

    /**
     * Muestra una categoría específica.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    /**
     * Actualiza una categoría.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $request->validate([
            'cat_name' => 'required|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        $category->update([
            'cat_name' => $request->cat_name,
            'status' => $request->status ?? $category->status,
        ]);
        return response()->json($category);
    }

    /**
     * Elimina una categoría.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Categoría eliminada']);
    }
}