<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function buscarPorCedula($cedula)
    {
        $cliente = Cliente::where('cedula', $cedula)->first();

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json($cliente);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cedula' => 'required|unique:clientes,cedula',
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'correo' => 'nullable|email',
            'telefono' => 'nullable|string',
        ]);

        $cliente = Cliente::create($request->all());

        return response()->json($cliente, 201);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $request->validate([
            'cedula' => 'required|unique:clientes,cedula,' . $cliente->id,
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'correo' => 'nullable|email',
            'telefono' => 'nullable|string',
        ]);

        $cliente->update($request->all());

        return response()->json($cliente);
    }
}
