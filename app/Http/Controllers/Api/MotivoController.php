<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Motivo;
use App\Models\Catalogo; // Importamos el modelo de Catalogo
use Illuminate\Http\Request;

class MotivoController extends Controller
{
    public function index()
{
    $motivos = Motivo::with(['tipificacion', 'catalogo'])->latest()->get();
    return response()->json($motivos);
}

    public function store(Request $request)
    {
        // Validación de los campos
        $request->validate([
            'nombre' => 'required|string|max:255',
            'catalogo_id' => 'required|exists:catalogos,id',  // Validamos que el catalogo_id exista
        ]);

        // Obtenemos el catálogo usando el catalogo_id
        $catalogo = Catalogo::find($request->catalogo_id);

        // Verificamos que el catalogo tenga el tipificacion_id igual a 2
        if (!$catalogo || $catalogo->tipificacion_id !== 2) {
            return response()->json(['error' => 'El catálogo debe tener tipificacion_id igual a 2'], 422);
        }

        // Creamos el motivo
        $motivo = Motivo::create($request->all());
        return response()->json($motivo, 201);
    }

    public function show($id)
    {
        // Obtener un motivo específico con su tipificación asociada
        $motivo = Motivo::with('tipificacion')->findOrFail($id);
        return response()->json($motivo);
    }

    public function update(Request $request, $id)
    {
        // Validación de los campos
        $request->validate([
            'nombre' => 'required|string|max:255',
            'catalogo_id' => 'required|exists:catalogos,id',  // Validamos que el catalogo_id exista
        ]);

        // Obtenemos el catálogo usando el catalogo_id
        $catalogo = Catalogo::find($request->catalogo_id);

        // Verificamos que el catalogo tenga el tipificacion_id igual a 2
        if (!$catalogo || $catalogo->tipificacion_id !== 2) {
            return response()->json(['error' => 'El catálogo debe tener tipificacion_id igual a 2'], 422);
        }

        // Encontramos el motivo a actualizar
        $motivo = Motivo::findOrFail($id);

        // Actualizamos el motivo con los nuevos datos
        $motivo->update($request->all());
        return response()->json($motivo);
    }

    public function destroy($id)
    {
        // Eliminar un motivo
        Motivo::findOrFail($id)->delete();
        return response()->json(['mensaje' => 'Motivo eliminado correctamente']);
    }
}
