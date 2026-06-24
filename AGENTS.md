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

### 2026-06-22 — Leaflet/OpenStreetMap Picker en formulario de Canchas

**Contexto:** Se migró de Google Maps a **Leaflet + OpenStreetMap** (100% gratuito, sin API key, sin facturación). Mapa interactivo con pin arrastrable, búsqueda por lugares vía Nominatim y reverse geocoding.

**Archivos modificados:**
- `resources/js/Components/GoogleMapPicker.vue` — reescrito completamente con Leaflet. Incluye:
  - Search box con autocompletado vía Nominatim API (gratuito, sin key)
  - Mapa OpenStreetMap con tiles gratuitos
  - Clic en mapa → coloca pin → emite lat/lng
  - Drag del pin → actualiza lat/lng
  - Reverse geocoding vía Nominatim → emite dirección
  - Sin dependencia de API key ni facturación
- `resources/js/Pages/Canchas/Create.vue` — inputs de latitud/longitud reemplazados por `<GoogleMapPicker>`. Lat/lng ahora son campos de solo lectura. Dirección se auto-completa si está vacía.
- `resources/js/Pages/Canchas/Edit.vue` — mismo cambio.
- `resources/css/app.css` — `.pac-container` eliminado, agregado `.leaflet-container` override.
- `package.json` — eliminado `@googlemaps/js-api-loader`, agregado `leaflet`.

**Nota:** Ya no es necesario `VITE_GOOGLE_MAPS_API_KEY` en `.env`. Puedes eliminar esa línea.

### 2026-06-22 — Restricciones delegate en Canchas y Jornadas

**Archivos modificados:**
- `resources/js/Pages/Canchas/Index.vue` — botones "Nueva Cancha", "Editar" y "Eliminar" ocultos para delegate (`v-if="!hasRole('delegate')"`).
- `resources/js/Pages/Jornadas/Index.vue` — botones "Nueva Jornada", "Editar" y "Eliminar" ocultos para delegate (mismo patrón).

### 2026-06-22 — Correo de inscripción al aprobar equipo en torneo

**Contexto:** Al aprobar la inscripción de un equipo en un torneo, se envía un correo al email registrado del equipo notificando que fue dado de alta. Se usa **queue** para no ralentizar la respuesta HTTP.

**Archivos creados:**
- `app/Mail/EquipoInscritoTorneo.php` — Mailable con `Queueable`, subject dinámico con nombre del equipo y torneo.
- `resources/views/emails/equipo-inscrito.blade.php` — Template HTML con diseño deportivo en línea con el sistema (verde primary `#10b77f`, fuente Bebas Neue/Barlow, tabla de resumen con equipo/torneo/categoría/seed).

**Archivos modificados:**
- `app/Services/TorneoInscripcionService.php` — agregado `Mail::to(...)->queue(...)` después de la transacción en `inscribir()` (cuando auto-aprobado) y `aprobar()`. Nuevo método privado `enviarCorreoInscripcion()` que verifica estado `aprobado` y email no vacío.
- `.env` — default mailer cambiado a `resend`, sección Mailtrap comentada para desarrollo local.
- `.env.example` — agregado `RESEND_API_KEY` y `MAIL_FROM_ADDRESS` actualizado.

**Proveedor recomendado:** **Resend** (3000 emails/mes gratis, ya preconfigurado en Laravel 12 con `config/mail.php`). Requiere:
  1. Crear cuenta en https://resend.com
  2. Verificar dominio
  3. Agregar `RESEND_API_KEY=re_xxx` en `.env`

**Flujo del correo:**
1. Admin inscribe un equipo → `TorneoInscripcionService@inscribir()` o `@aprobar()`
2. Si el estado resultante es `aprobado` y el equipo tiene email → `Mail::to($email)->queue(...)`
3. El email se encola y se envía asíncronamente (requiere `php artisan queue:work`)

