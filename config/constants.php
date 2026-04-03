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

];