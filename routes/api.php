<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\TipificacionController;
use App\Http\Controllers\Api\MotivoController;
use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\GrupoController;
use App\Http\Controllers\Api\SubmotivoController;
use App\Http\Controllers\Api\FormularioController;
use App\Http\Controllers\Api\MatrizAtencionController;
use App\Http\Controllers\Api\JornadaLaboralController;
use App\Http\Controllers\Api\FeriadoController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketArchivoController;
use App\Http\Controllers\Api\TicketComentarioController;
use App\Http\Controllers\Api\TicketHistorialController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\SlaController;
use App\Http\Middleware\IsUserAuth;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


//PUBLIC ROUTES
Route::post('login', [AuthController::class, 'login']);


//PRIVATE ROUTES
Route::middleware([IsUserAuth::class])->group(function () {

    Route::controller(AuthController::class)->group(function () {
        Route::post('logout', 'logout');
        Route::get('me', 'getUser');
    });

    // Ruta para obtener todos los permisos de usuario
    Route::get('mis-permisos', [AuthController::class, 'getUserPermissions']);

    // Ruta para actualizar el perfil del usuario
    Route::put('me', [UserController::class, 'updateProfile']);

    // Ruta para cambiar la contraseña
    Route::put('change-password', [UserController::class, 'changePassword']);

    // Ruta para ver los permisos asignados a un rol
    Route::get('/roles/{roleId}/permissions', [RolePermissionController::class, 'getPermissionsForRole']);

    Route::get('users', [UserController::class, 'index']); // Mostrar todos los usuarios

    Route::post('reportes/trazabilidad', [ReporteController::class, 'trazabilidad']);

    Route::post('reportes/estado-tickets', [ReporteController::class, 'estadoTickets']);

    Route::post('reportes/trazabilidad/exportar', [ReporteController::class, 'exportarTrazabilidad']);

    Route::post('reportes/estado-tickets/exportar', [ReporteController::class, 'exportarEstadoTickets']);

    Route::get('/clientes/cedula/{cedula}', [ClienteController::class, 'buscarPorCedula']);
    Route::post('/clientes', [ClienteController::class, 'store']);
    Route::put('/clientes/{id}', [ClienteController::class, 'update']);

    Route::get('tickets', [TicketController::class, 'index']);
    Route::get('tickets/asignados', [TicketController::class, 'asignados']);
    Route::post('tickets', [TicketController::class, 'store']);
    Route::get('tickets/{id}', [TicketController::class, 'show']);
    Route::post('tickets/{ticket}/archivos', [TicketArchivoController::class, 'store']);
    Route::put('tickets/{ticket}/estado', [TicketController::class, 'updateEstado']);
    Route::get('tickets/{id}/atencion', [TicketController::class, 'showAtencion']);
    Route::post('tickets/{id}/atencion', [TicketController::class, 'atender']);
    Route::get('/tickets/descargar-archivo/{archivo}', [TicketArchivoController::class, 'descargar']);
    Route::post('/tickets/{id}/resolver', [TicketController::class, 'resolverOCerrar']);



    Route::get('tickets/{ticket}/comentarios', [TicketComentarioController::class, 'index']);
    Route::post('{ticket}/comentarios', [TicketComentarioController::class, 'store']);
    Route::delete('comentarios/{comentario}', [TicketComentarioController::class, 'destroy']);
    Route::get('comentarios/descargar/{archivo}', [TicketComentarioController::class, 'descargar']);

    Route::get('tickets/{ticket}/historial', [TicketHistorialController::class, 'index']);

    Route::post('users', [UserController::class, 'store']); // Crear un nuevo usuario
    Route::get('/users/search', [UserController::class, 'search']); // Búsqueda de usuarios
    Route::put('users/{id}', [UserController::class, 'update']); // Editar un usuario
    Route::delete('users/{id}', [UserController::class, 'destroy']); // Eliminar un usuario

    Route::get('roles', [RolePermissionController::class, 'indexRoles']); // Listar roles
    Route::post('roles', [RolePermissionController::class, 'storeRole']); // Crear un rol
    Route::delete('roles/{id}', [RolePermissionController::class, 'destroyRole']); // Eliminar un rol
    Route::put('roles/{roleId}/permissions', [RolePermissionController::class, 'assignPermissionsToRole']); // Asignar permisos a un rol

    Route::get('permissions', [RolePermissionController::class, 'indexPermissions']); // Listar permisos
    Route::post('permissions', [RolePermissionController::class, 'storePermission']); // Crear un permiso
    Route::delete('permissions/{id}', [RolePermissionController::class, 'destroyPermission']); // Eliminar un permiso

    Route::put('users/{userId}/roles-y-permisos', [UserController::class, 'asignarRolesYPermisos']); // Ruta para asignar roles y permisos a un usuario

    Route::get('catalogos', [CatalogoController::class, 'index']); // Mostrar todos los catálogos
    Route::post('catalogos', [CatalogoController::class, 'store']); // Crear un nuevo catálogo
    Route::get('catalogos/{id}', [CatalogoController::class, 'show']); // Ver un catálogo específico
    Route::put('catalogos/{id}', [CatalogoController::class, 'update']); // Actualizar un catálogo
    Route::delete('catalogos/{id}', [CatalogoController::class, 'destroy']); // Eliminar un catálogo

    Route::get('tipificaciones', [TipificacionController::class, 'index']); // Mostrar todas las tipificaciones
    Route::post('tipificaciones', [TipificacionController::class, 'store']); // Crear una nueva tipificación
    Route::get('tipificaciones/{id}', [TipificacionController::class, 'show']); // Ver una tipificación específica
    Route::put('tipificaciones/{id}', [TipificacionController::class, 'update']); // Actualizar una tipificación
    Route::delete('tipificaciones/{id}', [TipificacionController::class, 'destroy']); // Eliminar una tipificación

    Route::get('slas', [SlaController::class, 'index']); // Obtener todos los SLAs
    Route::post('slas', [SlaController::class, 'store']); // Crear un nuevo SLA
    Route::get('slas/{id}', [SlaController::class, 'show']); // Obtener un SLA por ID
    Route::put('slas/{id}', [SlaController::class, 'update']); // Actualizar un SLA
    Route::delete('slas/{id}', [SlaController::class, 'destroy']); // Eliminar un SLA

    Route::get('motivos', [MotivoController::class, 'index']); // Obtener todos los motivos
    Route::post('motivos', [MotivoController::class, 'store']); // Crear un nuevo motivo
    Route::get('motivos/{id}', [MotivoController::class, 'show']); // Obtener un motivo por ID
    Route::put('motivos/{id}', [MotivoController::class, 'update']); // Actualizar un motivo
    Route::delete('motivos/{id}', [MotivoController::class, 'destroy']); // Eliminar un motivo

    Route::get('estados', [EstadoController::class, 'index']); // Obtener todos los estados
    Route::post('estados', [EstadoController::class, 'store']); // Crear un nuevo estado
    Route::get('estados/{id}', [EstadoController::class, 'show']); // (opcional) Obtener un estado por ID
    Route::put('estados/{id}', [EstadoController::class, 'update']); // Actualizar un estado
    Route::delete('estados/{id}', [EstadoController::class, 'destroy']); // Eliminar un estado

    Route::get('grupos', [GrupoController::class, 'index']); // Obtener todos los grupos
    Route::post('grupos', [GrupoController::class, 'store']); // Crear un nuevo grupo
    Route::get('grupos/{id}', [GrupoController::class, 'show']); // Obtener un grupo por ID
    Route::put('grupos/{id}', [GrupoController::class, 'update']); // Actualizar un grupo
    Route::delete('grupos/{id}', [GrupoController::class, 'destroy']); // Eliminar un grupo

    Route::get('submotivos', [SubmotivoController::class, 'index']); // Obtener todos los submotivos
    Route::post('submotivos', [SubmotivoController::class, 'store']); // Crear un nuevo submotivo
    Route::get('submotivos/{id}', [SubmotivoController::class, 'show']); // Obtener un submotivo por ID
    Route::put('submotivos/{id}', [SubmotivoController::class, 'update']); // Actualizar un submotivo
    Route::delete('submotivos/{id}', [SubmotivoController::class, 'destroy']); // Eliminar un submotivo
    Route::get('/submotivos/por-motivo/{motivo_id}', [SubmotivoController::class, 'porMotivo']);


    Route::get('formularios/submotivo/{submotivoId}', [FormularioController::class, 'showBySubmotivo']); // Obtener el formulario asociado a un submotivo
    Route::post('formularios/visual', [FormularioController::class, 'storeVisual']); // Crear un formulario visual con campos individuales
    Route::post('formularios/json', [FormularioController::class, 'storeJSON']); // Crear un formulario a partir de un JSON (modo código)
    Route::put('formularios/json/{id}', [FormularioController::class, 'updateJSON']); // Actualizar el formulario en formato JSON
    Route::delete('formularios/{id}', [FormularioController::class, 'destroy']); // Eliminar un formulario (visual o por JSON)
    Route::put('formularios/visual/{id}', [FormularioController::class, 'updateVisual']);

    Route::get('/matriz-atencion/{submotivoId}', [MatrizAtencionController::class, 'index']); //Obtener las matrices de atencion del submotivo
    Route::post('/matriz-atencion', [MatrizAtencionController::class, 'store']); // Crear matriz de atencion
    Route::put('/matriz-atencion/{id}', [MatrizAtencionController::class, 'update']); // actualizar matriz de atencion
    Route::delete('/matriz-atencion/{id}', [MatrizAtencionController::class, 'destroy']); // Eliminar matriz de atencion

    Route::get('/jornada-laboral', [JornadaLaboralController::class, 'index']);
    Route::put('/jornada-laboral', [JornadaLaboralController::class, 'updateMultiple']);

    Route::get('/feriados', [FeriadoController::class, 'index']);
    Route::post('/feriados', [FeriadoController::class, 'store']);
    Route::put('/feriados/{feriado}', [FeriadoController::class, 'update']);
    Route::delete('/feriados/{feriado}', [FeriadoController::class, 'destroy']);

    Route::middleware([IsAdmin::class])->group(function () {


       
    });
});