**Notas:**
- Para desarrollo local, descomentar las líneas de Mailtrap en `.env` y comentar las de Resend.
- Para producción, configurar `RESEND_API_KEY` y correr el worker de colas.
- Laravel Pint ejecutado en todos los archivos PHP nuevos/modificados.

### 2026-06-22 — Correo de creación de equipo + correo de inscripción a torneo

**Contexto:** Se agregaron dos notificaciones por correo: (1) al crear un equipo, se notifica al email registrado; (2) al aprobar inscripción en torneo, se notifica al equipo (ya implementado arriba).

**Archivos creados:**
- `app/Mail/EquipoCreado.php` — Mailable con `Queueable`, subject dinámico con nombre del equipo.
- `resources/views/emails/equipo-creado.blade.php` — Template HTML con diseño deportivo (verde primary `#10b77f`, Bebas Neue/Barlow, resumen de datos del equipo).

**Archivos modificados:**
- `app/Http/Controllers/TeamController.php` — agregados imports de `Mail` y `EquipoCreado`. En `store()`, después de crear el equipo, hace `$team->load('delegado')` y `Mail::to($team->email)->queue(...)` si el equipo tiene email.

### 2026-06-22 — Correo al registrar jugador en equipo

**Contexto:** Al dar de alta un jugador en un equipo, se envía un correo al contacto del equipo (email del equipo o, si no tiene, email del delegado) notificando que el jugador fue registrado.

**Archivos creados:**
- `app/Mail/JugadorRegistrado.php` — Mailable con `Queueable`, subject: "{Jugador} ha sido registrado en {Equipo}".
- `resources/views/emails/jugador-registrado.blade.php` — Template HTML con diseño deportivo (verde primary `#10b77f`, Bebas Neue/Barlow), resumen con nombre del jugador, equipo, número y posición.

**Archivos modificados:**
- `app/Http/Controllers/PlayerController.php` — agregados imports de `Mail` y `JugadorRegistrado`. En `store()`, después de crear el jugador, carga `equipo.delegado` y envía el correo. Nuevo método privado `enviarCorreoJugador()` que prioriza `equipo->email`, fallback a `delegado->email`.

**Flujo de correos actual:**
1. **Crear equipo** (`TeamController@store`) → `EquipoCreado` → notifica al email del equipo
2. **Inscribir en torneo** (`TorneoInscripcionService@inscribir`, si auto-aprobado) → `EquipoInscritoTorneo`
3. **Aprobar inscripción** (`TorneoInscripcionService@aprobar`) → `EquipoInscritoTorneo`

### 2026-06-23 — Módulo Pagos de Inscripción a Torneos

**Contexto:** Se implementó un módulo de pagos para inscripciones a torneos. Los administradores pueden configurar un precio de inscripción por torneo, y los delegados (dueños de equipo) pueden registrar pagos por transferencia (subiendo comprobante) o en efectivo. Los administradores confirman o rechazan los pagos.

