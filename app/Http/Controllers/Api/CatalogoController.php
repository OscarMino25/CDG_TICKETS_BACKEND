<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Catalogo;
use App\Models\Tipificacion;

class CatalogoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retorna todos los catálogos, incluyendo la relación con Tipificacion
        return Catalogo::with('tipificacion')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validamos los datos, asegurándonos de que se pase un 'tipificacion_id'
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'activo' => 'required|boolean',
            'tipificacion_id' => 'required|exists:tipificaciones,id', // Validamos que el tipificacion_id sea válido
        ]);

        // Creamos el catalogo y asociamos la tipificación
        $catalogo = Catalogo::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo' => $request->activo,
            'tipificacion_id' => $request->tipificacion_id, // Asociamos la tipificación seleccionada
        ]);

        return response()->json($catalogo, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $catalogo = Catalogo::with('tipificacion')->findOrFail($id); // Cargamos la tipificación asociada
        return response()->json($catalogo);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Buscar el catálogo a actualizar
        $catalogo = Catalogo::findOrFail($id);

        // Validación de datos
        $request->validate([
            'nombre' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'activo' => 'nullable|boolean',
            'tipificacion_id' => 'nullable|exists:tipificaciones,id',  // Verifica que el tipificacion_id exista
        ]);

        // Actualizar los campos del catálogo
        $catalogo->update($request->all());

        return response()->json($catalogo); // Retorna el catálogo actualizado
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Buscar el catálogo a eliminar
        $catalogo = Catalogo::findOrFail($id);

        // Eliminar el catálogo
        $catalogo->delete();

        return response()->json(null, 204); // Retorna respuesta 204 (No Content) indicando que se eliminó
    }
}
