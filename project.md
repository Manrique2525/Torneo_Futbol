# 📁 Project Structure — Soccer League Manager SaaS

> State: After Tenants + Auth + Roles & Permissions module completed.
> Stack: Laravel 11 + Vue 3 + Inertia.js + Pinia + TailwindCSS + MySQL + Spatie Permission v7

---

## 🗂️ BACKEND STRUCTURE

```
app/
├── Contexts/
│   └── TenantContext.php              ← Singleton: holds current tenant_id for the request
│
├── Enums/
│   ├── RoleEnum.php                   ← 6 roles + default permission matrix per role
│   └── PermissionEnum.php             ← 45+ permissions (single source of truth)
│
├── Exceptions/
│   └── TenantNotFoundException.php    ← 403 when tenant is missing or invalid
│
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php             ← Base controller with AuthorizesRequests trait
│   │   ├── Auth/
│   │   │   ├── AuthenticatedSessionController.php    ← Login/Logout (Breeze)
│   │   │   ├── RegisteredUserController.php          ← Register (creates Tenant + User + Roles)
│   │   │   ├── ConfirmablePasswordController.php     ← Breeze default
│   │   │   ├── EmailVerificationNotificationController.php
│   │   │   ├── EmailVerificationPromptController.php
│   │   │   ├── NewPasswordController.php
│   │   │   ├── PasswordController.php
│   │   │   ├── PasswordResetLinkController.php
│   │   │   └── VerifyEmailController.php
│   │   ├── ProfileController.php      ← Breeze default
│   │   ├── RoleController.php         ← CRUD roles (Inertia responses)
│   │   └── UserRoleController.php     ← Assign/revoke roles & permissions to users
│   │
│   ├── Middleware/
│   │   ├── HandleInertiaRequests.php  ← Shares auth.user with roles/permissions to Vue
│   │   ├── ResolveTenant.php          ← Validates tenant + sets TenantContext + Spatie team
│   │   └── SetPermissionsTeam.php     ← Sets Spatie team_id from authenticated user
│   │
│   └── Requests/
│       ├── Auth/
│       │   └── LoginRequest.php       ← Breeze default
│       ├── StoreRoleRequest.php       ← Validates role name + permissions array
│       └── UpdateRolePermissionsRequest.php  ← Validates permissions array
│
├── Models/
│   ├── Tenant.php                     ← Organization model (see fields below)
│   ├── User.php                       ← With HasRoles + BelongsToTenant traits
│   ├── Scopes/
│   │   └── TenantScope.php           ← Global scope: WHERE tenant_id = ?
│   └── Traits/
│       └── BelongsToTenant.php        ← Auto-filter + auto-fill tenant_id
│
├── Providers/
│   └── AppServiceProvider.php         ← Registers TenantContext singleton + Gate::before for super_admin
│
└── Services/
    └── RolePermissionService.php      ← All role/permission business logic
```

---

## 🗂️ FRONTEND STRUCTURE

```
resources/js/
├── app.js                             ← Inertia app setup + Ziggy
├── bootstrap.js                       ← Axios defaults
│
├── Composables/
│   └── useDarkMode.js                 ← Dark mode toggle
│
├── Components/
│   ├── ApplicationLogo.vue
│   ├── Badge.vue                      ← NEW: variant badges (success/warning/danger/info)
│   ├── Card.vue
│   ├── Dropdown.vue
│   ├── DropdownLink.vue
│   ├── EmptyState.vue
│   ├── InputError.vue
│   ├── InputLabel.vue
│   ├── MatchLiveCard.vue
│   ├── MatchTable.vue
│   ├── MetricCard.vue
│   ├── Modal.vue
│   ├── PageHeader.vue                 ← NEW: title + description + #actions slot
│   ├── Pagination.vue
│   ├── PrimaryButton.vue
│   ├── TabSelector.vue
│   ├── TextInput.vue
│   └── VSelectCustom.vue
│
├── Layouts/
│   ├── AuthenticatedLayout.vue        ← Sidebar + header + slot (with useCan for menu visibility)
│   └── GuestLayout.vue                ← Login/register wrapper
│
├── Pages/
│   ├── Auth/
│   │   ├── ConfirmPassword.vue
│   │   ├── ForgotPassword.vue
│   │   ├── Login.vue
│   │   ├── Register.vue               ← With organization_name field
│   │   ├── ResetPassword.vue
│   │   └── VerifyEmail.vue
│   ├── Dashboard.vue                  ← Static demo data for now
│   ├── Profile/
│   │   ├── Edit.vue
│   │   ├── Partials/
│   │   │   ├── DeleteUserForm.vue
│   │   │   ├── UpdatePasswordForm.vue
│   │   │   └── UpdateProfileInformationForm.vue
│   ├── Roles/
│   │   └── Index.vue                  ← NEW: Role list + permission matrix editor
│   ├── Users/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   └── Edit.vue
│   └── Welcome.vue                    ← Public landing page with pricing
│
└── Shared/
    ├── Composables/
    │   └── useCan.js                  ← NEW: can('permission') + hasRole('role') for Vue
    └── Constants/
        └── roles.js                   ← NEW: ROLE_CONFIG, MODULE_LABELS, ACTION_LABELS
```

