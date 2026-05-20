<?php

namespace App\Enums;

enum TorneoEquipoEstadoEnum: string
{
    case PENDIENTE      = 'pendiente';
    case APROBADO       = 'aprobado';
    case RECHAZADO      = 'rechazado';
    case RETIRADO       = 'retirado';
    case DESCALIFICADO  = 'descalificado';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE     => 'Pendiente',
            self::APROBADO      => 'Aprobado',
            self::RECHAZADO     => 'Rechazado',
            self::RETIRADO      => 'Retirado',
            self::DESCALIFICADO => 'Descalificado',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Estados que ocupan cupo en el torneo.
     *
     * @return list<string>
     */
    public static function cupoOcupante(): array
    {
        return [self::APROBADO->value];
    }
}
