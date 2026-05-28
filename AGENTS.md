# AGENTS.md — Torneo de Fútbol (Laravel Multi-Tenant SaaS)

Stack: Laravel 12, Inertia.js 2, Vue 3, Tailwind CSS 3, MySQL 8, Spatie Permission v7 (teams)

Full architecture documentation: `project.md`

## Commands

```bash
composer setup          # Full install: deps + .env + key + migrate + npm install + build
composer dev            # Runs serve + queue + pail + vite concurrently
composer test           # Clears config then runs PHPUnit (SQLite in-memory)
npm run dev             # Vite dev server (hot reload)
npm run build           # Vite production build
./vendor/bin/pint       # PHP code formatter (Laravel Pint)
```

## Multi-Tenant Architecture

Every model with a `tenant_id` column **must** use the `BelongsToTenant` trait (`app/Models/Traits/BelongsToTenant.php`). The trait:
- **Auto-scopes** all queries via `TenantScope` global scope (adds `WHERE tenant_id = ?`)
- **Auto-fills** `tenant_id` on create from the `TenantContext` singleton

The `TenantContext` singleton is set by the `ResolveTenant` middleware (aliased as `'tenant'`). It validates the tenant exists, is not suspended, and sets both the context and `setPermissionsTeamId()`.

**Critical gotcha**: When creating records that need to bypass the tenant scope (e.g., during registration, or super-admin queries across tenants), use `->withoutGlobalScope(TenantScope::class)` or the `withoutTenantScope()` query scope.

## Spatie Permission with Teams

- `config/permission.php`: `'teams' => true`, `team_foreign_key => 'tenant_id'`
- `setPermissionsTeamId($tenantId)` must be called before any permission/role checks
- `Gate::before` in `AppServiceProvider` grants super_admin bypass for all permissions
- Super admin has **no** permissions in DB — the Gate bypass is the only mechanism
- Each tenant gets independent copies of roles on registration via `RolePermissionService::setupTenantRoles()`

## Middleware Pipeline Order (critical)

For tenant routes the execution order is:
```
StartSession → SetPermissionsTeam → HandleInertiaRequests → auth → tenant(ResolveTenant) → Controller
```

`HandleInertiaRequests` shares `auth.user.roles` and `auth.user.permissions` with all Vue pages. `SetPermissionsTeam` sets the Spatie team context from `$user->tenant_id`.

## Routes

Module routes live in dedicated files under `routes/` and are loaded from `routes/web.php` inside the `['auth', 'verified', 'tenant']` middleware group. Example pattern:
```php
Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::group([], base_path('routes/roles.php'));
});
```

Named conventions use Spanish: `torneos.php`, `arbitros.php`, `teams.php`, `players.php`. Route names follow Laravel resource conventions.

## Frontend

- `@/` alias → `resources/js/` (configured in `jsconfig.json`)
- Inertia pages auto-resolved from `resources/js/Pages/` via `import.meta.glob('./Pages/**/*.vue')`
- Ziggy (`tightenco/ziggy`) provides route helpers in Vue templates
- Tailwind dark mode: `class` strategy
- Custom colors: `primary`, `primary-dark`, `background-light`, `background-dark`, `surface-light`, `surface-dark`

## Models & Naming

Models use Spanish names for domain entities: `Torneo`, `Arbitro`, `Player`, `Team`, `TorneoEquipo`, `TorneoGrupo`. All extend Eloquent Model and use `BelongsToTenant` when they have `tenant_id`.

`User` model uses `HasRoles` (Spatie), `BelongsToTenant`, `SoftDeletes`. Status constants: `active`, `inactive`, `suspended`.

## Testing

- PHPUnit (not Pest), SQLite in-memory (`:memory:`)
- `composer test` runs `php artisan config:clear` before tests
- `phpunit.xml` configures all env overrides (DB_CONNECTION=sqlite, QUEUE_CONNECTION=sync, etc.)
- Test namespace: `Tests\`

## Code Style

- 4-space indent, LF line endings (see `.editorconfig`)
- Laravel Pint for formatting — no custom ruleset configured
- PHP 8.2+ required