---

## 🗂️ CONFIG & ROUTES

```
config/
├── permission.php                     ← Spatie config: teams=true, team_foreign_key=tenant_id
└── plans.php                          ← Plan definitions (basic/pro/enterprise) as config

bootstrap/
└── app.php                            ← Middleware registration (HandleInertiaRequests + SetPermissionsTeam + tenant alias)

routes/
├── web.php                            ← Public + auth routes + tenant module group loader
├── auth.php                           ← Breeze auth routes (login, register, password, etc.)
└── roles.php                          ← NEW: Role CRUD + user-role assignment routes

database/
├── migrations/
│   ├── 0001_01_01_000000_create_tenants_table.php     ← NEW
│   ├── 0001_01_01_000001_create_users_table.php       ← MODIFIED (added tenant_id, status)
│   ├── 0001_01_01_000002_create_cache_table.php       ← Laravel default
│   ├── 0001_01_01_000003_create_jobs_table.php        ← Laravel default
│   └── xxxx_xx_xx_create_permission_tables.php        ← Spatie (auto-generated)
└── seeders/
    ├── DatabaseSeeder.php
    ├── RolesAndPermissionsSeeder.php                  ← NEW: Creates 45+ permissions + 6 roles
    └── TenantSeeder.php                               ← NEW: Demo tenant + 4 users
```

---

## 📊 DATABASE MODELS

### Tenant
```
tenants
├── id
├── uuid                  (unique)
├── name                  "Liga Municipal Villahermosa"
├── slug                  "liga-villahermosa" (unique)
├── custom_domain         nullable
├── logo                  nullable, file path
├── email                 nullable
├── phone                 nullable
├── address               nullable, text
├── country               "MX"
├── timezone              "America/Mexico_City"
├── locale                "es"
├── currency              "MXN"
├── plan                  "basic" | "pro" | "enterprise" (from config/plans.php)
├── status                "active" | "suspended" | "trial"
├── last_activity_at      nullable timestamp
├── settings              nullable JSON
├── timestamps
└── soft_deletes
```

### User
```
users
├── id
├── tenant_id             FK → tenants (nullable for transition)
├── name
├── email                 (unique)
├── phone                 nullable
├── email_verified_at     nullable timestamp
├── password
├── avatar                nullable, file path
├── status                "active" | "inactive" | "suspended"
├── remember_token
├── timestamps
└── soft_deletes

Traits: HasRoles (Spatie), BelongsToTenant
```

### Spatie Tables (auto-generated, teams enabled)
```
roles
├── id
├── tenant_id             ← added by teams feature (nullable = global role)
├── name                  "admin", "manager", "referee", etc.
├── guard_name            "web"
├── timestamps

permissions
├── id
├── name                  "tournaments.view", "matches.record_events", etc.
├── guard_name            "web"
├── timestamps

model_has_roles
├── role_id               FK → roles
├── model_type            "App\Models\User"
├── model_id              FK → users
├── tenant_id             ← added by teams feature

model_has_permissions
├── permission_id         FK → permissions
├── model_type            "App\Models\User"
├── model_id              FK → users
├── tenant_id             ← added by teams feature

role_has_permissions
├── permission_id         FK → permissions
├── role_id               FK → roles
```

---

## 🔐 ROLES & PERMISSIONS SYSTEM

### How It Works

