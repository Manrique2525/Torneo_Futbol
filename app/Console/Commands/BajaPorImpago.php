<?php

namespace App\Console\Commands;

use App\Mail\EquipoBajaImpago;
use App\Models\Torneo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BajaPorImpago extends Command
{
    protected $signature = 'torneos:baja-por-impago';

    protected $description = 'Da de baja a equipos que no han pagado la inscripción tras superar el máximo de jornadas';

    public function handle(): int
    {
        $this->info('Verificando equipos sin pago...');

        $dadosDeBaja = 0;

        $torneos = Torneo::where('pago_requerido', true)
            ->where('baja_por_impago_automatica', true)
            ->whereNotNull('max_jornadas_sin_pago')
            ->where('max_jornadas_sin_pago', '>', 0)
            ->where('estado', 'activo')
            ->get();

        foreach ($torneos as $torneo) {
            $jornadasTranscurridas = $torneo->jornadas()->count();

            if ($jornadasTranscurridas < $torneo->max_jornadas_sin_pago) {
                continue;
            }

            $equiposSinPago = $torneo->inscripciones()
                ->where('estado', 'aprobado')
                ->whereDoesntHave('pagos', fn ($q) => $q->where('estado', 'confirmado'))
                ->get();

            foreach ($equiposSinPago as $equipo) {
                DB::transaction(function () use ($equipo, $torneo, $jornadasTranscurridas) {
                    $equipo->update([
                        'estado' => 'baja_por_impago',
                        'motivo_rechazo' => 'Baja automática por impago: superó el máximo de jornadas sin pagar.',
                        'notas' => "Jornadas transcurridas: {$jornadasTranscurridas}, Máximo permitido: {$torneo->max_jornadas_sin_pago}",
                    ]);

                    if ($equipo->equipo?->email) {
                        Mail::to($equipo->equipo->email)->queue(
                            new EquipoBajaImpago($equipo, $jornadasTranscurridas, $torneo->max_jornadas_sin_pago)
                        );
                    }
                });

                $dadosDeBaja++;
                Log::info('Equipo dado de baja por impago', [
                    'torneo_id' => $torneo->id,
                    'equipo_id' => $equipo->id,
                    'team_id' => $equipo->team_id,
                    'jornadas' => $jornadasTranscurridas,
                    'max_jornadas' => $torneo->max_jornadas_sin_pago,
                ]);
            }
        }

        $this->info("Proceso completado. Equipos dados de baja: {$dadosDeBaja}");

        if ($dadosDeBaja > 0) {
            Log::info('BajaPorImpago summary', ['dados_de_baja' => $dadosDeBaja]);
        }

        return self::SUCCESS;
    }
}
