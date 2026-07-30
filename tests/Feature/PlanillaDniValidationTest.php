<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\EmpresasClientes;
use App\Models\Planilla;
use App\Models\Area;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PlanillaDniValidationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dni_must_be_unique_per_company_on_create(): void
    {
        // 1. Create a User first
        $user = User::factory()->create([
            'estado' => 1,
            'tipo_usuario' => 1,
        ]);

        // 2. Create an Area and associate with the user
        $area = Area::create([
            'nombre' => 'ADMINISTRACION',
            'id_user' => $user->id,
            'estado' => 1
        ]);

        // 3. Update the user's area
        $user->update(['id_area' => $area->id_area]);

        // 4. Create Companies with non-nullable fields
        $empresa1 = EmpresasClientes::create([
            'razon_social' => 'EMPRESA 1 SAS',
            'ruc' => '20123456789',
            'planilla' => 1,
            'estado_actual' => 1,
            'estado' => 'A1',
            'sol_usuario' => 'user1',
            'sol_clave' => 'pass1',
            'detraccion' => 'NO',
            'retencion' => 'NO',
        ]);

        $empresa2 = EmpresasClientes::create([
            'razon_social' => 'EMPRESA 2 SAS',
            'ruc' => '20987654321',
            'planilla' => 1,
            'estado_actual' => 1,
            'estado' => 'A1',
            'sol_usuario' => 'user2',
            'sol_clave' => 'pass2',
            'detraccion' => 'NO',
            'retencion' => 'NO',
        ]);

        // 5. Register a worker in Empresa 1
        $worker1Data = [
            'id_empresas_clientes' => $empresa1->id_empresas_clientes,
            'dni' => '12345678',
            'nombre_trabajador' => 'JUAN PEREZ',
            'fecha_ingreso' => '2026-01-01',
            'cargo' => 'CONTADOR',
            'sueldo' => 2000.00,
            'modalidad' => 'CONTRATO DE TRABAJO',
            'fecha_fin' => '2026-12-31',
        ];

        $response = $this->actingAs($user)->postJson('/planillas', $worker1Data);
        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Trabajador agregado correctamente.']);

        // 6. Try to register another worker with the SAME DNI in Empresa 1 (should FAIL validation)
        $worker2Data = [
            'id_empresas_clientes' => $empresa1->id_empresas_clientes,
            'dni' => '12345678', // Same DNI
            'nombre_trabajador' => 'PEDRO GOMEZ',
            'fecha_ingreso' => '2026-01-01',
            'cargo' => 'ASISTENTE',
            'sueldo' => 1500.00,
            'modalidad' => 'CONTRATO DE TRABAJO',
            'fecha_fin' => '2026-12-31',
        ];

        $response = $this->actingAs($user)->postJson('/planillas', $worker2Data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['dni']);
        $response->assertJsonFragment([
            'errors' => [
                'dni' => ['El DNI ingresado ya se encuentra registrado para esta empresa.']
            ]
        ]);

        // 7. Try to register a worker with the SAME DNI in Empresa 2 (should PASS validation)
        $worker3Data = [
            'id_empresas_clientes' => $empresa2->id_empresas_clientes,
            'dni' => '12345678', // Same DNI, but different company
            'nombre_trabajador' => 'JUAN PEREZ',
            'fecha_ingreso' => '2026-01-01',
            'cargo' => 'CONTADOR',
            'sueldo' => 2000.00,
            'modalidad' => 'CONTRATO DE TRABAJO',
            'fecha_fin' => '2026-12-31',
        ];

        $response = $this->actingAs($user)->postJson('/planillas', $worker3Data);
        $response->assertStatus(200);
    }

    public function test_dni_must_be_unique_per_company_on_update(): void
    {
        // 1. Create a User first
        $user = User::factory()->create([
            'estado' => 1,
            'tipo_usuario' => 1,
        ]);

        // 2. Create an Area and associate with the user
        $area = Area::create([
            'nombre' => 'ADMINISTRACION',
            'id_user' => $user->id,
            'estado' => 1
        ]);

        // 3. Update the user's area
        $user->update(['id_area' => $area->id_area]);

        $empresa = EmpresasClientes::create([
            'razon_social' => 'EMPRESA TEST',
            'ruc' => '20123456789',
            'planilla' => 1,
            'estado_actual' => 1,
            'estado' => 'A1',
            'sol_usuario' => 'user3',
            'sol_clave' => 'pass3',
            'detraccion' => 'NO',
            'retencion' => 'NO',
        ]);

        $planilla1 = Planilla::create([
            'id_empresas_clientes' => $empresa->id_empresas_clientes,
            'dni' => '11111111',
            'nombre_trabajador' => 'WORKER ONE',
            'fecha_ingreso' => '2026-01-01',
            'cargo' => 'CARGO 1',
            'sueldo' => 1000.00,
        ]);

        $planilla2 = Planilla::create([
            'id_empresas_clientes' => $empresa->id_empresas_clientes,
            'dni' => '22222222',
            'nombre_trabajador' => 'WORKER TWO',
            'fecha_ingreso' => '2026-01-01',
            'cargo' => 'CARGO 2',
            'sueldo' => 1200.00,
        ]);

        // Try updating Planilla 2 with DNI of Planilla 1 (should FAIL validation)
        $response = $this->actingAs($user)->postJson("/planillas/{$planilla2->id_planilla}/actualizar", [
            'dni' => '11111111', // DNI of Planilla 1
            'nombre_trabajador' => 'WORKER TWO',
            'fecha_ingreso' => '2026-01-01',
            'cargo' => 'CARGO 2',
            'sueldo' => 1200.00,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['dni']);

        // Try updating Planilla 2 keeping its own DNI (should PASS validation)
        $response = $this->actingAs($user)->postJson("/planillas/{$planilla2->id_planilla}/actualizar", [
            'dni' => '22222222', // Its own DNI
            'nombre_trabajador' => 'WORKER TWO MODIFIED',
            'fecha_ingreso' => '2026-01-01',
            'cargo' => 'CARGO 2',
            'sueldo' => 1300.00,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('WORKER TWO MODIFIED', $planilla2->fresh()->nombre_trabajador);
    }
}
