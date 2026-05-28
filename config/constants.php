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
        'finalizado' => 'Finalizado',
        'suspendido' => 'Suspendido',
        'cancelado' => 'Cancelado',
    ],
];
