<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Este seeder crea los servicios del lavadero y verifica que las categorías existan.
     * Si alguna categoría no existe, la crea automáticamente.
     * 
     * @return void
     */
    public function run(): void
    {
        // Iniciar transacción para mantener integridad de datos
        DB::beginTransaction();

        try {
            // Definir las categorías necesarias
            $requiredCategories = [
                'Lavados',
                'Tapicería',
                'Pulidos',
                'Desinfección',
                'Accesorios',
                'Mecánica / Limpieza',
            ];

            // Verificar y crear categorías si no existen
            $categoriesMap = [];
            foreach ($requiredCategories as $categoryName) {
                $category = Category::where('cat_name', $categoryName)->first();
                
                if (!$category) {
                    $this->command->warn("⚠️  Categoría '{$categoryName}' no encontrada. Creándola...");
                    
                    $category = Category::create([
                        'id' => (string) Str::uuid(),
                        'cat_name' => $categoryName,
                        'status' => Category::STATUS_ACTIVE,
                        'creation_date' => now(),
                    ]);
                    
                    $this->command->info("✓ Categoría '{$categoryName}' creada exitosamente.");
                }
                
                $categoriesMap[$categoryName] = $category->id;
            }

            // Definir los servicios con su información completa
            $services = [
                // Lavados
                [
                    'name' => 'Lavado Básico Exterior',
                    'category' => 'Lavados',
                    'details' => 'Lavado exterior completo del vehículo',
                    'value' => 15.00,
                    'duration' => 30, // minutos
                ],
                [
                    'name' => 'Lavado Básico Interior',
                    'category' => 'Lavados',
                    'details' => 'Limpieza interior del vehículo',
                    'value' => 20.00,
                    'duration' => 45,
                ],
                [
                    'name' => 'Lavado Básico Completo',
                    'category' => 'Lavados',
                    'details' => 'Lavado exterior e interior completo',
                    'value' => 30.00,
                    'duration' => 60,
                ],
                [
                    'name' => 'Brillante Integral',
                    'category' => 'Lavados',
                    'details' => 'Lavado premium con acabado brillante',
                    'value' => 50.00,
                    'duration' => 90,
                ],
                [
                    'name' => 'Detailing Premium',
                    'category' => 'Lavados',
                    'details' => 'Servicio completo de detailing profesional',
                    'value' => 150.00,
                    'duration' => 240,
                ],
                
                // Tapicería
                [
                    'name' => 'Tapicería en tela (Completa)',
                    'category' => 'Tapicería',
                    'details' => 'Limpieza profunda de toda la tapicería de tela',
                    'value' => 80.00,
                    'duration' => 120,
                ],
                [
                    'name' => 'Tapicería en tela (Por Asiento)',
                    'category' => 'Tapicería',
                    'details' => 'Limpieza de tapicería de tela por asiento',
                    'value' => 20.00,
                    'duration' => 30,
                ],
                [
                    'name' => 'Tapicería en cuero (Completa)',
                    'category' => 'Tapicería',
                    'details' => 'Tratamiento y limpieza de tapicería de cuero completa',
                    'value' => 100.00,
                    'duration' => 150,
                ],
                [
                    'name' => 'Tapicería en cuero (Por Asiento)',
                    'category' => 'Tapicería',
                    'details' => 'Tratamiento de tapicería de cuero por asiento',
                    'value' => 25.00,
                    'duration' => 40,
                ],
                
                // Pulidos
                [
                    'name' => 'Cuidado de faros (Superficial)',
                    'category' => 'Pulidos',
                    'details' => 'Pulido superficial de faros opacos',
                    'value' => 30.00,
                    'duration' => 30,
                ],
                [
                    'name' => 'Pulido de faro (Izquierdo)',
                    'category' => 'Pulidos',
                    'details' => 'Pulido completo del faro izquierdo',
                    'value' => 20.00,
                    'duration' => 20,
                ],
                [
                    'name' => 'Pulido de faro (Derecho)',
                    'category' => 'Pulidos',
                    'details' => 'Pulido completo del faro derecho',
                    'value' => 20.00,
                    'duration' => 20,
                ],
                [
                    'name' => 'Pulido de faro (Ambos)',
                    'category' => 'Pulidos',
                    'details' => 'Pulido completo de ambos faros',
                    'value' => 35.00,
                    'duration' => 35,
                ],
                [
                    'name' => 'Pulido carrocería (Completo)',
                    'category' => 'Pulidos',
                    'details' => 'Pulido profesional de toda la carrocería',
                    'value' => 200.00,
                    'duration' => 300,
                ],
                [
                    'name' => 'Pulido carrocería (Por pieza)',
                    'category' => 'Pulidos',
                    'details' => 'Pulido profesional por pieza de carrocería',
                    'value' => 40.00,
                    'duration' => 60,
                ],
                
                // Desinfección
                [
                    'name' => 'Desinfección (1 hora)',
                    'category' => 'Desinfección',
                    'details' => 'Desinfección completa del habitáculo con ozono',
                    'value' => 35.00,
                    'duration' => 60,
                ],
                
                // Accesorios
                [
                    'name' => 'Sustitución Alfombrillas',
                    'category' => 'Accesorios',
                    'details' => 'Cambio e instalación de alfombrillas nuevas',
                    'value' => 25.00,
                    'duration' => 15,
                ],
                [
                    'name' => 'Sustitución Limpia parabrisas',
                    'category' => 'Accesorios',
                    'details' => 'Cambio de escobillas limpiaparabrisas',
                    'value' => 15.00,
                    'duration' => 10,
                ],
                [
                    'name' => 'Cubre Volantes con instalación',
                    'category' => 'Accesorios',
                    'details' => 'Instalación de funda de volante personalizada',
                    'value' => 20.00,
                    'duration' => 15,
                ],
                
                // Mecánica / Limpieza
                [
                    'name' => 'Lavado motor en seco',
                    'category' => 'Mecánica / Limpieza',
                    'details' => 'Limpieza profesional del motor en seco',
                    'value' => 40.00,
                    'duration' => 45,
                ],
                [
                    'name' => 'Limpieza de bajos',
                    'category' => 'Mecánica / Limpieza',
                    'details' => 'Limpieza a presión de bajos del vehículo',
                    'value' => 25.00,
                    'duration' => 30,
                ],
                [
                    'name' => 'Limpieza radiador',
                    'category' => 'Mecánica / Limpieza',
                    'details' => 'Limpieza del radiador y sistema de refrigeración',
                    'value' => 30.00,
                    'duration' => 40,
                ],
                [
                    'name' => 'Limpieza de patinete',
                    'category' => 'Mecánica / Limpieza',
                    'details' => 'Limpieza completa de patinete eléctrico',
                    'value' => 15.00,
                    'duration' => 20,
                ],
            ];

            // Verificar si ya existen servicios
            $existingServicesCount = Service::count();
            
            if ($existingServicesCount > 0) {
                $this->command->warn("⚠️  Ya existen {$existingServicesCount} servicios en la base de datos.");
                
                if (!$this->command->confirm('¿Deseas continuar y agregar los servicios faltantes?', true)) {
                    DB::rollBack();
                    $this->command->info('Proceso cancelado por el usuario.');
                    return;
                }
            }

            // Insertar servicios
            $inserted = 0;
            $skipped = 0;

            foreach ($services as $serviceData) {
                // Verificar si el servicio ya existe
                $exists = Service::where('name', $serviceData['name'])->exists();
                
                if ($exists) {
                    $this->command->warn("⊘ Servicio '{$serviceData['name']}' ya existe. Omitiendo...");
                    $skipped++;
                    continue;
                }

                // Obtener el ID de la categoría
                $categoryId = $categoriesMap[$serviceData['category']] ?? null;
                
                if (!$categoryId) {
                    $this->command->error("✗ Error: Categoría '{$serviceData['category']}' no encontrada para el servicio '{$serviceData['name']}'");
                    continue;
                }

                // Crear el servicio
                Service::create([
                    'id' => (string) Str::uuid(),
                    'category_id' => $categoryId,
                    'name' => $serviceData['name'],
                    'details' => $serviceData['details'],
                    'value' => $serviceData['value'],
                    'duration' => $serviceData['duration'],
                    'status' => 1,
                    'creation_date' => now(),
                ]);

                $inserted++;
                $this->command->info("✓ Servicio '{$serviceData['name']}' creado exitosamente.");
            }

            // Confirmar transacción
            DB::commit();

            // Mostrar resumen
            $this->command->info('');
            $this->command->info('════════════════════════════════════════');
            $this->command->info('   RESUMEN DE SERVICIOS INSERTADOS');
            $this->command->info('════════════════════════════════════════');
            $this->command->info("✓ Servicios insertados: {$inserted}");
            $this->command->info("⊘ Servicios omitidos (ya existían): {$skipped}");
            $this->command->info("📊 Total de servicios en BD: " . Service::count());
            $this->command->info('════════════════════════════════════════');

        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            DB::rollBack();
            
            $this->command->error('');
            $this->command->error('════════════════════════════════════════');
            $this->command->error('   ERROR AL INSERTAR SERVICIOS');
            $this->command->error('════════════════════════════════════════');
            $this->command->error('Error: ' . $e->getMessage());
            $this->command->error('Archivo: ' . $e->getFile() . ':' . $e->getLine());
            $this->command->error('════════════════════════════════════════');
            
            throw $e;
        }
    }
}
