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
- `torneos.php`, `teams.php`, `arbitros.php`, `players.php`, `canchas.php`, `jornadas.php`, `partidos.php`, `partidos-en-vivo.php`, `standings.php`, `calendario.php` (via `require` at bottom of web.php)

**Outside tenant group** (web.php line 47): `permissions` resource (auth only)

**Auth-only routes** (auth.php): Breeze auth scaffold (register, login, password, verify)

**API routes**: `routes/api.php`

Route naming uses Spanish convention for domain modules: `torneos`, `arbitros`, `canchas`, `jornadas`, `partidos`.

## Models

18 models in `app/Models/`. Domain models use Spanish names: `Torneo`, `Arbitro`, `Jornada`, `Cancha`, `Partido`, `PartidoEvento`, `PartidoAsistencia`, `PartidoSustitucion`, `TorneoEquipo`, `TorneoGrupo`, `TorneoStanding`, `DisponibilidadCancha`, `Team`, `Player`, `Tenant`, `User`, `Plan`, `Subscription`, `UsageLimit`.

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

### 2026-06-17 — Módulo Calendario Automático de Partidos

**Archivos creados:**
- `database/migrations/2026_06_17_000001_add_calendario_config_to_torneos.php` — agrega campos: `ida_y_vuelta`, `formato_relampago`, `tiene_playoff`, `playoff_equipos`, `playoff_ida_vuelta`.
- `database/migrations/2026_06_17_000002_add_fase_to_partidos.php` — agrega campos: `fase`, `es_vuelta`, `llave_bracket`, `orden_bracket`.
- `database/migrations/2026_06_17_000003_create_partido_sustituciones_table.php` — tabla para registrar sustituciones de equipos.
- `app/Models/PartidoSustitucion.php` — modelo con trait `BelongsToTenant`, relaciones a partido, equipos, usuario.
- `app/Services/RoundRobinGenerator.php` — algoritmo Round-Robin para generar enfrentamientos todos vs todos, con soporte para ida y vuelta.
- `app/Services/BracketGenerator.php` — genera llaves de eliminación directa con cruzamiento estándar (1° vs N°, 2° vs N-1°, etc.).
- `app/Services/CalendarioService.php` — servicio principal que orquesta la generación de calendarios (preview, confirmar, eliminar).
- `app/Http/Controllers/CalendarioController.php` — controlador con métodos: show, preview, store, update, destroy, sustituir.
- `app/Http/Requests/StoreSustitucionRequest.php` — validación para sustitución de equipos.
- `routes/calendario.php` — rutas del módulo calendario dentro de middleware `['auth', 'verified', 'tenant']`.
- `resources/js/Pages/Calendario/Show.vue` — página principal del calendario con preview modal, tabs fase regular/playoff, modal de sustitución.
- `tests/Unit/RoundRobinGeneratorTest.php` — 8 tests unitarios para el generador Round-Robin.
- `tests/Unit/BracketGeneratorTest.php` — 11 tests unitarios para el generador de brackets.

**Archivos modificados:**
- `app/Models/Torneo.php` — nuevos campos en `$fillable` y `$casts`, helpers: `esLiga()`, `esCopa()`, `esRelampago()`, `tieneIdaYVuelta()`, `tienePlayoff()`, `esPotenciaDe2()`.
- `app/Models/Partido.php` — nuevos campos en `$fillable` y `$casts`, relación `sustituciones()`.
- `app/Enums/PermissionEnum.php` — agregado `CALENDAR_MANAGE = 'calendar.manage'`.
- `app/Enums/RoleEnum.php` — `CALENDAR_MANAGE` asignado a roles admin (vía `PermissionEnum::all()`) y referee.
- `app/Http/Controllers/TorneoController.php` — validación de nuevos campos en store/update.
- `app/Services/MatchSchedulingService.php` — nuevo método `assertSinRepeticionExcesiva()` que valida que no se repitan partidos más de lo permitido según `ida_y_vuelta`.
- `config/constants.php` — agregados: `fases_partido`, `formatos_relampago`, `motivos_sustitucion`, `tipos_resolucion_sustitucion`.
- `routes/web.php` — agregado `require __DIR__.'/calendario.php'`.
- `resources/js/Pages/Torneos/Create.vue` — sección "Formato del Torneo" con toggles y selects condicionales según tipo.
- `resources/js/Pages/Torneos/Edit.vue` — misma sección de formato.
- `resources/js/Pages/Torneos/Index.vue` — botón "Calendario" en cada torneo.

