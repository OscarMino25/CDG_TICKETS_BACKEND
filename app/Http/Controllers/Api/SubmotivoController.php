<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submotivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmotivoController extends Controller
{
    public function index()
    {
        return Submotivo::with(['motivo', 'sla', 'creador'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'motivo_id' => 'required|exists:motivos,id',
            'sla_id' => 'required|exists:slas,id',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user(); // Esto resuelve el error de Intelephense

        $submotivo = Submotivo::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'motivo_id' => $request->motivo_id,
            'sla_id' => $request->sla_id,
            'created_by' => auth()->id(), 
        ]);

        return response()->json($submotivo->load(['motivo', 'sla', 'creador']), 201);
    }

    public function show($id)
    {
        // Buscar el submotivo por ID o retornar un error si no se encuentra
        $submotivo = Submotivo::with(['motivo', 'sla', 'creador'])->find($id);

        if (!$submotivo) {
            // Si no se encuentra, devolver un error 404
            return response()->json(['message' => 'Submotivo no encontrado'], 404);
        }

        return response()->json($submotivo);
    }

    public function update(Request $request, $id)
{
    $submotivo = Submotivo::findOrFail($id);

    $validated = $request->validate([
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'motivo_id' => 'required|exists:motivos,id',
        'sla_id' => 'required|exists:slas,id',
    ]);

    $submotivo->fill($validated);
    $submotivo->save();

    return response()->json($submotivo->load(['motivo', 'sla', 'creador']));
}
    

    public function destroy($id)
    {
        $submotivo = Submotivo::findOrFail($id);
        $submotivo->delete();

        return response()->json(null, 204);
    }

    public function porMotivo($motivo_id)
{
    $submotivos = Submotivo::with(['motivo', 'sla', 'creador'])
        ->where('motivo_id', $motivo_id)
        ->get();

    return response()->json($submotivos);
}
}
