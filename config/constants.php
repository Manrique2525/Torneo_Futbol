<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Perfiles de Usuario - Sistema de Torneos
    |--------------------------------------------------------------------------
    */

    'admin' => [
        'slug' => 'administrador',
        'descripcion' => 'Control total del sistema y gestión de usuarios.',
    ],

    'organizador' => [
        'slug' => 'organizador',
        'descripcion' => 'Creación de torneos, gestión de sedes y sorteos.',
    ],

    'arbitro' => [
        'slug' => 'arbitro',
        'descripcion' => 'Carga de resultados, tarjetas y actas de partidos.',
    ],

    'capitan' => [
        'slug' => 'capitan',
        'descripcion' => 'Gestión de su equipo, inscripción de jugadores y pagos.',
    ],

    'espectador' => [
        'slug' => 'espectador',
        'descripcion' => 'Usuario que solo consulta estadísticas y calendarios.',
    ],

    'jugador' => [
        'slug' => 'jugador',
        'descripcion' => 'Usuario que solo consulta estadísticas y calendarios.',
    ],


];
