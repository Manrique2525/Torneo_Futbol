# Database Analysis — Torneo de Fútbol

26 migration files → 26 tables (including Laravel system tables).

---

## Table Inventory

| # | Table | Type | Migration | Model |
|---|-------|------|-----------|-------|
| 1 | `tenants` | Domain | `0001_01_01_000001_tenants_table` | `Tenant` |
| 2 | `users` | Auth | `0001_01_01_000002_create_users_table` | `User` |
| 3 | `password_reset_tokens` | System | (same as users) | — |
| 4 | `sessions` | System | (same as users) | — |
| 5 | `cache` | System | `0001_01_01_000003_create_cache_table` | — |
| 6 | `cache_locks` | System | (same as cache) | — |
| 7 | `jobs` | System | `0001_01_01_000003_create_jobs_table` | — |
| 8 | `job_batches` | System | (same as jobs) | — |
| 9 | `failed_jobs` | System | (same as jobs) | — |
| 10 | `personal_access_tokens` | System | (Sanctum auto) | — |
| 11 | `plans` | Billing | `2026_04_03_000001_create_plans_table` | `Plan` |
| 12 | `subscriptions` | Billing | `2026_04_03_000002_subscriptions_table` | `Subscription` |
| 13 | `usage_limits` | Billing | `2026_04_03_000003_usage_limits_table` | `UsageLimit` |
| 14 | `torneos` | Domain | `2026_04_03_181904_create_torneos_table` | `Torneo` |
| 15 | `permissions` | Spatie | `2026_04_04_215156_create_permission_tables` | — |
| 16 | `roles` | Spatie | (same) | — |
| 17 | `model_has_permissions` | Spatie | (same) | — |
| 18 | `model_has_roles` | Spatie | (same) | — |
| 19 | `role_has_permissions` | Spatie | (same) | — |
| 20 | `teams` | Domain | `2026_04_16_004930_create_teams_table` | `Team` |
| 21 | `arbitros` | Domain | `2026_04_17_001702_create_arbitros_table` | `Arbitro` |
| 22 | `players` | Domain | `2026_04_17_004458_create_players_table` | `Player` |
| 23 | `torneo_grupos` | Domain | `2026_05_19_000001_create_torneo_grupos_table` | `TorneoGrupo` |
| 24 | `torneo_equipos` | Domain | `2026_05_19_000003_create_torneo_equipos_table` | `TorneoEquipo` |
| 25 | `canchas` | Domain | `2026_05_25_000001_create_canchas_table` | `Cancha` |
| 26 | `disponibilidad_canchas` | Domain | `2026_05_27_000001_create_disponibilidad_canchas_table` | `DisponibilidadCancha` |
| 27 | `jornadas` | Domain | `2026_05_27_000002_create_jornadas_table` | `Jornada` |
| 28 | `partidos` | Domain | `2026_05_27_000003_create_partidos_table` | `Partido` |
| 29 | `partido_eventos` | Domain | `2026_06_02_000003_create_partido_eventos_table` | `PartidoEvento` |
| 30 | `partido_asistencias` | Domain | `2026_06_02_000004_create_partido_asistencias_table` | `PartidoAsistencia` |
| 31 | `torneo_standings` | Domain | `2026_06_03_000002_create_torneo_standings_table` | `TorneoStanding` |

---

## Column Details

### `tenants`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| uuid | uuid | **unique** |
| name | varchar(150) | not null |
| slug | varchar(100) | **unique** |
| custom_domain | varchar(255) | nullable, **unique** |
| logo | varchar | nullable |
| email | varchar(150) | nullable |
| phone | varchar(20) | nullable |
| address | text | nullable |
| country | varchar(5) | default 'MX' |
| timezone | varchar(50) | default 'America/Mexico_City' |
| locale | varchar(5) | default 'es' |
| currency | varchar(3) | default 'MXN' |
| plan | varchar(20) | default 'basic' — string, **no FK** to plans |
| status | enum('active','suspended','trial') | default 'trial' |
| last_activity_at | timestamp | nullable |
| settings | json | nullable |
| created_at / updated_at | timestamps | |
| deleted_at | softDeletes | nullable |

