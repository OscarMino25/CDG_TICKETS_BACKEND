<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrupoController extends Controller
{
    public function index()
    {
        $grupos = Grupo::with(['creador', 'usuarios'])->get();
        return response()->json($grupos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'usuarios' => 'array',
            'usuarios.*' => 'exists:users,id',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user(); // Esto elimina el error de Intelephense

        $grupo = Grupo::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'created_by' => $user?->id,
        ]);

        $grupo->usuarios()->sync($request->usuarios ?? []);

        return response()->json(['message' => 'Grupo creado correctamente']);
    }

    public function show($id)
    {
        $grupo = Grupo::with(['usuarios', 'creador'])->findOrFail($id);
        return response()->json($grupo);
    }

    public function update(Request $request, $id)
    {
        $grupo = Grupo::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'usuarios' => 'array',
            'usuarios.*' => 'exists:users,id',
        ]);

        $grupo->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        $grupo->usuarios()->sync($request->usuarios ?? []);

        return response()->json(['message' => 'Grupo actualizado correctamente']);
    }

    public function destroy($id)
    {
        $grupo = Grupo::findOrFail($id);
        $grupo->delete();

        return response()->json(['message' => 'Grupo eliminado']);
    }
}
