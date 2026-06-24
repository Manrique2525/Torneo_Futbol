<?php

namespace Tests\Unit;

use App\Contexts\TenantContext;
use App\Models\Partido;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\Torneo;
use App\Models\TorneoEquipo;
use App\Models\User;
use App\Services\PartidoPlayoffService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PartidoPlayoffServiceTest extends TestCase
{
    use RefreshDatabase;

    private PartidoPlayoffService $service;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PartidoPlayoffService::class);
        $this->tenant = Tenant::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'status' => 'active',
        ]);
        app(TenantContext::class)->set($this->tenant->id);
        $this->user = User::factory()->create();
    }

    private function crearTorneo(): Torneo
    {
        return Torneo::create([
            'nombre' => 'Test Copa',
            'tipo' => 'copa',
            'categoria' => 'libre',
            'rama' => 'varonil',
            'fecha_inicio' => '2026-01-01',
            'created_by' => $this->user->id,
        ]);
    }

    private function crearEquipo(Torneo $torneo, int $seed): TorneoEquipo
    {
        $team = Team::create([
            'name' => "Equipo $seed",
            'delegado_id' => $this->user->id,
        ]);

        return TorneoEquipo::create([
            'torneo_id' => $torneo->id,
            'team_id' => $team->id,
            'seed' => $seed,
            'estado' => 'aprobado',
        ]);
    }

    private function crearPartido(Torneo $torneo, array $data): Partido
    {
        $p = Partido::create(array_merge([
            'torneo_id' => $torneo->id,
            'fecha' => '2026-01-01',
            'hora' => '10:00',
            'estado' => 'finalizado',
        ], $data));

        return $p->fresh()->load('equipoLocal', 'equipoVisitante');
    }

    public function test_determina_ganador_por_goles_en_partido_unico(): void
    {
        $torneo = $this->crearTorneo();
        $local = $this->crearEquipo($torneo, 1);
        $visitante = $this->crearEquipo($torneo, 2);

        $partido = $this->crearPartido($torneo, [
            'fase' => 'semifinal',
            'llave_bracket' => 'SF1',
            'equipo_local_id' => $local->id,
            'equipo_visitante_id' => $visitante->id,
        ]);
        $partido->goles_local = 3;
        $partido->goles_visitante = 1;
        $partido->save();
        $partido->load('equipoLocal', 'equipoVisitante');

        $ganador = $this->service->determinarGanador(new Collection([$partido]));

        $this->assertNotNull($ganador);
        $this->assertEquals($local->id, $ganador->id);
    }

    public function test_determina_ganador_por_penales(): void
    {
        $torneo = $this->crearTorneo();
        $local = $this->crearEquipo($torneo, 1);
        $visitante = $this->crearEquipo($torneo, 2);

        $partido = $this->crearPartido($torneo, [
            'fase' => 'semifinal',
            'llave_bracket' => 'SF1',
            'equipo_local_id' => $local->id,
            'equipo_visitante_id' => $visitante->id,
            'goles_penales_local' => 4,
            'goles_penales_visitante' => 3,
        ]);
        $partido->goles_local = 1;
        $partido->goles_visitante = 1;
        $partido->save();
        $partido->load('equipoLocal', 'equipoVisitante');

        $ganador = $this->service->determinarGanador(new Collection([$partido]));

        $this->assertNotNull($ganador);
        $this->assertEquals($local->id, $ganador->id);
    }

    public function test_determina_ganador_por_agregado_en_ida_y_vuelta(): void
    {
        $torneo = $this->crearTorneo();
        $a = $this->crearEquipo($torneo, 1);
        $b = $this->crearEquipo($torneo, 2);

        $ida = $this->crearPartido($torneo, [
            'fase' => 'semifinal',
            'llave_bracket' => 'SF1',
            'orden_bracket' => 1,
            'es_vuelta' => false,
            'equipo_local_id' => $a->id,
            'equipo_visitante_id' => $b->id,
        ]);
        $ida->goles_local = 2;
        $ida->goles_visitante = 1;
        $ida->save();

        $vuelta = $this->crearPartido($torneo, [
            'fase' => 'semifinal',
            'llave_bracket' => 'SF1',
            'orden_bracket' => 2,
            'es_vuelta' => true,
            'equipo_local_id' => $b->id,
            'equipo_visitante_id' => $a->id,
            'fecha' => '2026-01-08',
        ]);
        $vuelta->goles_local = 1;
        $vuelta->goles_visitante = 0;
        $vuelta->save();

        $partidosLlave = Partido::where('torneo_id', $torneo->id)
            ->where('llave_bracket', 'SF1')
            ->get()
            ->load('equipoLocal', 'equipoVisitante');

        $ganador = $this->service->determinarGanador($partidosLlave);

        $this->assertNotNull($ganador);
        $this->assertEquals($b->id, $ganador->id);
    }

    public function test_no_avanza_si_hay_partidos_sin_finalizar_en_la_llave(): void
    {
        $torneo = $this->crearTorneo();
        $a = $this->crearEquipo($torneo, 1);
        $b = $this->crearEquipo($torneo, 2);

        $ida = $this->crearPartido($torneo, [
            'fase' => 'semifinal',
            'llave_bracket' => 'SF1',
            'orden_bracket' => 1,
            'es_vuelta' => false,
            'equipo_local_id' => $a->id,
            'equipo_visitante_id' => $b->id,
        ]);
        $ida->goles_local = 2;
        $ida->goles_visitante = 1;
        $ida->save();

        Partido::create([
            'torneo_id' => $torneo->id,
            'fase' => 'semifinal',
            'llave_bracket' => 'SF1',
            'orden_bracket' => 2,
            'es_vuelta' => true,
            'equipo_local_id' => $b->id,
            'equipo_visitante_id' => $a->id,
            'fecha' => '2026-01-08',
            'hora' => '10:00',
            'estado' => 'programado',
        ]);

        $final = Partido::create([
            'torneo_id' => $torneo->id,
            'fase' => 'final',
            'llave_bracket' => 'F1',
            'fecha' => '2026-01-15',
            'hora' => '12:00',
            'equipo_local_id' => $a->id,
            'equipo_visitante_id' => $b->id,
            'estado' => 'programado',
        ]);
        $final->equipo_local_id = null;
        $final->equipo_visitante_id = null;
        $final->save();

        $this->service->avanzarGanador($ida);

        $final->refresh();
        $this->assertNull($final->equipo_local_id);
        $this->assertNull($final->equipo_visitante_id);
    }

    public function test_avanza_ganador_a_siguiente_ronda(): void
    {
        $torneo = $this->crearTorneo();
        $a = $this->crearEquipo($torneo, 1);
        $b = $this->crearEquipo($torneo, 2);
        $c = $this->crearEquipo($torneo, 3);
        $d = $this->crearEquipo($torneo, 4);

        $sf1 = $this->crearPartido($torneo, [
            'fase' => 'semifinal',
            'llave_bracket' => 'SF1',
            'equipo_local_id' => $a->id,
            'equipo_visitante_id' => $b->id,
        ]);
        $sf1->goles_local = 3;
        $sf1->goles_visitante = 0;
        $sf1->save();

        $sf2 = $this->crearPartido($torneo, [
            'fase' => 'semifinal',
            'llave_bracket' => 'SF2',
            'equipo_local_id' => $c->id,
            'equipo_visitante_id' => $d->id,
            'fecha' => '2026-01-02',
        ]);
        $sf2->goles_local = 2;
        $sf2->goles_visitante = 1;
        $sf2->save();

        $final = Partido::create([
            'torneo_id' => $torneo->id,
            'fase' => 'final',
            'llave_bracket' => 'F1',
            'fecha' => '2026-01-15',
            'hora' => '12:00',
            'equipo_local_id' => $a->id,
            'equipo_visitante_id' => $b->id,
            'estado' => 'programado',
        ]);
        $final->equipo_local_id = null;
        $final->equipo_visitante_id = null;
        $final->save();

        $this->service->avanzarGanador($sf1);
        $this->service->avanzarGanador($sf2);

        $final->refresh();
        $this->assertEquals($a->id, $final->equipo_local_id);
        $this->assertEquals($c->id, $final->equipo_visitante_id);
    }

    public function test_buscar_siguiente_partido_mapping(): void
    {
        $torneo = $this->crearTorneo();
        $equipos = [];
        for ($i = 1; $i <= 4; $i++) {
            $equipos[$i] = $this->crearEquipo($torneo, $i);
        }

        $sf1 = $this->crearPartido($torneo, [
            'fase' => 'semifinal',
            'llave_bracket' => 'SF1',
            'equipo_local_id' => $equipos[1]->id,
            'equipo_visitante_id' => $equipos[2]->id,
        ]);

        $sf2 = $this->crearPartido($torneo, [
            'fase' => 'semifinal',
            'llave_bracket' => 'SF2',
            'equipo_local_id' => $equipos[3]->id,
            'equipo_visitante_id' => $equipos[4]->id,
            'fecha' => '2026-01-02',
        ]);

        $final = Partido::create([
            'torneo_id' => $torneo->id,
            'fase' => 'final',
            'llave_bracket' => 'F1',
            'fecha' => '2026-01-15',
            'hora' => '12:00',
            'equipo_local_id' => $equipos[1]->id,
            'equipo_visitante_id' => $equipos[2]->id,
            'estado' => 'programado',
        ]);
        $final->equipo_local_id = null;
        $final->equipo_visitante_id = null;
        $final->save();

        $siguiente = $this->service->buscarSiguientePartido($sf1);
        $this->assertNotNull($siguiente);
        $this->assertEquals($final->id, $siguiente->id);
        $this->assertTrue($this->service->esLocal($sf1));

        $siguiente = $this->service->buscarSiguientePartido($sf2);
        $this->assertNotNull($siguiente);
        $this->assertEquals($final->id, $siguiente->id);
        $this->assertFalse($this->service->esLocal($sf2));
    }

    public function test_no_hace_nada_si_es_fase_regular_o_final(): void
    {
        $torneo = $this->crearTorneo();
        $e1 = $this->crearEquipo($torneo, 1);
        $e2 = $this->crearEquipo($torneo, 2);

        $regular = $this->crearPartido($torneo, [
            'fase' => 'regular',
            'equipo_local_id' => $e1->id,
            'equipo_visitante_id' => $e2->id,
        ]);

        $this->service->avanzarGanador($regular);
        $this->assertTrue(true);

        $final = Partido::create([
            'torneo_id' => $torneo->id,
            'fase' => 'final',
            'llave_bracket' => 'F1',
            'equipo_local_id' => $e1->id,
            'equipo_visitante_id' => $e2->id,
            'fecha' => '2026-01-15',
            'hora' => '12:00',
            'estado' => 'finalizado',
        ]);

        $this->service->avanzarGanador($final);
        $this->assertTrue(true);
    }

    public function test_determina_ganador_con_visitante_ganando(): void
    {
        $torneo = $this->crearTorneo();
        $local = $this->crearEquipo($torneo, 1);
        $visitante = $this->crearEquipo($torneo, 2);

        $partido = $this->crearPartido($torneo, [
            'fase' => 'semifinal',
            'llave_bracket' => 'SF1',
            'equipo_local_id' => $local->id,
            'equipo_visitante_id' => $visitante->id,
        ]);
        $partido->goles_local = 0;
        $partido->goles_visitante = 2;
        $partido->save();
        $partido->load('equipoLocal', 'equipoVisitante');

        $ganador = $this->service->determinarGanador(new Collection([$partido]));

        $this->assertNotNull($ganador);
        $this->assertEquals($visitante->id, $ganador->id);
    }

    public function test_retorna_null_si_empate_sin_penales(): void
    {
        $torneo = $this->crearTorneo();
        $local = $this->crearEquipo($torneo, 1);
        $visitante = $this->crearEquipo($torneo, 2);

        $partido = $this->crearPartido($torneo, [
            'fase' => 'semifinal',
            'llave_bracket' => 'SF1',
            'equipo_local_id' => $local->id,
            'equipo_visitante_id' => $visitante->id,
        ]);
        $partido->goles_local = 1;
        $partido->goles_visitante = 1;
        $partido->save();
        $partido->load('equipoLocal', 'equipoVisitante');

        $ganador = $this->service->determinarGanador(new Collection([$partido]));

        $this->assertNull($ganador);
    }
}
