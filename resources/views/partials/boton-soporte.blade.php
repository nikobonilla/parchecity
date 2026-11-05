@php $soporte = \App\Models\Configuracion::obtener()->whatsapp_soporte; @endphp

@if ($soporte)
    <a href="https://wa.me/{{ preg_replace('/\D/', '', $soporte) }}?text={{ urlencode('Hola, soy dueño de un negocio en ParcheCity y necesito ayuda.') }}"
       target="_blank" class="btn btn-success">
        Atención al cliente por WhatsApp
    </a>
@endif