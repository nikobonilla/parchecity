@php
    $k = $datos['kpi'];
    $total = (int) $k->total;
    $tasaCancelacion = $total > 0 ? round($k->canceladas / $total * 100) : 0;
    $maximo = max(1, (int) collect($datos['serie'])->max('total'));

    $etiquetaPeriodo = function ($fila) use ($rango) {
        $f = \Carbon\Carbon::parse($fila->primera);

        return match ($rango['agrupar']) {
            'dia' => $f->format('d/m/Y') . ' (' . $f->translatedFormat('l') . ')',
            'semana' => 'Semana del ' . $f->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->format('d/m') . ' al ' . $f->copy()->endOfWeek(\Carbon\Carbon::SUNDAY)->format('d/m/Y'),
            'mes' => ucfirst($f->translatedFormat('F Y')),
            'anio' => $f->format('Y'),
        };
    };
@endphp

<p class="text-muted">
    Del <strong>{{ $rango['desde']->format('d/m/Y') }}</strong> al <strong>{{ $rango['hasta']->format('d/m/Y') }}</strong>
    ({{ $rango['dias'] }} {{ $rango['dias'] === 1 ? 'día' : 'días' }}) ·
    {{ strtolower(\App\Services\EstadisticasService::AGRUPACIONES[$rango['agrupar']]) }} ·
    según la fecha de la reserva.
</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center p-3 h-100">
            <div class="display-6">{{ $total }}</div>
            <div class="text-muted small">Reservas</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3 h-100 border-success">
            <div class="display-6 text-success">{{ (int) $k->completadas }}</div>
            <div class="text-muted small">Completadas</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3 h-100 border-primary">
            <div class="display-6 text-primary">{{ (int) $k->confirmadas }}</div>
            <div class="text-muted small">Confirmadas (por venir)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3 h-100 border-warning">
            <div class="display-6 text-warning">{{ (int) $k->pendientes }}</div>
            <div class="text-muted small">Pendientes de pago</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card text-center p-3 h-100 border-danger">
            <div class="display-6 text-danger">{{ (int) $k->canceladas }}</div>
            <div class="text-muted small">Canceladas ({{ $tasaCancelacion }}%)</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card text-center p-3 h-100 border-success">
            <div class="fs-3 fw-bold text-success">@cop($k->ingresos)</div>
            <div class="text-muted small">Ingresos generados (pagadas)</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card text-center p-3 h-100">
            <div class="fs-3 fw-bold text-secondary">@cop($k->reembolsos)</div>
            <div class="text-muted small">Reembolsado</div>
        </div>
    </div>
</div>

<h4>Reservas en el tiempo</h4>
<div class="table-responsive mb-4">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Periodo</th>
                <th style="width: 30%;">Reservas</th>
                <th>Completadas</th>
                <th>Canceladas</th>
                <th>Ingresos</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datos['serie'] as $fila)
                <tr>
                    <td>{{ $etiquetaPeriodo($fila) }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height: 10px;">
                                <div class="progress-bar" style="width: {{ round($fila->total / $maximo * 100) }}%"></div>
                            </div>
                            <strong>{{ (int) $fila->total }}</strong>
                        </div>
                    </td>
                    <td>{{ (int) $fila->completadas }}</td>
                    <td>{{ (int) $fila->canceladas }}</td>
                    <td>@cop($fila->ingresos)</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted">No hay reservas en este rango.</td></tr>
            @endforelse
        </tbody>
        @if ($datos['serie']->count() > 1)
            <tfoot>
                <tr class="fw-bold">
                    <td>Total</td>
                    <td>{{ $total }}</td>
                    <td>{{ (int) $k->completadas }}</td>
                    <td>{{ (int) $k->canceladas }}</td>
                    <td>@cop($k->ingresos)</td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

<h4>Servicios más solicitados</h4>
<div class="row g-3 mb-4">
    @if ($datos['negocios']->count() > 1)
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h6>Negocios más reservados</h6>
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach ($datos['negocios'] as $n)
                            <tr>
                                <td>{{ $n->nombre }} <span class="text-muted small">({{ ucfirst($n->tipo) }})</span></td>
                                <td class="text-end"><strong>{{ $n->total }}</strong></td>
                                <td class="text-end text-muted">@cop($n->ingresos)</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div></div>
        </div>
    @endif

    @if ($datos['habitaciones']->isNotEmpty())
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h6>Habitaciones más reservadas</h6>
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach ($datos['habitaciones'] as $h)
                            <tr>
                                <td>{{ $h->nombre }}</td>
                                <td class="text-end"><strong>{{ $h->total }}</strong></td>
                                <td class="text-end text-muted">@cop($h->ingresos)</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div></div>
        </div>
    @endif

    @if ($datos['modalidades']->isNotEmpty())
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h6>Por horas o por noches</h6>
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach ($datos['modalidades'] as $m)
                            <tr>
                                <td>{{ $m->modalidad === 'horas' ? 'Por horas' : 'Por noches' }}</td>
                                <td class="text-end"><strong>{{ $m->total }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div></div>
        </div>
    @endif

    @if ($datos['franjas']->isNotEmpty())
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h6>Franjas más reservadas (restaurantes)</h6>
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach ($datos['franjas'] as $f)
                            <tr>
                                <td>{{ $f->nombre }}</td>
                                <td class="text-end"><strong>{{ $f->total }}</strong></td>
                                <td class="text-end text-muted">@cop($f->ingresos)</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div></div>
        </div>
    @endif

    @if ($datos['grupos']->isNotEmpty())
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h6>Tamaño de grupo (restaurantes)</h6>
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach ($datos['grupos'] as $g)
                            <tr>
                                <td>{{ $g->personas }} {{ $g->personas == 1 ? 'persona' : 'personas' }}</td>
                                <td class="text-end"><strong>{{ $g->total }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div></div>
        </div>
    @endif

    @if ($total === 0)
        <p class="text-muted">Cuando haya reservas en este rango, aquí verás lo más solicitado.</p>
    @endif
</div>