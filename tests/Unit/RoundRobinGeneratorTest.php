<?php

namespace Tests\Unit;

use App\Services\RoundRobinGenerator;
use PHPUnit\Framework\TestCase;

class RoundRobinGeneratorTest extends TestCase
{
    private RoundRobinGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new RoundRobinGenerator();
    }

    public function test_generates_correct_number_of_jornadas_for_even_teams(): void
    {
        $equipos = [
            1 => ['id' => 1, 'team_id' => 1],
            2 => ['id' => 2, 'team_id' => 2],
            3 => ['id' => 3, 'team_id' => 3],
            4 => ['id' => 4, 'team_id' => 4],
        ];

        $jornadas = $this->generator->generar($equipos, false);

        $this->assertCount(3, $jornadas);
    }

    public function test_generates_correct_number_of_jornadas_for_odd_teams(): void
    {
        $equipos = [
            1 => ['id' => 1, 'team_id' => 1],
            2 => ['id' => 2, 'team_id' => 2],
            3 => ['id' => 3, 'team_id' => 3],
            4 => ['id' => 4, 'team_id' => 4],
            5 => ['id' => 5, 'team_id' => 5],
        ];

        $jornadas = $this->generator->generar($equipos, false);

        $this->assertCount(5, $jornadas);
    }

    public function test_doubles_jornadas_with_ida_y_vuelta(): void
    {
        $equipos = [
            1 => ['id' => 1, 'team_id' => 1],
            2 => ['id' => 2, 'team_id' => 2],
            3 => ['id' => 3, 'team_id' => 3],
            4 => ['id' => 4, 'team_id' => 4],
        ];

        $jornadas = $this->generator->generar($equipos, true);

        $this->assertCount(6, $jornadas);
    }

    public function test_each_pair_plays_exactly_once_without_ida_y_vuelta(): void
    {
        $equipos = [
            1 => ['id' => 1, 'team_id' => 1],
            2 => ['id' => 2, 'team_id' => 2],
            3 => ['id' => 3, 'team_id' => 3],
            4 => ['id' => 4, 'team_id' => 4],
        ];

        $jornadas = $this->generator->generar($equipos, false);

        $pares = [];
        foreach ($jornadas as $jornada) {
            foreach ($jornada['partidos'] as $partido) {
                $par = min($partido['local']['id'], $partido['visitante']['id']) . '-' .
                       max($partido['local']['id'], $partido['visitante']['id']);
                $pares[$par] = ($pares[$par] ?? 0) + 1;
            }
        }

        foreach ($pares as $count) {
            $this->assertEquals(1, $count, 'Cada par debe jugar exactamente 1 vez sin ida y vuelta');
        }
    }

    public function test_each_pair_plays_exactly_twice_with_ida_y_vuelta(): void
    {
        $equipos = [
            1 => ['id' => 1, 'team_id' => 1],
            2 => ['id' => 2, 'team_id' => 2],
            3 => ['id' => 3, 'team_id' => 3],
            4 => ['id' => 4, 'team_id' => 4],
        ];

        $jornadas = $this->generator->generar($equipos, true);

        $pares = [];
        foreach ($jornadas as $jornada) {
            foreach ($jornada['partidos'] as $partido) {
                $par = min($partido['local']['id'], $partido['visitante']['id']) . '-' .
                       max($partido['local']['id'], $partido['visitante']['id']);
                $pares[$par] = ($pares[$par] ?? 0) + 1;
            }
        }

        foreach ($pares as $count) {
            $this->assertEquals(2, $count, 'Cada par debe jugar exactamente 2 veces con ida y vuelta');
        }
    }

    public function test_returns_empty_for_less_than_two_teams(): void
    {
        $equipos = [
            1 => ['id' => 1, 'team_id' => 1],
        ];

        $jornadas = $this->generator->generar($equipos, false);

        $this->assertEmpty($jornadas);
    }

    public function test_each_team_plays_once_per_jornada(): void
    {
        $equipos = [
            1 => ['id' => 1, 'team_id' => 1],
            2 => ['id' => 2, 'team_id' => 2],
            3 => ['id' => 3, 'team_id' => 3],
            4 => ['id' => 4, 'team_id' => 4],
            5 => ['id' => 5, 'team_id' => 5],
            6 => ['id' => 6, 'team_id' => 6],
        ];

        $jornadas = $this->generator->generar($equipos, false);

        foreach ($jornadas as $jornada) {
            $equiposEnJornada = [];

            foreach ($jornada['partidos'] as $partido) {
                $localId = $partido['local']['id'];
                $visitanteId = $partido['visitante']['id'];

                $this->assertNotContains($localId, $equiposEnJornada, "Equipo {$localId} aparece más de una vez en la jornada");
                $this->assertNotContains($visitanteId, $equiposEnJornada, "Equipo {$visitanteId} aparece más de una vez en la jornada");

                $equiposEnJornada[] = $localId;
                $equiposEnJornada[] = $visitanteId;
            }
        }
    }

    public function test_ida_y_vuelta_inverts_home_away(): void
    {
        $equipos = [
            1 => ['id' => 1, 'team_id' => 1],
            2 => ['id' => 2, 'team_id' => 2],
            3 => ['id' => 3, 'team_id' => 3],
            4 => ['id' => 4, 'team_id' => 4],
        ];

        $jornadas = $this->generator->generar($equipos, true);

        $ida = array_slice($jornadas, 0, 3);
        $vuelta = array_slice($jornadas, 3, 3);

        $idaPares = [];
        foreach ($ida as $jornada) {
            foreach ($jornada['partidos'] as $partido) {
                $idaPares[] = $partido['local']['id'] . '-' . $partido['visitante']['id'];
            }
        }

        $vueltaPares = [];
        foreach ($vuelta as $jornada) {
            foreach ($jornada['partidos'] as $partido) {
                $vueltaPares[] = $partido['local']['id'] . '-' . $partido['visitante']['id'];
            }
        }

        foreach ($idaPares as $idx => $par) {
            [$local, $visitante] = explode('-', $par);
            $this->assertContains(
                $visitante . '-' . $local,
                $vueltaPares,
                "El partido de vuelta debe invertir local/visitante"
            );
        }
    }
}
