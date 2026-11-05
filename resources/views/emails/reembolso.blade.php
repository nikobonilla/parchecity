<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
    <div style="max-width: 500px; margin: 0 auto; background: white; border-radius: 8px; padding: 24px;">
        <h2 style="color: #198754;">ParcheCity</h2>

        <p>Hola {{ $reserva->nombre_cliente }},</p>
        <p>Procesamos el reembolso de tu reserva.</p>

        <div style="background: #f4f4f4; border-radius: 6px; padding: 16px; text-align: center; margin: 16px 0;">
            <div style="font-size: 13px; color: #666;">Valor reembolsado</div>
            <div style="font-size: 24px; font-weight: bold;">@cop($reserva->valor_reserva)</div>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 0;"><strong>Negocio:</strong></td>
                <td>{{ $reserva->negocio->nombre }}</td>
            </tr>
            @if ($reserva->codigo)
                <tr>
                    <td style="padding: 6px 0;"><strong>Reserva:</strong></td>
                    <td>{{ $reserva->codigo }}</td>
                </tr>
            @endif
            @if ($reserva->tarjeta_ultimos4)
                <tr>
                    <td style="padding: 6px 0;"><strong>Tarjeta:</strong></td>
                    <td>terminada en {{ $reserva->tarjeta_ultimos4 }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding: 6px 0;"><strong>Procesado:</strong></td>
                <td>{{ now()->format('d/m/Y') }} a las @hora(now())</td>
            </tr>
        </table>

        <p style="font-size: 13px; color: #666; margin-top: 20px;">
            El tiempo en que se ve reflejado depende de tu banco.
        </p>
    </div>
</body>
</html>