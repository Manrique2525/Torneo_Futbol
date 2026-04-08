import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useCan() {
    const page = usePage()

    console.log("User:", page.props?.auth);

    const permissions = computed(
        () => page.props?.auth?.user?.permissions ?? []
    )

    const can = (permission) => permissions.value.includes(permission)

    const hasRole = (role) => {
        const roles = page.props?.auth?.user?.roles ?? []
        return roles.includes(role)
    }

    return { can, hasRole }
}
