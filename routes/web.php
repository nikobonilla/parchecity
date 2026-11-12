<?php

use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\NegocioAdminController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\CupoController;
use App\Http\Controllers\CupoHabitacionController;
use App\Http\Controllers\NegocioController;
use App\Http\Controllers\NegocioPublicoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservaClienteController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\ReservaDuenoController;
use App\Http\Controllers\ReservaHabitacionController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\TarifaController;
use App\Http\Controllers\TipoHabitacionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TarjetaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\BuzonAdminController;
use App\Http\Controllers\BuzonController;
use App\Http\Controllers\NegocioEstadoController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\Admin\EstadisticasAdminController;
use App\Http\Controllers\EstadisticasDuenoController;

/* ===== Públicas ===== */
Route::get('/', [NegocioPublicoController::class, 'index'])->name('home');
Route::get('/restaurantes', [NegocioPublicoController::class, 'restaurantes'])->name('restaurantes');
Route::get('/hoteles', [NegocioPublicoController::class, 'hoteles'])->name('hoteles');
Route::get('/moteles', [NegocioPublicoController::class, 'moteles'])->name('moteles');
Route::get('/negocios/{negocio}', [NegocioPublicoController::class, 'show'])->name('negocios.show');
Route::get('/negocios/{negocio}/habitaciones/{tipoHabitacion}', [NegocioPublicoController::class, 'habitacion'])->name('habitaciones.show');
Route::view('/terminos', 'terminos')->name('terminos');
Route::get('/ubicaciones/municipios/{departamento}', [UbicacionController::class, 'municipios'])->where('departamento', '[0-9]{2}')->name('ubicaciones.municipios');
Route::get('/ubicaciones/localidades/{municipio}', [UbicacionController::class, 'localidades'])->where('municipio', '[0-9]{5}')->name('ubicaciones.localidades');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

/* ===== Cualquier usuario con sesión ===== */
    Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/buzon', [BuzonController::class, 'crear'])->name('buzon.crear');
    Route::post('/buzon', [BuzonController::class, 'store'])->middleware('throttle:5,1')->name('buzon.store');
    Route::get('/buzon/historial', [BuzonController::class, 'historial'])->name('buzon.historial');
    Route::post('/buzon/{mensaje}/eliminar', [BuzonController::class, 'eliminar'])->name('buzon.eliminar');
    Route::post('/buzon/{mensaje}/restaurar', [BuzonController::class, 'restaurar'])->name('buzon.restaurar');

    Route::post('/negocios/{negocio}/reservar', [ReservaController::class, 'store'])->name('reservas.store');
    Route::post('/negocios/{negocio}/habitaciones/{tipoHabitacion}/reservar', [ReservaHabitacionController::class, 'store'])->name('habitaciones.reservar');

    Route::get('/mis-reservas', [ReservaClienteController::class, 'index'])->name('cliente.reservas.index');
    Route::get('/mis-reservas/historial', [ReservaClienteController::class, 'historial'])->name('cliente.reservas.historial');
    Route::post('/mis-reservas/{reserva}/cancelar', [ReservaClienteController::class, 'cancelar'])->name('cliente.reservas.cancelar');
    Route::post('/mis-reservas/{reserva}/eliminar', [ReservaClienteController::class, 'eliminar'])->name('cliente.reservas.eliminar');
    Route::get('/mis-reservas/{reserva}/pagar', [PagoController::class, 'formulario'])->name('cliente.reservas.pagar');
    Route::post('/mis-reservas/{reserva}/pagar', [PagoController::class, 'procesar'])->name('cliente.reservas.pagar.procesar');
    Route::get('/mis-tarjetas/{tarjeta}/editar', [TarjetaController::class, 'edit'])->name('cliente.tarjetas.edit');
    Route::put('/mis-tarjetas/{tarjeta}', [TarjetaController::class, 'update'])->name('cliente.tarjetas.update');
    Route::get('/mis-reservas/{reserva}/editar', [ReservaClienteController::class, 'editar'])->name('cliente.reservas.editar');
    Route::put('/mis-reservas/{reserva}', [ReservaClienteController::class, 'actualizar'])->name('cliente.reservas.actualizar');
    Route::get('/mis-tarjetas', [TarjetaController::class, 'index'])->name('cliente.tarjetas.index');
    Route::post('/mis-tarjetas', [TarjetaController::class, 'store'])->name('cliente.tarjetas.store');
    Route::delete('/mis-tarjetas/{tarjeta}', [TarjetaController::class, 'destroy'])->name('cliente.tarjetas.destroy');

    Route::post('/negocios/{negocio}/resenas', [ResenaController::class, 'store'])->name('resenas.store');
    Route::delete('/resenas/{resena}', [ResenaController::class, 'destroy'])->name('resenas.destroy');

    Route::get('/buzon/mis-solicitudes', [BuzonController::class, 'mis'])->name('buzon.mis');

    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::get('/notificaciones/{id}/abrir', [NotificacionController::class, 'abrir'])->name('notificaciones.abrir');
    Route::post('/notificaciones/leer-todas', [NotificacionController::class, 'leerTodas'])->name('notificaciones.leerTodas');
    Route::delete('/notificaciones/limpiar', [NotificacionController::class, 'limpiar'])->name('notificaciones.limpiar');
    Route::delete('/notificaciones/{id}', [NotificacionController::class, 'destroy'])->name('notificaciones.destroy');

    Route::get('/favoritos', [FavoritoController::class, 'index'])->name('favoritos.index');
    Route::post('/favoritos/{negocio}', [FavoritoController::class, 'store'])->name('favoritos.store');
    Route::delete('/favoritos/{negocio}', [FavoritoController::class, 'destroy'])->name('favoritos.destroy');
});