```
┌─────────────────────────────────────────────────────────────┐
│                    REQUEST LIFECYCLE                          │
│                                                              │
│  1. User logs in                                             │
│  2. SetPermissionsTeam middleware                             │
│     → setPermissionsTeamId($user->tenant_id)                │
│     → Spatie now knows which tenant to scope to              │
│  3. HandleInertiaRequests middleware                          │
│     → setPermissionsTeamId() again (safety)                 │
│     → $user->getRoleNames() → scoped to tenant              │
│     → $user->getAllPermissions() → scoped to tenant          │
│     → Shares auth.user.roles + auth.user.permissions to Vue │
│  4. ResolveTenant middleware (on 'tenant' routes only)       │
│     → Validates tenant exists & is active                    │
│     → Sets TenantContext singleton                           │
│     → TenantScope auto-filters all queries                   │
│  5. Controller                                               │
│     → $this->authorize('tournaments.view')                  │
│     → Gate checks user's permissions (scoped to tenant)      │
│  6. Vue                                                      │
│     → useCan().can('tournaments.view')                      │
│     → Reads from shared auth.user.permissions                │
└─────────────────────────────────────────────────────────────┘
```

### Super Admin Bypass

```
// AppServiceProvider.php
Gate::before(function ($user, $ability) {
    if ($user->hasRole('super_admin')) {
        return true;  // bypasses ALL permission checks
    }
});

→ super_admin has NO permissions in DB
→ Gate::before returns true for everything
→ No need to sync permissions when adding new ones
```

### The 6 Roles

```
┌──────────────┬──────────────────────────────────────────────┐
│ Role         │ Description                                   │
├──────────────┼──────────────────────────────────────────────┤
│ super_admin  │ SaaS owner. Bypasses all via Gate::before.    │
│ admin        │ Tenant owner. ALL permissions.                │
│ manager      │ Sports coordinator. Most permissions.         │
│ referee      │ Views + records match events.                 │
│ delegate     │ Manages own team + players.                   │
│ player       │ Views schedules + confirms attendance.        │
└──────────────┴──────────────────────────────────────────────┘
```

### The 45+ Permissions (by module)

```
tournaments     → view, create, update, delete
teams           → view, create, update, update_own, delete
players         → view, view_own, create, update, update_own, delete
matches         → view, create, update, delete, record_events, confirm, confirm_attendance
match_days      → view, create, update, delete
fields          → view, create, update, delete
referees        → view, create, update, delete
sanctions       → view, create, update, void
payments        → view, create, verify, upload_receipt
stats           → view
standings       → view, recalculate
reports         → view, export
users           → view, create, update, delete
roles           → view, create, update, delete
settings        → view, update
```

### Default Permission Matrix

```
Permission              │ admin │ manager │ referee │ delegate │ player
────────────────────────┼───────┼─────────┼─────────┼──────────┼───────
tournaments.view        │  ✅   │   ✅    │   ✅    │    ✅    │  ✅
tournaments.create      │  ✅   │   ✅    │         │          │
tournaments.update      │  ✅   │   ✅    │         │          │
tournaments.delete      │  ✅   │         │         │          │
teams.view              │  ✅   │   ✅    │   ✅    │    ✅    │  ✅
teams.create            │  ✅   │   ✅    │         │          │
teams.update            │  ✅   │   ✅    │         │          │
teams.update_own        │  ✅   │         │         │    ✅    │
players.view            │  ✅   │   ✅    │   ✅    │    ✅    │
players.view_own        │  ✅   │         │         │          │  ✅
players.create          │  ✅   │   ✅    │         │    ✅    │
players.update          │  ✅   │   ✅    │         │          │
players.update_own      │  ✅   │         │         │    ✅    │
matches.record_events   │  ✅   │   ✅    │   ✅    │          │
matches.confirm         │  ✅   │         │         │    ✅    │
matches.confirm_att.    │  ✅   │         │         │          │  ✅
sanctions.create        │  ✅   │   ✅    │         │          │
sanctions.void          │  ✅   │         │         │          │
payments.verify         │  ✅   │         │         │          │
payments.upload_receipt │  ✅   │         │         │    ✅    │
roles.*                 │  ✅   │         │         │          │
users.*                 │  ✅   │         │         │          │
settings.*              │  ✅   │         │         │          │
```

### Tenant Role Isolation

```
Tenant A (Liga Villahermosa)          Tenant B (Club Deportivo)
┌────────────────────────────┐       ┌────────────────────────────┐
│ admin   → 45 permissions   │       │ admin   → 45 permissions   │
│ manager → 30 permissions   │       │ manager → 25 permissions   │ ← customized!
│ referee → 12 permissions   │       │ referee → 12 permissions   │
│ delegate → 15 permissions  │       │ custom_role → 8 perms      │ ← tenant-specific
└────────────────────────────┘       └────────────────────────────┘

→ Each tenant has INDEPENDENT copies of roles
→ Admin in Tenant A can modify permissions WITHOUT affecting Tenant B
→ Tenants can create custom roles (e.g. "field_coordinator")
→ Global roles (tenant_id = NULL) are read-only templates
```

---