**Archivos creados:**
- `database/migrations/2026_06_22_163818_add_precio_inscripcion_to_torneos.php` — agrega columnas: `precio_inscripcion` (decimal 10,2), `moneda` (varchar 3, default MXN), `pago_requerido` (boolean).
- `database/migrations/2026_06_22_163819_create_inscripcion_pagos_table.php` — tabla de pagos con: `tenant_id`, `torneo_equipo_id`, `torneo_id`, `team_id`, `monto`, `moneda`, `metodo_pago` (efectivo/transferencia), `comprobante_path`, `comprobante_original`, `referencia`, `estado` (pendiente/confirmado/rechazado), `confirmado_por`, `confirmado_at`, `notas`, soft deletes.
- `app/Models/InscripcionPago.php` — modelo con `BelongsToTenant`, relaciones a `TorneoEquipo`, `Torneo`, `Team`, `confirmadoPor` (User). Constantes `METODO_EFECTIVO`, `METODO_TRANSFERENCIA`, `ESTADO_PENDIENTE`, `ESTADO_CONFIRMADO`, `ESTADO_RECHAZADO`.
- `app/Services/InscripcionPagoService.php` — métodos: `pagosDelTorneo()` (filtra por delegate), `registrarTransferencia()`, `registrarEfectivo()`, `confirmarPago()`, `rechazarPago()`, `assertNoPagoPendiente()`.
- `app/Http/Controllers/InscripcionPagoController.php` — controlador con métodos: `index()`, `store()`, `confirmar()`, `rechazar()`. Filtra inscripciones por rol delegate. Store exige rol delegate + permisos.
- `app/Http/Requests/StoreInscripcionPagoRequest.php` — validación con reglas para comprobante (required_if transferencia, max 5MB, jpg/jpeg/png/pdf), referencia, notas.
- `routes/pagos.php` — rutas: `pagos.index`, `pagos.store`, `pagos.confirmar`, `pagos.rechazar`.
- `resources/js/Pages/Pagos/Index.vue` — página con tabla de inscripciones/pagos, modal de registro de pago (transferencia con upload o efectivo), botones confirmar/rechazar con SweetAlert2, banner flash messages.

**Archivos modificados:**
- `app/Models/Torneo.php` — relación `pagos()` HasMany via `torneo_id`. Nuevos campos en `$fillable` y `$casts`.
- `app/Models/TorneoEquipo.php` — relación `pagos()` HasMany via `torneo_equipo_id`.
- `app/Models/Team.php` — relación `pagos()` HasMany via `team_id`.
- `app/Http/Controllers/TorneoController.php` — validación de `precio_inscripcion` y `pago_requerido` en store/update.
- `app/Http/Controllers/InscripcionPagoController.php` — filtro por delegate en `index()`, guard de rol delegate en `store()`, comprobante_url en respuesta.
- `config/constants.php` — agregados `metodos_pago` y `estados_pago`.
- `routes/web.php` — agregado `require __DIR__.'/pagos.php'`.
- `resources/js/Pages/Torneos/Index.vue` — botón "Pagos" por torneo (ícono ámbar), gateado por `can('payments.view')`.
- `resources/js/Pages/Torneos/Create.vue` — sección "Pago de Inscripción" con toggle pago_requerido + input precio_inscripcion.
- `resources/js/Pages/Torneos/Edit.vue` — misma sección de pago.

**Comportamiento por rol:**

| Rol | Ve en tabla | Puede hacer |
|-----|-------------|-------------|
| **Admin/Manager** | Todos los equipos y sus pagos | Confirmar efectivo, ver/descargar comprobante, confirmar o rechazar pagos |
| **Delegate** | Solo su(s) equipo(s) | Pagar (transferencia con comprobante o marcar efectivo) |

**Notas:**
- Admin tiene `PermissionEnum::all()` que incluye `upload_receipt`, por eso el frontend usa `upload_receipt && !create_payments` para identificar delegates.
- Backend de store() exige `hasRole('delegate')` adicional al permiso.
- Pagos usa `Storage::url()` para servir comprobantes (requiere `php artisan storage:link` ya ejecutado).
- Build exitoso. Pendiente de pruebas funcionales en navegador.

### 2026-06-23 — Baja automática por impago

**Contexto:** Se implementó un sistema de baja automática para equipos que no pagan la inscripción después de X jornadas. Configurable por torneo, con notificación por correo.

**Archivos creados:**
- `database/migrations/2026_06_23_000001_add_baja_por_impago_to_torneos.php` — agrega `baja_por_impago_automatica` (boolean) y `max_jornadas_sin_pago` (integer, nullable) a `torneos`.
- `database/migrations/2026_06_23_000002_add_baja_por_impago_to_torneo_equipos_estado.php` — agrega estado `baja_por_impago` al ENUM de `torneo_equipos.estado`.
- `app/Console/Commands/BajaPorImpago.php` — comando Artisan `torneos:baja-por-impago` que recorre torneos activos con la configuración activa, detecta equipos aprobados sin pago confirmado que hayan superado el máximo de jornadas, los da de baja y notifica por correo.
- `app/Mail/EquipoBajaImpago.php` — Mailable con `Queueable`, notifica al equipo que fue dado de baja por impago.
- `resources/views/emails/equipo-baja-impago.blade.php` — Template HTML con diseño rojo/deportivo, resumen de datos y motivo.

