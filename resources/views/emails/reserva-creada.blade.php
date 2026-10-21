<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
    <div style="max-width: 500px; margin: 0 auto; background: white; border-radius: 8px; padding: 24px;">
        <h2 style="color: #0d6efd;">ParcheCity</h2>

        <p>Hola {{ $reserva->nombre_cliente }},</p>
        <p>Tu reserva fue creada correctamente. Muéstrale este código al negocio al llegar:</p>

        <div style="background: #f4f4f4; border-radius: 6px; padding: 16px; text-align: center; margin: 16px 0;">
            <span style="font-size: 24px; font-weight: bold; letter-spacing: 2px;">{{ $reserva->codigo }}</span>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 0;"><strong>Negocio:</strong></td>
                <td>{{ $reserva->negocio->nombre }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0;"><strong>Fecha:</strong></td>
                <td>{{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0;"><strong>Hora:</strong></td>
                <td>{{ substr($reserva->hora_entrada, 0, 5) }} a {{ substr($reserva->hora_salida, 0, 5) }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0;"><strong>Personas:</strong></td>
                <td>{{ $reserva->personas }}</td>
            </tr>
            @if ($reserva->valor_reserva)
                <tr>
                    <td style="padding: 6px 0;"><strong>Valor para apartar:</strong></td>
                    <td>${{ number_format($reserva->valor_reserva, 0, ',', '.') }}</td>
                </tr>
            @endif
        </table>

        <p style="margin-top: 20px; font-size: 13px; color: #666;">
            Recuerda: el valor de la reserva solo es reembolsable dentro de los 30 minutos posteriores a haberla hecho.
            Pasado ese tiempo, no hay devolución.
        </p>

        <p style="font-size: 13px; color: #666;">
            Si necesitas cancelar, hazlo desde "Mis reservas" en la página.
        </p>
    </div>
</body>
</html>