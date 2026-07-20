<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Planta;

class PlantaSeeder extends Seeder
{
    public function run(): void
    {
        Planta::create([
            'nombre' => 'Tomate',
            'humedad_min' => 60,
            'humedad_max' => 75,
            'temperatura_min' => 18,
            'temperatura_max' => 26,
            'nutrientes_recomendados' => 'Nitrógeno (N) para crecimiento foliar, Fósforo (P) para raíces y floración, Potasio (K) para fructificación. Aplicar fertilizante NPK 15-15-15 cada 15 días.',
            'descripcion' => 'Requiere riego constante y buena ventilación para evitar hongos.',
        ]);

        Planta::create([
            'nombre' => 'Lechuga',
            'humedad_min' => 65,
            'humedad_max' => 80,
            'temperatura_min' => 15,
            'temperatura_max' => 22,
            'nutrientes_recomendados' => 'Nitrógeno (N) en mayor proporción para desarrollo de hojas. Aplicar fertilizante rico en nitrógeno (ej. Urea) cada 10 días. Calcio para evitar quemado de bordes.',
            'descripcion' => 'Sensible al exceso de calor; prefiere climas templados.',
        ]);

        Planta::create([
            'nombre' => 'Fresa',
            'humedad_min' => 60,
            'humedad_max' => 70,
            'temperatura_min' => 15,
            'temperatura_max' => 24,
            'nutrientes_recomendados' => 'Potasio (K) para calidad del fruto, Calcio para firmeza, Boro para floración. Fertilizante balanceado con micronutrientes cada 2 semanas.',
            'descripcion' => 'Requiere buen drenaje y evitar encharcamiento de raíces.',
        ]);
    }
}