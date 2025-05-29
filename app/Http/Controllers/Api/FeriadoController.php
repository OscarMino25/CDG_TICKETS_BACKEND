<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feriado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeriadoController extends Controller
{
    public function index()
    {
        return Feriado::orderBy('fecha', 'asc')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha' => 'required|date|unique:feriados,fecha',
        ]);

        $feriado = Feriado::create([
            'nombre' => $request->nombre,
            'fecha' => $request->fecha,
            'creado_por' => Auth::user()?->name ?? 'sistema',
        ]);

        return response()->json($feriado, 201);
    }

    public function update(Request $request, Feriado $feriado)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha' => 'required|date|unique:feriados,fecha,' . $feriado->id,
        ]);

        $feriado->update([
            'nombre' => $request->nombre,
            'fecha' => $request->fecha,
        ]);

        return response()->json($feriado);
    }

    public function destroy(Feriado $feriado)
    {
        $feriado->delete();
        return response()->json(['message' => 'Feriado eliminado']);
    }
}
