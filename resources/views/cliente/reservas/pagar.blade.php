@extends('layouts.main')

@section('contenido')
    @php
        $limite = $reserva->created_at->copy()->addMinutes(\App\Models\Reserva::MINUTOS_PARA_PAGAR);
        $primera = $tarjetas->first(fn ($t) => ! $t->vencida());
        $seleccion = (string) old('tarjeta_id', $primera ? $primera->id : 'nueva');
    @endphp

    <div class="row justify-content-center">
        <div class="col-md-7">
            <h1 class="mb-1">Pagar reserva</h1>
            <p class="text-muted mb-1">{{ $reserva->negocio->nombre }}</p>
            <p class="text-danger small">
                Tienes hasta las @hora($limite) para pagar; después la reserva se cancela sola.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card border-primary mb-3">
                <div class="card-body">
                    <div class="text-muted small">Valor total a pagar</div>
                    <div class="fs-3 fw-bold">@cop($reserva->valor_reserva)</div>
                    <div class="small text-muted" id="detalleCuotas">Pago de contado</div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('cliente.reservas.pagar.procesar', $reserva) }}" id="formPago">
                        @csrf

                        <h6>¿Con qué tarjeta pagas?</h6>

                        @foreach ($tarjetas as $tarjeta)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="tarjeta_id" id="t{{ $tarjeta->id }}"
                                       value="{{ $tarjeta->id }}" data-tipo="{{ $tarjeta->tipo }}"
                                       @checked($seleccion === (string) $tarjeta->id) @disabled($tarjeta->vencida())>
                                <label class="form-check-label" for="t{{ $tarjeta->id }}">
                                    {{ $tarjeta->etiqueta() }}
                                    <small class="text-muted d-block">
                                        {{ $tarjeta->titular }} · vence {{ str_pad($tarjeta->mes, 2, '0', STR_PAD_LEFT) }}/{{ $tarjeta->ano }}
                                        @if ($tarjeta->vencida())
                                            <span class="badge bg-danger">Vencida</span>
                                        @endif
                                    </small>
                                </label>
                            </div>
                        @endforeach

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="tarjeta_id" id="tNueva" value="nueva" @checked($seleccion === 'nueva')>
                            <label class="form-check-label" for="tNueva">Usar otra tarjeta</label>
                        </div>

                        <div id="cajaNueva" class="border rounded p-3 mb-3" style="display:none;">
                            <div class="mb-3">
                                <label class="form-label">Tipo de tarjeta</label>
                                <select name="tipo" id="tipoNueva" class="form-select">
                                    <option value="credito" @selected(old('tipo') === 'credito')>Crédito</option>
                                    <option value="debito" @selected(old('tipo') === 'debito')>Débito</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Número de tarjeta</label>
                                <input type="text" name="numero" class="form-control" placeholder="Cualquier número de 12 a 19 dígitos" value="{{ old('numero') }}" maxlength="23">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nombre del titular</label>
                                <input type="text" name="titular" class="form-control" value="{{ old('titular', $reserva->nombre_cliente) }}">
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Mes</label>
                                    <input type="number" name="mes" class="form-control" placeholder="MM" min="1" max="12" value="{{ old('mes') }}">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Año</label>
                                    <input type="number" name="ano" class="form-control" placeholder="AA o AAAA" value="{{ old('ano') }}">
                                </div>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" name="guardar" value="1" class="form-check-input" id="guardar" @checked(! $errors->any() || old('guardar'))>
                                <label class="form-check-label" for="guardar">Guardar esta tarjeta en mi cuenta</label>
                            </div>
                        </div>

                        <div id="cajaCuotas" class="mb-3" style="display:none;">
                            <label class="form-label">Número de cuotas</label>
                            <select name="cuotas" id="cuotas" class="form-select">
                                @foreach ([1, 2, 3, 6, 12, 24, 36] as $n)
                                    <option value="{{ $n }}" @selected((int) old('cuotas', 1) === $n)>
                                        {{ $n === 1 ? '1 cuota (de contado)' : $n . ' cuotas' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">CVV</label>
                            <input type="text" name="cvv" class="form-control" style="max-width: 140px;" placeholder="123" maxlength="4" required>
                            <div class="form-text">Los 3 o 4 números del reverso. No se guarda.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Pagar @cop($reserva->valor_reserva)</button>
                    </form>

                    <p class="small mt-3 mb-0">
                        <a href="{{ route('cliente.tarjetas.index') }}">Administrar mis tarjetas</a>
                    </p>
                </div>
            </div>

            <p class="text-muted small mt-2">
                Ambiente de pruebas: el pago es simulado y no se cobra dinero real. Puedes usar cualquier número de tarjeta. Solo se pide que no esté vencida y un CVV de 3 o 4 números.
            </p>
        </div>
    </div>

    <script>
        const total = @json((float) $reserva->valor_reserva);

        const cajaNueva = document.getElementById('cajaNueva');
        const tipoNueva = document.getElementById('tipoNueva');
        const cajaCuotas = document.getElementById('cajaCuotas');
        const cuotasSel = document.getElementById('cuotas');
        const detalle = document.getElementById('detalleCuotas');

        function pesos(n) {
            return '$' + Math.round(n).toLocaleString('es-CO') + ' COP';
        }

        function seleccionada() {
            return document.querySelector('input[name="tarjeta_id"]:checked');
        }

        function tipoActual() {
            const r = seleccionada();
            if (!r) return null;
            return r.value === 'nueva' ? tipoNueva.value : r.dataset.tipo;
        }

        function cuotasActuales() {
            return tipoActual() === 'credito' ? Number(cuotasSel.value) : 1;
        }

        function actualizar() {
            const r = seleccionada();
            cajaNueva.style.display = (r && r.value === 'nueva') ? 'block' : 'none';
            cajaCuotas.style.display = tipoActual() === 'credito' ? 'block' : 'none';

            const n = cuotasActuales();
            detalle.textContent = n > 1
                ? n + ' cuotas de ' + pesos(total / n) + ' (sin intereses)'
                : 'Pago de contado';
        }

        document.querySelectorAll('input[name="tarjeta_id"]').forEach(el => el.addEventListener('change', actualizar));
        tipoNueva.addEventListener('change', actualizar);
        cuotasSel.addEventListener('change', actualizar);

        document.getElementById('formPago').addEventListener('submit', function (e) {
            const n = cuotasActuales();
            const texto = 'Vas a pagar un total de ' + pesos(total)
                + (n > 1 ? ' en ' + n + ' cuotas de ' + pesos(total / n) : '')
                + '. ¿Confirmas el pago?';

            if (!confirm(texto)) {
                e.preventDefault();
            }
        });

        actualizar();
    </script>
@endsection