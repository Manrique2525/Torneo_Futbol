<?php

namespace Tests\Feature;

use App\Contexts\TenantContext;
use App\Models\Jornada;
use App\Models\Partido;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\Torneo;
use App\Models\TorneoEquipo;
use App\Models\User;
use App\Services\RolePermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class CalendarioSustitucionTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Torneo $torneo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'uuid' => Str::uuid(),
            'name' => 'Tenant Test',
            'slug' => 'tenant-test',
        ]);

        app(TenantContext::class)->set($this->tenant->id);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        setPermissionsTeamId($this->tenant->id);
        app(RolePermissionService::class)->setupRoles();
        $this->user->assignRole('admin');

        $this->torneo = Torneo::create([
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Torneo Test',
            'tipo' => 'liga',
            'categoria' => 'libre',
            'rama' => 'varonil',
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-10-01',
            'estado' => 'activo',
            'created_by' => $this->user->id,
            'ida_y_vuelta' => true,
        ]);
    }

    private function crearEquipo(string $nombre): TorneoEquipo
    {
        $team = Team::create([
            'tenant_id' => $this->tenant->id,
            'name' => $nombre,
        ]);

        return TorneoEquipo::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'team_id' => $team->id,
            'estado' => 'aprobado',
            'aprobado_at' => now(),
            'aprobado_por' => $this->user->id,
        ]);
    }

    public function test_sustitucion_doble_jornada_con_intercambio_automatico(): void
    {
        $eq10 = $this->crearEquipo('Equipo 10');
        $eq7 = $this->crearEquipo('Equipo 7');
        $eq8 = $this->crearEquipo('Equipo 8');

        $jornada1 = Jornada::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'nombre' => 'Jornada 1',
            'numero' => 1,
            'fecha_inicio' => '2026-06-01',
            'estado' => 'borrador',
        ]);

        $jornada13 = Jornada::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'nombre' => 'Jornada 13',
            'numero' => 13,
            'fecha_inicio' => '2026-08-30',
            'estado' => 'borrador',
        ]);

        $partidoA = Partido::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'jornada_id' => $jornada1->id,
            'equipo_local_id' => $eq10->id,
            'equipo_visitante_id' => $eq8->id,
            'fecha' => '2026-06-01',
            'hora' => '19:00',
            'estado' => 'programado',
            'fase' => 'regular',
        ]);

        $partidoB = Partido::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'jornada_id' => $jornada13->id,
            'equipo_local_id' => $eq7->id,
            'equipo_visitante_id' => $eq8->id,
            'fecha' => '2026-08-30',
            'hora' => '12:00',
            'estado' => 'programado',
            'fase' => 'regular',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('calendario.sustituir', $partidoA), [
                'equipo_original_id' => $eq10->id,
                'equipo_sustituto_id' => $eq7->id,
                'motivo' => 'no_asistencia',
                'tipo_resolucion' => 'doble_jornada',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('partidos', [
            'id' => $partidoA->id,
            'equipo_local_id' => $eq7->id,
            'equipo_visitante_id' => $eq8->id,
        ]);

        $this->assertDatabaseHas('partidos', [
            'id' => $partidoB->id,
            'equipo_local_id' => $eq10->id,
            'equipo_visitante_id' => $eq8->id,
        ]);

        $this->assertDatabaseCount('partido_sustituciones', 2);
    }

    public function test_sustitucion_doble_jornada_sin_duplicado_no_genera_intercambio(): void
    {
        $eq10 = $this->crearEquipo('Equipo 10');
        $eq7 = $this->crearEquipo('Equipo 7');
        $eq8 = $this->crearEquipo('Equipo 8');
        $eq9 = $this->crearEquipo('Equipo 9');

        $jornada1 = Jornada::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'nombre' => 'Jornada 1',
            'numero' => 1,
            'fecha_inicio' => '2026-06-01',
            'estado' => 'borrador',
        ]);

        $partidoA = Partido::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'jornada_id' => $jornada1->id,
            'equipo_local_id' => $eq10->id,
            'equipo_visitante_id' => $eq8->id,
            'fecha' => '2026-06-01',
            'hora' => '19:00',
            'estado' => 'programado',
            'fase' => 'regular',
        ]);

        Partido::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'jornada_id' => $jornada1->id,
            'equipo_local_id' => $eq7->id,
            'equipo_visitante_id' => $eq9->id,
            'fecha' => '2026-06-01',
            'hora' => '20:00',
            'estado' => 'programado',
            'fase' => 'regular',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('calendario.sustituir', $partidoA), [
                'equipo_original_id' => $eq10->id,
                'equipo_sustituto_id' => $eq7->id,
                'motivo' => 'no_asistencia',
                'tipo_resolucion' => 'doble_jornada',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('partidos', [
            'id' => $partidoA->id,
            'equipo_local_id' => $eq7->id,
            'equipo_visitante_id' => $eq8->id,
        ]);

        $this->assertDatabaseCount('partido_sustituciones', 1);
    }

    public function test_sustitucion_no_intercambia_cuando_el_par_coincide_al_reves(): void
    {
        $eq10 = $this->crearEquipo('Equipo 10');
        $eq7 = $this->crearEquipo('Equipo 7');
        $eq8 = $this->crearEquipo('Equipo 8');

        $jornada1 = Jornada::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'nombre' => 'Jornada 1',
            'numero' => 1,
            'fecha_inicio' => '2026-06-01',
            'estado' => 'borrador',
        ]);

        $jornada4 = Jornada::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'nombre' => 'Jornada 4',
            'numero' => 4,
            'fecha_inicio' => '2026-07-05',
            'estado' => 'borrador',
        ]);

        $partidoA = Partido::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'jornada_id' => $jornada1->id,
            'equipo_local_id' => $eq10->id,
            'equipo_visitante_id' => $eq8->id,
            'fecha' => '2026-06-01',
            'hora' => '19:00',
            'estado' => 'programado',
            'fase' => 'regular',
        ]);

        $partidoB = Partido::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'jornada_id' => $jornada4->id,
            'equipo_local_id' => $eq8->id,
            'equipo_visitante_id' => $eq7->id,
            'fecha' => '2026-07-05',
            'hora' => '12:00',
            'estado' => 'programado',
            'fase' => 'regular',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('calendario.sustituir', $partidoA), [
                'equipo_original_id' => $eq10->id,
                'equipo_sustituto_id' => $eq7->id,
                'motivo' => 'no_asistencia',
                'tipo_resolucion' => 'doble_jornada',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('partidos', [
            'id' => $partidoA->id,
            'equipo_local_id' => $eq7->id,
            'equipo_visitante_id' => $eq8->id,
        ]);

        $this->assertDatabaseHas('partidos', [
            'id' => $partidoB->id,
            'equipo_local_id' => $eq8->id,
            'equipo_visitante_id' => $eq7->id,
        ]);

        $this->assertDatabaseCount('partido_sustituciones', 1);
    }

    public function test_sustitucion_reprogramado_con_intercambio_automatico(): void
    {
        $eq10 = $this->crearEquipo('Equipo 10');
        $eq7 = $this->crearEquipo('Equipo 7');
        $eq8 = $this->crearEquipo('Equipo 8');

        $jornada1 = Jornada::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'nombre' => 'Jornada 1',
            'numero' => 1,
            'fecha_inicio' => '2026-06-01',
            'estado' => 'borrador',
        ]);

        $jornada13 = Jornada::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'nombre' => 'Jornada 13',
            'numero' => 13,
            'fecha_inicio' => '2026-08-30',
            'estado' => 'borrador',
        ]);

        $partidoA = Partido::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'jornada_id' => $jornada1->id,
            'equipo_local_id' => $eq10->id,
            'equipo_visitante_id' => $eq8->id,
            'fecha' => '2026-06-01',
            'hora' => '19:00',
            'estado' => 'programado',
            'fase' => 'regular',
        ]);

        $partidoB = Partido::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'jornada_id' => $jornada13->id,
            'equipo_local_id' => $eq7->id,
            'equipo_visitante_id' => $eq8->id,
            'fecha' => '2026-08-30',
            'hora' => '12:00',
            'estado' => 'programado',
            'fase' => 'regular',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('calendario.sustituir', $partidoA), [
                'equipo_original_id' => $eq10->id,
                'equipo_sustituto_id' => $eq7->id,
                'motivo' => 'no_asistencia',
                'tipo_resolucion' => 'reprogramado',
                'nueva_fecha' => '2030-06-02',
                'nueva_hora' => '20:00',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('partidos', [
            'id' => $partidoA->id,
            'estado' => 'cancelado',
        ]);

        $this->assertDatabaseHas('partidos', [
            'id' => $partidoB->id,
            'equipo_local_id' => $eq10->id,
            'equipo_visitante_id' => $eq8->id,
        ]);

        $nuevoPartido = Partido::query()
            ->where('torneo_id', $this->torneo->id)
            ->where('equipo_local_id', $eq7->id)
            ->where('equipo_visitante_id', $eq8->id)
            ->where('estado', 'programado')
            ->where('fecha', '2030-06-02')
            ->first();

        $this->assertNotNull($nuevoPartido);
        $this->assertDatabaseCount('partido_sustituciones', 2);
    }

    public function test_sustitucion_no_modifica_partidos_de_playoff(): void
    {
        $eq10 = $this->crearEquipo('Equipo 10');
        $eq7 = $this->crearEquipo('Equipo 7');
        $eq8 = $this->crearEquipo('Equipo 8');

        $jornada1 = Jornada::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'nombre' => 'Jornada 1',
            'numero' => 1,
            'fecha_inicio' => '2026-06-01',
            'estado' => 'borrador',
        ]);

        $partidoRegular = Partido::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'jornada_id' => $jornada1->id,
            'equipo_local_id' => $eq10->id,
            'equipo_visitante_id' => $eq8->id,
            'fecha' => '2026-06-01',
            'hora' => '19:00',
            'estado' => 'programado',
            'fase' => 'regular',
        ]);

        $partidoPlayoff = Partido::create([
            'tenant_id' => $this->tenant->id,
            'torneo_id' => $this->torneo->id,
            'jornada_id' => null,
            'equipo_local_id' => $eq7->id,
            'equipo_visitante_id' => $eq8->id,
            'fecha' => '2026-06-01',
            'hora' => '19:00',
            'estado' => 'programado',
            'fase' => 'cuartos',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('calendario.sustituir', $partidoRegular), [
                'equipo_original_id' => $eq10->id,
                'equipo_sustituto_id' => $eq7->id,
                'motivo' => 'no_asistencia',
                'tipo_resolucion' => 'doble_jornada',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('partidos', [
            'id' => $partidoRegular->id,
            'equipo_local_id' => $eq7->id,
            'equipo_visitante_id' => $eq8->id,
        ]);

        $this->assertDatabaseHas('partidos', [
            'id' => $partidoPlayoff->id,
            'equipo_local_id' => $eq7->id,
            'equipo_visitante_id' => $eq8->id,
        ]);
    }
}
