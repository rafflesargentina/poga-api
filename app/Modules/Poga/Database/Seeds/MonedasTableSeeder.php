<?php

namespace Raffles\Modules\Poga\Database\Seeds;

use Raffles\Modules\Poga\Models\Moneda;

use Illuminate\Database\Seeder;

class MonedasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $monedas = [
            [
                'moneda' => 'Guaraní', 
                'abbr' => 'Gs', 
                'enum_estado' => 'ACTIVO'
            ], // 1
            [
                'moneda' => 'Dólar Norteamericano',
                'abbr' => 'USD',
                'enum_estado' => 'ACTIVO'
            ], // 2
        ];

        foreach ($monedas as $moneda) { Moneda::create($moneda);
        }
    }
}
