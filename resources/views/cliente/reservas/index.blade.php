@extends('layouts.main')

@section('contenido')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1>Mis reservas</h1>
        <div class="d-flex gap-2 flex-wrap">
            @if (request()->boolean('pasadas'))
                <a href="{{ route('cliente.reservas.index') }}" class="btn btn-outline-secondary">Ver solo próximas</a>
            @else
                <a href="{{ route('cliente.reservas.index', ['pasadas' => 1]) }}" class="btn btn-outline-secondary">Ver también pasadas</a>
            @endif
            <a href="{{ route('cliente.reservas.historial') }}" class="btn btn-outline-dark">Historial</a>
            <a href="{{ route('cliente.tarjetas.index') }}" class="btn btn-outline-primary">Mis tarjetas</a>
        </div>
    </div>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="text-muted small">
        Tienes {{ \App\Models\Reserva::MINUTOS_PARA_PAGAR }} minutos desde que reservas para pagar con tarjeta; si no pagas, la reserva se cancela sola.
        Después de pagar tienes {{ \App\Models\Reserva::MINUTOS_REEMBOLSO }} minutos para pedir reembolso.
        Para cambiar la fecha, la hora o las personas, cancela y haz una nueva reserva.
    </p>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Negocio</th>
                    <th>Detalle</th>
                    <th>Personas</th>
                    <th>Valor</th>
                    <th>Estado</th>
                    <th>Pago</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservas as $reserva)
                    @php
                        $activa = $reserva->estaActiva();
                        $colorPago = ['pagado' => 'success', 'reembolsado' => 'secondary'][$reserva->pago_estado] ?? 'warning';
                        $hastaReembolso = $reserva->reembolsoHasta();
                        $limitePago = $reserva->created_at->copy()->addMinutes(\App\Models\Reserva::MINUTOS_PARA_PAGAR);
                    @endphp
                    <tr>
                        <td>
                            @if ($reserva->codigo)
                                <strong>{{ $reserva->codigo }}</strong>
                            @else
                                <span class="text-muted small">Se genera al pagar</span>
                            @endif
                        </td>
                        <td><a href="{{ route('negocios.show', $reserva->negocio) }}">{{ $reserva->negocio->nombre }}</a></td>
                        <td>@include('partials.detalle-reserva')</td>
                        <td>{{ $reserva->personas ?? '-' }}</td>
                        <td>@cop($reserva->valor_reserva)</td>
                        <td>
                            <span class="badge bg-{{ $reserva->estado === 'confirmada' ? 'success' : ($reserva->estado === 'cancelada' ? 'danger' : 'warning') }}">
                                {{ ucfirst($reserva->estado) }}
                            </span>
                            @if ($reserva->liberada_at)
                                <span class="badge bg-info">Liberada</span>
                            @endif

                            @if ($activa && $hastaReembolso)
                                @if ($reserva->puedeReembolsar())
                                    <div class="small text-success mt-1">
                                        Reembolso disponible:
                                        <span class="badge text-bg-success font-monospace" data-cuenta="{{ $hastaReembolso->toIso8601String() }}" data-vencido="plazo vencido">--:--</span>
                                        <span class="text-muted">(hasta las @hora($hastaReembolso))</span>
                                    </div>
                                @else
                                    <div class="small text-muted mt-1">El plazo de reembolso venció a las @hora($hastaReembolso)</div>
                                @endif
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $colorPago }}">{{ ucfirst($reserva->pago_estado) }}</span>

                            @if ($reserva->tarjeta_ultimos4)
                                <div class="small text-muted">
                                    •••• {{ $reserva->tarjeta_ultimos4 }}
                                    @if ($reserva->cuotas && $reserva->cuotas > 1)
                                        · {{ $reserva->cuotas }} cuotas
                                    @endif
                                </div>
                            @endif

                            @if ($activa && $reserva->pago_estado === 'pendiente')
                                <div class="small text-danger mt-1">
                                    Paga en
                                    <span class="badge text-bg-danger font-monospace" data-cuenta="{{ $limitePago->toIso8601String() }}" data-recargar="1" data-vencido="venció">--:--</span>
                                    <span class="text-muted">(antes de las @hora($limitePago))</span>
                                </div>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if ($activa && $reserva->pago_estado === 'pendiente')
                                <a href="{{ route('cliente.reservas.pagar', $reserva) }}" class="btn btn-sm btn-primary">Pagar con tarjeta</a>
                            @endif
                            @if ($activa)
                                <a href="{{ route('cliente.reservas.editar', $reserva) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form method="POST" action="{{ route('cliente.reservas.cancelar', $reserva) }}" class="d-inline" onsubmit="return confirm('¿Cancelar esta reserva?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Cancelar</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('cliente.reservas.eliminar', $reserva) }}" class="d-inline" onsubmit="return confirm('¿Eliminar de tu lista? Si la reserva está activa, también se cancelará. Seguirá apareciendo en tu historial.')">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">Todavía no tienes reservas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        // Relojes de cuenta regresiva (mm:ss) para el plazo de pago y el de reembolso
        function dosDigitos(n) {
            return String(n).padStart(2, '0');
        }

        function actualizarRelojes() {
            document.querySelectorAll('[data-cuenta]').forEach(function (el) {
                const restante = new Date(el.dataset.cuenta) - new Date();

                if (restante <= 0) {
                    el.textContent = el.dataset.vencido || 'venció';
                    el.classList.remove('text-bg-success', 'text-bg-danger');
                    el.classList.add('text-bg-secondary');
                    el.removeAttribute('data-cuenta');

                    if (el.dataset.recargar) {
                        setTimeout(function () { location.reload(); }, 1500);
                    }
                    return;
                }

                const total = Math.floor(restante / 1000);
                el.textContent = dosDigitos(Math.floor(total / 60)) + ':' + dosDigitos(total % 60);
            });
        }

        actualizarRelojes();
        setInterval(actualizarRelojes, 1000);
    </script>
@endsection