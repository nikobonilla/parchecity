@extends('layouts.main')

@section('contenido')
    <h1 class="mb-4">Términos y condiciones</h1>

    <p>Al crear una cuenta en ParcheCity, aceptas lo siguiente:</p>

    <ul>
        <li>ParcheCity es una plataforma que conecta clientes con negocios independientes (restaurantes, hoteles y moteles). No somos dueños ni operadores de ninguno de los negocios publicados.</li>
        <li>ParcheCity no procesa ni custodia dinero. Los pagos se realizan directamente entre el cliente y el negocio, usando el medio de pago que cada negocio registra.</li>
        <li>ParcheCity no se hace responsable por la calidad del servicio, el estado del establecimiento, disputas sobre el pago, ni por información falsa o incompleta publicada por un negocio o un usuario.</li>
        <li>La confirmación de pago entre cliente y negocio depende de la verificación manual o automática descrita en la plataforma, y ParcheCity no garantiza la exactitud de esa confirmación más allá de lo que el sistema registra.</li>
        <li>El usuario es responsable de la veracidad de la información que registra (datos personales, de contacto y del negocio, en caso de ser dueño).</li>
        <li>ParcheCity puede suspender o eliminar cuentas que incumplan estos términos, publiquen información falsa, o hagan uso indebido de la plataforma.</li>
        <li>Este proyecto fue desarrollado con fines académicos para la Corporación Universitaria Minuto de Dios (UNIMINUTO).</li>
    </ul>

    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Volver</a>
@endsection