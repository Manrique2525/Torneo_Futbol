# AGENTS.md — Torneo de Fútbol (Laravel Multi-Tenant SaaS)

Stack: Laravel 12, Inertia.js 2, Vue 3, Tailwind CSS 3, MySQL 8 (SQLite dev/test), Spatie Permission v7 (teams)

Full architecture: `project.md` (mostly accurate but references Laravel 11 and some outdated structure).

## Development Rules

Before modifying any code, read all related models, migrations, controllers, and Vue pages/components. Reuse existing patterns; never create duplicate functionality. Never assume a table, relationship, service, or feature exists — verify everything against the actual codebase. Prefer extending existing modules over creating new ones.

**Tenant isolation**: every model with `tenant_id` must use `BelongsToTenant` trait (unless documented exception). Never bypass `TenantScope` without explicit justification. Respect Spatie Permission teams config.

**Naming & conventions**: use existing Spanish domain naming (Torneo, Partido, Jornada, Arbitro, Cancha, etc.). Follow existing route, controller, and frontend patterns. Reuse existing Vue/Inertia components. Follow Laravel 12 + Inertia 2 + Vue 3 + Tailwind 3 conventions.

**Before proposing a change**: analyze current implementation, identify all affected files (models, migrations, controllers, routes, Vue pages/components), present a plan, and verify existing functionality is not broken.

## Commands

```bash
composer setup          # Full install: deps + .env + key + migrate + npm install + build
composer dev            # Runs serve + queue + pail + vite concurrently
composer test           # Clears config then runs PHPUnit (SQLite in-memory)
npm run dev             # Vite dev server (hot reload)
npm run build           # Vite production build
./vendor/bin/pint       # Laravel Pint (no custom ruleset)
```

## Multi-Tenant Architecture

Every model with `tenant_id` **must** use `BelongsToTenant` trait (`app/Models/Traits/BelongsToTenant.php`). It:
- **Auto-scopes** all queries via `TenantScope` global scope (`WHERE tenant_id = ?`)
- **Auto-fills** `tenant_id` on create from `TenantContext` singleton

`TenantContext` singleton is set by `ResolveTenant` middleware (aliased as `'tenant'`). It validates tenant exists & is not suspended, sets context and calls `setPermissionsTeamId()`.

**Bypass**: `->withoutGlobalScope(TenantScope::class)` or `->withoutTenantScope()` for cross-tenant queries (registration, super-admin).

## Spatie Permission with Teams

- `config/permission.php`: `'teams' => true`, `team_foreign_key => 'tenant_id'`
- `setPermissionsTeamId($tenantId)` must be called before permission/role checks
- Called in **two places**: `HandleInertiaRequests::share()` and `ResolveTenant::handle()`
- `Gate::before` in `AppServiceProvider` grants super_admin bypass for all permissions
  - Uses `$user->roles()->where('name', RoleEnum::SUPER_ADMIN)->exists()` — **not** `$user->hasRole()` (which requires Spatie team context)
- Super admin has **no** permissions in DB — the Gate bypass is the only mechanism
- **Roles are global** (`tenant_id = null`), NOT per-tenant copies. `RolePermissionService::setupRoles()` creates them once globally. Each tenant sees the same role templates.

## Middleware Registration (bootstrap/app.php)

- `HandleInertiaRequests::class` appended to `web` middleware group (shares `auth.user.roles` + `auth.user.permissions` to Vue)
- `'tenant'` alias → `ResolveTenant::class`
- **No `SetPermissionsTeam` middleware exists** — that functionality is in `HandleInertiaRequests::share()` and `ResolveTenant::handle()`

Actual execution order for tenant routes:
```
web group (StartSession → HandleInertiaRequests) → auth → verified → tenant(ResolveTenant) → Controller
```

## Routes

Module routes live in dedicated files under `routes/`. Some are loaded inside tenant middleware, some outside.

**Inside `['auth', 'verified', 'tenant']`** (web.php line 35):
- `roles.php`, `plans.php` (via `Route::group([], base_path(...))`)
- `torneos.php`, `teams.php`, `arbitros.php`, `players.php`, `canchas.php`, `jornadas.php`, `partidos.php`, `partidos-en-vivo.php`, `standings.php` (via `require` at bottom of web.php)

**Outside tenant group** (web.php line 47): `permissions` resource (auth only)