### `users`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | nullable → FK(`tenants`.`id`) |
| name | varchar(150) | not null |
| email | varchar | **unique** |
| phone | varchar(20) | nullable |
| email_verified_at | timestamp | nullable |
| password | varchar | not null |
| avatar | varchar | nullable |
| status | enum('active','inactive','suspended') | default 'active' |
| remember_token | varchar(100) | nullable |
| created_at / updated_at | timestamps | |
| deleted_at | softDeletes | nullable |

**Indexes**: `idx_users_tenant_status` on (`tenant_id`, `status`)

### `plans`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| name | varchar(80) | not null |
| slug | varchar(80) | **unique** |
| description | text | nullable |
| monthly_price | decimal(10,2) | not null |
| annual_price | decimal(10,2) | not null |
| currency | varchar(3) | default 'MXN' |
| max_tournaments | int | default 2 |
| max_teams | int | default 20 |
| max_players | int | default 300 |
| max_users | int | default 2 |
| max_fields | int | default 5 |
| max_referees | int | default 10 |
| storage_mb | int | default 500 |
| has_mobile_app | boolean | default false |
| has_streaming | boolean | default false |
| has_advanced_stats | boolean | default false |
| has_api_access | boolean | default false |
| has_whatsapp | boolean | default false |
| has_reports | boolean | default false |
| has_custom_domain | boolean | default false |
| support_level | enum('basic','priority','dedicated') | default 'basic' |
| sort_order | unsignedSmallInt | default 0 |
| is_active | boolean | default true |
| is_featured | boolean | default false |
| created_at / updated_at | timestamps | |

### `subscriptions`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) |
| plan_id | bigint | FK(`plans`.`id`) |
| status | enum('trial','active','past_due','suspended','cancelled') | default 'trial' |
| billing_cycle | enum('monthly','annual') | default 'monthly' |
| price_paid | decimal(10,2) | not null |
| discount_amount | decimal(10,2) | default 0 |
| discount_reason | varchar(150) | nullable |
| starts_at | date | not null |
| ends_at | date | nullable |
| trial_ends_at | date | nullable |
| next_billing_at | date | nullable |
| auto_renew | boolean | default true |
| payment_method | varchar(30) | nullable |
| external_id | varchar(100) | nullable |
| cancelled_at | timestamp | nullable |
| cancellation_reason | varchar(255) | nullable |
| created_at / updated_at | timestamps | |

**Indexes**: `idx_subscriptions_tenant_status` on (`tenant_id`, `status`)

### `usage_limits`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | **unique**, FK(`tenants`.`id`) — one row per tenant |
| active_tournaments | unsignedInt | default 0 |
| total_teams | unsignedInt | default 0 |
| total_players | unsignedInt | default 0 |
| total_users | unsignedInt | default 0 |
| storage_used_mb | unsignedInt | default 0 |
| api_requests_month | unsignedInt | default 0 |
| last_reset_at | date | nullable |
| created_at / updated_at | timestamps | |

### `torneos`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) **cascade** |
| nombre | varchar | not null |
| tipo | varchar | not null (liga, copa, relampago) |
| categoria | varchar | not null (libre, infantil, veteranos) |
| rama | varchar | not null (varonil, femenil, mixta) |
| fecha_inicio | date | not null |
| fecha_fin | date | nullable |
| reglas | text | nullable |
| estado | enum('activo','finalizado','cancelado') | default 'activo' |
| max_equipos | unsignedSmallInt | nullable (added later) |
| inscripcion_abierta | boolean | default true (added later) |
| configuracion_asistencia_delegado | boolean | default false (added later) |
| fair_play_automatico | boolean | default false (added later) |
| created_by | bigint | FK(`users`.`id`) — **no onDelete** (default restrict) |
| created_at / updated_at | timestamps | |

