<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JornadaLaboral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- IMPORTANTE

class JornadaLaboralController extends Controller
{
    public function index()
    {
        return JornadaLaboral::all();
    }

    public function updateMultiple(Request $request)
    {
        $request->validate([
            'jornadas' => 'required|array',
            'jornadas.*.dia' => 'required|string',
            'jornadas.*.hora_inicio' => 'nullable|date_format:H:i',
            'jornadas.*.hora_fin' => 'nullable|date_format:H:i',
        ]);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        foreach ($request->jornadas as $item) {
            JornadaLaboral::updateOrCreate(
                ['dia' => $item['dia']],
                [
                    'hora_inicio' => $item['hora_inicio'],
                    'hora_fin' => $item['hora_fin'],
                    'creado_por' => $user?->name ?? 'sistema',
                ]
            );
        }

        return response()->json(['message' => 'Jornadas actualizadas correctamente']);
    }
}