**Archivos modificados:**
- `app/Enums/TorneoEquipoEstadoEnum.php` — agregado `case BAJA_POR_IMPAGO = 'baja_por_impago'`.
- `app/Models/Torneo.php` — `baja_por_impago_automatica` y `max_jornadas_sin_pago` en `$fillable` y `$casts`.
- `app/Http/Controllers/TorneoController.php` — validación de `baja_por_impago_automatica` (boolean) y `max_jornadas_sin_pago` (integer 1-99) en store/update. En update, `boolean()` explícito.
- `config/constants.php` — agregado `baja_por_impago` a `estados_inscripcion`.
- `routes/console.php` — agregado `Schedule::command('torneos:baja-por-impago')->dailyAt('03:00')`.
- `resources/js/Pages/Torneos/Create.vue` — sección "Baja por Impago" (toggle + input jornadas), visible solo si `pago_requerido` está activo.
- `resources/js/Pages/Torneos/Edit.vue` — misma sección.

**Comportamiento:**
- Solo visible si `pago_requerido = true`.
- El comando se ejecuta diariamente a las 03:00 AM.
- Compara jornadas totales del torneo vs `max_jornadas_sin_pago`.
- Si un equipo no tiene ningún pago confirmado y las jornadas transcurridas >= máximo → cambia estado a `baja_por_impago` y envía correo.
- `baja_por_impago` NO ocupa cupo en el torneo (no está en `cupoOcupante()`).

### 2026-06-23 — Badge de baja por impago en módulo de equipos (admin/manager + delegate)

**Contexto:** En el módulo de Equipos (`Teams/Index.vue`), admin y manager ven el badge rojo "Baja por impago" y el fondo rojo en cualquier equipo dado de baja por impago. El delegate solo ve estos indicadores en su propio equipo.

**Archivos modificados:**

- `app/Http/Controllers/TeamController.php` — en `index()`, agregado `withCount` para contar inscripciones con estado `baja_por_impago` por equipo (`inscripciones_baja_por_impago_count`).
- `resources/js/Pages/Teams/Index.vue`:
  - Nueva función `esPropietario(team)` para determinar si el usuario es dueño del equipo.
  - El color del renglón cambia según rol: admin/manager ven rojo en cualquier equipo con baja; delegate ve rojo solo en su equipo (verde si está normal).
  - Badge "Baja por impago": visible para admin/manager en cualquier equipo afectado; para delegate solo en su equipo.

**Comportamiento:**

| Rol | Equipo | Fondo | Badge |
|-----|--------|-------|-------|
| Admin/Manager | Cualquiera con baja | Rojo | "Baja por impago" |
| Admin/Manager | Sin baja | Sin resaltado | No |
| Delegate | Su equipo con baja | Rojo | "Baja por impago" |
| Delegate | Su equipo normal | Verde | No |
| Delegate | Otro equipo | Sin resaltado | No |

**Nota:** El `MatchSchedulingService.php` tuvo un fix de parseo doble de Carbon (fecha + hora) que estaba causando error al concatenar directamente un objeto Carbon en string.

### 2026-06-23 — Tipo de gestión de torneo: auto (Calendario) vs manual

**Contexto:** Se agregó el campo `tipo_gestion` a `torneos` para distinguir entre torneos gestionados automáticamente vía Calendario (`auto`) y torneos con gestión manual tradicional (`manual`). Todos los torneos nuevos se crean como `auto`. Los existentes se migran como `manual` para no romper la compatibilidad.

**Archivos creados:**