/* ===== Dueño ===== */
    Route::middleware(['auth', 'dueño'])->group(function () {
    Route::put('/panel-dueno/tarifas/{tarifa}', [TarifaController::class, 'update'])->name('dueno.tarifas.update');
    Route::put('/panel-dueno/categorias/{categoria}', [TipoHabitacionController::class, 'updateCategoria'])->name('dueno.categorias.update');
    Route::get('/panel-dueno/negocios', [NegocioController::class, 'index'])->name('dueno.negocios.index');
    Route::get('/panel-dueno/negocios/crear', [NegocioController::class, 'create'])->name('dueno.negocios.create');
    Route::post('/panel-dueno/negocios', [NegocioController::class, 'store'])->name('dueno.negocios.store');
    Route::get('/panel-dueno/negocios/{negocio}/editar', [NegocioController::class, 'edit'])->name('dueno.negocios.edit');
    Route::put('/panel-dueno/negocios/{negocio}', [NegocioController::class, 'update'])->name('dueno.negocios.update');
    Route::delete('/panel-dueno/imagenes/{imagen}', [NegocioController::class, 'eliminarImagen'])->name('dueno.imagenes.destroy');
    Route::post('/panel-dueno/negocios/{negocio}/congelar', [NegocioEstadoController::class, 'congelar'])->name('dueno.negocios.congelar');
    Route::post('/panel-dueno/negocios/{negocio}/descongelar', [NegocioEstadoController::class, 'descongelar'])->name('dueno.negocios.descongelar');
    Route::delete('/panel-dueno/negocios/{negocio}', [NegocioEstadoController::class, 'destroy'])->name('dueno.negocios.destroy');
    Route::post('/panel-dueno/negocios/{negocio}/menus', [MenuController::class, 'store'])->name('dueno.menus.store');
    Route::put('/panel-dueno/menus/{menu}', [MenuController::class, 'update'])->name('dueno.menus.update');
    Route::delete('/panel-dueno/menus/{menu}', [MenuController::class, 'destroy'])->name('dueno.menus.destroy');
    Route::get('/panel-dueno/estadisticas', [EstadisticasDuenoController::class, 'index'])->name('dueno.estadisticas');

    // Restaurantes
    Route::post('/panel-dueno/negocios/{negocio}/tarifas', [TarifaController::class, 'store'])->name('dueno.tarifas.store');
    Route::delete('/panel-dueno/tarifas/{tarifa}', [TarifaController::class, 'destroy'])->name('dueno.tarifas.destroy');
    Route::get('/panel-dueno/negocios/{negocio}/cupos', [CupoController::class, 'index'])->name('dueno.cupos.index');
    Route::put('/panel-dueno/negocios/{negocio}/cupos', [CupoController::class, 'update'])->name('dueno.cupos.update');

    // Hoteles y moteles
    Route::get('/panel-dueno/negocios/{negocio}/habitaciones', [TipoHabitacionController::class, 'index'])->name('dueno.habitaciones.index');
    Route::post('/panel-dueno/negocios/{negocio}/habitaciones', [TipoHabitacionController::class, 'store'])->name('dueno.habitaciones.store');
    Route::put('/panel-dueno/habitaciones/{tipoHabitacion}', [TipoHabitacionController::class, 'update'])->name('dueno.habitaciones.update');
    Route::delete('/panel-dueno/habitaciones/{tipoHabitacion}', [TipoHabitacionController::class, 'destroy'])->name('dueno.habitaciones.destroy');
    Route::post('/panel-dueno/negocios/{negocio}/categorias', [TipoHabitacionController::class, 'storeCategoria'])->name('dueno.categorias.store');
    Route::delete('/panel-dueno/categorias/{categoria}', [TipoHabitacionController::class, 'destroyCategoria'])->name('dueno.categorias.destroy');
    Route::delete('/panel-dueno/imagenes-habitacion/{imagenHabitacion}', [TipoHabitacionController::class, 'eliminarImagen'])->name('dueno.imagenesHabitacion.destroy');
    Route::get('/panel-dueno/habitaciones/{tipoHabitacion}/cupos', [CupoHabitacionController::class, 'index'])->name('dueno.habitaciones.cupos');
    Route::put('/panel-dueno/habitaciones/{tipoHabitacion}/cupos', [CupoHabitacionController::class, 'update'])->name('dueno.habitaciones.cupos.update');

    // Reservas recibidas
    Route::get('/panel-dueno/reservas', [ReservaDuenoController::class, 'index'])->name('dueno.reservas.index');
    Route::get('/panel-dueno/reservas/historial', [ReservaDuenoController::class, 'historial'])->name('dueno.reservas.historial');
    Route::post('/panel-dueno/reservas/{reserva}/cancelar', [ReservaDuenoController::class, 'cancelar'])->name('dueno.reservas.cancelar');
    Route::post('/panel-dueno/reservas/{reserva}/liberar', [ReservaDuenoController::class, 'liberar'])->name('dueno.reservas.liberar');
    Route::post('/panel-dueno/reservas/{reserva}/eliminar', [ReservaDuenoController::class, 'eliminar'])->name('dueno.reservas.eliminar');
});