## 🛣️ ROUTES STRUCTURE

### web.php
```
GET  /                           → Welcome (public landing page)

[auth + verified + tenant]
GET  /dashboard                  → Dashboard

[auth]
GET  /profile                    → Profile edit
PATCH /profile                   → Profile update
DELETE /profile                  → Profile destroy
```

### auth.php (Breeze)
```
[guest]
GET   /register                  → Register form
POST  /register                  → Create tenant + user + assign admin role
GET   /login                     → Login form
POST  /login                     → Authenticate
GET   /forgot-password           → Password reset request
POST  /forgot-password           → Send reset link
GET   /reset-password/{token}    → Reset form
POST  /reset-password            → Update password

[auth]
GET   /verify-email              → Verification prompt
GET   /verify-email/{id}/{hash}  → Verify email
POST  /email/verification-notification → Resend verification
GET   /confirm-password          → Confirm password form
POST  /confirm-password          → Validate password
PUT   /password                  → Change password
POST  /logout                    → Logout
```

### roles.php
```
[auth + verified + tenant]
GET    /roles                    → Role list + permission matrix
POST   /roles                    → Create custom role
PUT    /roles/{role}             → Update role permissions
DELETE /roles/{role}             → Delete custom role

GET    /users/{user}/permissions → User permission breakdown
POST   /users/{user}/roles      → Assign role to user
DELETE /users/{user}/roles       → Revoke role from user
POST   /users/{user}/permissions → Give direct permission
DELETE /users/{user}/permissions → Revoke direct permission
```

---

## ⚙️ MIDDLEWARE PIPELINE (order matters)

```
bootstrap/app.php registers:

web group:
  1. HandleInertiaRequests    ← shares auth.user + roles + permissions to Vue
  2. SetPermissionsTeam       ← sets Spatie team context from user.tenant_id

priority (before SubstituteBindings):
  1. SetPermissionsTeam

aliases:
  'tenant' → ResolveTenant    ← validates tenant + sets TenantContext singleton

Final execution order for a tenant route:
  StartSession → SetPermissionsTeam → HandleInertiaRequests → auth → tenant(ResolveTenant) → Controller
```

---

## 🌱 SEEDERS

### RolesAndPermissionsSeeder
```
Creates:
  - 45+ permissions (from PermissionEnum::all())
  - 6 global roles (tenant_id = null) as templates
  - Each role gets its default permissions (from RoleEnum::defaultPermissions())
  - super_admin gets NO permissions (Gate::before handles it)
```

### TenantSeeder
```
Creates:
  - 1 Tenant: "Liga Municipal Villahermosa" (plan: pro, status: active)
  - 4 Users with roles:
    ┌─────────────────────┬────────────────────┬──────────┬──────────┐
    │ Name                │ Email              │ Password │ Role     │
    ├─────────────────────┼────────────────────┼──────────┼──────────┤
    │ Admin Liga          │ admin@demo.com     │ password │ admin    │
    │ Carlos Coordinador  │ manager@demo.com   │ password │ manager  │
    │ Roberto Árbitro     │ referee@demo.com   │ password │ referee  │
    │ Miguel Delegado     │ delegate@demo.com  │ password │ delegate │
    └─────────────────────┴────────────────────┴──────────┴──────────┘
```

---

## 📦 REGISTRATION FLOW

```
User submits Register form
  ↓
RegisteredUserController::store()
  ↓
DB::transaction {
  1. Create Tenant (status: trial, plan: basic)
  2. Create User (withoutGlobalScopes, tenant_id = tenant.id)
  3. RolePermissionService::setupTenantRoles(tenant.id)
     → Creates admin, manager, referee, delegate, player roles FOR this tenant
     → Assigns default permissions to each role
  4. setPermissionsTeamId(tenant.id)
  5. $user->assignRole('admin')
}
  ↓
Auth::login($user)
  ↓
Redirect to /dashboard
```

---

## 🔮 NEXT MODULES TO BUILD (in order)

```
1. Tournaments   → routes/tournaments.php
2. Teams         → routes/teams.php
3. Players       → routes/players.php
4. Fields        → routes/fields.php
5. Referees      → routes/referees.php
6. Match Days    → routes/match-days.php
7. Matches       → routes/matches.php
8. Goals + Cards → inside matches routes
9. Sanctions     → routes/sanctions.php
10. Payments     → routes/payments.php
```

Each module follows the pattern:
```
Migration → Model (with BelongsToTenant) → Service → Controller → FormRequest → Vue Page
Route file loaded in web.php: Route::group([], base_path('routes/module.php'))
```