### `teams`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) **cascade** |
| name | varchar | not null |
| shield | varchar | nullable |
| colors | varchar | nullable |
| delegado_id | bigint | nullable → FK(`users`.`id`) **nullOnDelete** |
| phone | varchar | nullable |
| email | varchar | nullable |
| status | enum('active','suspended') | default 'active' |
| created_at / updated_at | timestamps | |
| deleted_at | softDeletes | nullable |

### `arbitros`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) **cascade** (manual syntax) |
| nombre | varchar | not null |
| telefono | varchar | nullable |
| email | varchar | nullable |
| nivel | varchar | default 'regional' |
| disponible | boolean | default true |
| pago_por_partido | decimal(10,2) | nullable |
| created_at / updated_at | timestamps | |

**Indexes**: (`tenant_id`, `nombre`)

### `players`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) **cascade** (manual syntax) |
| equipo_id | bigint | FK(`teams`.`id`) **cascade** |
| nombre | varchar | not null |
| numero | int | nullable |
| posicion | varchar | nullable |
| edad | int | nullable |
| curp | varchar | nullable, **unique** |
| foto | varchar | nullable |
| estado | varchar | default 'activo' — **no constraint** |
| created_at / updated_at | timestamps | |

**Indexes**: (`tenant_id`, `equipo_id`)

### `torneo_grupos`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) **cascade** |
| torneo_id | bigint | FK(`torneos`.`id`) **cascade** |
| nombre | varchar(50) | not null |
| orden | unsignedSmallInt | default 0 |
| created_at / updated_at | timestamps | |

**Unique**: (`torneo_id`, `nombre`)
**Index**: (`tenant_id`, `torneo_id`)

### `torneo_equipos`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) **cascade** |
| torneo_id | bigint | FK(`torneos`.`id`) **cascade** |
| team_id | bigint | FK(`teams`.`id`) **restrictOnDelete** |
| torneo_grupo_id | bigint | nullable → FK(`torneo_grupos`.`id`) **nullOnDelete** |
| seed | unsignedSmallInt | nullable |
| estado | enum('pendiente','aprobado','rechazado','retirado','descalificado') | default 'pendiente' |
| fair_play_points | decimal(5,2) | default 10.00 |
| inscrito_at | timestamp | default CURRENT_TIMESTAMP |
| aprobado_at | timestamp | nullable |
| aprobado_por | bigint | nullable → FK(`users`.`id`) **nullOnDelete** |
| rechazado_at | timestamp | nullable |
| motivo_rechazo | varchar(255) | nullable |
| retirado_at | timestamp | nullable |
| notas | text | nullable |
| metadata | json | nullable |
| created_at / updated_at | timestamps | |
| deleted_at | softDeletes | nullable |

**Unique**: (`torneo_id`, `team_id`), (`torneo_id`, `seed`)
**Indexes**: (`torneo_id`, `estado`), (`tenant_id`, `torneo_id`)

### `canchas`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) **cascade** |
| nombre | varchar | not null |
| direccion | text | nullable |
| tipo | varchar | default 'futbol-11' |
| capacidad | int | nullable, default 0 |
| latitud | decimal(10,7) | nullable |
| longitud | decimal(10,7) | nullable |
| estado | varchar | default 'activo' |
| created_at / updated_at | timestamps | |

**Indexes**: (`tenant_id`, `nombre`), (`tenant_id`, `tipo`), (`tenant_id`, `estado`)

### `disponibilidad_canchas`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) **cascade** |
| cancha_id | bigint | FK(`canchas`.`id`) **cascade** |
| dia_semana | tinyInt | not null (0=Sun..6=Sat) |
| hora_inicio | time | not null |
| hora_fin | time | not null |
| created_at / updated_at | timestamps | |

