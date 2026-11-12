<?php

namespace App\Providers;

use App\Models\BuzonMensaje;
use App\Models\Negocio;
use App\Models\Reserva;
use App\Models\Resena;
use App\Observers\BuzonMensajeObserver;
use App\Observers\NegocioObserver;
use App\Observers\ReservaObserver;
use App\Observers\ResenaObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Blade::directive('cop', fn ($expresion) => '<?php echo e(\App\Support\Formato::cop(' . $expresion . ')); ?>');
        Blade::directive('hora', fn ($expresion) => '<?php echo e(\App\Support\Formato::hora(' . $expresion . ')); ?>');

        // Contraseñas seguras en todo el sitio: 12 o más caracteres con mayúscula, minúscula, número y símbolo.
        // En producción también se rechazan las que aparecieron en filtraciones de datos.
        Password::defaults(function () {
            $regla = Password::min(12)->letters()->mixedCase()->numbers()->symbols();

            return app()->isProduction() ? $regla->uncompromised() : $regla;
        });

        // Quienes crean los avisos de la campana
        Reserva::observe(ReservaObserver::class);
        Negocio::observe(NegocioObserver::class);
        BuzonMensaje::observe(BuzonMensajeObserver::class);
        Resena::observe(ResenaObserver::class);
    }
}