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
        ['nombre' => 'Continental'],
        ['nombre' => 'Itau'],
            ['nombre' => 'Visión'],
        ];
    
        foreach ($bancos as $banco) {
            Banco::create($banco);
        }
    }
}