**Lógica implementada:**
- **Liga/Copa**: Round-Robin todos vs todos, configurable ida y vuelta (2 partidos por par), playoff opcional con top N (potencia de 2).
- **Relámpago**: Admin elige entre fase de grupos o eliminación directa. Sin ida y vuelta.
- **Playoff**: Cruzamiento estándar (1° vs N°, 2° vs N-1°), configurable eliminación directa o ida y vuelta por llave.
- **Sustitución de equipos**: Si un equipo no asiste, otro puede tomar su lugar. Admin elige: reprogramar partido o doble jornada.
- **Validación de repetición**: Sin ida y vuelta = 1 enfrentamiento máximo por par. Con ida y vuelta = 2 enfrentamientos máximo. Excepción: partidos de playoff.

**Notas:**
- Tests unitarios pasan (19 tests, 80 assertions). Tests de Feature fallan por problema pre-existente (driver SQLite no instalado).
- Build de frontend exitoso.
- Laravel Pint ejecutado en todos los archivos PHP nuevos/modificados.

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

### 2026-06-22 — Restricciones de rol Delegate (dueño de equipo)

**Contexto:** Se implementaron restricciones permisológicas para el rol `delegate` en los módulos de Equipos, Jugadores y Partidos.

**Archivos modificados:**

- `app/Http/Controllers/TeamController.php` — en `update()`, si el usuario es delegate y `$team->delegado_id !== auth()->id()`, aborta con 403.
- `app/Http/Controllers/PlayerController.php`:
  - `index()`: si es delegate y no hay filtro `equipo_id`, muestra solo jugadores de sus equipos.
  - `create()`: si es delegate, solo muestra sus equipos en el selector.
  - `store()`: si es delegate, valida que `equipo_id` pertenezca a sus equipos.
  - `edit()`/`update()`: si es delegate, verifica que el jugador pertenezca a uno de sus equipos.
  - `destroy()`: si es delegate, aborta con 403.
- `resources/js/Pages/Teams/Index.vue` — botones "Nuevo Equipo", "Editar" y "Eliminar" condicionados por rol delegate y `delegado_id`.
- `resources/js/Pages/Players/Index.vue` — botones "Nuevo Jugador", "Editar" y "Eliminar" condicionados por `esDelegate` y `misEquiposIds`.
- `resources/js/Pages/Partidos/Index.vue` — botones "Nuevo Partido", "Registrar en vivo", "Editar" y "Eliminar" ocultos para delegate.

**Comportamiento por rol:**

| Módulo | Delegate |
|--------|----------|
| **Equipos** | Ve todos, su equipo resaltado en verde, solo lectura (sin editar/eliminar) |
| **Jugadores** | Por defecto ve solo los suyos; si filtra otro equipo, solo lectura (sin editar/eliminar) |
| **Partidos** | Ve todos, sin botones de acción (nuevo, editar, eliminar, en vivo) |

**Nota:** La rama de estos cambios es `feature/roles-delegate-manager`.

### 2026-06-22 — Selector de delegado: solo delegates sin equipo + email único en equipos

**Contexto:** Al crear/editar un equipo, el selector de responsable (delegado) ahora solo muestra usuarios con rol `delegate` que no tengan un equipo asignado. Además se agregó validación de email único.

**Archivos modificados:**