- `database/migrations/2026_06_23_000003_add_tipo_gestion_to_torneos.php` — agrega columna `tipo_gestion` (varchar 10, nullable), setea `manual` a todos los torneos existentes.

**Archivos modificados:**

- `app/Models/Torneo.php` — `tipo_gestion` en `$fillable` y `$casts`. Nuevo helper `esAuto(): bool`.
- `config/constants.php` — agregado `tipos_gestion` con `auto` y `manual`.
- `app/Http/Controllers/TorneoController.php` — en `store()`, se setea `tipo_gestion = 'auto'` en todos los torneos nuevos.
- `app/Http/Controllers/PartidoController.php`:
  - `create()`: filtra torneos, solo muestra los manuales en el selector.
  - `store()`: aborta 403 si el torneo es auto.
  - `index()`: pasa `tipo_gestion` en la lista de torneos.
  - `edit()`: permite editar partidos de torneos auto (para editar calendario).
- `app/Http/Controllers/JornadaController.php`:
  - `create()`: pasa `tipo_gestion` en lista de torneos.
  - `store()`: aborta 403 si el torneo es auto.
  - `edit()` / `update()` / `destroy()`: aborta 403 si la jornada pertenece a un torneo auto.
- `resources/js/Pages/Partidos/Index.vue`:
  - Nuevo computado `torneoSeleccionadoEsAuto` basado en filtro activo.
  - Botón "Nuevo Partido" oculto cuando el torneo seleccionado es auto.
- `resources/js/Layouts/AuthenticatedLayout.vue` — enlace de Jornadas eliminado del sidebar.

**Comportamiento:**

| Tipo | Sidebar Jornadas | Nuevo Partido | Nuevo Partido En Vivo | Nueva Jornada | Editar/Eliminar Partido |
|------|-----------------|---------------|----------------------|---------------|------------------------|
| **auto** | Oculto | Bloqueado (UI + backend) | Bloqueado (backend) | Bloqueado (backend) | Permitido (editar calendario) |
| **manual** | Visible | Normal (con permisos) | Normal | Normal | Normal |

### 2026-06-23 — Delegate: partidos resaltados en verde, filtro por jornada, selector solo sus torneos

**Contexto:** Mejoras en el módulo de Partidos para la experiencia del delegado (dueño de equipo).

**Archivos modificados:**

- `app/Http/Controllers/PartidoController.php`:
  - `index()`: carga `delegado_id` en `equipoLocal.equipo` y `equipoVisitante.equipo` para identificar partidos del delegado. Filtra `$torneos` para delegate: solo aparecen torneos donde tiene al menos un equipo aprobado. Carga `$jornadas` del torneo seleccionado para el filtro de jornada. Agrega filtro por `jornada_id` en la query de partidos. Pasa `jornadas` prop a la vista.
  - Nuevo import: `use App\Models\Team;`.
- `resources/js/Pages/Partidos/Index.vue`:
  - **Partidos en verde**: función `esPartidoDelDelegate()` que compara `delegado_id` del equipo local/visitante con el usuario actual. El `tr` del partido se resalta en verde (`bg-primary/5 ring-2 ring-primary/30`) cuando coincide.
  - **Filtro por jornada**: nuevo ref `filterJornada`, computed `jornadaOptions` que se pobla según las jornadas del torneo seleccionado. `VSelectCustom` de jornada visible solo cuando hay torneo seleccionado. Al cambiar de torneo se reinicia el filtro de jornada.
  - **Nuevas props aceptadas**: `jornadas` (Array).

**Comportamiento:**

| Funcionalidad | Delegate |
|--------------|----------|
| Partidos propios en listado | Resaltados en verde |
| Filtro por jornada | Visible al seleccionar un torneo |
| Selector de torneos | Solo muestra torneos donde tiene equipos inscritos |

## Code Style

- 4-space indent, LF line endings (`.editorconfig`)
- Laravel Pint for formatting — no custom ruleset
- PHP 8.2+ required
