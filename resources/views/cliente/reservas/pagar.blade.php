@extends('layouts.main')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="mb-1">Pagar reserva</h1>
            <p class="text-muted">{{ $reserva->negocio->nombre }} · Código pendiente · Valor: ${{ number_format($reserva->valor_reserva, 0, ',', '.') }}</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('cliente.reservas.pagar.procesar', $reserva) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Número de tarjeta</label>
                            <input type="text" name="numero_tarjeta" class="form-control" placeholder="4242 4242 4242 4242" value="{{ old('numero_tarjeta') }}" maxlength="19" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre del titular</label>
                            <input type="text" name="nombre_titular" class="form-control" value="{{ old('nombre_titular', $reserva->nombre_cliente) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-4 mb-3">
                                <label class="form-label">Mes</label>
                                <input type="number" name="mes_vencimiento" class="form-control" placeholder="MM" min="1" max="12" value="{{ old('mes_vencimiento') }}" required>
                            </div>
                            <div class="col-4 mb-3">
                                <label class="form-label">Año</label>
                                <input type="number" name="ano_vencimiento" class="form-control" placeholder="AA" min="{{ date('y') }}" value="{{ old('ano_vencimiento') }}" required>
                            </div>
                            <div class="col-4 mb-3">
                                <label class="form-label">CVV</label>
                                <input type="text" name="cvv" class="form-control" placeholder="123" maxlength="4" value="{{ old('cvv') }}" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Pagar ${{ number_format($reserva->valor_reserva, 0, ',', '.') }}</button>
                    </form>
                </div>
            </div>

            <p class="text-muted small mt-2">
                Ambiente de pruebas. Usa un número de tarjeta válido según el algoritmo de Luhn, por ejemplo 4242 4242 4242 4242.
            </p>
        </div>
    </div>
@endsection