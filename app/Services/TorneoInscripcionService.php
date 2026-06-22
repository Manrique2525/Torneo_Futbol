<?php

namespace App\Services;

use App\Enums\PermissionEnum;
use App\Enums\TorneoEquipoEstadoEnum;
use App\Mail\EquipoInscritoTorneo;
use App\Models\Team;
use App\Models\Torneo;
use App\Models\TorneoEquipo;
use App\Models\TorneoGrupo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class TorneoInscripcionService
{
    public const GRUPO_GENERAL = 'General';

    public const FAIR_PLAY_INICIAL = 10.0;

    /**
     * Crea el grupo "General" si el torneo no tiene grupos.
     */
    public function ensureGrupoGeneral(Torneo $torneo): TorneoGrupo
    {
        return $torneo->grupos()->firstOrCreate(
            ['nombre' => self::GRUPO_GENERAL],
            ['orden' => 0]
        );
    }

    /**
     * @param  array{team_id: int, torneo_grupo_id?: int|null, seed?: int|null, notas?: string|null, estado?: string|null}  $data
     */
    public function inscribir(Torneo $torneo, array $data, User $actor): TorneoEquipo
    {
        $this->assertTorneoAceptaInscripciones($torneo);

        $team = Team::findOrFail($data['team_id']);
        $this->assertMismoTenant($torneo, $team);
        $this->assertEquipoActivo($team);
        $this->assertSinDuplicado($torneo, $team);

        $estado = $this->resolverEstadoInicial($actor, $data['estado'] ?? null);

        if ($estado === TorneoEquipoEstadoEnum::APROBADO->value) {
            $this->assertCupoDisponible($torneo);
        }

        $grupo = $this->resolverGrupo($torneo, $data['torneo_grupo_id'] ?? null);

        if (! empty($data['seed'])) {
            $this->assertSeedUnico($torneo, (int) $data['seed']);
        }

        $inscripcion = DB::transaction(function () use ($torneo, $team, $data, $estado, $grupo, $actor) {
            $inscripcion = TorneoEquipo::create([
                'torneo_id' => $torneo->id,
                'team_id' => $team->id,
                'torneo_grupo_id' => $grupo->id,
                'seed' => $data['seed'] ?? null,
                'estado' => $estado,
                'fair_play_points' => self::FAIR_PLAY_INICIAL,
                'notas' => $data['notas'] ?? null,
                'inscrito_at' => now(),
            ]);

            if ($estado === TorneoEquipoEstadoEnum::APROBADO->value) {
                $inscripcion = $this->marcarAprobado($inscripcion, $actor);
            }

            return $inscripcion->load(['equipo', 'grupo', 'aprobadoPor']);
        });

        $this->enviarCorreoInscripcion($inscripcion);

        return $inscripcion;
    }

    public function aprobar(TorneoEquipo $inscripcion, User $actor): TorneoEquipo
    {
        $torneo = $inscripcion->torneo;

        $this->assertTorneoAceptaInscripciones($torneo);

        if ($inscripcion->estado !== TorneoEquipoEstadoEnum::PENDIENTE->value) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden aprobar inscripciones pendientes.',
            ]);
        }

        $this->assertCupoDisponible($torneo, $inscripcion);

        if ($inscripcion->seed !== null) {
            $this->assertSeedUnico($torneo, (int) $inscripcion->seed, $inscripcion->id);
        }

        $inscripcion = DB::transaction(fn () => $this->marcarAprobado($inscripcion, $actor)
            ->load(['equipo', 'grupo', 'aprobadoPor']));

        $this->enviarCorreoInscripcion($inscripcion);

        return $inscripcion;
    }

    public function rechazar(TorneoEquipo $inscripcion, ?string $motivo = null): TorneoEquipo
    {
        if ($inscripcion->estado !== TorneoEquipoEstadoEnum::PENDIENTE->value) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden rechazar inscripciones pendientes.',
            ]);
        }

        $inscripcion->update([
            'estado' => TorneoEquipoEstadoEnum::RECHAZADO->value,
            'rechazado_at' => now(),
            'motivo_rechazo' => $motivo,
        ]);

        return $inscripcion->fresh(['equipo', 'grupo']);
    }

    public function asignarGrupo(TorneoEquipo $inscripcion, int $torneoGrupoId): TorneoEquipo
    {
        $grupo = TorneoGrupo::where('id', $torneoGrupoId)
            ->where('torneo_id', $inscripcion->torneo_id)
            ->first();

        if (! $grupo) {
            throw ValidationException::withMessages([
                'torneo_grupo_id' => 'El grupo no pertenece a este torneo.',
            ]);
        }

        $inscripcion->update(['torneo_grupo_id' => $grupo->id]);

        return $inscripcion->fresh(['equipo', 'grupo']);
    }

    public function asignarSeed(TorneoEquipo $inscripcion, ?int $seed): TorneoEquipo
    {
        if ($seed !== null) {
            $this->assertSeedUnico($inscripcion->torneo, $seed, $inscripcion->id);
        }

        $inscripcion->update(['seed' => $seed]);

        return $inscripcion->fresh(['equipo', 'grupo']);
    }

    public function contarCupoOcupado(Torneo $torneo): int
    {
        return $torneo->inscripciones()
            ->whereIn('estado', TorneoEquipoEstadoEnum::cupoOcupante())
            ->count();
    }

    // ── Assertions ──────────────────────────────────

    public function assertTorneoAceptaInscripciones(Torneo $torneo): void
    {
        if (in_array($torneo->estado, ['finalizado', 'cancelado'], true)) {
            throw ValidationException::withMessages([
                'torneo' => 'El torneo no acepta cambios en inscripciones (estado: '.$torneo->estado.').',
            ]);
        }

        if (! $torneo->inscripcion_abierta && ! auth()->user()?->can(PermissionEnum::TOURNAMENTS_UPDATE)) {
            throw ValidationException::withMessages([
                'torneo' => 'Las inscripciones están cerradas para este torneo.',
            ]);
        }
    }

    public function assertCupoDisponible(Torneo $torneo, ?TorneoEquipo $except = null): void
    {
        if ($torneo->max_equipos === null) {
            return;
        }

        $query = $torneo->inscripciones()->whereIn('estado', TorneoEquipoEstadoEnum::cupoOcupante());

        if ($except) {
            $query->where('id', '!=', $except->id);
        }

        if ($query->count() >= $torneo->max_equipos) {
            throw ValidationException::withMessages([
                'cupo' => "Se alcanzó el cupo máximo de {$torneo->max_equipos} equipos para este torneo.",
            ]);
        }
    }

    public function assertSeedUnico(Torneo $torneo, int $seed, ?int $exceptInscripcionId = null): void
    {
        $query = $torneo->inscripciones()
            ->where('seed', $seed)
            ->whereNotIn('estado', [
                TorneoEquipoEstadoEnum::RECHAZADO->value,
                TorneoEquipoEstadoEnum::RETIRADO->value,
            ]);

        if ($exceptInscripcionId) {
            $query->where('id', '!=', $exceptInscripcionId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'seed' => "El seed {$seed} ya está asignado en este torneo.",
            ]);
        }
    }

    protected function assertSinDuplicado(Torneo $torneo, Team $team): void
    {
        $exists = $torneo->inscripciones()
            ->where('team_id', $team->id)
            ->whereNotIn('estado', [
                TorneoEquipoEstadoEnum::RECHAZADO->value,
                TorneoEquipoEstadoEnum::RETIRADO->value,
            ])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'team_id' => 'Este equipo ya tiene una inscripción activa en el torneo.',
            ]);
        }
    }

    protected function assertMismoTenant(Torneo $torneo, Team $team): void
    {
        if ($team->tenant_id !== $torneo->tenant_id) {
            throw ValidationException::withMessages([
                'team_id' => 'El equipo no pertenece a la misma organización que el torneo.',
            ]);
        }
    }

    protected function assertEquipoActivo(Team $team): void
    {
        if ($team->status !== Team::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'team_id' => 'El equipo no está activo.',
            ]);
        }
    }

    protected function resolverEstadoInicial(User $actor, ?string $estadoSolicitado): string
    {
        if ($actor->can(PermissionEnum::TOURNAMENTS_UPDATE)) {
            return $estadoSolicitado === TorneoEquipoEstadoEnum::APROBADO->value
              ? TorneoEquipoEstadoEnum::APROBADO->value
              : TorneoEquipoEstadoEnum::PENDIENTE->value;
        }

        return TorneoEquipoEstadoEnum::PENDIENTE->value;
    }

    protected function resolverGrupo(Torneo $torneo, ?int $grupoId): TorneoGrupo
    {
        if ($grupoId) {
            $grupo = TorneoGrupo::where('id', $grupoId)
                ->where('torneo_id', $torneo->id)
                ->first();

            if (! $grupo) {
                throw ValidationException::withMessages([
                    'torneo_grupo_id' => 'El grupo no pertenece a este torneo.',
                ]);
            }

            return $grupo;
        }

        return $this->ensureGrupoGeneral($torneo);
    }

    protected function marcarAprobado(TorneoEquipo $inscripcion, User $actor): TorneoEquipo
    {
        $inscripcion->update([
            'estado' => TorneoEquipoEstadoEnum::APROBADO->value,
            'aprobado_at' => now(),
            'aprobado_por' => $actor->id,
            'rechazado_at' => null,
            'motivo_rechazo' => null,
        ]);

        return $inscripcion;
    }

    private function enviarCorreoInscripcion(TorneoEquipo $inscripcion): void
    {
        if ($inscripcion->estado !== TorneoEquipoEstadoEnum::APROBADO->value) {
            return;
        }

        $email = $inscripcion->equipo->email;

        if (! $email) {
            return;
        }

        Mail::to($email)
            ->queue(new EquipoInscritoTorneo($inscripcion));
    }
}
