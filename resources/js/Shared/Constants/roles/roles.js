/**
 * Role configuration — labels & badge colors.
 *
 * Keys match RoleEnum values from the backend.
 * Used for Badge rendering and SelectInput options.
 */
export const ROLE_CONFIG = {
    super_admin: {
        label: 'Super Admin',
        classes: 'bg-violet-50 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400',
    },
    admin: {
        label: 'Administrador',
        classes: 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    },
    manager: {
        label: 'Coordinador',
        classes: 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
    },
    referee: {
        label: 'Árbitro',
        classes: 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
    },
    delegate: {
        label: 'Delegado',
        classes: 'bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400',
    },
    player: {
        label: 'Jugador',
        classes: 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    },
}

/**
 * For SelectInput / dropdowns.
 */
export const roleOptions = Object.entries(ROLE_CONFIG)
    .filter(([id]) => id !== 'super_admin')
    .map(([id, { label }]) => ({ id, name: label }))

/**
 * Human-readable label for a permission module.
 */
export const MODULE_LABELS = {
    tournaments: 'Torneos',
    teams:       'Equipos',
    players:     'Jugadores',
    matches:     'Partidos',
    match_days:  'Jornadas',
    fields:      'Canchas',
    referees:    'Árbitros',
    sanctions:   'Sanciones',
    payments:    'Pagos',
    stats:       'Estadísticas',
    standings:   'Tabla de posiciones',
    reports:     'Reportes',
    users:       'Usuarios',
    roles:       'Roles',
    settings:    'Configuración',
}

/**
 * Human-readable label for a permission action.
 */
export const ACTION_LABELS = {
    view:               'Ver',
    view_own:           'Ver propios',
    create:             'Crear',
    update:             'Editar',
    update_own:         'Editar propios',
    delete:             'Eliminar',
    record_events:      'Registrar eventos',
    confirm:            'Confirmar',
    confirm_attendance: 'Confirmar asistencia',
    verify:             'Verificar',
    upload_receipt:     'Subir comprobante',
    recalculate:        'Recalcular',
    export:             'Exportar',
    void:               'Anular',
}
