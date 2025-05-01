<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estado;
use Illuminate\Http\Request;

class EstadoController extends Controller
{
    public function index()
    {
        $estados = Estado::latest()->get();
        return response()->json($estados);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'indicador' => 'required|in:SI,NO',
        ]);

        Estado::create([
            'nombre' => $validated['nombre'],
            'indicador' => $validated['indicador'] === 'SI',
        ]);

        return response()->json(['message' => 'Estado creado correctamente']);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'indicador' => 'required|in:SI,NO',
        ]);

        $estado = Estado::findOrFail($id);
        $estado->update([
            'nombre' => $validated['nombre'],
            'indicador' => $validated['indicador'] === 'SI',
        ]);

        return response()->json(['message' => 'Estado actualizado correctamente']);
    }

    public function destroy($id)
    {
        $estado = Estado::findOrFail($id);
        $estado->delete();

        return response()->json(['message' => 'Estado eliminado correctamente']);
    }
}