**Indexes**: (`tenant_id`, `cancha_id`), (`cancha_id`, `dia_semana`)

### `jornadas`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) **cascade** |
| torneo_id | bigint | FK(`torneos`.`id`) **cascade** |
| nombre | varchar | not null |
| numero | int | not null |
| fecha_inicio | date | not null |
| fecha_fin | date | nullable |
| estado | varchar | default 'borrador' |
| descripcion | text | nullable |
| created_at / updated_at | timestamps | |

**Unique**: (`torneo_id`, `numero`)
**Indexes**: (`tenant_id`, `torneo_id`), (`tenant_id`, `estado`)

### `partidos`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) **cascade** |
| torneo_id | bigint | FK(`torneos`.`id`) **cascade** |
| jornada_id | bigint | nullable → FK(`jornadas`.`id`) **nullOnDelete** |
| equipo_local_id | bigint | FK(`torneo_equipos`.`id`) **cascade** |
| equipo_visitante_id | bigint | FK(`torneo_equipos`.`id`) **cascade** |
| cancha_id | bigint | nullable → FK(`canchas`.`id`) **nullOnDelete** |
| arbitro_id | bigint | nullable → FK(`arbitros`.`id`) **nullOnDelete** |
| fecha | date | not null |
| hora | time | not null |
| duracion_minutos | unsignedSmallInt | default 90 |
| estado | varchar | default 'programado' |
| mitad | unsignedTinyInt | default 1 (added later) |
| goles_local | unsignedTinyInt | nullable |
| goles_visitante | unsignedTinyInt | nullable |
| created_at / updated_at | timestamps | |

**Indexes**: (`tenant_id`, `torneo_id`), (`cancha_id`, `fecha`), (`arbitro_id`, `fecha`), (`jornada_id`), (`fecha`, `estado`), (`torneo_id`, `equipo_local_id`, `equipo_visitante_id`)

### `partido_eventos`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) **cascade** |
| partido_id | bigint | FK(`partidos`.`id`) **cascade** |
| equipo_id | bigint | FK(`teams`.`id`) **cascade** |
| jugador_id | bigint | nullable → FK(`players`.`id`) **nullOnDelete** |
| jugador_relacionado_id | bigint | nullable → FK(`players`.`id`) **nullOnDelete** |
| tipo | varchar | not null (9 event types) |
| minuto | unsignedTinyInt | not null |
| comentario | text | nullable |
| created_at / updated_at | timestamps | |

**Indexes**: (`partido_id`, `tipo`), (`partido_id`, `minuto`), (`jugador_id`, `tipo`)

### `partido_asistencias`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) **cascade** |
| partido_id | bigint | FK(`partidos`.`id`) **cascade** |
| equipo_id | bigint | FK(`teams`.`id`) **cascade** |
| jugador_id | bigint | FK(`players`.`id`) **cascade** |
| asistio_primera_mitad | boolean | nullable |
| asistio_segunda_mitad | boolean | nullable |
| created_at / updated_at | timestamps | |

**Unique**: (`partido_id`, `jugador_id`)
**Index**: (`partido_id`, `equipo_id`)

### `torneo_standings`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint, PK | auto-increment |
| tenant_id | bigint | FK(`tenants`.`id`) **cascade** |
| torneo_id | bigint | FK(`torneos`.`id`) **cascade** |
| torneo_grupo_id | bigint | nullable → FK(`torneo_grupos`.`id`) **nullOnDelete** |
| torneo_equipo_id | bigint | FK(`torneo_equipos`.`id`) **cascade** |
| pj | unsignedSmallInt | default 0 |
| pg | unsignedSmallInt | default 0 |
| pe | unsignedSmallInt | default 0 |
| pp | unsignedSmallInt | default 0 |
| gf | unsignedSmallInt | default 0 |
| gc | unsignedSmallInt | default 0 |
| dg | smallInt | default 0 |
| pts | unsignedSmallInt | default 0 |
| fair_play | decimal(5,2) | default 10.00 |
| posicion_posiciones | unsignedSmallInt | nullable (added later, replaced `posicion`) |
| posicion_rendimiento | unsignedSmallInt | nullable (added later) |
| created_at / updated_at | timestamps | |