/* ===== Administrador ===== */
    Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [DashboardAdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/negocios/pendientes', [NegocioAdminController::class, 'pendientes'])->name('admin.negocios.pendientes');
    Route::get('/negocios', [NegocioAdminController::class, 'index'])->name('admin.negocios.index');
    Route::post('/negocios/{negocio}/aprobar', [NegocioAdminController::class, 'aprobar'])->name('admin.negocios.aprobar');
    Route::post('/negocios/{negocio}/rechazar', [NegocioAdminController::class, 'rechazar'])->name('admin.negocios.rechazar');
    Route::get('/negocios/{negocio}/editar', [NegocioAdminController::class, 'editar'])->name('admin.negocios.editar');
    Route::put('/negocios/{negocio}', [NegocioAdminController::class, 'actualizar'])->name('admin.negocios.actualizar');
    Route::post('/negocios/{negocio}/congelar', [NegocioAdminController::class, 'congelar'])->name('admin.negocios.congelar');
    Route::post('/negocios/{negocio}/descongelar', [NegocioAdminController::class, 'descongelar'])->name('admin.negocios.descongelar');
    Route::delete('/negocios/{negocio}', [NegocioAdminController::class, 'destroy'])->name('admin.negocios.destroy');
    Route::get('/buzon', [BuzonAdminController::class, 'index'])->name('admin.buzon.index');
    Route::put('/buzon/{mensaje}/estado', [BuzonAdminController::class, 'estado'])->name('admin.buzon.estado');
    Route::delete('/buzon/{mensaje}', [BuzonAdminController::class, 'destroy'])->name('admin.buzon.destroy');   
    Route::get('/configuracion', [ConfiguracionController::class, 'edit'])->name('admin.configuracion.edit');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('admin.configuracion.update');
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios.index');
    Route::put('/usuarios/{usuario}/rol', [UsuarioController::class, 'actualizarRol'])->name('admin.usuarios.rol');
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('admin.usuarios.destroy');
    Route::get('/negocios/eliminados', [NegocioAdminController::class, 'eliminados'])->name('admin.negocios.eliminados');
    Route::post('/negocios/{id}/restaurar', [NegocioAdminController::class, 'restaurar'])->name('admin.negocios.restaurar');
    Route::get('/buzon/historial', [BuzonAdminController::class, 'historial'])->name('admin.buzon.historial');
    Route::put('/buzon/{mensaje}/responder', [BuzonAdminController::class, 'responder'])->name('admin.buzon.responder');
    Route::post('/buzon/{mensaje}/restaurar', [BuzonAdminController::class, 'restaurar'])->name('admin.buzon.restaurar');
    Route::get('/estadisticas', [EstadisticasAdminController::class, 'index'])->name('admin.estadisticas');
});

require __DIR__.'/auth.php';