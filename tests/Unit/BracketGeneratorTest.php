<?php

namespace Tests\Unit;

use App\Services\BracketGenerator;
use PHPUnit\Framework\TestCase;

class BracketGeneratorTest extends TestCase
{
    private BracketGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new BracketGenerator();
    }

    public function test_generates_correct_phases_for_8_teams(): void
    {
        $equipos = [];
        for ($i = 1; $i <= 8; $i++) {
            $equipos[] = ['id' => $i, 'team_id' => $i];
        }

        $bracket = $this->generator->generar($equipos, false);

        $fases = array_column($bracket, 'fase');

        $this->assertContains('cuartos', $fases);
        $this->assertContains('semifinal', $fases);
        $this->assertContains('final', $fases);
    }

    public function test_generates_correct_phases_for_4_teams(): void
    {
        $equipos = [];
        for ($i = 1; $i <= 4; $i++) {
            $equipos[] = ['id' => $i, 'team_id' => $i];
        }

        $bracket = $this->generator->generar($equipos, false);

        $fases = array_column($bracket, 'fase');

        $this->assertContains('semifinal', $fases);
        $this->assertContains('final', $fases);
        $this->assertNotContains('cuartos', $fases);
    }

    public function test_generates_correct_phases_for_2_teams(): void
    {
        $equipos = [
            ['id' => 1, 'team_id' => 1],
            ['id' => 2, 'team_id' => 2],
        ];

        $bracket = $this->generator->generar($equipos, false);

        $this->assertCount(1, $bracket);
        $this->assertEquals('final', $bracket[0]['fase']);
    }

    public function test_returns_empty_for_non_power_of_2(): void
    {
        $equipos = [];
        for ($i = 1; $i <= 6; $i++) {
            $equipos[] = ['id' => $i, 'team_id' => $i];
        }

        $bracket = $this->generator->generar($equipos, false);

        $this->assertEmpty($bracket);
    }

    public function test_returns_empty_for_less_than_2_teams(): void
    {
        $equipos = [
            ['id' => 1, 'team_id' => 1],
        ];

        $bracket = $this->generator->generar($equipos, false);

        $this->assertEmpty($bracket);
    }

    public function test_standard_seeding_crosses_1_vs_last(): void
    {
        $equipos = [];
        for ($i = 1; $i <= 8; $i++) {
            $equipos[] = ['id' => $i, 'team_id' => $i, 'posicion' => $i];
        }

        $bracket = $this->generator->generar($equipos, false);

        $cuartos = $bracket[0];
        $primerPartido = $cuartos['partidos'][0];

        $this->assertEquals(1, $primerPartido['local']['id'], 'El 1er seed debe ser local en el primer partido');
        $this->assertEquals(8, $primerPartido['visitante']['id'], 'El último seed debe ser visitante en el primer partido');
    }

    public function test_ida_y_vuelta_generates_two_matches_per_key(): void
    {
        $equipos = [];
        for ($i = 1; $i <= 4; $i++) {
            $equipos[] = ['id' => $i, 'team_id' => $i];
        }

        $bracket = $this->generator->generar($equipos, true);

        $semifinal = $bracket[0];
        $this->assertCount(4, $semifinal['partidos'], 'Semifinal debe tener 4 partidos (2 llaves x 2 partidos)');

        $llaves = array_unique(array_column($semifinal['partidos'], 'llave'));
        $this->assertCount(2, $llaves, 'Debe haber 2 llaves en semifinal');
    }

    public function test_final_is_always_single_match_even_with_ida_y_vuelta(): void
    {
        $equipos = [];
        for ($i = 1; $i <= 4; $i++) {
            $equipos[] = ['id' => $i, 'team_id' => $i];
        }

        $bracket = $this->generator->generar($equipos, true);

        $final = end($bracket);
        $this->assertEquals('final', $final['fase']);
        $this->assertCount(1, $final['partidos'], 'La final siempre es un solo partido');
    }

    public function test_generates_correct_number_of_matches_for_16_teams(): void
    {
        $equipos = [];
        for ($i = 1; $i <= 16; $i++) {
            $equipos[] = ['id' => $i, 'team_id' => $i];
        }

        $bracket = $this->generator->generar($equipos, false);

        $fases = array_column($bracket, 'fase');

        $this->assertContains('octavos', $fases);
        $this->assertContains('cuartos', $fases);
        $this->assertContains('semifinal', $fases);
        $this->assertContains('final', $fases);

        $octavos = $bracket[0];
        $this->assertCount(8, $octavos['partidos'], 'Octavos debe tener 8 partidos');
    }

    public function test_later_phases_have_placeholder_matches(): void
    {
        $equipos = [];
        for ($i = 1; $i <= 8; $i++) {
            $equipos[] = ['id' => $i, 'team_id' => $i];
        }

        $bracket = $this->generator->generar($equipos, false);

        $semifinal = $bracket[1];

        foreach ($semifinal['partidos'] as $partido) {
            $this->assertNull($partido['local']);
            $this->assertNull($partido['visitante']);
            $this->assertArrayHasKey('placeholder', $partido);
        }
    }
}