**Unique**: (`torneo_id`, `torneo_equipo_id`)
**Index**: (`torneo_id`, `torneo_grupo_id`)

### Spatie tables

**`permissions`**: id, name, guard_name, timestamps → unique(`name`, `guard_name`)

**`roles`**: id, `tenant_id` (unsignedBigInt, **no FK**), name, guard_name, timestamps → unique(`tenant_id`, `name`, `guard_name`)

**`model_has_permissions`**: `permission_id` → FK(permissions) cascade, `model_type`, `model_id`, `tenant_id` → PK(`tenant_id`, `permission_id`, `model_id`, `model_type`)

**`model_has_roles`**: `role_id` → FK(roles) cascade, `model_type`, `model_id`, `tenant_id` → PK(`tenant_id`, `role_id`, `model_id`, `model_type`)

**`role_has_permissions`**: `permission_id` → FK(permissions) cascade, `role_id` → FK(roles) cascade → PK(`permission_id`, `role_id`)

---

## Relationship Map

```
tenants
├── users (hasMany) — FK tenant_id → tenants.id (nullable)
|   ├── teams via delegado_id (FK nullable, nullOnDelete)
|   ├── torneos via created_by (FK, restrict)
|   └── torneo_equipos via aprobado_por (FK nullable, nullOnDelete)
├── subscriptions (hasMany) — FK tenant_id → tenants.id
├── usage_limits (hasOne) — FK tenant_id → tenants.id (unique)
├── torneos (hasMany) — FK tenant_id, cascade
├── teams (hasMany) — FK tenant_id, cascade
├── arbitros (hasMany) — FK tenant_id, cascade
├── players (hasMany) — FK tenant_id, cascade
├── canchas (hasMany) — FK tenant_id, cascade
└── Spatie (model_has_roles/perms via users)

plans
├── subscriptions (hasMany)
└── tenants (hasMany via string slug — NOT a real FK)

teams
├── players (hasMany via equipo_id, cascade)
├── torneo_equipos (hasMany via team_id, restrict)
├── partido_eventos (hasMany via equipo_id)
└── partido_asistencias (hasMany via equipo_id)

torneos
├── torneo_grupos (hasMany, cascade)
├── torneo_equipos (hasMany, cascade)
├── jornadas (hasMany, cascade)
├── partidos (hasMany, cascade)
└── torneo_standings (hasMany, cascade)

torneo_equipos
├── partidos as local (hasMany via equipo_local_id, cascade)
└── partidos as visitor (hasMany via equipo_visitante_id, cascade)

canchas
├── disponibilidad_canchas (hasMany, cascade)
└── partidos (hasMany, nullOnDelete)

arbitros → partidos (hasMany, nullOnDelete)

jornadas → partidos (hasMany, nullOnDelete)

players
├── partido_eventos via jugador_id (nullOnDelete)
├── partido_eventos via jugador_relacionado_id (nullOnDelete)
└── partido_asistencias via jugador_id (cascade)
```

---

## Index Summary