**Auth-only routes** (auth.php): Breeze auth scaffold (register, login, password, verify)

**API routes**: `routes/api.php`

Route naming uses Spanish convention for domain modules: `torneos`, `arbitros`, `canchas`, `jornadas`, `partidos`.

## Models

18 models in `app/Models/`. Domain models use Spanish names: `Torneo`, `Arbitro`, `Jornada`, `Cancha`, `Partido`, `PartidoEvento`, `PartidoAsistencia`, `TorneoEquipo`, `TorneoGrupo`, `TorneoStanding`, `DisponibilidadCancha`, `Team`, `Player`, `Tenant`, `User`, `Plan`, `Subscription`, `UsageLimit`.

`User` model: `HasRoles` (Spatie), `BelongsToTenant`, `SoftDeletes`. Status constants: `active`, `inactive`, `suspended`.

## Frontend

- `@/` alias → `resources/js/` (jsconfig.json)
- Inertia pages auto-resolved from `resources/js/Pages/**/*.vue` via `import.meta.glob`
- Ziggy provides route helpers in Vue templates
- Tailwind dark mode: `class` strategy
- Custom colors: `primary` (#10b77f), `primary-dark`, `background-light`, `background-dark`, `surface-light`, `surface-dark`
- `useCan()` composable in `resources/js/Shared/Composables/useCan.js` — includes `console.log` debug statements
- Additional JS deps: `jquery`, `lodash`, `select2`, `vue-select`

## Testing

- PHPUnit (not Pest), SQLite in-memory (`:memory:`)
- `composer test` = `php artisan config:clear` + `php artisan test`
- `phpunit.xml` sets `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `QUEUE_CONNECTION=sync`
- Test namespace: `Tests\`, 9 test files across `tests/Feature/Auth/`, `tests/Feature/`, `tests/Unit/`
- Test files use `RefreshDatabase` trait
- No custom TestCase methods — `Tests\TestCase` is empty

## Session History

### 2026-06-15 — Módulo Estadísticas + Modal por equipo

**Archivos creados:**
- `app/Services/EstadisticasService.php` — servicio con `globales()` (torneo completo) y `porEquipo()` (filtrado por torneo_equipo_id). Agrega goleo, asistencias, tarjetas amarillas, tarjetas rojas, faltas desde `partido_eventos`.
- `app/Http/Controllers/EstadisticasController.php` — `index()` renderiza página con tabs, `equipo()` devuelve JSON para modal.
- `routes/estadisticas.php` — dos rutas dentro de `['auth', 'verified', 'tenant']`.
- `resources/js/Components/TableEstadisticas.vue` — tabla reutilizable con ranking, colores por posición, responsive.
- `resources/js/Pages/Torneos/Estadisticas.vue` — página con tabs (Goleo, Asistencias, T. Amarillas, T. Rojas, Faltas).

**Archivos modificados:**
- `routes/web.php` — agregado `require __DIR__.'/estadisticas.php'`.
- `app/Http/Controllers/StandingsController.php` — agregado `torneo_equipo_id` al map de standings.
- `resources/js/Pages/Torneos/Posiciones.vue` — botón "Estadísticas" con permiso `stats.view`, pasa `torneo-id` a StandingsTable.
- `resources/js/Components/StandingsTable.vue` — filas clickeables abren modal con tabs por equipo (fetch a `estadisticas.equipo`).

**Bugs corregidos:**
1. `EstadisticasService::porEquipo()` — closure faltaba `use ($torneoEquipoId)` → PHP Notice devenía en 500. Además `equipo_local_id`/`equipo_visitante_id` son `torneo_equipos.id`, no `teams.id`; ahora se usa `$torneoEquipoId` directo.
2. El mensaje de error en el modal ahora muestra el status HTTP real.

**Notas:**
- `stats.view` ya existe en `PermissionEnum` y está asignado a admin, super_admin, owner.
- `estadisticas.equipo` recibe `{torneo}` (model binding) y `{torneoEquipo}` (int — torneo_equipos.id).
- Build falla pre-existentemente por `lodash/debounce` no instalado (Permissions/Index.vue). No afecta este módulo.

## Code Style

- 4-space indent, LF line endings (`.editorconfig`)
- Laravel Pint for formatting — no custom ruleset
- PHP 8.2+ required
