<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
    <div style="max-width: 500px; margin: 0 auto; background: white; border-radius: 8px; padding: 24px;">
        <h2 style="color: #0d6efd;">ParcheCity</h2>

        <p>Hola {{ $reserva->nombre_cliente }},</p>
        <p>Tu pago fue confirmado y tu reserva quedó lista. Muéstrale este código al negocio al llegar:</p>

        <div style="background: #f4f4f4; border-radius: 6px; padding: 16px; text-align: center; margin: 16px 0;">
            <span style="font-size: 24px; font-weight: bold; letter-spacing: 2px;">{{ $reserva->codigo }}</span>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 0;"><strong>Negocio:</strong></td>
                <td>{{ $reserva->negocio->nombre }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; vertical-align: top;"><strong>Detalle:</strong></td>
                <td>@include('partials.detalle-reserva')</td>
            </tr>
            @if ($reserva->personas)
                <tr>
                    <td style="padding: 6px 0;"><strong>Personas:</strong></td>
                    <td>{{ $reserva->personas }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding: 6px 0;"><strong>Valor pagado:</strong></td>
                <td>
                    @cop($reserva->valor_reserva)
                    @if ($reserva->cuotas && $reserva->cuotas > 1)
                        <br><small>en {{ $reserva->cuotas }} cuotas de @cop($reserva->valor_reserva / $reserva->cuotas)</small>
                    @endif
                </td>
            </tr>
            @if ($reserva->tarjeta_ultimos4)
                <tr>
                    <td style="padding: 6px 0;"><strong>Tarjeta:</strong></td>
                    <td>terminada en {{ $reserva->tarjeta_ultimos4 }}</td>
                </tr>
            @endif
        </table>

        <p style="margin-top: 20px; font-size: 13px; color: #666;">
            Puedes cancelar y pedir reembolso hasta las <strong>@hora($reserva->reembolsoHasta())</strong>
            ({{ \App\Models\Reserva::MINUTOS_REEMBOLSO }} minutos después del pago). Pasado ese tiempo, no hay devolución.
        </p>

        <p style="font-size: 13px; color: #666;">
            Si necesitas cancelar, hazlo desde "Mis reservas" en la página.
        </p>
    </div>
</body>
</html>