| Table | Index | Columns | Type |
|-------|-------|---------|------|
| tenants | PRIMARY | id | PK |
| tenants | `tenants_uuid_unique` | uuid | unique |
| tenants | `tenants_slug_unique` | slug | unique |
| tenants | `tenants_custom_domain_unique` | custom_domain | unique |
| users | PRIMARY | id | PK |
| users | `users_email_unique` | email | unique |
| users | `idx_users_tenant_status` | tenant_id, status | index |
| plans | PRIMARY | id | PK |
| plans | `plans_slug_unique` | slug | unique |
| subscriptions | PRIMARY | id | PK |
| subscriptions | `idx_subscriptions_tenant_status` | tenant_id, status | index |
| usage_limits | PRIMARY | id | PK |
| usage_limits | `usage_limits_tenant_id_unique` | tenant_id | unique FK |
| torneos | PRIMARY | id | PK |
| teams | PRIMARY | id | PK |
| arbitros | PRIMARY | id | PK |
| arbitros | — | tenant_id, nombre | index |
| players | PRIMARY | id | PK |
| players | `players_curp_unique` | curp | unique |
| players | — | tenant_id, equipo_id | index |
| torneo_grupos | PRIMARY | id | PK |
| torneo_grupos | `torneo_grupos_torneo_id_nombre_unique` | torneo_id, nombre | unique |
| torneo_grupos | — | tenant_id, torneo_id | index |
| torneo_equipos | PRIMARY | id | PK |
| torneo_equipos | `torneo_equipos_torneo_id_team_id_unique` | torneo_id, team_id | unique |
| torneo_equipos | `torneo_equipos_torneo_id_seed_unique` | torneo_id, seed | unique |
| torneo_equipos | — | torneo_id, estado | index |
| torneo_equipos | — | tenant_id, torneo_id | index |
| canchas | PRIMARY | id | PK |
| canchas | — | tenant_id, nombre | index |
| canchas | — | tenant_id, tipo | index |
| canchas | — | tenant_id, estado | index |
| disponibilidad_canchas | PRIMARY | id | PK |
| disponibilidad_canchas | — | tenant_id, cancha_id | index |
| disponibilidad_canchas | — | cancha_id, dia_semana | index |
| jornadas | PRIMARY | id | PK |
| jornadas | `jornadas_torneo_id_numero_unique` | torneo_id, numero | unique |
| jornadas | — | tenant_id, torneo_id | index |
| jornadas | — | tenant_id, estado | index |
| partidos | PRIMARY | id | PK |
| partidos | — | tenant_id, torneo_id | index |
| partidos | — | cancha_id, fecha | index |
| partidos | — | arbitro_id, fecha | index |
| partidos | — | jornada_id | index |
| partidos | — | fecha, estado | index |
| partidos | — | torneo_id, equipo_local_id, equipo_visitante_id | index |
| partido_eventos | PRIMARY | id | PK |
| partido_eventos | — | partido_id, tipo | index |
| partido_eventos | — | partido_id, minuto | index |
| partido_eventos | — | jugador_id, tipo | index |
| partido_asistencias | PRIMARY | id | PK |
| partido_asistencias | `partido_asistencias_partido_id_jugador_id_unique` | partido_id, jugador_id | unique |
| partido_asistencias | — | partido_id, equipo_id | index |
| torneo_standings | PRIMARY | id | PK |
| torneo_standings | `torneo_standings_torneo_id_torneo_equipo_id_unique` | torneo_id, torneo_equipo_id | unique |
| torneo_standings | — | torneo_id, torneo_grupo_id | index |

---

## FK On-Delete Behavior Summary

