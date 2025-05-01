<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sla;
use Illuminate\Http\Request;

class SlaController extends Controller
{
    // Obtener todos los SLAs
    public function index()
    {
        return Sla::all();
    }

    // Crear un nuevo SLA
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tiempo_cliente' => 'required|integer',
            'tiempo_sistema' => 'required|integer',
        ]);

        $sla = Sla::create($request->all());

        return response()->json($sla, 201); // Retorna el SLA creado
    }

    // Obtener un SLA por ID
    public function show($id)
    {
        $sla = Sla::findOrFail($id);
        return response()->json($sla);
    }

    // Actualizar un SLA
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tiempo_cliente' => 'required|integer',
            'tiempo_sistema' => 'required|integer',
        ]);

        $sla = Sla::findOrFail($id);
        $sla->update($request->all());

        return response()->json($sla);
    }

    // Eliminar un SLA
    public function destroy($id)
    {
        $sla = Sla::findOrFail($id);
        $sla->delete();

        return response()->json(null, 204); // Retorna un código 204 para indicar que la eliminación fue exitosa
    }
}
