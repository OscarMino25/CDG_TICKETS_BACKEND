<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatrizAtencion;
use Illuminate\Http\Request;

class MatrizAtencionController extends Controller
{
    // Obtener todas las matrices para un submotivo
    public function index($submotivoId)
    {
        $matrices = MatrizAtencion::with(['estado', 'grupoAsignacion', 'grupoVisualizacion'])
            ->where('submotivo_id', $submotivoId)
            ->get();

        return response()->json($matrices);
    }

    // Crear nueva entrada en matriz de atención
    public function store(Request $request)
    {
        $request->validate([
            'submotivo_id' => 'required|exists:submotivos,id',
            'estado_id' => 'required|exists:estados,id',
            'grupo_asignacion_id' => 'required|exists:grupos,id',
            'grupo_visualizacion_id' => 'required|exists:grupos,id',
        ]);

        $matriz = MatrizAtencion::create($request->all());

        return response()->json($matriz->load(['estado', 'grupoAsignacion', 'grupoVisualizacion']), 201);
    }

    // Actualizar una entrada existente
    public function update(Request $request, $id)
    {
        $matriz = MatrizAtencion::findOrFail($id);

        $request->validate([
            'estado_id' => 'required|exists:estados,id',
            'grupo_asignacion_id' => 'required|exists:grupos,id',
            'grupo_visualizacion_id' => 'required|exists:grupos,id',
        ]);

        $matriz->update($request->all());

        return response()->json($matriz->load(['estado', 'grupoAsignacion', 'grupoVisualizacion']));
    }

    // Eliminar una entrada
    public function destroy($id)
    {
        $matriz = MatrizAtencion::findOrFail($id);
        $matriz->delete();

        return response()->json(['message' => 'Matriz eliminada correctamente.']);
    }
}