| FK | On Delete | Rationale |
|----|-----------|-----------|
| users → tenants | — (default restrict) | Cannot delete tenant with users — safe |
| subscriptions → tenants | — (default restrict) | Cannot delete tenant with subscriptions |
| usage_limits → tenants | — (default restrict) | One row per tenant, must delete explicitly |
| torneos → tenants | cascade | Tenant deletion cleans up all data |
| torneos → users (created_by) | — (default restrict) | Cannot delete user who created torneos |
| teams → tenants | cascade | |
| teams → users (delegado_id) | set null | Delegado deleted → team orphaned but preserved |
| arbitros → tenants | cascade | |
| players → tenants | cascade | |
| players → teams | cascade | Team deleted → all players deleted |
| torneo_grupos → tenants | cascade | |
| torneo_grupos → torneos | cascade | |
| torneo_equipos → tenants | cascade | |
| torneo_equipos → torneos | cascade | |
| torneo_equipos → teams | **restrict** | Prevents deleting a team registered in a tournament |
| torneo_equipos → torneo_grupos | set null | Group deleted → registration kept |
| torneo_equipos → users (aprobado_por) | set null | |
| canchas → tenants | cascade | |
| disponibilidad_canchas → tenants | cascade | |
| disponibilidad_canchas → canchas | cascade | |
| jornadas → tenants | cascade | |
| jornadas → torneos | cascade | |
| partidos → tenants | cascade | |
| partidos → torneos | cascade | |
| partidos → jornadas | set null | |
| partidos → torneo_equipos (local/visitante) | **cascade** | Deleting a registration → deletes all its matches |
| partidos → canchas | set null | |
| partidos → arbitros | set null | |
| partido_eventos → tenants | cascade | |
| partido_eventos → partidos | cascade | |
| partido_eventos → teams | cascade | |
| partido_eventos → players (jugador_id) | set null | |
| partido_eventos → players (relacionado) | set null | |
| partido_asistencias → tenants | cascade | |
| partido_asistencias → partidos | cascade | |
| partido_asistencias → teams | cascade | |
| partido_asistencias → players | **cascade** | Player deleted → attendance records deleted |
| torneo_standings → tenants | cascade | |
| torneo_standings → torneos | cascade | |
| torneo_standings → torneo_grupos | set null | |
| torneo_standings → torneo_equipos | cascade | |

---

## Model vs Migration Audit

| Model | Table | Uses BelongsToTenant? | Has tenant_id in DB? | Fillable matches migration? |
|-------|-------|----------------------|----------------------|-----------------------------|
| Tenant | tenants | **No** (correct — tenant is the context) | N/A | ✅ |
| User | users | **Yes** | Yes (nullable) | ✅ |
| Plan | plans | No (no tenant_id) | N/A | ✅ |
| Subscription | subscriptions | **No** | Yes | ✅ (but trait missing) |
| UsageLimit | usage_limits | **No** | Yes | ✅ (but trait missing) |
| Torneo | torneos | **Yes** | Yes | ✅ (incl. later-added columns) |
| Team | teams | **Yes** | Yes | ✅ |
| Arbitro | arbitros | **No** | Yes | ✅ |
| Player | players | **Yes** | Yes | ✅ |
| TorneoGrupo | torneo_grupos | **Yes** | Yes | ✅ |
| TorneoEquipo | torneo_equipos | **Yes** | Yes | ✅ (incl. metadata) |
| Cancha | canchas | **Yes** | Yes | ✅ |
| DisponibilidadCancha | disponibilidad_canchas | **Yes** | Yes | ✅ |
| Jornada | jornadas | **Yes** | Yes | ✅ |
| Partido | partidos | **Yes** | Yes | ✅ (incl. mitad) |
| PartidoEvento | partido_eventos | **Yes** | Yes | ✅ |
| PartidoAsistencia | partido_asistencias | **Yes** | Yes | ✅ |
| TorneoStanding | torneo_standings | **Yes** | Yes | ✅ (incl. posicion_*) |

---

## Identified Issues

### Critical

1. **`Arbitro` model missing `BelongsToTenant` trait** (`app/Models/Arbitro.php:9`)
   The migration has `tenant_id` with FK to tenants, but the model lacks the trait. Queries on `Arbitro` will NOT be auto-scoped, potentially leaking data across tenants. Every `Arbitro::query()` needs manual `->where('tenant_id', ...)` or the trait must be added.

2. **`Subscription` and `UsageLimit` models lack `BelongsToTenant` trait**
   Both have `tenant_id` with FK but don't use the trait. These are billing/usage tables, so this may be intentional (potentially accessed from admin context without tenant scope). However, if accessed from tenant context, they'd leak data. Inconsistent with the architecture rule.

