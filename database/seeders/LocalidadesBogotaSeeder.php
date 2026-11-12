<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalidadesBogotaSeeder extends Seeder
{
    public function run(): void
    {
        // 11001 = Bogotá, D.C. (código DANE)
        if (! DB::table('municipios')->where('id', '11001')->exists()) {
            $this->command?->warn('No se encontró Bogotá (11001) en la tabla municipios.');

            return;
        }

        $localidades = [
            'Usaquén', 'Chapinero', 'Santa Fe', 'San Cristóbal', 'Usme', 'Tunjuelito', 'Bosa',
            'Kennedy', 'Fontibón', 'Engativá', 'Suba', 'Barrios Unidos', 'Teusaquillo',
            'Los Mártires', 'Antonio Nariño', 'Puente Aranda', 'La Candelaria',
            'Rafael Uribe Uribe', 'Ciudad Bolívar', 'Sumapaz',
        ];

        foreach ($localidades as $nombre) {
            DB::table('localidades')->updateOrInsert(
                ['municipio_id' => '11001', 'nombre' => $nombre],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}