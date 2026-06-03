<?php

namespace App\Enums;

enum PartidoEventoTipoEnum: string
{
    case GOL = 'gol';
    case AUTOGOL = 'autogol';
    case GOL_PENAL = 'gol_penal';
    case TARJETA_AMARILLA = 'tarjeta_amarilla';
    case TARJETA_ROJA = 'tarjeta_roja';
    case FALTA = 'falta';
    case SUSTITUCION_ENTRADA = 'sustitucion_entrada';
    case SUSTITUCION_SALIDA = 'sustitucion_salida';
    case PENAL_CONCEDIDO = 'penal_concedido';

    public function label(): string
    {
        return match ($this) {
            self::GOL => 'Gol',
            self::AUTOGOL => 'Autogol',
            self::GOL_PENAL => 'Gol de penal',
            self::TARJETA_AMARILLA => 'Tarjeta amarilla',
            self::TARJETA_ROJA => 'Tarjeta roja',
            self::FALTA => 'Falta',
            self::SUSTITUCION_ENTRADA => 'Sustitución (entra)',
            self::SUSTITUCION_SALIDA => 'Sustitución (sale)',
            self::PENAL_CONCEDIDO => 'Penal concedido',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::GOL => 'sports_soccer',
            self::AUTOGOL => 'sports_soccer',
            self::GOL_PENAL => 'sports_soccer',
            self::TARJETA_AMARILLA => 'style',
            self::TARJETA_ROJA => 'style',
            self::FALTA => 'gavel',
            self::SUSTITUCION_ENTRADA => 'swap_horiz',
            self::SUSTITUCION_SALIDA => 'swap_horiz',
            self::PENAL_CONCEDIDO => 'sports',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::GOL, self::GOL_PENAL => 'text-emerald-600 bg-emerald-500/10 border-emerald-500/20',
            self::AUTOGOL => 'text-orange-600 bg-orange-500/10 border-orange-500/20',
            self::TARJETA_AMARILLA => 'text-amber-600 bg-amber-500/10 border-amber-500/20',
            self::TARJETA_ROJA => 'text-red-600 bg-red-500/10 border-red-500/20',
            self::FALTA => 'text-slate-600 bg-slate-500/10 border-slate-500/20',
            self::SUSTITUCION_ENTRADA, self::SUSTITUCION_SALIDA => 'text-blue-600 bg-blue-500/10 border-blue-500/20',
            self::PENAL_CONCEDIDO => 'text-purple-600 bg-purple-500/10 border-purple-500/20',
        };
    }

    public static function all(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'icon' => $case->icon(),
            'colorClass' => $case->colorClass(),
        ], self::cases());
    }
}
