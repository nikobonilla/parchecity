@php
    $filtro = $filtro ?? false;
    $negocio = $negocio ?? null;

    $depSel = old('departamento_id', $filtro ? request('departamento_id') : ($negocio->departamento_id ?? null));
    $munSel = old('municipio_id', $filtro ? request('municipio_id') : ($negocio->municipio_id ?? null));
    $locSel = old('localidad_id', $filtro ? request('localidad_id') : ($negocio->localidad_id ?? null));

    $departamentos = \Illuminate\Support\Facades\DB::table('departamentos')->orderBy('nombre')->get();
    $col = $filtro ? 'col-md-4' : 'col-md-4';
@endphp

<div class="row g-2 {{ $filtro ? '' : 'mb-3' }}">
    <div class="{{ $col }}">
        <label class="form-label {{ $filtro ? 'small' : '' }}">Departamento</label>
        <select name="departamento_id" id="ubDepartamento" class="form-select" @required(! $filtro)>
            <option value="">{{ $filtro ? 'Todos' : 'Selecciona...' }}</option>
            @foreach ($departamentos as $d)
                <option value="{{ $d->id }}" @selected((string) $depSel === (string) $d->id)>{{ \App\Support\Formato::lugar($d->nombre) }}</option>
            @endforeach
        </select>
    </div>

    <div class="{{ $col }}">
        <label class="form-label {{ $filtro ? 'small' : '' }}">Municipio / ciudad</label>
        <select name="municipio_id" id="ubMunicipio" class="form-select" @required(! $filtro)>
            <option value="">{{ $filtro ? 'Todos' : 'Selecciona...' }}</option>
        </select>
    </div>

    <div class="{{ $col }}" id="ubLocalidadCaja" style="display:none;">
        <label class="form-label {{ $filtro ? 'small' : '' }}">Localidad</label>
        <select name="localidad_id" id="ubLocalidad" class="form-select"></select>
    </div>
</div>

@if (! $filtro)
    <div class="row g-2 mb-3">
        <div class="col-md-5">
            <label class="form-label">Barrio</label>
            <input type="text" name="barrio" class="form-control" value="{{ old('barrio', $negocio->barrio ?? '') }}" placeholder="Ej: Chapinero Alto">
        </div>
        <div class="col-md-7">
            <label class="form-label">Información adicional para ubicar el negocio</label>
            <input type="text" name="indicaciones" class="form-control" value="{{ old('indicaciones', $negocio->indicaciones ?? '') }}" placeholder="Ej: Frente al parque, segundo piso">
        </div>
    </div>
@endif

<script>
    (function () {
        const base = @json(url('/ubicaciones'));
        const dep = document.getElementById('ubDepartamento');
        const mun = document.getElementById('ubMunicipio');
        const locCaja = document.getElementById('ubLocalidadCaja');
        const loc = document.getElementById('ubLocalidad');
        const vacioMun = @json($filtro ? 'Todos' : 'Selecciona...');
        const vacioLoc = @json($filtro ? 'Todas' : 'Selecciona (opcional)...');

        function llenar(select, items, seleccionado, vacio) {
            select.innerHTML = '';
            const primera = document.createElement('option');
            primera.value = '';
            primera.textContent = vacio;
            select.appendChild(primera);

            items.forEach(function (i) {
                const op = document.createElement('option');
                op.value = i.id;
                op.textContent = i.nombre;
                if (seleccionado !== null && String(i.id) === String(seleccionado)) { op.selected = true; }
                select.appendChild(op);
            });
        }

        function ocultarLocalidades() {
            loc.innerHTML = '';
            locCaja.style.display = 'none';
        }

        async function cargarLocalidades(municipioId, seleccionada) {
            if (!municipioId) { ocultarLocalidades(); return; }

            const respuesta = await fetch(base + '/localidades/' + encodeURIComponent(municipioId));
            const items = await respuesta.json();

            if (!items.length) { ocultarLocalidades(); return; }

            llenar(loc, items, seleccionada, vacioLoc);
            locCaja.style.display = 'block';
        }

        async function cargarMunicipios(departamentoId, seleccionado, localidadSeleccionada) {
            if (!departamentoId) {
                llenar(mun, [], null, vacioMun);
                ocultarLocalidades();
                return;
            }

            const respuesta = await fetch(base + '/municipios/' + encodeURIComponent(departamentoId));
            llenar(mun, await respuesta.json(), seleccionado, vacioMun);
            await cargarLocalidades(mun.value, localidadSeleccionada);
        }

        dep.addEventListener('change', function () { cargarMunicipios(dep.value, null, null); });
        mun.addEventListener('change', function () { cargarLocalidades(mun.value, null); });

        cargarMunicipios(dep.value, @json($munSel), @json($locSel));
    })();
</script>