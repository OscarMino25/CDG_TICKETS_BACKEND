<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Formulario;
use App\Models\FormularioCampo;
use Illuminate\Http\Request;

class FormularioController extends Controller
{
   // Obtener formulario por submotivo
   public function showBySubmotivo($submotivoId)
   {
       $formulario = Formulario::with('campos')->where('submotivo_id', $submotivoId)->first();
       return response()->json($formulario);
   }

   // Guardar formulario visual (campos)
   public function storeVisual(Request $request)
   {
       $validated = $request->validate([
           'submotivo_id' => 'required|exists:submotivos,id',
           'campos' => 'required|array',
           'campos.*.nombre' => 'required|string',
           'campos.*.etiqueta' => 'required|string',
           'campos.*.placeholder' => 'nullable|string',
           'campos.*.fila' => 'required|integer',
           'campos.*.tipo' => 'required|in:texto,numerico,correo',
           'campos.*.requerido' => 'required|boolean',
           'campos.*.validadores' => 'nullable|array',
           'campos.*.orden' => 'nullable|integer',
       ]);

       // Crear formulario
       $formulario = Formulario::create([
           'submotivo_id' => $validated['submotivo_id'],
       ]);

       // Crear campos
       foreach ($validated['campos'] as $campo) {
           $formulario->campos()->create($campo);
       }

       return response()->json(['message' => 'Formulario creado correctamente.'], 201);
   }

   // Guardar formulario por JSON
   public function storeJSON(Request $request)
   {
       $validated = $request->validate([
           'submotivo_id' => 'required|exists:submotivos,id',
           'formulario_json' => 'required|array',
       ]);

       Formulario::create($validated);

       return response()->json(['message' => 'Formulario en formato JSON guardado correctamente.'], 201);
   }

   // Actualizar formulario por JSON
   public function updateJSON(Request $request, $id)
   {
       $validated = $request->validate([
           'formulario_json' => 'required|array',
       ]);

       $formulario = Formulario::findOrFail($id);
       $formulario->update([
           'formulario_json' => $validated['formulario_json'],
       ]);

       return response()->json(['message' => 'Formulario actualizado.']);
   }

   // Eliminar un formulario
   public function destroy($id)
   {
       $formulario = Formulario::findOrFail($id);
       $formulario->delete();

       return response()->json(['message' => 'Formulario eliminado.']);
   }

   // Actualizar formulario visual sin reemplazar todos los campos
public function updateVisual(Request $request, $id)
{
    $validated = $request->validate([
        'campos' => 'required|array',
        'campos.*.nombre' => 'required|string',
        'campos.*.etiqueta' => 'required|string',
        'campos.*.placeholder' => 'nullable|string',
        'campos.*.fila' => 'required|integer',
        'campos.*.tipo' => 'required|in:texto,numerico,correo',
        'campos.*.requerido' => 'required|boolean',
        'campos.*.validadores' => 'nullable|array',
        'campos.*.orden' => 'nullable|integer',
        'campos.*.id' => 'nullable|integer|exists:formulario_campos,id',
        'campos_para_eliminar' => 'nullable|array',
        'campos_para_eliminar.*' => 'integer|exists:formulario_campos,id',
    ]);

    $formulario = Formulario::findOrFail($id);

    // Eliminar campos marcados
    if (!empty($validated['campos_para_eliminar'])) {
        FormularioCampo::destroy($validated['campos_para_eliminar']);
    }

    // Procesar campos (crear o actualizar)
    foreach ($validated['campos'] as $campoData) {
        if (isset($campoData['id'])) {
            // Actualizar campo existente
            $campo = FormularioCampo::find($campoData['id']);
            $campo->update($campoData);
        } else {
            // Crear nuevo campo
            $formulario->campos()->create($campoData);
        }
    }

    return response()->json(['message' => 'Formulario actualizado correctamente.']);
}
}