- `app/Http/Controllers/TeamController.php`:
  - `create()`: filtro `->role('delegate')->whereDoesntHave('equiposComoDelegado')`.
  - `edit()`: filtro similar pero incluye al delegado actual del equipo.
  - `store()`: `email` con regla `'nullable|email|unique:teams,email'`.
  - `update()`: `email` con regla `'nullable|email|unique:teams,email,'.$team->id`.
- `database/migrations/2026_06_22_163817_add_unique_email_to_teams.php` — agrega `unique` a la columna `email` en `teams`.
- `resources/js/Pages/Teams/Create.vue` — hint "Debe ser único por equipo" bajo el campo email.
- `resources/js/Pages/Teams/Edit.vue` — mismo hint.

### 2026-06-22 — Restricciones delegate en Torneos + Navbar dinámico + CURP único

**Archivos modificados:**

- `resources/js/Pages/Torneos/Index.vue` — botones "Nuevo Torneo", "Equipos inscritos", "Editar" y "Eliminar" ocultos para delegate.
- `resources/js/Layouts/AuthenticatedLayout.vue` — el texto "Administrador" hardcodeado ahora muestra el rol real del usuario (`userRole` computed), con mapa de roles en español.

**Nota:** La validación `unique` de CURP en jugadores ya existía tanto en la migración (`$table->string('curp')->nullable()->unique()`) como en las reglas de validación de `PlayerController@store` y `@update`.

### 2026-06-22 — PDF de tabla de posiciones

**Archivos modificados:**
- `resources/js/Pages/Torneos/Posiciones.vue` — agregado botón "PDF" y función `descargarPDF()` que genera un PDF con `jspdf` + `jspdf-autotable`.

**Diseño del PDF:**
- Encabezado con fondo verde primary (`#10b77f`) con nombre del torneo y tipo
- Fecha de generación
- Tabla estilizada con columnas: #, Equipo, PJ, PG, PE, PP, GF, GC, DG, FP, Pts
- Filas alternadas con fondo verde claro
- Leyenda de siglas al final
- Nombre del archivo: `posiciones-{nombre-torneo}.pdf`

### 2026-06-22 — Fix PDF posiciones + PDF estadísticas

**Contexto:** El botón PDF en Posiciones no funcionaba porque `jspdf-autotable` v5 en Vite/ESM no encuentra `window.jsPDF` para aplicar el plugin como método (`doc.autoTable()`).

**Solución:** Cambiar de `import 'jspdf-autotable'` (side-effect) a `import { autoTable } from 'jspdf-autotable'` y llamar `autoTable(doc, {...})` en vez de `doc.autoTable({...})`.

**Archivos modificados:**
- `resources/js/Pages/Torneos/Posiciones.vue` — fix import y llamada a autoTable.
- `resources/js/Pages/Torneos/Estadisticas.vue` — agregado botón PDF y función `descargarPDF()` que descarga PDF del tab activo (Goleo, Asistencias, T. Amarillas, T. Rojas, Faltas). Nombre de archivo: `estadisticas-{tab}-{torneo}.pdf`.
- `resources/js/Components/StandingsTable.vue` — agregado botón PDF en modal de estadísticas por equipo, función `descargarPDFModal()` que descarga PDF del tab activo del equipo seleccionado. Nombre de archivo: `estadisticas-{tab}-{equipo}.pdf`.

**Diseño del PDF (estadísticas):**
- Portrait, mismo estilo que Posiciones
- Columnas: #, Jugador, Equipo, Valor (label dinámico según tab)
- Filas alternadas
- Fecha de generación

### 2026-06-22 — Restricciones delegate en Canchas y Jornadas

**Archivos modificados:**
- `resources/js/Pages/Canchas/Index.vue` — botones "Nueva Cancha", "Editar" y "Eliminar" ocultos para delegate (`v-if="!hasRole('delegate')"`).
- `resources/js/Pages/Jornadas/Index.vue` — botones "Nueva Jornada", "Editar" y "Eliminar" ocultos para delegate (mismo patrón).

## Code Style

- 4-space indent, LF line endings (`.editorconfig`)
- Laravel Pint for formatting — no custom ruleset
- PHP 8.2+ required
