@php
    $hoy = now();
    $conservar = request()->except(['dia', 'desde', 'hasta', 'page']);
    $atajos = [
        'Hoy' => [$hoy->toDateString(), $hoy->toDateString()],
        'Esta semana' => [
            $hoy->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString(),
            $hoy->copy()->endOfWeek(\Carbon\Carbon::SUNDAY)->toDateString(),
        ],
        'Este mes' => [$hoy->copy()->startOfMonth()->toDateString(), $hoy->copy()->endOfMonth()->toDateString()],
        'Este año' => [$hoy->copy()->startOfYear()->toDateString(), $hoy->copy()->endOfYear()->toDateString()],
    ];
@endphp

<div class="d-flex flex-wrap gap-2 mb-3">
    @foreach ($atajos as $etiqueta => [$d, $h])
        <a class="btn btn-sm btn-outline-secondary" href="{{ url()->current() }}?{{ http_build_query(array_merge($conservar, ['desde' => $d, 'hasta' => $h])) }}">{{ $etiqueta }}</a>
    @endforeach
</div>

<div class="row g-2 mb-2">
    <div class="col-md-3">
        <label class="form-label small">Un solo día</label>
        <input type="date" name="dia" id="estDia" class="form-control" value="{{ request('dia') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label small">Desde</label>
        <input type="date" name="desde" id="estDesde" class="form-control" value="{{ request('dia') ? '' : $rango['desde']->toDateString() }}">
    </div>
    <div class="col-md-3">
        <label class="form-label small">Hasta</label>
        <input type="date" name="hasta" id="estHasta" class="form-control" value="{{ request('dia') ? '' : $rango['hasta']->toDateString() }}">
    </div>
    <div class="col-md-3">
        <label class="form-label small">Agrupar</label>
        <select name="agrupar" class="form-select">
            <option value="">Automático</option>
            @foreach (\App\Services\EstadisticasService::AGRUPACIONES as $clave => $etiqueta)
                <option value="{{ $clave }}" @selected(request('agrupar') === $clave)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>
</div>

<script>
    (function () {
        const dia = document.getElementById('estDia');
        const desde = document.getElementById('estDesde');
        const hasta = document.getElementById('estHasta');

        // El día y el rango no se mezclan: al usar uno, se limpia el otro
        dia.addEventListener('change', function () {
            if (dia.value) { desde.value = ''; hasta.value = ''; }
        });

        [desde, hasta].forEach(function (campo) {
            campo.addEventListener('change', function () {
                if (campo.value) { dia.value = ''; }
            });
        });
    })();
</script>