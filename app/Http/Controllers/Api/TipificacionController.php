<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tipificacion;
use Illuminate\Http\Request;

class TipificacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Tipificacion::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $tipificacion = Tipificacion::create($request->all());

        return response()->json($tipificacion, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tipificacion = Tipificacion::findOrFail($id);

        return response()->json($tipificacion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $tipificacion = Tipificacion::findOrFail($id);

        $tipificacion->update($request->all());

        return response()->json($tipificacion);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tipificacion = Tipificacion::findOrFail($id);
        $tipificacion->delete();

        return response()->json(null, 204);
    }
}
