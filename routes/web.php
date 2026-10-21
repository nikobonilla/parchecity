<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NegocioPublicoController;
use App\Http\Controllers\NegocioController;
use App\Http\Controllers\Admin\NegocioAdminController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\TarifaController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\CupoController;
use App\Http\Controllers\ReservaDuenoController;
use App\Http\Controllers\ReservaClienteController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\TipoHabitacionController;
use App\Http\Controllers\CupoHabitacionController;


    Route::view('/terminos', 'terminos')->name('terminos');
    Route::get('/', [NegocioPublicoController::class, 'index'])->name('home');
    Route::get('/restaurantes', [NegocioPublicoController::class, 'restaurantes'])->name('restaurantes');
    Route::get('/hoteles', [NegocioPublicoController::class, 'hoteles'])->name('hoteles');
    Route::get('/moteles', [NegocioPublicoController::class, 'moteles'])->name('moteles');
    Route::get('/negocios/{negocio}', [NegocioPublicoController::class, 'show'])->name('negocios.show');
    Route::get('/negocios/{negocio}/habitaciones/{tipoHabitacion}', [NegocioPublicoController::class, 'habitacion'])->name('habitaciones.show');
    Route::get('/dashboard', function () { return view('dashboard');})->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/mis-reservas', [ReservaClienteController::class, 'index'])->name('cliente.reservas.index');
    Route::post('/mis-reservas/{reserva}/cancelar', [ReservaClienteController::class, 'cancelar'])->name('cliente.reservas.cancelar');
    Route::post('/negocios/{negocio}/reservar', [ReservaController::class, 'store'])->name('reservas.store');
    Route::post('/mis-reservas/{reserva}/pagado', [ReservaClienteController::class, 'marcarPagado'])->name('cliente.reservas.pagado');
    Route::get('/mis-reservas/{reserva}/pagar', [PagoController::class, 'formulario'])->name('cliente.reservas.pagar');
    Route::post('/mis-reservas/{reserva}/pagar', [PagoController::class, 'procesar'])->name('cliente.reservas.pagar.procesar');
    Route::post('/negocios/{negocio}/resenas', [ResenaController::class, 'store'])->name('resenas.store');
    Route::delete('/resenas/{resena}', [ResenaController::class, 'destroy'])->name('resenas.destroy');

});

    Route::middleware(['auth', 'dueño'])->group(function () {
    Route::get('/panel-dueno/negocios', [NegocioController::class, 'index'])->name('dueno.negocios.index');
    Route::get('/panel-dueno/negocios/crear', [NegocioController::class, 'create'])->name('dueno.negocios.create');
    Route::post('/panel-dueno/negocios', [NegocioController::class, 'store'])->name('dueno.negocios.store');
    Route::get('/panel-dueno/negocios/{negocio}/editar', [NegocioController::class, 'edit'])->name('dueno.negocios.edit');
    Route::put('/panel-dueno/negocios/{negocio}', [NegocioController::class, 'update'])->name('dueno.negocios.update');
    Route::post('/panel-dueno/negocios/{negocio}/tarifas', [TarifaController::class, 'store'])->name('dueno.tarifas.store');
    Route::delete('/panel-dueno/tarifas/{tarifa}', [TarifaController::class, 'destroy'])->name('dueno.tarifas.destroy');
    Route::delete('/panel-dueno/imagenes/{imagen}', [NegocioController::class, 'eliminarImagen'])->name('dueno.imagenes.destroy');
    Route::get('/panel-dueno/negocios/{negocio}/cupos', [CupoController::class, 'index'])->name('dueno.cupos.index');
    Route::put('/panel-dueno/negocios/{negocio}/cupos', [CupoController::class, 'update'])->name('dueno.cupos.update');
    Route::get('/panel-dueno/reservas', [ReservaDuenoController::class, 'index'])->name('dueno.reservas.index');
    Route::post('/panel-dueno/reservas/{reserva}/confirmar-pago', [ReservaDuenoController::class, 'confirmarPago'])->name('dueno.reservas.confirmarPago');
    Route::post('/panel-dueno/reservas/{reserva}/cancelar', [ReservaDuenoController::class, 'cancelar'])->name('dueno.reservas.cancelar');
    Route::get('/panel-dueno/negocios/{negocio}/habitaciones', [TipoHabitacionController::class, 'index'])->name('dueno.habitaciones.index');
    Route::post('/panel-dueno/negocios/{negocio}/habitaciones', [TipoHabitacionController::class, 'store'])->name('dueno.habitaciones.store');
    Route::delete('/panel-dueno/habitaciones/{tipoHabitacion}', [TipoHabitacionController::class, 'destroy'])->name('dueno.habitaciones.destroy');
    Route::get('/panel-dueno/habitaciones/{tipoHabitacion}/cupos', [CupoHabitacionController::class, 'index'])->name('dueno.habitaciones.cupos');
    Route::put('/panel-dueno/habitaciones/{tipoHabitacion}/cupos', [CupoHabitacionController::class, 'update'])->name('dueno.habitaciones.cupos.update');
    Route::put('/panel-dueno/habitaciones/{tipoHabitacion}', [TipoHabitacionController::class, 'update'])->name('dueno.habitaciones.update');
    Route::delete('/panel-dueno/categorias/{categoria}', [TipoHabitacionController::class, 'destroyCategoria'])->name('dueno.categorias.destroy');
    Route::post('/panel-dueno/negocios/{negocio}/categorias', [TipoHabitacionController::class, 'storeCategoria'])->name('dueno.categorias.store');
    Route::delete('/panel-dueno/imagenes-habitacion/{imagenHabitacion}', [TipoHabitacionController::class, 'eliminarImagen'])->name('dueno.imagenesHabitacion.destroy');

});

    Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [DashboardAdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/negocios/pendientes', [NegocioAdminController::class, 'pendientes'])->name('admin.negocios.pendientes');
    Route::get('/negocios', [NegocioAdminController::class, 'index'])->name('admin.negocios.index');
    Route::post('/negocios/{negocio}/aprobar', [NegocioAdminController::class, 'aprobar'])->name('admin.negocios.aprobar');
    Route::post('/negocios/{negocio}/rechazar', [NegocioAdminController::class, 'rechazar'])->name('admin.negocios.rechazar');
    Route::get('/configuracion', [ConfiguracionController::class, 'edit'])->name('admin.configuracion.edit');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('admin.configuracion.update');
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios.index');
    Route::put('/usuarios/{usuario}/rol', [UsuarioController::class, 'actualizarRol'])->name('admin.usuarios.rol');
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('admin.usuarios.destroy');
});

require __DIR__.'/auth.php';