3. **`torneos.created_by` FK has no onDelete** (defaults to restrict)
   Deleting a user who created tournaments will fail with a foreign key violation. Should specify `nullOnDelete` or `cascadeOnDelete`.

4. **`partidos.equipo_local_id` / `equipo_visitante_id` cascade on delete**
   Deleting a `torneo_equipo` record (e.g., team disqualified) will cascade and delete **all matches** for that team, including past completed matches. This is destructive — `nullOnDelete` on the match and `restrict` on the registration would be safer.

5. **`partido_asistencias.jugador_id` cascade on delete**
   Deleting a player deletes all their attendance records. Usually OK but worth noting.

### Important

6. **`tenants.plan` is a string column — no FK to `plans` table**
   The `Tenant` model has `planRelation()` using `belongsTo(Plan::class, 'plan', 'slug')`, but there is no database-level foreign key. Renaming a plan slug would silently orphan all tenants pointing to the old slug.

7. **Spatie `roles.tenant_id` has no FK constraint**
   By Spatie design, the team_foreign_key is not enforced as a real FK. This means roles could theoretically exist with `tenant_id` values that don't reference any tenant. Not a bug, but worth awareness.

8. **`arbitros` and `players` migrations use manual FK syntax** (unsignedBigInteger + separate `->foreign()` call)
   This is functionally equivalent but inconsistent with the rest of the codebase which uses `foreignId()->constrained()`. Style inconsistency only.

9. **`players.estado` is a plain varchar — no CHECK/ENUM constraint**
   The model defines three constants (`activo`, `suspendido`, `lesionado`) but the DB doesn't enforce them. Any string can be inserted.

10. **Several `estado`/`status` columns are plain strings** (canchas, jornadas, partidos, etc.)
    No CHECK constraints. Data integrity relies entirely on application validation.

### Minor

11. **Migration naming inconsistency**
    Some use `YYYY_MM_DD_HHMMSS` format (`2026_04_03_181904_create_torneos_table`), others use `YYYY_MM_DD_000001` format (`2026_04_03_000001_create_plans_table`). Works but messy.

12. **Duplicate timestamp in migration prefix**
    `0001_01_01_000003_create_cache_table.php` and `0001_01_01_000003_create_jobs_table.php` share the same numeric prefix. Works because filenames differ, but unusual.

13. **`torneo_standings.posicion_posiciones` is an awkward column name**
    Replaced the original `posicion` with `posicion_posiciones` + `posicion_rendimiento`. The name is redundant ("position positions") but functional.

14. **`users.tenant_id` is nullable**
    Necessary for registration flow (user created before tenant), but creates a window where users exist without tenant context. The `BelongsToTenant` trait's auto-scope will not apply when `TenantContext` is not set.

15. **`Subscription` model does not soft-delete** while other tenant-scoped models do. Mixed pattern.

16. **Missing index on `torneos.created_by`** — the FK column has no index, though it's not frequently queried.

17. **Missing composite index on `partidos.(torneo_id, jornada_id)`** — most match queries filter by torneo + jornada, but there's only an index on `jornada_id` alone.

---

## Recommendations

| Priority | Suggestion |
|----------|------------|
| High | Add `BelongsToTenant` trait to `Arbitro` model |
| Medium | Review `Subscription` and `UsageLimit` — add trait or document intentional omission |
| Medium | Add `->nullOnDelete()` or `->cascadeOnDelete()` to `torneos.created_by` FK |
| Medium | Review `partidos.equipo_local_id/visitante_id` cascade behavior — consider `restrictOnDelete` or `nullOnDelete` |
| Low | Add CHECK constraints for estado/status columns via migration (or accept app-level enforcement) |
| Low | Add FK from `tenants.plan` to `plans.slug` or migrate to plan_id |
| Low | Add index on `torneos.created_by` |
| Low | Add composite index on `partidos.(torneo_id, jornada_id)` |
