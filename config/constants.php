<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Perfiles de Usuario - Sistema de Torneos
    |--------------------------------------------------------------------------
    */
    'roles' => [

        'admin' => [
            'slug' => 'admin',
            'descripcion' => 'Control total del sistema SaaS y gestión global de usuarios.',
        ],

        'organizador' => [
            'slug' => 'organizador',
            'descripcion' => 'Crea y administra torneos, equipos, reglas y configuraciones.',
        ],

        'entrenador' => [
            'slug' => 'entrenador',
            'descripcion' => 'Gestiona su equipo, registra jugadores y consulta partidos.',
        ],

        'jugador' => [
            'slug' => 'jugador',
            'descripcion' => 'Consulta estadísticas, partidos y su información personal.',
        ],

        'arbitro' => [
            'slug' => 'arbitro',
            'descripcion' => 'Registra resultados, tarjetas y reportes de partidos.',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Tipos de Torneo
    |--------------------------------------------------------------------------
    */
    'tipos_torneo' => [
        'liga' => 'Liga',
        'copa' => 'Copa',
        'relampago' => 'Relámpago',
    ],

    /*
    |--------------------------------------------------------------------------
    | Categorías
    |--------------------------------------------------------------------------
    */
    'categorias' => [
        'libre' => 'Libre',
        'infantil' => 'Infantil',
        'veteranos' => 'Veteranos',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ramas
    |--------------------------------------------------------------------------
    */
    'ramas' => [
        'varonil' => 'Varonil',
        'femenil' => 'Femenil',
        'mixta' => 'Mixta',
    ],

    /*
    |--------------------------------------------------------------------------
    | Colores de Equipos
    |--------------------------------------------------------------------------
    */
    'colores_equipos' => [
        'BLANCO' => '#FFFFFF',
        'NEGRO' => '#000000',
        'ROJO' => '#FF0000',
        'VERDE' => '#00FF00',
        'AZUL' => '#0000FF',
        'AMARILLO' => '#FFFF00',
        'VIOLETA' => '#8B00FF',
        'NARANJA' => '#FFA500',
        'ROSADO' => '#FFC0CB',
        'MORADO' => '#800080',
        'GRIS' => '#808080',
        'CELESTE' => '#87CEEB',
        'DORADO' => '#FFD700',
        'PLATEADO' => '#C0C0C0',
        'BRONCE' => '#CD7F32',
        'MARRON' => '#8B4513',
        'CAFE' => '#8B4513',
        'TURQUESA' => '#40E0D0',
        'MAGENTA' => '#FF00FF',
        'LIMA' => '#00FF00',
        'OLIVA' => '#808000',
        'MARINO' => '#000080',
        'BURDEOS' => '#800000',
    ],


    /*
    |--------------------------------------------------------------------------
    | Niveles de Árbitros
    |--------------------------------------------------------------------------
    */
    'niveles_arbitro' => [
        'nacional' => 'Nacional',
        'regional' => 'Regional',
        'local' => 'Local',
        'internacional' => 'Internacional',
    ],

    /*
    |--------------------------------------------------------------------------
    | Posiciones de Jugadores
    |--------------------------------------------------------------------------
    */
    'posiciones_jugador' => [
        'portero' => 'Portero',
        'defensa' => 'Defensa',
        'mediocampista' => 'Mediocampista',
        'delantero' => 'Delantero',
        'extremo' => 'Extremo',
        'lateral' => 'Lateral',
        'contencion' => 'Contención',
        'enganche' => 'Enganche',
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados de Jugadores
    |--------------------------------------------------------------------------
    */
    'estados_jugador' => [
        'activo' => 'Activo',
        'suspendido' => 'Suspendido',
        'lesionado' => 'Lesionado',
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados de inscripción (torneo_equipos)
    |--------------------------------------------------------------------------
    */
    'estados_inscripcion' => [
        'pendiente' => 'Pendiente',
        'aprobado' => 'Aprobado',
        'rechazado' => 'Rechazado',
        'retirado' => 'Retirado',
        'descalificado' => 'Descalificado',
        'baja_por_impago' => 'Baja por Impago',
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados del torneo
    |--------------------------------------------------------------------------
    */
    'estados_torneo' => [
        'activo' => 'Activo',
        'finalizado' => 'Finalizado',
        'cancelado' => 'Cancelado',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipos de Cancha
    |--------------------------------------------------------------------------
    */
    'tipos_cancha' => [
        'futbol-11' => 'Fútbol 11',
        'futbol-7' => 'Fútbol 7',
        'futbol-5' => 'Fútbol 5',
        'futbol-sala' => 'Fútbol Sala',
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados de Cancha
    |--------------------------------------------------------------------------
    */
    'estados_cancha' => [
        'activo' => 'Activo',
        'inactivo' => 'Inactivo',
        'mantenimiento' => 'En Mantenimiento',
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados de Jornada
    |--------------------------------------------------------------------------
    */
    'estados_jornada' => [
        'borrador' => 'Borrador',
        'programada' => 'Programada',
        'finalizada' => 'Finalizada',
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados de Partido
    |--------------------------------------------------------------------------
    */
    'estados_partido' => [
        'programado' => 'Programado',
        'en_juego' => 'En Juego',
        'descanso' => 'Descanso',
        'finalizado' => 'Finalizado',
        'suspendido' => 'Suspendido',
        'cancelado' => 'Cancelado',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fases de Partido
    |--------------------------------------------------------------------------
    */
    'fases_partido' => [
        'regular' => 'Fase Regular',
        'octavos' => 'Octavos de Final',
        'cuartos' => 'Cuartos de Final',
        'semifinal' => 'Semifinal',
        'final' => 'Final',
        'tercer_lugar' => 'Tercer Lugar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Formatos Relámpago
    |--------------------------------------------------------------------------
    */
    'formatos_relampago' => [
        'grupos' => 'Fase de Grupos',
        'eliminacion_directa' => 'Eliminación Directa',
    ],

    /*
    |--------------------------------------------------------------------------
    | Motivos de Sustitución
    |--------------------------------------------------------------------------
    */
    'motivos_sustitucion' => [
        'clima' => 'Clima',
        'no_presentacion' => 'No Presentación',
        'no_asistencia' => 'No Asistencia',
        'otro' => 'Otro',
    ],

    'tipos_resolucion_sustitucion' => [
        'reprogramado' => 'Reprogramar Partido',
        'doble_jornada' => 'Doble Jornada',
    ],

    /*
    |--------------------------------------------------------------------------
    | Métodos de Pago
    |--------------------------------------------------------------------------
    */
    'metodos_pago' => [
        'efectivo' => 'Efectivo',
        'transferencia' => 'Transferencia',
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados de Pago (inscripcion_pagos)
    |--------------------------------------------------------------------------
    */
    'estados_pago' => [
        'pendiente' => 'Pendiente',
        'confirmado' => 'Confirmado',
        'rechazado' => 'Rechazado',
    ],
    /*
    |--------------------------------------------------------------------------
    | Tipos de Gestión de Torneo
    |--------------------------------------------------------------------------
    */
    'tipos_gestion' => [
        'auto' => 'Automático (Calendario)',
        'manual' => 'Manual',
    ],
];
