<?php


return [
    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role'       => Spatie\Permission\Models\Role::class,
    ],

    // ENABLE TEAMS — this is the magic for multi-tenant
    'teams' => true,

    // Map team_id to our tenant_id
    'team_foreign_key' => 'tenant_id',

    'table_names' => [
        'roles'                 => 'roles',
        'permissions'           => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles'       => 'model_has_roles',
        'role_has_permissions'  => 'role_has_permissions',
    ],

    'column_names' => [
        'role_pivot_key'       => 'role_id',
        'permission_pivot_key' => 'permission_id',
        'model_morph_key'      => 'model_id',
        'team_foreign_key'     => 'tenant_id',  // matches our FK
    ],

    // Register permissions in Gate
    'register_permission_check_method' => true,

    // Cache
    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key'             => 'spatie.permission.cache',
        'store'           => 'default',
    ],
];
