<?php

namespace Raffles\Modules\Poga\Database\Seeds;

use Raffles\Modules\Poga\Models\EstadoInmueble;

use Illuminate\Database\Seeder;

class EstadosInmuebleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $estados = [
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Pintura', 'nombre' => 'Sala'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Pintura', 'nombre' => 'Dormitorio 1'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Pintura', 'nombre' => 'Dormitorio 2'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Pintura', 'nombre' => 'Dormitorio 3'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Pintura', 'nombre' => 'Dormitorio 4'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Pintura', 'nombre' => 'Baño'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Pintura', 'nombre' => 'Cocina'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Pintura', 'nombre' => 'Área servicio'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Pintura', 'nombre' => 'Instalaciones de A/C'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Pintura', 'nombre' => 'Piso'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Pintura', 'nombre' => 'Otros'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Puertas', 'nombre' => 'Marco de puerta'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Puertas', 'nombre' => 'Placa'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Puertas', 'nombre' => 'Lisa'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Puertas', 'nombre' => 'Tablero'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Puertas', 'nombre' => 'Chapa con vídrio'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Puertas', 'nombre' => 'Chapa sin vídrio'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Puertas', 'nombre' => 'Cerraduras'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Puertas', 'nombre' => 'Cerraduras p/baño'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Puertas', 'nombre' => 'Pasadores'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Puertas', 'nombre' => 'Juego de llaves'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Puertas', 'nombre' => 'Pintar puertas'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Puertas', 'nombre' => 'Otros'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Persianas', 'nombre' => 'Pasadores'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Persianas', 'nombre' => 'Fallevas'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Persianas', 'nombre' => 'Varillas'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Persianas', 'nombre' => 'Cortinas'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Persianas', 'nombre' => 'Vídrios'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Persianas', 'nombre' => 'Barnizar/Pintar'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Persianas', 'nombre' => 'Tirador'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Persianas', 'nombre' => 'Otras'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Balancines', 'nombre' => 'Vídrios'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Balancines', 'nombre' => 'Guía'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Balancines', 'nombre' => 'Otros'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Ventanas', 'nombre' => 'Vídrio templado'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Ventanas', 'nombre' => 'Tirador'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Ventanas', 'nombre' => 'Pasador con traba'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Ventanas', 'nombre' => 'Canal límpio'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Ventanas', 'nombre' => 'Otros'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Baño', 'nombre' => 'Juego de baño'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Baño', 'nombre' => 'Accesorios de baño'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Baño', 'nombre' => 'Canilla lavatorio'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Baño', 'nombre' => 'Canilla lavapie'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Baño', 'nombre' => 'Llave de paso de ducha'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Baño', 'nombre' => 'Llave de paso general'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Baño', 'nombre' => 'Cisterna'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Baño', 'nombre' => 'Azulejada pared'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Baño', 'nombre' => 'Piso'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Baño', 'nombre' => 'Registro con tapa y limpio'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Baño', 'nombre' => 'Tapa inodoro'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Baño', 'nombre' => 'Brazo p/ducha'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Baño', 'nombre' => 'Otros'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Cocina', 'nombre' => 'Mesada'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Cocina', 'nombre' => 'Bacha'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Cocina', 'nombre' => 'Canillas pico móvil'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Cocina', 'nombre' => 'Desengrasador con tapa y limpio'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Cocina', 'nombre' => 'Conexión cañería'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Cocina', 'nombre' => 'Piso'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Cocina', 'nombre' => 'Otros'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Rejas', 'nombre' => 'Portón entrada'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Rejas', 'nombre' => 'Acceso patio'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Rejas', 'nombre' => 'Otros'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Instalaciones eléctricas', 'nombre' => 'Llave declarada'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Instalaciones eléctricas', 'nombre' => 'Llaves tablero principal'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Instalaciones eléctricas', 'nombre' => 'Artefactos de iluminación'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Instalaciones eléctricas', 'nombre' => 'Ducha'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Instalaciones eléctricas', 'nombre' => 'Pico toma'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Instalaciones eléctricas', 'nombre' => 'Pico llave'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Instalaciones eléctricas', 'nombre' => 'Placas p/pico'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Instalaciones eléctricas', 'nombre' => 'Pulsador timbre'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Instalaciones eléctricas', 'nombre' => 'Timbre'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Instalaciones eléctricas', 'nombre' => 'Focos'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Instalaciones eléctricas', 'nombre' => 'Tubos fluorescentes'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Instalaciones eléctricas', 'nombre' => 'Otros'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Jardines', 'nombre' => 'Jardín trasero'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Jardines', 'nombre' => 'Jardín frente'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Jardines', 'nombre' => 'Otros'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Lavadero', 'nombre' => 'Canilla lavarropas'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Lavadero', 'nombre' => 'Pileta'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Lavadero', 'nombre' => 'Cañería'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Lavadero', 'nombre' => 'Otros'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Canaletas', 'nombre' => 'Frente'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Canaletas', 'nombre' => 'Fondo'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Canaletas', 'nombre' => 'Laterales'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Canaletas', 'nombre' => 'Limpieza'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Canaletas', 'nombre' => 'Caño bajada'],
            ['enum_estado' => 'ACTIVO', 'enum_categoria' => 'Canaletas', 'nombre' => 'Otros'],
        ];
    
        foreach ($estados as $estado) {
            EstadoInmueble::create($estado);
        }
    }
}
