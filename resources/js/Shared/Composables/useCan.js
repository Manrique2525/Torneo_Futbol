import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useCan() {
    const page = usePage()

    const user = computed(() => page.props?.auth?.user ?? null)

    console.log('User:', user.value)
    console.log('Permissions:', user.value?.permissions)
    console.log('Roles:', user.value?.roles)


    const permissions = computed(() => user.value?.permissions ?? [])
    const roles = computed(() => user.value?.roles ?? [])

    // SUPER ADMIN bypass (frontend)
    const isSuperAdmin = computed(() => roles.value.includes('super_admin'))

    /**
     * Check permission(s)
     * can('roles.create')
     * can(['roles.create', 'roles.update'])
     */
    const can = (permission) => {
        if (isSuperAdmin.value) return true

        if (Array.isArray(permission)) {
            return permission.some(p => permissions.value.includes(p))
        }

        return permissions.value.includes(permission)
    }

    /**
     * Check role(s)
     */
    const hasRole = (role) => {
        if (Array.isArray(role)) {
            return role.some(r => roles.value.includes(r))
        }

        return roles.value.includes(role)
    }

    return {
        can,
        hasRole,
        isSuperAdmin,
        permissions,
        roles,
    }
}
