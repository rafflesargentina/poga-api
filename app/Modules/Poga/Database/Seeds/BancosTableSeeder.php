<?php

namespace Raffles\Modules\Poga\Database\Seeds;

use Raffles\Modules\Poga\Models\Banco;

use Illuminate\Database\Seeder;

class BancosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bancos = [
            ['nombre' => 'BBVA'],
            ['nombre' => 'Banco Atlas'],
            ['nombre' => 'Banco Basa S.A.'],
            ['nombre' => 'Banco Central del Paraguay'],
            ['nombre' => 'Banco Continental S.A.'],
            ['nombre' => 'Banco de la Nación Argentina'],
            ['nombre' => 'Banco Do Brasil S.A.'],
            ['nombre' => 'Banco Familiar S.A.E.C.A.'],
            ['nombre' => 'Banco GNB Paraguay S.A.'],
            ['nombre' => 'Banco Itau Paraguay S.A.'],
            ['nombre' => 'Banco Nacional de Fomento'],
            ['nombre' => 'Banco Regional S.A.'],
            ['nombre' => 'Banco Río S.A.E.C.A.'],
            ['nombre' => 'Bancop S.A.'],
            ['nombre' => 'Citibank N.A.'],
            ['nombre' => 'Continental'],
            ['nombre' => 'Crisol y Encarnación Financiera S.A.'],
            ['nombre' => 'El Comercio Financiera S.A.E.C.A.'],
            ['nombre' => 'Financiera Exportadora Paraguay S.A.'],
            ['nombre' => 'Financiera Paguayo-Japonesa S.A.E.C.A.'],
            ['nombre' => 'Finlatina S.A. de Finanzas'],
            ['nombre' => 'FIC S.A. de Finanzas'],
            ['nombre' => 'Interfisa Banco'],
            ['nombre' => 'Itau'],
            ['nombre' => 'Solar S.A. de Ahorro y Préstamo para la...'],
            ['nombre' => 'Sudameris Bank S.A.E.C.A.'],
            ['nombre' => 'Tu Financiera S.A.'],
            ['nombre' => 'Visión Banco S.A.E.C.A.'],
        ];
    
        foreach ($bancos as $banco) {
            Banco::create($banco);
        }
    }
}
