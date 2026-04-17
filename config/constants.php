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

];
